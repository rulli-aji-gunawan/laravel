@props(['models'])

<head>
    <link rel="stylesheet" href="{{ secure_asset('css/input-production-layout.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<!-- ============= Home Section =============== -->

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

<section class="home">
    <div class="toggle-sidebar">
        <i class='bx bx-x-circle' id="hide-toggle"></i>
        <i class='bx bx-menu' id="show-toggle"></i>
    </div>
    </div>
    <div class="container">

        {{-- ===== Form Untuk Input Data Utama Report Produksi ===== --}}
        <form class="report-form" action="{{ route('input.production') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="production-main-row">
                <div class="production-form-col">
                    <table id="tbl-form-input-data-production">
                        <tr>
                            <th><label for="reporter" class="td-right-gen">Reporter :</label></th>
                            <td>
                                <select id="reporter" name="reporter" required>
                                    <option value="">Select Reporter</option>
                                    <option value="Joni">Joni</option>
                                    <option value="Kosim">Kosim</option>
                                    <option value="Sudarto">Sudarto</option>
                                    <option value="Eman">Eman</option>
                                </select>
                            </td>
                            <td class="td-right-gen">
                                <label for="group">Group :</label>
                            </td>
                            <td>
                                <select id="group" name="group" required>
                                    <option value="">-</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                </select>
                            </td>
                            <th rowspan="2" class="td-right-gen">
                                <label for="line">Press Line :</label>
                            </th>
                            <td rowspan="2">
                                <select id="line" name="line" required>
                                    <option value="">-</option>
                                    <option value="Line-A">Line-A</option>
                                    <option value="Line-B">Line-B</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="date" class="td-right-gen">Date :</label></th>
                            <td>
                                <input type="date" id="date" name="date" required>
                            </td>
                            <td class="td-right-gen">
                                <label for="shift">Shift :</label>
                            </td>
                            <td>
                                <select name="shift" id="shift" required>
                                    <option value="">-</option>
                                    <option value="day">Day</option>
                                    <option value="night">Night</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="start_time" class="td-right-gen">Start Time :</label>
                            </th>
                            <td>
                                <input type="time" id="production-time" name="start_time" required>
                            </td>
                            <th><label for="model" class="td-right-gen">Model :</label></th>
                            <td>
                                <select name="model" id="model" required>
                                    <option value="">---</option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model }}">{{ $model }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="td-right-gen">
                                <label for="total_prod_time">Total Time :</label>
                            </td>
                            <td>
                                <input type="text" name="show_total_prod_time" id="show_total_prod_time"
                                    placeholder="...minutes">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="finish_time" class="td-right-gen">Finish Time :</label>
                            </th>
                            <td>
                                <input type="time" id="production-time" name="finish_time" required>
                            </td>
                            <td class="td-right-gen">
                                <label class="model_year" for="model_year">Model Year :</label>
                            </td>
                            <td>
                                <select name="model_year" id="model_year" required>
                                    <option value="">----</option>
                                </select>
                            </td>
                            <td class="td-right-gen">
                                <label for="spm">SPM :</label>
                            </td>
                            <td>
                                <input type="number" name="spm" id="spm" min="00.0" max="15.0"
                                    step="00.1" value="00.0" placeholder="00.0" oninput="limitInputLength(this, 4)"
                                    required>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="item_name" class="td-right-gen">Item Name :</label></th>
                            <td colspan="3" class="items">
                                <select name="item_name" id="item_name" required>
                                    <option value="">MODEL-PNL,ITEM NAME</option>
                                </select>
                            </td>
                            <td class="td-right-gen">
                                <label for="coil_no">Coil No. :</label>
                            </td>
                            <td colspan="2">
                                <input type="text" name="coil_no" id="coil_no" placeholder="...input coil no."
                                    required>
                            </td>
                        </tr>
                        <tr class="tbl-qty">
                            <th class="td-right-gen">Qty :</th>
                            <td class="label-act-qty" id="plan">
                                <p>Plan</p>
                            </td>
                            <td class="label-act-qty" id="ok">
                                <p>OK</p>
                            </td>
                            <td class="label-act-qty" id="rework">
                                <p>Rework</p>
                            </td>
                            <td class="label-act-qty" id="ng">
                                <p>NG Scrap</p>
                            </td>
                            <td class="label-act-qty" id="sample">
                                <p>Sample</p>
                            </td>
                        </tr>
                        <tr class="tbl-qty">
                            <th>
                                <select class="which-side-a" name="which-plan_a" id="which-side-a" required>
                                    <option value="single">Single</option>
                                    <option value="lh">LH</option>
                                    <option value="otr">OTR</option>
                                    <option value="tg-Otr">T/G</option>
                                </select>
                            </th>
                            <td>
                                <label for="plan_a" class="td-right-a">A-side :</label>
                                <input class="input-qty" type="number" id="plan_a" name="plan_a"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                            <td>
                                <label for="ok_a" class="td-right-a">A-side :</label>
                                <input class="input-qty" type="number" id="ok_a" name="ok_a"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                            <td>
                                <label for="rework_a" class="td-right-a">A-side :</label>
                                <input class="input-qty" type="number" id="rework_a" name="rework_a"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                            <td>
                                <label for="scrap_a" class="td-right-a">A-side :</label>
                                <input class="input-qty" type="number" id="scrap_a" name="scrap_a"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                            <td>
                                <label for="sample_a" class="td-right-a">A-side :</label>
                                <input class="input-qty" type="number" id="sample_a" name="sample_a"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                        </tr>
                        <tr class="tbl-qty">
                            <th>
                                <select class="which-side-b" name="which-plan_b" id="which-side-b" required>
                                    <option value="---">---</option>
                                    <option value="rh">RH</option>
                                    <option value="inr">INR</option>
                                    <option value="spoiler">S/P</option>
                                    <option value="rne">RNE</option>
                                </select>
                            </th>
                            <td>
                                <label for="plan_b" class="td-right-b">B-side :</label>
                                <input class="input-qty" type="number" id="plan_b" name="plan_b"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                            <td>
                                <label for="ok_b" class="td-right-b">B-side :</label>
                                <input class="input-qty" type="number" id="ok_b" name="ok_b"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                            <td>
                                <label for="rework_b" class="td-right-b">B-side :</label>
                                <input class="input-qty" type="number" id="rework_b" name="rework_b"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                            <td>
                                <label for="scrap_b" class="td-right-b">B-side :</label>
                                <input class="input-qty" type="number" id="scrap_b" name="scrap_b"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                            <td>
                                <label for="sample_b" class="td-right-b">B-side :</label>
                                <input class="input-qty" type="number" id="sample_b" name="sample_b"
                                    min="0" max="999" oninput="limitInputLength(this, 3)"
                                    placeholder=".... pcs" required>
                            </td>
                        </tr>
                        <tr class="tbl-exp" class="tbl-qty-exp">

                        <tr class="tbl-exp">
                            <th class="td-right-gen">Rework :</th>
                            <td colspan="5">
                                <input type="text" name="rework_exp" id="qty_exp"
                                    placeholder="...input defect name">
                            </td>
                        </tr>
                        <tr class="tbl-exp">
                            <th class="td-right-gen">NG Process :</th>
                            <td colspan="5">
                                <input type="text" name="scrap_exp" id="qty_exp"
                                    placeholder="...input defect name">
                            </td>
                        </tr>
                        <tr class="tbl-exp">
                            <th class="td-right-gen">Trial & Sample :</th>
                            <td colspan="5">
                                <input type="text" name="trial_sample_exp" id="qty_exp"
                                    placeholder="...input trial or sample purpose">
                            </td>
                        </tr>
                        </tr>
                    </table>
                    <!-- Form utama sudah ada di atas, jadi kosong di sini -->
                </div>
                <div class="area-mapping-col">
                    <div class="area-mapping-image">
                        <table id="area-matrix">
                            <thead>
                                <tr>
                                    <th></th>
                                    @for ($col = 0; $col < 16; $col++)
                                        <th>{{ chr(65 + $col) }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @for ($row = 1; $row <= 16; $row++)
                                    <tr>
                                        <th>{{ $row }}</th>
                                        @for ($col = 0; $col < 16; $col++)
                                            <td class="matrix-cell"
                                                data-area="{{ chr(65 + $col) }}{{ $row }}">
                                                <!-- Area cell -->
                                            </td>
                                        @endfor
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                        <img id="product-image" src="" alt="Product Image" alt="Product Image">
                    </div>
                </div>
                <div class="area-mapping-table">
                    <table id="area-defect-table">
                        <thead>
                            <tr>
                                <th id="area">Area</th>
                                <th id="defect-name">Defect</th>
                                <th id="defect-qty-a">Qty-a</th>
                                <th id="defect-qty-b">Qty-b</th>
                                <th id="defect-category">Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row akan ditambah lewat JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <h3>Detail Description and Problem </h3>

            {{-- ===== Table untuk detail problem produksi ===== --}}

            <table id="tbl-prod-problem">
                <thead>
                    <tr>
                        <th>
                            <p class="header-a">From</p>
                        </th>
                        <th>
                            <p class="header-a">Until</p>
                        </th>
                        <th>
                            <p class="header-a2">Total</p>
                        </th>
                        <th>
                            <p class="header-b">Process</p>
                        </th>
                        <th>
                            <p class="header-b">DT Category</p>
                        </th>
                        <th class="header-hide">
                            <p class="header-b">DT Type</p>
                        </th>
                        <th>
                            <p class="header-b">DT Classification</p>
                        </th>
                        <th>
                            <p class="header-c">Problem Description</p>
                        </th>
                        <th>
                            <p class="header-c">Root Causes</p>
                        </th>
                        <th>
                            <p class="header-d">Action/Countermeasure</p>
                        </th>
                        <th>
                            <p class="header-a">PIC</p>
                        </th>
                        <th>
                            <p class="header-a">Status</p>
                        </th>
                        <th>
                            <p class="header-e">Picture</p>
                        </th>
                        <th>
                            <p class="header-e">Action</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Row akan ditambah lewat JS -->
                </tbody>

            </table>

            <div class="btn-row">
                <button id="btn-addRow">Add Row</button>
            </div>

            <div class="submit-btn">
                <button type="submit" id="submit">Save</button>
                <button type="button" id="cancel"
                    onclick="window.location.href='{{ route('dashboard') }}'">Cancel</button>
            </div>

        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectElementA = document.querySelector('.which-side-a');
                const selectElementB = document.querySelector('.which-side-b');
                const labelsA = document.querySelectorAll('label.td-right-a');
                const labelsB = document.querySelectorAll('label.td-right-b');

                function updateLabelsA(value) {
                    labelsA.forEach(label => {
                        switch (value) {
                            case 'single':
                                label.textContent = 'Single :';
                                break;
                            case 'lh':
                                label.textContent = 'LH :';
                                break;
                            case 'otr':
                                label.textContent = 'OTR :';
                                break;
                            case 'tg-Otr':
                                label.textContent = 'T/G :';
                                break;
                            default:
                                label.textContent = 'A-side :';
                        }
                    });
                }


                function updateLabelsB(value) {
                    labelsB.forEach(label => {
                        switch (value) {
                            case '---':
                                label.textContent = '--- :';
                                break;
                            case 'rh':
                                label.textContent = 'RH :';
                                break;
                            case 'inr':
                                label.textContent = 'INR :';
                                break;
                            case 'spoiler':
                                label.textContent = 'Spoiler :';
                                break;
                            case 'rne':
                                label.textContent = 'RNE :';
                                break;
                            default:
                                label.textContent = '--- :';
                        }
                    });
                }

                // Set initial label text based on default select value
                updateLabelsA(selectElementA.value);
                updateLabelsB(selectElementB.value);

                // Update labels when select value changes
                selectElementA.addEventListener('change', function() {
                    updateLabelsA(this.value);
                });
                selectElementB.addEventListener('change', function() {
                    updateLabelsB(this.value);
                });
            });


            document.getElementById('model').addEventListener('change', function() {
                let model = this.value;
                let yearSelect = document.getElementById('model_year');
                let itemSelect = document.getElementById('item_name');

                // Reset options
                yearSelect.innerHTML = '<option value="">----</option>';
                itemSelect.innerHTML = '<option value="">MODEL-PNL,ITEM NAME</option>';

                if (model) {
                    // Fetch years for selected model
                    fetch(`/api/years/${model}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(year => {
                                let option = new Option(year, year);
                                yearSelect.add(option);
                            });
                            console.log('Years fetched for model:', data);
                        });

                    // Fetch items for selected model
                    fetch(`/api/items/${model}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(item => {
                                // item harus punya id, model_code, model_year, item_name
                                let option = document.createElement('option');
                                // option.value = item.id; // value = id
                                option.value = `${item.model_code}-${item.item_name}`;
                                option.text = `${item.model_code}-${item.item_name}`;
                                option.setAttribute('data-model_code', item.model_code);
                                option.setAttribute('data-model_year', item.model_year);
                                option.setAttribute('data-item_name', item.item_name);
                                option.setAttribute('data-picture', item.product_picture);
                                itemSelect.add(option);
                            });
                            console.log('Items fetched for model:', data);
                        });
                }
            });
        </script>

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

