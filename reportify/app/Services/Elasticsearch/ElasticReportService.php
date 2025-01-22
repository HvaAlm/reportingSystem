<?php

namespace App\Services\Elasticsearch;


use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ElasticReportService
{
    protected Client|null $client;

    public function __construct()
    {
        $elasticUsername = env('ELASTIC_USER', 'elastic');
        $elasticPassword = env('ELASTIC_PASS', 'password');
        try {
            Log::info("Elastic Report Service");
            $this->client = ClientBuilder::create()
                ->setHosts(['elasticsearch:9200'])
                ->setBasicAuthentication($elasticUsername, $elasticPassword)
                ->build();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->client = null;
        }

    }

    public function getDailyKeywordHistogram(array $keywords): Elasticsearch|Promise
    {
        $params = [
            'index' => 'reporting_new',
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => array_map(function ($term) {
                            return [
                                'multi_match' => [
                                    'query' => $term,
                                    'fields' => ['*']
                                ]
                            ];
                        }, $keywords),
                        'minimum_should_match' => 1
                    ]
                ],
                "aggs" => [
                    'posts_per_day' => [
                        'date_histogram' => [
                            'field' => "published_at",
                            "format" => "yyyy-MM-dd",
                            'calendar_interval' => 'day',
                        ]
                    ]
                ],
                'size' => 0,
            ]
        ];


        return $this->client->search($params);
    }


    public function generateCsvFile(string $keywords)
    {
        try {
            $words = explode(',', $keywords);
            $response = $this->getDailyKeywordHistogram($words);
            $filePath = 'reports/keyword_histogram_' . now()->format('Y_m_d_H_i_s') . '.csv';

            $fileStream = fopen('php://temp', 'r+');
            fputcsv($fileStream, ['Date', 'Post Count']);
            Log::info($response["aggregations"]["posts_per_day"]["buckets"]);
            if (isset($response['aggregations']['posts_per_day']['buckets'])) {
                foreach ($response['aggregations']['posts_per_day']['buckets'] as $bucket) {
                    fputcsv($fileStream, [$bucket['key_as_string'], $bucket['doc_count']]);
                }
            } else {
                fputcsv($fileStream, ['No data available']);
            }

            rewind($fileStream);
            Storage::disk('public')->put($filePath, stream_get_contents($fileStream));
            fclose($fileStream);

            return $filePath;
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
