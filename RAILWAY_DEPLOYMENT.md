# Railway Deployment Guide

## Masalah yang Ditemukan
1. Database di Railway belum memiliki data production yang ada di backup_production_data.sql
2. Setelah login, halaman dashboard menampilkan error 500 karena tidak ada data

## Solusi yang Diterapkan

### 1. Script Import Database
Telah dibuat script untuk import database backup:
- **Windows**: `import-to-railway.ps1`
- **Linux/Mac**: `import-to-railway.sh`

### 2. Command Laravel untuk Import Data
Dibuat command Laravel baru: `php artisan db:import-production`
Command ini akan mengimport data production yang penting:
- Users (admin dan user)
- Downtime Classifications 
- Process Names
- Model Items
- Table Productions (sample)
- Table Downtimes (sample)
- Table Defects (sample)

### 3. Update Procfile
Procfile telah diupdate untuk menjalankan import data setelah migrasi:
```
php artisan migrate --force && php artisan db:import-production
```

## Cara Deploy ke Railway

### Opsi 1: Menggunakan Command Laravel (Direkomendasikan)
1. Push code terbaru ke repository
2. Railway akan otomatis menjalankan deployment
3. Procfile akan menjalankan migrasi dan import data secara otomatis

### Opsi 2: Import Manual Database
1. Install Railway CLI: `npm install -g @railway/cli`
2. Login ke Railway: `railway login`
3. Connect ke project: `railway link`
4. Jalankan script import:
   ```powershell
   # Di Windows
   .\import-to-railway.ps1
   
   # Di Linux/Mac  
   ./import-to-railway.sh
   ```

### Opsi 3: Menggunakan Railway Web Interface
1. Buka Railway dashboard
2. Pilih project Anda
3. Masuk ke Database service
4. Gunakan fitur "Connect" untuk mendapatkan connection string
5. Import file SQL menggunakan MySQL client

## Verifikasi Deployment

Setelah deployment selesai, verifikasi dengan:

1. **Login Test**:
   - Email: `admin@email.com`
   - Password: `aaaaa`

2. **Dashboard Test**:
   - Pastikan halaman dashboard tidak error 500
   - Cek apakah data production muncul

3. **Database Test**:
   ```bash
   railway run php artisan tinker
   
   # Test di tinker:
   User::count()
   ModelItem::count()
   TableProduction::count()
   ```

## Troubleshooting

### Error 500 di Dashboard
- Pastikan semua migrasi sudah dijalankan
- Cek apakah data production sudah ada di database
- Lihat log error di Railway dashboard

### Command Import Gagal
- Cek koneksi database di Railway
- Pastikan DATABASE_URL environment variable sudah benar
- Jalankan ulang dengan: `railway run php artisan db:import-production`

### Database Connection Error
- Verifikasi DATABASE_URL di Railway Variables
- Cek apakah database service sudah aktif
- Test koneksi dengan: `railway run php artisan migrate:status`

## Environment Variables yang Diperlukan

Pastikan environment variables berikut sudah di-set di Railway:
- `DATABASE_URL` (otomatis dari Railway MySQL)
- `APP_KEY` 
- `APP_ENV=production`
- `APP_DEBUG=false`

## File Penting

- `Procfile` - Konfigurasi deployment Railway
- `app/Console/Commands/ImportProductionData.php` - Command import data
- `backup_production_data.sql` - Backup data production
- `import-to-railway.ps1` - Script import Windows
- `import-to-railway.sh` - Script import Linux/Mac
