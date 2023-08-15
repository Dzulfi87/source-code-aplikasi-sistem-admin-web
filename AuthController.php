<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\CmsUser;
use App\Models\Mitra;
use App\Models\Pelanggan;
use App\Models\Pengemudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'telepon' => 'required|string',
            'password' => 'required|string',
            'playerId' => 'required|string',
        ]);
        $credentials = $request->only('telepon', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        CmsUser::find($user->id)->update(['playerId' => $request->playerId]);

        return response()->json([
            'status' => 'success',
            'message' => 'ok',
            'data' => new UserResource($user),
            'authorization' => [
                'type' => 'bearer',
                'token' => $token,
            ]
        ]);

    }

    public function register(Request $request){
        $request->validate([
            'nama' => 'required|string',
            'telepon' => 'required|string|max:255|unique:cms_users,telepon',
            'password' => 'required|string',
            'is_pengemudi' => 'required|integer'
        ]);

        $privilege = $request->is_pengemudi ? 4 : 3;

        $user = CmsUser::create([
            'name' => $request->nama,
            'telepon' => $request->telepon,
            'password' => Hash::make($request->password),
            'photo' => 'uploads/1/2023-05/group_33300.png',
            'id_cms_privileges' => $privilege,
            'status' => 'Active',
            'is_pengemudi' => $request->is_pengemudi
        ]);

        if ($request->is_pengemudi){
            Pengemudi::create([
                'kode' => initialWord($request->nama),
                'nama' => $request->nama,
                'telepon' => $request->telepon,
                'is_aktif' => 1
            ]);
        }else{
            Pelanggan::create([
                'kode' => initialWord($request->nama),
                'nama' => $request->nama,
                'telepon' => $request->telepon
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'ok',
            'data' => new UserResource(Auth::user()),
            'authorisation' => [
                'type' => 'bearer',
                'token' => Auth::refresh(),
            ]
        ]);
    }
}
