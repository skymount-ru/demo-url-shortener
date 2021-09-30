<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUrlEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('url_entries', function (Blueprint $table) {
            $table->id();
            $table->char('short_code', 6)->unique();
            $table->string('url', 2048);
            $table->unsignedBigInteger('url_short_hash');
            $table->char('url_hash', 64);
            $table->timestamp('valid_until')->nullable();

            $table->unique(['url_short_hash', 'url_hash']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('url_entries');
    }
}
