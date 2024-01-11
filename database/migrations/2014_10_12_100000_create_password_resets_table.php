<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index()->comment('User email id');
            $table->string('token')->comment('Generated token');
            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who last updated the record');
            $table->timestamp('created_at')->comment('Creation time')->nullable();
            $table->timestamp('updated_at')->comment('Updation time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}
