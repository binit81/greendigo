<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_features', function (Blueprint $table) {
           $table->increments('product_features_id');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->string('html_name',100);
            $table->string('html_id',100);
            $table->string('product_features_name',100);
            $table->string('feature_url',500);
            $table->integer('parent')->nullable();
            $table->integer('feature_type')->default('1')->comment = "1=Category,2=Pages,3=Blog";
            $table->longText('show_feature_url')->nullable();
            $table->integer('ordering')->default('0');
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

          DB::table('product_features')->insert(array(
            'company_id'=> '1',
            'product_features_name' => 'School',
            'html_name' => 'dynamic_school',
            'html_id' => 'dynamic_school',
            'is_active' => '0',
            'created_by' => '1',
            'parent'=>'0'
          ));

           DB::table('product_features')->insert(array(
            'company_id'=> '1',
            'product_features_name' => 'Gender',
            'html_name' => 'dynamic_gender',
            'html_id' => 'dynamic_gender',
            'is_active' => '0',
            'created_by' => '1',
            'parent'=>'0'

        ));

            DB::table('product_features')->insert(array(
            'company_id'=> '1',
            'product_features_name' => 'Grade',
            'html_name' => 'dynamic_grade',
            'html_id' => 'dynamic_grade',
            'is_active' => '0',
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
        Schema::dropIfExists('product_features');
    }
}
