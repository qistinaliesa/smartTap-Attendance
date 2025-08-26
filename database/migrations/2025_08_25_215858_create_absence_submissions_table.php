<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_absence_submissions_table
 *
 * Run: php artisan make:migration create_absence_submissions_table
 * Then replace the content with this code
 */
class CreateAbsenceSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absence_submissions', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');

            // Absence details
            $table->date('absence_date');
            $table->enum('absence_type', ['medical', 'emergency', 'family', 'official', 'other'])->default('other');
            $table->text('reason');
            $table->string('document_path')->nullable(); // Path to uploaded file

            // Status and review
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('lecturer_comment')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();

            // Who submitted and reviewed
            $table->enum('submitted_by', ['student', 'lecturer'])->default('student');
            $table->foreignId('reviewed_by')->nullable()->constrained('lecturers')->onDelete('set null');

            // Additional metadata
            $table->json('metadata')->nullable(); // For storing additional info like file size, type, etc.

            $table->timestamps();

            // Indexes for performance
            $table->index(['course_id', 'status']);
            $table->index(['enrollment_id', 'absence_date']);
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absence_submissions');
    }
}
