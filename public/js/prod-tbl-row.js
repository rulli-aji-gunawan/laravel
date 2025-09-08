// For calculate total production time


document.addEventListener('DOMContentLoaded', function () {
  const startTimeInput = document.querySelector('input[name="start_time"]');
  const finishTimeInput = document.querySelector('input[name="finish_time"]');
  const showTotalProdTimeInput = document.getElementById('show_total_prod_time');
  if (!startTimeInput || !finishTimeInput || !showTotalProdTimeInput) return;

  const hiddenTotalProdTimeInput = document.createElement('input');
  hiddenTotalProdTimeInput.type = 'hidden';
  hiddenTotalProdTimeInput.name = 'total_prod_time';
  showTotalProdTimeInput.parentNode.insertBefore(hiddenTotalProdTimeInput, showTotalProdTimeInput.nextSibling);

  startTimeInput.addEventListener('change', calculateTotalProductionTime);
  finishTimeInput.addEventListener('change', calculateTotalProductionTime);

  function calculateTotalProductionTime() {
    const startTime = startTimeInput.value;
    const finishTime = finishTimeInput.value;

    if (startTime && finishTime) {
      const start = new Date(`2000-01-01T${startTime}:00`);
      const finish = new Date(`2000-01-01T${finishTime}:00`);

      let timeDiff = finish - start;

      // If finish time is earlier than start time, assume it's the next day
      if (timeDiff < 0) {
        timeDiff += 24 * 60 * 60 * 1000;
      }

      // Convert milliseconds to minutes
      const minutes = Math.floor(timeDiff / 60000);

      // totalProdTimeInput.value = `${minutes} minutes`;
      showTotalProdTimeInput.value = `${minutes} minutes`;

      // Display the formatted value in the visible input
      hiddenTotalProdTimeInput.value = minutes;

    } else {
      hiddenTotalProdTimeInput.value = '';
      showTotalProdTimeInput.value = '';
    }
  }
});

// ================================================================================
// Untuk membuat list Process name dari Database bisa ditampilkan pada row table
let processNames = [];

// Fungsi untuk mengambil data Process name
function fetchProcessNames() {
  fetch('/master-data/process-name/all')
    .then(response => response.json())
    .then(data => {
      processNames = data;
      console.log('Process names loaded:', processNames);
      updateAllProcessSelects();
      return data;
    })
    .catch(error => {
      console.error('Error:', error);
      return [];
    });
}

// Panggil fungsi saat dokumen dimuat
document.addEventListener('DOMContentLoaded', fetchProcessNames);

// Fungsi untuk membuat opsi process names
function createProcessNameOptions() {
  let options = '<option value="">---</option>';
  processNames.forEach(process => {
    options += `<option value="${process.process_name}" data-id="${process.id}">${process.process_name}</option>`;
  });
  return options;
}

function updateAllProcessSelects() {
  $('select[name^="production_problems"][name$="[process_name]"]').each(function () {
    $(this).html(createProcessNameOptions());
  });
}


// ================================================================================
// Untuk membuat list Downtime Category dari Database bisa ditampilkan pada row table

let downtimeCategories = [];

// Fungsi untuk mengambil data downtime categories
function fetchDowntimeCategories() {
  fetch('/master-data/downtime-category/all')
    .then(response => response.json())
    .then(data => {
      downtimeCategories = data;
      console.log('Downtime categories loaded:', downtimeCategories);
      updateAllDowntimeSelects();
      return data;
    })
    .catch(error => {
      console.error('Error:', error);
      return [];
    });
}

// Panggil fungsi saat dokumen dimuat
document.addEventListener('DOMContentLoaded', fetchDowntimeCategories);

// Fungsi untuk membuat opsi downtime categories
function createDowntimeCategoryOptions() {
  let options = '<option value="">---</option>';
  downtimeCategories.forEach(category => {
    options += `<option value="${category.downtime_name}" data-id="${category.id}">${category.downtime_name}</option>`;
  });
  return options;
}

function updateAllDowntimeSelects() {
  $('select[name^="production_problems"][name$="[dt_category]"]').each(function () {
    $(this).html(createDowntimeCategoryOptions());
  });
}

// ================================================================================
// AUTO-FILL DOWNTIME TYPE BERDASARKAN DOWNTIME CATEGORY

