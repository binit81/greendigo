<?php

namespace Retailcore\Stock_Transfer\Models\stock_transfer;

use Illuminate\Database\Eloquent\Model;

class stock_transfer extends Model
{
    protected $primaryKey = 'stock_transfer_id'; //Default: id
    protected $guarded=['stock_transfer_id'];

    public function stock_transfer_detail()
    {
        return $this->hasMany('Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail','stock_transfer_id','stock_transfer_id');
    }
    public function store_name()
    {
        return $this->hasOne('Retailcore\Company_Profile\Models\company_profile\company_profile','company_profile_id','store_id');
    }

    public function warehouse()
    {
        return $this->hasOne('Retailcore\Company_Profile\Models\company_profile\company_profile','company_id','company_id');
    }
    public function company()
    {
        return $this->hasOne('Retailcore\Company_Profile\Models\company_profile\company_profile','company_id','company_id');
    }
}
