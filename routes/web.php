<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FileController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

/*
    http://127.0.0.1:8000/post/create
*/

Route::get('/secure-image/{path}', [FileController::class, 'secureImage'])->name('secure-image');
Route::get('/secure-pdf/{path}', [FileController::class, 'securePdf'])->name('secure-pdf');
Route::get('/secure-file/{path}', [FileController::class, 'secureFile'])->name('secure-file');

Route::resource('/post', PostController::class);
Route::get('/post-delete/{id}', [PostController::class, 'delete'])->name('post.delete');

// Route::middleware(['is_admin'])->group(function () {  // is  working true no pass val

Route::middleware(['is_admin:admin|user'])->group(function () { // is working true only multiple
 
    Route::get('/post-list', [PostController::class, 'list']);
    Route::post('/list-ajax-load', [PostController::class, 'ajaxPostList'])->name('ajaxPostList');
});

Route::post('/ajax-delete-al', [PostController::class, 'ajaxPost'])->name('ajax-delete-all');