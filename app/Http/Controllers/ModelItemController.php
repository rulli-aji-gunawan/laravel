<?php

namespace App\Http\Controllers;

use App\Models\ModelItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ModelItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $model_items = ModelItem::query()->limit(100)->get();
        return view('master-data.model-items', [
            'model_items' => $model_items
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'model_code' => ['required'],
            'model_year' => ['required'],
            'item_name' => ['required'],
            'product_picture' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048']
        ]);

        // Simpan data tanpa gambar dulu untuk dapatkan ID
        $model_item = ModelItem::create($request->only('model_code', 'model_year', 'item_name'));

        // Proses upload gambar
        if ($request->hasFile('product_picture')) {
            $ext = $request->file('product_picture')->getClientOriginalExtension();
            $filename = $model_item->id . '.' .
                $model_item->model_code . '.' .
                $model_item->model_year . '.' .
                $model_item->item_name . '.' . $ext;

            $request->file('product_picture')->move(public_path('images/products'), $filename);

            // Simpan nama file ke kolom product_picture
            $model_item->product_picture = $filename;
            $model_item->save();
        }

        return redirect('/master-data/model-items')->with('success', 'New Model and Item added successfully');
    }

    public function getItemsByModel($model)
    {
        $items = ModelItem::where('model_code', $model)
            ->get(['id', 'model_code', 'model_year', 'item_name', 'product_picture']);
        return response()->json($items);
    }

    public function getYearsByModel($model)
    {
        $years = ModelItem::where('model_code', $model)
            ->select('model_year')
            ->distinct()
            ->pluck('model_year');
        return response()->json($years);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ModelItem $model_item)
    {
        return response()->json($model_item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModelItem $model_item)
    {
        $request->validate([
            'model_code' => ['required'],
            'model_year' => ['required'],
            'item_name' => ['required'],
            'product_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048']
        ]);

        // Update data text
        $model_item->model_code = $request->model_code;
        $model_item->model_year = $request->model_year;
        $model_item->item_name = $request->item_name;
        $model_item->product_picture = $request->product_picture;

        // Jika ada file baru, upload dan update nama file
        if ($request->hasFile('product_picture')) {
            $ext = $request->file('product_picture')->getClientOriginalExtension();
            $filename = $model_item->id . '.' .
                $model_item->model_code . '.' .
                $model_item->model_year . '.' .
                $model_item->item_name . '.' . $ext;
            $request->file('product_picture')->move(public_path('images/products'), $filename);
            $model_item->product_picture = $filename;
        }

        $model_item->save();

        return response()->json([
            'message' => 'Model updated successfully',
            'model' => $model_item
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function delete($id)
    {
        try {
            $model_item = ModelItem::findOrFail($id);
            $model_item->delete();
            return response()->json(['success' => true, 'message' => 'Model deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting model'], 500);
        }
    }
}
