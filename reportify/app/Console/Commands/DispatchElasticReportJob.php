<?php

namespace App\Console\Commands;

use App\Jobs\GenerateElasticReportJob;
use Illuminate\Console\Command;


class DispatchElasticReportJob extends Command
{
    protected $signature = 'report:dispatch';
    protected $description = 'Dispatch an asynchronous job to generate the Elasticsearch report';

    public function handle(): void
    {
        $keywords = ['keyword1', 'keyword2'];  // Define your keywords
        GenerateElasticReportJob::dispatch();
        $this->info('Job dispatched to queue!');
    }
}
