<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('category', 30);
            $table->string('name', 150);
            $table->unsignedSmallInteger('points');
            $table->string('status', 20)->default('active')->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'category', 'status']);
        });

        Schema::create('violation_thresholds', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedSmallInteger('points');
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->unique(['school_id', 'points']);
        });

        Schema::create('student_violations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignUuid('violation_type_id')->constrained('violation_types')->restrictOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->date('date');
            $table->string('category_snapshot', 30);
            $table->unsignedSmallInteger('points_snapshot');
            $table->text('note')->nullable();
            $table->string('evidence_path')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->foreignUuid('reported_by')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();
            $table->index(['school_id', 'student_id', 'semester_id', 'status']);
            $table->index(['school_id', 'class_id', 'date']);
            $table->index('violation_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_violations');
        Schema::dropIfExists('violation_thresholds');
        Schema::dropIfExists('violation_types');
    }
};
