# Manual Import Guide for Railway

Jika Railway CLI tidak tersedia, gunakan cara manual ini untuk import data:

## Opsi 1: Via Railway Dashboard

1. **Buka Railway Project Dashboard**
   - Login ke railway.app
   - Pilih project Laravel Anda
   
2. **Akses Database**
   - Klik service "MySQL" atau "PostgreSQL" 
   - Klik tab "Connect"
   - Copy connection string

3. **Import via phpMyAdmin/Adminer**
   - Buka phpMyAdmin atau tool database favorit
   - Connect menggunakan credentials dari Railway
   - Import file `backup_production_data.sql`

## Opsi 2: Via Railway Web Terminal

1. **Buka Project di Railway**
2. **Klik service Laravel**
3. **Pilih tab "Deploy Logs"**
4. **Scroll ke bawah dan klik "Open Terminal"**
5. **Jalankan command:**
   ```bash
   php artisan db:setup-minimal
   ```

## Opsi 3: Via GitHub Actions (Otomatis)

File .github/workflows/deploy.yml sudah dibuat untuk otomatis setup data.

## Verifikasi Import Berhasil

Setelah import, cek:
1. Login dengan `admin@email.com` / `aaaaa`
2. Dashboard menampilkan data (bukan "No Production Data Available")
3. Chart muncul dengan data sample

## Troubleshooting

Jika import gagal:
1. Check Railway logs untuk error
2. Pastikan migration sudah jalan
3. Check database connection
4. Manual run: `php artisan db:setup-minimal`

## Data yang Diimport

- 1 Admin User (admin@email.com)
- Master Data: Downtime Classifications, Process Names, Model Items
- 2 Sample Production Records dengan data realistic
- Sample Downtime dan Defect Records
- Data untuk chart functionality