function autoFillDowntimeTypeOnCategoryChange() {
  // Event delegation untuk semua select dt_category
  $(document).on('change', 'select[name^="production_problems"][name$="[dt_category]"]', function () {
    let $select = $(this);
    let categoryId = $select.find('option:selected').data('id');
    let $row = $select.closest('tr');
    let $downtimeTypeInput = $row.find('input[name$="[downtime_type]"]');

    if (categoryId) {
      $.ajax({
        url: `/get-downtime-type/${categoryId}`,
        method: 'GET',
        success: function (response) {
          $downtimeTypeInput.val(response.downtime_type);
        },
        error: function () {
          $downtimeTypeInput.val('');
        }
      });
    } else {
      $downtimeTypeInput.val('');
    }
  });
}

// Panggil fungsi ini saat dokumen siap
$(document).ready(function () {
  autoFillDowntimeTypeOnCategoryChange();

  // Tambahkan event listener untuk memanggil updateDowntimeType saat dt_category berubah
  $(document).on('change', 'select[name^="production_problems"][name$="[dt_category]"]', function () {
    updateDowntimeType(this);
  });
});

let downtimeTypes = {};

function updateDowntimeType(selectElement) {
  let categoryId = $(selectElement).find('option:selected').data('id');
  let row = $(selectElement).closest('tr');
  let downtimeTypeInput = row.find('input[name$="[downtime_type]"]');

  if (categoryId) {
    $.ajax({
      url: `/get-downtime-type/${categoryId}`,
      method: 'GET',
      success: function (response) {
        downtimeTypeInput.val(response.downtime_type);
      },
      error: function (xhr) {
        console.error('Gagal mengambil downtime type:', xhr);
        downtimeTypeInput.val('');
      }
    });
  } else {
    downtimeTypeInput.val('');
  }
}

// ================================================================================
// Untuk membuat list Downtime Classification dari Database bisa ditampilkan pada row table

let downtimeClassifications = [];

// Fungsi untuk mengambil data downtime classfication
function fetchDowntimeClassifications() {
  fetch('/master-data/downtime-classification/all')
    .then(response => response.json())
    .then(data => {
      downtimeClassifications = data;
      console.log('Downtime Classifications loaded:', downtimeClassifications);
      updateAllDowntimeClassificationSelects();
      return data;
    })
    .catch(error => {
      console.error('Error:', error);
      return [];
    });
}

// Panggil fungsi saat dokumen dimuat
document.addEventListener('DOMContentLoaded', fetchDowntimeClassifications);

// Fungsi untuk membuat opsi downtime classfications
function createDowntimeClassificationOptions() {
  let options = '<option value="">---</option>';
  downtimeClassifications.forEach(classification => {
    options += `<option value="${classification.downtime_classification}" data-id="${classification.id}">${classification.downtime_classification}</option>`;
  });
  return options;
}

function updateAllDowntimeClassificationSelects() {
  $('select[name^="production_problems"][name$="[dt_classification]"]').each(function () {
    $(this).html(createDowntimeClassificationOptions());
  });
}


// ================================================================================
// Untuk membuat row table otomatis

let rowCount = 0;

