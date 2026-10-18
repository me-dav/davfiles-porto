<?php

// app/Support/ApiResponse.php
namespace App\Support;

trait ApiResponse
{
    protected function ok($data = null, string $msg = 'OK', int $code = 200)
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $msg], $code);
    }

    protected function fail(string $err, int $code = 400)
    {
        return response()->json(['success' => false, 'error' => $err, 'statusCode' => $code], $code);
    }
}