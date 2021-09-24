<?php

namespace Retailcore\Products\Models\product;

use Illuminate\Database\Eloquent\Model;

class product_features_data extends Model
{
    protected $primaryKey = 'product_features_data_id';
     protected $guarded=['product_features_data_id'];

    public function product_features()
     {
        return $this->hasOne('Retailcore\Products\Models\product\ProductFeatures','product_features_id','product_features_id');
     }

}
