<?php

namespace App\Traits;

trait ApiResponses
{
    protected function apiResponse($data, $code = 500)
    {
        $response = match ($code) {
            200 => [
                'data' => $data,
            ],
            204 => [
                'message' => $data ?? 'Success with No Content'
            ],
            401 => [
                'message' => $data ?? 'Unauthorized'
            ],
            403 => [
                'message' => $data ?? 'Forbidden'
            ],
            404 => [
                'message' => $data ?? 'Not found'
            ],
            500 => [
                'message' => $data ?? 'Internal server error'
            ],
            default => [
                'data' => $data,
                'message' => 'Unknown error',
            ],
        };

        return response()->json($response, $code);
    }
}