<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScanController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/scankode', [ScanController::class, 'scanKode'])->name('scan.kode');
Route::post('/scan', [ScanController::class, 'processScan'])->name('scan.process');

Route::get('/scan-data-produk', fn() => view('scandataproduk'));
Route::post('/scan-produk', [ScanController::class, 'processScanProduk']);

