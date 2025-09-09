<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('downtime_categories', function (Blueprint $table) {
            // Check if old column exists and rename/update structure
            if (Schema::hasColumn('downtime_categories', 'dt_category')) {
                $table->dropColumn('dt_category');
            }
            
            // Add correct columns if they don't exist
            if (!Schema::hasColumn('downtime_categories', 'downtime_name')) {
                $table->string('downtime_name');
            }
            
            if (!Schema::hasColumn('downtime_categories', 'downtime_type')) {
                $table->string('downtime_type');
            }
            
            // Remove remember_token if it exists (not needed for this table)
            if (Schema::hasColumn('downtime_categories', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('downtime_categories', function (Blueprint $table) {
            $table->string('dt_category')->nullable();
            $table->dropColumn(['downtime_name', 'downtime_type']);
        });
    }
};
