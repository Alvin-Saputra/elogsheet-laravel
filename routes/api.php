<?php

use App\Http\Controllers\Api\AROIPChemicalController;
use App\Http\Controllers\ARIMByTruckController;
use App\Http\Controllers\ARIMByVesselController;
use App\Http\Controllers\AROIPFuelController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\COAController;
use App\Http\Controllers\MstBusinessUnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Analytical Report Incoming Material by Vessel

Route::middleware('auth:sanctum')->group(function () {
    // business unit master
    Route::resource('bunit', MstBusinessUnitController::class);
    // Analytical Report Incoming Material by Vessel
    Route::post('arimvess', [ARIMByVesselController::class, 'create']);
    Route::put('arimvess', [ARIMByVesselController::class, 'update']);
    Route::get('arimvess', [ARIMByVesselController::class, 'get']);
    Route::delete('arimvess/{id}', [ARIMByVesselController::class, 'destroy']);
    Route::put('arimvess/approve-reject', [ARIMByVesselController::class, 'updateApprovalStatusApi']);
});

Route::middleware('auth:sanctum')->group(function () {
    // business unit master
    Route::resource('bunit', MstBusinessUnitController::class);
    // Analytical Report Incoming Material by Vessel
    Route::post('arimvess', [ARIMByVesselController::class, 'create']);
    Route::put('arimvess', [ARIMByVesselController::class, 'update']);
    Route::get('arimvess', [ARIMByVesselController::class, 'get']);
    Route::delete('arimvess/{id}', [ARIMByVesselController::class, 'destroy']);
    Route::put('arimvess/approve-reject', [ARIMByVesselController::class, 'updateApprovalStatusApi']);
});

Route::middleware('auth:sanctum')->group(function () {
    // business unit master
    Route::resource('bunit', MstBusinessUnitController::class);
    // Analytical Report Incoming Material by Vessel
    Route::post('arimtruck', [ARIMByTruckController::class, 'create']);
    Route::get('arimtruck', [ARIMByTruckController::class, 'get']);
    Route::delete('arimtruck/{id}', [ARIMByTruckController::class, 'destroy']);
    Route::put('arimtruck', [ARIMByTruckController::class, 'update']);
    Route::put('arimtruck/approve-reject', [ARIMByTruckController::class, 'updateApprovalStatusApi']);
});

// Analytical Report Incoming Plant Chemical / Ingredient

Route::middleware('auth:sanctum')->group(function () {
    // business unit master
    Route::resource('bunit', MstBusinessUnitController::class);
    // Certificate of Analysis
    // legacy
    Route::post('coa-plant-chemical', [COAController::class, 'create']);
    Route::get('coa-plant-chemical', [COAController::class, 'get']);
    Route::put('coa-plant-chemical/{id}', [COAController::class, 'update']);

    // new single api route
    Route::post('ariopchemical', [AROIPChemicalController::class, 'create']);
    Route::get('ariopchemical', [AROIPChemicalController::class, 'get']);
    Route::put('ariopchemical/{id}', [AROIPChemicalController::class, 'update']);
    Route::delete('ariopchemical/{id}', [AROIPChemicalController::class, 'destroy']);
    Route::post('ariopchemical/{id}/restore', [AROIPChemicalController::class, 'restore']);
    Route::delete('ariopchemical/{id}/force', [AROIPChemicalController::class, 'forceDelete']);
    Route::put('ariopchemical/{id}/approve', [AROIPChemicalController::class, 'updateApproval']);
});

Route::middleware('auth:sanctum')->group(function () {

    // new single api route
    Route::post('aroipfuel', [AROIPFuelController::class, 'create']);
    Route::get('aroipfuel', [AROIPFuelController::class, 'get']);
    Route::delete('aroipfuel/{id}', [AROIPFuelController::class, 'destroy']);
    Route::put('aroipfuel/{id}', [AROIPFuelController::class, 'update']);
    Route::put('aroipfuel/{id}/approve', [AROIPFuelController::class, 'updateApproval']);
});
