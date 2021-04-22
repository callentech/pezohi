<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Relationships.
            $table->unsignedBigInteger('calendar_id');
            $table->foreign('calendar_id')
                  ->references('id')->on('calendars')
                  ->onDelete('cascade');

            // Data.
            $table->string('google_id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('allday')->default(false);

            // Timestamps.
            $table->datetime('started_at')->nullable();
            $table->datetime('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
