<?php

namespace Retailcore\Stock_Transfer\Models\stock_transfer;

use Illuminate\Database\Eloquent\Model;

class stock_transfer_detail extends Model
{
    protected $primaryKey = 'stock_transfers_detail_id'; //Default: id
    protected $guarded=['stock_transfers_detail_id'];

    public function product_data()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product','product_id','product_id');
    }
    public function stock_transfer_no()
    {
        return $this->hasOne('Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer','stock_transfer_id','stock_transfer_id');
    }
    public function stock_transfer()
    {
        return $this->hasOne('Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer','stock_transfer_id','stock_transfer_id');
    }
    public function product()
    {
        return $this->hasOne('Retailcore\Products\Models\product\product','product_id','product_id');
    }

    //for batch no report count sold qty
    public function price_master_batch_wise()
    {
        return $this->hasOne('Retailcore\Products\Models\product\price_master','price_master_id','price_master_id');
    }

}
