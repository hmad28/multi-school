<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_attendances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignUuid('attendance_status_id')->constrained('attendance_statuses')->restrictOnDelete();
            $table->date('date');
            $table->text('note')->nullable();
            $table->foreignUuid('submitted_by')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('corrected_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'teacher_id', 'date']);
            $table->index(['school_id', 'date']);
        });

        Schema::create('teacher_attendance_submissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->date('date');
            $table->foreignUuid('submitted_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['school_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
        Schema::dropIfExists('teacher_attendance_submissions');
    }
};
