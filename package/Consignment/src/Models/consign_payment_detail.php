<?php

namespace Retailcore\Consignment\Models;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromCollection;

class consign_payment_detail extends Model
{
    protected $primaryKey = 'consign_payment_detail_id'; //Default: id
    protected $guarded=['consign_payment_detail_id'];

    public function payment_method()
    {
        return $this->hasMany('Retailcore\Sales\Models\payment_method','payment_method_id','payment_method_id')->whereNull('deleted_at')->orderBy('payment_order','ASC');
    }
}
