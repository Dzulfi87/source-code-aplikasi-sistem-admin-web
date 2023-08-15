<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GeneralResource;
use App\Models\CmsSetting;
use Illuminate\Http\Request;

class GeneralController extends BaseController
{
    public function index(){
        $data = CmsSetting::where('group_setting','General Setting')->get();
        $data = GeneralResource::collection($data);
        return $this->response($data);
    }
}
