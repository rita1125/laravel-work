<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\LandingController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('articles')->group(function () {
    Route::get('/search', [ArticleController::class, 'search']);                     // 搜尋   　　
    Route::get('/sort', [ArticleController::class, 'sort']);                         // 排序   　
    Route::patch('/change-status/{id}', [ArticleController::class, 'changeStatus']); // 啟用 &停用  
    Route::post('/', [ArticleController::class, 'store']);                           // 新增  
    Route::post('/{id}', [ArticleController::class, 'update']);                      // 更新
    Route::delete('/{id}', [ArticleController::class, 'destroy']);                   // 刪除  
    // Route::put('/{id}', [ArticleController::class, 'update']);                    // 更新

});
