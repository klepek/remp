<?php

namespace Tests\Unit;

use App\Schedule;
use Carbon\Carbon;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function testWaitingSchedule()
    {
        $schedule = new Schedule([
            'start_time' => new Carbon('2017-11-01 01:23:45'),
            'end_time' => new Carbon('2017-11-02 01:23:45'),
        ]);
        Carbon::setTestNow(new Carbon('2017-11-01 00:00:00'));
        $this->assertFalse($schedule->isRunning());
        $this->assertTrue($schedule->isRunnable());
        $this->assertTrue($schedule->isDeletable());
    }

    public function testForcedRunnningBeforeScheduledStarted()
    {
        $schedule = new Schedule([
            'start_time' => new Carbon('2017-11-01 01:23:45'),
            'end_time' => new Carbon('2017-11-02 01:23:45'),
        ]);
        $schedule->status = Schedule::STATUS_RUNNING;
        Carbon::setTestNow(new Carbon('2017-11-01 00:00:00'));
        $this->assertTrue($schedule->isRunning());
        $this->assertFalse($schedule->isRunnable());
        $this->assertFalse($schedule->isDeletable());
    }

    public function testForcedRunnningAfterScheduledEnd()
    {
        $schedule = new Schedule([
            'start_time' => new Carbon('2017-11-01 01:23:45'),
            'end_time' => new Carbon('2017-11-02 01:23:45'),
        ]);
        $schedule->status = Schedule::STATUS_RUNNING;
        Carbon::setTestNow(new Carbon('2017-11-03 00:00:00'));
        $this->assertFalse($schedule->isRunning());
        $this->assertFalse($schedule->isRunnable());
        $this->assertFalse($schedule->isDeletable());
    }

    public function testForcedStoppedBeforeScheduledStarted()
    {
        $schedule = new Schedule([
            'start_time' => new Carbon('2017-11-01 01:23:45'),
            'end_time' => new Carbon('2017-11-02 01:23:45'),
        ]);
        $schedule->status = Schedule::STATUS_STOPPED;
        Carbon::setTestNow(new Carbon('2017-11-01 00:00:00'));
        $this->assertFalse($schedule->isRunning());
        $this->assertFalse($schedule->isRunnable());
        $this->assertFalse($schedule->isDeletable());
    }

    public function testNativeRunning()
    {
        $schedule = new Schedule([
            'start_time' => new Carbon('2017-11-01 01:23:45'),
            'end_time' => new Carbon('2017-11-02 01:23:45'),
        ]);
        Carbon::setTestNow(new Carbon('2017-11-02 00:00:00'));
        $this->assertTrue($schedule->isRunning());
        $this->assertFalse($schedule->isRunnable());
        $this->assertFalse($schedule->isDeletable());
    }

    public function testNativeFinished()
    {
        $schedule = new Schedule([
            'start_time' => new Carbon('2017-11-01 01:23:45'),
            'end_time' => new Carbon('2017-11-02 01:23:45'),
        ]);
        Carbon::setTestNow(new Carbon('2017-11-03 00:00:00'));
        $this->assertFalse($schedule->isRunning());
        $this->assertFalse($schedule->isRunnable());
        $this->assertFalse($schedule->isDeletable());
    }

    public function testPausedRunnable()
    {
        $schedule = new Schedule([
            'start_time' => new Carbon('2017-11-01 01:23:45'),
            'end_time' => new Carbon('2017-11-02 01:23:45'),
        ]);
        $schedule->status = Schedule::STATUS_PAUSED;
        Carbon::setTestNow(new Carbon('2017-11-02 00:00:00'));
        $this->assertFalse($schedule->isRunning());
        $this->assertTrue($schedule->isRunnable());
        $this->assertFalse($schedule->isDeletable());
    }

    public function testPausedNotRunnable()
    {
        $schedule = new Schedule([
            'start_time' => new Carbon('2017-11-01 01:23:45'),
            'end_time' => new Carbon('2017-11-02 01:23:45'),
        ]);
        $schedule->status = Schedule::STATUS_PAUSED;
        Carbon::setTestNow(new Carbon('2017-11-03 00:00:00'));
        $this->assertFalse($schedule->isRunning());
        $this->assertFalse($schedule->isRunnable());
        $this->assertFalse($schedule->isDeletable());
    }
}
