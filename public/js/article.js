//載入頁面時自動載入文章
document.addEventListener('DOMContentLoaded', () => {
    //以預設排序顯示 
    sortArticle(); 
    document.querySelector('.articleList').addEventListener('click', function(event){
        //編輯按鈕
        if (event.target.classList.contains('btn-custom-edit')){   
            let id = event.target.getAttribute('data-id');  
            let title = event.target.getAttribute('data-title');
            let content = event.target.getAttribute('data-content');
            let image = event.target.getAttribute('data-image');
            openEditDialog(id, title, content, image);
        }

        //刪除按鈕
        if (event.target.classList.contains('btn-custom-del')){
            let id = event.target.getAttribute('data-id');
            deleteArticle(id);
        }

        //啟用 & 停用按鈕
        if (event.target.classList.contains('btn-custom-active')){
            let id = event.target.getAttribute('data-id');
            let status = event.target.getAttribute('data-status');
            changeStatus(id, status);
        }
    });
});

//編輯文章的彈跳視窗
function openEditDialog(id, title, content, image){
    // console.log(image);
    document.getElementById('editId').value = id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editContent').value = content;

    //顯示已有的圖片
    let imgPreview = document.getElementById('previewImage');
    imgPreview.style.display = 'block';
    imgPreview.src = image; 

    let editDialog = new bootstrap.Modal(document.getElementById('editArticleDialog'));    //new bootstrap.Modal()創建彈跳視窗
    editDialog.show();
}

//刪除按鈕
function deleteArticle(id){
    showConfirmDialog('確定要刪除這篇文章嗎？', () => {
        fetch(`/api/articles/${id}`, {
            method: 'DELETE',
        })
        .then(response => response.json())
        .then(data => {
            showNormalDialog(data.message, () => location.reload());
        });
    });
    // if (confirm('確定要刪除這篇文章嗎？')){
    //     fetch(`/api/articles/${id}`,{
    //         method: 'DELETE',
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //         // alert(data.message);
    //         // location.reload(); 
    //         showNormalDialog(data.message, () => location.reload());
    //     });
    // }
}

//啟用 & 停用按鈕
function changeStatus(id){
    fetch(`/api/articles/change-status/${id}`,{
        method: 'PATCH',
    })
    .then(response => response.json())
    .then(data => {
        // alert(data.message);
        // location.reload(); 
        showNormalDialog(data.message, () => location.reload());
    });
}

//新增文章
async function addArticle(event){
    event.preventDefault();

    let form = document.getElementById('addArticleForm');
    let formData = new FormData(form);

    // 檢查表單是否為空
    let image = formData.get('image');
    // console.log(image);
    let title = formData.get('title').trim();
    let content = formData.get('content').trim();
    if (!image.name || !title || !content) {
        //alert('請選擇圖片與填入各項資訊，再儲存哦');
        showNormalDialog('請選擇圖片與填入各項資訊，再儲存哦');
        return;
    }

     let response = await fetch('/api/articles',{
        method: 'POST',
        body: formData
    });
    if (!response.ok) {
        let errorData = await response.json();
        console.log('新增文章失敗:' + JSON.stringify(errorData));
    }

    let result = await response.json();
    //alert(result.message); 
    form.reset();
    showNormalDialog(result.message, () => location.reload());
    //location.reload();
}


//編輯文章
async function updateArticle(event){
    event.preventDefault(); 
    
    let editForm = document.getElementById('editArticleForm');
    let formData = new FormData(editForm);
    
    // 檢查表單是否為空
    let title = formData.get('title').trim();
    let content = formData.get('content').trim();
    if (!title || !content){
        // alert('請填入各項資訊，再儲存哦'); 
        showNormalDialog('請填入各項資訊，再儲存哦');
        return;
    }

    let id = document.getElementById('editId').value;
    let response = await fetch(`/api/articles/${id}`,{
        method: 'POST', 
        body: formData,
    });

    if (!response.ok){
        let errorData = await response.json(); 
        console.log('編輯文章失敗:' + JSON.stringify(errorData));
    }

    let result = await response.json();
    // alert(result.message); 
    // location.reload(); 
    showNormalDialog(result.message, () => location.reload());
}


