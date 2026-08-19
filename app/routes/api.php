<?php

use App\Http\Controllers\ShippingRateController;
use Illuminate\Support\Facades\Route;

Route::get('/shipping-rates', [ShippingRateController::class, 'index'])->name('getShippingRates');
