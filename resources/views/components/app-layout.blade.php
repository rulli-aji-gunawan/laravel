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
        /* Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        
        /* Globals */
        * {
            font-family: 'Poppins', sans-serif;
            font-size: .9rem;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            list-style: none;
            text-decoration: none;
        }

        /* Variables */
        :root {
            --color_Blue: #3f93f1;
            --color_Dark1: #1e1e1e;
            --color_Dark2: #252527;
            --color_Light1: #dfdfdf;
            --color_Light2: #c4c4c4;
            --color_Green1: #1c6d3f;
            --color_Green2: #449d6b;
            --color_Green3: #f2f8f4;
            --color_DarkGreen: darkgreen;
        }

        /* Header */
        header {
            position: fixed;
            display: flex;
            top: 0;
            left: 0;
            height: 48px;
            font-size: .8rem;
            width: 100%;
            background-color: var(--color_Green1);
            align-items: center;
            transition: all .5s ease;
            z-index: 100;
            box-shadow: 5px 5px 5px -2px var(--color_Green2);
        }

        .welcome {
            flex: 25%;
            font-size: 14px;
            color: var(--color_Light1);
            margin-left: 18px;
            margin-right: auto;
            text-align: left;
        }

        header button {
            background: none;
            color: var(--color_Light1);
            border: 1px solid var(--color_Light2);
            border-radius: 6px;
            padding: 2px 5px 2px 5px;
            font-size: 12px;
        }

        header form:hover,
        .link-logout:hover {
            cursor: pointer;
            color: yellow;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 48px;
            left: 0;
            height: 100%;
            width: 180px;
            font-size: .8rem;
            background-color: var(--color_Green1);
            transition: all .5s ease;
            z-index: 100;
            box-shadow: 5px 2px 5px -2px var(--color_Green2);
        }

        .sidebar.close {
            width: 50px;
        }

        /* Logo */
        .logo-box {
            height: 60px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-items: center;
            padding: 10px 9px;
            color: var(--color_Light1);
            transition: all .5s ease;
        }

        .logo-box:hover {
            color: yellow;
        }

        .logo-box:hover i {
            border: 2px solid yellow;
        }

        .logo-box i {
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            font-size: 16px;
            height: 32px;
            width: 32px;
            text-align: center;
            transition: all .5s ease;
            border: 2px solid var(--color_Light2);
            border-radius: 100%;
        }

        .sidebar.close .logo-box i {
            display: flex;
            position: absolute;
            transform: rotate(-360deg);
            font-size: 16px;
            height: 32px;
            width: 32px;
            border-radius: 100%;
        }

        .logo-name {
            padding-left: 42px;
            font-size: 1rem;
            font-weight: 500;
            min-width: max-content;
            transition: all .8s ease;
        }

        /* Sidebar List */
        .sidebar-list {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 10px 0px;
            gap: 12px;
            overflow: auto;
        }

        .sidebar-list::-webkit-scrollbar {
            display: none;
        }

        .sidebar-list li {
            transition: all .5s ease;
        }

        .sidebar-list li:hover {
            background-color: var(--color_Green2);
        }

        .sidebar-list li .title {
            display: flex;
            align-items: center;
            transition: all .5s ease;
            cursor: pointer;
        }

        .sidebar-list li.active .title {
            background-color: var(--color_Green2);
        }

        .sidebar-list li.active .bxs-chevron-down {
            transition: all .5s ease;
            transform: rotate(180deg);
        }

        .sidebar-list li .title .link {
            display: flex;
            align-items: center;
        }

        .sidebar-list li .title i {
            height: 50px;
            min-width: 50px;
            text-align: center;
            line-height: 50px;
            color: var(--color_Light1);
            font-size: 18px;
        }

        .sidebar-list li .title .name {
            color: var(--color_Light1);
        }

        /* Submenu */
        .sidebar-list li .submenu {
            width: 0;
            height: 0;
            opacity: 0;
            transition-property: width;
            transition-duration: 2s;
            transition-timing-function: linear;
        }

        .submenu .submenu-title {
            font-weight: 500;
        }

        .sidebar-list li.dropdown.active .submenu {
            opacity: 1;
            width: unset;
            height: unset;
            display: flex;
            flex-direction: column;
            padding: 4px 4px 12px 56px;
            background-color: var(--color_Green2);
            transition: all .5s ease;
        }

        .submenu .link {
            color: var(--color_Light1);
            padding: 5px 0;
            transition: all .5s ease;
        }

        .submenu .link:hover {
            color: yellow;
        }

        .submenu-title {
            display: none;
        }

        /* Submenu Close */
        .sidebar.close .logo-name,
        .sidebar.close .title .name,
        .sidebar.close .title .bxs-chevron-down {
            display: none;
        }

        .sidebar.close .sidebar-list {
            overflow: visible;
        }

        .sidebar.close .sidebar-list li {
            position: relative;
        }

        .sidebar.close .sidebar-list li .submenu {
            display: flex;
            flex-direction: column;
            position: absolute;
            left: 100%;
            margin-top: 0;
            padding: 10px 20px;
            border-radius: 0 6px 6px 0;
            height: max-content;
            width: max-content;
            opacity: 0;
            transition: all .5s ease;
            pointer-events: none;
        }

        .sidebar.close .sidebar-list li:hover .submenu {
            opacity: 1;
            top: 0px;
            min-height: 50px;
            pointer-events: initial;
            background-color: var(--color_Green2);
            transition: all .5s ease;
            animation-name: slide-right;
            animation-duration: 1.5s;
        }

        @keyframes slide-right {
            0% {
                left: 14px;
                top: 0px;
            }
            25% {
                left: 50px;
                top: 0px;
            }
        }

        .sidebar.close .submenu-title {
            display: block;
            margin-top: 6px;
            font-style: 16px;
            color: yellow;
        }

        /* Home Section */
        .home {
            position: relative;
            left: 180px;
            width: calc(100% - 180px);
            transition: all .5s ease;
            opacity: 1;
        }

        .sidebar.close~.home {
            left: 50px;
            width: calc(100% - 50px);
        }

        /* Toggle Sidebar Button */
        .toggle-sidebar {
            position: fixed;
            top: 60px;
            left: 10px;
            z-index: 1000;
            background: white;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-sidebar:hover {
            background: #f0f0f0;
            transform: scale(1.1);
        }

        .toggle-sidebar i {
            font-size: 24px;
            color: var(--color_Green1);
            transition: all 0.3s ease;
        }

        .toggle-sidebar:hover i {
            color: var(--color_Green2);
        }

        /* Logout styling */
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

    <!-- Toggle Sidebar Button -->
    <div class="toggle-sidebar" style="position: fixed; top: 60px; left: 10px; z-index: 1000; background: white; border-radius: 50%; padding: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer;">
        <i class='bx bx-x-circle' id="hide-toggle" style="font-size: 24px; color: #1c6d3f;"></i>
        <i class='bx bx-menu' id="show-toggle" style="font-size: 24px; color: #1c6d3f; display: none;"></i>
    </div>

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

<!-- Sidebar JavaScript -->
@if(app()->environment('production'))
    <script src="{{ secure_asset('js/sidebar.js') }}"></script>
@else
    <script src="{{ asset('js/sidebar.js') }}"></script>
@endif

<!-- Inline sidebar JavaScript backup -->
<script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar functionality inline backup
        const listItems = document.querySelectorAll(".sidebar-list li");

        listItems.forEach((item) => {
          item.addEventListener("click", () => {
            let isActive = item.classList.contains("active");

            listItems.forEach((el) => {
              el.classList.remove("active");
            });

            if (isActive) item.classList.remove("active");
            else item.classList.add("active");
          });
        });

        const toggleSidebar = document.querySelector(".toggle-sidebar");
        const logo = document.querySelector(".logo-box");
        const sidebar = document.querySelector(".sidebar");
        const hideToggle = document.getElementById("hide-toggle");
        const showToggle = document.getElementById("show-toggle");

        // Function to toggle sidebar
        function toggleSidebarFunction() {
            if (sidebar && hideToggle && showToggle) {
                sidebar.classList.toggle("close");
                
                // Switch icons based on sidebar state
                if (sidebar.classList.contains("close")) {
                    hideToggle.style.display = "none";
                    showToggle.style.display = "block";
                } else {
                    hideToggle.style.display = "block";
                    showToggle.style.display = "none";
                }
            }
        }

        // Add event listeners
        if (toggleSidebar) {
            toggleSidebar.addEventListener("click", toggleSidebarFunction);
        }

        if (logo) {
            logo.addEventListener("click", (e) => {
                e.preventDefault();
                toggleSidebarFunction();
            });
        }

        // Initialize proper icon display
        if (sidebar && sidebar.classList.contains("close")) {
            if (hideToggle) hideToggle.style.display = "none";
            if (showToggle) showToggle.style.display = "block";
        } else {
            if (hideToggle) hideToggle.style.display = "block";
            if (showToggle) showToggle.style.display = "none";
        }
    });
</script>

</body>

</html>