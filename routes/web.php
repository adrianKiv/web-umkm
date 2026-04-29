<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\KategoriAdminController;
use App\Http\Controllers\Admin\KelompokAdminController;
use App\Http\Controllers\Admin\LokasiAdminController;
use App\Http\Controllers\Admin\MenuAdminController;
use App\Http\Controllers\Admin\RatingAdminController;
use App\Http\Controllers\Admin\UmkmAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\DataUmkmController;
use App\Http\Controllers\PublicUmkmSubmissionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DataUmkmController::class, 'landing'])->name('landing');
Route::get('/landing', [DataUmkmController::class, 'landing'])->name('landing.page');
Route::get('/map', [DataUmkmController::class, 'map'])->name('data-umkm.map');
Route::post('/rating', [DataUmkmController::class, 'storeRating'])->name('rating.store');
Route::get('/data-umkm', [DataUmkmController::class, 'index'])->name('data-umkm.index');
Route::post('/umkm-submissions', [PublicUmkmSubmissionController::class, 'store'])->name('umkm-submissions.store');
Route::post('/menu-submissions', [PublicUmkmSubmissionController::class, 'storeMenu'])->name('menu-submissions.store');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('dashboard');

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('umkm', UmkmAdminController::class);
        Route::resource('menu', MenuAdminController::class);
        Route::resource('kategori', KategoriAdminController::class);
        Route::resource('kelompok', KelompokAdminController::class);
        Route::resource('lokasi', LokasiAdminController::class);
        Route::resource('user', UserAdminController::class);
        Route::resource('rating', RatingAdminController::class)
            ->only(['index', 'show', 'destroy']);

        Route::patch('submissions/{submission}/approve', [AdminDashboardController::class, 'approveSubmission'])
            ->name('submissions.approve');
        Route::patch('submissions/{submission}/reject', [AdminDashboardController::class, 'rejectSubmission'])
            ->name('submissions.reject');

        Route::patch('menu-submissions/{menuSubmission}/approve', [AdminDashboardController::class, 'approveMenuSubmission'])
            ->name('menu-submissions.approve');
        Route::patch('menu-submissions/{menuSubmission}/reject', [AdminDashboardController::class, 'rejectMenuSubmission'])
            ->name('menu-submissions.reject');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
