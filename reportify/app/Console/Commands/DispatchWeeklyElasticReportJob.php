<?php

namespace App\Console\Commands;

use App\Jobs\GenerateDailyElasticReportJob;
use Illuminate\Console\Command;


class DispatchWeeklyElasticReportJob extends Command
{
    protected $signature = 'report:weekly';
    protected $description = 'Dispatch an asynchronous job to generate the Elasticsearch report';

    public function handle(): void
    {
        GenerateDailyElasticReportJob::dispatch();
        $this->info('Job dispatched to queue!');
    }
}
