<?php

namespace Retailcore\Sales\Http\Controllers;
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
use Retailcore\Consignment\Models\consign_products_detail;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Auth;
use DB;
use Log;


class SalesBillController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
  
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

        $todate       =    date('Y-m-d');
        
        $newyear      =   date('Y-04-01');
        
        $newmonth     =   date('Y-m-01');

//////////////////For Bill series Year Wise 
        if($cstate_id[0]['series_type']==1)
        {

            if($todate>=$newyear)
                {
                      $nseries  =  sales_bill::select('bill_series')
                                                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') >= '$newyear'")
                                                //->where('sales_bill_id','<',$sales_bill_id)
                                                ->where('company_id',Auth::user()->company_id)
                                                ->orderBy('sales_bill_id','DESC')
                                                ->take('1')
                                                ->first();
                }
                else
                {
                      $nseries  =  sales_bill::select('bill_series')
                                  ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') <= '$todate'")
                                  //->where('sales_bill_id','<',$sales_bill_id)
                                  ->where('company_id',Auth::user()->company_id)
                                  ->orderBy('sales_bill_id','DESC')
                                  ->take('1')
                                  ->first();
                }
              if($nseries=='')
              {
                  $billseries  =  1;
              }
              else
              {
                  $billseries   = $nseries['bill_series']+1;
                  
              }


            $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
            $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

            $invoiceno          =       $cstate_id[0]['bill_number_prefix'].$billseries.'/'.$f1.'-'.$f2;  
           
             
        }

 //////////////////For Bill series Month Wise        
        else
        {
            if($todate>=$newmonth)
              {

                  $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') >= '$newmonth'")
                                            ->where('sales_bill_id','<',$last_invoice_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                       
               
                  if($newseries=='')
                  {
                      $billseries  =  1;
                  }
                  else
                  {
                      $billseries   = $newseries['bill_series']+1;
                      
                  }
                 
               
              }
              else
              {
                $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') <= '$todate'")
                                            ->where('sales_bill_id','<',$last_invoice_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                      $billseries   = $newseries['bill_series']+1;

                
              }

              $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
              $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');
             
              $co     =     strlen($billseries);  
    
              if($co<=2)
              $id1  = '00'.$billseries; 
              elseif($co<=3)
              $id1  = '0'.$billseries;      
              elseif($co<=4)
              $id1  = $billseries;
              $dd   = date('my');
              
              $invoiceno = $cstate_id[0]['bill_number_prefix'].$dd.''.$id1;
        }

      

        $chargeslist      =   product::select('product_id','product_name','sell_gst_percent') 
                              ->where('company_id',Auth::user()->company_id)
                              ->where('item_type','=',2)
                              ->get();

        $customer_source = customer_source::where('company_id',Auth::user()->company_id)
          ->where('deleted_at','=',NULL)
          ->orderBy('customer_source_id','DESC')->get();

       $sales_type=1;
       $get_store = company_relationship_tree::where('warehouse_id', '=', Auth::user()->company_id)
        ->with('company_profile')
        ->get();


       
        return view('sales::sales_bill',compact('payment_methods','invoiceno','state','country','chargeslist','ppvalues','customer_source','sales_type','get_store'));
    }

   public function consign_bill()
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

        $todate       =    date('Y-m-d');
        
        $newyear      =   date('Y-04-01');
        
        $newmonth     =   date('Y-m-01');

//////////////////For Bill series Year Wise 
        if($cstate_id[0]['series_type']==1)
        {

            $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
            $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

            $invoiceno          =       $cstate_id[0]['bill_number_prefix'].$last_invoice_id.'/'.$f1.'-'.$f2;  
           
             
        }

 //////////////////For Bill series Month Wise        
        else
        {
            if($todate>=$newmonth)
              {

                  $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') >= '$newmonth'")
                                            ->where('sales_bill_id','<',$last_invoice_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                       
               
                  if($newseries=='')
                  {
                      $billseries  =  1;
                  }
                  else
                  {
                      $billseries   = $newseries['bill_series']+1;
                      
                  }
                 
               
              }
              else
              {
                $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') <= '$todate'")
                                            ->where('sales_bill_id','<',$last_invoice_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                      $billseries   = $newseries['bill_series']+1;

                
              }

              $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
              $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');
             
              $co     =     strlen($billseries);  
    
              if($co<=2)
              $id1  = '00'.$billseries; 
              elseif($co<=3)
              $id1  = '0'.$billseries;      
              elseif($co<=4)
              $id1  = $billseries;
              $dd   = date('my');
              
              $invoiceno = $cstate_id[0]['bill_number_prefix'].$dd.''.$id1;
        }

      

        $chargeslist      =   product::select('product_id','product_name','sell_gst_percent') 
                              ->where('company_id',Auth::user()->company_id)
                              ->where('item_type','=',2)
                              ->get();

        $customer_source = customer_source::where('company_id',Auth::user()->company_id)
          ->where('deleted_at','=',NULL)
          ->orderBy('customer_source_id','DESC')->get();

       $sales_type=1;
       $get_store = company_relationship_tree::where('warehouse_id', '=', Auth::user()->company_id)
        ->with('company_profile')
        ->get();


       
        return view('sales::consign_bill',compact('payment_methods','invoiceno','state','country','chargeslist','ppvalues','customer_source','sales_type','get_store'));
    }

    public function franchise_bill()
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

        $todate       =    date('Y-m-d');
        
        $newyear      =   date('Y-04-01');
        
        $newmonth     =   date('Y-m-01');

//////////////////For Bill series Year Wise 
        if($cstate_id[0]['series_type']==1)
        {

            $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
            $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

            $invoiceno          =       $cstate_id[0]['bill_number_prefix'].$last_invoice_id.'/'.$f1.'-'.$f2;  
           
             
        }

 //////////////////For Bill series Month Wise        
        else
        {
            if($todate>=$newmonth)
              {

                  $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') >= '$newmonth'")
                                            ->where('sales_bill_id','<',$last_invoice_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                       
               
                  if($newseries=='')
                  {
                      $billseries  =  1;
                  }
                  else
                  {
                      $billseries   = $newseries['bill_series']+1;
                      
                  }
                 
               
              }
              else
              {
                $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') <= '$todate'")
                                            ->where('sales_bill_id','<',$last_invoice_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                      $billseries   = $newseries['bill_series']+1;

                
              }

              $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
              $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');
             
              $co     =     strlen($billseries);  
    
              if($co<=2)
              $id1  = '00'.$billseries; 
              elseif($co<=3)
              $id1  = '0'.$billseries;      
              elseif($co<=4)
              $id1  = $billseries;
              $dd   = date('my');
              
              $invoiceno = $cstate_id[0]['bill_number_prefix'].$dd.''.$id1;
        }

      

        $chargeslist      =   product::select('product_id','product_name','sell_gst_percent') 
                              ->where('company_id',Auth::user()->company_id)
                              ->where('item_type','=',2)
                              ->get();

        $customer_source = customer_source::where('company_id',Auth::user()->company_id)
          ->where('deleted_at','=',NULL)
          ->orderBy('customer_source_id','DESC')->get();

          $sales_type=2;
          $get_store = company_relationship_tree::where('warehouse_id', '=', Auth::user()->company_id)
          ->with('company_profile')
          ->get();



       
        return view('sales::sales_bill',compact('payment_methods','invoiceno','state','country','chargeslist','ppvalues','customer_source','sales_type','get_store'));
    }

   public function refname_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {

            $json = [];
            $result = reference::select('reference_name')
                ->where('reference_name', 'LIKE', "%$request->search_val%")
                ->where('company_id',Auth::user()->company_id)->get();

           
           

            if(!empty($result))
            {
           
                foreach($result as $billkey=>$billvalue){


                      $json[] = $billvalue['reference_name'];
                      
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
    public function creditnote_numbersearch(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {

            $json = [];
            $result = customer_creditnote::select('creditnote_no')
                ->where('creditnote_no', 'LIKE', "%$request->search_val%")
                ->where('company_id',Auth::user()->company_id)->get();

           
           

            if(!empty($result))
            {
           
                foreach($result as $billkey=>$billvalue){


                      $json[] = $billvalue['creditnote_no'];
                      
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
    public function consignproduct_search(Request $request)
    {
        $customer_id  = $request->customer_id;
        if($request->search_val !='')
        {

            $json = [];

            
            $result = product::select('product_name','product_system_barcode','supplier_barcode','product_id','product_code','product_id')
                ->where('item_type','!=',2)
                ->where(function($query) use ($request,$customer_id)
                {
                    $query->where('product_name', 'LIKE', "%$request->search_val%")
                        ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
                        ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%");
                })
                ->with('reportprice_master')
                ->whereHas('reportprice_master',function ($q)  use ($customer_id){
                  $q->where('company_id',Auth::user()->company_id);
                 })
                ->with([
                    'consign_products_detail' => function($fquery)  use ($customer_id){
                        $fquery->select('*');
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->with('consign_bill')->whereHas('consign_bill',function ($q) use ($customer_id){
                            $q->where('customer_id',$customer_id);
                            $q->where('company_id',Auth::user()->company_id);
                        });

                    }
                ])
                ->take(10)->get();

                // echo '<pre>';
                // print_r($result);
                // exit;
           
            $ik = 0;      
             if(sizeof($result) != 0) 
             {
                foreach($result as $sproductkey=>$sproductvalue){

                        foreach($sproductvalue['consign_products_detail'] as $psproductkey=>$psproductvalue){
                         

                          if($sproductvalue['supplier_barcode']!='' || $sproductvalue['supplier_barcode']!=null)
                          {
                             $showbarcode   =   $sproductvalue['supplier_barcode'];
                          }
                          else
                          {
                             $showbarcode   =   $sproductvalue['product_system_barcode'];
                          }
 
                                $json[$sproductkey][$psproductkey]['label'] = $showbarcode.'_'.$sproductvalue['product_name'].'_'.$psproductvalue['consign_bill']['bill_no'];
                                $json[$sproductkey][$psproductkey]['consign_products_detail_id'] = $psproductvalue['consign_products_detail_id'];
                                
                           

                        }
                            
                         
                }
               
            }
           
            if(sizeof($json)!=0)
            {
                return json_encode($json);
            }
            else
            {
                return json_encode(array("Success"=>"False","Message"=>"There is no Consign challan exist against this Barcode !"));
            }
             
           
        }
        else
        {
           $json = [];
           return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));
          
        }
       
        
    }
    public function consignproduct_detail(Request $request)
    {
      
     
            $consign_products_detail_id   =  $request->consign_products_detail_id;
            $todaydate    =  date("Y-m-d");

            $query = consign_products_detail::select('*',DB::raw("(SELECT cost_rate FROM inward_product_details WHERE inward_product_details.product_id = consign_products_details.product_id order by inward_product_detail_id LIMIT 1) as costprice"))
                ->withCount([
                    'sales_product_detail as totalsoldqty' => function($fquery){
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                      }
                  ])
                ->with('batchprice_master')             
                ->with('product')
                ->with('consign_bill')
                ->where('consign_products_detail_id',$consign_products_detail_id);
                  
                

              $result  =  $query->get();
              // echo '<pre>';
              // print_r($result[0]['product_id']);
              // exit;


               if(sizeof($result) != 0)
               {
                   foreach($result as $skey=>$svalue)
                   {

                           $product_features =  ProductFeatures::getproduct_feature('');

                          if(isset($svalue['product']['product_features_relationship']) && $svalue['product']['product_features_relationship'] != '')
                          {
                              foreach ($product_features AS $kk => $vv)
                              {
                                  $html_id = $vv['html_id'];

                                  if($svalue['product']['product_features_relationship'][$html_id] != '' && $svalue['product']['product_features_relationship'][$html_id] != NULL)
                                  {
                                      $nm =  product::feature_value($vv['product_features_id'],$svalue['product']['product_features_relationship'][$html_id]);
                                      $svalue['product'][$html_id] =$nm;
                                  }
                              }
                          }
                    }

                    if($result[0]['qty'] == $result[0]['totalsoldqty'])
                    {
                       return json_encode(array("Success"=>"False","Message"=>"Bill has already been generated against this consign Product"));
                    }
                    else
                    {
                       return json_encode(array("Success"=>"True","Data"=>$result));
                    }

                      
                 
               }
               else
               {
                   return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));
               }
                

    }
    public function sinvoice_no_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
           $result = inward_stock::select('inward_stock_id', 'supplier_gst_id', 'invoice_no', 'inward_type')
            ->where('company_id', Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->whereNull('warehouse_id')
            ->where('invoice_no', 'LIKE', "%$request->search_val%")
            ->with('supplier_gstdetail.supplier_company_info')
            ->get();

            $ik = 0;      
             if(sizeof($result) != 0) 
             {
           
                foreach($result as $skey=>$svalue){

                           $json[$ik]['label'] = $svalue['invoice_no'].'_'.$svalue['supplier_gstdetail']['supplier_company_info']['supplier_company_name']; 
                           $json[$ik]['inward_stock_id'] = $svalue['inward_stock_id'];                           
                           $ik++;
                   
                }
            } 
         
           
            if(sizeof($json)!=0)
            {
                return json_encode($json);
            }
            else
            {
                return json_encode(array("Success"=>"False","Message"=>"Scanned Product Barcode does not exist"));
            }
       
        
    }
    public function inwardproduct_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

           $inward_stock_id   =  $request->inward_stock_id;


            $result = inward_product_detail::where('company_id', Auth::user()->company_id)
                                          ->where('inward_stock_id',$inward_stock_id)
                                          ->where('pending_return_qty','>',0)
                                          ->with('product')
                                          ->get();

                              foreach($result as $pkey=>$pval)
                              {
                                   if($pval['batch_no'] == NULL)
                                   {
                                        $pricemasterid  =  price_master::where('company_id', Auth::user()->company_id)
                                                                        ->where('product_id',$pval['product_id'])
                                                                        ->where('offer_price',$pval['offer_price'])
                                                                        ->whereNull('batch_no')
                                                                        ->where('product_qty','>',0)
                                                                        ->where('deleted_at',NULL)
                                                                        ->select('price_master_id')
                                                                        ->first();
                                   }
                                   else
                                   {
                                        $pricemasterid  =  price_master::where('company_id', Auth::user()->company_id)
                                                                        ->where('product_id',$pval['product_id'])
                                                                        ->where('offer_price',$pval['offer_price'])
                                                                        ->where('batch_no',$pval['batch_no'])
                                                                        ->where('product_qty','>',0)
                                                                        ->where('deleted_at',NULL)
                                                                        ->select('price_master_id')
                                                                        ->first();
                                   }

                                   $pval['price_master_id']  = $pricemasterid['price_master_id'];
                              }
                  

                
                $blankprice_master_id  =  0;   
                $blankbarcode  =  '';     
                $showbatchno ='';                  

               if(sizeof($result) != 0)
               {
                      foreach($result as $rkey=>$rvalue)
                      {
                          if($rvalue['price_master_id']=='')
                          {
                              $showbatchno    =  $rvalue['batch_no'];
                              $blankprice_master_id++;
                              $blankbarcode   =  $rvalue['product']['product_system_barcode'];
                          }

                         $product_features =  ProductFeatures::getproduct_feature('');

                        if(isset($rvalue['product']['product_features_relationship']) && $rvalue['product']['product_features_relationship'] != '')
                          {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($rvalue['product']['product_features_relationship'][$html_id] != '' && $rvalue['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$rvalue['product']['product_features_relationship'][$html_id]);
                                    $rvalue['product'][$html_id] =$nm;
                                }
                            }
                      }
                    }
                }
                       
               

                   if(sizeof($result)==0)  
                   {
                      return json_encode(array("Success"=>"False","Message"=>"No Product details available"));
                   }
                   else
                   {
                       if($blankprice_master_id == 0)
                       {
                          return json_encode(array("Success"=>"True","Data"=>$result));
                       }
                       else
                       {
                          return json_encode(array("Success"=>"False","Data"=>$result,"Message"=>"No Entry found in price master table against this Barcode ".$blankbarcode." and Batch No. ".$showbatchno." "));
                       }
                   }

               
                  
              

                

    }
    public function sproduct_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {

            $json = [];

            $cstate_id = company_profile::select('billtype','inward_type')->where('company_id',Auth::user()->company_id)->get();


            $pquery = product::select('product_name','product_system_barcode','supplier_barcode','product_id','product_code','product_id')
                      ->where('item_type','!=',2)
                      ->where(function($query) use ($request)
                      {
                          $query->where('product_name', 'LIKE', "%$request->search_val%")
                              ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
                              ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%")
                              ->orWhere('product_code', 'LIKE', "%$request->search_val%");
                      })
                      ->with('reportprice_master');
                      


                  if($cstate_id[0]['inward_type']==2)
                  {
                      $pquery->with('product_features_relationship');
                  }

                  $result  =  $pquery->whereHas('reportprice_master',function ($q) {
                              $q->where('company_id',Auth::user()->company_id);
                             })->take(10)->get();

                


            $show_dynamic_feature = '';
           if($cstate_id[0]['inward_type']==2)
           {
                
                $product_features =  ProductFeatures::getproduct_feature('');

                        if (isset($product_features) && $product_features != '' && !empty($product_features))
                        {
                          foreach ($product_features AS $feature_key => $feature_value)
                          {
                              if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
                              {
                                  $search ='sales_bill';

                                  if (strstr($feature_value['show_feature_url'],$search) )
                                  {
                                          if($show_dynamic_feature == '')
                                          {
                                              $show_dynamic_feature =$feature_value['html_id'];
                                          }
                                          else
                                          {
                                              $show_dynamic_feature = $show_dynamic_feature.','.$feature_value['html_id'];
                                          }                              
                                   } 
                              }
                          }
                        }

                       
               
                      foreach($result as $pkey=>$pvalue)
                      {
                           
                            if(isset($pvalue['product_features_relationship']) && $pvalue['product_features_relationship'] != '')
                            {
                                foreach ($product_features AS $kk => $vv)
                                {
                                    $html_id = $vv['html_id'];

                                    if($pvalue['product_features_relationship'][$html_id] != '' && $pvalue['product_features_relationship'][$html_id] != NULL)
                                    {
                                        $nm =  product::feature_value($vv['product_features_id'],$pvalue['product_features_relationship'][$html_id]);
                                         $pvalue[$html_id] =$nm;

                                       

                                    }
                                }
                            }

                      }
                       


           }
          
           
            $ik = 0;      
             if(sizeof($result) != 0) 
             {
                $feature_show_val = "";
                foreach($result as $productkey=>$productvalue)
                {

                      
                      if($cstate_id[0]['inward_type']==2)
                      {
                          

                              $feature_show_val = "";
                              if($show_dynamic_feature != '')
                              {
                                  $feature = explode(',',$show_dynamic_feature);

                                  foreach($feature AS $fea_key=>$fea_val)
                                  {
                                      if($productvalue[$fea_val] !='' || $productvalue[$fea_val]!=null)
                                      {
                                         $feature_show_val .= '_'.$productvalue[$fea_val];
                                      }
                                      
                                  }
                              }
                      }
                
                       
                   
                     if (preg_match("/$request->search_val/i", $productvalue['supplier_barcode']))
                     {
                           $json[$ik]['label'] = $productvalue['supplier_barcode'].'_'.$productvalue['product_name'].$feature_show_val;
                           $json[$ik]['product_id'] = $productvalue['product_id'];
                           $ik++;
                     }
                     else if (preg_match("/$request->search_val/i", $productvalue['product_system_barcode']))
                     {
                       $json[$ik]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'].$feature_show_val;
                        $json[$ik]['product_id'] = $productvalue['product_id'];
                        $ik++;
                     }
                     else if(preg_match("/$request->search_val/i", $productvalue['product_code']))
                     {
                        $json[$ik]['label'] = $productvalue['product_code'];
                        $json[$ik]['product_id'] = $productvalue['product_id'];
                        $ik++;
                     }
                     else
                     {
                        $json[$ik]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'].$feature_show_val;
                        $json[$ik]['product_id'] = $productvalue['product_id'];
                        $ik++;
                     }
                      
                       
                      
                      
                      
                      
                }
            }

           
            if(sizeof($json)!=0)
            {
                return json_encode($json);
            }
            else
            {
                return json_encode(array("Success"=>"False","Message"=>"Scanned Product Barcode does not exist"));
            }
             
           
        }
        else
        {
           $json = [];
           return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));
          
        }
       
        
    }
     public function fastproduct_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $cstate_id = company_profile::select('po_with_unique_barcode')->where('company_id',Auth::user()->company_id)->first();

        if($cstate_id['po_with_unique_barcode'] ==0)
        {
              if($request->search_val !='')
              {

                  $json = [];


                  $result = product::select('product_name','product_system_barcode','supplier_barcode','product_id','hsn_sac_code','product_id')
                      ->where('item_type','!=',2)
                      ->where(function($query) use ($request)
                      {
                          $query->where('product_name', 'LIKE', "%$request->search_val%")
                              ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
                              ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%")
                              ->orWhere('hsn_sac_code', 'LIKE', "%$request->search_val%");
                      })
                      ->with('price_master')
                      ->whereHas('price_master',function ($q) {
                        $q->where('company_id',Auth::user()->company_id);
                       })->take(10)->get();

                 
                  $ik = 0;      
                   if(sizeof($result) != 0) 
                   {
                 
                      foreach($result as $productkey=>$productvalue){

                         
                           if (preg_match("/$request->search_val/i", $productvalue['supplier_barcode']))
                           {
                                 $json[$ik]['label'] = $productvalue['supplier_barcode'];
                                 $json[$ik]['product_id'] = $productvalue['product_id'];
                                 $ik++;
                           }
                           else if (preg_match("/$request->search_val/i", $productvalue['product_system_barcode']))
                           {
                             $json[$ik]['label'] = $productvalue['product_system_barcode'];
                              $json[$ik]['product_id'] = $productvalue['product_id'];
                              $ik++;
                           }
                            
                      }
                  }
                
                  
                  if(sizeof($json)!=0)
                  {
                      return json_encode($json);
                  }
                  else
                  {
                      return json_encode(array("Success"=>"False","Message"=>"Scanned Product Barcode does not exist"));
                  }
                 
              }
              else
              {
                 $json = [];
                 return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));
                
              }
        }
        else
        {
              if($request->search_val !='')
              {

                  $json = [];
                  $result = [];

                  $uresult  =  price_master::select('batch_no','price_master_id','product_id')
                                            ->where('batch_no','LIKE', "%$request->search_val%")
                                            ->where('company_id',Auth::user()->company_id)
                                            ->take(10)->get();
                                        // echo '<pre>';
                                        // print_r($uresult);    

                  if(sizeof($uresult)==0)
                  {                          
                      $result = product::select('product_name','product_system_barcode','supplier_barcode','product_id','hsn_sac_code','product_id')
                          ->where('item_type','!=',3)
                          ->where(function($query) use ($request)
                          {
                              $query->where('product_system_barcode','LIKE', "%$request->search_val%")
                                    ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%");
                          })
                          ->with('price_master')
                          ->whereHas('price_master',function ($q) {
                            $q->select('price_master_id');
                            $q->where('company_id',Auth::user()->company_id);
                            $q->orderBy('price_master_id','ASC');
                            $q->where('product_qty','>',0);
                            $q->take(1);
                           })->take(10)->get();
                    }

                 
                  $ik = 0;      
                   if(sizeof($result) != 0) 
                   {
                 
                      foreach($result as $productkey=>$productvalue){

                         
                           if (preg_match("/$request->search_val/i", $productvalue['supplier_barcode']))
                           {
                                 $json[$ik]['label'] = $productvalue['supplier_barcode'];
                                 $json[$ik]['product_id'] = $productvalue['product_id'];
                                 $json[$ik]['price_master_id'] = $productvalue['price_master']['price_master_id'];
                                 $ik++;
                           }
                           else if (preg_match("/$request->search_val/i", $productvalue['product_system_barcode']))
                           {
                             $json[$ik]['label'] = $productvalue['product_system_barcode'];
                              $json[$ik]['product_id'] = $productvalue['product_id'];
                              $json[$ik]['price_master_id'] = $productvalue['price_master']['price_master_id'];
                              $ik++;
                           }
                            
                      }
                  }
                  $jk = 0;      
                   if(sizeof($uresult) != 0) 
                   {
                      
                      foreach($uresult as $uproductkey=>$uproductvalue){

                         
                           if (preg_match("/$request->search_val/i", $uproductvalue['batch_no']))
                           {
                           
                                 $json[$ik]['label'] = $uproductvalue['batch_no'];
                                 $json[$ik]['product_id'] = $uproductvalue['product_id'];
                                 $json[$ik]['price_master_id'] = $uproductvalue['price_master_id'];
                                 $ik++;
                           }
                          
                      }
                  }
                //   print_r($json);
                // exit;
                  
                  if(sizeof($json)!=0)
                  {
                      return json_encode($json);
                  }
                  else
                  {
                      return json_encode(array("Success"=>"False","Message"=>"Scanned Product Barcode does not exist"));
                  }
                 
              }
              else
              {
                 $json = [];
                 return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));
                
              }
        }
    }

   public function bsproduct_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {
            $sresult = [];
            $json = [];
            $company_id  = Auth::user()->company_id;

               $result = price_master::where('company_id', Auth::user()->company_id)
                  ->where('deleted_at', '=', NULL)
                  ->where('batch_no', 'LIKE', "%$request->search_val%")
                  ->where('product_qty','>',0)
                  ->with('product')
                  ->whereHas('product',function ($q) use($request){
                          $q->select('product_name','product_system_barcode','supplier_barcode','product_id');
                          //$q->where('company_id', Auth::user()->company_id);
                    })->take(10)->get();


                  if(sizeof($result) == 0)
                  {

                       $sresult = product::select('product_name','product_system_barcode','supplier_barcode','product_id')
                                ->where('deleted_at', '=', NULL)
                                ->where('item_type','!=',3)
                                ->where(function($query) use ($request)
                                {
                                    $query->orWhere('product_name', 'LIKE', "%$request->search_val%")
                                        ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
                                        ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%");
                                })
                                ->groupBy('product_id')
                                ->with([
                                        'price_master' => function($fquery) use ($request,$company_id){
                                            $fquery->where('product_qty','>',0);
                                            $fquery->where('company_id', Auth::user()->company_id);                                        
                                            $fquery->select('batch_no','price_master_id','product_id');
                                           
                                        }
                                   ])
                                 ->take(10)->get();
                    }

                
               

            if(sizeof($result) != 0)
            {
                
                
                foreach($result as $productkey=>$productvalue){

                      if($productvalue['supplier_barcode']!='' || $productvalue['supplier_barcode']!=null)
                      {
                          $json[$productkey]['label'] = $productvalue['product']['supplier_barcode'].'_'.$productvalue['product']['product_name'].'_'.$productvalue['batch_no'];
                          $json[$productkey]['product_id'] = $productvalue['product']['product_id'];
                          $json[$productkey]['price_master_id'] = $productvalue['price_master_id'];
                          // $json[$productkey]['barcode'] = $productvalue['product']['product_system_barcode'];
                          // $json[$productkey]['product_name'] = $productvalue['product']['product_name'];
                          // $json[$productkey]['batch_no'] = $productvalue['batch_no'];
                      }
                      else
                      {
                        $json[$productkey]['label'] = $productvalue['product']['product_system_barcode'].'_'.$productvalue['product']['product_name'].'_'.$productvalue['batch_no'];
                        $json[$productkey]['product_id'] = $productvalue['product']['product_id'];
                        $json[$productkey]['price_master_id'] = $productvalue['price_master_id'];
                        // $json[$productkey]['barcode'] = $productvalue['product']['product_system_barcode'];
                        // $json[$productkey]['product_name'] = $productvalue['product']['product_name'];
                        // $json[$productkey]['batch_no'] = $productvalue['batch_no'];
                      }


                      
                }
            }
           if(sizeof($sresult) != 0)
            {
            
               foreach($sresult as $sproductkey=>$sproductvalue){

                        foreach($sproductvalue['price_master'] as $psproductkey=>$psproductvalue){
                         

                          if($sproductvalue['supplier_barcode']!='' || $sproductvalue['supplier_barcode']!=null)
                          {
                             $showbarcode   =   $sproductvalue['supplier_barcode'];
                          }
                          else
                          {
                             $showbarcode   =   $sproductvalue['product_system_barcode'];
                          }

                            if($psproductvalue['batch_no']!='' || $psproductvalue['batch_no']!=null)
                              {

                                $json[$sproductkey][$psproductkey]['label'] = $showbarcode.'_'.$sproductvalue['product_name'].'_'.$psproductvalue['batch_no'];
                                $json[$sproductkey][$psproductkey]['product_id'] = $sproductvalue['product_id'];
                                $json[$sproductkey][$psproductkey]['price_master_id'] = $psproductvalue['price_master_id'];
                                // $json[$sproductkey][$psproductkey]['barcode'] = $sproductvalue['product_system_barcode'];
                                // $json[$sproductkey][$psproductkey]['product_name'] = $sproductvalue['product_name'];
                                // $json[$sproductkey][$psproductkey]['batch_no'] = $psproductvalue['batch_no'];
                            }
                            else
                            {   
                                $json[$sproductkey][$psproductkey]['label'] = $showbarcode.'_'.$sproductvalue['product_name'].'_'.$psproductvalue['batch_no'];
                                $json[$sproductkey][$psproductkey]['product_id'] = $sproductvalue['product_id'];
                                $json[$sproductkey][$psproductkey]['price_master_id'] = $psproductvalue['price_master_id'];
                                // $json[$sproductkey][$psproductkey]['barcode'] = $sproductvalue['product_system_barcode'];
                                // $json[$sproductkey][$psproductkey]['product_name'] = $sproductvalue['product_name'];
                                // $json[$sproductkey][$psproductkey]['batch_no'] = $psproductvalue['batch_no'];

                            }

                        }
                            
                         
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

    public function sproduct_detail(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
            $product_id   =  $request->product_id;
            $todaydate    =  date("Y-m-d");

            $query = product::select('product_name','sku_code','hsn_sac_code','product_id','product_system_barcode','supplier_barcode','uqc_id')
                    ->with([
                    'price_master' => function($fquery) {
                         $fquery->select('*',DB::raw("(SELECT cost_rate FROM inward_product_details WHERE inward_product_details.product_id = price_masters.product_id order by inward_product_detail_id LIMIT 1) as costprice"));
                    }
                ])
                ->with([
                'discount_master' => function($fquery) use ($todaydate) {
                    $fquery->select('discount_percent','product_id');                       
                    $fquery->whereRaw("STR_TO_DATE(discount_masters.from_date,'%d-%m-%Y') <= '$todaydate' and STR_TO_DATE(discount_masters.to_date,'%d-%m-%Y') >= '$todaydate'");
                }
                ])
                ->with('product_features_relationship')
                ->where('product_id','=',$product_id);

                

              $result  =  $query->get();
              // echo '<pre>';
              // print_r($result);
              // exit;


               if(sizeof($result) != 0)
               {
                    $overallqty =  price_master::where('company_id',Auth::user()->company_id)
                         ->where('product_id','=',$product_id)
                         ->sum('product_qty');

                         $product_features =  ProductFeatures::getproduct_feature('');

                        if(isset($result[0]['product_features_relationship']) && $result[0]['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($result[0]['product_features_relationship'][$html_id] != '' && $result[0]['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$result[0]['product_features_relationship'][$html_id]);
                                    $result[0][$html_id] =$nm;
                                }
                            }
                        }

                   if($overallqty ==0 || $overallqty < 0)  
                   {
                      return json_encode(array("Success"=>"False","Message"=>"Stock not available for this Product"));
                   }
                   else
                   {
                      return json_encode(array("Success"=>"True","Data"=>$result,"Stock"=>$overallqty));
                   }
              

               }
               else
               {
                   return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));
               }
                

    }
    public function bsproduct_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
      // $prod_barcode     =  $request->barcode;
      // $prod_name        =  $request->product_name;
      // $batch_no         =  $request->batch_no;
      $product_id       =  $request->product_id;
      $price_master_id  =  $request->price_master_id;

      $presult   =  array();
      $ppresult  =  array();
      $todaydate = date("Y-m-d");
      $result    = array();

      
      
        // if($batch_no=='')
        // {
        //   $presult = product::select('product_id')->where('product_system_barcode',$prod_barcode)
        //       ->where('product_name',$prod_name)
        //       // ->orWhere('supplier_barcode',$prod_barcode)
        //       ->where('company_id',Auth::user()->company_id)
        //       ->get();
        // }
        // if(sizeof($presult) == 0) 
        // {

          $ppresult = price_master::select('price_master_id')
              //->where('batch_no',$batch_no)
              ->where('product_id',$product_id)
              ->where('price_master_id',$price_master_id)
              // ->where('product_id',DB::raw("(SELECT products.product_id FROM products WHERE products.product_system_barcode = '$prod_barcode' and products.product_name = '$prod_name')"))
              ->where('company_id',Auth::user()->company_id)
              //->where('product_qty','>',0)
              ->with([
                    'discount_master' => function($fquery) use ($todaydate) {
                        $fquery->select('discount_percent','product_id');                       
                        $fquery->whereRaw("STR_TO_DATE(discount_masters.from_date,'%d-%m-%Y') <= '$todaydate' and STR_TO_DATE(discount_masters.to_date,'%d-%m-%Y') >= '$todaydate'");
                    }
                ])
              ->get();    
       

          if(sizeof($ppresult) != 0) 
          {
            //echo 'bbb';
               $result = price_master::select('*',DB::raw("(SELECT cost_rate FROM inward_product_details WHERE inward_product_details.product_id = price_masters.product_id order by inward_product_detail_id LIMIT 1) as costprice"))
              ->where('price_master_id',$ppresult[0]['price_master_id'])
              ->where('company_id',Auth::user()->company_id)
               ->with(['product' => function ($pquery) {
                        $pquery->select('product_id','product_name', 'product_system_barcode','supplier_barcode','sku_code','hsn_sac_code');
                    }])
               ->with('product.uqc')                
               ->with('product.product_features_relationship')
               ->where('product_qty','>',0)
               ->with([
                    'discount_master' => function($fquery) use ($todaydate) {
                        $fquery->select('discount_percent','product_id');                       
                        $fquery->whereRaw("STR_TO_DATE(discount_masters.from_date,'%d-%m-%Y') <= '$todaydate' and STR_TO_DATE(discount_masters.to_date,'%d-%m-%Y') >= '$todaydate'");
                    }
                ])
               ->get();
              

                $product_features =  ProductFeatures::getproduct_feature('');

                        if(isset($result[0]['product']['product_features_relationship']) && $result[0]['product']['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($result[0]['product']['product_features_relationship'][$html_id] != '' && $result[0]['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$result[0]['product']['product_features_relationship'][$html_id]);
                                    $result[0]['product'][$html_id] =$nm;

                                   

                                }
                            }
                        }


          }

              
    

      return json_encode(array("Success"=>"True","Data"=>$result));
    }
    public function charges_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {

            $json = [];
            $result = product::select('product_name','product_id')
                ->where('product_name', 'LIKE', "%$request->search_val%")
                ->where('company_id',Auth::user()->company_id)
                ->Where('item_type','=','2')->get();

          
           

              if(!empty($result))
              {
           
                  foreach($result as $productkey=>$productvalue){


                      $json[] = $productvalue['product_name'];
                     
                     
                      
                }
            }
           
            return json_encode($json);
        }
        else
        {
          $json = [];
          return json_encode($json);
        }
       
        //return json_encode(array("Success"=>"True","Data"=>$result) );
    }
    public function creditnote_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $result = customer_creditnote::where('creditnote_no',$request->creditnoteno)
           // ->where('customer_id',$request->customer_id)
            ->where('company_id',Auth::user()->company_id)            
            ->get();
       


      return json_encode(array("Success"=>"True","Data"=>$result));
    }
    public function search_pricedetail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $result = price_master::where('price_master_id',$request->price_id)
            ->where('company_id',Auth::user()->company_id)            
            ->get();
       


      return json_encode(array("Success"=>"True","Data"=>$result));
    }
    public function gstrange_detail(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = gst_slabs_master::where('selling_price_from','<=',$request->sellingprice)
            ->where('selling_price_to','>=',$request->sellingprice)
            //->where('company_id',Auth::user()->company_id)
            ->get();
       
        if(sizeof($result) != 0) 
        {
          return json_encode(array("Success"=>"True","Data"=>$result));
        }
        else
        {
           return json_encode(array("Success"=>"False","Message"=>"GST Range has not been Specified for this Product."));
        }
            
      
        }
   
    public function customer_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
       $result = customer::select('customer_name','customer_mobile','customer_id')
            ->where('customer_name', 'LIKE', "%$request->search_val%")
            ->where('company_id',Auth::user()->company_id)
            ->orWhere('customer_mobile', 'LIKE', "%$request->search_val%")
            ->get();
       

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function customer_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = customer::where('customer_id',$request->customer_id)
            ->where('company_id',Auth::user()->company_id)
            ->with('customer_address_detail')
            ->withCount([
                    'customer_creditaccount as totalcuscreditbalance' => function($fquery) {
                        $fquery->select(DB::raw('SUM(balance_amount)'));
                    }
                ])
            ->get();

        

      return json_encode(array("Success"=>"True","Data"=>$result));
    }
    public function product_popup_values(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $ppvalues = product::where('product_id',$request->productid)
            ->where('company_id',Auth::user()->company_id)
            ->with('product_image')
            ->get();
        //print_r($ppvalues);
        

      return view('sales::product_popup',compact('ppvalues'));
    }

