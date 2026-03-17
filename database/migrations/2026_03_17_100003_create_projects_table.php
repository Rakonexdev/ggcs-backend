<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->enum('type', ['fixed', 'variable']);
            $table->enum('status', ['draft', 'active', 'on_hold', 'completed', 'withdrawn'])->default('draft');
            $table->string('lpo_url')->nullable();
            $table->decimal('fixed_total_amount', 12, 2)->nullable();
            $table->text('fixed_description')->nullable();
            $table->integer('invoice_interval_days')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
