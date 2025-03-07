<?php

namespace WizcodePl\JobsCounterHealthCheck;

use Illuminate\Support\Facades\DB;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class JobsCounterCheck extends Check
{
    protected int $failedJobsThreshold = 10;

    protected ?string $queue = null;

    public function failedJobsThreshold(int $threshold): self
    {
        $this->failedJobsThreshold = $threshold;

        return $this;
    }

    public function queue(?string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    public function run(): Result
    {
        $activeJobsQuery = DB::table('jobs');
        if ($this->queue !== null) {
            $activeJobsQuery->where('queue', $this->queue);
        }
        $activeJobs = $activeJobsQuery->count();

        $failedJobsQuery = DB::table('failed_jobs');
        if ($this->queue !== null) {
            $failedJobsQuery->where('queue', $this->queue);
        }
        $failedJobs = $failedJobsQuery->count();

        $result = Result::make()
            ->shortSummary(sprintf(
                'Active: %s, Failed: %s (Queue: %s)',
                $activeJobs,
                $failedJobs,
                $this->queue ?? 'all'
            ))
            ->meta([
                'active_jobs' => $activeJobs,
                'failed_jobs' => $failedJobs,
                'queue' => $this->queue ?? 'all',
            ]);

        if ($failedJobs >= $this->failedJobsThreshold) {
            return $result->failed(sprintf(
                'Failed jobs in queue "%s" (%d > %d)',
                $this->queue ?? 'all',
                $failedJobs,
                $this->failedJobsThreshold
            ));
        }

        return $result->ok();
    }
}
