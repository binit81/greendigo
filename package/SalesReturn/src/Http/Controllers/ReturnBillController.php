<?php

namespace Retailcore\SalesReturn\Http\Controllers;

use Retailcore\SalesReturn\Models\return_bill;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\Sales\Models\reference;
use Retailcore\CreditBalance\Models\customer_creditaccount;
use Retailcore\Products\Models\product\product;
use Retailcore\GST_Slabs\Models\GST_Slabs\gst_slabs_master;
use Retailcore\Customer\Models\customer\customer\customer;
use Retailcore\Customer\Models\customer\customer_address_detail;
use Retailcore\Customer_Source\Models\customer_source\customer_source;
use Retailcore\Sales\Models\payment_method;
use Retailcore\Consignment\Models\consign_bill;
use Retailcore\Consignment\Models\consign_products_detail;
use App\state;
use App\country;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Auth;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\SalesReturn\Models\return_bill_payment;
use Retailcore\CreditNote\Models\customer_creditnote;
use Retailcore\SalesReturn\Models\returnbill_product;
use Retailcore\CreditBalance\Models\customer_creditreceipt;
use Retailcore\CreditBalance\Models\customer_creditreceipt_detail;
use Retailcore\CreditBalance\Models\customer_crerecp_payment;
use Retailcore\CreditNote\Models\creditnote_payment;
use Retailcore\Products\Models\product\ProductFeatures;
use DB;
use Log;
class ReturnBillController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $state = state::all();
        $country = country::all();

         $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->orderBy('payment_order','ASC')->get();

          $chargeslist      =   product::select('product_id','product_name')
                              ->where('company_id',Auth::user()->company_id)
                              ->where('item_type','=',2)
                              ->get();

        $customer_source = customer_source::where('company_id',Auth::user()->company_id)
          ->where('deleted_at','=',NULL)
          ->orderBy('customer_source_id','DESC')->get();

         return view('salesreturn::sales_return',compact('payment_methods','state','country','chargeslist','customer_source'));
    }

    public function billno_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->search_val !='')
        {

            $json = [];
            $result = sales_bill::select('bill_no','sales_bill_id')
                ->where('bill_no', 'LIKE', "%$request->search_val%")
                ->where('sales_type','=',1)
                // ->whereNull('consign_bill_id')
                ->where('company_id',Auth::user()->company_id)->get();

            if(!empty($result))
            {

                foreach($result as $billkey=>$billvalue){

                       $json[$billkey]['label'] = $billvalue['bill_no'];
                       $json[$billkey]['sales_bill_id'] = $billvalue['sales_bill_id'];


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
    public function consignbill_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->search_val !='')
        {

            $json = [];
            $result = consign_bill::select('bill_no','consign_bill_id')
                ->where('bill_no', 'LIKE', "%$request->search_val%")
                ->where('company_id',Auth::user()->company_id)->get();




            if(!empty($result))
            {

                foreach($result as $billkey=>$billvalue){

                       $json[$billkey]['label'] = $billvalue['bill_no'];
                       $json[$billkey]['consign_bill_id'] = $billvalue['consign_bill_id'];


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
    public function returnproduct_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->search_val !='')
        {
            $sresult = [];
            $json = [];

               $result = price_master::where('company_id', Auth::user()->company_id)
                  ->where('deleted_at', '=', NULL)
                  ->where('batch_no', 'LIKE', "%$request->search_val%")
                  //->where('product_qty','>',0)
                  ->with('product')
                  ->whereHas('product',function ($q) use($request){
                          $q->select('product_name','product_system_barcode','supplier_barcode','item_type');
                    })->take(10)->get();


                  if(sizeof($result) == 0)
                  {

                      $sresult = product::select('product_name','product_system_barcode','supplier_barcode','product_id','item_type')
                                ->where('deleted_at', '=', NULL)
                                ->where('product_system_barcode', 'like', '%'.$request->search_val.'%')
                                ->orWhere('supplier_barcode', 'like', '%'.$request->search_val.'%')
                                ->orWhere('product_name', 'like', '%'.$request->search_val.'%')
                                ->groupBy('product_id')
                                ->with('reportprice_master')
                                ->whereHas('reportprice_master',function ($q) use($request){
                                        $q->select('batch_no');
                                        // $q->where('batch_no','!=',NULL);
                                        $q->where('company_id', Auth::user()->company_id);
                                    })->take(10)->get();
                    }
                            // echo '<pre>';
                            // print_r($result);
                            //  echo '</pre>';


            if(sizeof($result) != 0)
            {
                // echo 'aaa';
                // print_r($result);
                foreach($result as $productkey=>$productvalue){

                      if($productvalue['product']['supplier_barcode']!='' || $productvalue['product']['supplier_barcode']!=null)
                      {

                          if($productvalue['product']['item_type']==3)
                          {
                              $json[$productkey]['label'] = $productvalue['batch_no'];
                              $json[$productkey]['product_id'] = $productvalue['product']['product_id'];
                              // $json[$productkey]['product_name'] = $productvalue['product']['product_name'];
                              $json[$productkey]['batch_no'] = $productvalue['batch_no'];
                          }
                          else
                          {
                              $json[$productkey]['label'] = $productvalue['product']['supplier_barcode'].'_'.$productvalue['product']['product_name'].'_'.$productvalue['batch_no'];
                              $json[$productkey]['product_id'] = $productvalue['product']['product_id'];
                              // $json[$productkey]['product_name'] = $productvalue['product']['product_name'];
                              $json[$productkey]['batch_no'] = $productvalue['batch_no'];
                          }

                      }
                      else
                      {
                         if($productvalue['product']['item_type']==3)
                          {
                              $json[$productkey]['label'] = $productvalue['batch_no'];
                              $json[$productkey]['product_id'] = $productvalue['product']['product_id'];
                              // $json[$productkey]['product_name'] = $productvalue['product']['product_name'];
                              $json[$productkey]['batch_no'] = $productvalue['batch_no'];
                          }
                          else
                          {
                              $json[$productkey]['label'] = $productvalue['product']['product_system_barcode'].'_'.$productvalue['product']['product_name'].'_'.$productvalue['batch_no'];
                              $json[$productkey]['product_id'] = $productvalue['product']['product_id'];
                              // $json[$productkey]['product_name'] = $productvalue['product']['product_name'];
                              $json[$productkey]['batch_no'] = $productvalue['batch_no'];
                          }
                      }



                }
            }
           if(sizeof($sresult) != 0)
            {

               foreach($sresult as $sproductkey=>$sproductvalue){

                        foreach($sproductvalue['reportprice_master'] as $psproductkey=>$psproductvalue){

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
                                    // $json[$sproductkey][$psproductkey]['product_name'] = $sproductvalue['product_name'];
                                    $json[$sproductkey][$psproductkey]['batch_no'] = $psproductvalue['batch_no'];


                            }
                            else
                            {
                                $json[$sproductkey][$psproductkey]['label'] = $showbarcode.'_'.$sproductvalue['product_name'].'_'.$psproductvalue['batch_no'];
                                $json[$sproductkey][$psproductkey]['product_id'] = $sproductvalue['product_id'];
                                // $json[$sproductkey][$psproductkey]['product_name'] = $sproductvalue['product_name'];
                                $json[$sproductkey][$psproductkey]['batch_no'] = $psproductvalue['batch_no'];

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
    public function manualproduct_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->search_val !='')
        {

            $json = [];
            $sales_bill_id    =   $request->sales_bill_id;


            $result  =  product::select('product_name','product_system_barcode','supplier_barcode','product_id','hsn_sac_code','product_id')
                        ->where('item_type','!=',2)
                        ->where(function($query) use ($request,$sales_bill_id)
                        {
                            $query->where('product_name', 'LIKE', "%$request->search_val%")
                                ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
                                ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%")
                                ->orWhere('hsn_sac_code', 'LIKE', "%$request->search_val%");
                        })
                        ->with([
                            'sales_product_detail' => function($fquery) use ($sales_bill_id) {
                                $fquery->where('company_id',Auth::user()->company_id);
                                $fquery->where('sales_bill_id',$sales_bill_id);
                                $fquery->where('product_type',1);
                                $fquery->with('batchprice_master');
                            }
                        ])
                        ->get();




                          $ik = 0;
             if(sizeof($result) != 0)
             {

                foreach($result as $productkey=>$productvalue){

                    foreach($productvalue['sales_product_detail'] as $psproductkey=>$psproductvalue){

                              if($psproductvalue['batchprice_master']['batch_no']!='' || $psproductvalue['batchprice_master']['batch_no']!= NULL)
                              {
                                    $batch_no = '_'.$psproductvalue['batchprice_master']['batch_no'];

                              }
                              else
                              {
                                   $batch_no = '';
                              }

                              if (preg_match("/$request->search_val/", $productvalue['supplier_barcode']))
                             {
                                   $json[$ik]['label'] = $productvalue['supplier_barcode'].'_'.$productvalue['product_name'].$batch_no;
                                   $json[$ik]['product_id'] = $productvalue['product_id'];
                                   $json[$ik]['sales_products_detail_id'] = $psproductvalue['sales_products_detail_id'];
                                   $ik++;
                             }
                             else if(preg_match("/$request->search_val/", $productvalue['product_system_barcode']))
                             {
                                   $json[$ik]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'].$batch_no;
                                   $json[$ik]['product_id'] = $productvalue['product_id'];
                                   $json[$ik]['sales_products_detail_id'] = $psproductvalue['sales_products_detail_id'];
                                   $ik++;
                             }
                             else if(preg_match("/$request->search_val/", $productvalue['product_name']))
                             {
                                   $json[$ik]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'].$batch_no;
                                   $json[$ik]['product_id'] = $productvalue['product_id'];
                                   $json[$ik]['sales_products_detail_id'] = $psproductvalue['sales_products_detail_id'];
                                   $ik++;
                             }


                           $ik++;
                    }
                 }
              }


                // foreach($result as $key=>$value)
                // {
                //   echo '<pre>';
                //   print_r($value['sales_product_detail']['batchprice_master']);
                // }

                // exit;



            // $result = product::select('product_name','product_system_barcode','supplier_barcode','product_id','hsn_sac_code','product_id')
            //     ->where('item_type','!=',2)
            //     ->where(function($query) use ($request)
            //     {
            //         $query->where('product_name', 'LIKE', "%$request->search_val%")
            //             ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
            //             ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%")
            //             ->orWhere('hsn_sac_code', 'LIKE', "%$request->search_val%");
            //     })
            //     ->with('reportprice_master')
            //     ->whereHas('reportprice_master',function ($q) {
            //       $q->where('company_id',Auth::user()->company_id);
            //      })->take(10)->get();


            //$ik = 0;
            //  if(sizeof($result) != 0)
            //  {

            //     foreach($result as $productkey=>$productvalue){


            //          if (preg_match("/$request->search_val/", $productvalue['supplier_barcode']))
            //          {
            //                $json[$ik]['label'] = $productvalue['supplier_barcode'].'_'.$productvalue['product_name'];
            //                $json[$ik]['product_id'] = $productvalue['product_id'];
            //                $ik++;
            //          }
            //          if (preg_match("/$request->search_val/", $productvalue['product_system_barcode']))
            //          {
            //            $json[$ik]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'];
            //             $json[$ik]['product_id'] = $productvalue['product_id'];
            //             $ik++;
            //          }






            //     }
            // }

             return json_encode($json);

        }
        else
        {
           $json = [];
           return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));

        }


    }
    public function manualproduct_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $returndate    =   company_profile::select('return_days','billtype')
                              ->where('company_id',Auth::user()->company_id)->get();


           $returndays    =    $returndate[0]['return_days'];
           $bill_type     =    $returndate[0]['billtype'];


           if($returndays == '' || $returndays == null)
           {

              return json_encode(array("Success"=>"False","Message"=>"Return Policy is not Defined. Please update it through Company Profile"));
           }
           else
           {
                $product_id   =   $request->product_id;
                $sales_bill_id      =   $request->sales_bill_id;
                $sales_products_detail_id  = $request->sales_products_detail_id;
                $todaydate    =   date("Y-m-d");

                $sales_bill   =   sales_bill::select('sales_bill_id')
                                  ->where('company_id',Auth::user()->company_id)
                                  ->where('sales_bill_id',$sales_bill_id)
                                  ->first();
                $result       =   sales_product_detail::where('company_id',Auth::user()->company_id)
                                  ->where('product_id',$product_id)
                                  ->where('sales_bill_id',$sales_bill_id)
                                  ->where('sales_products_detail_id',$sales_products_detail_id)
                                  ->with('return_product_detail')
                                  ->with('product')
                                  ->with(['return_product_detail' => function ($query) {
                                        $query->select('sales_products_detail_id','qty', 'inwardids','inwardqtys');
                                    }])
                                  ->withCount([
                                    'return_product_detail as totalreturnqty' => function($fquery) {
                                        $fquery->select(DB::raw('SUM(qty)'));
                                    }
                                    ])
                                   ->with(['batchprice_master' => function ($bquery) {
                                        $bquery->select('price_master_id','batch_no');
                                    }])
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




                if($sales_bill == null)
                {
                    return json_encode(array("Success"=>"False","Message"=>"Sales return not Allowed after ".$returndays." Days from Purchase date"));
                }
                elseif(sizeof($result) == 0)
                {
                    return json_encode(array("Success"=>"False","Message"=>"Wrong Barcode scanned as per the Bill no. entered"));
                }
                else
                {
                    if($result[0]['qty']==$result[0]['totalreturnqty'])
                    {
                      return json_encode(array("Success"=>"False","Message"=>"Product has already been returned against this bill"));
                    }
                    else
                    {
                      return json_encode(array("Success"=>"True","Data"=>$result,"Billtype"=>$bill_type));
                    }

                }



            }



    }
    public function manualconsignproduct_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->search_val !='')
        {

            $json = [];
            $sales_bill_id    =   $request->sales_bill_id;


            $result  =  product::select('product_name','product_system_barcode','supplier_barcode','product_id','hsn_sac_code','product_id')
                        ->where('item_type','!=',2)
                        ->where(function($query) use ($request,$sales_bill_id)
                        {
                            $query->where('product_name', 'LIKE', "%$request->search_val%")
                                ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
                                ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%")
                                ->orWhere('hsn_sac_code', 'LIKE', "%$request->search_val%");
                        })
                        ->with([
                            'consign_products_detail' => function($fquery) use ($sales_bill_id) {
                                $fquery->where('company_id',Auth::user()->company_id);
                                $fquery->where('consign_bill_id',$sales_bill_id);
                                $fquery->where('product_type',1);
                                $fquery->with('batchprice_master');
                            }
                        ])
                        ->get();




                          $ik = 0;
             if(sizeof($result) != 0)
             {

                foreach($result as $productkey=>$productvalue){

                    foreach($productvalue['consign_products_detail'] as $psproductkey=>$psproductvalue){

                              if($psproductvalue['batchprice_master']['batch_no']!='' || $psproductvalue['batchprice_master']['batch_no']!= NULL)
                              {
                                    $batch_no = '_'.$psproductvalue['batchprice_master']['batch_no'];

                              }
                              else
                              {
                                   $batch_no = '';
                              }

                              if (preg_match("/$request->search_val/", $productvalue['supplier_barcode']))
                             {
                                   $json[$ik]['label'] = $productvalue['supplier_barcode'].'_'.$productvalue['product_name'].$batch_no;
                                   $json[$ik]['product_id'] = $productvalue['product_id'];
                                   $json[$ik]['consign_products_detail_id'] = $psproductvalue['consign_products_detail_id'];
                                   $ik++;
                             }
                             else if(preg_match("/$request->search_val/", $productvalue['product_system_barcode']))
                             {
                                   $json[$ik]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'].$batch_no;
                                   $json[$ik]['product_id'] = $productvalue['product_id'];
                                   $json[$ik]['consign_products_detail_id'] = $psproductvalue['consign_products_detail_id'];
                                   $ik++;
                             }
                             else if(preg_match("/$request->search_val/", $productvalue['product_name']))
                             {
                                   $json[$ik]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'].$batch_no;
                                   $json[$ik]['product_id'] = $productvalue['product_id'];
                                   $json[$ik]['consign_products_detail_id'] = $psproductvalue['consign_products_detail_id'];
                                   $ik++;
                             }


                           $ik++;
                    }
                 }
              }


             return json_encode($json);

        }
        else
        {
           $json = [];
           return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));

        }


    }
    public function manualconsignproduct_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $returndate    =   company_profile::select('return_days','billtype')
                              ->where('company_id',Auth::user()->company_id)->get();


           $returndays    =    $returndate[0]['return_days'];
           $bill_type     =    $returndate[0]['billtype'];


           // if($returndays == '' || $returndays == null)
           // {

           //    return json_encode(array("Success"=>"False","Message"=>"Return Policy is not Defined. Please update it through Company Profile"));
           // }
           // else
           // {
                $product_id   =   $request->product_id;
                $sales_bill_id      =   $request->sales_bill_id;
                $sales_products_detail_id  = $request->sales_products_detail_id;
                $todaydate    =   date("Y-m-d");

                $sales_bill   =   consign_bill::select('consign_bill_id')
                                  ->where('company_id',Auth::user()->company_id)
                                  ->where('consign_bill_id',$sales_bill_id)
                                  ->first();
                $result       =   consign_products_detail::where('company_id',Auth::user()->company_id)
                                  ->where('product_id',$product_id)
                                  ->where('consign_bill_id',$sales_bill_id)
                                  ->where('consign_products_detail_id',$sales_products_detail_id)
                                  ->with('return_product_detail')
                                  ->with('product')
                                  ->with(['return_product_detail' => function ($query) {
                                        $query->select('consign_products_detail_id','qty', 'inwardids','inwardqtys');
                                    }])
                                  ->withCount([
                                    'return_product_detail as totalreturnqty' => function($fquery) {
                                        $fquery->select(DB::raw('SUM(qty)'));
                                    }
                                    ])
                                   ->withCount([
                                    'sales_product_detail as totalbillqty' => function($fquery) {
                                        $fquery->select(DB::raw('SUM(qty)'));
                                    }
                                    ])
                                   ->with(['batchprice_master' => function ($bquery) {
                                        $bquery->select('price_master_id','batch_no');
                                    }])
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




                if($sales_bill == null)
                {
                    return json_encode(array("Success"=>"False","Message"=>"Sales return not Allowed after ".$returndays." Days from Purchase date"));
                }
                elseif(sizeof($result) == 0)
                {
                    return json_encode(array("Success"=>"False","Message"=>"Wrong Barcode scanned as per the Bill no. entered"));
                }
                else
                {
                    if($result[0]['qty']==($result[0]['totalreturnqty'] + $result[0]['totalbillqty']))
                    {
                      return json_encode(array("Success"=>"False","Message"=>"Product has already been returned or Billed against this Consign Challan"));
                    }
                    else
                    {
                      return json_encode(array("Success"=>"True","Data"=>$result,"Billtype"=>$bill_type));
                    }

                }



    }
    public function returncustomer_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $returndate    =   company_profile::select('return_days','billtype')
                              ->where('company_id',Auth::user()->company_id)->get();


           $returndays    =    $returndate[0]['return_days'];
           $bill_type     =    $returndate[0]['billtype'];


              $to_date       =    date("Y-m-d");

              $date          =    date_create($to_date);

              date_sub($date,date_interval_create_from_date_string("".$returndays." days"));

              $from_date      =   date_format($date,"Y-m-d");
              $sales_bill_id = $request->sales_bill_id;

              $bill_data = sales_bill::where([
                  ['sales_bill_id','=',$sales_bill_id],
                  ['company_id',Auth::user()->company_id]])
                  ->whereRaw("Date(sales_bills.created_at) between '$from_date' and '$to_date'")
                  ->with('customer')
                  ->with('customer_address_detail')
                  ->with('reference')
                  ->with('sales_bill_payment_detail.payment_method')
                  ->with('customer_creditaccount')
                  ->where('sales_type','=',1)
                  // ->whereNull('consign_bill_id')
                  ->select('*')
                  ->first();

              $billproduct_data = sales_product_detail::where([
                  ['sales_bill_id','=',$bill_data['sales_bill_id']],
                  ['company_id',Auth::user()->company_id]])
                  //  ->with(['batchprice_master' => function ($bquery) {
                  //       $bquery->select('price_master_id','batch_no');
                  //   }])
                   ->with(['product' => function ($pquery) {
                        $pquery->select('product_id','product_name', 'product_system_barcode','supplier_barcode','sku_code');
                    }])
                  // ->with(['return_product_detail' => function ($query) {
                  //       $query->select('sales_products_detail_id','qty', 'inwardids','inwardqtys');
                  //   }])
                  ->withCount([
                    'return_product_detail as totalreturnqty' => function($fquery) {
                        $fquery->select(DB::raw('SUM(qty)'));
                    }
                    ])
                   ->withCount([
                    'return_product_detail as totalreccharges' => function($fquery) {
                        $fquery->select(DB::raw('SUM(mrp)'));
                        $fquery->where('product_type','=',2);
                    }
                    ])
                  ->where('product_type',2)
                  ->get();


                return json_encode(array("Success"=>"True","Data"=>$bill_data,"Billtype"=>$bill_type,"ProductData"=>$billproduct_data));


    }
    public function returnconsigncustomer_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $returndate    =   company_profile::select('return_days','billtype')
                              ->where('company_id',Auth::user()->company_id)->get();


           $returndays    =    $returndate[0]['return_days'];
           $bill_type     =    $returndate[0]['billtype'];


              $sales_bill_id = $request->sales_bill_id;

              $bill_data = consign_bill::where([
                  ['consign_bill_id','=',$sales_bill_id],
                  ['company_id',Auth::user()->company_id]])
                  ->with('customer')
                  ->with('customer_address_detail')
                  ->with('reference')
                  ->select('*')
                  ->first();

              $billproduct_data = consign_products_detail::where([
                  ['consign_bill_id','=',$bill_data['consign_bill_id']],
                  ['company_id',Auth::user()->company_id]])
                   ->with(['product' => function ($pquery) {
                        $pquery->select('product_id','product_name', 'product_system_barcode','supplier_barcode','sku_code');
                    }])
                  // ->withCount([
                  //   'return_product_detail as totalreturnqty' => function($fquery) {
                  //       $fquery->select(DB::raw('SUM(qty)'));
                  //   }
                  //   ])
                   // ->withCount([
                   //  'return_product_detail as totalreccharges' => function($fquery) {
                   //      $fquery->select(DB::raw('SUM(mrp)'));
                   //      $fquery->where('product_type','=',2);
                   //  }
                   //  ])
                  ->where('product_type',2)
                  ->get();


                return json_encode(array("Success"=>"True","Data"=>$bill_data,"Billtype"=>$bill_type,"ProductData"=>$billproduct_data));


    }
    public function returnbill_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $returndate    =   company_profile::select('return_days','billtype')
                              ->where('company_id',Auth::user()->company_id)->get();


           $returndays    =    $returndate[0]['return_days'];
           $bill_type     =    $returndate[0]['billtype'];


           if($returndays == '' || $returndays == null)
           {

              return json_encode(array("Success"=>"False","Message"=>"Return Policy is not Defined. Please update it through Company Profile"));
           }
           else
           {

                    $to_date       =    date("Y-m-d");

                    $date          =    date_create($to_date);

                    date_sub($date,date_interval_create_from_date_string("".$returndays." days"));

                    $from_date      =   date_format($date,"Y-m-d");
                    $bill_no = $request->bill_no;

              $bill_data = sales_bill::where([
                  ['sales_bill_id','=',$bill_no],
                  ['company_id',Auth::user()->company_id]])
                  ->whereRaw("Date(sales_bills.created_at) between '$from_date' and '$to_date'")
                  ->with('customer')
                  ->with('customer_address_detail')
                  ->with('reference')
                  ->with('sales_bill_payment_detail.payment_method')
                  ->with('customer_creditaccount')
                  ->where('sales_type','=',1)
                  // ->whereNull('consign_bill_id')
                  ->select('*')
                  ->first();


                   $billproduct_data = sales_product_detail::where([
                  ['sales_bill_id','=',$bill_data['sales_bill_id']],
                  ['company_id',Auth::user()->company_id]])
                   ->with(['batchprice_master' => function ($bquery) {
                        $bquery->select('price_master_id','batch_no');
                    }])
                   ->with(['product' => function ($pquery) {
                        $pquery->select('product_id','product_name', 'product_system_barcode','supplier_barcode','sku_code');
                    }])
                  ->with(['return_product_detail' => function ($query) {
                        $query->select('sales_products_detail_id','qty', 'inwardids','inwardqtys');
                    }])
                  ->withCount([
                    'return_product_detail as totalreturnqty' => function($fquery) {
                        $fquery->select(DB::raw('SUM(qty)'));
                    }
                    ])
                   ->withCount([
                    'return_product_detail as totalreccharges' => function($fquery) {
                        $fquery->select(DB::raw('SUM(mrp)'));
                        $fquery->where('product_type','=',2);
                    }
                    ])
                  ->get();

                  // echo '<pre>';
                  // print_r($billproduct_data);
                  // exit;

                   $product_features =  ProductFeatures::getproduct_feature('');

                   foreach ($billproduct_data AS $bkk => $bvv)
                   {

                        if(isset($bvv['product']['product_features_relationship']) && $bvv['product']['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($bvv['product']['product_features_relationship'][$html_id] != '' && $bvv['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$bvv['product']['product_features_relationship'][$html_id]);
                                    $bvv['product'][$html_id] =$nm;



                                }
                            }
                        }
                    }




