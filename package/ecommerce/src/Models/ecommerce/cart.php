<?php

namespace Retailcore\ecommerce\Models\ecommerce;

use Illuminate\Database\Eloquent\Model;

class cart extends Model
{
    protected $primaryKey = 'cart_id'; //Default: id
    protected $guarded=['cart_id'];

    public function product()
    {
    	return $this->hasOne('Retailcore\Products\Models\product\product','product_id','product_id')->where('deleted_at','=',NULL);
    }

    public function price_master()
    {
        return $this->hasOne('Retailcore\Products\Models\product\price_master','product_id','product_id');
    }

    public function product_image()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product_image','product_id','product_id')->where('deleted_at','=',NULL)->orderBy('product_image_id','DESC');
    }

}
