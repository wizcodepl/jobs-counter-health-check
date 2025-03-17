<?php

declare(strict_types=1);

namespace WizcodePl\JobsCounterHealthCheck\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;
use Spatie\Health\Checks\Result;
use Spatie\Health\Enums\Status;
use WizcodePl\JobsCounterHealthCheck\JobsCounterCheck;

class JobsCounterCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('CREATE TABLE jobs (id INTEGER PRIMARY KEY AUTOINCREMENT, queue TEXT)');
        DB::statement('CREATE TABLE failed_jobs (id INTEGER PRIMARY KEY AUTOINCREMENT, queue TEXT)');
    }

    public function test_it_returns_ok_when_failed_jobs_are_below_threshold(): void
    {
        DB::table('jobs')->insert(['queue' => 'default']);
        DB::table('failed_jobs')->insert(['queue' => 'default']);

        $check = new JobsCounterCheck;
        $result = $check->run();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->status === Status::ok());
    }

    public function test_it_returns_failed_when_failed_jobs_exceed_threshold(): void
    {
        for ($i = 0; $i < 11; $i++) {
            DB::table('failed_jobs')->insert(['queue' => 'default']);
        }

        $check = (new JobsCounterCheck)->failedJobsThreshold(10);
        $result = $check->run();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->status === Status::failed());
    }

    public function test_it_filters_by_queue(): void
    {
        DB::table('jobs')->insert(['queue' => 'default']);
        DB::table('jobs')->insert(['queue' => 'custom']);
        DB::table('failed_jobs')->insert(['queue' => 'default']);
        DB::table('failed_jobs')->insert(['queue' => 'custom']);

        $check = (new JobsCounterCheck)->queue('default');
        $result = $check->run();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(1, $result->meta['active_jobs']);
        $this->assertEquals(1, $result->meta['failed_jobs']);
        $this->assertEquals('default', $result->meta['queue']);
    }
}
