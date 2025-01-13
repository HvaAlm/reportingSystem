<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class HttpRequestHelper
{

    /**
     * @param string $url
     * @param array $headers
     * @param string $data
     * @return array
     */
    public static function post(string $url, string $data = '', array $headers = []): array
    {
        try {
            $response = Http::withHeaders($headers)->withBody($data)
                ->post($url);
            return self::formatResponse($response);
        } catch (\Exception $exception) {
            return [
                'status' => 500,
                'error' => $exception->getMessage(),
                'successful' => false
            ];
        }

    }

    /**
     * @param string $url
     * @param array $headers
     * @return array
     */
    public static function get(string $url, array $headers = []): array
    {
        try {
            $response = Http::withHeaders($headers)->get($url);
            return self::formatResponse(json_decode($response));
        } catch (\Exception $exception) {
            return [
                'status' => 500,
                'error' => $exception->getMessage(),
                'successful' => false
            ];
        }
    }

    /**
     * @param $response
     * @return array
     */
    private static function formatResponse($response): array
    {
        return [
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
            'successful' => $response->successful(),
            'error' => $response->failed() ? $response->body() : null,
        ];
    }
}
