<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()   //執行遷移  當執行 php artisan migrate 時創建資料表，並定義資料表的結構
    {
        Schema::create('articles', function (Blueprint $table) {   
            $table->id();
            $table->string('image');  
            $table->string('title');  
            $table->text('content'); 
            $table->boolean('status')->default(true);  //用於標記文章是否啟用，true啟用，false停用
            $table->integer('order')->default(0);      //後來沒用到
            $table->timestamps();                       //自動建立 created_at 和 updated_at 兩個欄位
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()  
    {
        Schema::dropIfExists('articles');
    }
}
