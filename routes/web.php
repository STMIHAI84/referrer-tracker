<?php

use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;

Route::get('/', function () {
    return redirect('landing');
});

Route::get('/landing', [LandingController::class, 'show'])->name('landing');

Route::prefix('admin')->group(function () {
    Route::get('/referrers', [ReferralController::class, 'index'])->name('admin.referrers');
    Route::get('/referrers/export', [ReferralController::class, 'export'])->name('admin.referrers.export');
});

// Rute pentru generat link-uri de test
Route::get('/generate-links', function () {
    $baseUrl = url('/landing');
    $links = [
        'Facebook' => $baseUrl . '?utm_source=facebook&utm_medium=social&utm_campaign=test',
        'Instagram' => $baseUrl . '?utm_source=instagram&utm_medium=social&utm_campaign=test',
        'WhatsApp' => $baseUrl . '?utm_source=whatsapp&utm_medium=messaging&utm_campaign=test',
        'Twitter' => $baseUrl . '?utm_source=twitter&utm_medium=social&utm_campaign=test',
        'Organic' => $baseUrl . '?ref=organic',
    ];

    return view('link-generator', compact('links'));
})->name('generate.links');

Route::get('/test-page-1', function () {
    return view('test-page', ['page' => 1]);
})->name('test.page1');

Route::get('/test-page-2', function () {
    return view('test-page', ['page' => 2]);
})->name('test.page2');

Route::get('/test-page-{page}', function ($page) {
    return view('test-page', ['page' => $page]);
})->where('page', '[0-9]+')->name('test.page');
