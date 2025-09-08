<x-app-layout>Dashboard Manufacturing Stamping</x-app-layout>

<head>
    <link rel="stylesheet" href="../css/dashboard-layout.css">
    <link rel="stylesheet" href="{{ asset('css/input-production-layout.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
            {{-- <label for="fyFilter">FY: </label> --}}
            <select id="fyFilter">
                <option value="all" {{ request('fy') == 'all' ? 'selected' : '' }}>All FY</option>
                @foreach (array_unique(array_map(fn($d) => 'FY' . substr(explode('-', $d['fy_n'] ?? 'FY25-1')[0], -2), $chartData->toArray())) as $fy)
                    <option value="{{ $fy }}" {{ $fy == $currentFY ? 'selected' : '' }}>
                        {{ $fy }}</option>
                @endforeach
            </select>
        </div>
        <div>
            {{-- <label for="monthFilter">Month: </label> --}}
            <select id="monthFilter">
                <option value="all">All Month</option>
                @php
                    $months = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];
                    $monthNames = [];
                    foreach ($chartData as $data) {
                        if (isset($data['fy_n']) && !empty($data['fy_n']) && strpos($data['fy_n'], '-') !== false) {
                            $monthIndex = (intval(explode('-', $data['fy_n'])[1]) - 1) % 12;
                            $monthNames[$monthIndex] = $months[$monthIndex];
                        }
                    }
                    ksort($monthNames);
                @endphp
                @foreach ($monthNames as $index => $month)
                    <option value="{{ $index + 1 }}">{{ $month }}</option>
                @endforeach
            </select>
        </div>
        <div>
            {{-- <label for="dateFilter">Date: </label> --}}
            <input type="date" id="dateFilter">
            <button id="clearDateFilter" class="btn-sm">X</button>
        </div>
        <div>
            {{-- <label for="shiftFilter">Shift: </label> --}}
            <select id="shiftFilter">
                <option value="all">All Shift</option>
                @foreach (array_unique(array_column($chartData->toArray(), 'shift')) as $shift)
                    @if ($shift && !empty($shift))
                        <option value="{{ $shift }}">{{ $shift }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            {{-- <label for="modelFilter">Model: </label> --}}
            <select id="modelFilter">
                <option value="all">All Model</option>
                @foreach (array_unique(array_column($chartData->toArray(), 'model')) as $model)
                    @if ($model && !empty($model))
                        <option value="{{ $model }}">{{ $model }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            {{-- <label for="itemFilter">Item: </label> --}}
            <select id="itemFilter">
                <option value="all">All Item Name</option>
                @foreach (array_unique(array_column($chartData->toArray(), 'item_name')) as $item)
                    @if ($item && !empty($item))
                        <option value="{{ $item }}">{{ $item }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            {{-- <label for="lineFilter">Line: </label> --}}
            <select id="lineFilter">
                <option value="all">All Line</option>
                @foreach (array_unique(array_column($chartData->toArray(), 'line')) as $line)
                    @if ($line && !empty($line))
                        <option value="{{ $line }}">{{ $line }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            {{-- <label for="groupFilter">Group: </label> --}}
            <select id="groupFilter">
                <option value="all">All Group</option>
                @foreach (array_unique(array_column($chartData->toArray(), 'group')) as $group)
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
        <div class="chart-container" style="position: relative; height: 300px;">
            <canvas id="defectChart"></canvas>
        </div>
        <div class="home-content">
            <canvas id="srChart"></canvas>
        </div>
        @else
        <div class="home-content" style="text-align: center; padding: 50px;">
            <h3>No Production Data Available</h3>
            <p>Please import production data or add new production records.</p>
        </div>
        @endif
    </div>

    {{-- <script src="../js/prod-tbl-row.js"></script> --}}

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="{{ asset('js/sph-chart.js') }}"></script>
    <script src="{{ asset('js/or-chart.js') }}"></script>
    <script src="{{ asset('js/ftc-chart.js') }}"></script>
    <script src="{{ asset('js/rr-chart.js') }}"></script>
    <script src="{{ asset('js/sr-chart.js') }}"></script>
    <script src="{{ asset('js/defect-chart.js') }}"></script>

    <script>
        @if(count($chartData) > 0)
        window.initSPHDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initORDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initFTCDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initRRDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initSRDashboardChart(@json($chartData), '{{ $currentFY }}');
        window.initDefectChart(@json($defectData), '{{ $currentFY }}');
        @else
        console.log('No chart data available');
        @endif

        document.getElementById('clearDateFilter').addEventListener('click', function() {
            document.getElementById('dateFilter').value = '';
            updateAllFilters();
        });

        function updateAllFilters() {
            @if(count($chartData) > 0)
            const fyFilter = document.getElementById('fyFilter').value;
            const modelFilter = document.getElementById('modelFilter').value;
            const itemFilter = document.getElementById('itemFilter').value;
            const monthFilter = document.getElementById('monthFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            const shiftFilter = document.getElementById('shiftFilter').value;
            const lineFilter = document.getElementById('lineFilter').value;
            const groupFilter = document.getElementById('groupFilter').value;

            window.updateSPHDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                lineFilter, groupFilter);
            window.updateORDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                lineFilter, groupFilter);
            window.updateFTCDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                lineFilter, groupFilter);
            window.updateRRDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                lineFilter, groupFilter);
            window.updateSRDashboardChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter,
                lineFilter, groupFilter);
            window.updateDefectChart(fyFilter, modelFilter, itemFilter, monthFilter, dateFilter, shiftFilter, lineFilter,
                groupFilter);
            @endif
        }

        // Tambahkan event listener untuk semua filter
        @if(count($chartData) > 0)
        document.querySelectorAll(
                '#fyFilter, #modelFilter, #itemFilter, #monthFilter, #dateFilter, #shiftFilter, #lineFilter, #groupFilter')
            .forEach(filter => {
                filter.addEventListener('change', updateAllFilters);
            });
        @endif
    </script>

</section>

<script src="../js/sidebar.js"></script>
