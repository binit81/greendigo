<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferralPointsTable extends Migration
{
    public function up()
    {
        Schema::create('referral_points', function (Blueprint $table)
        {
            $table->increments('referral_point_id');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->string('type_of_purchase',100);
            $table->double('reffering_percent',20,4)->default(0);
            $table->double('reffering_points',20,4)->default(0);
            $table->double('new_customer_percent',20,4)->default(0);
            $table->double('new_customer_points',20,4)->default(0);
            $table->double('points_amount',20,4)->default(0);
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

        DB::table('referral_points')->insert(array(
            'company_id' => '1',
            'type_of_purchase' => "First Purchase",
            'reffering_percent' => "0",
            'reffering_points' => "0",
            'new_customer_percent' => "0",
            'new_customer_points' => "0",
        ));

        DB::table('referral_points')->insert(array(
            'company_id' => '1',
            'type_of_purchase' => "Subsequent Purchase",
            'reffering_percent' => "0",
            'reffering_points' => "0",
            'new_customer_percent' => "0",
            'new_customer_points' => "0",
        ));

    }


    public function down()
    {
        Schema::dropIfExists('referral_points');
    }
}
