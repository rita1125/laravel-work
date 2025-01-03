<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; //用於檢查用戶是否有權限執行某些操作
//分發（dispatch） Laravel 的任務（jobs），將一些需要延遲執行或在後台執行的邏輯，交給 Laravel 的 隊列系統（Queue） 處理，從而減少應用程式的響應時間，提升效能
//發送電子郵件、處理大型文件 例如圖片上傳後的壓縮或轉換或生成報表、同步外部服務，將數據發送到外部 API，但不需要立即等待回應、定時任務 配合 Laravel 的排程系統，讓某些任務按照時間執行（延遲或排程）
use Illuminate\Foundation\Bus\DispatchesJobs;           
use Illuminate\Foundation\Validation\ValidatesRequests;   //驗證請求數據的功能，常用於控制器中快速驗證
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
