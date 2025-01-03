<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;  //驗證例外，處理驗證失敗的錯誤，$request->validate()

class ArticleController extends Controller
{
    //新增文章
    public function store(Request $request)
    {   // 記錄資訊到 storage/logs
        //Log::info($request->all());
        
        //驗證表單資料
        try{
            $testdata = $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',  //圖片大小2MB
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);
        }catch(ValidationException $e){
            return response()->json([
                'message' => '驗證失敗',
                'errors' => $e->errors(),
            ], 422);
        }

        //新增文章
        try{
            //圖片存到 storage/app/public/articles資料夾
            $path = $request->file('image')->store('public/articles');

            //取圖片URL存到 DB
            $testdata['image'] = Storage::url($path);
            Article::create($testdata);                                     //Eloquent的 create方法在資料表Article新增記錄

            return response()->json(['message' => '新增文章成功!'], 201);    //201用於新增數據
            
        }catch(\Exception $e){                                                     
            return response()->json([
                'message' => '新增文章失敗', 'error' => $e->getMessage()     //getMessage() PHP方法
            ], 500); 
        }
    }

    //更新文章
    public function update(Request $request, $id)
    {   
        //驗證表單資料
        try{
            $testdata = $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',   //nullable可選的
                'title' => 'required|string|max:255',                        
                'content' => 'required|string',
                ]);
        }catch(ValidationException $e){
            return response()->json([
                'message' => '驗證失敗',
                'errors' => $e->errors(),
            ], 422);
        }

        //更新文章
        try {    
            //更新文章資料
            $article = Article::findOrFail($id);            //Eloquent的findOrFail方法找文章
    
            if ($request->hasFile('image')){
                //刪除舊圖片
                if ($article->image && Storage::exists(str_replace('/storage/', 'public/', $article->image))) {   //Laravel上傳的檔案存 storage資料夾(/storage/articles/img.jpg)，實際伺服器路徑是 public/articles/img.jpg
                    Storage::delete(str_replace('/storage/', 'public/', $article->image));
                }
    
                //儲存新圖片到 storage/app/public/articles資料夾
                $path = $request->file('image')->store('public/articles');   
                $testdata['image'] = Storage::url($path);

                //取圖片URL存紀錄storage/logs
                // $imageUrl = Storage::url($path);
                // Log::info('圖片 URL: ' . $imageUrl);
            }
    
            $article->update($testdata);
            return response()->json(['message' => '文章編輯成功!'], 200); 

        }catch (\Exception $e) {                   
            return response()->json(['message' => '編輯文章失敗', 'error' => $e->getMessage()], 500);  
        }
    }
 
    //刪除文章
    public function destroy($id)
    { 
        Article::findOrFail($id)->delete();  // Eloquent提供的 findOrFail方法，查詢的資料在不在
        return response()->json(['message' => '刪除成功!']);
    }
 
    //搜尋文章
    public function search(Request $request)
    {
        $keyword = $request->input('searchInput','');

        $articles = Article::query()
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%$keyword%");
            })
            ->orderBy('status', 'desc')     // 先 status 1 啟用的文章，隨後 status 0 停用的文章
            ->orderBy('created_at', 'desc') // 預設先 Newest First，隨後 Oldest First
            ->get();

        return response()->json($articles);
    }
     
    //以時間倒序，最新的文章在前
    public function sort(Request $request)
    {
        $order = $request->input('order', 'desc');

        $articles = Article::query()
            ->orderBy('status', 'desc')                   //先 status 1 啟用的文章，隨後 status 0 停用的文章
            ->when($order === 'asc', function ($query){  //再以 sort by值排序
                $query->orderBy('created_at', 'asc');
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->get();

        return response()->json($articles);
    }

    //啟用或停用文章，暫時設定停用文章的背景、文字、按鈕都會變灰色，並且排序於頁面底部
    public function changeStatus($id)
    {
        $article = Article::findOrFail($id);
        $article->status = !$article->status;
        $article->save();
        return response()->json(['message' => '文章狀態更新成功!']);
    }

    public function index()
    {
        // 從資料庫取所有文章，預設排序 : 從最新建立的文章到最早建立文章
        // $articles = \App\Models\Article::all();
        $articles = Article::query()
        ->orderBy('created_at', 'desc')
        ->get();

        return view('article', compact('articles'));  //讓 article.blade可以用 $articles 
    }
}
