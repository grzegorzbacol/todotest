<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'done'])->default('active');
            $table->boolean('starred')->default(false);
            $table->date('due_date')->nullable();
            $table->date('scheduled_for')->nullable();
            $table->enum('bucket', ['inbox', 'single', 'project'])->default('inbox');
            $table->uuid('project_id')->nullable();
            $table->float('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            
            // Indeksy zgodnie z wymaganiami
            $table->index(['status', 'scheduled_for']);
            $table->index(['status', 'due_date']);
            $table->index(['project_id', 'status']);
            $table->index(['bucket', 'status']);
            $table->index(['starred', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

