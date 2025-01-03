<?php

namespace App\Models;   

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;  

class Article extends Model
{
    use HasFactory; 
    protected $fillable = ['image', 'title', 'content', 'status'];  
}

// 批量賦值範例 : 
// Article::create([
//     'image' => 'path/to/image.jpg',
//     'title' => '文章標題',
//     'content' => '文章內容',
//     'status' => 'published',
//     'order' => 1,
// ]);
// 定義在$fillable的欄位可被 create 或 update 批量更改，其他欄位不被影響，防止批量賦值漏洞的安全機制