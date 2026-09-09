<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DirectoryController,AuthController,SubmissionController,AdminController};
Route::get('/',[DirectoryController::class,'home'])->name('home');
Route::get('/explore',[DirectoryController::class,'explore'])->name('explore');
Route::get('/places/{slug}',[DirectoryController::class,'show'])->name('listing');
Route::view('/plan','plan');
Route::view('/saved','saved');
Route::view('/about','about');
Route::view('/privacy','privacy');
Route::get('/sitemap.xml',[DirectoryController::class,'sitemap']);
Route::get('/robots.txt',fn()=>response("User-agent: *\nAllow: /\nDisallow: /dashboard\nDisallow: /admin\nDisallow: /submit\nDisallow: /saved\nSitemap: https://".app(App\Models\City::class)->domain."/sitemap.xml\n")->header('Content-Type','text/plain'));
Route::middleware('guest')->group(function(){Route::view('/login','auth',['register'=>false])->name('login');Route::view('/register','auth',['register'=>true]);Route::post('/login',[AuthController::class,'login'])->middleware('throttle:auth');Route::post('/register',[AuthController::class,'register'])->middleware('throttle:auth');});
Route::middleware('auth')->group(function(){Route::post('/logout',[AuthController::class,'logout']);Route::post('/password',[AuthController::class,'password'])->middleware('throttle:auth');Route::get('/dashboard',[SubmissionController::class,'dashboard']);Route::get('/submit/{slug?}',[SubmissionController::class,'form']);Route::post('/submit',[SubmissionController::class,'store'])->middleware('throttle:submissions');});
Route::middleware(['auth','admin'])->group(function(){Route::get('/admin',[AdminController::class,'index']);Route::post('/admin/review/{id}',[AdminController::class,'review']);Route::post('/admin/listings/{id}/visibility',[AdminController::class,'archive']);});