// echo '<pre>';
// print_r($billproduct_data);
// echo '</pre>';
// exit;

                if($bill_data == null)
                {
                    return json_encode(array("Success"=>"Null","Message"=>"Sales return not Allowed after ".$returndays." Days from Purchase date"));
                }
                else
                {
                    return json_encode(array("Success"=>"True","Data"=>$bill_data,"ProductData"=>$billproduct_data,"Billtype"=>$bill_type));
                }



            }


    }
    public function returnconsignbill_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $returndate    =   company_profile::select('return_days','billtype')
                              ->where('company_id',Auth::user()->company_id)->get();


           $returndays    =    $returndate[0]['return_days'];
           $bill_type     =    $returndate[0]['billtype'];


           // if($returndays == '' || $returndays == null)
           // {

           //    return json_encode(array("Success"=>"False","Message"=>"Return Policy is not Defined. Please update it through Company Profile"));
           // }
           // else
           // {

                    $to_date       =    date("Y-m-d");

                    $date          =    date_create($to_date);

                    date_sub($date,date_interval_create_from_date_string("".$returndays." days"));

                    $from_date      =   date_format($date,"Y-m-d");
                    $bill_no = $request->bill_no;

              $bill_data = consign_bill::where([
                  ['consign_bill_id','=',$bill_no],
                  ['company_id',Auth::user()->company_id]])
                  // ->whereRaw("Date(sales_bills.created_at) between '$from_date' and '$to_date'")
                  ->with('customer')
                  ->with('customer_address_detail')
                  ->select('*')
                  ->first();


                   $billproduct_data = consign_products_detail::where([
                  ['consign_bill_id','=',$bill_data['consign_bill_id']],
                  ['company_id',Auth::user()->company_id]])
                   ->with(['batchprice_master' => function ($bquery) {
                        $bquery->select('price_master_id','batch_no');
                    }])
                   ->with(['product' => function ($pquery) {
                        $pquery->select('product_id','product_name', 'product_system_barcode','supplier_barcode','sku_code');
                    }])
                  ->with(['return_product_detail' => function ($query) {
                        $query->select('consign_products_detail_id','qty', 'inwardids','inwardqtys');
                    }])
                  ->withCount([
                    'return_product_detail as totalreturnqty' => function($fquery) {
                        $fquery->select(DB::raw('SUM(qty)'));
                    }
                    ])
                   ->withCount([
                    'sales_product_detail as totalbillqty' => function($fquery) {
                        $fquery->select(DB::raw('SUM(qty)'));
                    }
                    ])
                   ->withCount([
                    'return_product_detail as totalreccharges' => function($fquery) {
                        $fquery->select(DB::raw('SUM(mrp)'));
                        $fquery->where('product_type','=',2);
                    }
                    ])
                  ->get();

                  // echo '<pre>';
                  // print_r($billproduct_data);
                  // exit;

                   $product_features =  ProductFeatures::getproduct_feature('');

                   foreach ($billproduct_data AS $bkk => $bvv)
                   {

                        if(isset($bvv['product']['product_features_relationship']) && $bvv['product']['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($bvv['product']['product_features_relationship'][$html_id] != '' && $bvv['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$bvv['product']['product_features_relationship'][$html_id]);
                                    $bvv['product'][$html_id] =$nm;



                                }
                            }
                        }
                    }




