<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('plan', 50);
            $table->string('period', 20)->default('monthly');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_reference', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
