<?php

namespace Retailcore\Inward_Stock\Http\Controllers\inward;

use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Inward_Stock\Models\inward\price_master_report;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail;
use function foo\func;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Log;
class PriceMasterReportController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $sort_by = 'updated_at';
        $sort_type = 'desc';

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type')->first();

        $inward_type = 1;
        if(isset($inward_type_from_comp) && !empty($inward_type_from_comp) && $inward_type_from_comp['inward_type'] != '')
        {
            $inward_type = $inward_type_from_comp['inward_type'];
        }

        $price_master = price_master::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->with('product')
            ->orderBy($sort_by, $sort_type)->paginate(10);

        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($price_master AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $price_master[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }



        return view('inward_stock::inward/price_master_report',compact('price_master','inward_type'));
    }

    public function price_master_record(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();


        $sort_by = $data['sortby'];
        $sort_type = $data['sorttype'];
        $query = (isset($data['query']) ? $data['query'] : '');

        //$query = str_replace(" ", "", $query);

        if($request->ajax())
        {
            $price_master = price_master::where('company_id', Auth::user()->company_id)->with('product');

            if(isset($query) && $query != '' && $query['barcode'] != '')
            {
                $price_master->whereHas('product',function ($q) use($query)
                {
                    $q->where('product_system_barcode', 'like', '%'.$query['barcode'].'%');
                    $q->orWhere('supplier_barcode', 'like', '%'.$query['barcode'].'%');
                });
            }
            if(isset($query) && $query != '' && $query['product_name'] != '')
            {
                $price_master->whereHas('product',function ($q) use($query){
                    $q->where('product_name', 'like', '%'.$query['product_name'].'%');
                });
            }

            $price_master = $price_master->orderBy($sort_by, $sort_type)->paginate(10);

            $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type')->first();

            $inward_type = 1;
            if(isset($inward_type_from_comp) && !empty($inward_type_from_comp) && $inward_type_from_comp['inward_type'] != '')
            {
                $inward_type = $inward_type_from_comp['inward_type'];
            }

            return view('inward_stock::inward/price_master_report_data', compact('price_master','inward_type'))->render();
        }
    }


    public function update_price_in_all(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();


        if(isset($data) && $data != '' && isset($data['product_id']) && $data['product_id'] != '')
        {
            try {
                DB::beginTransaction();

              if(isset($data['company_id']) && $data['company_id'] != '')
              {

                   //update price in price master
                   price_master::where('product_id', $data['product_id'])
                       ->whereIn('company_id', $data['company_id'])
                       ->where('product_mrp', $data['update_mrp_on'])
                       ->where('offer_price', $data['update_offer_price_on'])
                       ->where('batch_no', $data['update_batch_no'])
                       ->update([
                           'product_mrp' => $data['mrp'],
                           'offer_price' => $data['offer_price'],
                           'wholesaler_price' => $data['wholesaler_price'],
                           'sell_price' => $data['selling_rate'],
                           'selling_gst_percent' => $data['selling_gst_percent'],
                           'selling_gst_amount' => $data['sell_gst_amount'],
                       ]);

                  foreach($data['company_id'] AS $kk=>$vv)
                  {
                      $price = price_master::select(DB::raw('sum(product_qty) AS totalqty'))
                          ->where('product_id',$data['product_id'])
                          ->where('company_id',$vv)
                          ->where('product_mrp', $data['mrp'])
                          ->where('offer_price', $data['offer_price'])
                          ->where('batch_no', $data['update_batch_no'])
                          ->first();

                      if(isset($price) && $price != '' && isset($price['totalqty']) && $price['totalqty'] != '')
                      {

                          //set 0 qty where condition match
                          price_master::
                          where('product_id', $data['product_id'])
                              ->where('company_id', $vv)
                              ->where('product_mrp', $data['mrp'])
                              ->where('offer_price', $data['offer_price'])
                              ->where('batch_no', $data['update_batch_no'])
                              ->update([
                                  'product_qty' => 0
                              ]);

                          //update with qty
                          price_master::
                          where('product_id',$data['product_id'])
                              ->where('company_id', $vv)
                              ->where('product_mrp', $data['mrp'])
                              ->where('offer_price', $data['offer_price'])
                              ->where('batch_no', $data['update_batch_no'])
                              ->limit(1)
                              ->orderBy('price_master_id','DESC')
                              ->update([
                                  'product_qty' => $price['totalqty']
                              ]);
                      }
                  }

                   //update price in inward product detail
                   inward_product_detail::where('product_id', $data['product_id'])
                       ->whereIn('company_id', $data['company_id'])
                       ->where('product_mrp', $data['update_mrp_on'])
                       ->where('offer_price', $data['update_offer_price_on'])
                       ->where('batch_no', $data['update_batch_no'])
                       ->update([
                           'product_mrp' => $data['mrp'],
                           'offer_price' => $data['offer_price'],
                           'selling_gst_amount' => $data['sell_gst_amount'],
                           'selling_gst_percent' => $data['selling_gst_percent'],
                           'sell_price' => $data['selling_rate'],
                           'profit_amount' => DB::raw('sell_price-cost_price'),
                           'profit_percent' => DB::raw('(profit_amount*100)/cost_price'),
                       ]);


                   //get value from stock_transfer_detail


                   $stock_transfer_detail = stock_transfer_detail::where('product_id', $data['product_id'])
                       ->with('stock_transfer_no')
                       ->whereHas('stock_transfer_no', function ($q) use ($data) {
                           $q->whereIn('store_id', $data['company_id']);
                       })
                       ->where('product_mrp', $data['update_mrp_on'])
                       ->where('offer_price', $data['update_offer_price_on'])
                       ->where('batch_no', $data['update_batch_no'])
                       ->whereNull('deleted_at')
                       ->groupBy('stock_transfer_id')->get();


                   if (isset($stock_transfer_detail) && $stock_transfer_detail != '' && !empty($stock_transfer_detail)) {

                       foreach ($stock_transfer_detail AS $k => $v) {
                           stock_transfer_detail::where('product_id', $data['product_id'])
                               ->where('company_id', $v['company_id'])
                               ->where('product_mrp', $data['update_mrp_on'])
                               ->where('offer_price', $data['update_offer_price_on'])
                               ->where('stock_transfers_detail_id', $v['stock_transfers_detail_id'])
                               ->where('stock_transfer_id', $v['stock_transfer_id'])
                               ->where('batch_no', $data['update_batch_no'])
                               ->update([
                                   'product_mrp' => $data['mrp'],
                                   'offer_price' => $data['offer_price'],
                                   'selling_gst_amount' => $data['sell_gst_amount'],
                                   'selling_gst_percent' => $data['selling_gst_percent'],
                                   'sell_price' => $data['selling_rate'],
                                   'profit_amount' => DB::raw('sell_price-cost_price'),
                                   'profit_percent' => DB::raw('(profit_amount*100)/cost_price'),
                               ]);


                           $total_sum = stock_transfer_detail::select(DB::raw('SUM(product_mrp) AS total_product_mrp'),
                               DB::raw('SUM(sell_price) AS total_sell_price'),
                               DB::raw('SUM(offer_price) AS total_offerprice'))
                               ->where('stock_transfer_id', $v['stock_transfer_id'])
                               ->first();

                           //update total mrp,offer price etc in stock_transfer
                           if (isset($total_sum) && $total_sum != '') {
                               stock_transfer::where('company_id', $v['company_id'])
                                   ->where('stock_transfer_id', $v['stock_transfer_id'])
                                   ->update([
                                       'total_mrp' => $total_sum['total_product_mrp'],
                                       'total_sellprice' => $total_sum['total_sell_price'],
                                       'total_offerprice' => $total_sum['total_offerprice']
                                   ]);
                           }
                       }
                   }
               }

                DB::commit();

                return json_encode(array("Success"=>"True","Message"=>"Price Updated Successfully."));
            }
            catch (\Illuminate\Database\QueryException $e)
            {
                DB::rollback();
                return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
            }


        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong!"));
        }
        exit;
    }
}
