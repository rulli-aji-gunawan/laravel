<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\ModelItem;
use App\Models\TableDefect;
use Illuminate\Http\Request;
use App\Models\InputProduction;
use App\Models\ProductionProblem;
use App\Models\TableProduction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    public function index()
    {
        try {
            Log::info('ProductionController@index: Starting');
            
            // Check if ModelItem table exists and has data
            $modelsQuery = ModelItem::select('model_code')->distinct();
            Log::info('ProductionController@index: Query created');
            
            $models = $modelsQuery->pluck('model_code');
            Log::info('ProductionController@index: Models fetched', ['count' => $models->count()]);
            
            // If no models found, return empty collection
            if ($models->isEmpty()) {
                Log::warning('ProductionController@index: No models found in database');
                $models = collect([]);
            }
            
            Log::info('ProductionController@index: Returning view');
            return view('input-report.production', compact('models'));
        } catch (Exception $e) {
            Log::error('Error in ProductionController@index: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return view with empty models on error
            $models = collect([]);
            return view('input-report.production', compact('models'));
        }
    }

    public function getYears($model)
    {
        $years = ModelItem::where('model_code', $model)
            ->select('model_year')
            ->distinct()
            ->pluck('model_year');
        return response()->json($years);
    }

    public function getItems($model)
    {
        $items = ModelItem::where('model_code', $model)
            ->select('id', 'model_code', 'item_name', 'product_picture')
            ->get();
        return response()->json($items);
    }


    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Log raw request data
            Log::info('Raw request data', [
                'content_type' => $request->header('Content-Type'),
                'data' => $request->all()
            ]);

            // Validasi input
            $validatedData = $request->validate([
                'reporter' => 'required|string',
                'group' => 'required|string',
                'date' => 'required|date',
                'shift' => 'required|string',
                'line' => 'required|string',
                'start_time' => 'required|date_format:H:i',
                'finish_time' => 'required |date_format:H:i',
                'total_prod_time' => 'required|integer',
                'model' => 'required|string',
                'model_year' => 'nullable|string',
                'spm' => 'required|numeric',
                'item_name' => 'required|string',
                'coil_no' => 'required|string',
                'plan_a' => 'required|integer',
                'plan_b' => 'required|integer',
                'ok_a' => 'required|integer',
                'ok_b' => 'required|integer',
                'rework_a' => 'required|integer',
                'rework_b' => 'required|integer',
                'scrap_a' => 'required|integer',
                'scrap_b' => 'required|integer',
                'sample_a' => 'required|integer',
                'sample_b' => 'required|integer',
                'rework_exp' => 'nullable|string',
                'scrap_exp' => 'nullable|string',
                'trial_sample_exp' => 'nullable|string',

                // Validasi untuk production problems dinamis
                'production_problems' => 'nullable|array',
                'production_problems.*.time_from' => 'required|date_format:H:i',
                'production_problems.*.time_until' => 'required|date_format:H:i',
                'production_problems.*.total_time' => 'required|integer',
                'production_problems.*.process_name' => 'required|string',
                'production_problems.*.dt_category' => 'required|string',
                'production_problems.*.downtime_type' => 'nullable|string',
                'production_problems.*.dt_classification' => 'required|string',
                'production_problems.*.problem_description' => 'required|string',
                'production_problems.*.root_cause' => 'required|string',
                'production_problems.*.counter_measure' => 'required|string',
                'production_problems.*.pic' => 'required|string',
                'production_problems.*.status' => 'required|string',
                'production_problems.*.problem_picture_data' => 'nullable|string',
                'production_problems.*.problem_picture_name' => 'nullable|string',
            ]);

            Log::info('Validation passed', ['validatedData' => $validatedData]);

            $date = $validatedData['date'];
            $carbonDate = \Carbon\Carbon::parse($date);
            $year = $carbonDate->year;
            $month = $carbonDate->month;

            // Hitung tahun fiskal
            if ($month >= 4) {
                $fyYear = $year;
            } else {
                $fyYear = $year - 1;
            }

            // Hitung urutan bulan fiskal (April = 1, Maret = 12)
            $fiscalMonth = $month >= 4 ? $month - 3 : $month + 9;

            // Format: FY2025-1, FY2025-2, dst
            $validatedData['fy_n'] = 'FY' . $fyYear . '-' . $fiscalMonth;

            // Simpan data produksi
            // $dataProduction = InputProduction::create($validatedData);
            $dataProduction = TableProduction::create($validatedData);
            Log::info('InputProduction created', ['id' => $dataProduction->id]);

            if ($request->has('defect_areas')) {
                $count = count($request->defect_areas);
                for ($i = 0; $i < $count; $i++) {
                    TableDefect::create([
                        'table_production_id' => $dataProduction->id,
                        'reporter' => $request->reporter,
                        'group' => $request->group,
                        'date' => $request->date,
                        'fy_n' => $validatedData['fy_n'],
                        'shift' => $request->shift,
                        'line' => $request->line,
                        'model' => $request->model,
                        'model_year' => $request->model_year,
                        'item_name' => $request->item_name,
                        'coil_no' => $request->coil_no,
                        'defect_area' => $request->defect_areas[$i],
                        'defect_name' => $request->defect_names[$i],
                        'defect_qty_a' => $request->defect_qtys_a[$i],
                        'defect_qty_b' => $request->defect_qtys_b[$i] ?? null,
                        'defect_category' => $request->defect_categories[$i],
                    ]);
                }
            }

            // Data yang akan dishare
            $sharedData = [
                'table_production_id' => $dataProduction->id,
                'reporter' => $dataProduction->reporter,
                'group' => $dataProduction->group,
                'date' => $dataProduction->date,
                'fy_n' => $validatedData['fy_n'],
                'shift' => $dataProduction->shift,
                'line' => $dataProduction->line,
                'model' => $dataProduction->model,
                'model_year' => $dataProduction->model_year,
                'item_name' => $dataProduction->item_name,
                'coil_no' => $dataProduction->coil_no,
            ];

            // Ambil data tanpa memaksa format JSON
            $productionProblems = $request->input('production_problems', []);

            if (empty($productionProblems)) {
                throw new Exception('Tidak ada data production problems yang dikirim');
            }

            foreach ($productionProblems as $index => $problem) {
                try {
                    Log::info('Processing production problem', [
                        'index' => $index,
                        'problem' => $problem
                    ]);

                    $problemData = array_merge($sharedData, $problem);

                    // Cek apakah ada data gambar base64
                    if (isset($problem['problem_picture_data']) && !empty($problem['problem_picture_data'])) {
                        // Ekstrak data gambar dari string base64
                        $base64Image = $problem['problem_picture_data'];
                        list($type, $data) = explode(';', $base64Image);
                        list(, $data) = explode(',', $data);
                        $imageData = base64_decode($data);

                        // Dapatkan ekstensi file
                        $extension = 'jpg'; // Default
                        if (isset($problem['problem_picture_name'])) {
                            $originalName = $problem['problem_picture_name'];
                            $extension = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';
                        }

                        // Buat nama file unik
                        $filename = 'problem_picture_' . str_pad($dataProduction->id, 7, '0', STR_PAD_LEFT) . '_' . ($index + 1) . '.' . $extension;

                        // Simpan file
                        $path = public_path('images/problems');
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        file_put_contents($path . '/' . $filename, $imageData);

                        // Simpan nama file ke database
                        $problemData['problem_picture'] = 'images/problems/' . $filename;

                        // Hapus data base64 dan nama file asli dari data yang akan disimpan ke database
                        unset($problemData['problem_picture_data']);
                        unset($problemData['problem_picture_name']);
                    }
                    // Cek apakah menggunakan metode tradisional file upload
                    else if ($request->hasFile('problem_pictures') && isset($request->file('problem_pictures')[$index])) {
                        $file = $request->file('problem_pictures')[$index];
                        if ($file) {
                            $filename = 'problem_picture_' . str_pad($dataProduction->id, 7, '0', STR_PAD_LEFT) . '_' . ($index + 1) . '.' . $file->getClientOriginalExtension();
                            $file->move(public_path('images/problems'), $filename);

                            // Ubah ini:
                            $problemData['problem_picture'] = $filename;

                            // Menjadi:
                            $problemData['problem_picture'] = 'images/problems/' . $filename;
                        }
                    }

                    // Simpan ke table_downtimes
                    $createdProblem = $dataProduction->tableDowntimes()->create($problemData);

                    Log::info('ProductionProblem created', [
                        'id' => $createdProblem->id,
                        'data' => $createdProblem->toArray()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error creating production problem', [
                        'index' => $index,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            DB::commit();
            Log::info('Transaction committed successfully');

            return redirect()->route('form.production')->with('success', 'Data berhasil disimpan');;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in store method', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Redirect kembali ke form dengan pesan error
            return redirect()->route('form.production')->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function deleteProblemPicture($id)
    {
        try {
            $downtime = \App\Models\TableDowntime::find($id);

            if (!$downtime) {
                return response()->json(['error' => 'Record not found'], 404);
            }

            if ($downtime->problem_picture) {
                // Hapus file fisik
                $filePath = public_path($downtime->problem_picture);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                // Update database - set field menjadi null
                $downtime->problem_picture = null;
                $downtime->save();

                return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus']);
            }

            return response()->json(['warning' => 'Tidak ada gambar untuk dihapus'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting problem picture', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Gagal menghapus gambar: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Validasi input
            $validatedData = $request->validate([
                // ... validasi lainnya
                'production_problems' => 'nullable|array',
                'production_problems.*.id' => 'nullable|integer',
                'production_problems.*.delete_picture' => 'nullable|boolean',
                // ... validasi lainnya
            ]);

            $production = TableProduction::findOrFail($id);

            // Update data produksi
            $production->update($validatedData);

            // Ambil data production problems
            $productionProblems = $request->input('production_problems', []);

            foreach ($productionProblems as $index => $problem) {
                // Jika ada ID, berarti ini update record yang sudah ada
                if (isset($problem['id'])) {
                    $downtimeId = $problem['id'];
                    $downtime = \App\Models\TableDowntime::find($downtimeId);

                    if ($downtime) {
                        // Cek apakah ada flag delete_picture
                        if (isset($problem['delete_picture']) && $problem['delete_picture'] == 1) {
                            if ($downtime->problem_picture) {
                                // Hapus file fisik
                                $filePath = public_path($downtime->problem_picture);
                                if (file_exists($filePath)) {
                                    unlink($filePath);
                                }

                                // Set problem_picture menjadi null
                                $problem['problem_picture'] = null;
                            }
                        }

                        // Update data
                        $downtime->update($problem);
                    }
                }
                // Jika tidak ada ID, berarti ini data baru
                else {
                    // Proses seperti di method store
                    // ...
                }
            }

            DB::commit();
            return redirect()->route('production.edit', $id)->with('success', 'Data berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in update method', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('production.edit', $id)->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
