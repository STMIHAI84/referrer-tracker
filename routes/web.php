<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;


Route::get('/source', function () {
    return <<<HTML
<!doctype html>
<html lang="ro"><meta charset="utf-8">
<body style="font-family: system-ui; padding:2rem">
  <h1>Pagina sursă (HTTP ➜ HTTP)</h1>
  <p>Dă click mai jos ca să trimiți Referer către /landing.</p>
  <p><a href="/landing">Mergi la landing</a></p>
  <p><a href="/landing?utm_source=facebook">Landing cu UTM (Facebook)</a></p>
  <p><a href="/landing?ref=instagram">Landing cu ref (Instagram)</a></p>
</body></html>
HTML;
});

Route::get('/landing', [LandingController::class, 'show'])->name('landing');

Route::get('/admin/referrers', [AdminReferralController::class, 'index'])
    ->name('admin.referrers')
    ->middleware('throttle:20,1');

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
