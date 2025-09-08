# Railway Manual Setup - Tanpa Admin Access

## Metode 1: Via Railway Web Dashboard (Direkomendasikan)

### A. Akses Railway Dashboard
1. Buka https://railway.app
2. Login ke account Anda
3. Pilih project Laravel

### B. Check Deployment Status
1. Klik service "web" (Laravel app)
2. Check tab "Deployments" - pastikan deploy terbaru sukses
3. Check tab "Variables" - pastikan environment variables ada

### C. Run Commands via Web Terminal
1. Di tab "Deployments", klik deployment terbaru
2. Scroll ke bawah, cari "Open Terminal" atau "Run Command"
3. Jalankan command berikut satu per satu:

```bash
# Check database connection
php artisan migrate:status

# Setup minimal data
php artisan db:setup-minimal

# Test database
php artisan db:test

# Check data
php artisan tinker
```

### D. Di Tinker, test:
```php
User::count()
TableProduction::count()
ModelItem::count()
exit
```

## Metode 2: Via GitHub Actions (Otomatis)

File GitHub Actions sudah dibuat di `.github/workflows/deploy.yml`

### Setup Railway Token:
1. Di Railway Dashboard, klik Profile → Account Settings
2. Klik "Tokens" tab
3. Generate new token
4. Copy token tersebut

### Setup GitHub Secret:
1. Di GitHub repository, klik Settings
2. Klik "Secrets and variables" → Actions
3. Klik "New repository secret"
4. Name: `RAILWAY_TOKEN`
5. Value: paste token dari Railway
6. Save

Setelah itu, setiap push ke main akan otomatis deploy dan setup data.

## Metode 3: Manual Database Import

Jika command tidak jalan, import manual:

### A. Get Database Credentials:
1. Di Railway Dashboard → MySQL service
2. Tab "Connect" 
3. Copy "Database URL" atau individual credentials

### B. Import via Web Tool:
1. Buka phpMyAdmin online atau Adminer
2. Connect dengan credentials Railway
3. Import file SQL manual

## Troubleshooting Tanpa Admin

### Jika Node.js/NPM tidak tersedia:
- Gunakan Railway web interface saja
- Download Node.js portable version (tidak perlu install)
- Atau gunakan GitHub Codespaces (gratis)

### Jika Git push gagal:
- Pastikan Git sudah dikonfigurasi user
- Gunakan GitHub Desktop (tidak perlu admin)
- Atau edit files langsung di GitHub web

### Alternatif Tools (Portable):
- **Git**: Git Portable
- **Node.js**: Node.js Portable  
- **Text Editor**: VS Code Portable
- **Database Tool**: phpMyAdmin portable

## Current Status Check

Untuk check status deployment tanpa CLI:

1. **Buka URL Railway app di browser**
2. **Login dengan admin@email.com / aaaaa**
3. **Check apakah:**
   - Layout CSS sudah normal ✓
   - Dashboard tampil data atau "No Production Data Available"
   - Sidebar navigation berfungsi
   - No error 500

## Next Steps

1. ✅ **Check deployment via Railway web dashboard**
2. ✅ **Test login dan layout**
3. ✅ **Jika masih "No Production Data", run setup via web terminal**
4. ✅ **Setup GitHub Actions token untuk future automated deployment**

Semua bisa dilakukan tanpa akses administrator! 🎉
