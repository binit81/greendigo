<?php

namespace Retailcore\StoreReturn\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class storereturn_product extends Model
{
     protected $primaryKey = 'storereturn_product_id'; //Default: id
     protected $guarded=['storereturn_product_id'];


    public function product()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product','product_id','product_id');
    }
    public function inward_product_detail()
    {
        return $this->hasOne('Retailcore\Inward_Stock\Models\inward\inward_product_detail','inward_product_detail_id','inward_product_detail_id')->whereNull('deleted_at')->where('company_id',Auth::user()->company_id);
    }
    public function store_return()
    {
        return $this->hasOne('Retailcore\StoreReturn\Models\store_return','store_return_id','store_return_id');
    }
    public function company()
    {
        return $this->hasOne('Retailcore\Company_Profile\Models\company_profile\company_profile','company_id','company_id');
    }
    public function returnbill_product()
    {
        return $this->hasMany('Retailcore\SalesReturn\Models\returnbill_product','storereturn_product_id','storereturn_product_id');
    }
}
