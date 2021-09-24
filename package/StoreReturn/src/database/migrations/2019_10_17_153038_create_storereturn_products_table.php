<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStorereturnProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storereturn_products', function (Blueprint $table) {
            $table->Increments('storereturn_product_id');
            $table->integer('warehouse_id')->unsigned();
            $table->foreign('warehouse_id')->references('company_id')->on('companies');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->integer('store_return_id')->unsigned();
            $table->foreign('store_return_id')->references('store_return_id')->on('store_returns');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('product_id')->on('products');
            $table->integer('price_master_id')->unsigned();
            $table->foreign('price_master_id')->references('price_master_id')->on('price_masters');
            $table->integer('inward_product_detail_id')->unsigned();
            $table->foreign('inward_product_detail_id')->references('inward_product_detail_id')->on('inward_product_details');
            $table->double('qty',20,4);            
            $table->tinyInteger('stockstatus')->default('0')->comment = "1=restocked or damaged ,0=pending for restock and add to damage";
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
        Schema::dropIfExists('storereturn_products');
    }
}
