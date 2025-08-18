<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSchemas extends Migration
{
    public function up()
    {
        // Создаем схемы
        DB::statement('CREATE SCHEMA IF NOT EXISTS tasks_management');
        DB::statement('CREATE SCHEMA IF NOT EXISTS audit');
    }

    public function down()
    {
        DB::statement('DROP SCHEMA IF EXISTS audit CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS tasks_management CASCADE');
    }
}
