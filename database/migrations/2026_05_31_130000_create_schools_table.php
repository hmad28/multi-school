<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('slug', 100)->unique();
            $table->string('npsn', 30)->nullable();
            $table->string('email', 150);
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->string('status', 20)->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->time('student_attendance_late_after')->nullable();
            $table->time('student_attendance_start_time')->nullable();
            $table->time('student_attendance_departure_time')->nullable();
            $table->string('principal_name', 100)->nullable();
            $table->string('principal_nip', 50)->nullable();
            $table->text('letterhead_footer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
