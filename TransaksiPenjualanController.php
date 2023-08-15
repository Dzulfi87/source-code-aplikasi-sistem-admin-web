<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HistoriResource;
use App\Http\Resources\LacakResource;
use App\Http\Resources\TandaTerimaResource;
use App\Models\Armada;
use App\Models\CmsUser;
use App\Models\Notifikasi;
use App\Models\Pelanggan;
use App\Models\Pengemudi;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Berkayk\OneSignal\OneSignalFacade as OneSignal;

class TransaksiPenjualanController extends BaseController
{
    public function save(Request $request)
    {
        $postdata = $request->all();

        try {
            $armada = Armada::find($postdata['armada_id']);

            if ($request->file('bukti_pengiriman')){
                $postdata['bukti_pengiriman'] = $request->file('bukti_pengiriman')->store('bukti_pengiriman');
            }

            $postdata['faktur'] = 'PP'.date('dmy').rand(100,999);
            $postdata['tanggal'] = now();
            $postdata['antrian'] = $armada->antrian + 1;

            $data = Penjualan::create($postdata);

            if ($data){
                ++$armada->antrian;
                $armada->save();
            }

            $result = new TandaTerimaResource($data);
            return $this->response($result);
        } catch (\Exception $e){
            return $this->response(null, $e->getMessage(), true);
        }
    }

    public function histori($user, $id)
    {
        try {
            $cmsUser = CmsUser::find($id);
            $where = 'pelanggan_id';
            $data = Penjualan::where($where, $id)->orderBy('tanggal','DESC')->get();
            if ($user === "pengemudi"){
                $where = 'pengemudi_id';
                $userData = Pengemudi::firstWhere('telepon', $cmsUser->telepon);
                $data = Penjualan::where($where, $userData->id)->orderBy('tanggal','DESC')->get();
            }

            $result = HistoriResource::collection($data);

            return $this->response($result);
        } catch (\Exception $exception){
            return $this->response(null, $exception->getMessage(), true);
        }
    }

    public function notifikasi($user, $id)
    {
        try {
            $cmsUser = CmsUser::find($id);
            $where = 'pelanggan_id';
            $data = Penjualan::where($where, $id)->orderBy('tanggal','DESC')->get();
            if ($user === "pengemudi"){
                $where = 'pengemudi_id';
                $userData = Pengemudi::firstWhere('telepon', $cmsUser->telepon);
                $data = Penjualan::where($where, $userData->id)->orderBy('tanggal','DESC')->get();
            }

            $result = HistoriResource::collection($data);

            return $this->response($result);
        } catch (\Exception $exception){
            return $this->response(null, $exception->getMessage(), true);
        }
    }

    public function lacak($faktur)
    {
        $data = Penjualan::with(['pengemudi'])->where('faktur', $faktur)->first();
        $result = new LacakResource($data);

        return $this->response($result);
    }

    public function terima($faktur)
    {
        $data = Penjualan::with('pelanggan')->where('faktur', $faktur)->first();
        $data->status = 1;
        $data->tanggal_pengiriman = now();
        $data->save();

        $armada = Armada::find($data->armada_id);
        --$armada->antrian;
        $armada->save();

//        $user = CmsUser::where('telepon',$data->pelanggan->telepon)->first();

//        OneSignal::sendNotificationToUser(
//            "Some Message", $user->playerId
//        );

        return $this->response();
    }

    public function tolak($faktur)
    {
        $data = Penjualan::with(['pelanggan'])->where('faktur', $faktur)->first();
        $data->status = 2;
        $data->save();

        $armada = Armada::find($data->armada_id);
        --$armada->antrian;
        $armada->save();

        if ($data){
//            $notifikasi = Notifikasi::create([
//                'tanggal' => now(),
//                'telepon' => $data->pelanggan->telepon,
//                'faktur' => $data->faktur,
//            ]);

//            $user = CmsUser::where('telepon',$data->pelanggan->telepon)->first();

//            OneSignal::sendNotificationToUser(
//                "Some Message", $user->playerId
//            );

            return $this->response();
        }
        return $this->response(null,'error',true);
    }

    public function selesai(Request $request)
    {
        $foto = $request->foto->store('uploads/bukti_pengiriman');
        $data = Penjualan::with('pelanggan')->where('faktur', $request->faktur)->first();
        $data->bukti_pengiriman = $foto;
        $data->tanggal_selesai = now();
        $data->status = 3;
        $data->save();

//        $armada = Armada::find($data->armada_id);
//        --$armada->antrian;
//        $armada->save();

        $user = CmsUser::where('telepon',$data->pelanggan->telepon)->first();

//        OneSignal::sendNotificationToUser(
//            "Some Message", $user->playerId
//        );

        return $this->response();
    }
}