//搜尋框，有加防抖 
let debounceTime;       //前一次的計時器
function searchArticle(){
    let searchInput = document.getElementById('searchInput').value.trim();     //搜尋框
    let sortOption = document.getElementById('sortOption').value;              //排序選擇

    // 清除前一次計時器
    clearTimeout(debounceTime);
    debounceTime = setTimeout(() => {
        fetch(`/api/articles/search?searchInput=${searchInput}`)
            .then(response => response.json())
            .then(data => {
                if ( !searchInput.trim() ){ 
                    // console.log("搜尋為空");
                    sortArticle();      //重新排序
                }else{
                    //搜尋框有文字，結合Sort By選項去排序 
                    let sortedData = sortArticleBySearch(data, sortOption);
                    updateArticleList(sortedData);
                }
            });
    }, 400); 
}

// 搜尋框有文字，結合Sort By選項去排序  
function sortArticleBySearch(articles, sortOption){
    // console.log(articles);
    return articles.sort((a, b) => {
        // 排序，先 status 1，隨後 status 0
        if (a.status !== b.status) {
            return b.status - a.status; 
        }
        if (sortOption === 'asc') {
            return new Date(a.created_at) - new Date(b.created_at); 
        } else {
            return new Date(b.created_at) - new Date(a.created_at); 
        }
    });
}

//依照搜尋框有無文字的情況，去重新排序文章 
function sortArticle(){
    let searchInput = document.getElementById('searchInput').value.trim();      //搜尋框
    let sortOption = document.getElementById('sortOption').value;               //排序選擇

    if (searchInput){
        fetch(`/api/articles/search?searchInput=${searchInput}`)
            .then(response => response.json())
            .then(data => {
                //搜尋框有文字，結合 Sort By選項去排序 
                let sortedData = sortArticleBySearch(data, sortOption);
                // console.log(sortedData);
                updateArticleList(sortedData);
            });
    } else {
        //搜尋框無文字，按照 Sort By對所有文章排序
        fetch(`/api/articles/sort?order=${sortOption}`)
            .then(response => response.json())
            .then(data => {
                updateArticleList(data);
            });
    }
}

//動態更新文章
function updateArticleList(articles){
    let articlesHtml = '';
    if (!articles || articles.length === 0) {
        articlesHtml = '<p>目前沒有相關的文章哦</p>';
    } else {
        articles.forEach(item => {
            articlesHtml = articlesHtml + 
            `<div class="col-12 mb-4">
                <div class="card ${item.status ? 'atActive' : 'notActive'}">
                    <img src="${item.image}" class="card-img-top" alt="Image">
                    <div class="card-body">
                        <p class="card-title fw-bolder fs-5">${item.title}</p>
                        <p class="card-text">${item.content}</p>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-custom-edit me-2" data-id="${item.id}" data-title="${item.title}" data-content="${item.content}" data-image="${item.image}">Edit</button>
                            <button class="btn btn-custom-del me-2" data-id="${item.id}">Delete</button>
                            <button class="btn btn-custom-active" data-id="${item.id}" data-status="${item.status}">${item.status ? 'Inactive' : 'Active'}</button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
    }

    document.querySelector('.articleList').innerHTML = articlesHtml;
}

//一般提示彈跳視窗
function showNormalDialog(message, reload){
    let modal = new bootstrap.Modal(document.getElementById('normalDialog'));  
    let modalBody = document.querySelector('#normalDialog .modal-body');
    let modalBtn = document.querySelector('#normalDialog .btn-confirm');
    modalBody.textContent = message;
    modal.show();
    modalBtn.onclick = () => {
        if (reload) reload();
        modal.hide();
    };
}

//刪除彈跳視窗 
function showConfirmDialog(message, onConfirm) {
    let modal = new bootstrap.Modal(document.getElementById('confirmDialog'));
    let modalBody = document.querySelector('#confirmDialog .modal-body');
    let confirmBtn = document.querySelector('#confirmDialog .btn-confirm');
    modalBody.textContent = message;
    modal.show();
    confirmBtn.onclick = () => {
        onConfirm(); // 執行確認後的操作
        modal.hide();
    };
}