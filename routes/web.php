<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortNameController;
use App\Http\Controllers\PortCodeController;
use App\Http\Controllers\ScheduleController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/search', function () {
    return view('search');
});
Route::get('/oneline-results', function () {
    return view('oneline-results');
});
Route::get('/ports', [PortNameController::class, 'searchPortNames']);
Route::get('/get-port-code', [PortCodeController::class, 'getPortCode'])->name('get-port-code');
Route::get('/fetch-schedule', [ScheduleController::class, 'fetchSchedule']);
