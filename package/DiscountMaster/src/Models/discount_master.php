<?php

namespace Retailcore\DiscountMaster\Models;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromCollection;

class discount_master extends Model
{
    protected $primaryKey = 'discount_master_id'; //Default: id
    protected $guarded=['discount_master_id'];

    public function product()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product','product_id','product_id');
    }
}
