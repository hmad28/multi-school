<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('nisn', 20)->nullable()->after('nis');
            $table->foreignUuid('class_id')->nullable()->after('nisn')->constrained('classes')->nullOnDelete();
            $table->string('gender', 20)->nullable()->after('class_id');
            $table->string('guardian_name', 100)->nullable()->after('gender');
            $table->string('guardian_phone', 20)->nullable()->after('guardian_name');
            $table->text('address')->nullable()->after('guardian_phone');
            $table->string('status', 20)->default('active')->after('address');
            $table->softDeletes();

            $table->unique(['school_id', 'nisn']);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'nisn']);
            $table->dropConstrainedForeignId('class_id');
            $table->dropColumn([
                'nisn',
                'gender',
                'guardian_name',
                'guardian_phone',
                'address',
                'status',
                'deleted_at',
            ]);
        });
    }
};
