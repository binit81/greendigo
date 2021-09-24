<?php

namespace Retailcore\SalesReport\Http\Controllers;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\SalesReport\Models\profitloss_export;
use Retailcore\SalesReport\Models\supplier_salereport_export;
use Illuminate\Http\Request;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Sales\Models\payment_method;
use App\state;
use App\country;
use Auth;
use Retailcore\Customer\Models\customer\customer;
use Retailcore\Products\Models\product\product;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\company;
use Retailcore\Customer\Models\customer\customer_address_detail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
class ProfitLossReportController extends Controller
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
        $state_id        =  company_profile::select('state_id')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];

        $date        =    date("Y-m-d");

        $productdetails =  sales_product_detail::select('*')
                  ->where('company_id',Auth::user()->company_id)
                  ->where('deleted_by','=',NULL)
                  ->where('qty','!=',0)
                  ->with([
                    'batchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                  ])
                  ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($date){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'");
                    })
                  ->orderBy('sales_products_detail_id','DESC')
                  ->paginate(10);

          $rproductdetails =  return_product_detail::select('*')
          ->where('company_id',Auth::user()->company_id)
          ->where('deleted_by','=',NULL)
          ->where('qty','!=',0)
          ->with([
                    'rbatchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
          ->with('return_bill')->whereHas('return_bill',function ($q) use ($date){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'");
                  })
          ->orderBy('return_product_detail_id','DESC')
          ->paginate(10);

                  // echo '<pre>';
                  // print_r($productdetails);
                  // echo '</pre>';
                  // exit;
        $product_features =  ProductFeatures::getproduct_feature('');
        foreach ($productdetails AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $productdetails[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }
        
        foreach ($rproductdetails AS $rkey=>$rv) {
                if (isset($rv['product']['product_features_relationship']) && $rv['product']['product_features_relationship'] != '')
                {
                    foreach($product_features AS $rkk => $rvv)
                    {
                        $html_id = $rvv['html_id'];

                        if ($rv['product']['product_features_relationship'][$html_id] != '' && $rv['product']['product_features_relationship'][$html_id] != NULL)
                        {

                            $nm = product::feature_value($rvv['product_features_id'], $rv['product']['product_features_relationship'][$html_id]);

                            $rproductdetails[$rkey]['product'][$html_id] = $nm;
                        }
                    }
                }
            }

         return view('salesreport::profit_loss_report',compact('productdetails','rproductdetails','company_state','get_store','companyname'));

    }


    function datewise_profitloss_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
                $get_store       =  company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();
                $state_id        =  company_profile::select('state_id')->where('company_id',Auth::user()->company_id)->get();
                $company_state   =  $state_id[0]['state_id'];
                $data            =  $request->all();
                $sort_by         =  $data['sortby'];
                $sort_type       =  $data['sorttype'];
                $query           =  isset($data['query']) ? $data['query']  : '';


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


            $squery           =      sales_product_detail::select('*')->where('company_id',$company_id)->where('qty','!=',0);
            $rquery           =      return_product_detail::select('*')->where('company_id',$company_id);


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
            if($query['from_date'] == '' && $query['to_date'] == '' && $query['billno'] == '' && $query['barcode'] == '' && $query['productcode'] == '')
            {
                 $rstart           =      date("Y-m-d");
                 $rend             =      date("Y-m-d");
                 $squery ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
                 $rquery->with('return_bill')->whereHas('return_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
            }

                 $squery
                 ->with([
                    'batchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ]);


                 $rquery
                 ->with([
                    'rbatchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ]);





            $productdetails = $squery->orderBy($sort_by, $sort_type)->where('deleted_at','=',NULL)->paginate(10);

            // echo '<pre>';
            // print_r($productdetails);
            // exit;

            // $rcustom   =   collect();
            // $rdata     =   $rcustom->merge($rquery->get());

             $rproductdetails = $rquery->orderBy('return_product_detail_id','DESC')->where('deleted_at','=',NULL)->paginate(10);
             
              $product_features =  ProductFeatures::getproduct_feature('');
        foreach ($productdetails AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $productdetails[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }
        
        foreach ($rproductdetails AS $rkey=>$rv) {
                if (isset($rv['product']['product_features_relationship']) && $rv['product']['product_features_relationship'] != '')
                {
                    foreach($product_features AS $rkk => $rvv)
                    {
                        $html_id = $rvv['html_id'];

                        if ($rv['product']['product_features_relationship'][$html_id] != '' && $rv['product']['product_features_relationship'][$html_id] != NULL)
                        {

                            $nm = product::feature_value($rvv['product_features_id'], $rv['product']['product_features_relationship'][$html_id]);

                            $rproductdetails[$rkey]['product'][$html_id] = $nm;
                        }
                    }
                }
            }

            return view('salesreport::profit_loss_reportdata',compact('productdetails','rproductdetails','get_store','companyname'))->render();
        }


    }

    public function supplier_salereport()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';


        $date        =    date("Y-m-d");

        $productdetails =  sales_product_detail::select('*')
                  ->where('company_id',Auth::user()->company_id)
                  ->where('deleted_by','=',NULL)
                  ->where('qty','!=',0)
                  ->where('product_type','=',1)
                  ->with([
                    'batchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                  ])
                  ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($date){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'");
                    })
                  ->orderBy('sales_products_detail_id','DESC')
                  ->paginate(10);

          $rproductdetails =  return_product_detail::select('*')
          ->where('company_id',Auth::user()->company_id)
          ->where('deleted_by','=',NULL)
          ->where('qty','!=',0)
          ->with([
                    'rbatchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
          ->with('return_bill')->whereHas('return_bill',function ($q) use ($date){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'");
                  })
          ->orderBy('return_product_detail_id','DESC')
          ->paginate(10);
         return view('salesreport::supplier_salereport',compact('productdetails','rproductdetails','company_state','tax_type','taxname'));
    }
    function datewise_suppliersale_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
                $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
                $company_state   = $state_id[0]['state_id'];
                $tax_type        = $state_id[0]['tax_type'];
                $tax_title       = $state_id[0]['tax_title'];
                $taxname         = $tax_type==1?$tax_title:'IGST';
                $data            =      $request->all();
                $sort_by = $data['sortby'];
                $sort_type = $data['sorttype'];
                $query = isset($data['query']) ? $data['query']  : '';

            $squery           =      sales_product_detail::select('*');
            $rquery           =      return_product_detail::select('*');


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
                ->where('company_id',Auth::user()->company_id)
                ->get();

                $squery->whereIn('product_id',$product);
                $rquery->whereIn('product_id',$product);
            }
            if(isset($query) && $query != '' && $query['billno'] != '')
            {


                  $tbill_no  =  sales_bill::select('sales_bill_id')->where('bill_no', 'like', '%'.$query['billno'].'%')->where('company_id',Auth::user()->company_id)->get();

                  $squery->whereIn('sales_bill_id', $tbill_no);

                  $treturn_id = return_bill::select('return_bill_id')
                 ->where('company_id',Auth::user()->company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('sales_bill_id',$tbill_no)
                 ->get();

                  $rquery->whereIn('return_bill_id', $treturn_id);
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
            else
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



            $productdetails = $squery
            ->with([
                    'batchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
            ->where('product_type','=',1)->orderBy('sales_products_detail_id','DESC')->where('deleted_at','=',NULL)->paginate(10);

            // $rcustom   =   collect();
            // $rdata     =   $rcustom->merge($rquery->get());

             $rproductdetails = $rquery
             ->with([
                    'rbatchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
             ->where('product_type','=',1)->orderBy('return_product_detail_id','DESC')->where('deleted_at','=',NULL)->paginate(10);

            return view('salesreport::supplier_salereportdata',compact('productdetails','rproductdetails','tax_type','taxname','company_state'))->render();
        }


    }
    public function exportprofitloss_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

            $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();
               $state_id  =  company_profile::select('state_id','billtype')->where('company_id',Auth::user()->company_id)->get();
                $company_state   = $state_id[0]['state_id'];
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


            $squery           =      sales_product_detail::select('*')->where('company_id',$company_id)->where('qty','!=',0);
            $rquery           =      return_product_detail::select('*')->where('company_id',$company_id);


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
            if($query['from_date'] == '' && $query['to_date'] == '' && $query['billno'] == '' && $query['barcode'] == '' && $query['productcode']=='')
            {
                 $rstart           =      date("Y-m-d");
                 $rend             =      date("Y-m-d");
                 $squery ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
                 $rquery->with('return_bill')->whereHas('return_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
            }

              

            $productdetails = $squery
            ->with([
                    'batchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
            ->orderBy('sales_products_detail_id', 'DESC')->where('deleted_at','=',NULL)->get();
            $rproductdetails = $rquery
            ->with([
                    'rbatchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
            ->orderBy('return_product_detail_id','DESC')->where('deleted_at','=',NULL)->get();
            
            
             $product_features =  ProductFeatures::getproduct_feature('');
        foreach ($productdetails AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $productdetails[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }
        
        foreach ($rproductdetails AS $rkey=>$rv) {
                if (isset($rv['product']['product_features_relationship']) && $rv['product']['product_features_relationship'] != '')
                {
                    foreach($product_features AS $rkk => $rvv)
                    {
                        $html_id = $rvv['html_id'];

                        if ($rv['product']['product_features_relationship'][$html_id] != '' && $rv['product']['product_features_relationship'][$html_id] != NULL)
                        {

                            $nm = product::feature_value($rvv['product_features_id'], $rv['product']['product_features_relationship'][$html_id]);

                            $rproductdetails[$rkey]['product'][$html_id] = $nm;
                        }
                    }
                }
            }

        
        $this->page_url = ProductFeatures::get_current_page_url();
        $show_dynamic_feature = array();
             $overallsales   =  [];
             $header         =  [];
             if(sizeof($get_store)!=0)
             {
              $header[]  =  'Location';
             }
             $header[]  =  'Bill No.';
             $header[]  =  'Bill Date';
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
             $header[]  =  'Qty';
             $header[]  =  'SellingPrice';
             $header[]  =  'Discount Amount';
             $header[]  =  'Taxable Amount';
             $header[]  =  'Average Cost';
             $header[]  =  'Profit/Loss';
             $header[]  =  'Profit/Loss%';


            $overallsales['productdetails']    =  $productdetails;
            $overallsales['rproductdetails']   =  $rproductdetails;

          return Excel::download(new profitloss_export($overallsales, $header,$show_dynamic_feature,$companyname), 'ProfitLoss-Export.xlsx');


    }
    public function exportsuppliersale_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

           $state_id  =  company_profile::select('state_id','billtype','bill_calculation')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];

            $data            =      $request->all();
            $from_date       =      $data['from_date'];
            $to_date         =      $data['to_date'];
            $billno          =      $data['bill_no'];
            $customerid      =      $data['customerid'];
            $barcode         =      $data['barcode'];

            $squery           =      sales_product_detail::select('*');
            $rquery           =      return_product_detail::select('*');


            if($barcode != '')
            {
                if(strpos($barcode, '_') !== false)
                {
                    $prodbarcode   =   explode('_',$barcode);
                    $prod_barcode  =  $prodbarcode[0];
                    $prod_name     =  $prodbarcode[1];
                }
                else
                {
                    $prod_barcode      =  $barcode;
                    $prod_name         =  $barcode;
                }
                 $product = product::select('product_id')
                ->where('product_system_barcode','LIKE', "%$prod_barcode%")
                ->orWhere('product_name','LIKE',"%$prod_name%")
                ->where('company_id',Auth::user()->company_id)
                ->get();

                $squery->whereIn('product_id',$product);
                $rquery->whereIn('product_id',$product);
            }
            if($billno != '')
            {
                 $tbill_no  =  sales_bill::select('sales_bill_id')->where('bill_no', 'like', '%'.$billno.'%')->where('company_id',Auth::user()->company_id)->get();

                  $squery->whereIn('sales_bill_id', $tbill_no);

                  $treturn_id = return_bill::select('return_bill_id')
                 ->where('company_id',Auth::user()->company_id)
                 ->where('deleted_at','=',NULL)
                 ->whereIn('sales_bill_id',$tbill_no)
                 ->get();

                  $rquery->whereIn('return_bill_id', $treturn_id);
            }
            if($from_date != '' && $to_date != '')
            {

                 $rstart           =      date("Y-m-d",strtotime($from_date));
                 $rend             =      date("Y-m-d",strtotime($to_date));

                 // $squery->whereRaw("Date(created_at) between '$rstart' and '$rend'");
                 // $rquery->whereRaw("Date(created_at) between '$rstart' and '$rend'");
                 $squery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
                 $rquery->with('return_bill')->whereHas('return_bill',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
            }



            $productdetails = $squery
            ->with([
                    'batchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
            ->where('product_type','=',1)->orderBy('sales_products_detail_id', 'DESC')->where('qty','!=',0)->where('deleted_at','=',NULL)->get();
            $rproductdetails = $rquery
            ->with([
                    'rbatchprice_master' => function($fquery) {
                        $fquery->select('batch_no','price_master_id');
                    }
                ])
            ->where('product_type','=',1)->orderBy('return_product_detail_id','DESC')->where('deleted_at','=',NULL)->get();

            $overallsales   =  [];

             $header       = [];
             $header[]  =  'Supplier';
             $header[]  =  'Invoice No.';
             $header[]  =  'Bill No.';
             $header[]  =  'Bill Date';
             $header[]  =  'Product Name';
             $header[]  =  'Barcode';
             $header[]  =  'HSN';
             $header[]  =  'Size/UQC';
             if($state_id[0]['billtype']==3)
             {
              $header[]  =  'Batch No.';
             }
             $header[]  =  'Qty';
             if($state_id[0]['bill_calculation']==1)
             {

             $header[]  =  'Taxable Amount';
             $header[]  =  'CGST%';
             $header[]  =  'CGST Amount';
             $header[]  =  'SGST%';
             $header[]  =  'SGST Amount';
             $header[]  =  'IGST%';
             $header[]  =  'IGST Amount';
             $header[]  =  'Total Amount';
             $header[]  =  'Cost Price';
             $header[]  =  'Profit/Loss';
             $header[]  =  'Profit/Loss%';
           }



            $overallsales['productdetails']    =  $productdetails;
            $overallsales['rproductdetails']   =  $rproductdetails;

          return Excel::download(new supplier_salereport_export($overallsales, $header), 'Supplier_Salereport-Export.xlsx');


    }

}
