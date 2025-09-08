# Troubleshooting Error 500 Dashboard - Railway Deployment

## Masalah yang Diidentifikasi

1. **Database Kosong**: Dashboard controller mencoba mengambil data dari tabel yang kosong
2. **Null Value Handling**: Kode tidak menangani nilai null dengan baik
3. **Array Explode Error**: Error ketika memproses fy_n yang null/kosong
4. **Chart Initialization**: JavaScript chart gagal ketika data kosong

## Perbaikan yang Dilakukan

### 1. DashboardController.php
- ✅ Tambah pengecekan data kosong di awal method
- ✅ Tambah try-catch untuk error handling
- ✅ Gunakan COALESCE untuk handling null values
- ✅ Perbaiki logic explode untuk fy_n
- ✅ Return view dengan data kosong jika error
- ✅ Tambah whereNotNull untuk field penting
- ✅ Filter out null values dari result

### 2. dashboard.blade.php
- ✅ Tambah null checking untuk array operations
- ✅ Tambah conditional untuk chart initialization
- ✅ Tambah empty state untuk no data
- ✅ Safe array operations dengan empty checking

### 3. Commands
- ✅ Buat TestDatabaseConnection command
- ✅ Improved error handling di ImportProductionData
- ✅ Tambah sample data creation

## Cara Test dan Deploy

### 1. Local Testing (jika PHP tersedia)
```bash
# Test database connection
php artisan db:test

# Run migration dan import
php artisan migrate
php artisan db:import-production

# Test dashboard
php artisan serve
```

### 2. Railway Deployment
```bash
# Commit changes
git add .
git commit -m "Fix: Dashboard error 500 - Add null handling and empty data checks"
git push origin main
```

### 3. Railway Commands (setelah deploy)
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login dan connect
railway login
railway link

# Test database
railway run php artisan db:test

# Check migration status
railway run php artisan migrate:status

# Run import if needed
railway run php artisan db:import-production

# Check logs
railway logs
```

## Expected Fixes

### Sebelum Perbaikan:
- ❌ Error 500 karena null values
- ❌ Array explode error pada fy_n
- ❌ JavaScript chart error dengan data kosong

### Setelah Perbaikan:
- ✅ Dashboard load tanpa error
- ✅ Tampil "No Production Data Available" jika data kosong
- ✅ Chart tidak error pada data kosong
- ✅ Filter dropdown handle empty values
- ✅ Proper error logging

## Data Requirements

Untuk dashboard berfungsi normal, pastikan ada data di:
1. `users` table (minimal 1 admin user)
2. `table_productions` table (minimal 1 record)
3. `model_items` table (untuk dropdown)
4. `process_names` table (untuk dropdown)
5. `downtime_classifications` table (untuk dropdown)

## Monitoring

Setelah deploy, check:
1. Dashboard load tanpa error 500
2. Login berhasil dengan admin@email.com / aaaaa
3. Charts tampil atau show empty state
4. No JavaScript errors di browser console
5. Railway logs tidak show PHP errors

## Fallback Options

Jika masih error:
1. Check Railway logs: `railway logs`
2. Check database connection: `railway run php artisan db:test`
3. Create minimal data: `railway run php artisan tinker` 
4. Manual SQL import via Railway dashboard
5. Debug with: `railway run php artisan tinker`
