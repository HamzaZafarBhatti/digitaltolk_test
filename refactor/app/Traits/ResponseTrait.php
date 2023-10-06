<?php

namespace App\Traits;

use Exception;
use Throwable;

trait ResponseTrait
{

    public static function success($data = [], $code = 200, $message = 'success')
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error($data = [], $code = 500, $message = 'error')
    {
        Log::error($message);
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
