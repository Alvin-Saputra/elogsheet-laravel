<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use App\Models\MBusinessUnit;
use App\Models\MPlant;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $businessUnits = MBusinessUnit::all();
        $plants = MPlant::all();

        return view('auth.login', [
            'businessUnits' => $businessUnits,
            'plants' => $plants,
        ]);
    }


    // public function login(Request $request)
    // {
    //     // Validasi input
    //     $request->validate([
    //         'username' => 'required|string',
    //         'password' => 'required|string',
    //         'business_unit' => 'required|string',
    //         'plant' => 'required|string',
    //     ]);

    //     $credentials = $request->only('username', 'password');

    //     if (Auth::attempt($credentials)) {

    //         // Ambil data business unit
    //         $bu = MBusinessUnit::where('bu_code', $request->business_unit)->first();

    //         // Ambil data plant
    //         $pl = MPlant::where('plant_code', $request->plant)->first();

    //         // Simpan ke session
    //         session()->put([
    //             'business_unit_code' => $bu->bu_code,
    //             'business_unit_name' => $bu->bu_name ?? '-',
    //             'plant_name' => $pl->plant_name ?? '-',
    //             'plant_code' => $pl->plant_code ?? '-',
    //         ]);

    //         return redirect()->route('dashboard')
    //             ->with('success', 'Login berhasil! Selamat datang.');
    //     }

    //     return back()->with('error', 'Username atau Password salah.');
    // }

    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'business_unit' => 'required|string',
            'plant' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        // Coba login normal (hash bcrypt)
        if (Auth::attempt($credentials)) {
            return $this->afterLoginSuccess($request);
        }

        // Fallback: cek manual plain password
        $user = \App\Models\MUser::where('username', $request->username)
            ->where('password', $request->password) // plain text check
            ->first();
        // var_dump($user); exit;
        if ($user) {
            Auth::login($user);
            return $this->afterLoginSuccess($request);
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau Password salah.'
            ], 401);
        }

        return back()->with('error', 'Username atau Password salah.');
    }

    private function afterLoginSuccess(Request $request)
    {
        // Business unit & plant (sudah ada)
        $bu = \App\Models\MBusinessUnit::where('bu_code', $request->business_unit)->first();
        $pl = \App\Models\MPlant::where('plant_code', $request->plant)->first();

        session()->put([
            'business_unit_code' => $bu->bu_code ?? '-',
            'business_unit_name' => $bu->bu_name ?? '-',
            'plant_code' => $pl->plant_code ?? '-',
            'plant_name' => $pl->plant_name ?? '-',
        ]);

        // === Ambil menu sesuai role ===
        // $menus = \App\Models\MMenu::where('isactive', 'T')
        //     ->whereNull('parent_id')
        //     ->whereHas('roleMenus', function ($query) {
        //         $query->where('role_code', auth()->user()->roles);
        //     })
        //     ->get();
        $menus = \App\Models\MMenu::whereHas('roleMenus', function ($q) {
            $q->where('role_code', auth()->user()->roles);
        })
            ->where('isactive', 'T')
            ->whereNull('parent_id')
            ->with([
                'children' => function ($q) {
                    $q->where('isactive', 'T')
                        ->whereHas('roleMenus', function ($r) {
                            $r->where('role_code', auth()->user()->roles);
                        });
                }
            ])
            ->orderBy('sort_order')
            ->get();





        session()->put('menus', $menus);

        // If this is an API / JSON request, return JSON (and issue a token)
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            // Issue a personal access token for API clients if the user model supports it
            $user = Auth::user();
            $token = null;
             $token = $request->user()->createToken('api-token')->plainTextToken;

            $payload = [
                'success' => true,
                'message' => 'Login berhasil! Selamat datang.',
                'user' => $user,
                'business_unit' => [
                    'code' => $bu->bu_code ?? '-',
                    'name' => $bu->bu_name ?? '-',
                ],
                'plant' => [
                    'code' => $pl->plant_code ?? '-',
                    'name' => $pl->plant_name ?? '-',
                ],
                'menus' => $menus,
            ];

            if ($token) {
                $payload['token'] = $token;
                $payload['token_type'] = 'Bearer';
            }

            return response()->json($payload, 200);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Login berhasil! Selamat datang.');
    }



    public function logout(Request $request)
    {
        // If the user is authenticated via token, revoke the current access token
        $user = $request->user();
        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            // revoke current token
            $user->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout.'
            ], 200);
        }

        // Otherwise, fallback to session logout for web
        Auth::logout();
        $request->session()->flush(); // hapus semua session
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout.'
            ], 200);
        }

        return redirect()->route('login')->with('success', 'Berhasil logout.');
    }

    /**
     * Return authenticated user info for API clients.
     */
    public function me(Request $request)
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            return response()->json([
                'success' => true,
                'user' => $request->user(),
            ], 200);
        }

        return redirect()->route('dashboard');
    }
}
