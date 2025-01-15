<?php

namespace App\Services\Elasticsearch;


use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;

class ElasticReportService
{
    protected $client;

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

    public function getDailyKeywordHistogram(array $keywords)
    {
        Log::info("Elastic Report Service++++++++++");

        // Use multi_match for all fields with multiple keywords
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
                        }, ['تهران', 'آلودگی']),
                        'minimum_should_match' => 1
                    ]
                ],
                'aggs' => [],
                'size' => 5  // Get a few documents to check
            ]
        ];


// Execute the query
        $response = $this->client->search($params);
        Log::info($response);  // Log the response

        return $response['hits']['hits'];
    }
}