public function billing_create(Request $request)
{
    Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
    $data = $request->all();
 //////////*************************Save Normal and Franchise Bills*********************************************
    if($data[1]['sales_type']!=3)
     { 
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;


        $created_by = $userId;

       
        $cstate_id = company_profile::select('state_id','billtype','bill_number_prefix','tax_type','billprint_type','series_type','bill_number_prefix')->where('company_id',Auth::user()->company_id)->get(); 
                
               
         if($data[1]['customer_id'] == '')
         {
              $state_id   =    $cstate_id[0]['state_id'];
         }
         else{

               if($data[1]['duedays']!='' && $data[1]['duedays']!=0)
               {
                    customer::where('customer_id',$data[1]['customer_id'])->update(array(
                    'modified_by' => Auth::User()->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'outstanding_duedays'=>$data[1]['duedays']
                  ));
               }
               $custate   =   customer_address_detail::select('state_id')
                ->where('company_id',Auth::user()->company_id)
                ->where('customer_id','=',$data[1]['customer_id'])
                ->get(); 

                if($custate[0]['state_id'] == '' || $custate[0]['state_id'] == null)
                {
                    
                     $state_id   =    $cstate_id[0]['state_id'];
                }
                else
                {
                     $state_id   =    $custate[0]['state_id'];
                }
               
         }      
       
        if($data[1]['refname'] != '')
         {

              $result = reference::select('reference_id','reference_name')
                ->where('reference_name','=',$data[1]['refname'])
                ->where('company_id',Auth::user()->company_id)->first();

                if($result=='')
                {
                     $refss = reference::updateOrCreate(
                        ['reference_id' => '', 'company_id'=>$company_id,],
                        ['reference_name'=>$data[1]['refname'],
                            'created_by' =>$created_by,
                            'is_active' => "1"
                        ]
                      );

                     $refid   =  $refss->reference_id;
                }
                else
                {
                    $refid   =  $result['reference_id'];
                    
                }

                 
         }
         else
         {
              $refid   =  NULL;
         }

        
         $invoice_date            =     $data[1]['invoice_date'];
         $selling_after_discount  =     $data[1]['totalwithout_gst'] - $data[1]['roomwisediscount_amount'];
         $roundoff    =    round($data[1]['ggrand_total']) - $data[1]['ggrand_total'];

         //$state_id = customer_address_detail::select('state_id')->where('company_id',Auth::user()->company_id)->where('customer_id','=',$data[1]['customer_id'])->first();

          sales_bill::where('sales_bill_id',$data[1]['sales_bill_id'])->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
          ));

     try {
            DB::beginTransaction();    

          if($cstate_id[0]['tax_type'] == 1)
              {
                  $totalcgst      =     0;
                  $totalsgst      =     0;
              }
              else
              {
                  $totalcgst      =     $data[1]['total_cgst'];
                  $totalsgst      =     $data[1]['total_sgst'];
              } 

        $sales = sales_bill::updateOrCreate(
            ['sales_bill_id' => $data[1]['sales_bill_id'], 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
            'sales_type'=>$data[1]['sales_type'],
            'consign_bill_id'=>$data[1]['consign_bill_id'],
            'bill_no'=>$data[1]['invoice_no'],
                'bill_date'=>$invoice_date,
                'state_id'=>$state_id,
                'reference_id'=>$refid,
                'total_qty'=>$data[1]['overallqty'],
                'sellingprice_before_discount'=>$data[1]['totalwithout_gst'],
                'discount_percent'=>$data[1]['discount_percent'],
                'discount_amount'=>$data[1]['discount_amount'],
                'productwise_discounttotal'=>$data[1]['roomwisediscount_amount'],
                'sellingprice_after_discount'=>$selling_after_discount,
                'totalbillamount_before_discount'=>$data[1]['sales_total'],
                'total_igst_amount'=>$data[1]['total_igst'],
                'total_cgst_amount'=>$totalcgst,
                'total_sgst_amount'=>$totalsgst,
                'gross_total'=>$data[1]['grand_total'],
                'shipping_charges'=>$data[1]['charges_total'],
                'round_off'=>$roundoff,
                'total_bill_amount'=>$data[1]['ggrand_total'],
                'official_note'=>$data[1]['official_note'],
                'print_note'=>$data[1]['print_note'],
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );

      $sales_bill_id = $sales->sales_bill_id;
        
///********************Franchise Bill detail in stock transfer detail table**********************************
      if($data[1]['sales_type']==2)
      {
        $stock_transfer = stock_transfer::updateOrCreate(
            ['stock_transfer_id' => $data[1]['stock_transfer_id'], 'company_id'=>$company_id,],
            ['sales_bill_id'=>$sales_bill_id,
            'stock_transfer_no'=>$data[1]['invoice_no'],
            'store_id'=>$data[1]['store_id'],
                'stock_transfer_date'=>$invoice_date,
                'total_mrp'=>0,
                'total_qty'=>0,
                'total_gst'=>0,
                'total_sellprice'=>0,
                'total_offerprice'=>0,
                'total_cost_igst_amount'=>0,
                'total_cost_cgst_amount'=>0,
                'total_cost_sgst_amount'=>0,
                'store_type'=>1,
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );

      }

      

       
       $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
       $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

        if($data[1]['sales_type']==2)
        {
          $stock_transfer_id   =       $stock_transfer->stock_transfer_id;
          $stock_transfer_no   =       $stock_transfer_id.'/'.$f1.'-'.$f2;
        }

       

  //////////////////////////////////// To make Bill series Month Wise and Year wise as per the value selected from Company Profile......

        $todate       =    date('Y-m-d');
        
        $newyear      =   date('Y-04-01');
        
        $newmonth     =   date('Y-m-01');

//////////////////For Bill series Year Wise 

        if ($cstate_id[0]['series_type'] == 1) {

                    $nseries = sales_bill::select('bill_series')
                                        ->where('sales_bill_id', '<', $sales_bill_id)
                                        ->where('company_id', Auth::user()->company_id)
                                        ->orderBy('sales_bill_id', 'DESC')
                                        ->take('1');

                    if($todate >= $newyear){
                       $nseries->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') >= '$newyear'");
                    }

                    $billprefix = '';
                    if ($data[1]['sales_type'] == 1 || $data[1]['sales_type'] == 2) {
                        $nseries->whereIn('sales_type', array(1,2));
                        $billprefix = $cstate_id[0]['bill_number_prefix'];
                    }
                    if ($data[1]['sales_type'] == 4) {
                        $nseries->where('sales_type', 4);
                        $billprefix = $cstate_id[0]['b2b_number_prefix'];
                    }


                    $newseries = $nseries->first();

                    if ($newseries != '') {
                        $billseries = $newseries['bill_series'] + 1;
                    } else {
                        $billseries = 1;
                    }

                    $finalinvoiceno = $billprefix . $billseries . '/' . $f1 . '-' . $f2;
                } 

 //////////////////For Bill series Month Wise        
        else
        {
            if($todate>=$newmonth)
              {

                  $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') >= '$newmonth'")
                                            ->where('sales_bill_id','<',$sales_bill_id)
                                            ->where('company_id',Auth::user()->company_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                       
               
                  if($newseries=='')
                  {
                      $billseries  =  1;
                  }
                  else
                  {
                      $billseries   = $newseries['bill_series']+1;
                      
                  }
                 
               
              }
              else
              {
                $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') <= '$todate'")
                                            ->where('sales_bill_id','<',$sales_bill_id)
                                            ->where('company_id',Auth::user()->company_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                      $billseries   = $newseries['bill_series']+1;

                
              }

             
              $co     =     strlen($billseries);  
    
              if($co<=2)
              $id1  = '00'.$billseries; 
              elseif($co<=3)
              $id1  = '0'.$billseries;      
              elseif($co<=4)
              $id1  = $billseries;
              $dd   = date('my');
              
              $finalinvoiceno = $cstate_id[0]['bill_number_prefix'].$dd.''.$id1;
        }   
              

        

        if($data[1]['sales_bill_id']=='' || $data[1]['sales_bill_id']==null)
        {  

         sales_bill::where('sales_bill_id',$sales_bill_id)->update(array(
            'bill_no' => $finalinvoiceno,
            'bill_series' => $billseries
         ));
       }
    

       sales_product_detail::where('sales_bill_id',$sales_bill_id)->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));

    
        $productdetail        =    array();
        $stocktransferdetail  =    array();
        $ctotal_mrp   = 0;
        $ctotal_gst   = 0;
        $ctotal_sellprice = 0;
        $ctotal_offerprice = 0;
        $ctotal_cost_igst_amount = 0;
        $ctotal_cost_cgst_amount = 0;
        $ctotal_cost_sgst_amount = 0;

       

         foreach($data[0] AS $billkey=>$billvalue)
          {
              $inwardids    =  '';
              $inwardqtys   =  '';
               if($billvalue['barcodesel']!='')
              {

                  if($cstate_id[0]['tax_type'] == 1)
                  {
                      $halfgstper      =     0;
                      $halfgstamt      =     0;
                  }
                  else
                  {
                      $halfgstper      =     $billvalue['prodgstper']/2;
                      $halfgstamt      =     $billvalue['prodgstamt']/2;
                  }
                     
                      // $productdetail['bill_date']                            =    $invoice_date;
                      // $productdetail['product_system_barcode']               =    $billvalue['barcodesel'];

                      $productdetail['consign_products_detail_id']           =    $billvalue['consign_products_id'];
                      $productdetail['product_id']                           =    $billvalue['productid'];
                      $productdetail['price_master_id']                      =    $billvalue['price_master_id'];
                      $productdetail['qty']                                  =    $billvalue['qty'];
                      $productdetail['mrp']                                  =    $billvalue['mrp'];
                      $productdetail['sellingprice_before_discount']         =    $billvalue['sellingprice_before_discount'];
                      $productdetail['discount_percent']                     =    $billvalue['discount_percent'];
                      $productdetail['discount_amount']                      =    $billvalue['discount_amount'];
                      $productdetail['mrpdiscount_amount']                   =    $billvalue['mrpdiscount_amount'];
                      $productdetail['sellingprice_after_discount']          =    $billvalue['totalsellingwgst'];
                      $productdetail['overalldiscount_percent']              =    $billvalue['overalldiscount_percent'];
                      $productdetail['overalldiscount_amount']               =    $billvalue['overalldiscount_amount'];
                      $productdetail['overallmrpdiscount_amount']            =    $billvalue['overallmrpdiscount_amount'];
                      $productdetail['sellingprice_afteroverall_discount']   =    $billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount'];
                      $productdetail['cgst_percent']                         =    $halfgstper;
                      $productdetail['cgst_amount']                          =    $halfgstamt;
                      $productdetail['sgst_percent']                         =    $halfgstper;
                      $productdetail['sgst_amount']                          =    $halfgstamt;
                      $productdetail['igst_percent']                         =    $billvalue['prodgstper'];
                      $productdetail['igst_amount']                          =    $billvalue['prodgstamt'];
                      $productdetail['total_amount']                         =    $billvalue['totalamount'];
                      $productdetail['product_type']                         =     1;
                      $productdetail['created_by']                           =     Auth::User()->user_id;




          if($billvalue['consign_products_id'] =='')
          {

                if($billvalue['oldprice_master_id'] != ''){

                   price_master::where('price_master_id',$billvalue['oldprice_master_id'])->update(array(
                    'modified_by' => Auth::User()->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'product_qty' => DB::raw('product_qty + '.$billvalue['oldqty'])
                    ));
                 
                  }

                   price_master::where('price_master_id',$billvalue['price_master_id'])->update(array(
                  'modified_by' => Auth::User()->user_id,
                  'updated_at' => date('Y-m-d H:i:s'),
                  'product_qty' => DB::raw('product_qty - '.$billvalue['qty'])
                  ));

///////////////////First In First Out Logic//////////////////////////////////////////////////////////////

                    $oldinwardids       =     explode(',',substr($billvalue['inwardids'],0,-1));
                    $oldinwardqtys      =     explode(',',substr($billvalue['inwardqtys'],0,-1));

                
                       $ccount    =   0;  
                       $icount    =   0;
                       $pcount    =   0;
                       $done      =   0;
                       $firstout  =   0;
                       $restqty   =   $billvalue['qty'];


                         

              if($billvalue['price_master_id']!=$billvalue['oldprice_master_id'] || $billvalue['qty']!=$billvalue['oldqty'])  
               {    

                   if($billvalue['sales_product_id'] !='')
                       { 
                            foreach($oldinwardids as $l=>$val)
                            {
                                inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$oldinwardids[$l])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty + '.$oldinwardqtys[$l])
                                              ));

                            }  
                       }   
                       
                  if($billvalue['qty']>0)
                  {
                      $prodtype    =        product::select('product_type')
                                            //->where('company_id',Auth::user()->company_id)
                                            ->where('product_id',$billvalue['productid'])->get();

                       $prid      =         price_master::select('offer_price','batch_no')
                                            ->where('company_id',Auth::user()->company_id)
                                            ->where('price_master_id',$billvalue['price_master_id'])->get();

                       $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                                            ->where('product_id',$billvalue['productid'])
                                            ->where('company_id',Auth::user()->company_id)
                                            ->where('pending_return_qty','>',0);

                                            
                     if($cstate_id[0]['billtype']==3)
                      {
                            $qquery->where('batch_no',$prid[0]['batch_no']);
                      }
                      if($prodtype[0]['product_type']==1)
                      {
                            $qquery->where('offer_price',$prid[0]['offer_price']);
                      }

                      
                      $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                      
                      if(sizeof($inwarddetail)==0)
                      {
                        
                                
                                return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details against Barcode No. ".$billvalue['barcodesel']." "));
                                exit;
                           
                      }
                            
                   

                       foreach($inwarddetail as $inwarddata)
                       {
                          //echo $inwarddata['pending_return_qty'];
                            if($inwarddata['pending_return_qty'] >= $restqty && $firstout==0)
                            {  
                                  if($done == 0)
                                  {

                                    //echo 'hello';

                                          $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                          $inwardqtys   .=   $restqty.',';
                                      
                                          inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['qty'])
                                              ));
                                          $pcount++;
                                          $done++;
                                 }
                           }
                           else
                           {
                              if($pcount==0 && $done == 0 && $icount==0)
                              {
                                  
                                 
                                  if($restqty  > $inwarddata['pending_return_qty'])
                                  {
                                    //echo 'bbb';
                                    //echo $restqty;
                                      $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                      $inwardqtys   .=   $inwarddata['pending_return_qty'].',';
                                      $ccount       =   $restqty  - $inwarddata['pending_return_qty'];
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
                                    //echo 'ccc';
                                    //echo $restqty;
                                      $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                      $inwardqtys   .=   $restqty.',';
                                      $ccount   =   $restqty  - $inwarddata['pending_return_qty'];
                                      inward_product_detail::where('company_id',Auth::user()->company_id)
                                      ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                      ->update(array(
                                          'modified_by' => Auth::User()->user_id,
                                          'updated_at' => date('Y-m-d H:i:s'),
                                          'pending_return_qty' => DB::raw('pending_return_qty - '.$restqty)
                                          ));
                                  }


                                   if($ccount > 0)
                                    {
                                       $firstout++;
                                       // echo $pcount;
                                       // echo $done;
                                       // echo $icount;
                                       $restqty   =   $restqty  - $inwarddata['pending_return_qty'];
                                      // echo $restqty;

                                       
                                    }
                                    if($ccount <= 0)
                                    {
                                      //echo 'no';
                                      $firstout++;
                                       $icount++;
                                         
                                    }
                                   
                              }
                           }

                       }    
                   }        

                }   
                if($inwardids!='')
                {
                  $productdetail['inwardids']                          =    $inwardids;
                  $productdetail['inwardqtys']                         =    $inwardqtys;
                }   
                else
                {  
                  $productdetail['inwardids']                          =    $billvalue['inwardids'];
                  $productdetail['inwardqtys']                         =    $billvalue['inwardqtys'];
                }
                 // echo $inwardids;
                 // echo $inwardqtys;

            }  
            else
            {
                  $productdetail['inwardids']                          =    $billvalue['inwardids'];
                  $productdetail['inwardqtys']                         =    $billvalue['inwardqtys'];


            }  

        ///***************************End of FIFO logic**************************************    

                $billproductdetail = sales_product_detail::updateOrCreate(
                   ['sales_bill_id' => $sales_bill_id,
                    'company_id'=>$company_id,'sales_products_detail_id'=>$billvalue['sales_product_id'],],
                   $productdetail);

                   $sales_products_detail_id = $billproductdetail->sales_products_detail_id;

  ///********************Franchise Bill detail in stock transfer detail table**********************************
            if($data[1]['sales_type']==2)
            {
                  $pricemastervalues    =   price_master::select('product_mrp','offer_price','selling_gst_percent','selling_gst_amount','batch_no')
                                                          ->where('price_master_id',$billvalue['price_master_id'])
                                                          ->first();
                  $costgstamt                                                  =    (($billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount']) * $billvalue['prodgstper'])/100; 
                  $averagecost                                                 =    $billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount'];
                  $profitamt                                                   =    $billvalue['sellingprice_before_discount'] - $averagecost;
                  $profitper                                                   =    ($profitamt/$averagecost) * 100;
                  $stocktransferdetail['sales_products_detail_id']             =    $sales_products_detail_id;
                  $stocktransferdetail['product_id']                           =    $billvalue['productid'];
                  $stocktransferdetail['price_master_id']                      =    $billvalue['price_master_id'];
                  $stocktransferdetail['batch_no']                             =    $pricemastervalues['batch_no'];
                  $stocktransferdetail['base_price']                           =    $averagecost;
                  $stocktransferdetail['base_discount_percent']                =    0;
                  $stocktransferdetail['base_discount_amount']                 =    0;
                  $stocktransferdetail['scheme_discount_percent']              =    0;
                  $stocktransferdetail['scheme_discount_amount']               =    0;
                  $stocktransferdetail['free_discount_percent']                =    0;
                  $stocktransferdetail['free_discount_amount']                 =    0;
                  $stocktransferdetail['cost_rate']                            =    $averagecost;
                  $stocktransferdetail['cost_igst_percent']                    =    $billvalue['prodgstper'];
                  $stocktransferdetail['cost_igst_amount']                     =    $costgstamt;
                  $stocktransferdetail['cost_cgst_percent']                    =    $billvalue['prodgstper']/2;
                  $stocktransferdetail['cost_cgst_amount']                     =    $costgstamt/2;
                  $stocktransferdetail['cost_sgst_percent']                    =    $billvalue['prodgstper']/2;
                  $stocktransferdetail['cost_sgst_amount']                     =    $costgstamt/2;
                  $stocktransferdetail['cost_price']                           =    $averagecost + $costgstamt;
                  $stocktransferdetail['profit_percent']                       =    $profitper;
                  $stocktransferdetail['profit_amount']                        =    $profitamt;
                  $stocktransferdetail['sell_price']                           =    $billvalue['sellingprice_before_discount'];
                  $stocktransferdetail['selling_gst_percent']                  =    $pricemastervalues['selling_gst_percent'];
                  $stocktransferdetail['selling_gst_amount']                   =    $pricemastervalues['selling_gst_amount'];
                  $stocktransferdetail['offer_price']                          =    $pricemastervalues['offer_price'];
                  $stocktransferdetail['product_mrp']                          =    $pricemastervalues['product_mrp'];
                  $stocktransferdetail['product_qty']                          =    $billvalue['qty'];
                  if($inwardids!='')
                  {
                    $stocktransferdetail['inward_product_detail_id']           =    $inwardids;
                    $stocktransferdetail['inward_product_qtys']                =    $inwardqtys;
                  }   
                  else
                  {  
                    $stocktransferdetail['inward_product_detail_id']           =    $billvalue['inwardids'];
                    $stocktransferdetail['inward_product_qtys']                =    $billvalue['inwardqtys'];
                  }
                  $stocktransferdetail['free_qty']                             =    0;
                  $stocktransferdetail['pending_rcv_qty']                      =    $billvalue['qty'];
                  $stocktransferdetail['total_cost_rate_with_qty']             =    $averagecost  * $billvalue['qty'];
                  $stocktransferdetail['total_igst_amount_with_qty']           =    $costgstamt  * $billvalue['qty'];
                  $stocktransferdetail['total_cgst_amount_with_qty']           =    ($costgstamt/2)  * $billvalue['qty'];
                  $stocktransferdetail['total_sgst_amount_with_qty']           =    ($costgstamt/2)   * $billvalue['qty'];
                  $stocktransferdetail['total_offer_price']                    =    $billvalue['mrp']  * $billvalue['qty'];
                  $stocktransferdetail['total_cost']                           =    ($averagecost + $costgstamt)  * $billvalue['qty'];
                  $stocktransferdetail['is_active']                            =     1;
                  $stocktransferdetail['created_by']                           =     Auth::User()->user_id;

                  $stockdetail = stock_transfer_detail::updateOrCreate(
                   ['stock_transfer_id' => $stock_transfer_id,
                    'company_id'=>$company_id,'stock_transfers_detail_id'=>$billvalue['stock_transfer_detail_id'],],
                   $stocktransferdetail);

                    $ctotal_mrp   += $billvalue['mrp']  * $billvalue['qty'];
                    $ctotal_gst   += $pricemastervalues['selling_gst_amount'] * $billvalue['qty'];
                    $ctotal_sellprice += $billvalue['sellingprice_before_discount'] * $billvalue['qty'];
                    $ctotal_offerprice += $pricemastervalues['offer_price'] * $billvalue['qty'];
                    $ctotal_cost_igst_amount += $costgstamt  * $billvalue['qty'];
                    $ctotal_cost_cgst_amount = ($costgstamt/2)  * $billvalue['qty'];



            }    
              
      }
     
     
            
     }
