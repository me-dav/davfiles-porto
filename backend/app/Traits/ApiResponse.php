<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a standardised success envelope.
     *
     * @param  mixed       $data
     * @param  string      $message
     * @param  int         $code     HTTP status code
     */
    protected function success(mixed $data, string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ], $code);
    }

    /**
     * Return a standardised error envelope.
     *
     * @param  string  $message
     * @param  int     $statusCode
     */
    protected function error(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success'    => false,
            'error'      => $message,
            'statusCode' => $statusCode,
        ], $statusCode);
    }
}