<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->increments('stock_transfer_id');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->integer('sales_bill_id')->unsigned()->nullable();
            $table->foreign('sales_bill_id')->references('sales_bill_id')->on('sales_bills');
            $table->string('stock_transfer_no',100);
            $table->integer('store_id')->nullable()->comment="company_profie_id";
            $table->string('stock_transfer_date',55);
            $table->string('total_mrp',100);
            $table->double('total_qty');
            $table->double('total_gst',20,4)->nullable();
            $table->double('total_sellprice',20,4)->nullable();
            $table->double('total_offerprice',20,4)->nullable();
            $table->double('total_cost_igst_amount',20,4)->nullable();
            $table->double('total_cost_cgst_amount',20,4)->nullable();
            $table->double('total_cost_sgst_amount',20,4)->nullable();
            $table->tinyInteger('store_type')->default('2')->comment = "1=franchisee,2=normal_transfer";
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
        Schema::dropIfExists('stock_transfers');
    }
}
