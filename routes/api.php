<?php

use App\Http\Controllers\ARIMByVesselController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MstBusinessUnitController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('health', function () {
	return response()->json([
		'status' => 'ok',
		'timestamp' => now()->toDateTimeString(),
	], 200);
});
Route::post('login', [LoginController::class, 'login']);
// Simple healthcheck (unauthenticated)

// Protected API routes using token-based auth (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
	Route::post('logout', [LoginController::class, 'logout']);
	Route::get('me', [LoginController::class, 'me']);
});

//Analytical Report Incoming Material by Vessel

Route::middleware('auth:sanctum')->group(function () {
	// business unit master
	Route::resource('bunit', MstBusinessUnitController::class);

	// analytical report incoming by vessel
	Route::post('arimvess', [ARIMByVesselController::class, 'create']);
	Route::put('arimvess', [ARIMByVesselController::class, 'update']);
	Route::get('arimvess', [ARIMByVesselController::class, 'get']);
	Route::delete('arimvess/{id}', [ARIMByVesselController::class, 'destroy']);
});
