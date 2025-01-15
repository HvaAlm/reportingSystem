<?php

namespace Database\Seeders;


use App\Helpers\HttpRequestHelper;
use Illuminate\Database\Seeder;

class ElasticSearchSeeder extends Seeder
{
    const URL = 'http://elasticsearch:9200/_bulk';
    const ElasticSearchIndexName = 'reporting';

    /**
     * @return void
     */
    public function run(): void
    {
        $elasticUsername = env('ELASTIC_USER', 'elastic');
        $elasticPassword = env('ELASTIC_PASS', 'password');
        $elasticSeedFile = env('ELASTIC_SEED_FILE', 'seed_data.json');


        // read json file
        $filePath = base_path() . '/' . $elasticSeedFile;
        $jsonData = json_decode(file_get_contents($filePath), true);

        if (empty($jsonData)) {
            echo "Failed to load elastic search data.\n";
            return;
        }
        // make right format for elastic
        $bulkData = '';
        foreach ($jsonData as $data) {
            $bulkData .= json_encode(["index" => ["_index" => self::ElasticSearchIndexName, "_id" => $data["id"]]]) . "\n";
            $bulkData .= json_encode($data) . "\n";
        }

        $bulkData .= "\n";
        // send data to elastic
        try {
            $response = HttpRequestHelper::post(self::URL,
                $bulkData,
                [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($elasticUsername . ':' . $elasticPassword)
                ]);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            return;
        }

        if (!$response['successful']) {
            echo "Failed to seed Elasticsearch: " . $response['error'] . "\n";
        } else {
            echo "Successfully seeded Elasticsearch.\n";
        }
    }
}
