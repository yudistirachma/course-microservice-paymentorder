<?php

use Illuminate\Support\Facades\Http;

function createPremiumAccess($data)
{
    $url = env('URL_SERVICE_COURSE').'my-courses/premium';

    try {
        $response = Http::post($url, $data);
        $data = $response->json();
        $data['http_code'] = $response->status();
        
        return $data;
    } catch (\Throwable $th) {
        return [
            'status' => false,
            'message' => $th->getMessage(),
            'http_code' => $th->getCode() ? $th->getCode() : 500
        ];
    }
}