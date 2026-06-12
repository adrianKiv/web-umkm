<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\KategoriAdminController;
use App\Http\Controllers\Admin\KelompokAdminController;
use App\Http\Controllers\Admin\LokasiAdminController;
use App\Http\Controllers\Admin\MenuAdminController;
use App\Http\Controllers\Admin\RatingAdminController;
use App\Http\Controllers\Admin\UmkmAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\UserActivityAdminController;
use App\Http\Controllers\DataUmkmController;
use App\Http\Controllers\PublicUmkmSubmissionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DataUmkmController::class, 'landing'])->name('landing');
Route::get('/landing', [DataUmkmController::class, 'landing'])->name('landing.page');
Route::post('/landing/preference', [DataUmkmController::class, 'storePreference'])->name('landing.preference.store');
Route::get('/umkm/{umkm}/detail', [DataUmkmController::class, 'detail'])->name('umkm.detail');
Route::post('/umkm/{umkm}/track-activity', [DataUmkmController::class, 'trackActivity'])->name('umkm.track');
Route::get('/map', [DataUmkmController::class, 'map'])->name('data-umkm.map');
Route::post('/rating', [DataUmkmController::class, 'storeRating'])->name('rating.store');
Route::get('/data-umkm', [DataUmkmController::class, 'index'])->name('data-umkm.index');
Route::post('/umkm-submissions', [PublicUmkmSubmissionController::class, 'store'])->name('umkm-submissions.store');
Route::post('/menu-submissions', [PublicUmkmSubmissionController::class, 'storeMenu'])->name('menu-submissions.store');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user && ($user->isAdmin() || $user->isSuperAdmin())) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('landing');
})->middleware('auth')->name('dashboard');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin,super_admin'])
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

        Route::get('activities', [UserActivityAdminController::class, 'index'])
            ->name('activities.index');

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
