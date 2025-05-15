<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    public function up()
    {
        Schema::create('task.projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('description')->nullable();

            // Системные поля: created_at и updated_at
            $table->timestamps();
            // Мягкое удаление
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task.projects');
    }
}