///********************Franchise Bill detail in stock transfer detail table**********************************
     if($data[1]['sales_type']==2)
     {
        stock_transfer::where('stock_transfer_id',$stock_transfer_id)->update(array(
            'stock_transfer_no' => $stock_transfer_no,
            'total_mrp' => $ctotal_mrp,
            'total_qty' => $data[1]['overallqty'],
            'total_gst' => $ctotal_gst,
            'total_sellprice' => $ctotal_sellprice,
            'total_offerprice' => $ctotal_offerprice,
            'total_cost_igst_amount' => $ctotal_cost_igst_amount,
            'total_cost_cgst_amount' => $ctotal_cost_cgst_amount,
            'total_cost_sgst_amount' => $ctotal_cost_cgst_amount

         ));
     }

          $chargesdetail     =    array();

       

         foreach($data[3] AS $chargeskey=>$chargesvalue)
          {

               if($chargesvalue['chargesamt']!='')
              {


                      $halfgstper      =     $chargesvalue['csprodgstper']/2;
                      $halfgstamt      =     $chargesvalue['csprodgstamt']/2;
                      // $chargesdetail['bill_date']                            =    $invoice_date;
                      $chargesdetail['product_id']                           =    $chargesvalue['cproductid'];
                      $chargesdetail['qty']                                  =    $chargesvalue['cqty'];
                      $chargesdetail['mrp']                                  =    $chargesvalue['chargesamt'];
                      $chargesdetail['sellingprice_before_discount']         =    $chargesvalue['chargesamt'];
                      $chargesdetail['sellingprice_after_discount']          =    $chargesvalue['chargesamt'];
                      $chargesdetail['sellingprice_afteroverall_discount']   =    $chargesvalue['chargesamt'];
                      $chargesdetail['cgst_percent']                         =    $halfgstper;
                      $chargesdetail['cgst_amount']                          =    $halfgstamt;
                      $chargesdetail['sgst_percent']                         =    $halfgstper;
                      $chargesdetail['sgst_amount']                          =    $halfgstamt;
                      $chargesdetail['igst_percent']                         =    $chargesvalue['csprodgstper'];
                      $chargesdetail['igst_amount']                          =    $chargesvalue['csprodgstamt'];
                      $chargesdetail['total_amount']                         =    $chargesvalue['ctotalamount'];
                      $chargesdetail['product_type']                         =     2;
                      $chargesdetail['created_by']                           =     Auth::User()->user_id;

                
               
                   $billchargesdetail = sales_product_detail::updateOrCreate(
                   ['sales_bill_id' => $sales_bill_id,
                    'company_id'=>$company_id,'sales_products_detail_id'=>$chargesvalue['csales_product_id'],],
                   $chargesdetail);

              
      }
     
     
            
    }  

      sales_bill_payment_detail::where('sales_bill_id',$sales_bill_id)->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
            'total_bill_amount'=>0
        ));
    

    $paymentanswers     =    array();

         foreach($data[2] AS $key=>$value2)
          {
             if($value2['id']==3)
              {
                $paymentanswers['bankname']    =   $data[1]['bankname'];
                $paymentanswers['chequeno']    =   $data[1]['chequeno'];
                $paymentanswers['customer_creditnote_id']    =     NULL;
              }
              elseif($value2['id']==7)
              {
                $paymentanswers['bankname']    =     $data[1]['netbankname'];
                $paymentanswers['chequeno']    =     '';
                $paymentanswers['customer_creditnote_id']    =     NULL;
              }
              elseif($value2['id']==6)
              {
                $paymentanswers['bankname']    =     $data[1]['duedate'];
                $paymentanswers['chequeno']    =     '';
                $paymentanswers['customer_creditnote_id']    =     NULL;
              }
              elseif($value2['id']==8)
              {
                $paymentanswers['customer_creditnote_id']    =     $data[1]['creditnoteid'];
                $paymentanswers['bankname']='';
                $paymentanswers['chequeno'] =  '';

              }
              else
              {
                $paymentanswers['bankname']='';
                $paymentanswers['chequeno'] =  '';
                $paymentanswers['customer_creditnote_id']    =     NULL;
              }
           
                
                $paymentanswers['sales_bill_id']                 =  $sales_bill_id;
                $paymentanswers['total_bill_amount']             =  $value2['value'];
                $paymentanswers['payment_method_id']             =  $value2['id'];
                $paymentanswers['created_by']                    =  Auth::User()->user_id;
                $paymentanswers['deleted_at'] =  NULL;
                $paymentanswers['deleted_by'] =  NULL;
                
            
           
       
           $paymentdetail = sales_bill_payment_detail::updateOrCreate(
               ['sales_bill_id' => $sales_bill_id,'sales_bill_payment_detail_id'=>$value2['sales_payment_id'],],
               $paymentanswers);


            
    }

    if($data[1]['creditaccountid']=='')
    {

        customer_creditaccount::where('sales_bill_id',$sales_bill_id)->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
            'credit_amount'=>0,
            'balance_amount'=>0
          ));
   
        foreach($data[2] AS $key=>$value3)
          {
              if($value3['id']==6)
              {
                  if($value3['value'] !='' || $value3['value']!=0)
                  {
                         $sales = customer_creditaccount::updateOrCreate(
                        ['sales_bill_id' => $sales_bill_id, 'company_id'=>$company_id,],
                        ['customer_id'=>$data[1]['customer_id'],
                            'bill_date'=>$invoice_date,
                            'duedate'=>$data[1]['duedate'],
                            'credit_amount'=>$value3['value'],
                            'balance_amount'=>$value3['value'],
                            'created_by' =>$created_by,
                            'deleted_at' =>NULL,
                            'deleted_by' =>NULL,
                            'is_active' => "1"
                            ]
                        );
                    }

                
              }
        
          }

    }
    else
    {
       if($data[1]['creditbalcheck']==0)
       {

            customer_creditaccount::where('sales_bill_id',$sales_bill_id)->update(array(
              'deleted_by' => Auth::User()->user_id,
              'deleted_at' => date('Y-m-d H:i:s'),
              'credit_amount'=>0,
              'balance_amount'=>0
            ));
     
          foreach($data[2] AS $key=>$value3)
            {
                if($value3['id']==6)
                {
                    if($value3['value'] !='' || $value3['value']!=0)
                    {
                           $sales = customer_creditaccount::updateOrCreate(
                          ['sales_bill_id' => $sales_bill_id, 'company_id'=>$company_id,],
                          ['customer_id'=>$data[1]['customer_id'],
                              'bill_date'=>$invoice_date,
                              'duedate'=>$data[1]['duedate'],
                              'credit_amount'=>$value3['value'],
                              'balance_amount'=>$value3['value'],
                              'created_by' =>$created_by,
                              'deleted_at' =>NULL,
                              'deleted_by' =>NULL,
                              'is_active' => "1"
                              ]
                          );
                      }

                  
                }
          
            }
       }
    }

    if($data[1]['editcreditnotepaymentid']!='')
    {

        creditnote_payment::where('creditnote_payment_id',$data[1]['editcreditnotepaymentid'])->update(array(
              'deleted_by' => Auth::User()->user_id,
              'deleted_at' => date('Y-m-d H:i:s'),
              'creditnote_amount'=>0,
              'used_amount'=>0,
              'balance_amount'=>0
            ));

        $updatecreditnoteamount    =  customer_creditnote::select('balance_amount')
                    ->where('customer_creditnote_id',$data[1]['editcreditnoteid'])
                    ->where('company_id',Auth::user()->company_id)
                    ->get();

          $updatecreditamount   =    $updatecreditnoteamount[0]['balance_amount'] + $data[1]['editcreditnoteamount'];

         customer_creditnote::where('customer_creditnote_id',$data[1]['editcreditnoteid'])->update(array(
                      'balance_amount' => $updatecreditamount
                  ));   
    }
    

    if($data[1]['creditnoteid']!='')
    {

        $creditbalanceamt   =    $data[1]['creditnoteamount'] - $data[1]['issueamount'];
        $creditnotepayment = creditnote_payment::updateOrCreate(
            ['sales_bill_id' => $sales_bill_id, 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
                'customer_creditnote_id'=>$data[1]['creditnoteid'],
                'creditnote_amount'=>$data[1]['creditnoteamount'],
                'used_amount'=>$data[1]['issueamount'],
                'balance_amount'=>$creditbalanceamt,
                'created_by' =>$created_by,
                'is_active' => "1",
                'deleted_at' =>NULL,
                'deleted_by' =>NULL,
            ]
        );

          customer_creditnote::where('customer_creditnote_id',$data[1]['creditnoteid'])->update(array(
                      'balance_amount' => $creditbalanceamt
                  ));    

    }

     DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }
        if($billproductdetail)
        {
            
            if($data[1]['sales_type']==1)
            {
               if($data[1]['sales_bill_id'] != '')
              {   
                  return json_encode(array("Success"=>"True","Message"=>"Billing successfully Update!","url"=>"view_bill"));
              }
              else
              {
                  return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>"sales_bill"));
              }
          }
          else
          {
            return json_encode(array("Success"=>"True","Message"=>"Billing successfully Update!","url"=>"franchise_bill"));
          }

               

           
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }

   }
   //**********************************************************Save Store Stock transfer Data**************************************************************************************************************************************************************************************************************************////
   else
   {
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;


        $created_by = $userId;

       
        $cstate_id = company_profile::select('state_id','billtype','bill_number_prefix','tax_type','billprint_type','series_type','bill_number_prefix')->where('company_id',Auth::user()->company_id)->get(); 
                
       
        
         $invoice_date            =     $data[1]['invoice_date'];
         $selling_after_discount  =     $data[1]['totalwithout_gst'] - $data[1]['roomwisediscount_amount'];
         $roundoff                =    round($data[1]['ggrand_total']) - $data[1]['ggrand_total'];

        
     try {
            DB::beginTransaction();    

        
        
///********************Franchise Bill detail in stock transfer detail table**********************************
    
        $stock_transfer = stock_transfer::updateOrCreate(
            ['stock_transfer_id' => $data[1]['stock_transfer_id'], 'company_id'=>$company_id,],
            ['stock_transfer_no'=>$data[1]['invoice_no'],
            'store_id'=>$data[1]['store_id'],
                'stock_transfer_date'=>$invoice_date,
                'total_mrp'=>0,
                'total_qty'=>0,
                'total_gst'=>0,
                'total_sellprice'=>0,
                'total_offerprice'=>0,
                'total_cost_igst_amount'=>0,
                'total_cost_cgst_amount'=>0,
                'total_cost_sgst_amount'=>0,
                'store_type'=>1,
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );


       
       $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
       $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

       
          $stock_transfer_id   =  $stock_transfer->stock_transfer_id;
          $stock_transfer_no          =       $stock_transfer_id.'/'.$f1.'-'.$f2;
       
  
        $stocktransferdetail  =    array();
        $ctotal_mrp   = 0;
        $ctotal_gst   = 0;
        $ctotal_sellprice = 0;
        $ctotal_offerprice = 0;
        $ctotal_cost_igst_amount = 0;
        $ctotal_cost_cgst_amount = 0;
        $ctotal_cost_sgst_amount = 0;

       

         foreach($data[0] AS $billkey=>$billvalue)
          {
              $inwardids    =  '';
              $inwardqtys   =  '';
               if($billvalue['barcodesel']!='')
              {

                  if($cstate_id[0]['tax_type'] == 1)
                  {
                      $halfgstper      =     0;
                      $halfgstamt      =     0;
                  }
                  else
                  {
                      $halfgstper      =     $billvalue['prodgstper']/2;
                      $halfgstamt      =     $billvalue['prodgstamt']/2;
                  }
                  $pricemastervalues    =   price_master::select('product_mrp','offer_price','selling_gst_percent','selling_gst_amount','batch_no')
                                                          ->where('price_master_id',$billvalue['price_master_id'])
                                                          ->first();
                  // $costgstamt                                                  =    (($billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount']) * $billvalue['prodgstper'])/100; 
                  // $averagecost                                                 =    $billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount'];
                  
               
                  $stocktransferdetail['product_id']                           =    $billvalue['productid'];
                  $stocktransferdetail['price_master_id']                      =    $billvalue['price_master_id'];
                  $stocktransferdetail['batch_no']                             =    $pricemastervalues['batch_no'];
                  
                  $stocktransferdetail['base_discount_percent']                =    0;
                  $stocktransferdetail['base_discount_amount']                 =    0;
                  $stocktransferdetail['scheme_discount_percent']              =    0;
                  $stocktransferdetail['scheme_discount_amount']               =    0;
                  $stocktransferdetail['free_discount_percent']                =    0;
                  $stocktransferdetail['free_discount_amount']                 =    0;
                  
                  $stocktransferdetail['cost_igst_percent']                    =    0;
                  $stocktransferdetail['cost_igst_amount']                     =    0;
                  $stocktransferdetail['cost_cgst_percent']                    =    0;
                  $stocktransferdetail['cost_cgst_amount']                     =    0;
                  $stocktransferdetail['cost_sgst_percent']                    =    0;
                  $stocktransferdetail['cost_sgst_amount']                     =    0;
                  
                  $stocktransferdetail['sell_price']                           =    $billvalue['sellingprice_before_discount'];
                  $stocktransferdetail['selling_gst_percent']                  =    $pricemastervalues['selling_gst_percent'];
                  $stocktransferdetail['selling_gst_amount']                   =    $pricemastervalues['selling_gst_amount'];
                  $stocktransferdetail['offer_price']                          =    $pricemastervalues['offer_price'];
                  $stocktransferdetail['product_mrp']                          =    $pricemastervalues['product_mrp'];
                  $stocktransferdetail['product_qty']                          =    $billvalue['qty'];
                  
                  $stocktransferdetail['free_qty']                             =    0;
                  $stocktransferdetail['pending_rcv_qty']                      =    $billvalue['qty'];
                  
                  $stocktransferdetail['total_offer_price']                    =    $billvalue['mrp']  * $billvalue['qty'];
                  $stocktransferdetail['is_active']                            =     1;
                  $stocktransferdetail['created_by']                           =     Auth::User()->user_id;


                if($billvalue['oldprice_master_id'] != ''){

                   price_master::where('price_master_id',$billvalue['oldprice_master_id'])->update(array(
                    'modified_by' => Auth::User()->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'product_qty' => DB::raw('product_qty + '.$billvalue['oldqty'])
                    ));
                 
                  }

                   price_master::where('price_master_id',$billvalue['price_master_id'])->update(array(
                  'modified_by' => Auth::User()->user_id,
                  'updated_at' => date('Y-m-d H:i:s'),
                  'product_qty' => DB::raw('product_qty - '.$billvalue['qty'])
                  ));

///////////////////First In First Out Logic//////////////////////////////////////////////////////////////

                    $oldinwardids       =     explode(',',substr($billvalue['inwardids'],0,-1));
                    $oldinwardqtys      =     explode(',',substr($billvalue['inwardqtys'],0,-1));

                
                       $ccount    =   0;  
                       $icount    =   0;
                       $pcount    =   0;
                       $done      =   0;
                       $firstout  =   0;
                       $restqty   =   $billvalue['qty'];


            if($data[1]['inward_stock_id']!='')
            {
                if($billvalue['inwardids']!='')
                {

                   inward_product_detail::where('company_id',Auth::user()->company_id)
                                            ->where('inward_product_detail_id',$billvalue['inwardids'])
                                            ->update(array(
                                                'modified_by' => Auth::User()->user_id,
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['qty'])
                                                )); 
                    $stocktransferdetail['inward_product_detail_id']           =    $billvalue['inwardids'].',';
                    $stocktransferdetail['inward_product_qtys']                =    $billvalue['qty'].',';
                    $inwardids    .=   $billvalue['inwardids'].',';
                    $inwardqtys   .=   $billvalue['qty'].',';

                }
                else
                {
   ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                  if($billvalue['price_master_id']!=$billvalue['oldprice_master_id'] || $billvalue['qty']!=$billvalue['oldqty'])  
                       {    

                           if($billvalue['sales_product_id'] !='')
                               { 
                                    foreach($oldinwardids as $l=>$val)
                                    {
                                        inward_product_detail::where('company_id',Auth::user()->company_id)
                                                  ->where('inward_product_detail_id',$oldinwardids[$l])
                                                  ->update(array(
                                                      'modified_by' => Auth::User()->user_id,
                                                      'updated_at' => date('Y-m-d H:i:s'),
                                                      'pending_return_qty' => DB::raw('pending_return_qty + '.$oldinwardqtys[$l])
                                                      ));

                                    }  
                               }   
                               
                          if($billvalue['qty']>0)
                          {
                              $prodtype    =        product::select('product_type')
                                                    //->where('company_id',Auth::user()->company_id)
                                                    ->where('product_id',$billvalue['productid'])->get();

                               $prid      =         price_master::select('offer_price','batch_no')
                                                    ->where('company_id',Auth::user()->company_id)
                                                    ->where('price_master_id',$billvalue['price_master_id'])->get();

                               $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                                                    ->where('product_id',$billvalue['productid'])
                                                    ->where('company_id',Auth::user()->company_id)
                                                    ->where('pending_return_qty','>',0);

                                                    
                             if($cstate_id[0]['billtype']==3)
                              {
                                    $qquery->where('batch_no',$prid[0]['batch_no']);
                              }
                              if($prodtype[0]['product_type']==1)
                              {
                                    $qquery->where('offer_price',$prid[0]['offer_price']);
                              }

                              
                              $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                              
                              if(sizeof($inwarddetail)==0)
                              {
                                
                                        
                                       return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details against Barcode No. ".$billvalue['barcodesel']." "));
                                    exit;
                                   
                              }
                                    
                           

                               foreach($inwarddetail as $inwarddata)
                               {
                                  //echo $inwarddata['pending_return_qty'];
                                    if($inwarddata['pending_return_qty'] >= $restqty && $firstout==0)
                                    {  
                                          if($done == 0)
                                          {

                                            //echo 'hello';

                                                  $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                                  $inwardqtys   .=   $restqty.',';
                                              
                                                  inward_product_detail::where('company_id',Auth::user()->company_id)
                                                  ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                                  ->update(array(
                                                      'modified_by' => Auth::User()->user_id,
                                                      'updated_at' => date('Y-m-d H:i:s'),
                                                      'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['qty'])
                                                      ));
                                                  $pcount++;
                                                  $done++;
                                         }
                                   }
                                   else
                                   {
                                      if($pcount==0 && $done == 0 && $icount==0)
                                      {
                                          
                                         
                                          if($restqty  > $inwarddata['pending_return_qty'])
                                          {
                                            //echo 'bbb';
                                            //echo $restqty;
                                              $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                              $inwardqtys   .=   $inwarddata['pending_return_qty'].',';
                                              $ccount       =   $restqty  - $inwarddata['pending_return_qty'];
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
                                            //echo 'ccc';
                                            //echo $restqty;
                                              $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                              $inwardqtys   .=   $restqty.',';
                                              $ccount   =   $restqty  - $inwarddata['pending_return_qty'];
                                              inward_product_detail::where('company_id',Auth::user()->company_id)
                                              ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                              ->update(array(
                                                  'modified_by' => Auth::User()->user_id,
                                                  'updated_at' => date('Y-m-d H:i:s'),
                                                  'pending_return_qty' => DB::raw('pending_return_qty - '.$restqty)
                                                  ));
                                          }


                                           if($ccount > 0)
                                            {
                                               $firstout++;
                                               // echo $pcount;
                                               // echo $done;
                                               // echo $icount;
                                               $restqty   =   $restqty  - $inwarddata['pending_return_qty'];
                                              // echo $restqty;

                                               
                                            }
                                            if($ccount <= 0)
                                            {
                                              //echo 'no';
                                              $firstout++;
                                               $icount++;
                                                 
                                            }
                                           
                                      }
                                   }

                               }    
                           }        

                        }   
                       if($inwardids!='')
                        {
                          $stocktransferdetail['inward_product_detail_id']           =    $inwardids;
                          $stocktransferdetail['inward_product_qtys']                =    $inwardqtys;
                        }   
                        else
                        {  
                          $stocktransferdetail['inward_product_detail_id']           =    $billvalue['inwardids'];
                          $stocktransferdetail['inward_product_qtys']                =    $billvalue['inwardqtys'];
                          $inwardids                                                 =    $billvalue['inwardids'];
                          $inwardqtys                                                =    $billvalue['inwardqtys'];

                        }

        ///////////////////////////////////////////////////////
                }


            } 
            else
            {



              if($billvalue['price_master_id']!=$billvalue['oldprice_master_id'] || $billvalue['qty']!=$billvalue['oldqty'])  
               {    

                   if($billvalue['sales_product_id'] !='')
                       { 
                            foreach($oldinwardids as $l=>$val)
                            {
                                inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$oldinwardids[$l])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty + '.$oldinwardqtys[$l])
                                              ));

                            }  
                       }   
                       
                  if($billvalue['qty']>0)
                  {
                      $prodtype    =        product::select('product_type')
                                            //->where('company_id',Auth::user()->company_id)
                                            ->where('product_id',$billvalue['productid'])->get();

                       $prid      =         price_master::select('offer_price','batch_no')
                                            ->where('company_id',Auth::user()->company_id)
                                            ->where('price_master_id',$billvalue['price_master_id'])->get();

                       $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                                            ->where('product_id',$billvalue['productid'])
                                            ->where('company_id',Auth::user()->company_id)
                                            ->where('pending_return_qty','>',0);

                                            
                     if($cstate_id[0]['billtype']==3)
                      {
                            $qquery->where('batch_no',$prid[0]['batch_no']);
                      }
                      if($prodtype[0]['product_type']==1)
                      {
                            $qquery->where('offer_price',$prid[0]['offer_price']);
                      }

                      
                      $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                      
                      if(sizeof($inwarddetail)==0)
                      {
                        
                                
                                return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details against Barcode No. ".$billvalue['barcodesel']." "));
                                exit;
                           
                      }
                            
                   

                       foreach($inwarddetail as $inwarddata)
                       {
                          //echo $inwarddata['pending_return_qty'];
                            if($inwarddata['pending_return_qty'] >= $restqty && $firstout==0)
                            {  
                                  if($done == 0)
                                  {

                                    //echo 'hello';

                                          $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                          $inwardqtys   .=   $restqty.',';
                                      
                                          inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['qty'])
                                              ));
                                          $pcount++;
                                          $done++;
                                 }
                           }
                           else
                           {
                              if($pcount==0 && $done == 0 && $icount==0)
                              {
                                  
                                 
                                  if($restqty  > $inwarddata['pending_return_qty'])
                                  {
                                    //echo 'bbb';
                                    //echo $restqty;
                                      $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                      $inwardqtys   .=   $inwarddata['pending_return_qty'].',';
                                      $ccount       =   $restqty  - $inwarddata['pending_return_qty'];
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
                                    //echo 'ccc';
                                    //echo $restqty;
                                      $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                      $inwardqtys   .=   $restqty.',';
                                      $ccount   =   $restqty  - $inwarddata['pending_return_qty'];
                                      inward_product_detail::where('company_id',Auth::user()->company_id)
                                      ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                      ->update(array(
                                          'modified_by' => Auth::User()->user_id,
                                          'updated_at' => date('Y-m-d H:i:s'),
                                          'pending_return_qty' => DB::raw('pending_return_qty - '.$restqty)
                                          ));
                                  }


                                   if($ccount > 0)
                                    {
                                       $firstout++;
                                       // echo $pcount;
                                       // echo $done;
                                       // echo $icount;
                                       $restqty   =   $restqty  - $inwarddata['pending_return_qty'];
                                      // echo $restqty;

                                       
                                    }
                                    if($ccount <= 0)
                                    {
                                      //echo 'no';
                                      $firstout++;
                                       $icount++;
                                         
                                    }
                                   
                              }
                           }

                       }    
                   }        

                }   
               if($inwardids!='')
                {
                  $stocktransferdetail['inward_product_detail_id']           =    $inwardids;
                  $stocktransferdetail['inward_product_qtys']                =    $inwardqtys;
                }   
                else
                {  
                  $stocktransferdetail['inward_product_detail_id']           =    $billvalue['inwardids'];
                  $stocktransferdetail['inward_product_qtys']                =    $billvalue['inwardqtys'];
                  $inwardids                                                 =    $billvalue['inwardids'];
                  $inwardqtys                                                =    $billvalue['inwardqtys'];

                }
            }
                
