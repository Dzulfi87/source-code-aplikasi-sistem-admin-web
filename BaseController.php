<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function response($data = null, $message = 'ok', $error = false)
    {
        return response()->json([
            'error' => $error,
            'message' => $message,
            'data' => $data
        ]);
    }
}