// echo '<pre>';
// print_r($billproduct_data);
// echo '</pre>';
// exit;

                if($bill_data == null)
                {
                    return json_encode(array("Success"=>"Null","Message"=>"Sales return not Allowed after ".$returndays." Days from Purchase date"));
                }
                else
                {
                    return json_encode(array("Success"=>"True","Data"=>$bill_data,"ProductData"=>$billproduct_data,"Billtype"=>$bill_type));
                }



            // }


    }
    public function returnbillsecond_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $returndate    =   company_profile::select('return_days','billtype')
                              ->where('company_id',Auth::user()->company_id)->get();

           $returndays    =    $returndate[0]['return_days'];
           $bill_type     =    $returndate[0]['billtype'];


           if($returndays == '' || $returndays == null)
           {

              return json_encode(array("Success"=>"False","Message"=>"Return Policy is not Defined in Master"));
           }
           else
           {
                   $product_id = $request->product_id;
                   $batch_no = $request->batch_no;



                    $query = sales_product_detail::select('sales_bill_id')
                    ->where('company_id',Auth::user()->company_id)
                    ->where('deleted_at','=',NULL);

                    $to_date       =    date("Y-m-d");

                    $date          =    date_create($to_date);

                    date_sub($date,date_interval_create_from_date_string("".$returndays." days"));

                    $from_date      =   date_format($date,"Y-m-d");



                    if($product_id!='' && $batch_no=='')
                    {
                       $query->where('product_id',$product_id);
                    }
                    else
                    {
                         $priceid = price_master::where('company_id', Auth::user()->company_id)
                          ->where('deleted_at', '=', NULL)
                          ->where('batch_no', $batch_no)
                          ->where('product_id',$product_id)
                          //->where('product_qty','>',0)
                          ->first();
                          //print_r($priceid['price_master_id']);

                          $query->where('price_master_id',$priceid['price_master_id']);
                    }
                    if($from_date!='' && $to_date!='')
                    {
                    $query->whereRaw("Date(sales_product_details.created_at) between '$from_date' and '$to_date'");
                    }

                    $salesid = $query->get();

                    // echo '<pre>';
                    // print_r($salesid);
                    // exit;


                    $bill_data = sales_bill::where('company_id',Auth::user()->company_id)
                    ->whereIn('sales_bill_id',$salesid)
                    ->with('customer')
                    ->with('sales_product_detail.product')
                    ->with('customer_creditaccount')
                    ->where('sales_type','=',1)
                    // ->whereNull('consign_bill_id')
                    ->select('*')
                    ->get();

                    if(sizeof($bill_data)!=0)
                    {
                        return json_encode(array("Success"=>"True","Data"=>$bill_data));
                    }
                    else
                    {
                        return json_encode(array("Success"=>"False","ReturnDays"=>$returndays));
                    }


            }


    }
    public function returnconsignbillsecond_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $returndate    =   company_profile::select('return_days','billtype')
                              ->where('company_id',Auth::user()->company_id)->get();

           $returndays    =    $returndate[0]['return_days'];
           $bill_type     =    $returndate[0]['billtype'];


           // if($returndays == '' || $returndays == null)
           // {

           //    return json_encode(array("Success"=>"False","Message"=>"Return Policy is not Defined in Master"));
           // }
           // else
           // {
                   $product_id = $request->product_id;
                   $batch_no = $request->batch_no;



                    $query = consign_products_detail::select('consign_bill_id')
                    ->where('company_id',Auth::user()->company_id)
                    ->where('deleted_at','=',NULL);

                    $to_date       =    date("Y-m-d");

                    $date          =    date_create($to_date);

                    date_sub($date,date_interval_create_from_date_string("".$returndays." days"));

                    $from_date      =   date_format($date,"Y-m-d");



                    if($product_id!='' && $batch_no=='')
                    {
                       $query->where('product_id',$product_id);
                    }
                    else
                    {
                         $priceid = product_price_master::where('company_id', Auth::user()->company_id)
                          ->where('deleted_at', '=', NULL)
                          ->where('batch_no', $batch_no)
                          ->where('product_id',$product_id)
                          ->first();


                          $query->where('price_master_id',$priceid['price_master_id']);
                    }
                    // if($from_date!='' && $to_date!='')
                    // {
                    // $query->whereRaw("Date(sales_product_details.created_at) between '$from_date' and '$to_date'");
                    // }

                    $salesid = $query->get();




                    $bill_data = consign_bill::where('company_id',Auth::user()->company_id)
                    ->whereIn('consign_bill_id',$salesid)
                    ->with('customer')
                    ->with('consign_products_detail.product')
                    ->select('*')
                    ->get();

                    if(sizeof($bill_data)!=0)
                    {
                        return json_encode(array("Success"=>"True","Data"=>$bill_data));
                    }
                    else
                    {
                        return json_encode(array("Success"=>"False","ReturnDays"=>$returndays));
                    }


            // }


    }
    public function returnbilling_create(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $data = $request->all();

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;


        $created_by = $userId;

       $cstate_id = company_profile::select('state_id','decimal_points','credit_receipt_prefix','series_type')
                ->where('company_id',Auth::user()->company_id)->get();


         if($data[1]['customer_id'] == '')
         {
              $state_id   =    $cstate_id[0]['state_id'];
         }
         else{

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

  try {
    DB::beginTransaction();


         //$state_id = customer_address_detail::select('state_id')->where('company_id',Auth::user()->company_id)->where('customer_id','=',$data[1]['customer_id'])->first();

          return_bill::where('return_bill_id',$data[1]['return_bill_id'])->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
          ));


        $sales = return_bill::updateOrCreate(
            ['return_bill_id' => $data[1]['return_bill_id'], 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
            'sales_bill_id'=>$data[1]['sales_bill_id'],
            'consign_bill_id'=>$data[1]['consign_bill_id'],
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
                'total_cgst_amount'=>$data[1]['total_cgst'],
                'total_sgst_amount'=>$data[1]['total_sgst'],
                'gross_total'=>$data[1]['grand_total'],
                'shipping_charges'=>$data[1]['charges_total'],
                'total_bill_amount'=>$data[1]['ggrand_total'],
                'official_note'=>$data[1]['official_note'],
                'print_note'=>$data[1]['print_note'],
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );


       $return_bill_id = $sales->return_bill_id;



       return_product_detail::where('return_bill_id',$return_bill_id)->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));


        $productdetail     =    array();
        $returnproductdetail     =    array();


         foreach($data[0] AS $billkey=>$billvalue)
          {

               if($billvalue['barcodesel']!='')
              {


                      $halfgstper      =     $billvalue['prodgstper']/2;
                      $halfgstamt      =     $billvalue['prodgstamt']/2;
                      // $productdetail['bill_date']                            =    $invoice_date;
                      $productdetail['consign_products_detail_id']           =    $billvalue['consign_product_id'];
                      $productdetail['sales_products_detail_id']             =    $billvalue['sales_product_id'];
                      $productdetail['product_id']                           =    $billvalue['productid'];
                      $productdetail['price_master_id']                      =    $billvalue['price_master_id'];
                      $productdetail['qty']                                  =    $billvalue['qty'];
                      $productdetail['mrp']                                  =    $billvalue['mrp'];
                      $productdetail['sellingprice_before_discount']         =    $billvalue['sellingprice_before_discount'];
                      $productdetail['discount_percent']                     =    $billvalue['discount_percent'];
                      $productdetail['discount_amount']                      =    $billvalue['discount_amount'];
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




                      $returnproductdetail['return_date']                          =    $invoice_date;
                      $returnproductdetail['product_id']                           =    $billvalue['productid'];
                      $returnproductdetail['price_master_id']                      =    $billvalue['price_master_id'];
                      $returnproductdetail['qty']                                  =    $billvalue['qty'];

                      $returnproductdetail['created_by']                           =     Auth::User()->user_id;

                      $oldinwardids       =     explode(',',substr($billvalue['inwardids'],0,-1));
                      $oldinwardqtys      =     explode(',',substr($billvalue['inwardqtys'],0,-1));
                      //print_r($oldinwardids);

                      $restqty            =    $billvalue['qty'];
                      $ccount    =   0;
                       $icount    =   0;
                       $pcount    =   0;
                       $done      =   0;
                       $firstout  =   0;
                      $rinwardqtys        =    '';
                      $rinwardids        =    '';

                      foreach($oldinwardids as $l=>$lval)
                      {
                        //echo $oldinwardids[$l];

                        if($oldinwardqtys[$l] >= $restqty && $firstout==0)
                            {
                                  if($done == 0)
                                  {

                                    //echo 'hello';

                                          $rinwardids    .=   $oldinwardids[$l].',';
                                          $rinwardqtys   .=   $restqty.',';

                                          $pcount++;
                                          $done++;
                                 }
                           }
                           else
                           {
                              if($pcount==0 && $done == 0 && $icount==0)
                              {


                                  if($restqty  > $oldinwardqtys[$l])
                                  {
                                    //echo 'bbb';
                                    //echo $restqty;
                                      $rinwardids    .=   $oldinwardids[$l].',';
                                      $rinwardqtys   .=   $oldinwardqtys[$l].',';
                                      $ccount         =   $restqty  - $oldinwardqtys[$l];

                                  }
                                  else
                                  {
                                    //echo 'ccc';
                                    //echo $restqty;
                                      $rinwardids    .=   $oldinwardids[$l].',';
                                      $rinwardqtys   .=   $restqty.',';
                                      $ccount         =   $restqty  - $oldinwardqtys[$l];

                                  }


                                   if($ccount > 0)
                                    {
                                       $firstout++;
                                       $restqty   =   $restqty  - $oldinwardqtys[$l];


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

                    // echo $rinwardids.'_______________'.$rinwardqtys;
                    // echo '<br>';


                  $productdetail['inwardids']                          =    $rinwardids;
                  $productdetail['inwardqtys']                         =    $rinwardqtys;
                  $returnproductdetail['inwardids']                    =    $rinwardids;
                  $returnproductdetail['inwardqtys']                   =    $rinwardqtys;


                   $billproductdetail = return_product_detail::updateOrCreate(
                   ['return_bill_id' => $return_bill_id,
                    'company_id'=>$company_id,'return_product_detail_id'=>$billvalue['return_product_id'],],
                   $productdetail);

                   $return_product_detail_id = $billproductdetail->return_product_detail_id;


                   // $returnbillproductdetail = returnbill_product::updateOrCreate(
                   // ['company_id'=>$company_id,'return_product_detail_id'=>$return_product_detail_id,],
                   // $returnproductdetail);







      }



    }

      // exit;

     $chargesdetail     =    array();



         foreach($data[3] AS $chargeskey=>$chargesvalue)
          {
             if(!empty($chargesvalue))
             {
               if($chargesvalue['chargesamt']!='')
              {

                    if($chargesvalue['returnchargesamt']>0 && $chargesvalue['returnchargesamt']!='')
                    {
                      $halfgstper      =     $chargesvalue['csprodgstper']/2;
                      $halfgstamt      =     $chargesvalue['csprodgstamt']/2;
                      // $chargesdetail['bill_date']                            =    $invoice_date;
                      $chargesdetail['sales_products_detail_id']             =    $chargesvalue['csales_product_id'];
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


                 $billchargesdetail = return_product_detail::updateOrCreate(
                   ['return_bill_id' => $return_bill_id,
                    'company_id'=>$company_id,'return_product_detail_id'=>$chargesvalue['creturn_product_id'],],
                   $chargesdetail);
               }
      }
    }
  }



  if($data[1]['return_type']==1)
  {



      return_bill_payment::where('return_bill_id',$return_bill_id)->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
            'total_bill_amount'=>0
        ));


        $last_invoice_id = customer_creditnote::where('company_id',Auth::user()->company_id)->get()->max('customer_creditnote_id');
        $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
        $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

       if($last_invoice_id == '')
        {
            $last_invoice_id = 1;
        }
        else
        {
            $last_invoice_id = $last_invoice_id  + 1;
        }

        $creditnote_no          =       'CRE-'.$last_invoice_id.'/'.$f1.'-'.$f2;

         customer_creditnote::where('customer_creditnote_id',$data[1]['customer_creditnote_id'])->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
          ));

         $credit_amount  = $data[1]['ggrand_total'];

     if($credit_amount > 0)
     {
             $creditid = customer_creditnote::updateOrCreate(
                ['customer_creditnote_id' => $data[1]['customer_creditnote_id'], 'company_id'=>$company_id,],
                ['customer_id'=>$data[1]['customer_id'],
                'sales_bill_id'=>$data[1]['sales_bill_id'],
                'return_bill_id'=>$return_bill_id,
                'creditnote_no'=>$creditnote_no,
                    'creditnote_date'=>$invoice_date,
                    'creditnote_amount'=>$credit_amount,
                    'balance_amount'=>$credit_amount,
                    'created_by' =>$created_by,
                    'is_active' => "1"
                ]
            );



              $customer_creditnote_id = $creditid->customer_creditnote_id;


              $todate       =    date('Y-m-d');

              $newyear      =   date('Y-04-01');

              $newmonth     =   date('Y-m-01');

//////////////////For Credit Number series Year Wise
                if($cstate_id[0]['series_type']==1)
                {

                    $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
                    $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

                   $newseries  =  customer_creditnote::select('creditno_series')
                                  ->where('customer_creditnote_id','<',$customer_creditnote_id)
                                  ->where('company_id',Auth::user()->company_id)
                                  ->orderBy('customer_creditnote_id','DESC')
                                  ->take('1')
                                  ->first();

                    $billseries   = $newseries['creditno_series']+1;

                    $finalinvoiceno          =       $cstate_id[0]['credit_receipt_prefix'].$billseries.'/'.$f1.'-'.$f2;


                }

 //////////////////For Bill series Month Wise
                else
                {
                    if($todate>=$newmonth)
                      {

                          $newseries  =  customer_creditnote::select('creditno_series')
                                                    ->whereRaw("STR_TO_DATE(customer_creditnotes.creditnote_date,'%d-%m-%Y') >= '$newmonth'")
                                                    ->where('customer_creditnote_id','<',$customer_creditnote_id)
                                                    ->orderBy('customer_creditnote_id','DESC')
                                                    ->take('1')
                                                    ->first();


                          if($newseries=='')
                          {
                              $billseries  =  1;
                          }
                          else
                          {
                              $billseries   = $newseries['creditno_series']+1;

                          }


                      }
                      else
                      {
                        $newseries  =  customer_creditnote::select('creditno_series')
                                                    ->whereRaw("STR_TO_DATE(customer_creditnotes.creditnote_date,'%d-%m-%Y') <= '$todate'")
                                                    ->where('customer_creditnote_id','<',$customer_creditnote_id)
                                                    ->orderBy('customer_creditnote_id','DESC')
                                                    ->take('1')
                                                    ->first();
                              $billseries   = $newseries['creditno_series']+1;


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

                      $finalinvoiceno = $cstate_id[0]['credit_receipt_prefix'].$dd.''.$id1;
                }

              customer_creditnote::where('customer_creditnote_id',$customer_creditnote_id)->update(array(
                  'creditnote_no' => $finalinvoiceno,
                  'creditno_series' => $billseries
               ));


              $paymentanswers     =    array();


               foreach($data[2] AS $key=>$value2)
                {

                      $paymentanswers['return_bill_id']                =  $return_bill_id;
                      $paymentanswers['customer_creditnote_id']        =  $customer_creditnote_id;
                      $paymentanswers['total_bill_amount']             =  $value2['value'];
                      $paymentanswers['payment_method_id']             =  $value2['id'];
                      $paymentanswers['created_by']                    =  Auth::User()->user_id;
                      $paymentanswers['deleted_at'] =  NULL;
                      $paymentanswers['deleted_by'] =  NULL;

                 $paymentdetail = return_bill_payment::updateOrCreate(
                     ['return_bill_id' => $return_bill_id,],
                     $paymentanswers);


                }
       }

   //auto use of credit note in Customer credit receipts if bill contains Outstanding amount

        if($data[1]['creditaccountid']!='')
        {

                  $receiptremarks  =  'Credit Receipt against Return';

                  if($data[1]['totalcreditbalance']  >= $credit_amount)
                  {
                      $deductcredit    =  $credit_amount;
                      $creditbalance   =  $data[1]['totalcreditbalance']  - $credit_amount;
                  }
                  else
                  {
                      $deductcredit    =   $data[1]['totalcreditbalance'];
                      $creditbalance   =   0;
                  }



                  $creditreceipt = customer_creditreceipt::updateOrCreate(
                      ['customer_creditreceipt_id' => '', 'company_id'=>$company_id,],
                      ['customer_id'=>$data[1]['customer_id'],
                       'return_bill_id' => $return_bill_id,
                       'receipt_no'=>$data[1]['invoice_no'],
                          'receipt_date'=>$invoice_date,
                          'remarks'=>$creditnote_no,
                          'receiptremarks'=>$receiptremarks,
                          'total_amount'=>$deductcredit,
                          'created_by' =>$created_by,
                          'is_active' => "1"
                      ]
                  );


                 $customer_creditreceipt_id = $creditreceipt->customer_creditreceipt_id;

                  $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
                  $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');



                  $finalinvoiceno          =       'cus-'.$customer_creditreceipt_id.'/'.$f1.'-'.$f2;


                   customer_creditreceipt::where('customer_creditreceipt_id',$customer_creditreceipt_id)->update(array(
                      'receipt_no' => $finalinvoiceno
                   ));


                  $rpaymentanswers     =    array();


                 foreach($data[2] AS $rkey=>$rvalue2)
                  {

                        $rpaymentanswers['customer_creditreceipt_id']     =  $customer_creditreceipt_id;
                        $rpaymentanswers['total_bill_amount']             =  $deductcredit;
                        $rpaymentanswers['payment_method_id']             =  $value2['id'];
                        $rpaymentanswers['created_by']                    =  Auth::User()->user_id;
                        $rpaymentanswers['deleted_at'] =  NULL;
                        $rpaymentanswers['deleted_by'] =  NULL;

                   $rpaymentdetail = customer_crerecp_payment::updateOrCreate(
                       ['customer_crerecp_payment_id' => '',],
                       $rpaymentanswers);


                 }


    $creditreceipt = customer_creditreceipt_detail::updateOrCreate(
            ['customer_creditreceipt_detail_id' => '',],
            ['customer_creditreceipt_id'=>$customer_creditreceipt_id,
            'customer_creditaccount_id'=>$data[1]['creditaccountid'],
                'customer_id'=>$data[1]['customer_id'],
                'credit_amount'=>$data[1]['totalcreditbalance'],
                'payment_amount'=>$deductcredit,
                'balance_amount'=>$creditbalance,
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );





                customer_creditaccount::where('customer_creditaccount_id',$data[1]['creditaccountid'])->update(array('balance_amount' => $creditbalance
              ));





        $creditbalanceamt   =    $credit_amount - $deductcredit;
        $creditnotepayment = creditnote_payment::updateOrCreate(
            ['sales_bill_id' => $data[1]['sales_bill_id'],'return_bill_id' => $return_bill_id, 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
                'customer_creditnote_id'=>$customer_creditnote_id,
                'creditnote_amount'=>$credit_amount,
                'used_amount'=>$deductcredit,
                'balance_amount'=>$creditbalanceamt,
                'created_by' =>$created_by,
                'is_active' => "1",
                'deleted_at' =>NULL,
                'deleted_by' =>NULL,
            ]
        );

          customer_creditnote::where('customer_creditnote_id',$customer_creditnote_id)->update(array(
                      'balance_amount' => $creditbalanceamt
                  ));

    }
  }

  DB::commit();
    } catch (\Illuminate\Database\QueryException $e)
    {
        DB::rollback();
        return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
    }





        if($billproductdetail)
        {

           if($data[1]['return_bill_id'] != '')
          {
              return json_encode(array("Success"=>"True","Message"=>"Billing successfully Update!","url"=>"view_bill"));
          }
          else
          {
              return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>"sales_return"));
          }




        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
        //return back()->withInput();

    }