////////************************to Calculate Average Cost Price*******************************************
                  $total_price  = 0;
                  if($inwardids !='' || $inwardids !=null)
                  {
                      $cinward_product_detail_id  = explode(',' ,substr($inwardids,0,-1));
                      $cinward_product_qtys = explode(',' ,substr($inwardqtys,0,-1));


                      foreach($cinward_product_detail_id as $inidkey=>$inids)
                      {
                          $cost_price =  inward_product_detail::select('cost_rate')->find($inids);
                          $total_price += $cost_price['cost_rate'] * $cinward_product_qtys[$inidkey];
                          //print_r($cinward_product_qtys[$inidkey]);
                          
                      }

                      $averagecost      =   $total_price;
                  }
                  else
                  {
                      $averagecost      =   0;
                  }

                  $stockcostprice        =  $averagecost / $billvalue['qty'];

                 
                  $stocktransferdetail['base_price']                           =    $stockcostprice;
                  $stocktransferdetail['cost_rate']                            =    $stockcostprice;
                  $stocktransferdetail['cost_price']                           =    $stockcostprice;
                  $profitamt                                                   =    $billvalue['sellingprice_before_discount'] - $stockcostprice;
                  if($stockcostprice ==0)
                  {
                    $profitper = 0;
                  }
                  else
                  {
                    $profitper                                                  =    ($profitamt/$stockcostprice) * 100;
                  }
                  $costgst                                                     =    0;
                  $costgstamt                                                  =    0;
                  $stocktransferdetail['profit_percent']                       =    $profitper;
                  $stocktransferdetail['profit_amount']                        =    $profitamt;
                  $stocktransferdetail['total_cost_rate_with_qty']             =    $stockcostprice  * $billvalue['qty'];
                  $stocktransferdetail['total_igst_amount_with_qty']           =    $costgstamt  * $billvalue['qty'];
                  $stocktransferdetail['total_cgst_amount_with_qty']           =    ($costgstamt/2)  * $billvalue['qty'];
                  $stocktransferdetail['total_sgst_amount_with_qty']           =    ($costgstamt/2)   * $billvalue['qty'];                  
                  $stocktransferdetail['total_cost']                           =    ($stockcostprice + $costgstamt)  * $billvalue['qty'];
        

        ///***************************End of FIFO logic**************************************    


  ///********************Franchise Bill detail in stock transfer detail table**********************************
          
                  

                  $stockdetail = stock_transfer_detail::updateOrCreate(
                   ['stock_transfer_id' => $stock_transfer_id,
                    'company_id'=>$company_id,'stock_transfers_detail_id'=>$billvalue['stock_transfer_detail_id'],],
                   $stocktransferdetail);

                    $ctotal_mrp   += $billvalue['mrp']  * $billvalue['qty'];
                    $ctotal_gst   += $pricemastervalues['selling_gst_amount'] * $billvalue['qty'];
                    $ctotal_sellprice += $billvalue['sellingprice_before_discount'] * $billvalue['qty'];
                    $ctotal_offerprice += $pricemastervalues['offer_price'] * $billvalue['qty'];
                    $ctotal_cost_igst_amount = 0;
                    $ctotal_cost_cgst_amount = 0;


              
      }
     
     
            
     }

    
