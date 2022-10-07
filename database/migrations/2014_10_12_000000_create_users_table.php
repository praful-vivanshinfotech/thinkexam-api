<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
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
            $table->unsignedBigInteger('role_id')->index()->nullable();
            $table->string('first_name', 255)->nullable()->index();
            $table->string('last_name', 255)->nullable()->index();
            $table->string('email')->unique();
            $table->unsignedTinyInteger('gender')->default(1)->index()->comment("1:Male | 2:Female | 3:Other");
            $table->date('birth_date')->nullable()->index();
            $table->string('phone_number')->nullable()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedTinyInteger('two_factor_status')->default(1)->index()->comment("0:Disable | 1:Inable ");
            $table->unsignedTinyInteger('status')->default(1)->index()->comment("0:Inactive | 1:Active | 2:Archive");
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();

            $table->rememberToken();
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
};
