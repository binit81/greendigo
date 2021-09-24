<?php

namespace Retailcore\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class sales_bill_payment_detail extends Model
{
     protected $primaryKey = 'sales_bill_payment_detail_id'; //Default: id
     protected $guarded=['sales_bill_payment_detail_id'];
   
    
     public function payment_method()
    {
        return $this->hasMany('Retailcore\Sales\Models\payment_method','payment_method_id','payment_method_id')->whereNull('deleted_at')->orderBy('payment_order','ASC');
    }
    public function sales_bill()
    {
        return $this->hasOne('Retailcore\Sales\Models\sales_bill','sales_bill_id','sales_bill_id');
    }
}