///********************Franchise Bill detail in stock transfer detail table**********************************
   
        stock_transfer::where('stock_transfer_id',$stock_transfer_id)->update(array(
            'stock_transfer_no' => $stock_transfer_no,
            'total_mrp' => $ctotal_mrp,
            'total_qty' => $data[1]['overallqty'],
            'total_gst' => $ctotal_gst,
            'total_sellprice' => $ctotal_sellprice,
            'total_offerprice' => $ctotal_offerprice,
            'total_cost_igst_amount' => $ctotal_cost_igst_amount,
            'total_cost_cgst_amount' => $ctotal_cost_cgst_amount,
            'total_cost_sgst_amount' => $ctotal_cost_cgst_amount

         ));

     DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }

        if($stockdetail)
        {
            
           return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully Transferred.","url"=>"stock_transfer"));
           
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
   }


      
        //return back()->withInput();
}

public function billingprint_create(Request $request)
 {

     

   Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
    $data = $request->all();
 //////////*************************Save Normal and Franchise Bills*********************************************
    if($data[1]['sales_type']!=3)
     { 
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;


        $created_by = $userId;

       
        $cstate_id = company_profile::select('state_id','billtype','bill_number_prefix','tax_type','billprint_type','series_type','bill_number_prefix')->where('company_id',Auth::user()->company_id)->get(); 
                
               
         if($data[1]['customer_id'] == '')
         {
              $state_id   =    $cstate_id[0]['state_id'];
         }
         else{

               if($data[1]['duedays']!='' && $data[1]['duedays']!=0)
               {
                    customer::where('customer_id',$data[1]['customer_id'])->update(array(
                    'modified_by' => Auth::User()->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'outstanding_duedays'=>$data[1]['duedays']
                  ));
               }
               $custate   =   customer_address_detail::select('state_id')
                ->where('company_id',Auth::user()->company_id)
                ->where('customer_id','=',$data[1]['customer_id'])
                ->get(); 

                if($custate[0]['state_id'] == '' || $custate[0]['state_id'] == null)
                {
                    
                     $state_id   =    $cstate_id[0]['state_id'];
                }
                else
                {
                     $state_id   =    $custate[0]['state_id'];
                }
               
         }      
       
        if($data[1]['refname'] != '')
         {

              $result = reference::select('reference_id','reference_name')
                ->where('reference_name','=',$data[1]['refname'])
                ->where('company_id',Auth::user()->company_id)->first();

                if($result=='')
                {
                     $refss = reference::updateOrCreate(
                        ['reference_id' => '', 'company_id'=>$company_id,],
                        ['reference_name'=>$data[1]['refname'],
                            'created_by' =>$created_by,
                            'is_active' => "1"
                        ]
                      );

                     $refid   =  $refss->reference_id;
                }
                else
                {
                    $refid   =  $result['reference_id'];
                    
                }

                 
         }
         else
         {
              $refid   =  NULL;
         }

        
         $invoice_date            =     $data[1]['invoice_date'];
         $selling_after_discount  =     $data[1]['totalwithout_gst'] - $data[1]['roomwisediscount_amount'];
         $roundoff    =    round($data[1]['ggrand_total']) - $data[1]['ggrand_total'];

         //$state_id = customer_address_detail::select('state_id')->where('company_id',Auth::user()->company_id)->where('customer_id','=',$data[1]['customer_id'])->first();

          sales_bill::where('sales_bill_id',$data[1]['sales_bill_id'])->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
          ));

     try {
            DB::beginTransaction();    

          if($cstate_id[0]['tax_type'] == 1)
              {
                  $totalcgst      =     0;
                  $totalsgst      =     0;
              }
              else
              {
                  $totalcgst      =     $data[1]['total_cgst'];
                  $totalsgst      =     $data[1]['total_sgst'];
              } 

        $sales = sales_bill::updateOrCreate(
            ['sales_bill_id' => $data[1]['sales_bill_id'], 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
            'sales_type'=>$data[1]['sales_type'],
            'consign_bill_id'=>$data[1]['consign_bill_id'],
            'bill_no'=>$data[1]['invoice_no'],
                'bill_date'=>$invoice_date,
                'state_id'=>$state_id,
                'reference_id'=>$refid,
                'total_qty'=>$data[1]['overallqty'],
                'sellingprice_before_discount'=>$data[1]['totalwithout_gst'],
                'discount_percent'=>$data[1]['discount_percent'],
                'discount_amount'=>$data[1]['discount_amount'],
                'productwise_discounttotal'=>$data[1]['roomwisediscount_amount'],
                'sellingprice_after_discount'=>$selling_after_discount,
                'totalbillamount_before_discount'=>$data[1]['sales_total'],
                'total_igst_amount'=>$data[1]['total_igst'],
                'total_cgst_amount'=>$totalcgst,
                'total_sgst_amount'=>$totalsgst,
                'gross_total'=>$data[1]['grand_total'],
                'shipping_charges'=>$data[1]['charges_total'],
                'round_off'=>$roundoff,
                'total_bill_amount'=>$data[1]['ggrand_total'],
                'official_note'=>$data[1]['official_note'],
                'print_note'=>$data[1]['print_note'],
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );

      $sales_bill_id = $sales->sales_bill_id;
        
///********************Franchise Bill detail in stock transfer detail table**********************************
      if($data[1]['sales_type']==2)
      {
        $stock_transfer = stock_transfer::updateOrCreate(
            ['stock_transfer_id' => $data[1]['stock_transfer_id'], 'company_id'=>$company_id,],
            ['sales_bill_id'=>$sales_bill_id,
            'stock_transfer_no'=>$data[1]['invoice_no'],
            'store_id'=>$data[1]['store_id'],
                'stock_transfer_date'=>$invoice_date,
                'total_mrp'=>0,
                'total_qty'=>0,
                'total_gst'=>0,
                'total_sellprice'=>0,
                'total_offerprice'=>0,
                'total_cost_igst_amount'=>0,
                'total_cost_cgst_amount'=>0,
                'total_cost_sgst_amount'=>0,
                'store_type'=>1,
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );

      }

      

       
       $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
       $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

        if($data[1]['sales_type']==2)
        {
          $stock_transfer_id   =       $stock_transfer->stock_transfer_id;
          $stock_transfer_no   =       $stock_transfer_id.'/'.$f1.'-'.$f2;
        }

       

  //////////////////////////////////// To make Bill series Month Wise and Year wise as per the value selected from Company Profile......

        $todate       =    date('Y-m-d');
        
        $newyear      =   date('Y-04-01');
        
        $newmonth     =   date('Y-m-01');

//////////////////For Bill series Year Wise 

        if ($cstate_id[0]['series_type'] == 1) {

                    $nseries = sales_bill::select('bill_series')
                                        ->where('sales_bill_id', '<', $sales_bill_id)
                                        ->where('company_id', Auth::user()->company_id)
                                        ->orderBy('sales_bill_id', 'DESC')
                                        ->take('1');

                    if($todate >= $newyear){
                       $nseries->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') >= '$newyear'");
                    }

                    $billprefix = '';
                    if ($data[1]['sales_type'] == 1 || $data[1]['sales_type'] == 2) {
                        $nseries->whereIn('sales_type', array(1,2));
                        $billprefix = $cstate_id[0]['bill_number_prefix'];
                    }
                    if ($data[1]['sales_type'] == 4) {
                        $nseries->where('sales_type', 4);
                        $billprefix = $cstate_id[0]['b2b_number_prefix'];
                    }


                    $newseries = $nseries->first();

                    if ($newseries != '') {
                        $billseries = $newseries['bill_series'] + 1;
                    } else {
                        $billseries = 1;
                    }

                    $finalinvoiceno = $billprefix . $billseries . '/' . $f1 . '-' . $f2;
                } 

 //////////////////For Bill series Month Wise        
        else
        {
            if($todate>=$newmonth)
              {

                  $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') >= '$newmonth'")
                                            ->where('sales_bill_id','<',$sales_bill_id)
                                            ->where('company_id',Auth::user()->company_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                       
               
                  if($newseries=='')
                  {
                      $billseries  =  1;
                  }
                  else
                  {
                      $billseries   = $newseries['bill_series']+1;
                      
                  }
                 
               
              }
              else
              {
                $newseries  =  sales_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') <= '$todate'")
                                            ->where('sales_bill_id','<',$sales_bill_id)
                                            ->where('company_id',Auth::user()->company_id)
                                            ->orderBy('sales_bill_id','DESC')
                                            ->take('1')
                                            ->first();
                      $billseries   = $newseries['bill_series']+1;

                
              }

             
              $co     =     strlen($billseries);  
    
              if($co<=2)
              $id1  = '00'.$billseries; 
              elseif($co<=3)
              $id1  = '0'.$billseries;      
              elseif($co<=4)
              $id1  = $billseries;
              $dd   = date('my');
              
              $finalinvoiceno = $cstate_id[0]['bill_number_prefix'].$dd.''.$id1;
        }   
              

        

        if($data[1]['sales_bill_id']=='' || $data[1]['sales_bill_id']==null)
        {  

         sales_bill::where('sales_bill_id',$sales_bill_id)->update(array(
            'bill_no' => $finalinvoiceno,
            'bill_series' => $billseries
         ));
       }
    

       sales_product_detail::where('sales_bill_id',$sales_bill_id)->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));

    
        $productdetail        =    array();
        $stocktransferdetail  =    array();
        $ctotal_mrp   = 0;
        $ctotal_gst   = 0;
        $ctotal_sellprice = 0;
        $ctotal_offerprice = 0;
        $ctotal_cost_igst_amount = 0;
        $ctotal_cost_cgst_amount = 0;
        $ctotal_cost_sgst_amount = 0;

       

         foreach($data[0] AS $billkey=>$billvalue)
          {
              $inwardids    =  '';
              $inwardqtys   =  '';
               if($billvalue['barcodesel']!='')
              {

                  if($cstate_id[0]['tax_type'] == 1)
                  {
                      $halfgstper      =     0;
                      $halfgstamt      =     0;
                  }
                  else
                  {
                      $halfgstper      =     $billvalue['prodgstper']/2;
                      $halfgstamt      =     $billvalue['prodgstamt']/2;
                  }
                     
                      // $productdetail['bill_date']                            =    $invoice_date;
                      // $productdetail['product_system_barcode']               =    $billvalue['barcodesel'];

                      $productdetail['consign_products_detail_id']           =    $billvalue['consign_products_id'];
                      $productdetail['product_id']                           =    $billvalue['productid'];
                      $productdetail['price_master_id']                      =    $billvalue['price_master_id'];
                      $productdetail['qty']                                  =    $billvalue['qty'];
                      $productdetail['mrp']                                  =    $billvalue['mrp'];
                      $productdetail['sellingprice_before_discount']         =    $billvalue['sellingprice_before_discount'];
                      $productdetail['discount_percent']                     =    $billvalue['discount_percent'];
                      $productdetail['discount_amount']                      =    $billvalue['discount_amount'];
                      $productdetail['mrpdiscount_amount']                   =    $billvalue['mrpdiscount_amount'];
                      $productdetail['sellingprice_after_discount']          =    $billvalue['totalsellingwgst'];
                      $productdetail['overalldiscount_percent']              =    $billvalue['overalldiscount_percent'];
                      $productdetail['overalldiscount_amount']               =    $billvalue['overalldiscount_amount'];
                      $productdetail['overallmrpdiscount_amount']            =    $billvalue['overallmrpdiscount_amount'];
                      $productdetail['sellingprice_afteroverall_discount']   =    $billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount'];
                      $productdetail['cgst_percent']                         =    $halfgstper;
                      $productdetail['cgst_amount']                          =    $halfgstamt;
                      $productdetail['sgst_percent']                         =    $halfgstper;
                      $productdetail['sgst_amount']                          =    $halfgstamt;
                      $productdetail['igst_percent']                         =    $billvalue['prodgstper'];
                      $productdetail['igst_amount']                          =    $billvalue['prodgstamt'];
                      $productdetail['total_amount']                         =    $billvalue['totalamount'];
                      $productdetail['product_type']                         =     1;
                      $productdetail['created_by']                           =     Auth::User()->user_id;




          if($billvalue['consign_products_id'] =='')
          {

                if($billvalue['oldprice_master_id'] != ''){

                   price_master::where('price_master_id',$billvalue['oldprice_master_id'])->update(array(
                    'modified_by' => Auth::User()->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'product_qty' => DB::raw('product_qty + '.$billvalue['oldqty'])
                    ));
                 
                  }

                   price_master::where('price_master_id',$billvalue['price_master_id'])->update(array(
                  'modified_by' => Auth::User()->user_id,
                  'updated_at' => date('Y-m-d H:i:s'),
                  'product_qty' => DB::raw('product_qty - '.$billvalue['qty'])
                  ));

///////////////////First In First Out Logic//////////////////////////////////////////////////////////////

                    $oldinwardids       =     explode(',',substr($billvalue['inwardids'],0,-1));
                    $oldinwardqtys      =     explode(',',substr($billvalue['inwardqtys'],0,-1));

                
                       $ccount    =   0;  
                       $icount    =   0;
                       $pcount    =   0;
                       $done      =   0;
                       $firstout  =   0;
                       $restqty   =   $billvalue['qty'];


                         

              if($billvalue['price_master_id']!=$billvalue['oldprice_master_id'] || $billvalue['qty']!=$billvalue['oldqty'])  
               {    

                   if($billvalue['sales_product_id'] !='')
                       { 
                            foreach($oldinwardids as $l=>$val)
                            {
                                inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$oldinwardids[$l])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty + '.$oldinwardqtys[$l])
                                              ));

                            }  
                       }   
                       
                  if($billvalue['qty']>0)
                  {
                      $prodtype    =        product::select('product_type')
                                            //->where('company_id',Auth::user()->company_id)
                                            ->where('product_id',$billvalue['productid'])->get();

                       $prid      =         price_master::select('offer_price','batch_no')
                                            ->where('company_id',Auth::user()->company_id)
                                            ->where('price_master_id',$billvalue['price_master_id'])->get();

                       $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                                            ->where('product_id',$billvalue['productid'])
                                            ->where('company_id',Auth::user()->company_id)
                                            ->where('pending_return_qty','>',0);

                                            
                     if($cstate_id[0]['billtype']==3)
                      {
                            $qquery->where('batch_no',$prid[0]['batch_no']);
                      }
                      if($prodtype[0]['product_type']==1)
                      {
                            $qquery->where('offer_price',$prid[0]['offer_price']);
                      }

                      
                      $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                      
                      if(sizeof($inwarddetail)==0)
                      {
                        
                                
                               return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details against Barcode No. ".$billvalue['barcodesel']." "));
                                exit;
                           
                      }
                            
                   

                       foreach($inwarddetail as $inwarddata)
                       {
                          //echo $inwarddata['pending_return_qty'];
                            if($inwarddata['pending_return_qty'] >= $restqty && $firstout==0)
                            {  
                                  if($done == 0)
                                  {

                                    //echo 'hello';

                                          $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                          $inwardqtys   .=   $restqty.',';
                                      
                                          inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['qty'])
                                              ));
                                          $pcount++;
                                          $done++;
                                 }
                           }
                           else
                           {
                              if($pcount==0 && $done == 0 && $icount==0)
                              {
                                  
                                 
                                  if($restqty  > $inwarddata['pending_return_qty'])
                                  {
                                    //echo 'bbb';
                                    //echo $restqty;
                                      $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                      $inwardqtys   .=   $inwarddata['pending_return_qty'].',';
                                      $ccount       =   $restqty  - $inwarddata['pending_return_qty'];
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
                                    //echo 'ccc';
                                    //echo $restqty;
                                      $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                      $inwardqtys   .=   $restqty.',';
                                      $ccount   =   $restqty  - $inwarddata['pending_return_qty'];
                                      inward_product_detail::where('company_id',Auth::user()->company_id)
                                      ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                      ->update(array(
                                          'modified_by' => Auth::User()->user_id,
                                          'updated_at' => date('Y-m-d H:i:s'),
                                          'pending_return_qty' => DB::raw('pending_return_qty - '.$restqty)
                                          ));
                                  }


                                   if($ccount > 0)
                                    {
                                       $firstout++;
                                       // echo $pcount;
                                       // echo $done;
                                       // echo $icount;
                                       $restqty   =   $restqty  - $inwarddata['pending_return_qty'];
                                      // echo $restqty;

                                       
                                    }
                                    if($ccount <= 0)
                                    {
                                      //echo 'no';
                                      $firstout++;
                                       $icount++;
                                         
                                    }
                                   
                              }
                           }

                       }    
                   }        

                }   
                if($inwardids!='')
                {
                  $productdetail['inwardids']                          =    $inwardids;
                  $productdetail['inwardqtys']                         =    $inwardqtys;
                }   
                else
                {  
                  $productdetail['inwardids']                          =    $billvalue['inwardids'];
                  $productdetail['inwardqtys']                         =    $billvalue['inwardqtys'];
                }
                 // echo $inwardids;
                 // echo $inwardqtys;

            }  
            else
            {
                  $productdetail['inwardids']                          =    $billvalue['inwardids'];
                  $productdetail['inwardqtys']                         =    $billvalue['inwardqtys'];


            }  

        ///***************************End of FIFO logic**************************************    

                $billproductdetail = sales_product_detail::updateOrCreate(
                   ['sales_bill_id' => $sales_bill_id,
                    'company_id'=>$company_id,'sales_products_detail_id'=>$billvalue['sales_product_id'],],
                   $productdetail);

                   $sales_products_detail_id = $billproductdetail->sales_products_detail_id;

  ///********************Franchise Bill detail in stock transfer detail table**********************************
            if($data[1]['sales_type']==2)
            {
                  $pricemastervalues    =   price_master::select('product_mrp','offer_price','selling_gst_percent','selling_gst_amount','batch_no')
                                                          ->where('price_master_id',$billvalue['price_master_id'])
                                                          ->first();
                  $costgstamt                                                  =    (($billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount']) * $billvalue['prodgstper'])/100; 
                  $averagecost                                                 =    $billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount'];
                  $profitamt                                                   =    $billvalue['sellingprice_before_discount'] - $averagecost;
                  $profitper                                                   =    ($profitamt/$averagecost) * 100;
                  $stocktransferdetail['sales_products_detail_id']             =    $sales_products_detail_id;
                  $stocktransferdetail['product_id']                           =    $billvalue['productid'];
                  $stocktransferdetail['price_master_id']                      =    $billvalue['price_master_id'];
                  $stocktransferdetail['batch_no']                             =    $pricemastervalues['batch_no'];
                  $stocktransferdetail['base_price']                           =    $averagecost;
                  $stocktransferdetail['base_discount_percent']                =    0;
                  $stocktransferdetail['base_discount_amount']                 =    0;
                  $stocktransferdetail['scheme_discount_percent']              =    0;
                  $stocktransferdetail['scheme_discount_amount']               =    0;
                  $stocktransferdetail['free_discount_percent']                =    0;
                  $stocktransferdetail['free_discount_amount']                 =    0;
                  $stocktransferdetail['cost_rate']                            =    $averagecost;
                  $stocktransferdetail['cost_igst_percent']                    =    $billvalue['prodgstper'];
                  $stocktransferdetail['cost_igst_amount']                     =    $costgstamt;
                  $stocktransferdetail['cost_cgst_percent']                    =    $billvalue['prodgstper']/2;
                  $stocktransferdetail['cost_cgst_amount']                     =    $costgstamt/2;
                  $stocktransferdetail['cost_sgst_percent']                    =    $billvalue['prodgstper']/2;
                  $stocktransferdetail['cost_sgst_amount']                     =    $costgstamt/2;
                  $stocktransferdetail['cost_price']                           =    $averagecost + $costgstamt;
                  $stocktransferdetail['profit_percent']                       =    $profitper;
                  $stocktransferdetail['profit_amount']                        =    $profitamt;
                  $stocktransferdetail['sell_price']                           =    $billvalue['sellingprice_before_discount'];
                  $stocktransferdetail['selling_gst_percent']                  =    $pricemastervalues['selling_gst_percent'];
                  $stocktransferdetail['selling_gst_amount']                   =    $pricemastervalues['selling_gst_amount'];
                  $stocktransferdetail['offer_price']                          =    $pricemastervalues['offer_price'];
                  $stocktransferdetail['product_mrp']                          =    $pricemastervalues['product_mrp'];
                  $stocktransferdetail['product_qty']                          =    $billvalue['qty'];
                  if($inwardids!='')
                  {
                    $stocktransferdetail['inward_product_detail_id']           =    $inwardids;
                    $stocktransferdetail['inward_product_qtys']                =    $inwardqtys;
                  }   
                  else
                  {  
                    $stocktransferdetail['inward_product_detail_id']           =    $billvalue['inwardids'];
                    $stocktransferdetail['inward_product_qtys']                =    $billvalue['inwardqtys'];
                  }
                  $stocktransferdetail['free_qty']                             =    0;
                  $stocktransferdetail['pending_rcv_qty']                      =    $billvalue['qty'];
                  $stocktransferdetail['total_cost_rate_with_qty']             =    $averagecost  * $billvalue['qty'];
                  $stocktransferdetail['total_igst_amount_with_qty']           =    $costgstamt  * $billvalue['qty'];
                  $stocktransferdetail['total_cgst_amount_with_qty']           =    ($costgstamt/2)  * $billvalue['qty'];
                  $stocktransferdetail['total_sgst_amount_with_qty']           =    ($costgstamt/2)   * $billvalue['qty'];
                  $stocktransferdetail['total_offer_price']                    =    $billvalue['mrp']  * $billvalue['qty'];
                  $stocktransferdetail['total_cost']                           =    ($averagecost + $costgstamt)  * $billvalue['qty'];
                  $stocktransferdetail['is_active']                            =     1;
                  $stocktransferdetail['created_by']                           =     Auth::User()->user_id;

                  $stockdetail = stock_transfer_detail::updateOrCreate(
                   ['stock_transfer_id' => $stock_transfer_id,
                    'company_id'=>$company_id,'stock_transfers_detail_id'=>$billvalue['stock_transfer_detail_id'],],
                   $stocktransferdetail);

                    $ctotal_mrp   += $billvalue['mrp']  * $billvalue['qty'];
                    $ctotal_gst   += $pricemastervalues['selling_gst_amount'] * $billvalue['qty'];
                    $ctotal_sellprice += $billvalue['sellingprice_before_discount'] * $billvalue['qty'];
                    $ctotal_offerprice += $pricemastervalues['offer_price'] * $billvalue['qty'];
                    $ctotal_cost_igst_amount += $costgstamt  * $billvalue['qty'];
                    $ctotal_cost_cgst_amount = ($costgstamt/2)  * $billvalue['qty'];



            }    
              
      }
     
     
            
     }
