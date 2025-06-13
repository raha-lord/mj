<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('tasks_management.statuses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6B7280'); // Hex цвет для UI
            $table->integer('sort_order')->default(0);
            $table->boolean('is_final')->default(false); // Финальный статус?

            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('sort_order');
            $table->index('is_final');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks_management.statuses');
    }
}
