<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreceptToOrganisations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            Schema::table('organisations', function (Blueprint $table) {
                $table->boolean('precept')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!config('app.IS_TOOL')) {
            Schema::table('organisations', function (Blueprint $table) {
                $table->dropColumn('precept');
            });
        }
    }
}