///********************Franchise Bill detail in stock transfer detail table**********************************
     if($data[1]['sales_type']==2)
     {
        stock_transfer::where('stock_transfer_id',$stock_transfer_id)->update(array(
            'stock_transfer_no' => $stock_transfer_no,
            'total_mrp' => $ctotal_mrp,
            'total_qty' => $data[1]['overallqty'],
            'total_gst' => $ctotal_gst,
            'total_sellprice' => $ctotal_sellprice,
            'total_offerprice' => $ctotal_offerprice,
            'total_cost_igst_amount' => $ctotal_cost_igst_amount,
            'total_cost_cgst_amount' => $ctotal_cost_cgst_amount,
            'total_cost_sgst_amount' => $ctotal_cost_cgst_amount

         ));
     }

          $chargesdetail     =    array();

       

         foreach($data[3] AS $chargeskey=>$chargesvalue)
          {

               if($chargesvalue['chargesamt']!='')
              {


                      $halfgstper      =     $chargesvalue['csprodgstper']/2;
                      $halfgstamt      =     $chargesvalue['csprodgstamt']/2;
                      // $chargesdetail['bill_date']                            =    $invoice_date;
                      $chargesdetail['product_id']                           =    $chargesvalue['cproductid'];
                      $chargesdetail['qty']                                  =    $chargesvalue['cqty'];
                      $chargesdetail['mrp']                                  =    $chargesvalue['chargesamt'];
                      $chargesdetail['sellingprice_before_discount']         =    $chargesvalue['chargesamt'];
                      $chargesdetail['sellingprice_after_discount']          =    $chargesvalue['chargesamt'];
                      $chargesdetail['sellingprice_afteroverall_discount']   =    $chargesvalue['chargesamt'];
                      $chargesdetail['cgst_percent']                         =    $halfgstper;
                      $chargesdetail['cgst_amount']                          =    $halfgstamt;
                      $chargesdetail['sgst_percent']                         =    $halfgstper;
                      $chargesdetail['sgst_amount']                          =    $halfgstamt;
                      $chargesdetail['igst_percent']                         =    $chargesvalue['csprodgstper'];
                      $chargesdetail['igst_amount']                          =    $chargesvalue['csprodgstamt'];
                      $chargesdetail['total_amount']                         =    $chargesvalue['ctotalamount'];
                      $chargesdetail['product_type']                         =     2;
                      $chargesdetail['created_by']                           =     Auth::User()->user_id;

                
               
                   $billchargesdetail = sales_product_detail::updateOrCreate(
                   ['sales_bill_id' => $sales_bill_id,
                    'company_id'=>$company_id,'sales_products_detail_id'=>$chargesvalue['csales_product_id'],],
                   $chargesdetail);

              
      }
     
     
            
    }  

      sales_bill_payment_detail::where('sales_bill_id',$sales_bill_id)->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
            'total_bill_amount'=>0
        ));
    

    $paymentanswers     =    array();

         foreach($data[2] AS $key=>$value2)
          {
             if($value2['id']==3)
              {
                $paymentanswers['bankname']    =   $data[1]['bankname'];
                $paymentanswers['chequeno']    =   $data[1]['chequeno'];
                $paymentanswers['customer_creditnote_id']    =     NULL;
              }
              elseif($value2['id']==7)
              {
                $paymentanswers['bankname']    =     $data[1]['netbankname'];
                $paymentanswers['chequeno']    =     '';
                $paymentanswers['customer_creditnote_id']    =     NULL;
              }
              elseif($value2['id']==6)
              {
                $paymentanswers['bankname']    =     $data[1]['duedate'];
                $paymentanswers['chequeno']    =     '';
                $paymentanswers['customer_creditnote_id']    =     NULL;
              }
              elseif($value2['id']==8)
              {
                $paymentanswers['customer_creditnote_id']    =     $data[1]['creditnoteid'];
                $paymentanswers['bankname']='';
                $paymentanswers['chequeno'] =  '';

              }
              else
              {
                $paymentanswers['bankname']='';
                $paymentanswers['chequeno'] =  '';
                $paymentanswers['customer_creditnote_id']    =     NULL;
              }
           
                
                $paymentanswers['sales_bill_id']                 =  $sales_bill_id;
                $paymentanswers['total_bill_amount']             =  $value2['value'];
                $paymentanswers['payment_method_id']             =  $value2['id'];
                $paymentanswers['created_by']                    =  Auth::User()->user_id;
                $paymentanswers['deleted_at'] =  NULL;
                $paymentanswers['deleted_by'] =  NULL;
                
            
           
       
           $paymentdetail = sales_bill_payment_detail::updateOrCreate(
               ['sales_bill_id' => $sales_bill_id,'sales_bill_payment_detail_id'=>$value2['sales_payment_id'],],
               $paymentanswers);


            
    }

    if($data[1]['creditaccountid']=='')
    {

        customer_creditaccount::where('sales_bill_id',$sales_bill_id)->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
            'credit_amount'=>0,
            'balance_amount'=>0
          ));
   
        foreach($data[2] AS $key=>$value3)
          {
              if($value3['id']==6)
              {
                  if($value3['value'] !='' || $value3['value']!=0)
                  {
                         $sales = customer_creditaccount::updateOrCreate(
                        ['sales_bill_id' => $sales_bill_id, 'company_id'=>$company_id,],
                        ['customer_id'=>$data[1]['customer_id'],
                            'bill_date'=>$invoice_date,
                            'duedate'=>$data[1]['duedate'],
                            'credit_amount'=>$value3['value'],
                            'balance_amount'=>$value3['value'],
                            'created_by' =>$created_by,
                            'deleted_at' =>NULL,
                            'deleted_by' =>NULL,
                            'is_active' => "1"
                            ]
                        );
                    }

                
              }
        
          }

    }
    else
    {
       if($data[1]['creditbalcheck']==0)
       {

            customer_creditaccount::where('sales_bill_id',$sales_bill_id)->update(array(
              'deleted_by' => Auth::User()->user_id,
              'deleted_at' => date('Y-m-d H:i:s'),
              'credit_amount'=>0,
              'balance_amount'=>0
            ));
     
          foreach($data[2] AS $key=>$value3)
            {
                if($value3['id']==6)
                {
                    if($value3['value'] !='' || $value3['value']!=0)
                    {
                           $sales = customer_creditaccount::updateOrCreate(
                          ['sales_bill_id' => $sales_bill_id, 'company_id'=>$company_id,],
                          ['customer_id'=>$data[1]['customer_id'],
                              'bill_date'=>$invoice_date,
                              'duedate'=>$data[1]['duedate'],
                              'credit_amount'=>$value3['value'],
                              'balance_amount'=>$value3['value'],
                              'created_by' =>$created_by,
                              'deleted_at' =>NULL,
                              'deleted_by' =>NULL,
                              'is_active' => "1"
                              ]
                          );
                      }

                  
                }
          
            }
       }
    }

    if($data[1]['editcreditnotepaymentid']!='')
    {

        creditnote_payment::where('creditnote_payment_id',$data[1]['editcreditnotepaymentid'])->update(array(
              'deleted_by' => Auth::User()->user_id,
              'deleted_at' => date('Y-m-d H:i:s'),
              'creditnote_amount'=>0,
              'used_amount'=>0,
              'balance_amount'=>0
            ));

        $updatecreditnoteamount    =  customer_creditnote::select('balance_amount')
                    ->where('customer_creditnote_id',$data[1]['editcreditnoteid'])
                    ->where('company_id',Auth::user()->company_id)
                    ->get();

          $updatecreditamount   =    $updatecreditnoteamount[0]['balance_amount'] + $data[1]['editcreditnoteamount'];

         customer_creditnote::where('customer_creditnote_id',$data[1]['editcreditnoteid'])->update(array(
                      'balance_amount' => $updatecreditamount
                  ));   
    }
    

    if($data[1]['creditnoteid']!='')
    {

        $creditbalanceamt   =    $data[1]['creditnoteamount'] - $data[1]['issueamount'];
        $creditnotepayment = creditnote_payment::updateOrCreate(
            ['sales_bill_id' => $sales_bill_id, 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
                'customer_creditnote_id'=>$data[1]['creditnoteid'],
                'creditnote_amount'=>$data[1]['creditnoteamount'],
                'used_amount'=>$data[1]['issueamount'],
                'balance_amount'=>$creditbalanceamt,
                'created_by' =>$created_by,
                'is_active' => "1",
                'deleted_at' =>NULL,
                'deleted_by' =>NULL,
            ]
        );

          customer_creditnote::where('customer_creditnote_id',$data[1]['creditnoteid'])->update(array(
                      'balance_amount' => $creditbalanceamt
                  ));    

    }

     
     DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }
        if($billproductdetail)
        {
            
            if($data[1]['sales_type']==1)
            {
               if($data[1]['sales_bill_id'] != '')
              {   
                  if($cstate_id[0]['billprint_type']==1)
                  {
                      return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>route('print_bill', ['id' => encrypt($sales_bill_id)]),"burl"=>"sales_bill"));
                  }
                  else
                  {
                      return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>route('thermalprint_bill', ['id' => encrypt($sales_bill_id)]),"burl"=>"sales_bill"));
                  }
              }
              else
              {
                  if($cstate_id[0]['billprint_type']==1)
                  {
                    return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>route('print_bill', ['id' => encrypt($sales_bill_id)]),"burl"=>"sales_bill"));
                  }
                  else
                  {
                    return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>route('thermalprint_bill', ['id' => encrypt($sales_bill_id)]),"burl"=>"sales_bill"));
                  }
              }
           }
          else
          {
            return json_encode(array("Success"=>"True","Message"=>"Billing successfully Update!","url"=>"franchise_bill"));
          }

               

           
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }

  

   }
   //**********************************************************Save Store Stock transfer Data**************************************************************************************************************************************************************************************************************************////
   else
   {
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;


        $created_by = $userId;

       
        $cstate_id = company_profile::select('state_id','billtype','bill_number_prefix','tax_type','billprint_type','series_type','bill_number_prefix')->where('company_id',Auth::user()->company_id)->get(); 
                
       
        
         $invoice_date            =     $data[1]['invoice_date'];
         $selling_after_discount  =     $data[1]['totalwithout_gst'] - $data[1]['roomwisediscount_amount'];
         $roundoff                =    round($data[1]['ggrand_total']) - $data[1]['ggrand_total'];

        
     try {
            DB::beginTransaction();    

        
        
///********************Franchise Bill detail in stock transfer detail table**********************************
    
        $stock_transfer = stock_transfer::updateOrCreate(
            ['stock_transfer_id' => $data[1]['stock_transfer_id'], 'company_id'=>$company_id,],
            ['stock_transfer_no'=>$data[1]['invoice_no'],
            'store_id'=>$data[1]['store_id'],
                'stock_transfer_date'=>$invoice_date,
                'total_mrp'=>0,
                'total_qty'=>0,
                'total_gst'=>0,
                'total_sellprice'=>0,
                'total_offerprice'=>0,
                'total_cost_igst_amount'=>0,
                'total_cost_cgst_amount'=>0,
                'total_cost_sgst_amount'=>0,
                'store_type'=>1,
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );


       
       $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
       $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

       
          $stock_transfer_id   =  $stock_transfer->stock_transfer_id;
          $stock_transfer_no          =       $stock_transfer_id.'/'.$f1.'-'.$f2;
       
  
        $stocktransferdetail  =    array();
        $ctotal_mrp   = 0;
        $ctotal_gst   = 0;
        $ctotal_sellprice = 0;
        $ctotal_offerprice = 0;
        $ctotal_cost_igst_amount = 0;
        $ctotal_cost_cgst_amount = 0;
        $ctotal_cost_sgst_amount = 0;

       

         foreach($data[0] AS $billkey=>$billvalue)
          {
              $inwardids    =  '';
              $inwardqtys   =  '';
               if($billvalue['barcodesel']!='')
              {

                  if($cstate_id[0]['tax_type'] == 1)
                  {
                      $halfgstper      =     0;
                      $halfgstamt      =     0;
                  }
                  else
                  {
                      $halfgstper      =     $billvalue['prodgstper']/2;
                      $halfgstamt      =     $billvalue['prodgstamt']/2;
                  }
                  $pricemastervalues    =   price_master::select('product_mrp','offer_price','selling_gst_percent','selling_gst_amount','batch_no')
                                                          ->where('price_master_id',$billvalue['price_master_id'])
                                                          ->first();
                  $costgstamt                                                  =    (($billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount']) * $billvalue['prodgstper'])/100; 
                  $averagecost                                                 =    $billvalue['totalsellingwgst']-$billvalue['overalldiscount_amount'];
                  
               
                  $stocktransferdetail['product_id']                           =    $billvalue['productid'];
                  $stocktransferdetail['price_master_id']                      =    $billvalue['price_master_id'];
                  $stocktransferdetail['batch_no']                             =    $pricemastervalues['batch_no'];
                  
                  $stocktransferdetail['base_discount_percent']                =    0;
                  $stocktransferdetail['base_discount_amount']                 =    0;
                  $stocktransferdetail['scheme_discount_percent']              =    0;
                  $stocktransferdetail['scheme_discount_amount']               =    0;
                  $stocktransferdetail['free_discount_percent']                =    0;
                  $stocktransferdetail['free_discount_amount']                 =    0;
                  
                  $stocktransferdetail['cost_igst_percent']                    =    0;
                  $stocktransferdetail['cost_igst_amount']                     =    0;
                  $stocktransferdetail['cost_cgst_percent']                    =    0;
                  $stocktransferdetail['cost_cgst_amount']                     =    0;
                  $stocktransferdetail['cost_sgst_percent']                    =    0;
                  $stocktransferdetail['cost_sgst_amount']                     =    0;
                  
                  $stocktransferdetail['sell_price']                           =    $billvalue['sellingprice_before_discount'];
                  $stocktransferdetail['selling_gst_percent']                  =    $pricemastervalues['selling_gst_percent'];
                  $stocktransferdetail['selling_gst_amount']                   =    $pricemastervalues['selling_gst_amount'];
                  $stocktransferdetail['offer_price']                          =    $pricemastervalues['offer_price'];
                  $stocktransferdetail['product_mrp']                          =    $pricemastervalues['product_mrp'];
                  $stocktransferdetail['product_qty']                          =    $billvalue['qty'];
                  
                  $stocktransferdetail['free_qty']                             =    0;
                  $stocktransferdetail['pending_rcv_qty']                      =    $billvalue['qty'];
                  $stocktransferdetail['total_cost_rate_with_qty']             =    $averagecost  * $billvalue['qty'];
                  $stocktransferdetail['total_igst_amount_with_qty']           =    $costgstamt  * $billvalue['qty'];
                  $stocktransferdetail['total_cgst_amount_with_qty']           =    ($costgstamt/2)  * $billvalue['qty'];
                  $stocktransferdetail['total_sgst_amount_with_qty']           =    ($costgstamt/2)   * $billvalue['qty'];
                  $stocktransferdetail['total_offer_price']                    =    $billvalue['mrp']  * $billvalue['qty'];
                  $stocktransferdetail['total_cost']                           =    ($averagecost + $costgstamt)  * $billvalue['qty'];
                  $stocktransferdetail['is_active']                            =     1;
                  $stocktransferdetail['created_by']                           =     Auth::User()->user_id;


                if($billvalue['oldprice_master_id'] != ''){

                   price_master::where('price_master_id',$billvalue['oldprice_master_id'])->update(array(
                    'modified_by' => Auth::User()->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'product_qty' => DB::raw('product_qty + '.$billvalue['oldqty'])
                    ));
                 
                  }

                   price_master::where('price_master_id',$billvalue['price_master_id'])->update(array(
                  'modified_by' => Auth::User()->user_id,
                  'updated_at' => date('Y-m-d H:i:s'),
                  'product_qty' => DB::raw('product_qty - '.$billvalue['qty'])
                  ));

///////////////////First In First Out Logic//////////////////////////////////////////////////////////////

                    $oldinwardids       =     explode(',',substr($billvalue['inwardids'],0,-1));
                    $oldinwardqtys      =     explode(',',substr($billvalue['inwardqtys'],0,-1));

                
                       $ccount    =   0;  
                       $icount    =   0;
                       $pcount    =   0;
                       $done      =   0;
                       $firstout  =   0;
                       $restqty   =   $billvalue['qty'];


            if($data[1]['inward_stock_id']!='')
            {
                if($billvalue['inwardids']!='')
                {

                   inward_product_detail::where('company_id',Auth::user()->company_id)
                                            ->where('inward_product_detail_id',$billvalue['inwardids'])
                                            ->update(array(
                                                'modified_by' => Auth::User()->user_id,
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['qty'])
                                                )); 
                    $stocktransferdetail['inward_product_detail_id']           =    $billvalue['inwardids'].',';
                    $stocktransferdetail['inward_product_qtys']                =    $billvalue['qty'].',';
                    $inwardids    .=   $billvalue['inwardids'].',';
                    $inwardqtys   .=   $billvalue['qty'].',';

                }
                else
                {
   ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                  if($billvalue['price_master_id']!=$billvalue['oldprice_master_id'] || $billvalue['qty']!=$billvalue['oldqty'])  
                       {    

                           if($billvalue['sales_product_id'] !='')
                               { 
                                    foreach($oldinwardids as $l=>$val)
                                    {
                                        inward_product_detail::where('company_id',Auth::user()->company_id)
                                                  ->where('inward_product_detail_id',$oldinwardids[$l])
                                                  ->update(array(
                                                      'modified_by' => Auth::User()->user_id,
                                                      'updated_at' => date('Y-m-d H:i:s'),
                                                      'pending_return_qty' => DB::raw('pending_return_qty + '.$oldinwardqtys[$l])
                                                      ));

                                    }  
                               }   
                               
                          if($billvalue['qty']>0)
                          {
                              $prodtype    =        product::select('product_type')
                                                    //->where('company_id',Auth::user()->company_id)
                                                    ->where('product_id',$billvalue['productid'])->get();

                               $prid      =         price_master::select('offer_price','batch_no')
                                                    ->where('company_id',Auth::user()->company_id)
                                                    ->where('price_master_id',$billvalue['price_master_id'])->get();

                               $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                                                    ->where('product_id',$billvalue['productid'])
                                                    ->where('company_id',Auth::user()->company_id)
                                                    ->where('pending_return_qty','>',0);

                                                    
                             if($cstate_id[0]['billtype']==3)
                              {
                                    $qquery->where('batch_no',$prid[0]['batch_no']);
                              }
                              if($prodtype[0]['product_type']==1)
                              {
                                    $qquery->where('offer_price',$prid[0]['offer_price']);
                              }

                              
                              $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                              
                              if(sizeof($inwarddetail)==0)
                              {
                                
                                     return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details against Barcode No. ".$billvalue['barcodesel']." "));
                                exit;
                                   
                              }
                                    
                           

                               foreach($inwarddetail as $inwarddata)
                               {
                                  //echo $inwarddata['pending_return_qty'];
                                    if($inwarddata['pending_return_qty'] >= $restqty && $firstout==0)
                                    {  
                                          if($done == 0)
                                          {

                                            //echo 'hello';

                                                  $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                                  $inwardqtys   .=   $restqty.',';
                                              
                                                  inward_product_detail::where('company_id',Auth::user()->company_id)
                                                  ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                                  ->update(array(
                                                      'modified_by' => Auth::User()->user_id,
                                                      'updated_at' => date('Y-m-d H:i:s'),
                                                      'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['qty'])
                                                      ));
                                                  $pcount++;
                                                  $done++;
                                         }
                                   }
                                   else
                                   {
                                      if($pcount==0 && $done == 0 && $icount==0)
                                      {
                                          
                                         
                                          if($restqty  > $inwarddata['pending_return_qty'])
                                          {
                                            //echo 'bbb';
                                            //echo $restqty;
                                              $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                              $inwardqtys   .=   $inwarddata['pending_return_qty'].',';
                                              $ccount       =   $restqty  - $inwarddata['pending_return_qty'];
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
                                            //echo 'ccc';
                                            //echo $restqty;
                                              $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                              $inwardqtys   .=   $restqty.',';
                                              $ccount   =   $restqty  - $inwarddata['pending_return_qty'];
                                              inward_product_detail::where('company_id',Auth::user()->company_id)
                                              ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                              ->update(array(
                                                  'modified_by' => Auth::User()->user_id,
                                                  'updated_at' => date('Y-m-d H:i:s'),
                                                  'pending_return_qty' => DB::raw('pending_return_qty - '.$restqty)
                                                  ));
                                          }


                                           if($ccount > 0)
                                            {
                                               $firstout++;
                                               // echo $pcount;
                                               // echo $done;
                                               // echo $icount;
                                               $restqty   =   $restqty  - $inwarddata['pending_return_qty'];
                                              // echo $restqty;

                                               
                                            }
                                            if($ccount <= 0)
                                            {
                                              //echo 'no';
                                              $firstout++;
                                               $icount++;
                                                 
                                            }
                                           
                                      }
                                   }

                               }    
                           }        

                        }   
                       if($inwardids!='')
                        {
                          $stocktransferdetail['inward_product_detail_id']           =    $inwardids;
                          $stocktransferdetail['inward_product_qtys']                =    $inwardqtys;
                        }   
                        else
                        {  
                          $stocktransferdetail['inward_product_detail_id']           =    $billvalue['inwardids'];
                          $stocktransferdetail['inward_product_qtys']                =    $billvalue['inwardqtys'];
                          $inwardids                                                 =    $billvalue['inwardids'];
                          $inwardqtys                                                =    $billvalue['inwardqtys'];

                        }

        ///////////////////////////////////////////////////////
                }


            } 
            else
            {



              if($billvalue['price_master_id']!=$billvalue['oldprice_master_id'] || $billvalue['qty']!=$billvalue['oldqty'])  
               {    

                   if($billvalue['sales_product_id'] !='')
                       { 
                            foreach($oldinwardids as $l=>$val)
                            {
                                inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$oldinwardids[$l])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty + '.$oldinwardqtys[$l])
                                              ));

                            }  
                       }   
                       
                  if($billvalue['qty']>0)
                  {
                      $prodtype    =        product::select('product_type')
                                            //->where('company_id',Auth::user()->company_id)
                                            ->where('product_id',$billvalue['productid'])->get();

                       $prid      =         price_master::select('offer_price','batch_no')
                                            ->where('company_id',Auth::user()->company_id)
                                            ->where('price_master_id',$billvalue['price_master_id'])->get();

                       $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                                            ->where('product_id',$billvalue['productid'])
                                            ->where('company_id',Auth::user()->company_id)
                                            ->where('pending_return_qty','>',0);

                                            
                     if($cstate_id[0]['billtype']==3)
                      {
                            $qquery->where('batch_no',$prid[0]['batch_no']);
                      }
                      if($prodtype[0]['product_type']==1)
                      {
                            $qquery->where('offer_price',$prid[0]['offer_price']);
                      }

                      
                      $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                      
                      if(sizeof($inwarddetail)==0)
                      {
                        
                                
                                return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details against Barcode No. ".$billvalue['barcodesel']." "));
                                exit;
                           
                      }
                            
                   

                       foreach($inwarddetail as $inwarddata)
                       {
                          //echo $inwarddata['pending_return_qty'];
                            if($inwarddata['pending_return_qty'] >= $restqty && $firstout==0)
                            {  
                                  if($done == 0)
                                  {

                                    //echo 'hello';

                                          $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                          $inwardqtys   .=   $restqty.',';
                                      
                                          inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['qty'])
                                              ));
                                          $pcount++;
                                          $done++;
                                 }
                           }
                           else
                           {
                              if($pcount==0 && $done == 0 && $icount==0)
                              {
                                  
                                 
                                  if($restqty  > $inwarddata['pending_return_qty'])
                                  {
                                    //echo 'bbb';
                                    //echo $restqty;
                                      $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                      $inwardqtys   .=   $inwarddata['pending_return_qty'].',';
                                      $ccount       =   $restqty  - $inwarddata['pending_return_qty'];
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
                                    //echo 'ccc';
                                    //echo $restqty;
                                      $inwardids    .=   $inwarddata['inward_product_detail_id'].',';
                                      $inwardqtys   .=   $restqty.',';
                                      $ccount   =   $restqty  - $inwarddata['pending_return_qty'];
                                      inward_product_detail::where('company_id',Auth::user()->company_id)
                                      ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                      ->update(array(
                                          'modified_by' => Auth::User()->user_id,
                                          'updated_at' => date('Y-m-d H:i:s'),
                                          'pending_return_qty' => DB::raw('pending_return_qty - '.$restqty)
                                          ));
                                  }


                                   if($ccount > 0)
                                    {
                                       $firstout++;
                                       // echo $pcount;
                                       // echo $done;
                                       // echo $icount;
                                       $restqty   =   $restqty  - $inwarddata['pending_return_qty'];
                                      // echo $restqty;

                                       
                                    }
                                    if($ccount <= 0)
                                    {
                                      //echo 'no';
                                      $firstout++;
                                       $icount++;
                                         
                                    }
                                   
                              }
                           }

                       }    
                   }        

                }   
               if($inwardids!='')
                {
                  $stocktransferdetail['inward_product_detail_id']           =    $inwardids;
                  $stocktransferdetail['inward_product_qtys']                =    $inwardqtys;
                }   
                else
                {  
                  $stocktransferdetail['inward_product_detail_id']           =    $billvalue['inwardids'];
                  $stocktransferdetail['inward_product_qtys']                =    $billvalue['inwardqtys'];
                  $inwardids                                                 =    $billvalue['inwardids'];
                  $inwardqtys                                                =    $billvalue['inwardqtys'];

                }
            }
                
