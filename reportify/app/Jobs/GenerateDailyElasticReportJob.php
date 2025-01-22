<?php

namespace App\Jobs;

use App\Mail\ReportGenerated;
use App\Models\Report;
use App\Models\User;
use App\Services\Elasticsearch\ElasticReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class GenerateDailyElasticReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct()
    {}

    public function handle(ElasticReportService $elasticService): void
    {
        Report::query()
            ->with('user')
            ->where('interval', 'day')
            ->chunk(50, function ($reports) use ($elasticService) {
                foreach ($reports as $report) {
                    $filePath = $elasticService->generateCsvFile($report['filter_keys']);
                    $filePath = Storage::disk('public')->path($filePath);
                    $user = $report['user'];
                    Mail::to($user->email)
                        ->send(new ReportGenerated(
                            ['message' => "Your report has been generated."],
                            $filePath
                        ));
                }
            });
    }

}
