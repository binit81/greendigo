<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductFeaturesRelationshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_features_relationships', function (Blueprint $table) {
            $table->increments('product_features_relationship_id');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('company_id')->on('companies');
            //$table->integer('product_features_data_id')->unsigned();
            //$table->foreign('product_features_data_id')->references('product_features_data_id')->on('product_features_datas');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('product_id')->on('products');
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_features_relationships');
    }
}
