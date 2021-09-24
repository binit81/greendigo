<?php

namespace Retailcore\Consignment\Models;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromCollection;


class consign_bill extends Model
{
    protected $primaryKey = 'consign_bill_id'; //Default: id
    protected $guarded=['consign_bill_id'];
   
    public function customer()
    {
        return $this->hasOne('Retailcore\Customer\Models\customer\customer','customer_id','customer_id');
    }
    public function customer_address_detail()
    {
        return $this->hasOne('Retailcore\Customer\Models\customer\customer_address_detail','customer_id','customer_id');
    }
    public function consign_products_detail()
    {
        return $this->hasMany('Retailcore\Consignment\Models\consign_products_detail','consign_bill_id','consign_bill_id')->where('deleted_at','=',NULL);
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
    public function user()
    {
        return $this->hasOne('App\User','user_id','created_by');
    }
    public function consign_payment_detail()
    {
        return $this->hasMany('Retailcore\Consignment\Models\consign_payment_detail','consign_bill_id','consign_bill_id')->where('deleted_at','=',NULL);
    }
    public function customer_creditnote()
    {
        return $this->hasOne('Retailcore\CreditNote\Models\customer_creditnote','consign_bill_id','consign_bill_id')->where('deleted_at','=',NULL);
    }
    public function sales_bill()
    {
        return $this->hasMany('Retailcore\Sales\Models\sales_bill','consign_bill_id','consign_bill_id')->where('deleted_at','=',NULL);
    }
    public function return_bill()
    {
        return $this->hasOne('Retailcore\SalesReturn\Models\return_bill','consign_bill_id','consign_bill_id')->where('deleted_at','=',NULL);
    }
}

