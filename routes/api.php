<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpedizionisController;
use App\Http\Controllers\SupplyChainController;
use App\Http\Controllers\TransazioniController;
use App\Http\Controllers\WholesalerController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['cors'])->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/checkTrickUrl', [AuthController::class, 'checkTrickUrl']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::get('checkCodice/{codice}', [SupplyChainController::class, 'checkCodice']);
    Route::post('/measures/{type}/{shipment_id}/{sensor_id}', [WholesalerController::class, 'postChartsData']);
    
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('/me', function (Request $request) {
            return auth()->user();
        });
        Route::post('/auth/changePassword', [AuthController::class, 'changePassword']);
        Route::get('/auth/logout', [AuthController::class, 'logout']);

        Route::get('getNetwork', [SupplyChainController::class, 'getNetwork']);
        Route::get('getNetworkStatus/{status}', [SupplyChainController::class, 'getNetworkStatus']);
        Route::get('getNetworkByOwner', [SupplyChainController::class, 'getNetworkByOwner']);
        Route::get('changeFiliereStatus/{id}', [SupplyChainController::class, 'changeFiliereStatus']);
        Route::post('createFiliere', [SupplyChainController::class, 'createFiliere']);

        Route::get('getProdottisFromTrick', [SpedizionisController::class, 'getProdottisFromTrick']);
        Route::get('getProdottis', [SpedizionisController::class, 'getProdottis']);
        Route::post('createProduct', [SpedizionisController::class, 'createProduct']);
        Route::put('updateProduct/{id}', [SpedizionisController::class, 'updateProduct']);
        Route::delete('deleteProduct/{id}', [SpedizionisController::class, 'deleteProduct']);
        Route::post('setManualExpirationDate', [SpedizionisController::class, 'setManualExpirationDate']);
        Route::post('getAIPrediction', [SpedizionisController::class, 'getAIPrediction']);

        Route::get('getBatchesFromTrick', [SpedizionisController::class, 'getBatchesFromTrick']);
        Route::get('getSpedizionis', [SpedizionisController::class, 'getSpedizionis']);
        Route::post('createShipment', [SpedizionisController::class, 'createShipment']);
        Route::delete('deleteShipment/{id}', [SpedizionisController::class, 'deleteShipment']);
        Route::post('assignTransporter', [SpedizionisController::class, 'assignTransporter']);

        Route::get('getTransazionis', [TransazioniController::class, 'getTransazionis']);
        Route::get('acceptTransazioni', [TransazioniController::class, 'acceptTransazioni']);
        Route::get('rejectTransazioni/{id}', [TransazioniController::class, 'rejectTransazioni']);
        Route::put('assignTransazioni/{id}', [TransazioniController::class, 'assignTransazioni']);

        Route::get('getProductsByWholeSaler', [WholesalerController::class, 'getProductsByWholeSaler']);
        Route::get('getTransazioniHistory/{product_id}', [WholesalerController::class, 'getTransazioniHistory']);
        Route::get('getChartsData', [WholesalerController::class, 'getChartsData']);
        Route::get('getDeliveredProductsByWholeSaler', [WholesalerController::class, 'getDeliveredProductsByWholeSaler']);
        Route::post('createTransactionByWholesaler', [WholesalerController::class, 'createTransactionByWholesaler']);
        Route::post('calculateResidualShelfLife', [WholesalerController::class, 'calculateResidualShelfLife']);
    });
});
