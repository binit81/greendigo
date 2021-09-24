<?php

namespace Retailcore\Consignment\Models;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromCollection;

class consign_products_detail extends Model
{
    protected $primaryKey = 'consign_products_detail_id'; //Default: id
    protected $guarded=['consign_products_detail_id'];

    public function product()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product','product_id','product_id');
    }
    public function consign_bill()
    {
        return $this->hasOne('Retailcore\Consignment\Models\consign_bill','consign_bill_id','consign_bill_id');
    }
    public function price_master()
    {
        return $this->hasMany('Retailcore\Products\Models\product\price_master','product_id','product_id')->sum('product_qty')->groupBy('product_id');
    }
    public function batchprice_master()
    {
        return $this->hasOne('Retailcore\Products\Models\product\price_master','price_master_id','price_master_id');
    }
    public function sales_product_detail()
    {
        return $this->hasMany('Retailcore\Sales\Models\sales_product_detail','consign_products_detail_id','consign_products_detail_id')->select('qty','consign_products_detail_id')->where('deleted_at','=',NULL);
    }

    //for batch no report count sold qty
    public function price_master_batch_wise()
    {
        return $this->hasOne('Retailcore\Products\Models\product\price_master','price_master_id','price_master_id');
    }
    public function return_product_detail()
    {
        return $this->hasMany('Retailcore\SalesReturn\Models\return_product_detail','consign_products_detail_id','consign_products_detail_id')->where('deleted_at','=',NULL);
    }
}
