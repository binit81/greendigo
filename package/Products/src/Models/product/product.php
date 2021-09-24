<?php

namespace Retailcore\Products\Models\product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use DB;
class product extends Model
{
    protected $primaryKey = 'product_id'; //Default: id
    protected $guarded=['product_id'];
    use SoftDeletes;

    public function product_image_()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product_image','product_id','product_id')->where('deleted_at','=',NULL)->orderBy('product_image_id','DESC');
    }
    public function product_images()
    {
        return $this->hasMany('Retailcore\Products\Models\product\product_image','product_id','product_id')->where('deleted_at','=',NULL);
    }
    /*public function category()
    {
        return $this->hasOne('Retailcore\Products\Models\product\category','category_id','category_id');
    }
    public function subcategory()
    {
        return $this->hasOne('Retailcore\Products\Models\product\subcategory','subcategory_id','subcategory_id');
    }
    public function brand()
    {
        return $this->hasOne('Retailcore\Products\Models\product\brand','brand_id','brand_id');
    }
    public function colour()
    {
        return $this->hasOne('Retailcore\Products\Models\product\colour','colour_id','colour_id');
    }
    public function size()
    {
        return $this->hasOne('Retailcore\Products\Models\product\size','size_id','size_id');
    }*/
    public function uqc()
    {
        return $this->hasOne('Retailcore\Products\Models\product\uqc','uqc_id','uqc_id');
    }
    public function product_image()
    {
        return $this->hasMany('Retailcore\Products\Models\product\product_image','product_id','product_id');
    }
    public function stock_transfer_price_master()
    {
        return $this->hasMany('Retailcore\Products\Models\product\price_master','product_id','product_id')->groupBy('batch_no','product_id');
    }
    public function price_master()
    {
        return $this->hasMany('Retailcore\Products\Models\product\price_master','product_id','product_id')->where('product_qty','>',0)->orderBy('price_master_id','ASC')->where('company_id',Auth::user()->company_id);
    }
    ///to show products as per company
    public function reportprice_master()
    {
        return $this->hasMany('Retailcore\Products\Models\product\price_master','product_id','product_id')->orderBy('price_master_id','ASC')->where('company_id',Auth::user()->company_id);
    }
    public function editprice_master()
    {
        return $this->hasMany('Retailcore\Products\Models\product\price_master','product_id','product_id')->orderBy('price_master_id','ASC');
    }
    public function inward_product_detail()
    {
        return $this->hasMany('Retailcore\Inward_Stock\Models\inward\inward_product_detail','product_id','product_id')->where('deleted_at','=',NULL);
    }
    public function kitinward_product_detail()
    {
        return $this->hasOne('Retailcore\Inward_Stock\Models\inward\inward_product_detail','product_id','product_id')->where('deleted_at','=',NULL);
    }
    public function sales_product_detail()
    {
        return $this->hasMany('Retailcore\Sales\Models\sales_product_detail','product_id','product_id')->where('deleted_at','=',NULL);
    }
    public function returnbill_product()
    {
        return $this->hasMany('Retailcore\SalesReturn\Models\returnbill_product','product_id','product_id')->where('deleted_at','=',NULL);
    }
    public function damage_product_detail()
    {
        return $this->hasMany('Retailcore\DamageProducts\Models\damageproducts\damage_product_detail','product_id','product_id')->where('deleted_at','=',NULL);
    }
    public function debit_product_detail()
    {
        return $this->hasMany('Retailcore\Debit_Note\Models\debit_note\debit_product_detail','product_id','product_id')->where('deleted_at','=',NULL);
    }
    public function consign_products_detail()
    {
        return $this->hasMany('Retailcore\Consignment\Models\consign_products_detail','product_id','product_id')->where('deleted_at','=',NULL);
    }
    public function inward_product_detail_for_damage()
    {
        return $this->hasMany('Retailcore\Inward_Stock\Models\inward\inward_product_detail','product_id','product_id')->where('deleted_at','=',NULL)->groupBy('inward_stock_id')->where('company_id',Auth::user()->company_id);
    }
    public function combo_products_detail()
    {
        return $this->hasMany('Retailcore\Products_Kit\Models\combo_products_detail','kitproduct_id','product_id')->where('deleted_at','=',NULL);
    }
    public function discount_master()
    {
        return $this->hasOne('Retailcore\DiscountMaster\Models\discount_master','product_id','product_id')->where('deleted_at','=',NULL);
    }
    public function stock_transfer_detail()
    {
        return $this->hasMany('Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail','product_id','product_id')->where('deleted_at','=',NULL);
    }

     public function product_features_relationship()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product_features_relationship','product_id','product_id')->where('deleted_at','=',NULL);
    }

    public static function feature_value($product_features_id,$product_features_data_id)
    {
        $data_value =  DB::select(
            DB::raw('SELECT product_features_data_value FROM `product_features_datas` WHERE product_features_id="'.$product_features_id.'" AND product_features_data_id="'.$product_features_data_id.'" ')
        );

        return  $data_value[0]->product_features_data_value;
    }


    public function product_price_master()
    {
        return $this->hasMany('Retailcore\Products\Models\product\price_master','product_id','product_id')->orderBy('price_master_id','ASC');
    }

    public function pricemaster()
    {
        return $this->hasOne('Retailcore\Products\Models\product\price_master','product_id','product_id')->where('product_qty','>',0)->orderBy('price_master_id','ASC');
    }

}
