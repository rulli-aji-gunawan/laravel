<x-app-layout>Dashboard Manufacturing Stamping</x-app-layout>

<head>
    <link rel="stylesheet" href="{{ secure_asset('css/dashboard-layout.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/input-production-layout.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <!-- Inline Critical CSS for Railway deployment -->
    <style>
        /* Critical Dashboard Styles - Inline backup */
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            padding: 20px;
            margin-top: 20px;
        }

        .home-content {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-height: 300px;
        }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 48px;
            justify-content: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .filter-container select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            font-size: 14px;
        }

        .filter-container label {
            font-weight: 500;
            margin-right: 5px;
            color: #333;
        }

        .home {
            margin-left: 250px;
            transition: all 0.3s ease;
            padding: 20px;
            min-height: 100vh;
            background: #f5f5f5;
        }

        .toggle-sidebar {
            position: fixed;
            top: 60px;
            left: 10px;
            z-index: 1000;
            background: white;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .toggle-sidebar i {
            font-size: 24px;
            color: #1c6d3f;
            cursor: pointer;
        }

        /* No data message */
        .no-data-message {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-size: 18px;
            color: #666;
        }

        /* Chart containers */
        canvas {
            max-width: 100%;
            height: auto !important;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .home {
                margin-left: 0;
                padding: 10px;
            }
            
            .dashboard-container {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 15px;
            }
            
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<!-- ============= Home Section =============== -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: 'Error!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
    });
</script>

<section class="home">
    <div class="toggle-sidebar">
        <i class='bx bx-x-circle' id="hide-toggle"></i>
        <i class='bx bx-menu' id="show-toggle"></i>
    </div>
    <div class="filter-container">
        <div>
            <label for="fyFilter">FY:</label>
            <select id="fyFilter">
                <option value="all">All FY</option>
                @foreach (array_unique(array_filter(array_column($chartData->toArray(), 'fy'))) as $fy)
                    @if ($fy && !empty($fy))
                        <option value="{{ $fy }}" {{ $fy == $currentFY ? 'selected' : '' }}>{{ $fy }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            <label for="modelFilter">Model:</label>
            <select id="modelFilter">
                <option value="all">All Model</option>
                @foreach (array_unique(array_filter(array_column($chartData->toArray(), 'model'))) as $model)
                    @if ($model && !empty($model))
                        <option value="{{ $model }}">{{ $model }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            <label for="itemFilter">Item:</label>
            <select id="itemFilter">
                <option value="all">All Item</option>
                @foreach (array_unique(array_filter(array_column($chartData->toArray(), 'item_name'))) as $item)
                    @if ($item && !empty($item))
                        <option value="{{ $item }}">{{ $item }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            <label for="monthFilter">Month:</label>
            <select id="monthFilter">
                <option value="all">All Month</option>
                @foreach (array_unique(array_filter(array_column($chartData->toArray(), 'month'))) as $month)
                    @if ($month && !empty($month))
                        <option value="{{ $month }}">{{ $month }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            <label for="dateFilter">Date:</label>
            <select id="dateFilter">
                <option value="all">All Date</option>
                @foreach (array_unique(array_filter(array_column($chartData->toArray(), 'date'))) as $date)
                    @if ($date && !empty($date))
                        <option value="{{ $date }}">{{ $date }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            <label for="shiftFilter">Shift:</label>
            <select id="shiftFilter">
                <option value="all">All Shift</option>
                @foreach (array_unique(array_filter(array_column($chartData->toArray(), 'shift'))) as $shift)
                    @if ($shift && !empty($shift))
                        <option value="{{ $shift }}">{{ $shift }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            <label for="groupFilter">Group:</label>
            <select id="groupFilter">
                <option value="all">All Group</option>
                @foreach (array_unique(array_filter(array_column($chartData->toArray(), 'group'))) as $group)
                    @if ($group && !empty($group))
                        <option value="{{ $group }}">{{ $group }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    
    <div class="dashboard-container">
        @if(count($chartData) > 0)
        <div class="home-content">
            <canvas id="sphChart"></canvas>
        </div>
        <div class="home-content">
            <canvas id="orChart"></canvas>
        </div>
        <div class="home-content">
            <canvas id="ftcChart"></canvas>
        </div>
        <div class="home-content">
            <canvas id="rrChart"></canvas>
        </div>
        <div class="home-content">
            <canvas id="srChart"></canvas>
        </div>
        <div class="home-content">
            <canvas id="defectChart"></canvas>
        </div>
        @else
        <div class="no-data-message">
            <h3>No Production Data Available</h3>
            <p>Please import production data or check database connection.</p>
            <p>Use: <code>php artisan db:setup-minimal</code> to create sample data.</p>
        </div>
        @endif
    </div>

    @if(count($chartData) > 0)
        <div class="details-container">
            <h3>Production Details</h3>
            <ul class="details-list">
                @foreach($chartData->take(10) as $data)
                <li>
                    <strong>{{ $data->item_name ?? 'Unknown Item' }}</strong> - 
                    Model: {{ $data->model ?? 'N/A' }}, 
                    Date: {{ $data->date ?? 'N/A' }}, 
                    Shift: {{ $data->shift ?? 'N/A' }}, 
                    SPH: {{ $data->sph ?? '0' }}
                </li>
                @endforeach
            </ul>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="{{ secure_asset('js/sph-chart.js') }}"></script>
    <script src="{{ secure_asset('js/or-chart.js') }}"></script>
    <script src="{{ secure_asset('js/ftc-chart.js') }}"></script>
    <script src="{{ secure_asset('js/rr-chart.js') }}"></script>
    <script src="{{ secure_asset('js/sr-chart.js') }}"></script>
    <script src="{{ secure_asset('js/defect-chart.js') }}"></script>

    <script>
        @if(count($chartData) > 0)
        window.initSPHDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initORDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initFTCDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initRRDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initSRDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initDefectDashboardChart(@json($chartData), '{{ $currentFY }}');
        @endif

        // Filter functionality
        function updateCharts() {
            const fyFilter = document.getElementById('fyFilter').value;
            const modelFilter = document.getElementById('modelFilter').value;
            const itemFilter = document.getElementById('itemFilter').value;
            const monthFilter = document.getElementById('monthFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            const shiftFilter = document.getElementById('shiftFilter').value;
            const groupFilter = document.getElementById('groupFilter').value;

            @if(count($chartData) > 0)
            window.updateSPHDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                groupFilter);
            window.updateORDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                groupFilter);
            window.updateFTCDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                groupFilter);
            window.updateRRDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                groupFilter);
            window.updateSRDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                groupFilter);
            window.updateDefectDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                groupFilter);
            @endif
        }

        // Add event listeners to all filters
        document.getElementById('fyFilter').addEventListener('change', updateCharts);
        document.getElementById('modelFilter').addEventListener('change', updateCharts);
        document.getElementById('itemFilter').addEventListener('change', updateCharts);
        document.getElementById('monthFilter').addEventListener('change', updateCharts);
        document.getElementById('dateFilter').addEventListener('change', updateCharts);
        document.getElementById('shiftFilter').addEventListener('change', updateCharts);
        document.getElementById('groupFilter').addEventListener('change', updateCharts);
    </script>

    <script src="{{ secure_asset('js/sidebar.js') }}"></script>
</section>