function addRow() {
  rowCount++;
  console.log('Tambah row:', rowCount);
  let processNameOptions = processNames.length ? createProcessNameOptions() : '<option value="">Loading...</option>';
  let dtCategoryOptions = downtimeCategories.length ? createDowntimeCategoryOptions() : '<option value="">Loading...</option>';
  let dtClassificationOptions = downtimeClassifications.length ? createDowntimeClassificationOptions() : '<option value="">Loading...</option>';

  let newRow = `
    <tr id="row${rowCount}">
        <td class="time-problem">
          <input type="time" name="production_problems[${rowCount}][time_from]" required>
        </td>
        <td class="time-problem">
          <input type="time" name="production_problems[${rowCount}][time_until]" required>
        </td>
        <td class="time-problem">
          <input type="text" id="total-problem-time" name="production_problems[${rowCount}][total_time]" readonly>
        </td>

        <td>
            <select name="production_problems[${rowCount}][process_name]" required>
              ${processNameOptions}
            </select>
        </td>
        <td>
            <select name="production_problems[${rowCount}][dt_category]" required>
              <option>${dtCategoryOptions}</option>
            </select>
        </td>
        <td class="row-hide">
          <input type="text" name="production_problems[${rowCount}][downtime_type]" readonly>
        </td>
        <td>
            <select name="production_problems[${rowCount}][dt_classification]" required>
              <option>${dtClassificationOptions}</option>
            </select>
        </td>
        <td>
            <textarea rows="1" name="production_problems[${rowCount}][problem_description]"
                placeholder="...input problem description"></textarea>
        </td>
        <td>
            <textarea rows="1" name="production_problems[${rowCount}][root_cause]"
                placeholder="...input root causes analysis"></textarea>
        </td>
        <td>
            <textarea rows="1" name="production_problems[${rowCount}][counter_measure]"
                placeholder="...input action or countermeasure"></textarea>
        </td>
        <td>
            <select name="production_problems[${rowCount}][pic]">
                <option value="">---</option>
                <option value="press">Press</option>
                <option value="tooling">Tooling</option>
                <option value="mtc">MTC</option>
                <option value="mh">MH</option>
                <option value="pe stamping">PE Stamping</option>
                <option value="supplier">Supplier</option>
                <option value="other">Other</option>
            </select>
        </td>
        <td>
            <select name="production_problems[${rowCount}][status]">
                <option value="">---</option>
                <option value="open">Open</option>
                <option value="monitoring">Monitoring</monitoring>
                <option value="close">Close</close>
            </select>
        </td>
        <td>
          <span class="problem-picture-link" name="production_problems[${rowCount}][problem_picture]">
            <button type="button" class="btn-upload-picture" onclick="showUploadForm(this)">
              <i class="bx bx-camera"></i>
            </button>
            <input type="file" name="problem_pictures[]" accept="image/*" capture="environment" style="display:none;" onchange="handlePictureUpload(this)">
          </span>
        </td>
        <td>
          <button onclick="deleteRow(${rowCount})" class="btn-remove-row">Remove</button>
        </td>
    </tr>
`;

  $('#tbl-prod-problem tbody').append(newRow);
}


function showUploadForm(btn) {
  const fileInput = btn.nextElementSibling;
  fileInput.click();
}

// function handlePictureUpload(input) {
//   const file = input.files[0];
//   if (file) {
//     const row = $(input).closest('tr');
//     const rowIndex = row.index() + 1;
//     const filename = `pic-${rowIndex}.jpg`;
//     const linkSpan = row.find('.problem-picture-link');

//     // Buat preview gambar base64
//     const reader = new FileReader();
//     reader.onload = function (e) {
//       // Tampilkan nama file sebagai link, simpan base64 di data-img
//       linkSpan.html(`<a href="#" class="problem-img-link" data-img="${e.target.result}">${filename}</a>`);
//     };
//     reader.readAsDataURL(file);
//   }
// }

function handlePictureUpload(input) {
  const file = input.files[0];
  if (file) {
    const row = $(input).closest('tr');
    const rowId = row.attr('id').replace('row', '');
    const linkSpan = row.find('.problem-picture-link');
    const reader = new FileReader();

    reader.onload = function (e) {
      // Tambahkan tombol delete di samping preview
      linkSpan.html(`
        <div class="img-preview-container">
          <a href="#" class="problem-img-link" data-img="${e.target.result}">
            img  
          </a>
          <button type="button" class="btn-delete-image" onclick="deleteImage(this, ${rowId})">
            <i class="bx bx-trash"></i>
          </button>
          <input type="hidden" name="production_problems[${rowId}][problem_picture_data]" value="${e.target.result}">
          <input type="hidden" name="production_problems[${rowId}][problem_picture_name]" value="${file.name}">
        </div>
      `);
    };

    reader.readAsDataURL(file);
  }
}

function showUploadForm(btn) {
  const fileInput = btn.nextElementSibling;
  fileInput.click();
}

