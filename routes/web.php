<?php

use App\Http\Controllers\ArtifactController;
use App\Http\Controllers\ArtifactDownloadController;
use App\Http\Controllers\ArtifactLabelController;
use App\Http\Controllers\DatasetExportController;
use App\Http\Controllers\DatasetExportDownloadController;
use App\Http\Controllers\ProjectController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
        Route::get('projects/{project}/artifacts/{artifact}/download', [ArtifactDownloadController::class, 'redirect'])->name('artifacts.download');
        Route::get('artifacts/{artifact}/signed-download', [ArtifactDownloadController::class, 'download'])->middleware('signed')->name('artifacts.download.signed');
        Route::post('projects/{project}/artifacts/{artifact}/labels', [ArtifactLabelController::class, 'store'])->name('artifact-labels.store');
        Route::post('projects/{project}/artifacts', [ArtifactController::class, 'store'])->name('artifacts.store');
        Route::get('projects/{project}/exports/{export}/download', [DatasetExportDownloadController::class, 'redirect'])->name('exports.download');
        Route::get('exports/{export}/signed-download', [DatasetExportDownloadController::class, 'download'])->middleware('signed')->name('exports.download.signed');
        Route::post('projects/{project}/exports', [DatasetExportController::class, 'store'])->name('exports.store');
        Route::resource('projects', ProjectController::class)->only(['index', 'create', 'store', 'show']);
    });

Route::middleware(['auth'])->group(function () {
    Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('invitations.accept');
});

require __DIR__.'/settings.php';
