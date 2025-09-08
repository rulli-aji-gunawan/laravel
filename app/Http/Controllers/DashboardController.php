<?php

namespace App\Http\Controllers;

use App\Models\TableDefect;
use App\Models\TableDowntime;
use App\Models\TableProduction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Check if we have any table production data first
            $hasData = TableProduction::count() > 0;
            
            if (!$hasData) {
                // Return view with empty data if no production data exists
                return view('users.dashboard', [
                    'chartData' => collect([]),
                    'defectData' => collect([]),
                    'currentFY' => 'FY25'
                ]);
            }

            // Ambil data Non Productive Time dari table Downtime
            $nonProductiveTimeData = TableDowntime::where(function ($query) {
                $query->where('downtime_type', 'Non Productive Time')
                    ->orWhere('dt_category', 'trial');
            })
                ->select(
                    'fy_n',
                    'model',
                    'item_name',
                    'date',
                    'shift',
                    'line',
                    'group',
                    DB::raw('SUM(total_time) as total_non_productive_downtime')
                )
                ->groupBy('fy_n', 'date', 'shift', 'model', 'item_name', 'line', 'group')
                ->get()
                ->keyBy(function ($item) {
                    return "{$item->fy_n}_{$item->date}_{$item->shift}_{$item->model}_{$item->item_name}_{$item->line}_{$item->group}";
                });

        // Ambil data Non Productive Time dari table Downtime
        $downTimeData = TableDowntime::where(function ($query) {
            $query->where('downtime_type', 'Downtime');
        })
            ->select(
                'fy_n',
                'model',
                'item_name',
                'date',
                'shift',
                'line',
                'group',
                DB::raw('SUM(total_time) as total_downtime')
            )
            ->groupBy('fy_n', 'date', 'shift', 'model', 'item_name', 'line', 'group')
            ->get()
            ->keyBy(function ($item) {
                return "{$item->fy_n}_{$item->date}_{$item->shift}_{$item->model}_{$item->item_name}_{$item->line}_{$item->group}";
            });

        $defectData = TableDefect::select(
            'fy_n',
            'model',
            'item_name',
            'date',
            'shift',
            'line',
            'group',
            'defect_category',
            'defect_name',
            DB::raw('SUM(COALESCE(defect_qty_a, 0) + COALESCE(defect_qty_b, 0)) as total_defect')
        )
            ->when(request('fy'), function ($query, $fy) {
                return $query->where('fy_n', 'like', $fy . '%');
            })
            ->when(request('model'), function ($query, $model) {
                return $query->where('model', $model);
            })
            ->when(request('item'), function ($query, $item) {
                return $query->where('item_name', $item);
            })
            ->whereNotNull('defect_category')
            ->whereNotNull('defect_name')
            ->groupBy('fy_n', 'model', 'item_name', 'date', 'shift', 'line', 'group', 'defect_category', 'defect_name')
            ->orderBy(DB::raw('SUM(COALESCE(defect_qty_a, 0) + COALESCE(defect_qty_b, 0))'), 'desc')
            ->get();

        // Logging untuk debug defect data
        Log::info("Total defect records: " . count($defectData));
        if (count($defectData) > 0) {
            Log::info("Sample defect data: " . json_encode($defectData->first()->toArray()));
        } else {
            Log::warning("No defect data found");
        }

        // Ambil data dengan semua field yang diperlukan untuk filter
        $chartData = TableProduction::select(
            'fy_n',
            'model',
            'item_name',
            'date',
            'shift',
            'line',
            'group'
        )
            ->selectRaw('
                SUM(COALESCE(ok_a, 0)) as total_ok_a,
                SUM(COALESCE(rework_a, 0)) as total_rework_a,
                SUM(COALESCE(scrap_a, 0)) as total_ng_a,
                SUM(COALESCE(ok_b, 0)) as total_ok_b,
                SUM(COALESCE(rework_b, 0)) as total_rework_b,
                SUM(COALESCE(scrap_b, 0)) as total_ng_b,
                SUM(COALESCE(total_prod_time, 0)) as total_minutes
            ')
            ->whereNotNull('fy_n')
            ->whereNotNull('date')
            ->groupBy('fy_n', 'date', 'shift', 'model', 'item_name', 'line', 'group')
            ->orderBy('fy_n')
            ->get()
            ->map(function ($row) use ($nonProductiveTimeData, $downTimeData) {
                try {
                    $total_minutes = floatval($row->total_minutes ?? 0);
                    $total_hours = $total_minutes > 0 ? $total_minutes / 60 : 0;
                    $total_stroke = ($row->total_ok_a ?? 0) + ($row->total_rework_a ?? 0) + ($row->total_ng_a ?? 0);
                    $sph = $total_hours > 0 ? round($total_stroke / $total_hours, 2) : 0;

                    $total_ok = ($row->total_ok_a ?? 0) + ($row->total_ok_b ?? 0);
                    $total_rework = ($row->total_rework_a ?? 0) + ($row->total_rework_b ?? 0);
                    $total_ng = ($row->total_ng_a ?? 0) + ($row->total_ng_b ?? 0);
                    $total_qty = $total_ok + $total_rework + $total_ng;

                    // Safely extract year and month dari fy_n
                    $fyParts = explode('-', $row->fy_n ?? 'FY25-1');
                    $year = $fyParts[0] ?? 'FY25';
                    $month = $fyParts[1] ?? '1';
                    $yearShort = substr($year, -2);

                    // Konversi bulan fiskal ke nama bulan
                    $monthNames = ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan", "Feb", "Mar"];
                    $monthIndex = ((int)$month - 1) % 12;
                    $monthName = $monthNames[$monthIndex] ?? "Apr";

                    // Get downtime for this production record
                    $key = "{$row->fy_n}_{$row->date}_{$row->shift}_{$row->model}_{$row->item_name}_{$row->line}_{$row->group}";
                    $nonProductiveTime = isset($nonProductiveTimeData[$key]) ? $nonProductiveTimeData[$key]->total_non_productive_downtime : 0;
                    $downtime = isset($downTimeData[$key]) ? $downTimeData[$key]->total_downtime : 0;

                    // Calculate effective production time
                    $effective_minutes = max(0, $total_minutes - $nonProductiveTime);
                    $effective_hours = $effective_minutes > 0 ? $effective_minutes / 60 : 0;

                    // Calculate effective SPH based on effective hours
                    $effective_sph = $effective_hours > 0 ? round($total_stroke / $effective_hours, 2) : 0;

                    // Menghitung waktu press time murni
                    $press_time = max(0, $total_minutes - $downtime - $nonProductiveTime);

                    // Calculate effective OR based on effective hours
                    $effective_or = $effective_minutes > 0 ? ($press_time / $effective_minutes) : 0;

                    // Menghitung FTC, RR, SR dengan safe division
                    $ftc = $total_qty > 0 ? ($total_ok / $total_qty) : 0;
                    $rework_ratio = $total_qty > 0 ? ($total_rework / $total_qty) : 0;
                    $scrap_ratio = $total_qty > 0 ? ($total_ng / $total_qty) : 0;

                return [
                    'fy_n' => $row->fy_n ?? '',
                    'year' => $year,
                    'year_short' => $yearShort,
                    'month' => $month,
                    'month_name' => $monthName,
                    'date' => $row->date ?? '',
                    'shift' => $row->shift ?? '',
                    'model' => $row->model ?? '',
                    'item_name' => $row->item_name ?? '',
                    'line' => $row->line ?? '',
                    'group' => $row->group ?? '',
                    'sph' => $sph,
                    'total_stroke' => $total_stroke,
                    'total_hours' => $total_hours,
                    'non_productive_downtime' => $nonProductiveTime,
                    'downtime' => $downtime,
                    'press_time' => $press_time,
                    'effective_minutes' => $effective_minutes,
                    'effective_hours' => $effective_hours,
                    'effective_sph' => $effective_sph,
                    'effective_or' => $effective_or,
                    'total_ok' => $total_ok,
                    'total_rework' => $total_rework,
                    'total_ng' => $total_ng,
                    'total_qty' => $total_qty,
                    'ftc' => $ftc,
                    'rework_ratio' => $rework_ratio,
                    'scrap_ratio' => $scrap_ratio,
                ];
                } catch (\Exception $e) {
                    Log::error("Error processing chart data row: " . $e->getMessage());
                    return null;
                }
            })
            ->filter() // Remove null values
            ->values(); // Reset array keys

        // Logging untuk debug
        Log::info("Total chart records: " . count($chartData));
        if (count($chartData) > 0) {
            Log::info("Sample chart data: " . json_encode($chartData->first()));
        } else {
            Log::warning("No chart data found");
        }

        // Determine current fiscal year
        $now = now();
        $fyYear = $now->month < 4 ? $now->year - 1 : $now->year;
        $currentFY = 'FY' . substr($fyYear, -2);

        return view('users.dashboard', compact('chartData', 'defectData', 'currentFY'));

        } catch (\Exception $e) {
            Log::error("Dashboard error: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Return view with empty data in case of error
            return view('users.dashboard', [
                'chartData' => collect([]),
                'defectData' => collect([]),
                'currentFY' => 'FY25'
            ])->with('error', 'Unable to load dashboard data. Please check the logs.');
        }
    }
}
