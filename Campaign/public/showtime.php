<?php

use Airbrake\MonologHandler;
use App\Campaign;
use App\CampaignBanner;
use DeviceDetector\Cache\PSR6Bridge;
use GeoIp2\Database\Reader;
use Illuminate\Support\Collection;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

$logger = new Logger('showtime');
try {
    $enabledAirbrake = env('AIRBRAKE_ENABLED', env('APP_ENV') !== 'local');
    if ($enabledAirbrake) {
        $airbrake = new \Airbrake\Notifier([
            'enabled' => true,
            'projectId' => '_',
            'projectKey' => env('AIRBRAKE_API_KEY', ''),
            'host' => env('AIRBRAKE_API_HOST', 'api.airbrake.io'),
            'environment' => env('APP_ENV', 'production'),
        ]);

        $logHandler = new MonologHandler($airbrake, Logger::WARNING);
        $logger->setHandlers([$logHandler]);
    }
} catch (\Exception $e) {
    $logger->warning("unable to register airbrake notifier: " . $e->getMessage());
}

$data = filter_input(INPUT_GET, 'data');
$callback = filter_input(INPUT_GET, 'callback');

/**
 * @param string $callback jsonp callback name
 * @param array $response response to be json-encoded and returned
 * @param int $statusCode http status code to be returned
 */
function jsonp_response($callback, $response, $statusCode = 200) {
    http_response_code($statusCode);
    $params = json_encode($response);
    echo "{$callback}({$params})";
    exit;
}

/**
 * public_path overrides Laravel's helper function to prevent usage of Laravel's app()
 *
 * @param string $path
 * @return string
 */
