<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $slot }}</title>

    <!-- Box Icons  -->
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <!-- Styles  -->
    {{-- <link rel="shortcut icon" href="kxp_fav.png" type="image/x-icon"> --}}
    @if(app()->environment('production'))
        <link rel="stylesheet" href="{{ secure_asset('css/app-layout.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('css/app-layout.css') }}">
    @endif

</head>

<body>
    <!-- ============ Header ============ -->

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <header>
        <p class="welcome">Hi {{ auth()->user()->name }}</p>
        <p class="page-title">{{ $slot }}</p>
        <div class="right-header">
            <form id="link-logout" action="{{ route('logout') }}" method="post">
                @csrf
                <button class="link-logout">Logout</button>
            </form>
        </div>
    </header>

    <div class="sidebar close">
        <!-- ========== Logo ============  -->
        <a href="#" class="logo-box">
            <i class='bx bxl-xing'></i>
            <div class="logo-name">MMKI Stamping</div>
        </a>

        <!-- ========== List ============  -->
        <ul class="sidebar-list">
            <!-- -------- Non Dropdown List Item ------- -->
            <li>
                <div class="title">
                    <a href="/dashboard" class="link">
                        <i class='bx bx-grid-alt'></i>
                        <span class="name">Dashboard</span>
                    </a>
                    <!-- <i class='bx bxs-chevron-down'></i> -->
                </div>
                <div class="submenu">
                    <a href="/dashboard" class="submenu-title">Dashboard</a>
                    <!-- submenu links here  -->
                </div>
            </li>


            <!-- -------- Dropdown List Item ------- -->
            <li class="dropdown">
                <div class="title">
                    <a href="#" class="link">
                        <i class='bx bxs-keyboard'></i>
                        <span class="name">Input Report</span>
                    </a>
                    <i class='bx bxs-chevron-down'></i>
                </div>
                <div class="submenu">
                    <a href="#" class="submenu-title">Input Report</a>
                    <a href="/input-report/production" class="link">Production</a>
                    <a href="#" class="link">Tooling</a>
                </div>
            </li>

            <!-- -------- Dropdown List Item ------- -->
            <li class="dropdown">
                <div class="title">
                    <a href="#" class="link">
                        <i class='bx bx-table'></i>
                        <span class="name">Data Table</span>
                    </a>
                    <i class='bx bxs-chevron-down'></i>
                </div>
                <div class="submenu">
                    <a href="#" class="submenu-title">Data Table</a>
                    <a href="{{ route('table_production') }}" class="link">Tabel Production</a>
                    <a href="{{ route('table_downtime') }}" class="link">Tabel Downtime</a>
                    <a href="{{ route('table_defect') }}" class="link">Tabel Defect</a>
                    <a href="#" class="link">Tabel Tooling</a>
                </div>
            </li>


            <!-- -------- Dropdown List Item ------- -->
            <li class="dropdown">
                <div class="title">
                    <a href="#" class="link">
                        <i class='bx bx-edit'></i>
                        <span class="name">Master Data</span>
                    </a>
                    <i class='bx bxs-chevron-down'></i>
                </div>
                <div class="submenu">
                    <a href="#" class="submenu-title">Master Data</a>
                    <a href="{{ route('users') }}" class="link">Data Users</a>
                    <a href="{{ route('models') }}" class="link">List Model Items</a>
                    <a href="{{ route('process') }}" class="link">Process Name</a>
                    <a href="{{ route('downtime_categories') }}" class="link">DT Category</a>
                    <a href="{{ route('dt_classifications') }}" class="link">DT Classification</a>

                    <a href="#" class="link">Defects Category</a>
                </div>
            </li>
        </ul>
    </div>

    <!-- Home Section with Toggle Button (sesuai CSS asli) -->
    <section class="home">
        <div class="toggle-sidebar">
            <i class='bx bx-x-circle' id="hide-toggle"></i>
            <i class='bx bx-menu' id="show-toggle"></i>
        </div>
    </section>

    <!-- JavaScript di dalam body -->
    <script>
        // Mendeteksi apakah session masih aktif sebelum melakukan proses logout
        document.getElementById('link-logout').addEventListener('submit', function(e) {
            // Cek apakah user masih terautentikasi
            fetch('/api/check-auth', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.authenticated) {
                    // Jika tidak terautentikasi, batalkan form submission dan redirect ke home
                    e.preventDefault();
                    window.location.href = '/';
                }
                // Jika terautentikasi, biarkan form submit berjalan normal
            })
            .catch(() => {
                // Jika terjadi error, arahkan ke home
                e.preventDefault();
                window.location.href = '/';
            });
        });
    </script>

    <!-- Load JavaScript app.js -->
    @if(app()->environment('production'))
        <script src="{{ secure_asset('js/app.js') }}"></script>
    @else
        <script src="{{ asset('js/app.js') }}"></script>
    @endif

</body>

</html>