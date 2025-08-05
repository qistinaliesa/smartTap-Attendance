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
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'utype')) {
            $table->string('utype')->default('user')->after('password');
        }

        if (!Schema::hasColumn('users', 'lecturer_id')) {
            $table->foreignId('lecturer_id')
                ->nullable()
                ->constrained('lecturers')
                ->onDelete('set null')
                ->after('utype');
        }
    });
}

};
