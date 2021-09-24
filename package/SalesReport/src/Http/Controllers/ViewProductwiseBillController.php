<?php

namespace Retailcore\SalesReport\Http\Controllers;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\SalesReport\Models\productwiseBills_export;
use Illuminate\Http\Request;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\reference;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\SalesReturn\Models\returnbill_product;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\Sales\Models\payment_method;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\ProductAge_Range\Models\productage_range;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail;
use Retailcore\Consignment\Models\consign_bill;
use Retailcore\Consignment\Models\consign_products_detail;
use Retailcore\DamageProducts\Models\damageproducts\damage_product;
use Retailcore\DamageProducts\Models\damageproducts\damage_product_detail;
use Retailcore\Debit_Note\Models\debit_note\debit_note;
use Retailcore\Debit_Note\Models\debit_note\debit_product_detail;
use Retailcore\SalesReport\Models\productagereport_export;
use App\state;
use App\country;
use Auth;
use DB;
use Retailcore\Customer\Models\customer\customer;
use Retailcore\Products\Models\product\product;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\company;
use Retailcore\Customer\Models\customer\customer_address_detail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;
use Log;
class ViewProductwiseBillController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

          $compname       =   company::where('company_id',Auth::user()->company_id)    
                                        ->first();                                           
          $companyname    =   $compname['company_name'];  
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

        $date        =    date("Y-m-d");

        $squery = sales_product_detail::where('company_id',Auth::user()->company_id)
            ->where('deleted_by','=',NULL)
            ->with([
                    'batchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
            ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($date){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'");
            })
            ->with('sales_bill.reference')
            ->with('product.product_features_relationship')
            ->orderBy('sales_bill_id', 'DESC');


        $rquery  =   return_product_detail::where('company_id',Auth::user()->company_id)
            ->where('deleted_by','=',NULL)
            ->with([
                    'rbatchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
            ->with('return_bill')->whereHas('return_bill',function ($q) use ($date){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'");
                  })
            ->with('return_bill.reference')
            ->with('product')
            ->orderBy('return_bill_id', 'DESC');


        $scustom   =   collect();
        $sdata     =   $scustom->merge($squery->get());

        $rcustom   =   collect();
        $rdata     =   $rcustom->merge($rquery->get());

        $sales_room_details  = $squery->paginate(10);

        $product_features =  ProductFeatures::getproduct_feature('');


        foreach ($sales_room_details AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $sales_room_details[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }


        $return_room_details  = $rquery->paginate(10);

        foreach ($return_room_details AS $rkey=>$rv) {
                if (isset($rv['product']['product_features_relationship']) && $rv['product']['product_features_relationship'] != '')
                {
                    foreach($product_features AS $rkk => $rvv)
                    {
                        $html_id = $rvv['html_id'];

                        if ($rv['product']['product_features_relationship'][$html_id] != '' && $rv['product']['product_features_relationship'][$html_id] != NULL)
                        {

                            $nm = product::feature_value($rvv['product_features_id'], $rv['product']['product_features_relationship'][$html_id]);

                            $return_room_details[$rkey]['product'][$html_id] = $nm;
                        }
                    }
                }
            }


            $totaltariff = 0;
            $totaldiscount = 0;
            $taxabletariff = 0;
            $totalcgst = 0;
            $totalsgst = 0;
            $totaligst = 0;
            $grandtotal = 0;
            $halfchargesgst = 0;

            $rtotaltariff = 0;
            $rtotaldiscount = 0;
            $rtaxabletariff = 0;
            $rtotalcgst = 0;
            $rtotalsgst = 0;
            $rtotaligst = 0;
            $rgrandtotal = 0;
            $rhalfchargesgst =0;

            foreach ($sdata as $totsales)
            {

                $totaltariff    +=  $totsales->sellingprice_before_discount*$totsales->total_days;
                $totaldiscount  +=  $totsales->discount_amount;
                $taxabletariff  +=  $totsales->sellingprice_afteroverall_discount;
                $halfchargesgst   =   $totsales->chargesgst / 2;
                if($tax_type==1)
                {
                    $totaligst       +=  $totsales->igst_amount + $totsales->chargesgst;
                }
                else
                {
                    if($totsales['sales_bill']['state_id'] == $company_state)
                    {
                      $totalcgst       +=  $totsales->cgst_amount + $halfchargesgst;
                      $totalsgst       +=  $totsales->sgst_amount + $halfchargesgst;
                    }
                    else
                    {
                      $totaligst       +=  $totsales->igst_amount + $totsales->chargesgst;
                    }
                }

                $grandtotal     +=  $totsales->total_amount;
            }
            foreach ($rdata as $rtotsales)
            {

                $rtotaltariff   +=  $rtotsales->sellingprice_before_discount*$rtotsales->total_days;
                $rtotaldiscount  +=  $rtotsales->discount_amount;
                $rtaxabletariff  +=  $rtotsales->sellingprice_afteroverall_discount;
                $rhalfchargesgst =   $rtotsales->chargesgst / 2;
                if($tax_type==1)
                {
                    $rtotaligst       +=  $rtotsales->igst_amount + $rtotsales->chargesgst;
                }
                else
                {
                    if($rtotsales['return_bill']['state_id'] == $company_state)
                    {
                      $rtotalcgst       +=  $rtotsales->cgst_amount + $halfchargesgst;
                      $rtotalsgst       +=  $rtotsales->sgst_amount + $halfchargesgst;
                    }
                    else
                    {
                      $rtotaligst       +=  $rtotsales->igst_amount + $rtotsales->chargesgst;
                    }
                }

                $rgrandtotal     +=  $rtotsales->total_amount;
            }

            $todaytaxable  = $taxabletariff - $rtaxabletariff;
            $todaycgst     = $totalcgst - $rtotalcgst;
            $todaysgst     = $totalsgst - $rtotalsgst;
            $todayigst     = $totaligst - $rtotaligst;
            $todaygrand    = $grandtotal - $rgrandtotal;


         return view('salesreport::view_productwise_bill',compact('sales_room_details','todaytaxable','todaycgst','todaysgst','todaygrand','todayigst','company_state','return_room_details','tax_type','taxname','get_store','companyname'));

    }

    public function productcode_search(Request $request)
    {

      if($request->search_val !='')
        {
         $json = [];
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $result = product::where('deleted_at','=',NULL)
                  ->whereNotNull('product_code')
                  ->select('product_code','product_id')
                  ->where('product_code','LIKE', "%$request->search_val%")
                  ->with('product_price_master')
                  ->whereHas('product_price_master',function ($q) {
                            $q->where('company_id',Auth::user()->company_id);
                     })
                  ->get();

        if(!empty($result))
            {
           
                foreach($result as $productkey=>$productvalue){

                       $json[$productkey]['label'] = $productvalue['product_code'];
                       $json[$productkey]['product_id'] = $productvalue['product_id'];

                      
                }
            }
           
            return json_encode($json);
        }
        else
        {
          $json = [];
          return json_encode($json);
        }
       
    }

    function datewise_product_billdetail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {

          $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();
                $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title','bill_calculation')->where('company_id',Auth::user()->company_id)->get();
                $company_state   = $state_id[0]['state_id'];
                $tax_type        = $state_id[0]['tax_type'];
                $tax_title       = $state_id[0]['tax_title'];
                $taxname         = $tax_type==1?$tax_title:'IGST';
                $data            =      $request->all();
                $sort_by = $data['sortby'];
                $sort_type = $data['sorttype'];
                $query = isset($data['query']) ? $data['query']  : '';

                 $dynamic_search = array();
                if($query != '')
                {
                    foreach ($query as $key => $value)
                    {
                        if (strpos($key,'dynamic_') === 0)
                        {
                            if($value != '')
                            {
                                $dynamic_search[$key] = $value;
                                unset($query[$key]);
                            }
                            else
                            {
                                unset($query[$key]);
                            }
                        }
                    }
                }

            if(isset($query) && $query != '' && isset($query['store_name']) && $query['store_name'] != '')
            {
                 $company_id      =   $query['store_name'];
                 $compname        =   company_profile::where('company_profile_id',$company_id)    
                                            ->first();                                           
                 $companyname    =   $compname['full_name']; 
            }
            else
            {
                $company_id      =   Auth::user()->company_id;
                $compname        =   company::where('company_id',$company_id)    
                                            ->first();                                           
                 $companyname    =   $compname['company_name']; 
            }

            $squery           =      sales_product_detail::select('*')->where('deleted_at','=',NULL)->where('company_id',$company_id);
            $rquery           =      return_product_detail::select('*')->where('deleted_at','=',NULL)->where('company_id',$company_id);

            if(isset($query) && $query != '' && $query['customerid'] != '')
            {

                if(strpos($query['customerid'], '_') !== false)
                {
                    $cusname   =   explode('_',$query['customerid']);
                    $cus_name   =  $cusname[0];
                    $cus_mobile  =  $cusname[1];
                }
                else
                {
                    $cus_name   =   $query['customerid'];
                    $cus_mobile =   $query['customerid'];
                }

                $totalcustomer = customer::select('customer_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('customer_name', 'LIKE', "%$cus_name%")
                 ->orwhere('customer_mobile', 'LIKE', "%$cus_mobile%")
                 ->get();

                 $totalsalesid = sales_bill::select('sales_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('customer_id',$totalcustomer)
                 ->get();

                  $totalreturnid = return_bill::select('return_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('customer_id',$totalcustomer)
                 ->get();

                 $squery->whereIn('sales_bill_id',$totalsalesid);
                 $rquery->whereIn('return_bill_id',$totalreturnid);
            }
            if(isset($query) && $query != '' && $query['barcode'] != '')
            {
                if(strpos($query['barcode'], '_') !== false)
                {
                    $prodbarcode   =   explode('_',$query['barcode']);
                    $prod_barcode      =  $prodbarcode[0];
                    $prod_name    =  $prodbarcode[1];
                }
                else
                {
                    $prod_barcode      =  $query['barcode'];
                    $prod_name         =  $query['barcode'];
                }
                 $product = product::select('product_id')
                ->where('product_system_barcode','LIKE', "%$prod_barcode%")
                ->orWhere('product_name','LIKE',"%$prod_name%")
                ->with('product_price_master')
                ->whereHas('product_price_master',function ($q) use($company_id){
                        $q->where('company_id',$company_id);
                 })
                ->get();

                $squery->whereIn('product_id',$product);
                $rquery->whereIn('product_id',$product);
            }
            if(isset($query) && $query != '' && $query['productcode'] != '')
            {
               
                 $product = product::select('product_id')
                ->where('product_code',$query['productcode'])
                ->with('product_price_master')
                ->whereHas('product_price_master',function ($q) use($company_id){
                        $q->where('company_id',$company_id);
                 })
                ->get();

                $squery->whereIn('product_id',$product);
                $rquery->whereIn('product_id',$product);
            }
            if(isset($query) && $query != '' && $query['billno'] != '')
            {


                  $tbill_no  =  sales_bill::select('sales_bill_id')->where('bill_no', 'like', '%'.$query['billno'].'%')->where('company_id',$company_id)->get();

                  $squery->whereIn('sales_bill_id', $tbill_no);

                  $treturn_id = return_bill::select('return_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('sales_bill_id',$tbill_no)
                 ->get();

                  $rquery->whereIn('return_bill_id', $treturn_id);
            }
            if(isset($query) && $query != '' && $query['reference_name'] != '')
            {

                $ref_name = $query['reference_name'];

                $totalrefid = reference::select('reference_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('reference_name', 'LIKE', "%$ref_name%")
                 ->get();

                 $totalsalesrid = sales_bill::select('sales_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('reference_id',$totalrefid)
                 ->get();

                  $totalreturnrid = return_bill::select('return_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('reference_id',$totalrefid)
                 ->get();

                 $squery->whereIn('sales_bill_id',$totalsalesrid);
                 $rquery->whereIn('return_bill_id',$totalreturnrid);
            }
            if(isset($query) && $query != '' && $query['from_date'] != '' && $query['to_date'] != '')
            {

                 $rstart           =      date("Y-m-d",strtotime($query['from_date']));
                 $rend             =      date("Y-m-d",strtotime($query['to_date']));

                 $squery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
                 $rquery->with('return_bill')->whereHas('return_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
            }
            if($query['from_date'] == '' && $query['to_date'] == '' && $query['reference_name'] == '' && $query['billno'] == '' && $query['customerid'] == '' && $query['barcode'] == '' && $query['productcode'] == '')
            {
                 $rstart           =      date("Y-m-d");
                 $rend             =      date("Y-m-d");

                 $squery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
                 $rquery->with('return_bill')->whereHas('return_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
            }

            if(isset($dynamic_search) && $dynamic_search !='' &&  !empty($dynamic_search))
            {

                $squery =  $squery->with('product.product_features_relationship')
                    ->whereHas('product.product_features_relationship',function ($q) use($dynamic_search)
                    {
                     foreach($dynamic_search AS $k=>$v)
                      {
                          $q->where(DB::raw($k),$v);
                      }
                    });
                  $rquery =  $rquery->with('product.product_features_relationship')
                  ->whereHas('product.product_features_relationship',function ($q) use($dynamic_search)
                  {
                   foreach($dynamic_search AS $k=>$v)
                    {
                        $q->where(DB::raw($k),$v);
                    }
                  });
            }

            $scustom   =   collect();
            $sdata     =   $scustom->merge($squery->get());

            $product_features =  ProductFeatures::getproduct_feature('');
            $sales_room_details = $squery->with([
                    'batchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])->with('sales_bill.reference')->orderBy($sort_by, $sort_type)->paginate(10);

            foreach ($sales_room_details AS $key=>$v) {
                if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
                {
                    foreach($product_features AS $kk => $vv)
                    {
                        $html_id = $vv['html_id'];

                        if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                        {

                            $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                            $sales_room_details[$key]['product'][$html_id] = $nm;
                        }
                    }
                }
            }

            $rcustom   =   collect();
            $rdata     =   $rcustom->merge($rquery->get());

            $return_room_details = $rquery->with([
                    'rbatchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])->with('return_bill.reference')->orderBy('return_bill_id','DESC')->paginate(10);

            foreach ($return_room_details AS $rkey=>$rv) {
                if (isset($rv['product']['product_features_relationship']) && $rv['product']['product_features_relationship'] != '')
                {
                    foreach($product_features AS $rkk => $rvv)
                    {
                        $html_id = $rvv['html_id'];

                        if ($rv['product']['product_features_relationship'][$html_id] != '' && $rv['product']['product_features_relationship'][$html_id] != NULL)
                        {

                            $nm = product::feature_value($rvv['product_features_id'], $rv['product']['product_features_relationship'][$html_id]);

                            $return_room_details[$rkey]['product'][$html_id] = $nm;
                        }
                    }
                }
            }


            $totaltariff = 0;
            $totaldiscount = 0;
            $taxabletariff = 0;
            $totalcgst = 0;
            $totalsgst = 0;
            $totaligst = 0;
            $grandtotal = 0;
            $halfchargesgst = 0;

            $rtotaltariff = 0;
            $rtotaldiscount = 0;
            $rtaxabletariff = 0;
            $rtotalcgst = 0;
            $rtotalsgst = 0;
            $rtotaligst = 0;
            $rgrandtotal = 0;
            $rhalfchargesgst =0;

            foreach ($sdata as $totsales)
            {

                $totaltariff    +=  $totsales->sellingprice_before_discount*$totsales->total_days;
                $totaldiscount  +=  $totsales->discount_amount;
                $taxabletariff  +=  $totsales->sellingprice_afteroverall_discount;
                $halfchargesgst   =   $totsales->chargesgst / 2;
                if($tax_type==1)
                {
                      $totaligst       +=  $totsales->igst_amount + $totsales->chargesgst;
                }
                else
                {
                    if($totsales['sales_bill']['state_id'] == $company_state)
                    {
                      $totalcgst       +=  $totsales->cgst_amount + $halfchargesgst;
                      $totalsgst       +=  $totsales->sgst_amount + $halfchargesgst;
                    }
                    else
                    {
                      $totaligst       +=  $totsales->igst_amount + $totsales->chargesgst;
                    }
                }

                $grandtotal     +=  $totsales->total_amount;
            }
            foreach ($rdata as $rtotsales)
            {

                $rtotaltariff   +=  $rtotsales->sellingprice_before_discount*$rtotsales->total_days;
                $rtotaldiscount  +=  $rtotsales->discount_amount;
                $rtaxabletariff  +=  $rtotsales->sellingprice_afteroverall_discount;
                $rhalfchargesgst =   $rtotsales->chargesgst / 2;
                if($tax_type==1)
                {
                      $rtotaligst       +=  $rtotsales->igst_amount + $rtotsales->chargesgst;
                }
                else
                {
                      if($rtotsales['return_bill']['state_id'] == $company_state)
                      {
                        $rtotalcgst       +=  $rtotsales->cgst_amount + $halfchargesgst;
                        $rtotalsgst       +=  $rtotsales->sgst_amount + $halfchargesgst;
                      }
                      else
                      {
                        $rtotaligst       +=  $rtotsales->igst_amount + $rtotsales->chargesgst;
                      }
                }
                $rgrandtotal     +=  $rtotsales->total_amount;
            }

            $todaytaxable  = $taxabletariff - $rtaxabletariff;
            $todaycgst     = $totalcgst - $rtotalcgst;
            $todaysgst     = $totalsgst - $rtotalsgst;
            $todayigst     = $totaligst - $rtotaligst;
            $todaygrand    = $grandtotal - $rgrandtotal;


            return view('salesreport::view_productwise_bill_data',compact('sales_room_details','todaytaxable','todaycgst','todaysgst','todaygrand','todayigst','company_state','return_room_details','tax_type','taxname','get_store','companyname'))->render();
        }


    }

    public function exportroomwise_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);


               $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();
               $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title','bill_calculation','billtype')->where('company_id',Auth::user()->company_id)->get();
                $company_state   = $state_id[0]['state_id'];
                $tax_type        = $state_id[0]['tax_type'];
                $tax_title       = $state_id[0]['tax_title'];
                $taxname         = $tax_type==1?$tax_title:'IGST';
                $data            =      $request->all();
               
                $query = isset($data['query']) ? $data['query']  : '';
                $dynamic_search   = isset($data['dynamic_query']) ? $data['dynamic_query']  : ''; 
                
                if(isset($query) && $query != '' && isset($query['store_name']) && $query['store_name'] != '')
                {
                     $company_id      =   $query['store_name'];
                     $compname        =   company_profile::where('company_profile_id',$company_id)    
                                                ->first();                                           
                     $companyname    =   $compname['full_name']; 
                }
                else
                {
                    $company_id      =   Auth::user()->company_id;
                    $compname        =   company::where('company_id',$company_id)    
                                                ->first();                                           
                     $companyname    =   $compname['company_name']; 
                }

                

            $squery           =      sales_product_detail::select('*')->where('deleted_at','=',NULL)->where('company_id',$company_id);
            $rquery           =      return_product_detail::select('*')->where('deleted_at','=',NULL)->where('company_id',$company_id);

            

            if(isset($query) && $query != '' && $query['customerid'] != '')
            {

                if(strpos($query['customerid'], '_') !== false)
                {
                    $cusname   =   explode('_',$query['customerid']);
                    $cus_name   =  $cusname[0];
                    $cus_mobile  =  $cusname[1];
                }
                else
                {
                    $cus_name   =   $query['customerid'];
                    $cus_mobile =   $query['customerid'];
                }

                $totalcustomer = customer::select('customer_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('customer_name', 'LIKE', "%$cus_name%")
                 ->orwhere('customer_mobile', 'LIKE', "%$cus_mobile%")
                 ->get();

                 $totalsalesid = sales_bill::select('sales_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('customer_id',$totalcustomer)
                 ->get();

                  $totalreturnid = return_bill::select('return_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('customer_id',$totalcustomer)
                 ->get();

                 $squery->whereIn('sales_bill_id',$totalsalesid);
                 $rquery->whereIn('return_bill_id',$totalreturnid);
            }

            if(isset($query) && $query != '' && $query['barcode'] != '')
            {
                if(strpos($query['barcode'], '_') !== false)
                {
                    $prodbarcode   =   explode('_',$query['barcode']);
                    $prod_barcode      =  $prodbarcode[0];
                    $prod_name    =  $prodbarcode[1];
                }
                else
                {
                    $prod_barcode      =  $query['barcode'];
                    $prod_name         =  $query['barcode'];
                }
                 $product = product::select('product_id')
                ->where('product_system_barcode','LIKE', "%$prod_barcode%")
                ->orWhere('product_name','LIKE',"%$prod_name%")
                ->with('product_price_master')
                ->whereHas('product_price_master',function ($q) use($company_id){
                        $q->where('company_id',$company_id);
                 })
                ->get();

                $squery->whereIn('product_id',$product);
                $rquery->whereIn('product_id',$product);
            }
            if(isset($query) && $query != '' && $query['productcode'] != '')
            {
               
                 $product = product::select('product_id')
                ->where('product_code',$query['productcode'])
                ->with('product_price_master')
                ->whereHas('product_price_master',function ($q) use($company_id){
                        $q->where('company_id',$company_id);
                 })
                ->get();

                $squery->whereIn('product_id',$product);
                $rquery->whereIn('product_id',$product);
            }
            if(isset($query) && $query != '' && $query['billno'] != '')
            {


                  $tbill_no  =  sales_bill::select('sales_bill_id')->where('bill_no', 'like', '%'.$query['billno'].'%')->where('company_id',Auth::user()->company_id)->get();

                  $squery->whereIn('sales_bill_id', $tbill_no);

                  $treturn_id = return_bill::select('return_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('sales_bill_id',$tbill_no)
                 ->get();

                  $rquery->whereIn('return_bill_id', $treturn_id);
            }
            if(isset($query) && $query != '' && $query['reference_name'] != '')
            {

                $ref_name = $query['reference_name'];

                $totalrefid = reference::select('reference_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('reference_name', 'LIKE', "%$ref_name%")
                 ->get();

                 $totalsalesrid = sales_bill::select('sales_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('reference_id',$totalrefid)
                 ->get();

                  $totalreturnrid = return_bill::select('return_bill_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('reference_id',$totalrefid)
                 ->get();

                 $squery->whereIn('sales_bill_id',$totalsalesrid);
                 $rquery->whereIn('return_bill_id',$totalreturnrid);
            }
            if(isset($query) && $query != '' && $query['from_date'] != '' && $query['to_date'] != '')
            {

                 $rstart           =      date("Y-m-d",strtotime($query['from_date']));
                 $rend             =      date("Y-m-d",strtotime($query['to_date']));

                 $squery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
                 $rquery->with('return_bill')->whereHas('return_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
            }
            if($query['from_date'] == '' && $query['to_date'] == '' && $query['reference_name'] == '' && $query['billno'] == '' && $query['customerid'] == '' && $query['barcode'] == '')
            {
                 $rstart           =      date("Y-m-d");
                 $rend             =      date("Y-m-d");

                 $squery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
                 $rquery->with('return_bill')->whereHas('return_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
            }
          
            if(isset($dynamic_search) && $dynamic_search !='' &&  !empty($dynamic_search))
            {
                 foreach($dynamic_search AS $k=>$v)
                  {
                      if($v!='')
                      {
                           $squery =  $squery->with('product.product_features_relationship')
                          ->whereHas('product.product_features_relationship',function ($q) use($k,$v)
                          {
                          
                                $q->where(DB::raw($k),$v);
                            
                          });
                           $rquery =  $rquery->with('product.product_features_relationship')
                          ->whereHas('product.product_features_relationship',function ($q) use($k,$v)
                          {
                           
                                  $q->where(DB::raw($k),$v);
                               
                          });
                      }
                        
                  }
               

                 
            }

         

            $product_features =  ProductFeatures::getproduct_feature('');
            $sales = $squery->with([
                    'batchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
              ])
            ->with('sales_bill.reference')
            ->orderBy('sales_products_detail_id', 'DESC')
            ->get();

            // echo '<pre>';
            // print_r($sales);
            // exit;

            

            $returnbill = $rquery->with([
                    'rbatchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
             ])
            ->with('return_bill.reference')
            ->where('company_id',$company_id)
            ->orderBy('return_product_detail_id','DESC')
            ->get();

           




        $product_features =  ProductFeatures::getproduct_feature('');
        $this->page_url = ProductFeatures::get_current_page_url();
        $show_dynamic_feature = array();

            $overallsales   =  [];
             $header       = [];
             if(sizeof($get_store)!=0)
             {
              $header[]  =  'Location';
             }
             $header[]  =  'Bill No.';
             $header[]  =  'Bill Date';
             $header[]  =  'Customer Name';
             $header[]  =  'Product Name';
             $header[]  =  'Barcode';
             $header[]  =  'HSN';
             $header[]  =  'Product Code';
        $dynamic_header = '';


        if (isset($product_features) && $product_features != '' && !empty($product_features))
        {
            foreach ($product_features AS $feature_key => $feature_value)
            {
                if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
                {
                    $search =  $this->page_url;

                    if (strstr($feature_value['show_feature_url'],$search) )
                    {
                        $show_dynamic_feature[$feature_value['html_id']] = $feature_value['product_features_id'];
                        $dynamic_header .= $header[] = $feature_value['product_features_name'];
                    }
                }
            }
        }
        $dynamic_header;
             $header[]  =  'UQC';
             if($state_id[0]['billtype']==3)
             {
              $header[]  =  'Batch No.';
             }
             if($state_id[0]['bill_calculation']==1)
             {
             $header[]  =  'SellingPrice';
             }
             $header[]  =  'Qty';
             if($state_id[0]['bill_calculation']==1)
             {
             $header[]  =  'Discount Percent';
             $header[]  =  'Discount Amount';
             $header[]  =  'Overall Discount Amount';
             $header[]  =  'Taxable Amount';
             if($tax_type==1)
             {
                 $header[]  =  $taxname.' Percent';
                 $header[]  =  $taxname.' Amount';
             }
             else
             {
                 $header[]  =  'CGST Percent';
                 $header[]  =  'CGST Amount';
                 $header[]  =  'SGST Percent';
                 $header[]  =  'SGST Amount';
                 $header[]  =  'IGST Percent';
                 $header[]  =  'IGST Amount';

             }

             $header[]  =  'Total Amount';
           }
             $header[]  =  'Reference';

         
            $overallsales['sales']        =  $sales;
            $overallsales['returnbill']   =  $returnbill;

          return Excel::download(new productwiseBills_export($overallsales, $header,$show_dynamic_feature,$companyname), 'Productwise-Export.xlsx');


    }
    public function product_agingreport()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

       $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

        $compname       =   company::where('company_id',Auth::user()->company_id)    
                                      ->first();                                           
        $companyname    =   $compname['company_name'];         

        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

        $date            =    date("Y-m-d");

        $age_range       =   productage_range::where('company_id',Auth::user()->company_id)
                                              ->where('deleted_at',NULL)
                                              //->whereIn('productage_range_id',array(1,2))
                                              ->get();


        // $from_date        =         '2019-07-01';
        // $to_date          =         '2019-09-30';                                    

////////////////////////////Order By highest sold products///////////////////////////////////////////////////////////////////////////////

      // $productid_order     =        sales_product_detail::where('company_id',Auth::user()->company_id)
      //                               ->where('product_type',1)
      //                               ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($from_date,$to_date){
      //                                 $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
      //                                   $q->where('company_id',Auth::user()->company_id);
      //                                   $q->orderBy('sales_bill_id','ASC');
      //                                   $q->where('sales_type',1);
      //                                 })
      //                               ->select('product_id')                                    
      //                               ->groupBy('product_id')
      //                               ->orderBy(DB::raw('sum(qty)'),'DESC')
      //                               ->get();

      //                    $orderproduct_id   =      '';        
      //                   foreach($productid_order as $okey =>$oval)
      //                   {
      //                      $orderproduct_id   .=  $oval['product_id'].',';
      //                   }     
                           
      //                    $orderproduct_id   =  substr($orderproduct_id, 0,-1);
      //                    $exp      =  explode(',',$orderproduct_id);

                         
                        
                            // exit;

             $products        =   product::select('product_id','product_name','product_system_barcode','supplier_barcode','hsn_sac_code','product_code')
                                      ->where('company_id',Auth::user()->company_id)  
                                      ->whereIn('item_type',array(1,3)) 
                                      ->orderBy('product_id','DESC')
                                      //->where('product_id',3)
                                      // ->whereIn('product_id',$exp)
                                      // ->orderByRaw("field(product_id,{$orderproduct_id})", $exp)
                                      ->paginate(10);

                              // echo '<pre>';
                              // print_r($products);
                              // exit;



      $searchdate      =   date("Y-m-d");                               
      foreach($products as $pp=>$pval)
      {
          $pproduct_id    =   $pval['product_id'];

          foreach ($age_range AS $aa => $rr)
            {
                $totalsoldqty   =  0;
                $totalinward    =  0;
                $totalissueqty   =  0;
                $totalconsignqty   =  0;
                $totaldamageqty = 0;
                $totaldebitqty = 0;
                $totalrestockqty = 0;
               
                $from_date       =   date('Y-m-d', strtotime('-'.round($rr['range_to']).' day', strtotime($searchdate)));
                $to_date         =   date('Y-m-d', strtotime('-'.round($rr['range_from']).' day', strtotime($searchdate)));

                 $inwardqty       =    inward_product_detail::where('company_id',Auth::user()->company_id)
                                            ->where('product_id',$pval['product_id'])
                                            ->with('inward_stock')->whereHas('inward_stock',function ($q) use ($from_date,$to_date){
                                            $q->whereRaw("STR_TO_DATE(inward_stocks.inward_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                                            $q->where('company_id',Auth::user()->company_id);
                                            $q->orderBy('inward_stock_id','ASC');
                                            })
                                            // ->select(DB::raw('SUM(product_qty+free_qty) as totalinward'))
                                            ->select('inward_product_detail_id','product_qty','free_qty','inward_stock_id')
                                            ->orderBy('inward_product_detail_id','ASC')
                                            ->where('deleted_at',NULL)
                                            ->get();

                  foreach($inwardqty as $ikey=>$ivalue)
                  {
                       
                        $searchinwardids    =   $ivalue['inward_product_detail_id'].',';
                        $iinward_stock_id   =   $ivalue['inward_stock_id'];
                        //echo $ivalue['inward_product_detail_id'].'<br>';

 //////////////////////////////To Deduct Sold Products and products issue for franchise bills from inward as per the interval////////
                    $soldqty   =    sales_product_detail::where('company_id',Auth::user()->company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->where('product_type',1)
                                    ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($from_date,$to_date,$searchdate){
                                      $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',Auth::user()->company_id);
                                        $q->orderBy('sales_bill_id','ASC');
                                        //$q->where('sales_type',1);
                                      })
                                    ->where('inwardids','LIKE','%'.$searchinwardids.'%')
                                    ->select('sales_products_detail_id','inwardids','inwardqtys')
                                    ->orderBy('sales_products_detail_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($soldqty as $skey=>$svalue)
                            {
                                 $inwardids  = explode(',' ,substr($svalue['inwardids'],0,-1));
                                 $inwardqtys = explode(',' ,substr($svalue['inwardqtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($inwardids as $inidkey=>$inids)
                                  {
                                    
                                      if($inids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalsoldqty  +=  $inwardqtys[$inidkey];
                                      }
                                  }
                            } 

 ///////////////////////End code for Deduct Sold Products from inward as per the interval///////////////////////////////////////////

 ////////////////To Deduct Stock issue for Stores from inward as per the interval/////////////////////////////////////////
              $storeissueqty   =    stock_transfer_detail::where('company_id',Auth::user()->company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($from_date,$to_date,$searchdate){
                                      $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',Auth::user()->company_id);
                                        $q->orderBy('stock_transfer_id','ASC');
                                        $q->whereNull('sales_bill_id');
                                      })
                                    ->where('inward_product_detail_id','LIKE','%'.$searchinwardids.'%')
                                    ->select('stock_transfers_detail_id','inward_product_detail_id','inward_product_qtys')
                                    ->orderBy('stock_transfers_detail_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($storeissueqty as $skey=>$svalue)
                            {
                                 $sinwardids  = explode(',' ,substr($svalue['inward_product_detail_id'],0,-1));
                                 $sinwardqtys = explode(',' ,substr($svalue['inward_product_qtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($sinwardids as $sinidkey=>$sinids)
                                  {
                                    
                                      if($sinids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalissueqty  +=  $sinwardqtys[$sinidkey];
                                      }
                                  }
                            } 

 ///////////////////////End code for Deduct Sold Products from inward as per the interval///////////////////////////////////////////////

 ////////////////To Deduct Consignment Bills from inward as per the interval////////////////////////////////////////////////////////////
              $consignbillqty   =    consign_products_detail::where('company_id',Auth::user()->company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('consign_bill')->whereHas('consign_bill',function ($q) use ($from_date,$to_date,$searchdate){
                                      $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',Auth::user()->company_id);
                                        $q->orderBy('consign_bill_id','ASC');
                                      })
                                    ->where('inwardids','LIKE','%'.$searchinwardids.'%')
                                    ->select('consign_products_detail_id','inwardids','inwardqtys')
                                    ->orderBy('consign_products_detail_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($consignbillqty as $ckey=>$cvalue)
                            {
                                 $cinwardids  = explode(',' ,substr($cvalue['inwardids'],0,-1));
                                 $cinwardqtys = explode(',' ,substr($cvalue['inwardqtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($cinwardids as $cinidkey=>$cinids)
                                  {
                                    
                                      if($cinids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalconsignqty  +=  $cinwardqtys[$cinidkey];
                                      }
                                  }
                            } 

///////////////////////End code for Deduct Consign bills qty from inward as per the interval///////////////////////////////////////////////

////////////////To Deduct Damage Products from inward as per the interval/////////////////////////////////////////////////////////////////
              $damagebillqty   =    damage_product_detail::where('company_id',Auth::user()->company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('damage_product')->whereHas('damage_product',function ($q) use ($from_date,$to_date,$searchdate){
                                      $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',Auth::user()->company_id);
                                        $q->whereIn('damage_type_id',array(1,2));
                                        $q->orderBy('damage_product_id','ASC');
                                      })
                                    ->where('inward_product_detail_id','=',$ivalue['inward_product_detail_id'])
                                    ->select(DB::raw('sum(product_damage_qty) as totaldamageqty'),'product_id')
                                    ->where('deleted_at',NULL)
                                    ->get();
                 

                  if(sizeof($damagebillqty)!=0)
                  {
                    $totaldamageqty  +=   $damagebillqty[0]['totaldamageqty'];
                  }

///////////////////////End code for Deduct Damage bills qty from inward as per the interval///////////////////////////////////////////////

////////////////To Deduct Debit Products from inward as per the interval/////////////////////////////////////////////////////////////////
              $debitbillqty   =    debit_product_detail::where('company_id',Auth::user()->company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('debit_note')->whereHas('debit_note',function ($q) use ($from_date,$to_date,$searchdate,$pproduct_id,$iinward_stock_id){
                                      $q->whereRaw("STR_TO_DATE(debit_notes.debit_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',Auth::user()->company_id);
                                        $q->where('inward_stock_id',$iinward_stock_id);
                                        $q->with('inward_product_detail')->whereHas('inward_product_detail',function ($qq) use ($pproduct_id){
                                          $qq->select('inward_product_detail_id');
                                          $qq->where('inward_product_details.product_id',$pproduct_id);
                                          $qq->where('company_id',Auth::user()->company_id);
                                      });
                                      })
                                    ->select(DB::raw('sum(return_qty) as totaldebitqty'),'product_id')
                                    ->where('deleted_at',NULL)
                                    ->get();
                 
                

                  if(sizeof($debitbillqty)!=0)
                  {
                     $totaldebitqty  +=   $debitbillqty[0]['totaldebitqty'];

                  }
                 

///////////////////////End code for Deduct Debit Products from inward as per the interval///////////////////////////////////////////////

////////////////To add Restock products in inward as per the interval///////////////////////////////////////////////////////////////////
              $restockbillqty   =    returnbill_product::where('company_id',Auth::user()->company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->whereRaw("STR_TO_DATE(returnbill_products.return_date,'%d-%m-%Y') between '$from_date' and '$searchdate'")
                                    ->where('rinwardids','LIKE','%'.$searchinwardids.'%')
                                    ->select('returnbill_product_id','rinwardids','rinwardqtys')
                                    ->orderBy('returnbill_product_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($restockbillqty as $rrkey=>$rrvalue)
                            {
                                 $rrinwardids  = explode(',' ,substr($rrvalue['rinwardids'],0,-1));
                                 $rrinwardqtys = explode(',' ,substr($rrvalue['rinwardqtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($rrinwardids as $rrinidkey=>$rrinids)
                                  {
                                    
                                      if($rrinids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalrestockqty  +=  $rrinwardqtys[$rrinidkey];
                                      }
                                  }
                            } 

///////////////////////End code for adding restock qty in inward as per the interval//////////////////////////////////////////////////////

 //////////////////////////To calculate Total Inward qty as per the interval/////////////////////////////////////////////// 
                     $totalinward   +=   $ivalue['product_qty'] + $ivalue['free_qty'];   


                  }                          

          

                $instock   =  $totalinward - $totalsoldqty - $totalissueqty - $totalconsignqty - $totaldamageqty - $totaldebitqty + $totalrestockqty;
                // echo '<pre>';
                // echo $totalinward.' - '.$totalsoldqty.' - '.$totalissueqty.' - '.$totalconsignqty.' - '.$totaldamageqty.' - '.$totaldebitqty.' - '.$totalrestockqty;
                 $html_id = round($rr['range_from']).' - '.round($rr['range_to']);
                 $pval[$html_id] = $totalinward.' - '.$instock;
            }
              
        
       }
       
      // exit;

         return view('salesreport::product_agingreport',compact('age_range','products','get_store','companyname'));

    }


    public function datewise_product_agereport(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

       $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

        $data            =      $request->all();
        $query = isset($data['query']) ? $data['query']  : '';

        if(isset($query) && $query != '' && isset($query['store_name']) && $query['store_name'] != '')
            {
                 $company_id      =   $query['store_name'];
                 $compname        =   company_profile::where('company_profile_id',$company_id)    
                                            ->first();                                           
                 $companyname    =   $compname['full_name']; 
            }
            else
            {
                $company_id      =   Auth::user()->company_id;
                $compname        =   company::where('company_id',$company_id)    
                                            ->first();                                           
                 $companyname    =   $compname['company_name']; 
            }
            

        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

        $date            =    date("Y-m-d");

        $age_range       =   productage_range::where('company_id',Auth::user()->company_id)
                                              ->where('deleted_at',NULL)
                                              //->whereIn('productage_range_id',array(1,2))
                                              ->get();


        // $from_date        =         '2019-07-01';
        // $to_date          =         '2019-09-30';                                    

////////////////////////////Order By highest sold products///////////////////////////////////////////////////////////////////////////////

      // $productid_order     =        sales_product_detail::where('company_id',Auth::user()->company_id)
      //                               ->where('product_type',1)
      //                               ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($from_date,$to_date){
      //                                 $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
      //                                   $q->where('company_id',Auth::user()->company_id);
      //                                   $q->orderBy('sales_bill_id','ASC');
      //                                   $q->where('sales_type',1);
      //                                 })
      //                               ->select('product_id')                                    
      //                               ->groupBy('product_id')
      //                               ->orderBy(DB::raw('sum(qty)'),'DESC')
      //                               ->get();

      //                    $orderproduct_id   =      '';        
      //                   foreach($productid_order as $okey =>$oval)
      //                   {
      //                      $orderproduct_id   .=  $oval['product_id'].',';
      //                   }     
                           
      //                    $orderproduct_id   =  substr($orderproduct_id, 0,-1);
      //                    $exp      =  explode(',',$orderproduct_id);

                         
                        
                            // exit;

                       $pquery        =   product::select('product_id','product_name','product_system_barcode','supplier_barcode','hsn_sac_code','product_code')->with('product_price_master')
                                        ->whereHas('product_price_master',function ($q) use($company_id){
                                                $q->where('company_id',$company_id);
                                         })
                                          ->whereIn('item_type',array(1,3)) 
                                          ->orderBy('product_id','DESC');

                      if(isset($query) && $query != '' && $query['barcode'] != '')
                      {
                          if(strpos($query['barcode'], '_') !== false)
                          {
                              $prodbarcode   =   explode('_',$query['barcode']);
                              $prod_barcode      =  $prodbarcode[0];
                              $prod_name    =  $prodbarcode[1];
                          }
                          else
                          {
                              $prod_barcode      =  $query['barcode'];
                              $prod_name         =  $query['barcode'];
                          }
                           $product = product::select('product_id')
                          ->where('product_system_barcode','LIKE', "%$prod_barcode%")
                          ->orWhere('product_name','LIKE',"%$prod_name%")
                          ->with('product_price_master')
                          ->whereHas('product_price_master',function ($q) use($company_id){
                                  $q->where('company_id',$company_id);
                           })
                          ->get();

                          $pquery->whereIn('product_id',$product);
                          
                      }

                      $products  =  $pquery
                                    //->where('product_id',1)
                                    ->paginate(10);


                                      //->where('product_id',3)
                                      // ->whereIn('product_id',$exp)
                                      // ->orderByRaw("field(product_id,{$orderproduct_id})", $exp)
                                     


      $searchdate      =   date("Y-m-d",strtotime($query['from_date']));                             
      foreach($products as $pp=>$pval)
      {
          $pproduct_id    =   $pval['product_id'];

          foreach ($age_range AS $aa => $rr)
            {
                $totalsoldqty   =  0;
                $totalinward    =  0;
                $totalissueqty   =  0;
                $totalconsignqty   =  0;
                $totaldamageqty = 0;
                $totaldebitqty = 0;
                $totalrestockqty = 0;
               
               //echo $searchdate;
                $from_date       =   date('Y-m-d', strtotime('-'.round($rr['range_to']).' day', strtotime($searchdate)));
                $to_date         =   date('Y-m-d', strtotime('-'.round($rr['range_from']).' day', strtotime($searchdate)));

                 $inwardqty       =    inward_product_detail::where('company_id',$company_id)
                                            ->where('product_id',$pval['product_id'])
                                            ->with('inward_stock')->whereHas('inward_stock',function ($q) use ($from_date,$to_date,$company_id){
                                            $q->whereRaw("STR_TO_DATE(inward_stocks.inward_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                                            $q->where('company_id',$company_id);
                                            $q->orderBy('inward_stock_id','ASC');
                                            })
                                            // ->select(DB::raw('SUM(product_qty+free_qty) as totalinward'))
                                            ->select('inward_product_detail_id','product_qty','free_qty','inward_stock_id')
                                            ->orderBy('inward_product_detail_id','ASC')
                                            ->where('deleted_at',NULL)
                                            ->get();

                  foreach($inwardqty as $ikey=>$ivalue)
                  {
                       
                        $searchinwardids    =   $ivalue['inward_product_detail_id'].',';
                        $iinward_stock_id   =   $ivalue['inward_stock_id'];
                        //echo $ivalue['inward_product_detail_id'].'<br>';

 //////////////////////////////To Deduct Sold Products and products issue for franchise bills from inward as per the interval////////
                    $soldqty   =    sales_product_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->where('product_type',1)
                                    ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($from_date,$to_date,$searchdate,$company_id){
                                      $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->orderBy('sales_bill_id','ASC');
                                        //$q->where('sales_type',1);
                                      })
                                    ->where('inwardids','LIKE','%'.$searchinwardids.'%')
                                    ->select('sales_products_detail_id','inwardids','inwardqtys')
                                    ->orderBy('sales_products_detail_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($soldqty as $skey=>$svalue)
                            {
                                 $inwardids  = explode(',' ,substr($svalue['inwardids'],0,-1));
                                 $inwardqtys = explode(',' ,substr($svalue['inwardqtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($inwardids as $inidkey=>$inids)
                                  {
                                    
                                      if($inids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalsoldqty  +=  $inwardqtys[$inidkey];
                                      }
                                  }
                            } 

 ///////////////////////End code for Deduct Sold Products from inward as per the interval///////////////////////////////////////////

 ////////////////To Deduct Stock issue for Stores from inward as per the interval/////////////////////////////////////////
              $storeissueqty   =    stock_transfer_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($from_date,$to_date,$searchdate,$company_id){
                                      $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->orderBy('stock_transfer_id','ASC');
                                        $q->whereNull('sales_bill_id');
                                      })
                                    ->where('inward_product_detail_id','LIKE','%'.$searchinwardids.'%')
                                    ->select('stock_transfers_detail_id','inward_product_detail_id','inward_product_qtys')
                                    ->orderBy('stock_transfers_detail_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($storeissueqty as $skey=>$svalue)
                            {
                                 $sinwardids  = explode(',' ,substr($svalue['inward_product_detail_id'],0,-1));
                                 $sinwardqtys = explode(',' ,substr($svalue['inward_product_qtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($sinwardids as $sinidkey=>$sinids)
                                  {
                                    
                                      if($sinids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalissueqty  +=  $sinwardqtys[$sinidkey];
                                      }
                                  }
                            } 

 ///////////////////////End code for Deduct Sold Products from inward as per the interval///////////////////////////////////////////////

 ////////////////To Deduct Consignment Bills from inward as per the interval////////////////////////////////////////////////////////////
              $consignbillqty   =    consign_products_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('consign_bill')->whereHas('consign_bill',function ($q) use ($from_date,$to_date,$searchdate,$company_id){
                                      $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->orderBy('consign_bill_id','ASC');
                                      })
                                    ->where('inwardids','LIKE','%'.$searchinwardids.'%')
                                    ->select('consign_products_detail_id','inwardids','inwardqtys')
                                    ->orderBy('consign_products_detail_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($consignbillqty as $ckey=>$cvalue)
                            {
                                 $cinwardids  = explode(',' ,substr($cvalue['inwardids'],0,-1));
                                 $cinwardqtys = explode(',' ,substr($cvalue['inwardqtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($cinwardids as $cinidkey=>$cinids)
                                  {
                                    
                                      if($cinids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalconsignqty  +=  $cinwardqtys[$cinidkey];
                                      }
                                  }
                            } 

///////////////////////End code for Deduct Consign bills qty from inward as per the interval///////////////////////////////////////////////

////////////////To Deduct Damage Products from inward as per the interval/////////////////////////////////////////////////////////////////
              $damagebillqty   =    damage_product_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('damage_product')->whereHas('damage_product',function ($q) use ($from_date,$to_date,$searchdate,$company_id){
                                      $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->whereIn('damage_type_id',array(1,2));
                                        $q->orderBy('damage_product_id','ASC');
                                      })
                                    ->where('inward_product_detail_id','=',$ivalue['inward_product_detail_id'])
                                    ->select(DB::raw('sum(product_damage_qty) as totaldamageqty'),'product_id')
                                    ->where('deleted_at',NULL)
                                    ->get();
                 

                  if(sizeof($damagebillqty)!=0)
                  {
                    $totaldamageqty  +=   $damagebillqty[0]['totaldamageqty'];
                  }

///////////////////////End code for Deduct Damage bills qty from inward as per the interval///////////////////////////////////////////////

////////////////To Deduct Debit Products from inward as per the interval/////////////////////////////////////////////////////////////////
              $debitbillqty   =    debit_product_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('debit_note')->whereHas('debit_note',function ($q) use ($from_date,$to_date,$searchdate,$pproduct_id,$iinward_stock_id,$company_id){
                                      $q->whereRaw("STR_TO_DATE(debit_notes.debit_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->where('inward_stock_id',$iinward_stock_id);
                                        $q->with('inward_product_detail')->whereHas('inward_product_detail',function ($qq) use ($pproduct_id,$company_id){
                                          $qq->select('inward_product_detail_id');
                                          $qq->where('inward_product_details.product_id',$pproduct_id);
                                          $qq->where('company_id',$company_id);
                                      });
                                      })
                                    ->select(DB::raw('sum(return_qty) as totaldebitqty'),'product_id')
                                    ->where('deleted_at',NULL)
                                    ->get();
                 
                

                  if(sizeof($debitbillqty)!=0)
                  {
                     $totaldebitqty  +=   $debitbillqty[0]['totaldebitqty'];

                  }
                 

///////////////////////End code for Deduct Debit Products from inward as per the interval///////////////////////////////////////////////

////////////////To add Restock products in inward as per the interval///////////////////////////////////////////////////////////////////
              $restockbillqty   =    returnbill_product::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->whereRaw("STR_TO_DATE(returnbill_products.return_date,'%d-%m-%Y') between '$from_date' and '$searchdate'")
                                    ->where('rinwardids','LIKE','%'.$searchinwardids.'%')
                                    ->select('returnbill_product_id','rinwardids','rinwardqtys')
                                    ->orderBy('returnbill_product_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($restockbillqty as $rrkey=>$rrvalue)
                            {
                                 $rrinwardids  = explode(',' ,substr($rrvalue['rinwardids'],0,-1));
                                 $rrinwardqtys = explode(',' ,substr($rrvalue['rinwardqtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($rrinwardids as $rrinidkey=>$rrinids)
                                  {
                                    
                                      if($rrinids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalrestockqty  +=  $rrinwardqtys[$rrinidkey];
                                      }
                                  }
                            } 

///////////////////////End code for adding restock qty in inward as per the interval//////////////////////////////////////////////////////

 //////////////////////////To calculate Total Inward qty as per the interval/////////////////////////////////////////////// 
                     $totalinward   +=   $ivalue['product_qty'] + $ivalue['free_qty'];   


                  }                          

          

                $instock   =  $totalinward - $totalsoldqty - $totalissueqty - $totalconsignqty - $totaldamageqty - $totaldebitqty + $totalrestockqty;
                // echo '<pre>';
                // echo $totalinward.' - '.$totalsoldqty.' - '.$totalissueqty.' - '.$totalconsignqty.' - '.$totaldamageqty.' - '.$totaldebitqty.' - '.$totalrestockqty;
                 $html_id = round($rr['range_from']).' - '.round($rr['range_to']);
                 $pval[$html_id] = $totalinward.' - '.$instock;
            }
              
        
       }
       
       //exit;

         return view('salesreport::product_agingreport_data',compact('age_range','products','get_store','companyname'));

    }

    public function exportagereport_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

         $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

        $data            =      $request->all();
        $query = isset($data['query']) ? $data['query']  : '';


          if(isset($query) && $query != '' && isset($query['store_name']) && $query['store_name'] != '')
          {
               $company_id      =   $query['store_name'];
               $compname        =   company_profile::where('company_profile_id',$company_id)    
                                          ->first();                                           
               $companyname    =   $compname['full_name']; 
          }
          else
          {
              $company_id      =   Auth::user()->company_id;
              $compname        =   company::where('company_id',$company_id)    
                                          ->first();                                           
               $companyname    =   $compname['company_name']; 
          }
            

            

        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

        $date            =    date("Y-m-d");

        $age_range       =   productage_range::where('company_id',Auth::user()->company_id)
                                              ->where('deleted_at',NULL)
                                              //->whereIn('productage_range_id',array(1,2))
                                              ->get();




                       $pquery        =   product::select('product_id','product_name','product_system_barcode','supplier_barcode','hsn_sac_code','product_code')->with('product_price_master')
                                        ->whereHas('product_price_master',function ($q) use($company_id){
                                                $q->where('company_id',$company_id);
                                         })
                                         ->whereIn('item_type',array(1,3)) 
                                         ->orderBy('product_id','DESC');

                      if(isset($query) && $query != '' && $query['barcode'] != '')
                      {
                          if(strpos($query['barcode'], '_') !== false)
                          {
                              $prodbarcode   =   explode('_',$query['barcode']);
                              $prod_barcode      =  $prodbarcode[0];
                              $prod_name    =  $prodbarcode[1];
                          }
                          else
                          {
                              $prod_barcode      =  $query['barcode'];
                              $prod_name         =  $query['barcode'];
                          }
                           $product = product::select('product_id')
                          ->where('product_system_barcode','LIKE', "%$prod_barcode%")
                          ->orWhere('product_name','LIKE',"%$prod_name%")
                          ->with('product_price_master')
                          ->whereHas('product_price_master',function ($q) use($company_id){
                                  $q->where('company_id',$company_id);
                           })
                          ->get();

                          $pquery->whereIn('product_id',$product);
                          
                      }

                      $products  =  $pquery
                                    //->where('product_id',1)
                                    ->get();

                                     


      $searchdate      =   date("Y-m-d",strtotime($query['from_date']));                             
      foreach($products as $pp=>$pval)
      {
          $pproduct_id    =   $pval['product_id'];

          foreach ($age_range AS $aa => $rr)
            {
                $totalsoldqty   =  0;
                $totalinward    =  0;
                $totalissueqty   =  0;
                $totalconsignqty   =  0;
                $totaldamageqty = 0;
                $totaldebitqty = 0;
                $totalrestockqty = 0;
               
               //echo $searchdate;
                $from_date       =   date('Y-m-d', strtotime('-'.round($rr['range_to']).' day', strtotime($searchdate)));
                $to_date         =   date('Y-m-d', strtotime('-'.round($rr['range_from']).' day', strtotime($searchdate)));

                 $inwardqty       =    inward_product_detail::where('company_id',$company_id)
                                            ->where('product_id',$pval['product_id'])
                                            ->with('inward_stock')->whereHas('inward_stock',function ($q) use ($from_date,$to_date,$company_id){
                                            $q->whereRaw("STR_TO_DATE(inward_stocks.inward_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                                            $q->where('company_id',$company_id);
                                            $q->orderBy('inward_stock_id','ASC');
                                            })
                                            // ->select(DB::raw('SUM(product_qty+free_qty) as totalinward'))
                                            ->select('inward_product_detail_id','product_qty','free_qty','inward_stock_id')
                                            ->orderBy('inward_product_detail_id','ASC')
                                            ->where('deleted_at',NULL)
                                            ->get();

                  foreach($inwardqty as $ikey=>$ivalue)
                  {
                       
                        $searchinwardids    =   $ivalue['inward_product_detail_id'].',';
                        $iinward_stock_id   =   $ivalue['inward_stock_id'];
                        //echo $ivalue['inward_product_detail_id'].'<br>';

 //////////////////////////////To Deduct Sold Products and products issue for franchise bills from inward as per the interval////////
                    $soldqty   =    sales_product_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->where('product_type',1)
                                    ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($from_date,$to_date,$searchdate,$company_id){
                                      $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->orderBy('sales_bill_id','ASC');
                                        //$q->where('sales_type',1);
                                      })
                                    ->where('inwardids','LIKE','%'.$searchinwardids.'%')
                                    ->select('sales_products_detail_id','inwardids','inwardqtys')
                                    ->orderBy('sales_products_detail_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($soldqty as $skey=>$svalue)
                            {
                                 $inwardids  = explode(',' ,substr($svalue['inwardids'],0,-1));
                                 $inwardqtys = explode(',' ,substr($svalue['inwardqtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($inwardids as $inidkey=>$inids)
                                  {
                                    
                                      if($inids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalsoldqty  +=  $inwardqtys[$inidkey];
                                      }
                                  }
                            } 

 ///////////////////////End code for Deduct Sold Products from inward as per the interval///////////////////////////////////////////

 ////////////////To Deduct Stock issue for Stores from inward as per the interval/////////////////////////////////////////
              $storeissueqty   =    stock_transfer_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($from_date,$to_date,$searchdate,$company_id){
                                      $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->orderBy('stock_transfer_id','ASC');
                                        $q->whereNull('sales_bill_id');
                                      })
                                    ->where('inward_product_detail_id','LIKE','%'.$searchinwardids.'%')
                                    ->select('stock_transfers_detail_id','inward_product_detail_id','inward_product_qtys')
                                    ->orderBy('stock_transfers_detail_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($storeissueqty as $skey=>$svalue)
                            {
                                 $sinwardids  = explode(',' ,substr($svalue['inward_product_detail_id'],0,-1));
                                 $sinwardqtys = explode(',' ,substr($svalue['inward_product_qtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($sinwardids as $sinidkey=>$sinids)
                                  {
                                    
                                      if($sinids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalissueqty  +=  $sinwardqtys[$sinidkey];
                                      }
                                  }
                            } 

 ///////////////////////End code for Deduct Sold Products from inward as per the interval///////////////////////////////////////////////

 ////////////////To Deduct Consignment Bills from inward as per the interval////////////////////////////////////////////////////////////
              $consignbillqty   =    consign_products_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('consign_bill')->whereHas('consign_bill',function ($q) use ($from_date,$to_date,$searchdate,$company_id){
                                      $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->orderBy('consign_bill_id','ASC');
                                      })
                                    ->where('inwardids','LIKE','%'.$searchinwardids.'%')
                                    ->select('consign_products_detail_id','inwardids','inwardqtys')
                                    ->orderBy('consign_products_detail_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($consignbillqty as $ckey=>$cvalue)
                            {
                                 $cinwardids  = explode(',' ,substr($cvalue['inwardids'],0,-1));
                                 $cinwardqtys = explode(',' ,substr($cvalue['inwardqtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($cinwardids as $cinidkey=>$cinids)
                                  {
                                    
                                      if($cinids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalconsignqty  +=  $cinwardqtys[$cinidkey];
                                      }
                                  }
                            } 

///////////////////////End code for Deduct Consign bills qty from inward as per the interval///////////////////////////////////////////////

////////////////To Deduct Damage Products from inward as per the interval/////////////////////////////////////////////////////////////////
              $damagebillqty   =    damage_product_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('damage_product')->whereHas('damage_product',function ($q) use ($from_date,$to_date,$searchdate,$company_id){
                                      $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->whereIn('damage_type_id',array(1,2));
                                        $q->orderBy('damage_product_id','ASC');
                                      })
                                    ->where('inward_product_detail_id','=',$ivalue['inward_product_detail_id'])
                                    ->select(DB::raw('sum(product_damage_qty) as totaldamageqty'),'product_id')
                                    ->where('deleted_at',NULL)
                                    ->get();
                 

                  if(sizeof($damagebillqty)!=0)
                  {
                    $totaldamageqty  +=   $damagebillqty[0]['totaldamageqty'];
                  }

///////////////////////End code for Deduct Damage bills qty from inward as per the interval///////////////////////////////////////////////

////////////////To Deduct Debit Products from inward as per the interval/////////////////////////////////////////////////////////////////
              $debitbillqty   =    debit_product_detail::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->with('debit_note')->whereHas('debit_note',function ($q) use ($from_date,$to_date,$searchdate,$pproduct_id,$iinward_stock_id,$company_id){
                                      $q->whereRaw("STR_TO_DATE(debit_notes.debit_date,'%d-%m-%Y') between '$from_date' and '$searchdate'");
                                        $q->where('company_id',$company_id);
                                        $q->where('inward_stock_id',$iinward_stock_id);
                                        $q->with('inward_product_detail')->whereHas('inward_product_detail',function ($qq) use ($pproduct_id,$company_id){
                                          $qq->select('inward_product_detail_id');
                                          $qq->where('inward_product_details.product_id',$pproduct_id);
                                          $qq->where('company_id',$company_id);
                                      });
                                      })
                                    ->select(DB::raw('sum(return_qty) as totaldebitqty'),'product_id')
                                    ->where('deleted_at',NULL)
                                    ->get();
                 
                

                  if(sizeof($debitbillqty)!=0)
                  {
                     $totaldebitqty  +=   $debitbillqty[0]['totaldebitqty'];

                  }
                 

///////////////////////End code for Deduct Debit Products from inward as per the interval///////////////////////////////////////////////

////////////////To add Restock products in inward as per the interval///////////////////////////////////////////////////////////////////
              $restockbillqty   =    returnbill_product::where('company_id',$company_id)
                                    ->where('product_id',$pval['product_id'])
                                    ->whereRaw("STR_TO_DATE(returnbill_products.return_date,'%d-%m-%Y') between '$from_date' and '$searchdate'")
                                    ->where('rinwardids','LIKE','%'.$searchinwardids.'%')
                                    ->select('returnbill_product_id','rinwardids','rinwardqtys')
                                    ->orderBy('returnbill_product_id','ASC')
                                    ->where('deleted_at',NULL)
                                    ->get();



                            foreach($restockbillqty as $rrkey=>$rrvalue)
                            {
                                 $rrinwardids  = explode(',' ,substr($rrvalue['rinwardids'],0,-1));
                                 $rrinwardqtys = explode(',' ,substr($rrvalue['rinwardqtys'],0,-1));

                                  //echo $svalue['sales_products_detail_id'].'<br>';

                                  foreach($rrinwardids as $rrinidkey=>$rrinids)
                                  {
                                    
                                      if($rrinids == $ivalue['inward_product_detail_id'])
                                      {
                                       // echo $inids.'<br>';
                                          $totalrestockqty  +=  $rrinwardqtys[$rrinidkey];
                                      }
                                  }
                            } 

///////////////////////End code for adding restock qty in inward as per the interval//////////////////////////////////////////////////////

 //////////////////////////To calculate Total Inward qty as per the interval/////////////////////////////////////////////// 
                     $totalinward   +=   $ivalue['product_qty'] + $ivalue['free_qty'];   


                  }                          

          

                $instock   =  $totalinward - $totalsoldqty - $totalissueqty - $totalconsignqty - $totaldamageqty - $totaldebitqty + $totalrestockqty;
                // echo '<pre>';
                // echo $totalinward.' - '.$totalsoldqty.' - '.$totalissueqty.' - '.$totalconsignqty.' - '.$totaldamageqty.' - '.$totaldebitqty.' - '.$totalrestockqty;
                 $html_id = round($rr['range_from']).' - '.round($rr['range_to']);
                 $pval[$html_id] = $totalinward.' - '.$instock;
            }
              
        
       }
            $overallsales   =  [];
            $header         = [];
            if(sizeof($get_store)!=0)
            {
              $header[]       =   'Location';
            }
            $header[]       =   'Product Name';
            $header[]       =   'Barcode';
            $header[]       =   'HSN Code';
            foreach($age_range AS $rangekey=>$range_value)
            {
                
                $header[]      =    round($range_value['range_from']).'-'.round($range_value['range_to']) .' (Inward)';
                $header[]      =    round($range_value['range_from']).'-'.round($range_value['range_to']) .' (InStock)';
            }
            $header[]            =  'Total Inward';
            $header[]            =  'InStock';

            $overallsales['products']        =  $products;
            // echo '<pre>';
            // print_r($products);
            // exit;
            
       return Excel::download(new productagereport_export($overallsales, $header,$companyname), 'Product_agereport.xlsx');
    }



}
