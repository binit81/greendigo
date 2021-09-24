<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTransferDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transfer_details', function (Blueprint $table) {
            $table->increments('stock_transfers_detail_id');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->integer('stock_transfer_id')->unsigned();
            $table->foreign('stock_transfer_id')->references('stock_transfer_id')->on('stock_transfers');
            $table->integer('sales_products_detail_id')->unsigned()->nullable();
            $table->foreign('sales_products_detail_id')->references('sales_products_detail_id')->on('sales_product_details');
            $table->integer('supplier_gst_id')->unsigned()->nullable();
            $table->foreign('supplier_gst_id')->references('supplier_gst_id')->on('supplier_gsts');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('product_id')->on('products');
            $table->integer('price_master_id')->unsigned()->nullable();
            $table->foreign('price_master_id')->references('price_master_id')->on('price_masters');
           // $table->integer('werehouse_id')->nullable();
            $table->string('batch_no',100)->nullable();
            $table->double('base_price',20,4);
            $table->double('base_discount_percent',20,4);
            $table->double('base_discount_amount',20,4);
            $table->double('scheme_discount_percent',20,4);
            $table->double('scheme_discount_amount',20,4);
            $table->double('free_discount_percent',20,4);
            $table->double('free_discount_amount',20,4);
            $table->double('cost_rate',20,4);
            $table->double('extra_charge',20,4)->nullable();
            $table->double('cost_igst_percent',20,4);
            $table->double('cost_igst_amount',20,4);
            $table->double('cost_cgst_percent',20,4);
            $table->double('cost_cgst_amount',20,4);
            $table->double('cost_sgst_percent',20,4);
            $table->double('cost_sgst_amount',20,4);
            $table->double('cost_price',20,4);
            $table->double('profit_percent',20,4);
            $table->double('profit_amount',20,4);
            $table->double('sell_price',20,4)->nullable();
            $table->double('selling_gst_percent',20,4)->nullable();
            $table->double('selling_gst_amount',20,4)->nullable();
            $table->double('offer_price',20,4);
            $table->double('product_mrp',20,4);
            $table->integer('product_qty');
            $table->longText('inward_product_detail_id')->nullable();
            $table->longText('inward_product_qtys')->nullable();
            $table->integer('free_qty');
            $table->integer('pending_rcv_qty')->default(0)->comment = "number of qty can be return";
            $table->string('mfg_date',20)->nullable();
            $table->string('expiry_date',20)->nullable();
            $table->double('total_cost_rate_with_qty',20,4);
            $table->double('total_igst_amount_with_qty',20,4);
            $table->double('total_cgst_amount_with_qty',20,4);
            $table->double('total_sgst_amount_with_qty',20,4);
            $table->double('total_offer_price',20,4);
            $table->double('total_cost',20,4);
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
        Schema::dropIfExists('stock_transfer_details');
    }
}
