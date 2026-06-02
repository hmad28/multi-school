<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->nullOnDelete();
            $table->foreignUuid('attendance_status_id')->constrained('attendance_statuses')->restrictOnDelete();
            $table->date('date');
            $table->text('note')->nullable();
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->string('arrival_source', 50)->nullable();
            $table->string('departure_source', 50)->nullable();
            $table->foreignUuid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('corrected_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'date']);
            $table->index(['school_id', 'class_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
