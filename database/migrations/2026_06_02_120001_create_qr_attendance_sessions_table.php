<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_attendance_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->date('date');
            $table->string('scan_type', 20);
            $table->string('token_hash', 64);
            $table->timestamp('expires_at');
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();

            $table->index('school_id');
            $table->index(['class_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_attendance_sessions');
    }
};
