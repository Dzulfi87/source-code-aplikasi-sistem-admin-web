<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArmadaResource;
use App\Models\Armada;
use Illuminate\Http\Request;

class ArmadaController extends BaseController
{
    public function index(Request $request)
    {
        $data = Armada::where('maksimal_berat','>=', $request->eta_berat)->get();
        $data = ArmadaResource::collection($data);
        return $this->response($data);
    }
}
