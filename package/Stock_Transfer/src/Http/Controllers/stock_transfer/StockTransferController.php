<?php
namespace Retailcore\Stock_Transfer\Http\Controllers\stock_transfer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Products\Models\product\product_image;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\Sales\Models\reference;
use Retailcore\CreditBalance\Models\customer_creditaccount;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\GST_Slabs\Models\GST_Slabs\gst_slabs_master;
use Retailcore\Customer\Models\customer\customer;
use Retailcore\Customer\Models\customer\customer_address_detail;
use Retailcore\Customer_Source\Models\customer_source\customer_source;
use Retailcore\Sales\Models\payment_method;
use App\state;
use App\country;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\CreditNote\Models\customer_creditnote;
use Retailcore\CreditNote\Models\creditnote_payment;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail;
use Auth;
use DB;
use Log;
class StockTransferController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $ppvalues = array();
        $state    = state::all();
        $country  = country::all();

       $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->orderBy('payment_order','ASC')->get();
       $cstate_id = company_profile::select('state_id','billtype','bill_number_prefix','tax_type','billprint_type','series_type','bill_number_prefix','country_id')->where('company_id',Auth::user()->company_id)->get();
       $last_invoice_id = sales_bill::where('company_id',Auth::user()->company_id)->get()->max('sales_bill_id');

        if($last_invoice_id == '')
        {
            $last_invoice_id = 1;
        }
        else
        {
            $last_invoice_id = $last_invoice_id  + 1;
        }



        $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
        $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

        $invoiceno          =       $cstate_id[0]['bill_number_prefix'].$last_invoice_id.'/'.$f1.'-'.$f2;


        $chargeslist      =   product::select('product_id','product_name','sell_gst_percent')
                              ->where('company_id',Auth::user()->company_id)
                              ->where('item_type','=',2)
                              ->get();

        $customer_source = customer_source::where('company_id',Auth::user()->company_id)
          ->where('deleted_at','=',NULL)
          ->orderBy('customer_source_id','DESC')->get();

       $sales_type=3;
       $get_store = company_relationship_tree::where('warehouse_id', '=', Auth::user()->company_id)
        ->with('company_profile')
        ->get();


        return view('sales::sales_bill',compact('payment_methods','invoiceno','state','country','chargeslist','ppvalues','customer_source','sales_type','get_store'));
    }
    public function stock_transfer_view()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $company_id = Auth::User()->company_id;

        $stock_view = stock_transfer::where('company_id', '=', $company_id)->whereNull('deleted_at')->orderBy('stock_transfer_id','DESC')->paginate(10);


        return view('stock_transfer::stock_transfer/stock_transfer_view',compact('stock_view'));
    }

    public function stock_transfer_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $data = $request->all();

            $sort_by = isset($data['sortby'])?$data['sortby']:'stock_transfer_id';
            $sort_type = isset($data['sorttype'])?$data['sorttype']:'desc';
            $query = isset($data['query']) ? $data['query'] : '';
            $query = str_replace(" ", "", $query);


            $stock_view = stock_transfer::where('company_id', '=', Auth::User()->company_id)
                ->whereNull('deleted_at');


            if($query != '')
            {
                if ($query['from_date'] != '' || $query['to_date'] != '')
                {
                    $from_date = date("Y-m-d", strtotime($query['from_date']));
                    $to_date = date("Y-m-d", strtotime($query['to_date']));

                    $stock_view = $stock_view->whereRaw("STR_TO_DATE(stock_transfer_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                }
                if ($query['stock_transfer_no'] != '')
                {
                    $stock_view = $stock_view->where('stock_transfer_no', '=', $query['stock_transfer_no'])->where('deleted_at', '=', NULL);
                }
            }

            $stock_view = $stock_view->orderBy($sort_by, $sort_type)->paginate(10);


            return view('stock_transfer::stock_transfer/stock_transfer_viewdata',compact('stock_view'))->render();
        }
    }


    public function stock_transfer_detail_view()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $company_id = Auth::User()->company_id;
        $stock_transferdetail_view = stock_transfer_detail::where('company_id', '=', $company_id)
        ->with('product_data')->orderBy('stock_transfers_detail_id','DESC')->whereNull('deleted_at')->paginate(10);
        return view('stock_transfer::stock_transfer/stock_transfer_detail_view',compact('stock_transferdetail_view'));
    }


    public function stock_transfer_detail_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {

            $data = $request->all();

            $sort_by = isset($data['sortby'])?$data['sortby']:'stock_transfers_detail_id';
            $sort_type = isset($data['sorttype'])?$data['sorttype']:'desc';
            $query = isset($data['query']) ? $data['query'] : '';
            $query = str_replace(" ", "", $query);

            $stock_transferdetail_view = stock_transfer_detail::where('company_id', '=', Auth::User()->company_id)
                ->with('product_data');

            if($query != '') {
                if ($query['from_date'] != '' || $query['to_date'] != '') {
                    $from_date = date("Y-m-d", strtotime($query['from_date']));
                    $to_date = date("Y-m-d", strtotime($query['to_date']));
                    $stock_transferdetail_view->whereHas('stock_transfer_no', function ($q) use ($from_date, $to_date) {
                        $q->whereRaw("STR_TO_DATE(stock_transfer_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                    });
                }
                if ($query['stock_transfer_no'] != ''){
                    $stock_transferdetail_view->whereHas('stock_transfer_no', function ($q) use ($query) {
                        $q->whereRaw("stock_transfer_no='" . $query['stock_transfer_no'] . "'");
                    });
            }
            }

            $stock_transferdetail_view = $stock_transferdetail_view->orderBy($sort_by, $sort_type)->paginate(10);


            return view('stock_transfer::stock_transfer/stock_transfer_detail_viewdata',compact('stock_transferdetail_view'))->render();
        }
    }

    public function searchproduct(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->search_val !='')
        {
            $json = [];
            $result = product::where('company_id',Auth::user()->company_id)
                ->select('product_name','product_system_barcode','product_id','hsn_sac_code')
                ->where('product_name', 'LIKE', "%$request->search_val%")
                ->where('item_type','=',1)
                ->orWhere('product_system_barcode', 'LIKE', "%$request->search_val%")
                ->orWhere('hsn_sac_code', 'LIKE', "%$request->search_val%")
                ->with('stock_transfer_price_master')
//                ->take(10)
                ->get();

            if(sizeof($result) != 0)
            {

                $cnt = 0;
                foreach($result as $productkey=>$productvalue)
                {

                    if(isset($productvalue['price_master']) && $productvalue['price_master'] != '' && isset($productvalue['price_master']) && $productvalue['price_master'] != '')
                    {
                        foreach($productvalue['price_master'] as $key=>$batchvalue)
                        {
                            $cnt++;
                            $json[$cnt]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'].'_'.$batchvalue['batch_no'].'_'.$batchvalue['offer_price'];
                            $json[$cnt]['price_master_id'] = $batchvalue['price_master_id'];
                            $json[$cnt]['batch_no'] = $batchvalue['batch_no'];
                            $json[$cnt]['offer_price'] = $batchvalue['offer_price'];
                        }
                    }
                }

            }
            return json_encode(array("Data"=>$json));
        }
        else
        {
          $json = [];
          return json_encode($json);
        }
    }

    public function product_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $price_master_id      =  $request->price_master_id;

        $query = price_master::where('price_master_id',$price_master_id)
                ->with('product')
                ->get();

        if(sizeof($query) != 0)
        {
            $overallqty =  price_master::where('company_id',Auth::user()->company_id)
                 ->where('product_id','=',$query[0]['product_id'])
                 ->sum('product_qty');

            return json_encode(array("Success"=>"True","Data"=>$query,"Stock"=>$overallqty));
        }
    }
    public function search_pricedetail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $result = price_master::where('price_master_id',$request->price_id)
            ->where('company_id',Auth::user()->company_id)
            ->get();
      return json_encode(array("Success"=>"True","Data"=>$result));
    }

    public function search_batchdetail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $result = price_master::where('price_master_id',$request->batch_id)
                ->where('company_id',Auth::user()->company_id)
                ->get();
          return json_encode(array("Success"=>"True","Data"=>$result));

    }

    public function stock_create(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $data = $request->all();

        $stock_transfer = $data['stock_transfer'];
        $stock_transfer_detail = $data['stock_transfer_detail'];

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $created_by = $userId;
        $cstate_id = company_profile::select('state_id','billtype','bill_number_prefix','tax_type','billprint_type','series_type','bill_number_prefix','country_id')->where('company_id',Auth::user()->company_id)->get();

        try {
            DB::beginTransaction();

            $stock_transfer['created_by'] =  $created_by;
            $stock_transfer['store_type'] =  2;


            $stocktransfer = Stock_Transfer::updateOrCreate(
               ['stock_transfer_id' => $stock_transfer['stock_transfer_id'],
                'company_id'=>$company_id],
               $stock_transfer);

            $stock_id = $stocktransfer->stock_transfer_id;

            if($cstate_id[0]['series_type']==1)
            {
                $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
                $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

                $stock_transfer_no          =       $cstate_id[0]['bill_number_prefix'].$stock_id.'/'.$f1.'-'.$f2;
            }
            if($stock_transfer['stock_transfer_id']=='' || $stock_transfer['stock_transfer_id']==null)
            {
                stock_transfer::where('stock_transfer_id',$stock_id)->update(array(
                    'stock_transfer_no' => $stock_transfer_no
                ));
            }

            foreach($stock_transfer_detail AS $stockkey=>$stockvalue)
            {

                $stockvalue['stock_transfer_id']                    =    $stock_id;
                $stockvalue['supplier_gst_id']                      =    2;
                $stockvalue['created_by']                           =    $created_by;

                $product_qty = $stockvalue['product_qty'];

                if($stockvalue['oldprice_master_id'] != '')
                {
                    price_master::where('price_master_id',$stockvalue['oldprice_master_id'])->update(array(
                        'modified_by' => Auth::User()->user_id,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'product_qty' => DB::raw('product_qty + '.$stockvalue['oldqty'])
                    ));
                }
                price_master::where('price_master_id',$stockvalue['price_master_id'])->update(array(
                  'modified_by' => Auth::User()->user_id,
                  'updated_at' => date('Y-m-d H:i:s'),
                  'product_qty' => DB::raw('product_qty - '.$stockvalue['product_qty'])
                ));
// =====================================================================================================
                $oldinward_product_detail_id       =     explode(',',substr($stockvalue['inward_product_detail_id'],0,-1));
                $oldinward_product_qtys      =     explode(',',substr($stockvalue['inward_product_qtys'],0,-1));


                $ccount    =   0;
                $icount    =   0;
                $pcount    =   0;
                $done      =   0;
                $firstout  =   0;
                $inward_product_detail_id    =  '';
                $inward_product_qtys   =  '';
                $averagecost  =  0;

                if($stockvalue['price_master_id']!=$stockvalue['oldprice_master_id'] || $stockvalue['product_qty']!=$stockvalue['oldqty'])
                {
                   if($stockvalue['stock_transfers_detail_id'] !='')
                   {
                       foreach($oldinward_product_detail_id as $l=>$val)
                        {

                            if($oldinward_product_qtys[$l] != '' && $oldinward_product_qtys[$l] != 'undefine') {
                                 inward_product_detail::where('company_id', Auth::user()->company_id)
                                    ->where('inward_product_detail_id', $oldinward_product_detail_id[$l])
                                    ->update(array(
                                        'modified_by' => Auth::User()->user_id,
                                        'updated_at' => date('Y-m-d H:i:s'),
                                        'pending_return_qty' => DB::raw('pending_return_qty + ' . $oldinward_product_qtys[$l])
                                    ));
                            }
                        }
                    }
                    if($stockvalue['product_qty']>0)
                    {
                        $prid = price_master::select('offer_price','batch_no')
                            ->where('company_id',Auth::user()->company_id)
                            ->where('price_master_id',$stockvalue['price_master_id'])->get();
                        $qquery = inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                            ->where('product_id',$stockvalue['product_id'])
                            ->where('company_id',Auth::user()->company_id)
                            ->where('pending_return_qty','>',0);

                        if($stockvalue['batch'] != '')
                        {
                            $qquery->where('batch_no',$prid[0]['batch_no']);
                        }
                        $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                        foreach($inwarddetail as $inwarddata)
                        {
                            if($inwarddata['pending_return_qty'] >= $product_qty && $firstout==0)
                            {
                                if($done == 0)
                                {
                                    $inward_product_detail_id    .=   $inwarddata['inward_product_detail_id'].',';
                                    $inward_product_qtys   .=   $product_qty.',';
                                    inward_product_detail::where('company_id',Auth::user()->company_id)
                                    ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                    ->update(array(
                                      'modified_by' => Auth::User()->user_id,
                                      'updated_at' => date('Y-m-d H:i:s'),
                                      'pending_return_qty' => DB::raw('pending_return_qty - '.$stockvalue['product_qty'])
                                    ));
                                    $pcount++;
                                    $done++;
                                }
                            }
                            else
                            {
                                if($pcount==0 && $done == 0 && $icount==0)
                                {
                                    if($product_qty  > $inwarddata['pending_return_qty'])
                                    {
                                        $inward_product_detail_id    .=   $inwarddata['inward_product_detail_id'].',';
                                        $inward_product_qtys   .=   $inwarddata['pending_return_qty'].',';
                                        $ccount       =   $product_qty  - $inwarddata['pending_return_qty'];
                                        inward_product_detail::where('company_id',Auth::user()->company_id)
                                        ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                        ->update(array(
                                          'modified_by' => Auth::User()->user_id,
                                          'updated_at' => date('Y-m-d H:i:s'),
                                          'pending_return_qty' => DB::raw('pending_return_qty - '.$inwarddata['pending_return_qty'])
                                        ));
                                    }
                                    else
                                    {
                                        $inward_product_detail_id    .=   $inwarddata['inward_product_detail_id'].',';
                                        $inward_product_qtys   .=   $product_qty.',';
                                        $ccount   =   $product_qty  - $inwarddata['pending_return_qty'];
                                        inward_product_detail::where('company_id',Auth::user()->company_id)
                                        ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                        ->update(array(
                                          'modified_by' => Auth::User()->user_id,
                                          'updated_at' => date('Y-m-d H:i:s'),
                                          'pending_return_qty' => DB::raw('pending_return_qty - '.$product_qty)
                                        ));
                                    }
                                    if($ccount > 0)
                                    {
                                       $firstout++;
                                       $product_qty   =   $product_qty  - $inwarddata['pending_return_qty'];
                                    }
                                    if($ccount <= 0)
                                    {
                                       $firstout++;
                                       $icount++;
                                    }
                                }
                            }
                        }
                    }

                    if($inward_product_detail_id!='')
                    {
                      $stockvalue['inward_product_detail_id']                          =    $inward_product_detail_id;
                      $stockvalue['inward_product_qtys']                         =    $inward_product_qtys;
                    }
                    else
                    {
                      // $stockvalue['inward_product_detail_id']                          =    $stockvalue['inward_product_detail_id'];
                      // $stockvalue['inward_product_qtys']                         =    $stockvalue['inward_product_qtys'];
                    }

                    $total_price  = 0;
                    if($inward_product_detail_id !='' || $inward_product_detail_id !=null)
                    {
                        $cinward_product_detail_id  = explode(',' ,substr($inward_product_detail_id,0,-1));
                        $cinward_product_qtys = explode(',' ,substr($inward_product_qtys,0,-1));

                        foreach($cinward_product_detail_id as $inidkey=>$inids)
                        {
                            $cost_price =  inward_product_detail::select('cost_rate')->find($inids);
                            $total_price += $cost_price['cost_rate'] * $cinward_product_qtys[$inidkey];
                        }
                        $averagecost      +=   $total_price;
                    }
                    else
                    {
                        $averagecost      =   0;
                    }

                    $stockcostprice        =  $averagecost / $stock_transfer_detail[0]['product_qty'];

                    unset($stockvalue['price_master_id']);
                    unset($stockvalue['oldprice_master_id']);
                    unset($stockvalue['batch']);
                    unset($stockvalue['oldqty']);
                    unset($stockvalue['prodgstamt']);
                    unset($stockvalue['totalselprice']);
                    unset($stockvalue['totalofferprice']);

                    $stockvalue['cost_rate'] = $stockcostprice;
                    $stockvalue['cost_price'] = $stockcostprice;
                    $stockvalue['base_price'] = $stockcostprice;
                    //$stockvalue['werehouse_id'] = $company_id;
                    $stockvalue['total_cost_rate_with_qty'] = $averagecost;
                    $stockvalue['total_cost'] = $averagecost;

                    $stocktransferdetail = Stock_Transfer_Detail::updateOrCreate([
                        'stock_transfer_id' => $stock_id,
                        'company_id'=>$company_id,
                        'stock_transfers_detail_id'=>$stockvalue['stock_transfers_detail_id'],
                        'supplier_gst_id'=>2,
                        'product_id'=>$stockvalue['product_id'],],
                        $stockvalue);
                }
            }
            DB::commit();
        }

        catch(\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }
        if ($stocktransfer) {
            if ($stock_transfer['stock_transfer_id'] != '') {
                return json_encode(array("Success" => "True", "Message" => "stock has been successfully updated."));
            } else {
                return json_encode(array("Success" => "True", "Message" => "Stock has been successfully added."));
            }
        } else {
            return json_encode(array("Success" => "False", "Message" => "Something Went Wrong"));
        }
    }

    public function edit_stock_transfer(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $stock_transfer_id = decrypt($request->stock_transfer_id);

        $stock_transfer = stock_transfer::where([
            ['stock_transfer_id','=',$stock_transfer_id],
            ['company_id',Auth::user()->company_id]])
            ->with('stock_transfer_detail.product.editprice_master','stock_transfer_detail.product.colour','stock_transfer_detail.product.size','stock_transfer_detail.product.uqc')
            ->select('*')
            ->first();

        return json_encode(array("Success"=>"True","Data"=>$stock_transfer,"url"=>"stock_transfer"));
    }

    public function view_products_stock_transfer(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $stock_transfer_id = decrypt($request->stock_transfer_id);

        $stock_transfer = stock_transfer::where([
            ['stock_transfer_id','=',$stock_transfer_id],
            ['company_id',Auth::user()->company_id]])
            ->with('stock_transfer_detail.product.uqc')
            ->with('stock_transfer_detail.product.product_features_relationship')
            ->with('store_name')
            ->with('warehouse')
            ->select('*')
            ->where('deleted_at','=',NULL)
            ->first();

        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($stock_transfer['stock_transfer_detail'] AS $key=>$v) {

            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $stock_transfer['stock_transfer_detail'][$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }

        $data = json_encode($stock_transfer);

         //TO GET NEXT ID OF SELECTED ID IN POPUP
         $next_id = stock_transfer::where('stock_transfer_id', '>', $stock_transfer_id)->where('company_id',Auth::user()->company_id)->min('stock_transfer_id');


                 $next = '';
                 if(isset($next_id) && $next_id != '')
                {
                    $next = encrypt($next_id);
                }

                           //TO GET PREVIOUS ID OF SELECTED ID IN POPUP
                         $previous = stock_transfer::where('stock_transfer_id', '<', $stock_transfer_id)->where('company_id',Auth::user()->company_id)->max('stock_transfer_id');

                         $prev = '';
                         if(isset($previous) && $previous != '')
                            {
                                 $prev = encrypt($previous);
                            }

                 return json_encode(array("Success"=>"True","Data"=>$data,"next"=>$next,"previous"=>$prev,));

    }
}


