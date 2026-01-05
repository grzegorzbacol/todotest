<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('label_task', function (Blueprint $table) {
            $table->uuid('task_id');
            $table->uuid('label_id');
            $table->timestamps();

            $table->primary(['task_id', 'label_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('label_task');
    }
};

