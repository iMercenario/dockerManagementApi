<?php

use App\Http\Controllers\TranslationModelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Translation model routes
|--------------------------------------------------------------------------
*/
Route::post('/upload-model', [TranslationModelController::class, 'uploadModel']);
Route::delete('/translation-model/{id}', [TranslationModelController::class, 'deleteModel']);
Route::post('/translation-model/{id}', [TranslationModelController::class, 'updateModel']);
Route::get('/translation-models', [TranslationModelController::class, 'index']);
Route::get('/translation-model/{id}', [TranslationModelController::class, 'show']);