////////************************to Calculate Average Cost Price*******************************************
                  $total_price  = 0;
                  if($inwardids !='' || $inwardids !=null)
                  {
                      $cinward_product_detail_id  = explode(',' ,substr($inwardids,0,-1));
                      $cinward_product_qtys = explode(',' ,substr($inwardqtys,0,-1));


                      foreach($cinward_product_detail_id as $inidkey=>$inids)
                      {
                          $cost_price =  inward_product_detail::select('cost_rate')->find($inids);
                          $total_price += $cost_price['cost_rate'] * $cinward_product_qtys[$inidkey];
                          //print_r($cinward_product_qtys[$inidkey]);
                          
                      }

                      $averagecost      =   $total_price;
                  }
                  else
                  {
                      $averagecost      =   0;
                  }

                  $stockcostprice        =  $averagecost / $billvalue['qty'];

                 
                  $stocktransferdetail['base_price']                           =    $stockcostprice;
                  $stocktransferdetail['cost_rate']                            =    $stockcostprice;
                  $stocktransferdetail['cost_price']                           =    $stockcostprice;
                  $profitamt                                                   =    $billvalue['sellingprice_before_discount'] - $stockcostprice;
                  if($stockcostprice ==0)
                  {
                    $profitper = 0;
                  }
                  else
                  {
                    $profitper                                                  =    ($profitamt/$stockcostprice) * 100;
                  }
                  
                  $stocktransferdetail['profit_percent']                       =    $profitper;
                  $stocktransferdetail['profit_amount']                        =    $profitamt;
        

        ///***************************End of FIFO logic**************************************    


  ///********************Franchise Bill detail in stock transfer detail table**********************************
          
                  

                  $stockdetail = stock_transfer_detail::updateOrCreate(
                   ['stock_transfer_id' => $stock_transfer_id,
                    'company_id'=>$company_id,'stock_transfers_detail_id'=>$billvalue['stock_transfer_detail_id'],],
                   $stocktransferdetail);

                    $ctotal_mrp   += $billvalue['mrp']  * $billvalue['qty'];
                    $ctotal_gst   += $pricemastervalues['selling_gst_amount'] * $billvalue['qty'];
                    $ctotal_sellprice += $billvalue['sellingprice_before_discount'] * $billvalue['qty'];
                    $ctotal_offerprice += $pricemastervalues['offer_price'] * $billvalue['qty'];
                    $ctotal_cost_igst_amount = 0;
                    $ctotal_cost_cgst_amount = 0;


              
      }
     
     
            
     }

    
