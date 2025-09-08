#!/bin/bash

# Script untuk import database backup ke Railway
# Pastikan Railway CLI sudah terinstall dan login

echo "Starting database import to Railway..."

# Get Railway database URL
RAILWAY_DB_URL=$(railway variables get DATABASE_URL)

if [ -z "$RAILWAY_DB_URL" ]; then
    echo "Error: DATABASE_URL not found. Make sure you're connected to the right Railway project."
    exit 1
fi

# Extract database connection details from URL
# Format: mysql://user:password@host:port/database
DB_USER=$(echo $RAILWAY_DB_URL | sed -n 's/.*:\/\/\([^:]*\):.*/\1/p')
DB_PASS=$(echo $RAILWAY_DB_URL | sed -n 's/.*:\/\/[^:]*:\([^@]*\)@.*/\1/p')
DB_HOST=$(echo $RAILWAY_DB_URL | sed -n 's/.*@\([^:]*\):.*/\1/p')
DB_PORT=$(echo $RAILWAY_DB_URL | sed -n 's/.*:\([0-9]*\)\/.*/\1/p')
DB_NAME=$(echo $RAILWAY_DB_URL | sed -n 's/.*\/\([^?]*\).*/\1/p')

echo "Database Host: $DB_HOST"
echo "Database Port: $DB_PORT"
echo "Database Name: $DB_NAME"
echo "Database User: $DB_USER"

# Import the backup file
echo "Importing backup_production_data.sql..."
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS $DB_NAME < backup_production_data.sql

if [ $? -eq 0 ]; then
    echo "Database import completed successfully!"
else
    echo "Database import failed!"
    exit 1
fi
