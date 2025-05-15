<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTaskSchema extends Migration
{
    public function up()
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS task');
    }

    public function down()
    {
        DB::statement('DROP SCHEMA IF EXISTS task CASCADE');
    }
}