///********************Franchise Bill detail in stock transfer detail table**********************************
   
        stock_transfer::where('stock_transfer_id',$stock_transfer_id)->update(array(
            'stock_transfer_no' => $stock_transfer_no,
            'total_mrp' => $ctotal_mrp,
            'total_qty' => $data[1]['overallqty'],
            'total_gst' => $ctotal_gst,
            'total_sellprice' => $ctotal_sellprice,
            'total_offerprice' => $ctotal_offerprice,
            'total_cost_igst_amount' => $ctotal_cost_igst_amount,
            'total_cost_cgst_amount' => $ctotal_cost_cgst_amount,
            'total_cost_sgst_amount' => $ctotal_cost_cgst_amount

         ));

     DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }

        if($stockdetail)
        {
            
           return json_encode(array("Success"=>"True","Message"=>"Stock has been successfully Transferred.","burl"=>"stock_transfer","url"=>""));
           
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
   }

        

}
    public function edit_bill(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $bill_id = decrypt($request->bill_id);


        $actualbilltype    =   company_profile::select('billtype')
                              ->where('company_id',Auth::user()->company_id)->first(); 

        if($actualbilltype['billtype']==1 || $actualbilltype['billtype']==2)
        {
            $bill_data = sales_bill::where([
            ['sales_bill_id','=',$bill_id],
            ['company_id',Auth::user()->company_id]])
            ->with('stock_transfer')
            ->with('customer')
            ->with('reference')
            ->with('customer_address_detail')
            ->with([
                    'sales_product_detail.product.editprice_master' => function($fquery) {
                         $fquery->select('*',DB::raw("(SELECT cost_rate FROM inward_product_details WHERE inward_product_details.product_id = price_masters.product_id order by inward_product_detail_id LIMIT 1) as costprice"));
                    }
                    ])            
            ->with('sales_product_detail.product','sales_product_detail.product.uqc','sales_product_detail.stock_transfer_detail')
            ->with('sales_bill_payment_detail.payment_method')
            ->with('customer_creditaccount')
            ->with('creditnote_payment.customer_creditnote')
            ->select('*')
            ->first();
        }
        else
        {
            $bill_data = sales_bill::where([
            ['sales_bill_id','=',$bill_id],
            ['company_id',Auth::user()->company_id]])
            ->with('stock_transfer')
            ->with('customer')
            ->with('reference')
            ->with('customer_address_detail')
            ->with([
            'sales_product_detail.batchprice_master' => function($fquery) {
                 $fquery->select('*',DB::raw("(SELECT cost_rate FROM inward_product_details WHERE inward_product_details.product_id = price_masters.product_id order by inward_product_detail_id LIMIT 1) as costprice"));
            }])
            ->with('sales_product_detail.product','sales_product_detail.product.uqc','sales_product_detail.stock_transfer_detail')
            ->with('sales_bill_payment_detail.payment_method')
            ->with('customer_creditaccount')
            ->with('creditnote_payment.customer_creditnote')
            ->select('*')
            ->first();
        }
       
        

                // echo '<pre>';
                // print_r($bill_data['sales_product_detail']);
                // exit;

             $product_features =  ProductFeatures::getproduct_feature('');
             foreach($bill_data['sales_product_detail'] as $ss=>$bval)
             {
                // echo '<pre>';
                // print_r($bval);
              if(isset($bval['product']['product_features_relationship']) && $bval['product']['product_features_relationship'] != '')
              {
                  foreach ($product_features AS $kk => $vv)
                  {
                      $html_id = $vv['html_id'];

                      if($bval['product']['product_features_relationship'][$html_id] != '' && $bval['product']['product_features_relationship'][$html_id] != NULL)
                      {
                          $nm =  product::feature_value($vv['product_features_id'],$bval['product']['product_features_relationship'][$html_id]);
                          $bval['product'][$html_id] =$nm;
                      }
                  }
              }
            }
    
            $data = json_encode($bill_data);
            // echo '<pre>';
            // print_r($bill_data);
            // exit;

            if($bill_data['sales_type']==2)
            {
                return json_encode(array("Success"=>"True","Data"=>$data,"url"=>"franchise_bill"));
            }
            else
            {
              return json_encode(array("Success"=>"True","Data"=>$data,"url"=>"sales_bill"));
            }
          
          
      //dd($bill_data);
        


    }




}
