<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('table_downtimes', function (Blueprint $table) {
            $table->string('problem_picture', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('table_downtimes', function (Blueprint $table) {
            $table->string('problem_picture', 100)->nullable()->change();  // sesuaikan dengan ukuran semula
        });
    }
};
