<?php

namespace Retailcore\Products\Models\product;

use Illuminate\Database\Eloquent\Model;

class product_features_relationship extends Model
{
    protected $primaryKey = 'product_features_relationship_id';

    protected $guarded=['product_features_relationship_id'];

     public function product_features_data()
     {
        return $this->hasOne('Retailcore\Products\Models\product\product_features_data','product_features_data_id','product_features_data_id');
     }



    public function product()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product','product_id','product_id');
    }

    public function product_image()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product_image','product_id','product_id')->where('deleted_at','=',NULL)->orderBy('product_image_id','DESC');
    }
}
