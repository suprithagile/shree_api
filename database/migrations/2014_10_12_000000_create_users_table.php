<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',100)->comment('User first name');
            $table->string('email',100)->comment('User Email ID')->unique(); 
            // $table->string('password',250)->comment('Password');
            $table->string('mobile',15)->comment('Mobile number')->nullable();
            $table->integer('is_consumer')->default(0)->comment('User role');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->rememberToken()->comment('Token Generated')->nullable();
            $table->timestamp('email_verified_at')->comment('Email verification date and time')->nullable();
            $table->string('otp',100)->comment('Generated otp for recover password')->nullable();
            $table->dateTime('otp_valid_until')->comment('Generated otp expire date and time')->nullable();
            $table->integer('is_otp_validated')->default(1)->comment('Is the OTP Validated?');
            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who last updated the record');
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
        Schema::dropIfExists('users');
    }
}