function previewProblemPicture(input) {
  const img = input.nextElementSibling;
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      img.src = e.target.result;
      img.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Fungsi untuk menghapus gambar dengan konfirmasi
function deleteImage(btn, rowId) {
  // Tampilkan dialog konfirmasi
  if (!confirm("Apakah Anda yakin ingin menghapus foto ini?")) {
    return false; // Batal hapus jika pengguna memilih "Cancel"
  }

  // Dapatkan elemen parent dari tombol (span.problem-picture-link)
  const linkSpan = $(btn).closest('.problem-picture-link');

  // Kosongkan span
  linkSpan.empty();

  // Reset file input untuk baris ini
  const row = $(`#row${rowId}`);
  const fileInput = row.find('input[type="file"]');
  fileInput.val('');  // Reset input file

  // Tampilkan pesan bahwa tidak ada gambar yang dipilih
  linkSpan.html(`
    <button type="button" class="btn-upload-picture" onclick="showUploadForm(this)">
      <i class="bx bx-camera"></i>
    </button>
    <input type="file" name="problem_pictures[]" accept="image/*" capture="environment" style="display:none;" onchange="handlePictureUpload(this)">
  `);

  return false; // Prevent event bubbling
}

function deleteRow(rowId) {
  $(`#row${rowId}`).remove();
}

function calculateTotalDownTime(timeFrom, timeUntil) {
  if (timeFrom && timeUntil) {
    // Buat objek Date dengan format yang benar
    const start = new Date(`1970-01-01T${timeFrom}:00`);
    const finish = new Date(`1970-01-01T${timeUntil}:00`);

    let diff = finish - start;

    // If finish time is earlier than start time, assume it's the next day
    if (diff < 0) {
      diff += 24 * 60 * 60 * 1000;
    }

    // Convert milliseconds to minutes
    const totalTimes = Math.floor(diff / 60000);
    return `${totalTimes}`;
  } else {
    return '';
  }
}

$(document).ready(function () {
  addRow();  // Tambahkan baris pertama segera

  $('#btn-addRow').click(addRow);

  $(document).on('input', 'input[name^="production_problems"][name$="[time_from]"], input[name^="production_problems"][name$="[time_until]"]', function () {
    let row = $(this).closest('tr');
    let timeFrom = row.find('input[name$="[time_from]"]').val();
    let timeUntil = row.find('input[name$="[time_until]"]').val();
    let totalTimeInput = row.find('input[name$="[total_time]"]');

    if (timeFrom && timeUntil) {
      let totalTime = calculateTotalDownTime(timeFrom, timeUntil);
      totalTimeInput.val(totalTime);
    }
  });
});


function saveData() {
  let productionProblems = [];

  $('#tbl-prod-problem tbody tr').each(function () {
    let row = $(this);

    let problem = {
      time_from: row.find('input[name$="[time_from]"]').val(),
      time_until: row.find('input[name$="[time_until]"]').val(),
      total_time: row.find('input[name$="[total_time]"]').val(),
      process_name: row.find('select[name$="[process_name]"] option:selected').text(),
      dt_category: row.find('select[name$="[dt_category]"] option:selected').text(),
      dt_category_id: selectedOption.val(), // Mengambil value (ID) dari option
      downtime_type: row.find('input[name$="[downtime_type]"]').val(),
      dt_classification: row.find('select[name$="[dt_classification]"]').val(),
      problem_description: row.find('textarea[name$="[problem_description]"]').val(),
      root_cause: row.find('textarea[name$="[root_cause]"]').val(),
      counter_measure: row.find('textarea[name$="[counter_measure]"]').val(),
      pic: row.find('select[name$="[pic]"]').val(),
      status: row.find('select[name$="[status]"]').val(),
    };

    if (validateProblemData(problem)) {
      productionProblems.push(problem);
    }

    // productionProblems.push(problem);
  });

  if (productionProblems.length === 0) {
    alert('Tidak ada data valid untuk disimpan');
    return;

  }

  alert('Data produksi berhasil disimpan');


  // Debug: Lihat data sebelum dikirim
  console.log('Data yang akan dikirim:', productionProblems);

  $.ajax({
    url: '/input-report/production',
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'Content-Type': 'application/json'
    },
    data: JSON.stringify({
      production_problems: productionProblems
    }),
    processData: false, // Penting jika mengirim JSON
    contentType: 'application/json',
    dataType: 'json',
    success: function (response) {
      console.log('Respons dari server:', response);
      alert('Data berhasil disimpan');
    },

    error: function (xhr, status, error) {
      console.error('Error:', error);
      let errorMessage = 'Terjadi kesalahan saat menyimpan data';
      if (xhr.responseJSON && xhr.responseJSON.errors) {
        errorMessage = Object.values(xhr.responseJSON.errors).join('\n');
      }
      alert(errorMessage);
    }

  });
}

function validateProblemData(problem) {
  // Validasi dasar di sisi client
  return problem.time_from &&
    problem.time_until &&
    problem.process_name &&
    problem.dt_category &&
    problem.problem_description;
}