<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_point_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('category', 30);
            $table->string('name', 150);
            $table->unsignedSmallInteger('points');
            $table->string('status', 20)->default('active')->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['category', 'status']);
        });

        Schema::create('student_character_points', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignUuid('character_point_type_id')->constrained('character_point_types')->restrictOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->date('date');
            $table->string('category_snapshot', 30);
            $table->unsignedSmallInteger('points_snapshot');
            $table->text('note')->nullable();
            $table->foreignUuid('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['school_id', 'student_id', 'semester_id']);
            $table->index(['school_id', 'class_id', 'date']);
            $table->index('character_point_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_character_points');
        Schema::dropIfExists('character_point_types');
    }
};