</section>

<div id="imgModal"
    style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
    <span id="closeImgModal">&times;</span>
    <img id="imgModalContent" src="">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('item_name').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const picture = selectedOption.getAttribute('data-picture');
            const img = document.getElementById('product-image');
            if (picture) {
                img.src = `/images/products/${encodeURIComponent(picture)}`;
            } else {
                img.src = '';
            }
            img.onerror = function() {
                this.src = '';
            };
        });

        const selectedAreas = {};
        document.querySelectorAll('.matrix-cell').forEach(cell => {
            cell.addEventListener('click', function() {
                const area = this.getAttribute('data-area');
                if (selectedAreas[area]) {
                    // Unselect
                    this.classList.remove('selected');
                    delete selectedAreas[area];
                    const row = document.getElementById('area-row-' + area);
                    if (row) row.remove();
                } else {
                    // Select
                    this.classList.add('selected');
                    selectedAreas[area] = true;
                    // Tambah row di tabel
                    const tbody = document.querySelector('#area-defect-table tbody');
                    const row = document.createElement('tr');
                    row.id = 'area-row-' + area;
                    row.innerHTML = `
                    <td><input type="hidden" id="area" name="defect_areas[]" value="${area}">${area}</td>
                    <td><input type="text" id="defect-name" name="defect_names[]" placeholder="Defect" required></td>
                    <td><input type="number" id="defect-qty-a" name="defect_qtys_a[]" min="1" placeholder="Qty-a" required></td>
                    <td><input type="number" id="defect-qty-b" name="defect_qtys_b[]" min="1" placeholder="Qty-b" ></td>
                    <td>
                        <select id="defect-category" name="defect_categories[]" required>
                            <option value="" disabled selected>Category</option>
                            <option value="inline">in-Line</option>
                            <option value="outline">out-Line</option>
                            <option value="scrap">Scrap</option>
                        </select>
                    </td>
                `;
                    tbody.appendChild(row);
                }
            });
        });
    });

    $(document).on('click', '.problem-img-link', function(e) {
        e.preventDefault();
        const imgSrc = $(this).data('img');
        $('#imgModalContent').attr('src', imgSrc);
        $('#imgModal').fadeIn(200);
    });

    $('#closeImgModal, #imgModal').on('click', function(e) {
        // Hanya tutup jika klik di background atau tombol close
        if (e.target.id === 'imgModal' || e.target.id === 'closeImgModal') {
            $('#imgModal').fadeOut(200);
            $('#imgModalContent').attr('src', '');
        }
    });
</script>

<script>
    function showFormattedDate(dateStr) {
        if (!dateStr) {
            document.getElementById('formatted-date').textContent = '';
            return;
        }
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const d = new Date(dateStr);
        const day = String(d.getDate()).padStart(2, '0');
        const month = months[d.getMonth()];
        const year = d.getFullYear();
        document.getElementById('formatted-date').textContent = `${day}-${month}-${year}`;
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cegah submit form saat tekan Enter di input, select, textarea dalam form
        $('.report-form').on('keydown', 'input, select, textarea', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });
    });
</script>

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

<script>
    // Cek apakah ada session success dan tampilkan alert
    @if (session('success'))
        alert("{{ session('success') }}");
    @endif
</script>
