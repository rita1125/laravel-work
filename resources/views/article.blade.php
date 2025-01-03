<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>Article Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" type="text/css" href="{{ asset('css/global.css') }}?v=1"> 
   
</head>
<body class="bodyBg">
    <div class="container my-5">
        <!-- 搜尋框、排序、新增文章 -->
        <div class="d-flex justify-content-between mb-4 w-100">
            <div class="d-inline-flex justify-content-start">
                <!-- 搜尋框 -->
                <div class="d-flex align-items-center me-3">
                    <label for="searchInput" class="me-2 searchItem">Search :</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by title" onkeyup="searchArticle()">
                </div>
                <!-- 文章排序按新增時間去區分 -->
                <div class="d-flex align-items-center">
                    <label for="sortOption" class="me-2 sortItem">Sort By :</label>
                    <select id="sortOption" class="form-select" onchange="sortArticle()">
                        <option value="desc" selected>Newest First</option>
                        <option value="asc">Oldest First</option>
                    </select>
                </div>
            </div>
             <!-- 新增文章 -->
            <div class="d-flex justify-content-end">
                <button class="btn btn-custom-1" data-bs-toggle="modal" data-bs-target="#addArticleDialog">+ Add New</button>
            </div>
        </div>
        
        <!-- 文章列表 -->
        <div class="row articleList">
            @forelse($articles as $item)
                <div class="col-12 mb-4">
                    <div class="card">
                        <img src="{{ $item->image }}" class="card-img-top" alt="Image">
                        <div class="card-body">
                            <p class="card-title fw-bolder fs-5">{{ $item->title }}</p>
                            <p class="card-text">{{ $item->content }}</p>

                            <!-- 文章相關按鈕 -->
                            <div class="d-flex justify-content-end">
                                <!-- 編輯 -->
                                <button class="btn btn-custom-edit me-2" onclick="openEditModal({{ $item->id }}, '{{ $item->title }}', '{{ $item->content }}', '{{ $item->image }}')">Edit</button>
                                <!-- 刪除 -->
                                <button class="btn btn-custom-del me-2" onclick="deleteArticle({{ $item->id }})">Delete</button>
                                <!-- 啟用 & 停用 -->
                                <button class="btn btn-custom-active" onclick="changeStatus({{ $item->id }}, {{ $item->status }})">
                                    {{ $item->status ? 'Inactive' : 'Active' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p>目前沒有相關的文章哦</p>
            @endforelse
        </div>
    </div>

    <!-- 新增文章的彈跳視窗 -->
    <div class="modal fade" id="addArticleDialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Article</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"> </button>    {{-- data-bs-dismiss="modal"，表示按下此按鈕，Bootstrap自動觸發關閉當前開啟的彈跳視窗 --}}
                </div>
                <div class="modal-body">
                    <form id="addArticleForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*"> {{-- accept="image/*"表示只接受圖像檔案，例如 .jpg、.png、.gif --}}
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter title">
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="3" placeholder="Enter content"></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success me-2" onclick="addArticle(event)">Save</button>
                            <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>  {{-- data-bs-dismiss="modal"，表示按下此按鈕，Bootstrap自動觸發關閉當前開啟的彈跳視窗 --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 編輯文章的彈跳視窗 -->
    <div class="modal fade" id="editArticleDialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">編輯文章</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>  {{-- data-bs-dismiss="modal"，表示按下此按鈕，Bootstrap自動觸發關閉當前開啟的彈跳視窗 --}}
                </div>
                <div class="modal-body">
                    <form id="editArticleForm">
                        <input type="hidden" id="editId" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="editImage" class="form-label">Image</label>
                            <!-- 顯示已存在資料庫的圖片預覽 -->
                            <img id="previewImage" src="" alt="Current image" class="img-fluid mb-2" style="display: none; max-height: 200px;">
                            <input type="file" class="form-control" id="editImage" name="image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title">
                        </div>
                        <div class="mb-3">
                            <label for="editContent" class="form-label">Content</label>
                            <textarea class="form-control" id="editContent" name="content" rows="3"></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success me-2" onclick="updateArticle(event)">Save</button>
                            <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>  
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 一般提示彈跳視窗 -->
    <div class="modal fade" id="normalDialog">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body fs-5"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom-OK btn-confirm" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 刪除彈跳視窗 -->
    <div class="modal fade" id="confirmDialog">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom-OK btn-confirm">OK</button>
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/article.js') }}?v=3"></script>
</body>
</html>