<?php

namespace App\Http\Controllers;

use App\Models\AuthModel;
use App\Models\MahasiswaModel;
use Illuminate\Http\Request;
use App\Traits\ResponseApiTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 * @groupDescription API Authentication, Digunakan untuk memanggil fungsi yang berkaitan dengan modul Authentication
 */
class AuthController extends Controller
{
	use ResponseApiTrait;

    /**
     * Register
     * @unauthenticated
     * @bodyParam username string required username. Example: 1462200195
     * @bodyParam email string required email. Example: 1462200195@gmail.com
     * @bodyParam password string required password. Example: 12345678
     * @responseFile 200 response_docs_api/response_login_success.json
     * @responseFile 500 response_docs_api/response_bad_response.json
     * @responseFile 404 response_docs_api/response_login_failed.json
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = AuthModel::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            if ($token) {
                return $this->showSuccess(['data' => ['token' => $token] ]);
            } else {
                return $this->showBadResponse(['error' => 'Token tidak tergenerate']);
            }

        } catch (\Throwable $e) {
            return $this->showBadResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Login
     * @unauthenticated
     * @bodyParam username string required username. Example: 1462200195
     * @bodyParam password string required password. Example: 12345678
     * @responseFile 200 response_docs_api/response_login_success.json
     * @responseFile 500 response_docs_api/response_bad_response.json
     * @responseFile 404 response_docs_api/response_login_failed.json
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            // Cari pengguna berdasarkan username
            $user = AuthModel::where('username', $request->username)->first();

            // Jika pengguna tidak ditemukan, kembalikan pesan Unauthorized
            if (!$user) {
                return $this->showNotFound(['codeMessage' => "loginFalseUser"]);
            }

            // Coba untuk mengautentikasi pengguna
            if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                return $this->showNotFound(['codeMessage' => "loginFalsePassword"]);
            }


            // Jika autentikasi berhasil, buat token dan kembalikan respons berhasil
            $token = $user->createToken('auth_token')->plainTextToken;

            if ($token) {
                $data = [
                    'users' => $user,
                    'token' => $token,
                ];

                return $this->showSuccess(['data' => $data ]);
            } else {
                return $this->showBadResponse(['error' => 'Token tidak tergenerate']);
            }

        } catch (\Throwable $e) {
            return $this->showBadResponse(['error' => $e->getMessage()]);
        }
    }

}
