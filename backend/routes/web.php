<?php

use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/admin'));

// ── Invoice print view — protected by admin auth ──────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/invoice/{order}', function (Order $order) {
        $order->loadMissing('items');
        return view('invoice.show', compact('order'));
    })->name('invoice.show');
});
