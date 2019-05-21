<?php

namespace App\Providers;

use App\Contracts\PetitPress\Segment;
use App\Contracts\SegmentAggregator;
use App\Contracts\SegmentContract;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use http\Cookie;
use Illuminate\Foundation\Application;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\ServiceProvider;

class PetitPressSegmentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Segment::class, function (Application $app) {
            $cookieJar = CookieJar::fromArray([
                config('services.petit_press_segment.cookie_name') => config('services.petit_press_segment.cookie_value')
            ], config('services.petit_press_segment.cookie_domain'));

            $client = new Client([
                'base_uri' => config('services.petit_press_segment.base_url'),
                'cookies' => $cookieJar
            ]);
            /** @var RedisManager $redis */
            $redis = $app->make('redis')->connection()->client();
            return new Segment($client, $redis);
        });
        if (config('services.petit_press_segment.base_url')) {
            $this->app->tag(Segment::class, [SegmentAggregator::TAG]);
        }
    }

    public function provides()
    {
        return [SegmentContract::class];
    }
}
