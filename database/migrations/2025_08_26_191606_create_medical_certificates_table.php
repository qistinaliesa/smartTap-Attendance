<?php
// Create this migration file: database/migrations/xxxx_xx_xx_create_medical_certificates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medical_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->date('absence_date');
            $table->text('reason');
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamp('uploaded_at');
            $table->foreignId('uploaded_by')->constrained('lecturers')->onDelete('cascade');
            $table->timestamps();

            // Ensure one MC per student per date
            $table->unique(['enrollment_id', 'absence_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('medical_certificates');
    }
};
