<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptionalCalendarFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendars', function ($table) {
            $table->string('hash_tag')->after('timezone')->nullable();
            $table->string('zip_code')->after('timezone')->nullable();
            $table->string('league')->after('timezone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropColumn('hash_tag');
            $table->dropColumn('zip_code');
            $table->dropColumn('league');
        });
    }
}
