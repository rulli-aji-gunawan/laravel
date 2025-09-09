<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/check-auth', function () {
    return response()->json(['authenticated' => true]);
});

// API untuk mendapatkan years berdasarkan model
Route::get('/years/{model}', function ($model) {
    try {
        $years = DB::table('model_items')
            ->where('model_code', $model)
            ->distinct()
            ->pluck('model_year')
            ->toArray();
        
        return response()->json($years);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// API untuk mendapatkan items berdasarkan model
Route::get('/items/{model}', function ($model) {
    try {
        $items = DB::table('model_items')
            ->where('model_code', $model)
            ->select('id', 'model_code', 'model_year', 'item_name', 'product_picture')
            ->get();
        
        return response()->json($items);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
