<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // This migration ensures foreign key constraints are properly set
        // after importing data
        
        Schema::table('table_downtimes', function (Blueprint $table) {
            // Make sure foreign key constraint exists
            if (!$this->constraintExists('table_downtimes', 'fk_table_downtime_production')) {
                $table->foreign('table_production_id', 'fk_table_downtime_production')
                      ->references('id')->on('table_productions')
                      ->onDelete('cascade');
            }
        });

        Schema::table('table_defects', function (Blueprint $table) {
            // Make sure foreign key constraint exists
            if (!$this->constraintExists('table_defects', 'fk_table_defect_production')) {
                $table->foreign('table_production_id', 'fk_table_defect_production')
                      ->references('id')->on('table_productions')
                      ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('table_downtimes', function (Blueprint $table) {
            $table->dropForeign('fk_table_downtime_production');
        });

        Schema::table('table_defects', function (Blueprint $table) {
            $table->dropForeign('fk_table_defect_production');
        });
    }

    private function constraintExists($table, $constraintName)
    {
        $constraints = \DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME = ?
        ", [$table, $constraintName]);

        return count($constraints) > 0;
    }
};
