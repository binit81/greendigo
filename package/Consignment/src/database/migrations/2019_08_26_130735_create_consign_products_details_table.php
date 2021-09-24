<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsignProductsDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consign_products_details', function (Blueprint $table) {
           $table->increments('consign_products_detail_id');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->integer('consign_bill_id')->unsigned();
            $table->foreign('consign_bill_id')->references('consign_bill_id')->on('consign_bills');
            $table->tinyInteger('product_type')->comment = "1=products,0=charges";
            $table->integer('price_master_id')->unsigned()->nullable();
            $table->foreign('price_master_id')->references('price_master_id')->on('price_masters');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('product_id')->on('products');
            $table->double('mrp',20,4);
            $table->double('sellingprice_before_discount',20,4);
            $table->double('qty');
            $table->longText('inwardids')->nullable();
            $table->longText('inwardqtys')->nullable();
            $table->double('discount_percent',20,4)->nullable();
            $table->double('discount_amount',20,4)->nullable();
            $table->double('mrpdiscount_amount',20,4)->nullable();
            $table->double('sellingprice_after_discount',20,4);
            $table->double('overalldiscount_percent',20,4)->nullable();
            $table->double('overalldiscount_amount',20,4)->nullable();
            $table->double('overallmrpdiscount_amount',20,4)->nullable();
            $table->double('sellingprice_afteroverall_discount',20,4);
            $table->double('cgst_percent',20,4)->nullable();
            $table->double('cgst_amount',20,4)->nullable();
            $table->double('sgst_percent',20,4)->nullable();
            $table->double('sgst_amount',20,4)->nullable();
            $table->double('igst_percent',20,4)->nullable();
            $table->double('igst_amount',204)->nullable();
            $table->double('total_amount',20,4);
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
        Schema::dropIfExists('consign_products_details');
    }
}
