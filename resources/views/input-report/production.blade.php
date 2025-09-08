<x-app-layout>Input Production Report</x-app-layout>

<meta name="csrf-token" content="{{ csrf_token() }}">

<x-production-layout :models="$models" :years="$years" :items="$items"></x-production-layout>

<script src="{{ secure_asset('js/prod-tbl-row.js') }}"></script>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
