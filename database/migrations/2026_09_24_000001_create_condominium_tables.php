<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('homes', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('block')->nullable();
            $table->string('address'); $table->string('status')->default('active'); $table->timestamps();
        });
        Schema::create('residents', function (Blueprint $table) {
            $table->id(); $table->foreignId('home_id')->constrained('homes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name'); $table->string('dpi', 30)->nullable()->unique(); $table->string('phone', 30)->nullable();
            $table->string('email')->nullable(); $table->string('status')->default('active'); $table->timestamps();
        });
        Schema::create('visits', function (Blueprint $table) {
            $table->id(); $table->foreignId('home_id')->constrained('homes')->cascadeOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_name'); $table->string('visitor_dpi', 30)->nullable(); $table->string('vehicle_plate', 20)->nullable();
            $table->dateTime('scheduled_at'); $table->dateTime('authorized_at')->nullable(); $table->dateTime('entry_at')->nullable(); $table->dateTime('exit_at')->nullable();
            $table->string('status')->default('pending'); $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); $table->foreignId('home_id')->constrained('homes')->cascadeOnDelete(); $table->string('period', 7);
            $table->date('due_date'); $table->decimal('amount', 10, 2); $table->dateTime('paid_at')->nullable();
            $table->string('status')->default('pending'); $table->string('reference')->nullable(); $table->timestamps();
            $table->unique(['home_id', 'period']);
        });
        Schema::create('incidents', function (Blueprint $table) {
            $table->id(); $table->foreignId('home_id')->nullable()->constrained('homes')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('title');
            $table->text('description'); $table->string('priority')->default('medium'); $table->string('status')->default('open');
            $table->dateTime('reported_at'); $table->dateTime('resolved_at')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('incidents'); Schema::dropIfExists('payments'); Schema::dropIfExists('visits'); Schema::dropIfExists('residents'); Schema::dropIfExists('homes'); }
};