function public_path($path = '') {
    return __DIR__ .($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
}

/**
 * asset overrides Laravel's helper function to prevent usage of Laravel's app()
 *
 * @param $path
 * @param null $secure
 * @return string
 */
function asset($path, $secure = null) {
    return "//" . $_SERVER['HTTP_HOST'] . "/" . trim($path, '/');
}

/**
 * render is responsible for rendering JS to be executed on client.
 *
 * @param CampaignBanner $variant
 * @param Campaign $campaign
 * @param $alignments
 * @param $dimensions
 * @param $positions
 * @return string
 * @throws Exception
 */
function render($variant, $campaign, $alignments, $dimensions, $positions) {
    $alignmentsJson = json_encode($alignments);
    $dimensionsJson = json_encode($dimensions);
    $positionsJson = json_encode($positions);

    $bannerJs = asset(mix('/js/banner.js', '/assets/lib'));
    $isControlGroup = intval($variant->controlGroup);

    if (!$variant->banner ){
        $js = "var bannerUuid = null;";
    } else {
        $js = "
var bannerUuid = '{$variant->banner->uuid}';
var bannerId = 'b-' + bannerUuid;
var bannerJsonData = {$variant->banner->toJson()};
";
    }

    $js .= <<<JS
var variantUuid = '{$variant->uuid}';
var campaignUuid = '{$campaign->uuid}';
var isControlGroup = {$isControlGroup};
var scripts = [];
if (typeof window.remplib.banner === 'undefined') {
    scripts.push("{$bannerJs}");
}

var styles = [];

var waiting = scripts.length + styles.length;
var run = function() {
    if (waiting) {
        return;
    }

    var banner = {};
    var alignments = JSON.parse('{$alignmentsJson}');
    var dimensions = JSON.parse('{$dimensionsJson}');
    var positions = JSON.parse('{$positionsJson}');

    if (!isControlGroup) {
        banner = remplib.banner.fromModel(bannerJsonData);
    }

    banner.show = false;
    banner.alignmentOptions = alignments;
    banner.dimensionOptions = dimensions;
    banner.positionOptions = positions;

    banner.campaignUuid = campaignUuid;
    banner.variantUuid = variantUuid;
    banner.uuid = bannerUuid;

    if (isControlGroup) {
        banner.displayDelay = 0;
        banner.displayType = 'none';
    } else {
        var d = document.createElement('div');
        d.id = bannerId;
        var bp = document.createElement('banner-preview');
        d.appendChild(bp);

        var target = null;
        if (banner.displayType === 'inline') {
            target = document.querySelector(banner.targetSelector);
            if (target === null) {
                console.warn("REMP: unable to display banner, selector not matched: " + banner.targetSelector);
                return;
            }
        } else {
            target = document.getElementsByTagName('body')[0];
        }
        target.appendChild(d);

        remplib.banner.bindPreview('#' + bannerId, banner);
    }

    setTimeout(function() {
        remplib.tracker.trackEvent("banner", "show", null, null, {
            "utm_source": "remp_campaign",
            "utm_medium": banner.displayType,
            "utm_campaign": banner.campaignUuid,
            "utm_content": banner.uuid,
            "banner_variant": banner.variantUuid
        });
        banner.show = true;
        if (banner.closeTimeout) {
            setTimeout(function() {
                banner.show = false;
            }, banner.closeTimeout);
        }
        remplib.campaign.storeCampaignDetails(banner.campaignUuid, banner.uuid, banner.variantUuid);
    }, banner.displayDelay);
};

for (var i=0; i<scripts.length; i++) {
    remplib.loadScript(scripts[i], function() {
        waiting -= 1;
        run();
    });
}
for (i=0; i<styles.length; i++) {
    remplib.loadStyle(styles[i], function() {
        waiting -= 1;
        run();
    });
}
JS;

    return $js;
}

class GeoReader
{
    private $reader;

    public function get()
    {
        if (!$this->reader) {
            $this->reader = new Reader(realpath(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . getenv('MAXMIND_DATABASE')));
        }
        return $this->reader;
    }
}

class DeviceDetector
{
    private $detector;

    private $redis;

    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    public function get($userAgent)
    {
        if (!$this->detector) {
            $this->detector = new \DeviceDetector\DeviceDetector();
            $this->detector->setCache(
                new PSR6Bridge(
                    new \Cache\Adapter\Predis\PredisCachePool($this->redis)
                )
            );

            $this->detector->setUserAgent($userAgent);
            $this->detector->parse();
        }
        return $this->detector;
    }
}

class Request
{
    private $request;

    public function get()
    {
        if (!$this->request) {
            $this->request = \App\Http\Request::createFromGlobals();
        }
        return $this->request;
    }
}

////////////////////////////////////////////////////////////////////////////////
// ACTUAL SHOWTIME EXECUTION STEPS

// validation
try {
    $data = json_decode($data);
} catch (\InvalidArgumentException $e) {
    $logger->warning('could not decode JSON in Showtime', $data);
    jsonp_response($callback, [
            'success' => false,
            'errors' => ['invalid data json provided'],
        ], 400);
}

$url = $data->url ?? null;
if (!$url) {
    jsonp_response($callback, [
            'success' => false,
            'errors' => ['url is required and missing'],
        ], 400);
}

$userId = null;
if (isset($data->userId) || !empty($data->userId)) {
    $userId = $data->userId;
}

$browserId = null;
if (isset($data->browserId) || !empty($data->browserId)) {
    $browserId = $data->browserId;
}
if (!$browserId) {
    jsonp_response($callback, [
            'success' => false,
            'errors' => ['browserId is required and missing'],
        ], 400);
}

// dependencies initialization
$redis = new \Predis\Client([
    'scheme' => 'tcp',
    'host'   => getenv('REDIS_HOST'),
    'port'   => getenv('REDIS_PORT') ?: 6379,
],[
    'parameters' => ['password' => env('REDIS_PASSWORD', null)],
]);

/** @var \App\Contracts\SegmentAggregator $segmentAggregator */
$segmentAggregator = unserialize($redis->get(\App\Providers\AppServiceProvider::SEGMENT_AGGREGATOR_REDIS_KEY))();
if (!$segmentAggregator) {
    jsonp_response($callback, [
        'success' => false,
        'errors' => ['unable to get cached segment aggregator'],
    ], 500);
}

if (isset($data->cache)) {
    $segmentAggregator->setProviderData($data->cache);
}

$campaignIds = json_decode($redis->get(Campaign::ACTIVE_CAMPAIGN_IDS)) ?? [];
if (count($campaignIds) == 0) {
    jsonp_response($callback, [
        'success' => true,
        'data' => [],
        'providerData' => $segmentAggregator->getProviderData(),
    ]);
}

/** @var Campaign $campaign */
$positions = json_decode($redis->get(\App\Models\Position\Map::POSITIONS_MAP_REDIS_KEY)) ?? [];
$dimensions = json_decode($redis->get(\App\Models\Dimension\Map::DIMENSIONS_MAP_REDIS_KEY)) ?? [];
$alignments = json_decode($redis->get(\App\Models\Alignment\Map::ALIGNMENTS_MAP_REDIS_KEY)) ?? [];

$displayedCampaigns = [];

$deviceDetector = new DeviceDetector($redis);
$geoReader = new GeoReader();
$request = new Request();

// campaign resolution
foreach ($campaignIds as $campaignId) {
    $campaign = unserialize($redis->get(Campaign::CAMPAIGN_TAG . ":{$campaignId}"));
    $running = false;

    foreach ($campaign->schedules as $schedule) {
        if ($schedule->isRunning()) {
            $running = true;
            break;
        }
    }
    if (!$running) {
        continue;
    }

    /** @var Collection $campaignBanners */
    $campaignBanners = $campaign->campaignBanners->keyBy('uuid');

    // banner
    if ($campaignBanners->count() == 0) {
        $logger->error("Active campaign [{$campaign->uuid}] has no banner set");
        continue;
    }

    $bannerUuid = null;
    $variantUuid = null;

    // find variant previously displayed to user
    $seenCampaignsBanners = $data->campaignsBanners ?? false;
    if ($seenCampaignsBanners && isset($seenCampaignsBanners->{$campaign->uuid})) {
        $bannerUuid = $seenCampaignsBanners->{$campaign->uuid}->bannerId ?? null;
        $variantUuid = $seenCampaignsBanners->{$campaign->uuid}->variantId ?? null;
    }

    // fallback for older version of campaigns local storage data
    // where decision was based on bannerUuid and not variantUuid (which was not present at all)
    if ($bannerUuid && !$variantUuid) {
        foreach ($campaignBanners as $campaignBanner) {
            if (optional($campaignBanner->banner)->uuid === $bannerUuid) {
                $variantUuid = $campaignBanner->uuid;
                break;
            }
        }
    }

    /** @var CampaignBanner $seenVariant */
    // unset seen variant if it was deleted
    if (!($seenVariant = $campaignBanners->get($variantUuid))) {
        $variantUuid = null;
    }

    // unset seen variant if its proportion is 0%
    if ($seenVariant && $seenVariant->proportion === 0) {
        $variantUuid = null;
    }

    // variant still not set, choose random variant
    if ($variantUuid === null) {
        $variantsMapping = $campaign->getVariantsProportionMapping();

        $randVal = mt_rand(0, 100);
        $currPercent = 0;

        foreach ($variantsMapping as $uuid => $proportion) {
            $currPercent = $currPercent + $proportion;
            if ($currPercent >= $randVal) {
                $variantUuid = $uuid;
                break;
            }
        }
    }

    /** @var CampaignBanner $variant */
    $variant = $campaignBanners->get($variantUuid);
    if (!$variant) {
        $logger->error("Unable to get CampaignBanner [{$variantUuid}] for campaign [{$campaign->uuid}]");
        continue;
    }

    // check if campaign is set to be seen only once per session
    // and check campaign UUID against list of campaigns seen by user
    $campaignsSeen = $data->campaignsSeen ?? false;
    if ($campaign->once_per_session && $campaignsSeen) {
        $seen = false;
        foreach ($campaignsSeen as $campaignSeen) {
            if ($campaignSeen->campaignId === $campaign->uuid) {
                $seen = true;
                break;
            }
        }
        if ($seen) {
            continue;
        }
    }

    // signed in state
    if (isset($campaign->signed_in) && $campaign->signed_in !== boolval($userId)) {
        continue;
    }

    // using adblock?
    if ($campaign->using_adblock && !$data->usingAdblock || $campaign->using_adblock === false && $data->usingAdblock) {
        continue;
    }

    // pageview rules
    $pageviewCount = $data->pageviewCount ?? null;
    if ($pageviewCount !== null && $campaign->pageview_rules !== null) {
        foreach ($campaign->pageview_rules as $rule) {
            if (!$rule['num'] || !$rule['rule']) {
                continue;
            }

            switch ($rule['rule']) {
                case Campaign::PAGEVIEW_RULE_EVERY:
                    if ($pageviewCount % $rule['num'] !== 0) {
                        continue 3;
                    }
                    break;
                case Campaign::PAGEVIEW_RULE_SINCE:
                    if ($pageviewCount < $rule['num']) {
                        continue 3;
                    }
                    break;
                case Campaign::PAGEVIEW_RULE_BEFORE:
                    if ($pageviewCount >= $rule['num']) {
                        continue 3;
                    }
                    break;
            }
        }
    }

    // url filters
    if ($campaign->url_filter === Campaign::URL_FILTER_EXCEPT_AT) {
        foreach ($campaign->url_patterns as $urlPattern) {
            if (strpos($data->url, $urlPattern) !== false) {
                continue 2;
            }
        }
    }
    if ($campaign->url_filter === Campaign::URL_FILTER_ONLY_AT) {
        $matched = false;
        foreach ($campaign->url_patterns as $urlPattern) {
            if (strpos($data->url, $urlPattern) !== false) {
                $matched = true;
            }
        }
        if (!$matched) {
            continue;
        }
    }

    // referer filters
    if ($campaign->referer_filter === Campaign::URL_FILTER_EXCEPT_AT && $data->referer) {
        foreach ($campaign->referer_patterns as $refererPattern) {
            if (strpos($data->referer, $refererPattern) !== false) {
                continue 2;
            }
        }
    }
    if ($campaign->referer_filter === Campaign::URL_FILTER_ONLY_AT) {
        if (!$data->referer) {
            continue;
        }
        $matched = false;
        foreach ($campaign->referer_patterns as $refererPattern) {
            if (strpos($data->referer, $refererPattern) !== false) {
                $matched = true;
            }
        }
        if (!$matched) {
            continue;
        }
    }

    // device rules
    if (!isset($data->userAgent)) {
        $logger->error("Unable to load user agent for userId [{$userId}]");
    } else {
        if (!in_array(Campaign::DEVICE_MOBILE, $campaign->devices) && $deviceDetector->get($data->userAgent)->isMobile()) {
            continue;
        }

        if (!in_array(Campaign::DEVICE_DESKTOP, $campaign->devices) && $deviceDetector->get($data->userAgent)->isDesktop()) {
            continue;
        }
    }

    // country rules
    if (!$campaign->countries->isEmpty()) {
        // load country ISO code based on IP
        try {
            $record = $geoReader->get()->country($request->get()->ip());
            $countryCode = $record->country->isoCode;
        } catch (\MaxMind\Db\Reader\InvalidDatabaseException | GeoIp2\Exception\AddressNotFoundException $e) {
            $logger->error("Unable to load country for campaign [{$campaign->uuid}] with country rules: " . $e->getMessage());
            continue;
        }
        if (is_null($countryCode)) {
            $logger->error("Unable to load country for campaign [{$campaign->uuid}] with country rules");
            continue;
        }

        // check against white / black listed countries

        if (!$campaign->countriesBlacklist->isEmpty() && $campaign->countriesBlacklist->contains('iso_code', $countryCode)) {
            continue;
        }
        if (!$campaign->countriesWhitelist->isEmpty() && !$campaign->countriesWhitelist->contains('iso_code', $countryCode)) {
            continue;
        }
    }

    // segment
    foreach ($campaign->segments as $campaignSegment) {
        $campaignSegment->setRelation('campaign', $campaign); // setting this manually to avoid DB query

        if ($userId) {
            if (!$segmentAggregator->checkUser($campaignSegment, strval($userId))) {
                continue 2;
            }
        } else {
            if (!$segmentAggregator->checkBrowser($campaignSegment, strval($browserId))) {
                continue 2;
            }
        }
    }

    $displayedCampaigns[] = render(
        $variant,
        $campaign,
        $alignments,
        $dimensions,
        $positions
    );
}

if (empty($displayedCampaigns)) {
    jsonp_response($callback, [
        'success' => true,
        'data' => [],
        'providerData' => $segmentAggregator->getProviderData(),
    ]);
}

jsonp_response($callback, [
    'success' => true,
    'errors' => [],
    'data' => $displayedCampaigns,
    'providerData' => $segmentAggregator->getProviderData(),
]);
