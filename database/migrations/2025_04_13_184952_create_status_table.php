<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusTable extends Migration
{
    public function up()
    {
        Schema::create('task.statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('name', 255);
            $table->text('description')->nullable();

            // Системные поля
            $table->timestamps();
            // Мягкое удаление
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task.statuses');
    }
}
