<?php

use App\Http\Controllers\ShippingRateController;
use Illuminate\Support\Facades\Route;


Route::get('/shipping-rates', [ShippingRateController::class, 'index'])->name('shipping-rates.index');

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'status' => 'ok',
    ]);
});