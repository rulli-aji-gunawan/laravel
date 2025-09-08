# Script PowerShell untuk import database backup ke Railway
# Pastikan Railway CLI sudah terinstall dan login

Write-Host "Starting database import to Railway..." -ForegroundColor Green

# Get Railway database URL
try {
    $RAILWAY_DB_URL = railway variables get DATABASE_URL
    if ([string]::IsNullOrEmpty($RAILWAY_DB_URL)) {
        throw "DATABASE_URL not found"
    }
} catch {
    Write-Host "Error: DATABASE_URL not found. Make sure you're connected to the right Railway project." -ForegroundColor Red
    exit 1
}

# Parse the database URL
# Format: mysql://user:password@host:port/database
$urlPattern = "mysql://([^:]+):([^@]+)@([^:]+):(\d+)/([^\?]+)"
if ($RAILWAY_DB_URL -match $urlPattern) {
    $DB_USER = $Matches[1]
    $DB_PASS = $Matches[2]
    $DB_HOST = $Matches[3]
    $DB_PORT = $Matches[4]
    $DB_NAME = $Matches[5]
} else {
    Write-Host "Error: Unable to parse DATABASE_URL" -ForegroundColor Red
    exit 1
}

Write-Host "Database Host: $DB_HOST" -ForegroundColor Yellow
Write-Host "Database Port: $DB_PORT" -ForegroundColor Yellow
Write-Host "Database Name: $DB_NAME" -ForegroundColor Yellow
Write-Host "Database User: $DB_USER" -ForegroundColor Yellow

# Check if mysql client is available
$mysqlPath = Get-Command mysql -ErrorAction SilentlyContinue
if (-not $mysqlPath) {
    Write-Host "Error: MySQL client not found. Please install MySQL client or use Railway's web interface." -ForegroundColor Red
    exit 1
}

# Import the backup file
Write-Host "Importing backup_production_data.sql..." -ForegroundColor Green
$arguments = @(
    "-h", $DB_HOST,
    "-P", $DB_PORT,
    "-u", $DB_USER,
    "-p$DB_PASS",
    $DB_NAME
)

try {
    Get-Content "backup_production_data.sql" | mysql $arguments
    Write-Host "Database import completed successfully!" -ForegroundColor Green
} catch {
    Write-Host "Database import failed: $_" -ForegroundColor Red
    exit 1
}
