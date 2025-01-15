<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Elasticsearch\ElasticReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class GenerateElasticReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct()
    {}

    public function handle(ElasticReportService $elasticService): void
    {
        try {
            $response = $elasticService->getDailyKeywordHistogram(['تهران', 'شهر']);
            // Define the relative path for storage
            $filePath = 'reports/keyword_histogram_' . now()->format('Y_m_d_H_i_s') . '.csv';

            // Use storage path properly with Storage facade
            $fileStream = fopen('php://temp', 'r+');
            fputcsv($fileStream, ['Date', 'Post Count']);

            if (isset($response['aggregations']['posts_per_day']['buckets'])) {
                foreach ($response['aggregations']['posts_per_day']['buckets'] as $bucket) {
                    fputcsv($fileStream, [$bucket['key_as_string'], $bucket['doc_count']]);
                }
            } else {
                fputcsv($fileStream, ['No data available']);
            }

            rewind($fileStream);  // Move the pointer back to the beginning
            Storage::disk('public')->put($filePath, stream_get_contents($fileStream));
            fclose($fileStream);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

}