public function returnbillingprint_create(Request $request)
    {
         Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $data = $request->all();

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;


        $created_by = $userId;

       $cstate_id = company_profile::select('state_id','decimal_points','credit_receipt_prefix','series_type')
                ->where('company_id',Auth::user()->company_id)->get();


         if($data[1]['customer_id'] == '')
         {
              $state_id   =    $cstate_id[0]['state_id'];
         }
         else{

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

  try {
    DB::beginTransaction();


         //$state_id = customer_address_detail::select('state_id')->where('company_id',Auth::user()->company_id)->where('customer_id','=',$data[1]['customer_id'])->first();

          return_bill::where('return_bill_id',$data[1]['return_bill_id'])->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
          ));


        $sales = return_bill::updateOrCreate(
            ['return_bill_id' => $data[1]['return_bill_id'], 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
            'sales_bill_id'=>$data[1]['sales_bill_id'],
            'consign_bill_id'=>$data[1]['consign_bill_id'],
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
                'total_cgst_amount'=>$data[1]['total_cgst'],
                'total_sgst_amount'=>$data[1]['total_sgst'],
                'gross_total'=>$data[1]['grand_total'],
                'shipping_charges'=>$data[1]['charges_total'],
                'total_bill_amount'=>$data[1]['ggrand_total'],
                'official_note'=>$data[1]['official_note'],
                'print_note'=>$data[1]['print_note'],
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );


       $return_bill_id = $sales->return_bill_id;



       return_product_detail::where('return_bill_id',$return_bill_id)->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));


        $productdetail     =    array();
        $returnproductdetail     =    array();


         foreach($data[0] AS $billkey=>$billvalue)
          {

               if($billvalue['barcodesel']!='')
              {


                      $halfgstper      =     $billvalue['prodgstper']/2;
                      $halfgstamt      =     $billvalue['prodgstamt']/2;
                      // $productdetail['bill_date']                            =    $invoice_date;
                      $productdetail['consign_products_detail_id']           =    $billvalue['consign_product_id'];
                      $productdetail['sales_products_detail_id']             =    $billvalue['sales_product_id'];
                      $productdetail['product_id']                           =    $billvalue['productid'];
                      $productdetail['price_master_id']                      =    $billvalue['price_master_id'];
                      $productdetail['qty']                                  =    $billvalue['qty'];
                      $productdetail['mrp']                                  =    $billvalue['mrp'];
                      $productdetail['sellingprice_before_discount']         =    $billvalue['sellingprice_before_discount'];
                      $productdetail['discount_percent']                     =    $billvalue['discount_percent'];
                      $productdetail['discount_amount']                      =    $billvalue['discount_amount'];
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




                      $returnproductdetail['return_date']                          =    $invoice_date;
                      $returnproductdetail['product_id']                           =    $billvalue['productid'];
                      $returnproductdetail['price_master_id']                      =    $billvalue['price_master_id'];
                      $returnproductdetail['qty']                                  =    $billvalue['qty'];

                      $returnproductdetail['created_by']                           =     Auth::User()->user_id;

                      $oldinwardids       =     explode(',',substr($billvalue['inwardids'],0,-1));
                      $oldinwardqtys      =     explode(',',substr($billvalue['inwardqtys'],0,-1));
                      //print_r($oldinwardids);

                      $restqty            =    $billvalue['qty'];
                      $ccount    =   0;
                       $icount    =   0;
                       $pcount    =   0;
                       $done      =   0;
                       $firstout  =   0;
                      $rinwardqtys        =    '';
                      $rinwardids        =    '';

                      foreach($oldinwardids as $l=>$lval)
                      {
                        //echo $oldinwardids[$l];

                        if($oldinwardqtys[$l] >= $restqty && $firstout==0)
                            {
                                  if($done == 0)
                                  {

                                    //echo 'hello';

                                          $rinwardids    .=   $oldinwardids[$l].',';
                                          $rinwardqtys   .=   $restqty.',';

                                          $pcount++;
                                          $done++;
                                 }
                           }
                           else
                           {
                              if($pcount==0 && $done == 0 && $icount==0)
                              {


                                  if($restqty  > $oldinwardqtys[$l])
                                  {
                                    //echo 'bbb';
                                    //echo $restqty;
                                      $rinwardids    .=   $oldinwardids[$l].',';
                                      $rinwardqtys   .=   $oldinwardqtys[$l].',';
                                      $ccount         =   $restqty  - $oldinwardqtys[$l];

                                  }
                                  else
                                  {
                                    //echo 'ccc';
                                    //echo $restqty;
                                      $rinwardids    .=   $oldinwardids[$l].',';
                                      $rinwardqtys   .=   $restqty.',';
                                      $ccount         =   $restqty  - $oldinwardqtys[$l];

                                  }


                                   if($ccount > 0)
                                    {
                                       $firstout++;
                                       $restqty   =   $restqty  - $oldinwardqtys[$l];


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

                    // echo $rinwardids.'_______________'.$rinwardqtys;
                    // echo '<br>';


                  $productdetail['inwardids']                          =    $rinwardids;
                  $productdetail['inwardqtys']                         =    $rinwardqtys;
                  $returnproductdetail['inwardids']                    =    $rinwardids;
                  $returnproductdetail['inwardqtys']                   =    $rinwardqtys;


                   $billproductdetail = return_product_detail::updateOrCreate(
                   ['return_bill_id' => $return_bill_id,
                    'company_id'=>$company_id,'return_product_detail_id'=>$billvalue['return_product_id'],],
                   $productdetail);

                   $return_product_detail_id = $billproductdetail->return_product_detail_id;


//                   $returnbillproductdetail = returnbill_product::updateOrCreate(
//                   ['company_id'=>$company_id,'return_product_detail_id'=>$return_product_detail_id,],
//                   $returnproductdetail);







      }



    }

      // exit;

     $chargesdetail     =    array();



         foreach($data[3] AS $chargeskey=>$chargesvalue)
          {
             if(!empty($chargesvalue))
             {
               if($chargesvalue['chargesamt']!='')
              {

                    if($chargesvalue['returnchargesamt']>0 && $chargesvalue['returnchargesamt']!='')
                    {
                      $halfgstper      =     $chargesvalue['csprodgstper']/2;
                      $halfgstamt      =     $chargesvalue['csprodgstamt']/2;
                      // $chargesdetail['bill_date']                            =    $invoice_date;
                      $chargesdetail['sales_products_detail_id']             =    $chargesvalue['csales_product_id'];
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


                 $billchargesdetail = return_product_detail::updateOrCreate(
                   ['return_bill_id' => $return_bill_id,
                    'company_id'=>$company_id,'return_product_detail_id'=>$chargesvalue['creturn_product_id'],],
                   $chargesdetail);
               }
      }
    }
  }



  if($data[1]['return_type']==1)
  {



      return_bill_payment::where('return_bill_id',$return_bill_id)->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
            'total_bill_amount'=>0
        ));


        $last_invoice_id = customer_creditnote::where('company_id',Auth::user()->company_id)->get()->max('customer_creditnote_id');
        $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
        $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

       if($last_invoice_id == '')
        {
            $last_invoice_id = 1;
        }
        else
        {
            $last_invoice_id = $last_invoice_id  + 1;
        }

        $creditnote_no          =       'CRE-'.$last_invoice_id.'/'.$f1.'-'.$f2;

         customer_creditnote::where('customer_creditnote_id',$data[1]['customer_creditnote_id'])->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
          ));

         $credit_amount  = $data[1]['ggrand_total'];

     if($credit_amount > 0)
     {
             $creditid = customer_creditnote::updateOrCreate(
                ['customer_creditnote_id' => $data[1]['customer_creditnote_id'], 'company_id'=>$company_id,],
                ['customer_id'=>$data[1]['customer_id'],
                'sales_bill_id'=>$data[1]['sales_bill_id'],
                'return_bill_id'=>$return_bill_id,
                'creditnote_no'=>$creditnote_no,
                    'creditnote_date'=>$invoice_date,
                    'creditnote_amount'=>$credit_amount,
                    'balance_amount'=>$credit_amount,
                    'created_by' =>$created_by,
                    'is_active' => "1"
                ]
            );



              $customer_creditnote_id = $creditid->customer_creditnote_id;


              $todate       =    date('Y-m-d');

              $newyear      =   date('Y-04-01');

              $newmonth     =   date('Y-m-01');

//////////////////For Credit Number series Year Wise
                if($cstate_id[0]['series_type']==1)
                {

                    $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
                    $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

                   $newseries  =  customer_creditnote::select('creditno_series')
                                  ->where('customer_creditnote_id','<',$customer_creditnote_id)
                                  ->where('company_id',Auth::user()->company_id)
                                  ->orderBy('customer_creditnote_id','DESC')
                                  ->take('1')
                                  ->first();

                    $billseries   = $newseries['creditno_series']+1;

                    $finalinvoiceno          =       $cstate_id[0]['credit_receipt_prefix'].$billseries.'/'.$f1.'-'.$f2;


                }

 //////////////////For Bill series Month Wise
                else
                {
                    if($todate>=$newmonth)
                      {

                          $newseries  =  customer_creditnote::select('creditno_series')
                                                    ->whereRaw("STR_TO_DATE(customer_creditnotes.creditnote_date,'%d-%m-%Y') >= '$newmonth'")
                                                    ->where('customer_creditnote_id','<',$customer_creditnote_id)
                                                    ->orderBy('customer_creditnote_id','DESC')
                                                    ->take('1')
                                                    ->first();


                          if($newseries=='')
                          {
                              $billseries  =  1;
                          }
                          else
                          {
                              $billseries   = $newseries['creditno_series']+1;

                          }


                      }
                      else
                      {
                        $newseries  =  customer_creditnote::select('creditno_series')
                                                    ->whereRaw("STR_TO_DATE(customer_creditnotes.creditnote_date,'%d-%m-%Y') <= '$todate'")
                                                    ->where('customer_creditnote_id','<',$customer_creditnote_id)
                                                    ->orderBy('customer_creditnote_id','DESC')
                                                    ->take('1')
                                                    ->first();
                              $billseries   = $newseries['creditno_series']+1;


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

                      $finalinvoiceno = $cstate_id[0]['credit_receipt_prefix'].$dd.''.$id1;
                }

              customer_creditnote::where('customer_creditnote_id',$customer_creditnote_id)->update(array(
                  'creditnote_no' => $finalinvoiceno,
                  'creditno_series' => $billseries
               ));


              $paymentanswers     =    array();


               foreach($data[2] AS $key=>$value2)
                {

                      $paymentanswers['return_bill_id']                =  $return_bill_id;
                      $paymentanswers['customer_creditnote_id']        =  $customer_creditnote_id;
                      $paymentanswers['total_bill_amount']             =  $value2['value'];
                      $paymentanswers['payment_method_id']             =  $value2['id'];
                      $paymentanswers['created_by']                    =  Auth::User()->user_id;
                      $paymentanswers['deleted_at'] =  NULL;
                      $paymentanswers['deleted_by'] =  NULL;

                 $paymentdetail = return_bill_payment::updateOrCreate(
                     ['return_bill_id' => $return_bill_id,],
                     $paymentanswers);


                }
       }

   //auto use of credit note in Customer credit receipts if bill contains Outstanding amount

        if($data[1]['creditaccountid']!='')
        {

                  $receiptremarks  =  'Credit Receipt against Return';

                  if($data[1]['totalcreditbalance']  >= $credit_amount)
                  {
                      $deductcredit    =  $credit_amount;
                      $creditbalance   =  $data[1]['totalcreditbalance']  - $credit_amount;
                  }
                  else
                  {
                      $deductcredit    =   $data[1]['totalcreditbalance'];
                      $creditbalance   =   0;
                  }



                  $creditreceipt = customer_creditreceipt::updateOrCreate(
                      ['customer_creditreceipt_id' => '', 'company_id'=>$company_id,],
                      ['customer_id'=>$data[1]['customer_id'],
                       'return_bill_id' => $return_bill_id,
                       'receipt_no'=>$data[1]['invoice_no'],
                          'receipt_date'=>$invoice_date,
                          'remarks'=>$creditnote_no,
                          'receiptremarks'=>$receiptremarks,
                          'total_amount'=>$deductcredit,
                          'created_by' =>$created_by,
                          'is_active' => "1"
                      ]
                  );


                 $customer_creditreceipt_id = $creditreceipt->customer_creditreceipt_id;

                  $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
                  $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');



                  $finalinvoiceno          =       'cus-'.$customer_creditreceipt_id.'/'.$f1.'-'.$f2;


                   customer_creditreceipt::where('customer_creditreceipt_id',$customer_creditreceipt_id)->update(array(
                      'receipt_no' => $finalinvoiceno
                   ));


                  $rpaymentanswers     =    array();


                 foreach($data[2] AS $rkey=>$rvalue2)
                  {

                        $rpaymentanswers['customer_creditreceipt_id']     =  $customer_creditreceipt_id;
                        $rpaymentanswers['total_bill_amount']             =  $deductcredit;
                        $rpaymentanswers['payment_method_id']             =  $value2['id'];
                        $rpaymentanswers['created_by']                    =  Auth::User()->user_id;
                        $rpaymentanswers['deleted_at'] =  NULL;
                        $rpaymentanswers['deleted_by'] =  NULL;

                   $rpaymentdetail = customer_crerecp_payment::updateOrCreate(
                       ['customer_crerecp_payment_id' => '',],
                       $rpaymentanswers);


                 }


    $creditreceipt = customer_creditreceipt_detail::updateOrCreate(
            ['customer_creditreceipt_detail_id' => '',],
            ['customer_creditreceipt_id'=>$customer_creditreceipt_id,
            'customer_creditaccount_id'=>$data[1]['creditaccountid'],
                'customer_id'=>$data[1]['customer_id'],
                'credit_amount'=>$data[1]['totalcreditbalance'],
                'payment_amount'=>$deductcredit,
                'balance_amount'=>$creditbalance,
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );





                customer_creditaccount::where('customer_creditaccount_id',$data[1]['creditaccountid'])->update(array('balance_amount' => $creditbalance
              ));





        $creditbalanceamt   =    $credit_amount - $deductcredit;
        $creditnotepayment = creditnote_payment::updateOrCreate(
            ['sales_bill_id' => $data[1]['sales_bill_id'],'return_bill_id' => $return_bill_id, 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
                'customer_creditnote_id'=>$customer_creditnote_id,
                'creditnote_amount'=>$credit_amount,
                'used_amount'=>$deductcredit,
                'balance_amount'=>$creditbalanceamt,
                'created_by' =>$created_by,
                'is_active' => "1",
                'deleted_at' =>NULL,
                'deleted_by' =>NULL,
            ]
        );

          customer_creditnote::where('customer_creditnote_id',$customer_creditnote_id)->update(array(
                      'balance_amount' => $creditbalanceamt
                  ));

    }
  }
  DB::commit();
    } catch (\Illuminate\Database\QueryException $e)
    {
        DB::rollback();
        return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
    }


        if($billproductdetail)
        {

           if($data[1]['return_bill_id'] != '')
          {
              return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully returned.","url"=>route('print_creditnote', ['id' => encrypt($return_bill_id)]),"burl"=>"sales_return"));
          }
          else
          {
              return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully returned.","url"=>route('print_creditnote', ['id' => encrypt($return_bill_id)]),"burl"=>"sales_return"));
          }




        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
        //return back()->withInput();

    }

   public function edit_returnbill(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $bill_id = decrypt($request->bill_id);

        $creditnoteid      =  return_bill_payment::select('customer_creditnote_id','total_bill_amount')
                                                                ->where('return_bill_id',$bill_id)
                                                                ->where('deleted_at',NULL)->first();

        $creditreceiptid    =  customer_creditreceipt::select('customer_creditreceipt_id','return_bill_id','remarks','total_amount')
                                                    ->where('return_bill_id',$bill_id)
                                                    ->where('deleted_at',NULL)->first();


        $returnproductid      =  return_product_detail::select('return_product_detail_id','return_bill_id')
                                                    ->where('return_bill_id',$bill_id)
                                                    ->where('deleted_at',NULL)->get();

          $count = 0;

          foreach($returnproductid as $returnprodkey=>$returnprodvalue)
          {
              $restockrproduct  =  returnbill_product::where('return_product_detail_id',$returnprodvalue['return_product_detail_id'])
                                                      ->where('deleted_at',NULL)
                                                      ->where('returnstatus',1)
                                                      ->get();

              if(sizeof($restockrproduct)!=0)
              {
                  $count++;
              }

          }

          if($count>0)
          {
              return json_encode(array("Success"=>"False","Message"=>"Products returned in Bill has been restocked, So cannot Edit this bill"));
              exit;
          }


           if($creditreceiptid=='')
           {
               $creditnotepaymentid = creditnote_payment::where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])
                                     ->where('deleted_at',NULL)->first();

                      if($creditnotepaymentid!='')
                      {
                           return json_encode(array("Success"=>"False","Message"=>"Credit Note Generated through this return bill has already been used.. So Bill entry cannot be Edited."));
                            exit;
                      }

           }


        $bill_data = return_bill::where([
            ['return_bill_id','=',$bill_id],
            ['company_id',Auth::user()->company_id]])
            ->with('customer')
            ->with('reference')
            ->with('customer_address_detail')
            ->with('return_product_detail','return_product_detail.product.colour','return_product_detail.product.size','return_product_detail.product.uqc')
            ->with('return_bill_payment.payment_method')
            // ->with('customer_creditaccount')
            // ->with('creditnote_payment.customer_creditnote')
            ->select('*')
            ->first();

      //dd($bill_data);
        return json_encode(array("Success"=>"True","Data"=>$bill_data,"url"=>"sales_return"));


    }




}
