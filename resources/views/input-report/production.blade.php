<x-app-layout>Input Production Report</x-app-layout>

<meta name="csrf-token" content="{{ csrf_token() }}">

<x-production-layout :models="$models"></x-production-layout>

<script src="{{ secure_asset('js/input-qty.js') }}"></script>
<script src="{{ secure_asset('js/sidebar.js') }}"></script>
<script src="{{ secure_asset('js/prod-tbl-row.js') }}"></script>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
