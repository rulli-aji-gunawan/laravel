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
    
    <!-- Inline critical CSS backup for Railway -->
    <style>
        /* Critical layout styles inline backup */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        
        header {
            position: fixed;
            display: flex;
            top: 0;
            left: 0;
            height: 48px;
            width: 100%;
            background-color: #1c6d3f;
            align-items: center;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            position: fixed;
            top: 48px;
            left: 0;
            width: 250px;
            height: calc(100vh - 48px);
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 90;
        }
        
        .sidebar .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar li {
            border-bottom: 1px solid #eee;
        }
        
        .sidebar a {
            display: block;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar a:hover {
            background: #f0f0f0;
            color: #1c6d3f;
        }
        
        .welcome {
            color: white;
            margin-left: 20px;
            font-size: 14px;
        }
        
        .logout {
            margin-left: auto;
            margin-right: 20px;
        }
        
        .logout a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .logout a:hover {
            background: rgba(255,255,255,0.1);
        }
    </style>

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
                    <a href="/input-report" class="submenu-title">Input Report</a>
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


</body>


// Mendeteksi apakah session masih aktif sebelum melakukan proses logout
<script>
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