<?php

namespace Retailcore\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromCollection;


class sales_bill extends Model
{
    protected $primaryKey = 'sales_bill_id'; //Default: id
    protected $guarded=['sales_bill_id'];
   
    public function customer()
    {
        return $this->hasOne('Retailcore\Customer\Models\customer\customer','customer_id','customer_id');
    }
    public function stock_transfer()
    {
        return $this->hasOne('Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer','sales_bill_id','sales_bill_id');
    }
    public function customer_address_detail()
    {
        return $this->hasOne('Retailcore\Customer\Models\customer\customer_address_detail','customer_id','customer_id');
    }
    public function sales_product_detail()
    {
        return $this->hasMany('Retailcore\Sales\Models\sales_product_detail','sales_bill_id','sales_bill_id')->where('deleted_at','=',NULL);
    }
    public function deletedsales_product_detail()
    {
        return $this->hasMany('Retailcore\Sales\Models\sales_product_detail','sales_bill_id','sales_bill_id');
    }
  	public function sales_bill_payment_detail()
    {
        return $this->hasMany('Retailcore\Sales\Models\sales_bill_payment_detail','sales_bill_id','sales_bill_id')->where('deleted_at','=',NULL);
    }
    public function deletedsales_bill_payment_detail()
    {
        return $this->hasMany('Retailcore\Sales\Models\sales_bill_payment_detail','sales_bill_id','sales_bill_id')->where('total_bill_amount','!=',0);
    }
    public function reference()
    {
        return $this->hasOne('Retailcore\Sales\Models\reference','reference_id','reference_id');
    }
    public function company()
    {
        return $this->hasOne('Retailcore\Company_Profile\Models\company_profile\company_profile','company_id','company_id');
    }
    public function state()
    {
        return $this->hasOne('App\state','state_id','state_id');
    }
    public function customer_creditaccount()
    {
        return $this->hasOne('Retailcore\CreditBalance\Models\customer_creditaccount','sales_bill_id','sales_bill_id');
    }
    public function return_bill()
    {
        return $this->hasOne('Retailcore\SalesReturn\Models\return_bill','sales_bill_id','sales_bill_id')->where('deleted_at','=',NULL);
    }
  
    public function creditnote_payment()
    {
        return $this->hasOne('Retailcore\CreditNote\Models\creditnote_payment','sales_bill_id','sales_bill_id')->where('deleted_at','=',NULL);
    }
    public function user()
    {
        return $this->hasOne('App\User','user_id','created_by');
    }
    public function advance_payment()
    {
        return $this->hasOne('Retailcore\Sales\Models\sales_bill_payment_detail','sales_bill_id','sales_bill_id')->where('payment_method_id',10)->where('deleted_at','=',NULL);
    }

}

