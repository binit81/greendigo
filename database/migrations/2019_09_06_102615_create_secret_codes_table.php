<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecretCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secret_codes', function (Blueprint $table) {
            $table->increments('secret_code_id');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->string('digit',1);
            $table->string('secret_code',1);
            $table->tinyInteger('is_active')->default('1')->comment = "1=active,0=inactive";
            $table->integer('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('user_id')->on('users');
            $table->integer('modified_by')->unsigned()->nullable();
            $table->foreign('modified_by')->references('user_id')->on('users');
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->foreign('deleted_by')->references('user_id')->on('users');
            $table->softDeletes('deleted_at');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->nullable();
        });

        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '0',
            'secret_code' => 'A',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '1',
            'secret_code' => 'B',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '2',
            'secret_code' => 'C',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '3',
            'secret_code' => 'D',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '4',
            'secret_code' => 'E',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '5',
            'secret_code' => 'F',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '6',
            'secret_code' => 'G',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '7',
            'secret_code' => 'H',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '8',
            'secret_code' => 'I',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '9',
            'secret_code' => 'J',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
        DB::table('secret_codes')->insert(array(
            'company_id' => '1',
            'digit' => '.',
            'secret_code' => 'K',
            'is_active' => '1',
            'created_by' => '1',
            'created_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secret_codes');
    }
}
