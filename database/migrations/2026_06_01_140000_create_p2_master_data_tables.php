<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('nip', 30)->nullable();
            $table->string('full_name', 100);
            $table->string('position', 100)->default('Guru');
            $table->string('phone', 20)->nullable();
            $table->string('status', 20)->default('active');
            $table->boolean('can_input_teacher_attendance')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'nip']);
        });

        Schema::create('academic_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 50);
            $table->unsignedTinyInteger('numeric_value');
            $table->timestamps();

            $table->unique(['school_id', 'name']);
            $table->unique(['school_id', 'numeric_value']);
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 30);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name', 30);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'academic_year_id', 'name']);
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('academic_level_id')->constrained('academic_levels')->cascadeOnDelete();
            $table->foreignUuid('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('name', 50);
            $table->string('status', 20)->default('active');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'academic_level_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('academic_levels');
        Schema::dropIfExists('teachers');
    }
};
