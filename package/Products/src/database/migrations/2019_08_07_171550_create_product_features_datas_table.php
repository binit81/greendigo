<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductFeaturesDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_features_datas', function (Blueprint $table) {
            $table->increments('product_features_data_id');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('company_id')->on('companies');
             $table->integer('product_features_id')->unsigned();
            $table->foreign('product_features_id')->references('product_features_id')->on('product_features');
            $table->string('product_features_data_value',100);
            $table->string('product_features_data_url',500)->nullable();
            $table->string('product_features_data_image',255)->nullable();
            $table->string('product_features_banner_image',255)->nullable();
            $table->longText('feature_content')->nullable();
            $table->integer('parent')->nullable();
            $table->integer('ordering')->nullable()->default(0);
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
        //  DB::table('product_features_datas')->insert(array(
        //     'company_id'=> '1',
        //     'product_features_id'=>'1',
        //     'product_features_data_value' => 'ABC',
        //     'is_active' => '1',
        //     'created_by' => '1',
        //     'parent'=>'0'

        // ));

        //   DB::table('product_features_datas')->insert(array(
        //     'company_id'=> '1',
        //     'product_features_id'=>'1',
        //     'product_features_data_value' => 'XYZ',
        //     'is_active' => '1',
        //     'created_by' => '1',
        //     'parent'=>'0'

        // ));
           DB::table('product_features_datas')->insert(array(
            'company_id'=> '1',
            'product_features_id'=>'1',
            'product_features_data_value' => 'Boy_uniforms',
            'is_active' => '1',
            'created_by' => '1',
            'parent'=>'0'

        ));
            DB::table('product_features_datas')->insert(array(
            'company_id'=> '1',
            'product_features_id'=>'1',
            'product_features_data_value' => 'Girl_uniforms',
            'is_active' => '1',
            'created_by' => '1',
            'parent'=>'0'

        ));
            DB::table('product_features_datas')->insert(array(
            'company_id'=> '1',
            'product_features_id'=>'2',
            'product_features_data_value' => 'Girl',
            'is_active' => '1',
            'created_by' => '1',
            'parent'=>'0'

        ));
             DB::table('product_features_datas')->insert(array(
            'company_id'=> '1',
            'product_features_id'=>'2',
            'product_features_data_value' => 'Boy',
            'is_active' => '1',
            'created_by' => '1',
            'parent'=>'0'

        ));
              DB::table('product_features_datas')->insert(array(
            'company_id'=> '1',
            'product_features_id'=>'3',
            'product_features_data_value' => 'Winter Uniform',
            'is_active' => '1',
            'created_by' => '1',
            'parent'=>'0'

        ));

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_features_datas');
    }
}
