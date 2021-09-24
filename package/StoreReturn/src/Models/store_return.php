<?php

namespace Retailcore\StoreReturn\Models;

use Illuminate\Database\Eloquent\Model;

class store_return extends Model
{
    protected $primaryKey = 'store_return_id'; //Default: id
    protected $guarded=['store_return_id'];

    public function storereturn_product()
    {
        return $this->hasMany('Retailcore\StoreReturn\Models\storereturn_product','store_return_id','store_return_id')->where('deleted_at','=',NULL);
    }
    public function user()
    {
        return $this->hasOne('App\User','user_id','created_by');
    }    
}
