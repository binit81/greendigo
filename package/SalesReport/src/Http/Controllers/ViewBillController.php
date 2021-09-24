<?php
namespace Retailcore\SalesReport\Http\Controllers;
use App\Http\Controllers\Controller;

use Retailcore\SalesReport\Models\view_bill;
use Illuminate\Http\Request;
use Retailcore\SalesReport\Models\viewbill_export;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\SalesReturn\Models\returnbill_product;
use Retailcore\SalesReturn\Models\return_bill_payment;
use Retailcore\Products\Models\product\product;
use Retailcore\Sales\Models\reference;
use Retailcore\Sales\Models\payment_method;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\CreditNote\Models\customer_creditnote;
use Retailcore\CreditBalance\Models\customer_creditaccount;
use Retailcore\CreditNote\Models\creditnote_payment;
use Retailcore\CreditBalance\Models\customer_creditreceipt_detail;
use Retailcore\CreditBalance\Models\customer_creditreceipt;
use Retailcore\CreditBalance\Models\customer_crerecp_payment;
use Retailcore\SalesReport\Models\bill_template;
use Retailcore\Products\Models\product\ProductFeatures;
use App\state;
use App\country;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use App\company;
use Auth;
use App\User;
use Retailcore\Customer\Models\customer\customer;
use Retailcore\Customer\Models\customer\customer_address_detail;
use Retailcore\Consignment\Models\consign_bill;
use Retailcore\Consignment\Models\consign_products_detail;
use Retailcore\Consignment\Models\consign_payment_detail;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;

class ViewBillController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $maxsales_id   =  '';
        $minsales_id   =  '';
        $rmaxsales_id   =  '';
        $rminsales_id   =  '';
        $showedits      =   1;
        $returnsales   =  array();
        $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

         $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

        $compname       =   company::where('company_id',Auth::user()->company_id)    
                                      ->first();                                           
        $companyname    =   $compname['company_name'];         

        $date      =   date("Y-m-d");
      

        $squery = sales_bill::select("sales_bills.*",DB::raw("(SELECT SUM(sales_product_details.discount_amount + sales_product_details.overalldiscount_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id GROUP BY sales_product_details.sales_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(sales_product_details.mrp) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(sales_product_details.igst_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as chargesgst"))
            ->with('reference')
            ->with('user')
            ->with('sales_bill_payment_detail')
            ->where('company_id',Auth::user()->company_id)
            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'")
            // ->whereRaw("Date(sales_bills.created_at) between '$date' and '$date'")
            // ->whereRaw("bill_date between '$sdate' and '$sdate'")
            ->where('deleted_at','=',NULL)
            ->where('is_active','=',1)
            ->orderBy('sales_bill_id', 'DESC');

            $scustom   =   collect();
            $sdata     =   $scustom->merge($squery->get());
            $sales     =   $squery->paginate(10);

          

            $rquery = return_bill::select("return_bills.*",DB::raw("(SELECT SUM(return_product_details.discount_amount + return_product_details.overalldiscount_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id GROUP BY return_product_details.return_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(return_product_details.mrp) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(return_product_details.igst_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as chargesgst"))
            ->whereNull('consign_bill_id')
            ->with('reference')
            ->with('user')
            ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'")
            // ->whereRaw("Date(return_bills.created_at) between '$date' and '$date'")
            // ->whereRaw("bill_date between '$sdate' and '$sdate'")
            ->with('sales_bill')
            ->with('return_bill_payment')
            ->with('customer')
            ->where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->orderBy('return_bill_id', 'DESC');


            $rcustom      =  collect();
            $rdata        =  $rcustom->merge($rquery->get());
            $returnbill   =  $rquery->paginate(5);

                       
            $count = 0;
            $taxabletariff = 0;
            $totalcgst = 0;
            $totalsgst = 0;
            $totaligst = 0;
            $grandtotal = 0;
            $scash  = 0;
            $scard = 0;
            $scheque = 0;
            $swallet = 0;
            $sunpaidamt=0;
            $snetbanking =0;
            $screditnote = 0;

            foreach ($sdata as $totsales)
            {
                $count++;
                
                $halfchargesgst   =   $totsales->chargesgst / 2;
                
                $taxabletariff  +=   $totsales->sellingprice_after_discount + $totsales->totalcharges;

                if($tax_type==1)
                {
                    $totaligst       +=  $totsales->total_igst_amount + $totsales->chargesgst;
                }
                else
                {

                    if($totsales->state_id == $company_state)
                    {
                      $totalcgst       +=  $totsales->total_cgst_amount + $halfchargesgst;
                      $totalsgst       +=  $totsales->total_sgst_amount + $halfchargesgst; 
                    }
                    else
                    {
                      $totaligst       +=  $totsales->total_igst_amount + $totsales->chargesgst;  
                    }
                }
                foreach($totsales['sales_bill_payment_detail'] as $paymentvalue)
                {

                   if($paymentvalue->payment_method_id==1)
                   {
                        $scash    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==2)
                   {
                        $scard    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==3)
                   {
                        $scheque    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==5)
                   {
                        $swallet    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==6)
                   {    
                        $sunpaidamt    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==7)
                   {
                        $snetbanking    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==8)
                   {
                        $screditnote    +=  $paymentvalue->total_bill_amount;
                   }
                }

                                            
                $grandtotal     +=   $totsales->total_bill_amount;
            }


            $rtaxabletariff = 0;
            $rtotalcgst = 0;
            $rtotalsgst = 0;
            $rtotaligst = 0;
            $rgrandtotal = 0;
            $rcash  = 0;
            $rcard = 0;
            $rcheque = 0;
            $rwallet = 0;
            $runpaidamt=0;
            $rnetbanking =0;
            $rcreditnote = 0;

            foreach ($rdata as $rtotsales)
            {
               
                
                $rhalfchargesgst   =   $rtotsales->chargesgst / 2;
                
                $rtaxabletariff  +=   $rtotsales->sellingprice_after_discount + $rtotsales->totalcharges;

                if($tax_type==1)
                {
                    $rtotaligst       +=  $rtotsales->total_igst_amount + $rtotsales->chargesgst; 
                }
                else
                {

                    if($rtotsales->state_id == $company_state)
                    {
                      $rtotalcgst       +=  $rtotsales->total_cgst_amount + $rhalfchargesgst;
                      $rtotalsgst       +=  $rtotsales->total_sgst_amount + $rhalfchargesgst; 
                    }
                    else
                    {
                      $rtotaligst       +=  $rtotsales->total_igst_amount + $rtotsales->chargesgst;  
                    }
                }
                 foreach($rtotsales['return_bill_payment'] as $rpaymentvalue)
                {

                   if($rpaymentvalue->payment_method_id==1)
                   {
                        $rcash    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==2)
                   {
                        $rcard    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==3)
                   {
                        $rcheque    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==5)
                   {
                        $rwallet    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==6)
                   {    
                        $runpaidamt    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==7)
                   {
                        $rnetbanking    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==8)
                   {
                        $rcreditnote    +=  $rpaymentvalue->total_bill_amount;
                   }
                }

                                            
                $rgrandtotal     +=   $rtotsales->total_bill_amount;
            }

            $todaytaxable   =   $taxabletariff - $rtaxabletariff;
            $todaycgst      =   $totalcgst - $rtotalcgst;
            $todaysgst      =   $totalsgst - $rtotalsgst;
            $todayigst      =   $totaligst - $rtotaligst;
            $todaygrand     =   $grandtotal - $rgrandtotal;
            $cash           =   $scash  -  $rcash;
            $card           =   $scard  -  $rcard;
            $cheque         =   $scheque  -  $rcheque;
            $wallet         =   $swallet  - $rwallet;
            $unpaidamt      =   $sunpaidamt - $runpaidamt;
            $netbanking     =   $snetbanking - $rnetbanking;
            $creditnote     =   $screditnote - $rcreditnote;

               $max_date  =  $sdata->max('bill_date');
               $min_date  =  $sdata->min('bill_date');
           

           
        return view('salesreport::view_bill',compact('sales','payment_methods','count','todaytaxable','todaycgst','todaysgst','todayigst','todaygrand','maxsales_id','minsales_id','company_state','returnbill','returnsales','rmaxsales_id','rminsales_id','tax_type','taxname','max_date','min_date','cash','card','cheque','wallet','unpaidamt','netbanking','creditnote','showedits','get_store','companyname'))->render();

        
    }
    public function bill_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

       $maxsales_id   =  '';
        $minsales_id   =  '';
        $rmaxsales_id   =  '';
        $rminsales_id   =  '';
        $showedits      =   1;
        $returnsales   =  array();
        $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

        $date      =   date("Y-m-d");
      

        $squery = sales_bill::select("sales_bills.*",DB::raw("(SELECT SUM(sales_product_details.discount_amount + sales_product_details.overalldiscount_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id GROUP BY sales_product_details.sales_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(sales_product_details.mrp) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(sales_product_details.igst_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as chargesgst"))
            ->with('reference')
            ->with('user')
            ->with('sales_bill_payment_detail')
            ->where('company_id',Auth::user()->company_id)
            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'")
            // ->whereRaw("Date(sales_bills.created_at) between '$date' and '$date'")
            // ->whereRaw("bill_date between '$sdate' and '$sdate'")
            ->where('deleted_at','=',NULL)
            ->where('is_active','=',1)
            ->orderBy('sales_bill_id', 'DESC');

            $scustom   =   collect();
            $sdata     =   $scustom->merge($squery->get());
            $sales     =   $squery->paginate(10);

          

            $rquery = return_bill::select("return_bills.*",DB::raw("(SELECT SUM(return_product_details.discount_amount + return_product_details.overalldiscount_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id GROUP BY return_product_details.return_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(return_product_details.mrp) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(return_product_details.igst_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as chargesgst"))
            ->with('reference')
            ->with('user')
            ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'")
            // ->whereRaw("Date(return_bills.created_at) between '$date' and '$date'")
            // ->whereRaw("bill_date between '$sdate' and '$sdate'")
            ->with('sales_bill')
            ->with('return_bill_payment')
            ->with('customer')
            ->where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->orderBy('return_bill_id', 'DESC');


            $rcustom      =  collect();
            $rdata        =  $rcustom->merge($rquery->get());
            $returnbill   =  $rquery->paginate(5);

                       
            $count = 0;
            $taxabletariff = 0;
            $totalcgst = 0;
            $totalsgst = 0;
            $totaligst = 0;
            $grandtotal = 0;
            $scash  = 0;
            $scard = 0;
            $scheque = 0;
            $swallet = 0;
            $sunpaidamt=0;
            $snetbanking =0;
            $screditnote = 0;

            foreach ($sdata as $totsales)
            {
                $count++;
                
                $halfchargesgst   =   $totsales->chargesgst / 2;
                
                $taxabletariff  +=   $totsales->sellingprice_after_discount + $totsales->totalcharges;

                if($tax_type==1)
                {
                    $totaligst       +=  $totsales->total_igst_amount + $totsales->chargesgst;
                }
                else
                {

                    if($totsales->state_id == $company_state)
                    {
                      $totalcgst       +=  $totsales->total_cgst_amount + $halfchargesgst;
                      $totalsgst       +=  $totsales->total_sgst_amount + $halfchargesgst; 
                    }
                    else
                    {
                      $totaligst       +=  $totsales->total_igst_amount + $totsales->chargesgst;  
                    }
                }
                foreach($totsales['sales_bill_payment_detail'] as $paymentvalue)
                {

                   if($paymentvalue->payment_method_id==1)
                   {
                        $scash    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==2)
                   {
                        $scard    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==3)
                   {
                        $scheque    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==5)
                   {
                        $swallet    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==6)
                   {    
                        $sunpaidamt    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==7)
                   {
                        $snetbanking    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==8)
                   {
                        $screditnote    +=  $paymentvalue->total_bill_amount;
                   }
                }

                                            
                $grandtotal     +=   $totsales->total_bill_amount;
            }


            $rtaxabletariff = 0;
            $rtotalcgst = 0;
            $rtotalsgst = 0;
            $rtotaligst = 0;
            $rgrandtotal = 0;
            $rcash  = 0;
            $rcard = 0;
            $rcheque = 0;
            $rwallet = 0;
            $runpaidamt=0;
            $rnetbanking =0;
            $rcreditnote = 0;

            foreach ($rdata as $rtotsales)
            {
               
                
                $rhalfchargesgst   =   $rtotsales->chargesgst / 2;
                
                $rtaxabletariff  +=   $rtotsales->sellingprice_after_discount + $rtotsales->totalcharges;

                if($tax_type==1)
                {
                    $rtotaligst       +=  $rtotsales->total_igst_amount + $rtotsales->chargesgst; 
                }
                else
                {

                    if($rtotsales->state_id == $company_state)
                    {
                      $rtotalcgst       +=  $rtotsales->total_cgst_amount + $rhalfchargesgst;
                      $rtotalsgst       +=  $rtotsales->total_sgst_amount + $rhalfchargesgst; 
                    }
                    else
                    {
                      $rtotaligst       +=  $rtotsales->total_igst_amount + $rtotsales->chargesgst;  
                    }
                }
                 foreach($rtotsales['return_bill_payment'] as $rpaymentvalue)
                {

                   if($rpaymentvalue->payment_method_id==1)
                   {
                        $rcash    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==2)
                   {
                        $rcard    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==3)
                   {
                        $rcheque    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==5)
                   {
                        $rwallet    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==6)
                   {    
                        $runpaidamt    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==7)
                   {
                        $rnetbanking    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==8)
                   {
                        $rcreditnote    +=  $rpaymentvalue->total_bill_amount;
                   }
                }

                                            
                $rgrandtotal     +=   $rtotsales->total_bill_amount;
            }

            $todaytaxable   =   $taxabletariff - $rtaxabletariff;
            $todaycgst      =   $totalcgst - $rtotalcgst;
            $todaysgst      =   $totalsgst - $rtotalsgst;
            $todayigst      =   $totaligst - $rtotaligst;
            $todaygrand     =   $grandtotal - $rgrandtotal;
            $cash           =   $scash  -  $rcash;
            $card           =   $scard  -  $rcard;
            $cheque         =   $scheque  -  $rcheque;
            $wallet         =   $swallet  - $rwallet;
            $unpaidamt      =   $sunpaidamt - $runpaidamt;
            $netbanking     =   $snetbanking - $rnetbanking;
            $creditnote     =   $screditnote - $rcreditnote;

               $max_date  =  $sdata->max('bill_date');
               $min_date  =  $sdata->min('bill_date');
           

           
        return view('salesreport::view_bill_data',compact('sales','payment_methods','count','todaytaxable','todaycgst','todaysgst','todayigst','todaygrand','maxsales_id','minsales_id','company_state','returnbill','returnsales','rmaxsales_id','rminsales_id','tax_type','taxname','max_date','min_date','cash','card','cheque','wallet','unpaidamt','netbanking','creditnote','showedits'))->render();

        
    }

  public function view_bill_popup(Request $request)
  {
      Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
             $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';

            $sales = sales_bill::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('sales_bill_id','=',$request->billno)
                ->select('*')
                ->with('sales_product_detail.product')
                ->with('sales_bill_payment_detail.payment_method')
                ->with('customer')
                ->with('customer_address_detail')
                ->with('reference')
                ->with('company')
                ->with('state')
                ->with('user')
                ->get();

               
                  $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($sales[0]['sales_product_detail'] AS $key=>$v) {

                

                        if(isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$v['product']['product_features_relationship'][$html_id]);
                                    $v['product'][$html_id] =$nm;
                                }
                            }
                        }
                  }
                
                  // echo '<pre>';
                  // print_r($sales);
                  // exit;
                $maxsales_id   =  sales_bill::where('company_id',Auth::user()->company_id)->max('sales_bill_id');
                $minsales_id   =  sales_bill::where('company_id',Auth::user()->company_id)->min('sales_bill_id');

              
                 return view('salesreport::view_bill_popup',compact('sales','maxsales_id','minsales_id','tax_type','taxname'));

         }
        
    }
  public function previous_invoice(Request $request)
  {
      Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $billid   =  $request->billno;
            $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
             $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';

            $sales = sales_bill::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('sales_bill_id','<',$request->billno)
                ->select('*')
                ->with('sales_product_detail.product')
                ->with('sales_bill_payment_detail.payment_method')
                ->with('customer')
                ->with('customer_address_detail')
                ->with('reference')
                ->with('company')
                ->with('state')            
                ->orderBy('sales_bill_id','DESC')
                ->take(1)
                ->get();

                 $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($sales[0]['sales_product_detail'] AS $key=>$v) {

                

                        if(isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$v['product']['product_features_relationship'][$html_id]);
                                    $v['product'][$html_id] =$nm;
                                }
                            }
                        }
                  }

                $maxsales_id   =  sales_bill::where('company_id',Auth::user()->company_id)->max('sales_bill_id');
                $minsales_id   =  sales_bill::where('company_id',Auth::user()->company_id)->min('sales_bill_id');

          
              return view('salesreport::view_bill_popup',compact('sales','maxsales_id','minsales_id','tax_type','taxname'));

        }
        
    }
    public function next_invoice(Request $request)
   {
       Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $billid   =  $request->billno;
            $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
             $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';

            $sales = sales_bill::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('sales_bill_id','>',$request->billno)
                ->select('*')
                ->with('sales_product_detail.product')
                ->with('sales_bill_payment_detail.payment_method')
                ->with('customer')
                ->with('customer_address_detail')
                ->with('reference')
                ->with('company')
                ->with('state')            
                ->orderBy('sales_bill_id','ASC')
                ->take(1)
                ->get();

                 $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($sales[0]['sales_product_detail'] AS $key=>$v) {

                

                        if(isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$v['product']['product_features_relationship'][$html_id]);
                                    $v['product'][$html_id] =$nm;
                                }
                            }
                        }
                  }

               
                $maxsales_id   =  sales_bill::where('company_id',Auth::user()->company_id)->max('sales_bill_id');
                $minsales_id   =  sales_bill::where('company_id',Auth::user()->company_id)->min('sales_bill_id');

          
             return view('salesreport::view_bill_popup',compact('sales','maxsales_id','minsales_id','tax_type','taxname'));
            

        }
        
    }
     public function viewbillcustomer_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

         $json = [];
         $result = customer::select('customer_name','customer_mobile')
             ->where('company_id',Auth::user()->company_id)
             ->where('deleted_at','=',NULL)
             ->where('customer_name', 'LIKE', "%$request->search_val%")
             ->orwhere('customer_mobile', 'LIKE', "%$request->search_val%")
             ->get();

       
            foreach($result as $customerkey=>$customervalue){


                  $json[] = $customervalue['customer_name'].'_'.$customervalue['customer_mobile'];
                  
            }
        
        
        return json_encode($json);
       
        //return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function customerbill_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);


        $result = customer::select('customer_name AS result','customer_mobile')
             ->where('company_id',Auth::user()->company_id)
             ->where('deleted_at','=',NULL)
             ->where('customer_name', 'LIKE', "%$request->search_val%")
             ->orwhere('customer_mobile', 'LIKE', "%$request->search_val%")
             ->get();

        

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }
    public function reference_search(Request $request)
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
    public function employee_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->search_val !='')
        {

            $json = [];
            $result = User::select('employee_firstname')
                ->where('employee_firstname', 'LIKE', "%$request->search_val%")->get();

           
           

            if(!empty($result))
            {
           
                foreach($result as $billkey=>$billvalue){


                      $json[] = $billvalue['employee_firstname'];
                      
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
   
    function datewise_billdetail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {

          $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

            
            $payment_methods =      payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
            $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';
            $showedits      =   1;
            $data            =      $request->all();
           

            // $sort_by = $data['sortby'];
            // $sort_type = $data['sorttype'];
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
            


         $squery           =      sales_bill::select("sales_bills.*",DB::raw("(SELECT SUM(sales_product_details.discount_amount + sales_product_details.overalldiscount_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id GROUP BY sales_product_details.sales_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(sales_product_details.mrp) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(sales_product_details.igst_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as chargesgst"))->with('reference')->with('sales_bill_payment_detail')->where('company_id',$company_id)->where('deleted_at','=',NULL)->where('is_active','=',1);


            $rquery = return_bill::select("return_bills.*",DB::raw("(SELECT SUM(return_product_details.discount_amount + return_product_details.overalldiscount_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id GROUP BY return_product_details.return_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(return_product_details.mrp) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(return_product_details.igst_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as chargesgst"))
             ->whereNull('consign_bill_id')->with('reference')->with('return_bill_payment')->where('company_id',$company_id)->where('deleted_by','=',NULL);

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

                 $result = customer::select('customer_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('customer_name', 'LIKE', "%$cus_name%")
                 ->orwhere('customer_mobile', 'LIKE', "%$cus_mobile%")
                 ->get();

                 $squery->whereIn('customer_id',$result);
                 $rquery->whereIn('customer_id',$result);
            }
            if(isset($query) && $query != '' && $query['reference_name'] != '')
            {
                $ref_name =  $query['reference_name'];
                 $rresult = reference::select('reference_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('reference_name', 'LIKE', "%$ref_name%")
                 ->get();

                 $squery->whereIn('reference_id',$rresult);
                 $rquery->whereIn('reference_id',$rresult);
            }
            if(isset($query) && $query != '' && $query['employee_name'] != '')
            {
                $ref_name =  $query['reference_name'];
                $employee_name = $query['employee_name'];
                
                 $emp_name = User::select('user_id')                 
                 ->where('company_id',$company_id)
                 ->where('employee_firstname', 'LIKE', "%$employee_name%")->get();

                 $squery->whereIn('created_by',$emp_name);
                 $rquery->whereIn('created_by',$emp_name);
            }
            
            if(isset($query) && $query != '' && $query['billno'] != '')
            {
                 $squery->where('bill_no', 'like', '%'.$query['billno'].'%');

                 $tbill_no  =  sales_bill::select('sales_bill_id')->where('bill_no', 'like', '%'.$query['billno'].'%')->where('company_id',$company_id)->get();
                $rquery->whereIn('sales_bill_id', $tbill_no);
            }
            if(isset($query) && $query != '' && $query['from_date'] != '' && $query['to_date'] != '')
            {
                
                 $rstart           =      date("Y-m-d",strtotime($query['from_date']));
                 $rend             =      date("Y-m-d",strtotime($query['to_date']));
                 $squery->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                 $rquery->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                
            }
            if(isset($query) && $query != '' && $query['from_date'] == '' && $query['to_date'] == '' && $query['reference_name'] == '' && $query['billno'] == '' && $query['customerid'] == '' && $query['employee_name'] == '')
            {
                 $rstart           =      date("Y-m-d");
                 $rend             =      date("Y-m-d");
                 $squery->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                 $rquery->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
            }
            
            // $squery->whereRaw("Date(sales_bills.created_at) between '$rstart' and '$rend'");
            // $rquery->whereRaw("Date(return_bills.created_at) between '$rstart' and '$rend'");

           
           


            $scustom   =   collect();
            $sdata     =   $scustom->merge($squery->get());

            $sales     =   $squery->orderBy('sales_bill_id', 'DESC')
                           ->paginate(10);

          

            $rcustom   =   collect();
            $rdata     =   $rcustom->merge($rquery->get());

            $returnbill  =  $rquery->orderBy('return_bill_id', 'DESC')->paginate(10); 



            $count = 0;
            $taxabletariff = 0;
            $totalcgst = 0;
            $totalsgst = 0;
            $totaligst = 0;
            $grandtotal = 0;
            $scash  = 0;
            $scard = 0;
            $scheque = 0;
            $swallet = 0;
            $sunpaidamt=0;
            $snetbanking =0;
            $screditnote = 0;

            foreach ($sdata as $totsales)
            {
                $count++;
                
                $halfchargesgst   =   $totsales->chargesgst / 2;
                
                $taxabletariff  +=   $totsales->sellingprice_after_discount + $totsales->totalcharges;

                if($tax_type==1)
                {
                    $totaligst       +=  $totsales->total_igst_amount + $totsales->chargesgst;  
                }
                else
                {

                    if($totsales->state_id == $company_state)
                    {
                      $totalcgst       +=  $totsales->total_cgst_amount + $halfchargesgst;
                      $totalsgst       +=  $totsales->total_sgst_amount + $halfchargesgst; 
                    }
                    else
                    {
                      $totaligst       +=  $totsales->total_igst_amount + $totsales->chargesgst;  
                    }
                }
                foreach($totsales['sales_bill_payment_detail'] as $paymentvalue)
                {

                   if($paymentvalue->payment_method_id==1)
                   {
                        $scash    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==2)
                   {
                        $scard    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==3)
                   {
                        $scheque    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==5)
                   {
                        $swallet    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==6)
                   {    
                        $sunpaidamt    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==7)
                   {
                        $snetbanking    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==8)
                   {
                        $screditnote    +=  $paymentvalue->total_bill_amount;
                   }
                }
         
                                            
                $grandtotal     +=   $totsales->total_bill_amount;
            }
          
            $rtaxabletariff = 0;
            $rtotalcgst = 0;
            $rtotalsgst = 0;
            $rtotaligst = 0;
            $rgrandtotal = 0;
            $rcash  = 0;
            $rcard = 0;
            $rcheque = 0;
            $rwallet = 0;
            $runpaidamt=0;
            $rnetbanking =0;
            $rcreditnote = 0;

            foreach ($rdata as $rtotsales)
            {
               
                
                $rhalfchargesgst   =   $rtotsales->chargesgst / 2;
                
                $rtaxabletariff  +=   $rtotsales->sellingprice_after_discount + $rtotsales->totalcharges;

                 if($tax_type==1)
                {
                    $rtotaligst         +=  $rtotsales->total_igst_amount + $rtotsales->chargesgst;
                }
                else
                {

                    if($rtotsales->state_id == $company_state)
                    {
                      $rtotalcgst       +=  $rtotsales->total_cgst_amount + $rhalfchargesgst;
                      $rtotalsgst       +=  $rtotsales->total_sgst_amount + $rhalfchargesgst; 
                    }
                    else
                    {
                      $rtotaligst       +=  $rtotsales->total_igst_amount + $rtotsales->chargesgst;  
                    }   
                }

                foreach($rtotsales['return_bill_payment'] as $rpaymentvalue)
                {

                   if($rpaymentvalue->payment_method_id==1)
                   {
                        $rcash    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==2)
                   {
                        $rcard    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==3)
                   {
                        $rcheque    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==5)
                   {
                        $rwallet    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==6)
                   {    
                        $runpaidamt    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==7)
                   {
                        $rnetbanking    +=  $rpaymentvalue->total_bill_amount;
                   }
                   if($rpaymentvalue->payment_method_id==8)
                   {
                        $rcreditnote    +=  $rpaymentvalue->total_bill_amount;
                   }
                }

                                            
                $rgrandtotal     +=   $rtotsales->total_bill_amount;
            }

            $todaytaxable   =   $taxabletariff - $rtaxabletariff;
            $todaycgst      =   $totalcgst - $rtotalcgst;
            $todaysgst      =   $totalsgst - $rtotalsgst;
            $todayigst      =   $totaligst - $rtotaligst;
            $todaygrand     =   $grandtotal - $rgrandtotal;
            $cash           =   $scash  -  $rcash;
            $card           =   $scard  -  $rcard;
            $cheque         =   $scheque  -  $rcheque;
            $wallet         =   $swallet  - $rwallet;
            $unpaidamt      =   $sunpaidamt - $runpaidamt;
            $netbanking     =   $snetbanking - $rnetbanking;
            $creditnote     =   $screditnote - $rcreditnote;

            if($query['from_date']=='')
            {
                     $max_date  =  $sdata->max('bill_date');
                     $min_date  =  $sdata->min('bill_date');
            }
            else
            {   
                     $max_date  =  $query['from_date'];
                     $min_date  =  $query['to_date'];
            }
          
           
          
        
              return view('salesreport::view_bill_data',compact('sales','payment_methods','count','todaytaxable','todaycgst','todaysgst','todaygrand','company_state','todayigst','returnbill','tax_type','taxname','max_date','min_date','cash','card','cheque','wallet','unpaidamt','netbanking','creditnote','showedits','get_store','companyname'))->render();
        }
            
                
    }

   public function view_returnbill_popup(Request $request)
  {
      Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
             $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';

            $returnsales = return_bill::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('return_bill_id','=',$request->billno)
                ->select('*')
                ->with('sales_bill')
                ->with('return_product_detail.product')
                ->with('return_bill_payment.payment_method','return_bill_payment.customer_creditnote')
                ->with('customer')
                ->with('customer_address_detail')
                ->with('company')
                ->with('state')
                ->get();

                 $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($returnsales[0]['return_product_detail'] AS $key=>$v) {

                

                        if(isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$v['product']['product_features_relationship'][$html_id]);
                                    $v['product'][$html_id] =$nm;
                                }
                            }
                        }
                  }
               
               $rmaxsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->max('return_bill_id');
               $rminsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->min('return_bill_id');

              
                  return view('salesreport::view_returnbill_popup',compact('returnsales','rmaxsales_id','rminsales_id','tax_type','taxname'));

         }
        
    }
  public function rprevious_invoice(Request $request)
  {
      Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $billid   =  $request->billno;
            $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
            $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';

            $returnsales = return_bill::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('return_bill_id','<',$request->billno)
                ->select('*')
                ->with('sales_bill')
                ->with('return_product_detail.product')
                ->with('return_bill_payment.payment_method','return_bill_payment.customer_creditnote')
                ->with('customer')
                ->with('customer_address_detail')
                ->with('company')
                ->with('state')        
                ->orderBy('return_bill_id','DESC')
                ->take(1)
                ->get();

                 $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($returnsales[0]['return_product_detail'] AS $key=>$v) {

                

                        if(isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$v['product']['product_features_relationship'][$html_id]);
                                    $v['product'][$html_id] =$nm;
                                }
                            }
                        }
                  }
//dd($returnsales);
//exit;
              $rmaxsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->max('return_bill_id');
              $rminsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->min('return_bill_id');

          
              return view('salesreport::view_returnbill_popup',compact('returnsales','rmaxsales_id','rminsales_id','tax_type','taxname'));

        }
        
    }
    public function rnext_invoice(Request $request)
   {
       Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $billid   =  $request->billno;
            $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
            $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';

            $returnsales = return_bill::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('return_bill_id','>',$request->billno)
                ->select('*')
                ->with('sales_bill')
                ->with('return_product_detail.product')
                ->with('return_bill_payment.payment_method','return_bill_payment.customer_creditnote')
                ->with('customer')
                ->with('customer_address_detail')
                ->with('company')
                ->with('state')          
                ->orderBy('return_bill_id','ASC')
                ->take(1)
                ->get();

                 $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($returnsales[0]['return_product_detail'] AS $key=>$v) {

                

                        if(isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
                        {
                            foreach ($product_features AS $kk => $vv)
                            {
                                $html_id = $vv['html_id'];

                                if($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                                {
                                    $nm =  product::feature_value($vv['product_features_id'],$v['product']['product_features_relationship'][$html_id]);
                                    $v['product'][$html_id] =$nm;
                                }
                            }
                        }
                  }

               
                $rmaxsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->max('return_bill_id');
                $rminsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->min('return_bill_id');

          
              return view('salesreport::view_returnbill_popup',compact('returnsales','rmaxsales_id','rminsales_id','tax_type','taxname'));
            

        }
        
    }

    public function exportbill_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

       $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

            $data            =      $request->all();
            $query = isset($data['query']) ? $data['query']  : '';


            $payment_methods =      payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
            $state_id        =      company_profile::select('state_id','tax_type','tax_title','currency_title','bill_calculation')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';



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
            

            
   

            $squery           =      sales_bill::select("sales_bills.*",DB::raw("(SELECT SUM(sales_product_details.discount_amount + sales_product_details.overalldiscount_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id GROUP BY sales_product_details.sales_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(sales_product_details.mrp) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(sales_product_details.igst_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as chargesgst"))->with('reference')->with('user');

           
            

            $rquery = return_bill::select("return_bills.*",DB::raw("(SELECT SUM(return_product_details.discount_amount + return_product_details.overalldiscount_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id GROUP BY return_product_details.return_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(return_product_details.mrp) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(return_product_details.igst_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as chargesgst"))->with('reference')->with('user');




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

                 $result = customer::select('customer_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('customer_name', 'LIKE', "%$cus_name%")
                 ->orwhere('customer_mobile', 'LIKE', "%$cus_mobile%")
                 ->get();

                 $squery->whereIn('customer_id',$result);
                 $rquery->whereIn('customer_id',$result);
            }
            if(isset($query) && $query != '' && $query['reference_name'] != '')
            {
                $ref_name =  $query['reference_name'];
                 $rresult = reference::select('reference_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('reference_name', 'LIKE', "%$ref_name%")
                 ->get();

                 $squery->whereIn('reference_id',$rresult);
                 $rquery->whereIn('reference_id',$rresult);
            }
            if(isset($query) && $query != '' && $query['employee_name'] != '')
            {
                $ref_name =  $query['reference_name'];
                $employee_name = $query['employee_name'];
                
                 $emp_name = User::select('user_id')                 
                 ->where('company_id',$company_id)
                 ->where('employee_firstname', 'LIKE', "%$employee_name%")->get();

                 $squery->whereIn('created_by',$emp_name);
                 $rquery->whereIn('created_by',$emp_name);
            }
            if(isset($query) && $query != '' && $query['billno'] != '')
            {
                 $squery->where('bill_no', 'like', '%'.$query['billno'].'%');

                 $tbill_no  =  sales_bill::select('sales_bill_id')->where('bill_no', 'like', '%'.$query['billno'].'%')->where('company_id',$company_id)->get();
                $rquery->whereIn('sales_bill_id', $tbill_no);
            }
            if(isset($query) && $query != '' && $query['from_date'] != '' && $query['to_date'] != '')
            {
                
                 $rstart           =      date("Y-m-d",strtotime($query['from_date']));
                 $rend             =      date("Y-m-d",strtotime($query['to_date']));
                 $squery->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                 $rquery->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                
            }
            if(isset($query) && $query != '' && $query['from_date'] == '' && $query['to_date'] == '' && $query['reference_name'] == '' && $query['billno'] == '' && $query['customerid'] == '' && $query['employee_name'] == '')
            {
                 $rstart           =      date("Y-m-d");
                 $rend             =      date("Y-m-d");
                 $squery->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                 $rquery->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
            }


             $sales = $squery->with('sales_bill_payment_detail')->where('company_id',$company_id)->where('deleted_by','=',NULL)->orderBy('sales_bill_id','DESC')
                   ->get();            
            
            
            $returnbill  =  $rquery->with('return_bill_payment')->where('company_id',$company_id)->where('deleted_at','=',NULL)->orderBy('return_bill_id', 'DESC')->get(); 


             $overallsales   =  [];
             $header       = [];
            if(sizeof($get_store)!=0)
            {
                $header[]  =  'Location';  
            }
             $header[]  =  'Bill No.';  
             $header[]  =  'Bill Date';  
             $header[]  =  'Customer'; 
             $header[]  =  'TotalQty'; 
          if($state_id[0]['bill_calculation']==1)
          {
             $header[]  =  'SellingPrice';
             $header[]  =  'Discount Amount';
             $header[]  =  'Taxable Amount';
             if($tax_type==1)
             {
                 $header[]  =  $taxname.' Amount';
             }
             else
             {
                 $header[]  =  'CGST Amount';
                 $header[]  =  'SGST Amount'; 
                 $header[]  =  'IGST Amount';
                
             }
             
             $header[]  =  'Total Amount';
             $header[]  =  'Cash'; 
             $header[]  =  'Card';
             $header[]  =  'Cheque';
             $header[]  =  'Wallet';
             $header[]  =  'Outstanding';
             $header[]  =  'Net Banking';
             $header[]  =  'Credit Note';
           }
             $header[]  =  'Reference';
             $header[]  =  'Note for Internal Use';
             $header[]  =  'Note for Customer';
             $header[]  =  'Employee';
            

            $overallsales['sales']        =  $sales;
            $overallsales['returnbill']   =  $returnbill;



        $excel = Excel::download(new viewbill_export($overallsales, $header,$companyname), "ViewBill-Export.xlsx");
        return $excel;
      

    }

    public function restore_bills(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

       $sales = sales_bill::select("sales_bills.*",DB::raw("(SELECT SUM(sales_product_details.discount_amount + sales_product_details.overalldiscount_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id GROUP BY sales_product_details.sales_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(sales_product_details.mrp) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(sales_product_details.igst_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as chargesgst"))
            ->with('reference')
            ->where('company_id',Auth::user()->company_id)
            ->where('deleted_at','!=',NULL)
            ->orderBy('sales_bill_id', 'DESC')->paginate(10);

        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

        return view('salesreport::restore_deleted_bills',compact('sales','tax_type','tax_title','taxname','company_state'));
    }
    public function view_deletedbill_popup(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

            if($request->ajax())
            {
                $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
                 $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
                $company_state   = $state_id[0]['state_id'];
                $tax_type        = $state_id[0]['tax_type'];
                $tax_title       = $state_id[0]['tax_title'];
                $taxname         = $tax_type==1?$tax_title:'IGST';

                $sales = sales_bill::where('company_id',Auth::user()->company_id)
                    ->where('deleted_at','!=',NULL)
                    ->where('sales_bill_id','=',$request->billno)
                    ->select('*')
                    ->with('deletedsales_product_detail.product')
                    ->with('deletedsales_bill_payment_detail.payment_method')
                    ->with('customer')
                    ->with('customer_address_detail')
                    ->with('company')
                    ->with('state')
                    ->get();

                 
                  
                     return view('salesreport::view_deletedbill_popup',compact('sales','tax_type','taxname'));

             }
            
    }
    public function pagewise_deletedbill_popup(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

           $sales = sales_bill::select("sales_bills.*",DB::raw("(SELECT SUM(sales_product_details.discount_amount + sales_product_details.overalldiscount_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id GROUP BY sales_product_details.sales_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(sales_product_details.mrp) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(sales_product_details.igst_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as chargesgst"))
            ->with('reference')
            ->where('company_id',Auth::user()->company_id)
            ->where('deleted_at','!=',NULL)
            ->orderBy('sales_bill_id', 'DESC')->paginate(10);

        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

        return view('salesreport::restore_deleted_billsdata',compact('sales','tax_type','tax_title','taxname','company_state'));

           
            
    }
public function restore_bill_delete(request $request)
{
    Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

    $userId = Auth::User()->user_id;


     try {
        DB::beginTransaction();    


                    $creditnoteid      =  sales_bill_payment_detail::select('customer_creditnote_id','total_bill_amount')
                                                                ->where('sales_bill_id',$request->deleted_id)
                                                                ->where('payment_method_id',8)->first();
                     if($creditnoteid!='')
                     {
                        customer_creditnote::where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])->update(array(
                                  'balance_amount' => DB::raw('balance_amount - '.$creditnoteid['total_bill_amount']))); 
                        creditnote_payment::where('sales_bill_id',$request->deleted_id)
                                            ->where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])
                                            ->update([
                                                 'deleted_by' => NULL,
                                                'deleted_at' => NULL
                                                ]);
                     }                                           
                     $creditid      =  sales_bill_payment_detail::select('total_bill_amount')
                                                                ->where('sales_bill_id',$request->deleted_id)
                                                                ->where('payment_method_id',6)->first();

                      if($creditid!='')
                     {

                        $creditaccid    =  customer_creditaccount::select('customer_creditaccount_id')
                                                                    ->where('sales_bill_id',$request->deleted_id)->first();

                        
                             customer_creditaccount::where('sales_bill_id',$request->deleted_id)
                                            ->update([
                                                 'deleted_by' => NULL,
                                                'deleted_at' => NULL
                                                ]);
                        
                       
                     }     
                    
                    
                    $bill_delete =  sales_bill::where('sales_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => NULL,
                    'deleted_at' => NULL
                    ]);
                    

                    $billproduct_data = sales_product_detail::select('*')
                    ->where('sales_bill_id',$request->deleted_id)
                    ->get();

                    foreach($billproduct_data as $billdatakey=>$billdatavalue)
                    {
                       if($billdatavalue['inwardids'] !='' || $billdatavalue['inwardids'] !=null)
                        {

                           $inwardids  = explode(',' ,substr($billdatavalue['inwardids'],0,-1));
                           $inwardqtys = explode(',' ,substr($billdatavalue['inwardqtys'],0,-1));
                           

                            foreach($inwardids as $inidkey=>$inids)
                            {
                                inward_product_detail::where('company_id',Auth::user()->company_id)
                                                  ->where('inward_product_detail_id',$inids)
                                                  ->update(array(
                                                      'modified_by' => Auth::User()->user_id,
                                                      'updated_at' => date('Y-m-d H:i:s'),
                                                      'pending_return_qty' => DB::raw('pending_return_qty - '.$inwardqtys[$inidkey])
                                                      ));        
                            } 
                           
                        }

                        $productqty    =  price_master::select('product_qty')
                                ->where('price_master_id',$billdatavalue['price_master_id'])
                                ->where('company_id',Auth::user()->company_id)
                                ->get();

                        $updateqty   =    $productqty[0]['product_qty'] -  $billdatavalue['qty'];


                        price_master::where('price_master_id',$billdatavalue['price_master_id'])->update(array(
                                  'product_qty' => $updateqty)); 

                    }  
                    $billdata_delete =  sales_product_detail::where('sales_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => NULL,
                    'deleted_at' => NULL
                    ]);
                   
                    $billpayment_delete =  sales_bill_payment_detail::where('sales_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => NULL,
                    'deleted_at' => NULL
                    ]);

             DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }

       
        if($billdata_delete)
        {
            return json_encode(array("Success"=>"True","Message"=>"Bill has been successfully Restored.!"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong!"));
        }

    }

    public function bill_delete(request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $userId = Auth::User()->user_id;

     try {
        DB::beginTransaction();    


                    $creditnoteid      =  sales_bill_payment_detail::select('customer_creditnote_id','total_bill_amount')
                                                                ->where('sales_bill_id',$request->deleted_id)
                                                                ->where('payment_method_id',8)
                                                                ->where('deleted_at',NULL)->first();
                     if($creditnoteid!='')
                     {
                        customer_creditnote::where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])->update(array(
                                  'balance_amount' => DB::raw('balance_amount + '.$creditnoteid['total_bill_amount']))); 
                        creditnote_payment::where('sales_bill_id',$request->deleted_id)
                                            ->where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])
                                            ->update([
                                                 'deleted_by' => $userId,
                                                'deleted_at' => date('Y-m-d H:i:s')
                                                ]);
                     }                                           
                     $creditid      =  sales_bill_payment_detail::select('total_bill_amount')
                                                                ->where('sales_bill_id',$request->deleted_id)
                                                                ->where('payment_method_id',6)
                                                                ->where('deleted_at',NULL)->first();

                      if($creditid!='')
                     {

                        $creditaccid    =  customer_creditaccount::select('customer_creditaccount_id')
                                                                    ->where('sales_bill_id',$request->deleted_id)
                                                                    ->where('deleted_at',NULL)->first();

                        $creditrecpid    =  customer_creditreceipt_detail::where('customer_creditaccount_id',$creditaccid->customer_creditaccount_id)->get();

                        //print_r($creditaccid->customer_creditaccount_id);
                        //print_r($creditrecpid);

                        //exit;
                        if(sizeof($creditrecpid)!=0)
                        {
                                return json_encode(array("Success"=>"False","Message"=>"Outstanding Amount against this bill has already been received So can't this Bill Now!"));
                        }
                        else
                        {
                             customer_creditaccount::where('sales_bill_id',$request->deleted_id)
                                            ->update([
                                                 'deleted_by' => $userId,
                                                'deleted_at' => date('Y-m-d H:i:s')
                                                ]);
                        }
                       
                     }     
                    
                    
                    $bill_delete =  sales_bill::where('sales_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                    ]);
                    

                    $billproduct_data = sales_product_detail::select('*')
                    ->where('sales_bill_id',$request->deleted_id)
                    ->where('product_type',1)
                    ->get();

                    foreach($billproduct_data as $billdatakey=>$billdatavalue)
                    {
                       if($billdatavalue['inwardids'] !='' || $billdatavalue['inwardids'] !=null)
                        {

                           $inwardids  = explode(',' ,substr($billdatavalue['inwardids'],0,-1));
                           $inwardqtys = explode(',' ,substr($billdatavalue['inwardqtys'],0,-1));
                           

                            foreach($inwardids as $inidkey=>$inids)
                            {
                                inward_product_detail::where('company_id',Auth::user()->company_id)
                                                  ->where('inward_product_detail_id',$inids)
                                                  ->update(array(
                                                      'modified_by' => Auth::User()->user_id,
                                                      'updated_at' => date('Y-m-d H:i:s'),
                                                      'pending_return_qty' => DB::raw('pending_return_qty + '.$inwardqtys[$inidkey])
                                                      ));        
                            } 
                           
                        }

                        $productqty    =  price_master::select('product_qty')
                                ->where('price_master_id',$billdatavalue['price_master_id'])
                                ->where('company_id',Auth::user()->company_id)
                                ->get();

                        $updateqty   =    $productqty[0]['product_qty'] +  $billdatavalue['qty'];


                        price_master::where('price_master_id',$billdatavalue['price_master_id'])->update(array(
                                  'product_qty' => $updateqty)); 

                    }  
                    $billdata_delete =  sales_product_detail::where('sales_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                    ]);
                   
                    $billpayment_delete =  sales_bill_payment_detail::where('sales_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                    ]);

             DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }

       
        if($billdata_delete)
        {
            return json_encode(array("Success"=>"True","Message"=>"Bill has been successfully deleted.!"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong!"));
        }

    }
    public function consignbill_delete(request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $userId = Auth::User()->user_id;

     try {
        DB::beginTransaction();

                    $billproduct_data = consign_products_detail::select('*')
                    ->where('consign_bill_id',$request->deleted_id)
                    ->where('product_type',1)
                    ->get();    

                   foreach($billproduct_data as $billdatakey=>$billdatavalue)
                  {
                       $salesdetailid      =  sales_product_detail::select('consign_products_detail_id')
                                                              ->where('consign_products_detail_id',$billdatavalue['consign_products_detail_id'])
                                                              ->where('deleted_at',NULL)->get();

                                                            

                        if(sizeof($salesdetailid)!=0)
                        {
                                return json_encode(array("Success"=>"False","Message"=>"Consignment Challan can't be deleted since it already has been converted to Bill Now!"));
                                exit;
                        }
                  }
                    
                    
                    $bill_delete =  consign_bill::where('consign_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                    ]);
                    

                    

                    foreach($billproduct_data as $billdatakey=>$billdatavalue)
                    {
                       if($billdatavalue['inwardids'] !='' || $billdatavalue['inwardids'] !=null)
                        {

                           $inwardids  = explode(',' ,substr($billdatavalue['inwardids'],0,-1));
                           $inwardqtys = explode(',' ,substr($billdatavalue['inwardqtys'],0,-1));
                           

                            foreach($inwardids as $inidkey=>$inids)
                            {
                                inward_product_detail::where('company_id',Auth::user()->company_id)
                                                  ->where('inward_product_detail_id',$inids)
                                                  ->update(array(
                                                      'modified_by' => Auth::User()->user_id,
                                                      'updated_at' => date('Y-m-d H:i:s'),
                                                      'pending_return_qty' => DB::raw('pending_return_qty + '.$inwardqtys[$inidkey])
                                                      ));        
                            } 
                           
                        }

                        $productqty    =  price_master::select('product_qty')
                                ->where('price_master_id',$billdatavalue['price_master_id'])
                                ->where('company_id',Auth::user()->company_id)
                                ->get();

                        $updateqty   =    $productqty[0]['product_qty'] +  $billdatavalue['qty'];


                        price_master::where('price_master_id',$billdatavalue['price_master_id'])->update(array(
                                  'product_qty' => $updateqty)); 

                    }  
                    $billdata_delete =  consign_products_detail::where('consign_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                    ]);
                   
                    $billpayment_delete =  consign_payment_detail::where('consign_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                    ]);

             DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }

       
        if($billdata_delete)
        {
            return json_encode(array("Success"=>"True","Message"=>"Bill has been successfully deleted.!"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong!"));
        }

    }
    public function returnbill_delete(request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $userId = Auth::User()->user_id;

       

     try {
        DB::beginTransaction();    


                    $creditnoteid      =  return_bill_payment::select('customer_creditnote_id','total_bill_amount')
                                                                ->where('return_bill_id',$request->deleted_id)
                                                                ->where('deleted_at',NULL)->first();
                    $creditreceiptid    =  customer_creditreceipt::select('customer_creditreceipt_id','return_bill_id','remarks','total_amount')
                                                                ->where('return_bill_id',$request->deleted_id)
                                                                ->where('deleted_at',NULL)->first(); 


                    $returnproductid      =  return_product_detail::select('return_product_detail_id','return_bill_id')
                                                                ->where('return_bill_id',$request->deleted_id)
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
                        return json_encode(array("Success"=>"False","Message"=>"Products returned in Bill has been restocked, So cannot delete this bill"));
                        exit;
                    }

                                                        
                     if($creditreceiptid=='') 
                     {
                         $creditnotepaymentid = creditnote_payment::where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])
                                               ->where('deleted_at',NULL)->first();

                                if($creditnotepaymentid!='')
                                {
                                     return json_encode(array("Success"=>"False","Message"=>"Credit Note Generated through this return bill has already been used.. So entry cannot be deleted."));
                                      exit;
                                }  
                                else
                                {
                                    customer_creditnote::where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])
                                            ->update([
                                                 'deleted_by' => $userId,
                                                'deleted_at' => date('Y-m-d H:i:s')
                                                ]);
                                }             
                     }   
                     else
                     {
                            // customer_creditnote::where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])->update(array(
                            //      'balance_amount' => DB::raw('balance_amount + '.$creditreceiptid['total_amount']))); 
                            $creditreceiptdetailid    =  customer_creditreceipt_detail::select('customer_creditreceipt_id','customer_creditaccount_id','payment_amount','credit_amount')
                                                                ->where('customer_creditreceipt_id',$creditreceiptid->customer_creditreceipt_id)
                                                                ->where('deleted_at',NULL)->first(); 

                            customer_creditaccount::where('customer_creditaccount_id',$creditreceiptdetailid['customer_creditaccount_id'])->update(array(
                                 'balance_amount' => DB::raw('balance_amount + '.$creditreceiptdetailid['payment_amount']))); 

                         
                           customer_creditreceipt::where('return_bill_id',$request->deleted_id)
                                            ->update([
                                                 'deleted_by' => $userId,
                                                'deleted_at' => date('Y-m-d H:i:s')
                                                ]);
                            customer_creditreceipt_detail::where('customer_creditreceipt_id',$creditreceiptid->customer_creditreceipt_id)
                            ->update([
                                 'deleted_by' => $userId,
                                'deleted_at' => date('Y-m-d H:i:s')
                                ]);
                            customer_crerecp_payment::where('customer_creditreceipt_id',$creditreceiptid->customer_creditreceipt_id)
                            ->update([
                                 'deleted_by' => $userId,
                                'deleted_at' => date('Y-m-d H:i:s')
                                ]);
                            customer_creditnote::where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])
                                            ->update([
                                                 'deleted_by' => $userId,
                                                'deleted_at' => date('Y-m-d H:i:s')
                                                ]);
                            creditnote_payment::where('customer_creditnote_id',$creditnoteid['customer_creditnote_id'])
                            ->update([
                                 'deleted_by' => $userId,
                                'deleted_at' => date('Y-m-d H:i:s')
                                ]);

                             
                     }                                                                               
                                                          
                   


                    $bill_delete =  return_bill::where('return_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => $userId,
                     'deleted_at' => date('Y-m-d H:i:s')
                    ]);
                 
                     
                    $billdata_delete =  return_product_detail::where('return_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                    ]);
                   
                    $billpayment_delete =  return_bill_payment::where('return_bill_id', $request->deleted_id)
                        ->update([
                     'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                    ]);

                    foreach($returnproductid as $returnprodkey=>$returnprodvalue)
                    {
                        $billpproduct_delete =  returnbill_product::where('return_product_detail_id', $returnprodvalue['return_product_detail_id'])
                        ->update([
                         'deleted_by' => $userId,
                        'deleted_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    

             DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }

       
        if($billdata_delete)
        {
            return json_encode(array("Success"=>"True","Message"=>"Bill has been successfully deleted.!"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong!"));
        }

    }

    public function view_franchise_bill()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);


        $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

        $compname       =   company::where('company_id',Auth::user()->company_id)    
                                      ->first();                                           
        $companyname    =   $compname['company_name'];         


        $maxsales_id   =  '';
        $minsales_id   =  '';
        $rmaxsales_id   =  '';
        $rminsales_id   =  '';
        $showedits      =   0;
        $returnsales   =  array();
        $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

        $date      =   date("Y-m-d");
      

        $squery = sales_bill::select("sales_bills.*",DB::raw("(SELECT SUM(sales_product_details.discount_amount + sales_product_details.overalldiscount_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id GROUP BY sales_product_details.sales_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(sales_product_details.mrp) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(sales_product_details.igst_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id and product_type=2 GROUP BY sales_product_details.sales_bill_id)  as chargesgst"))
            ->with('reference')
            ->with('sales_bill_payment_detail')
            ->where('company_id',Auth::user()->company_id)
            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'")
            // ->whereRaw("Date(sales_bills.created_at) between '$date' and '$date'")
            // ->whereRaw("bill_date between '$sdate' and '$sdate'")
            ->where('deleted_at','=',NULL)
            ->where('is_active','=',1)
            ->where('sales_type','=',2)
            ->orderBy('sales_bill_id', 'DESC');

            $scustom   =   collect();
            $sdata     =   $scustom->merge($squery->get());
            $sales     =   $squery->paginate(10);

          

                       
            $count = 0;
            $taxabletariff = 0;
            $totalcgst = 0;
            $totalsgst = 0;
            $totaligst = 0;
            $grandtotal = 0;
            $scash  = 0;
            $scard = 0;
            $scheque = 0;
            $swallet = 0;
            $sunpaidamt=0;
            $snetbanking =0;
            $screditnote = 0;

            foreach ($sdata as $totsales)
            {
                $count++;
                
                $halfchargesgst   =   $totsales->chargesgst / 2;
                
                $taxabletariff  +=   $totsales->sellingprice_after_discount + $totsales->totalcharges;

                if($tax_type==1)
                {
                    $totaligst       +=  $totsales->total_igst_amount + $totsales->chargesgst;
                }
                else
                {

                    if($totsales->state_id == $company_state)
                    {
                      $totalcgst       +=  $totsales->total_cgst_amount + $halfchargesgst;
                      $totalsgst       +=  $totsales->total_sgst_amount + $halfchargesgst; 
                    }
                    else
                    {
                      $totaligst       +=  $totsales->total_igst_amount + $totsales->chargesgst;  
                    }
                }
                foreach($totsales['sales_bill_payment_detail'] as $paymentvalue)
                {

                   if($paymentvalue->payment_method_id==1)
                   {
                        $scash    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==2)
                   {
                        $scard    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==3)
                   {
                        $scheque    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==5)
                   {
                        $swallet    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==6)
                   {    
                        $sunpaidamt    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==7)
                   {
                        $snetbanking    +=  $paymentvalue->total_bill_amount;
                   }
                   if($paymentvalue->payment_method_id==8)
                   {
                        $screditnote    +=  $paymentvalue->total_bill_amount;
                   }
                }

                                            
                $grandtotal     +=   $totsales->total_bill_amount;
            }

            $returnbill    =  array();
            $rtaxabletariff = 0;
            $rtotalcgst = 0;
            $rtotalsgst = 0;
            $rtotaligst = 0;
            $rgrandtotal = 0;
            $rcash  = 0;
            $rcard = 0;
            $rcheque = 0;
            $rwallet = 0;
            $runpaidamt=0;
            $rnetbanking =0;
            $rcreditnote = 0;

            

            $todaytaxable   =   $taxabletariff - $rtaxabletariff;
            $todaycgst      =   $totalcgst - $rtotalcgst;
            $todaysgst      =   $totalsgst - $rtotalsgst;
            $todayigst      =   $totaligst - $rtotaligst;
            $todaygrand     =   $grandtotal - $rgrandtotal;
            $cash           =   $scash  -  $rcash;
            $card           =   $scard  -  $rcard;
            $cheque         =   $scheque  -  $rcheque;
            $wallet         =   $swallet  - $rwallet;
            $unpaidamt      =   $sunpaidamt - $runpaidamt;
            $netbanking     =   $snetbanking - $rnetbanking;
            $creditnote     =   $screditnote - $rcreditnote;

               $max_date  =  $sdata->max('bill_date');
               $min_date  =  $sdata->min('bill_date');
           

           
        return view('salesreport::view_bill',compact('sales','payment_methods','count','todaytaxable','todaycgst','todaysgst','todayigst','todaygrand','maxsales_id','minsales_id','company_state','returnbill','returnsales','rmaxsales_id','rminsales_id','tax_type','taxname','max_date','min_date','cash','card','cheque','wallet','unpaidamt','netbanking','creditnote','showedits','get_store','companyname'))->render();

        
    }

     //FOR DOWNLOAD Bills TEMPLATE
    public function bill_template(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        return Excel::download(new bill_template(), "bill_template.xlsx");
    }

    public function sales_check(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $company_data = company_profile::where('company_id',Auth::user()->company_id)
            ->select('bill_excel_column_check','bill_calculation','bill_number_prefix')->first();

        $sales_excel_data = $request->all();

       // exit;

        if(isset($sales_excel_data) && $sales_excel_data != '')
        {
            $error = 0;
            foreach ($sales_excel_data AS $key=>$value) {

                $validate_value['state_name'] = $value['State'];

                if($company_data['bill_excel_column_check']==1)
                {
                    $validate_value['product_code'] = $value['Product Code'];
                    if ($value['Product Code'] != '')
                    {
                        if (!product::where('product_code', $value['Product Code'])->exists())
                        {
                            $error = 1;
                            return json_encode(array("Success" => "False", "Message" => "Product Code " . $value['Product Code'] . " Not Found!"));
                            exit;
                        }
                    }       
                }
                else
                {
                    $validate_value['product_system_barcode'] = $value['Barcode'];
                    $validate_value['supplier_barcode'] = $value['Barcode'];

                    if ($value['Barcode'] != '')
                    {
                        if (!product::where('product_system_barcode', $value['Barcode'])->exists() && !product::where('supplier_barcode', $value['Barcode'])->exists())
                        {
                            $error = 1;
                            return json_encode(array("Success" => "False", "Message" => "Barcode " . $value['Barcode'] . " Not Found!"));
                            exit;
                        }
                    }   
                }

                
                $validate_value['reference_name'] = $value['Portal'];

                
                             
                if ($value['State'] != '')
                {
                    if (!state::where('state_name', $value['State'])->exists())
                    {
                        $error = 1;
                        return json_encode(array("Success" => "False", "Message" => "State Name " . $value['State'] . " Not Found!"));
                        exit;
                    }
                }
                if ($value['Portal'] != '')
                {
                    if (!reference::where('reference_name', $value['Portal'])->exists())
                    {
                        $error = 1;
                        return json_encode(array("Success" => "False", "Message" => "Portal Name " . $value['Portal'] . " Not Found!"));
                        exit;
                    }
                }
            }



          if($error == 0)
          {
              $userId = Auth::User()->user_id;
              $company_id = Auth::User()->company_id;

              if($company_data['bill_calculation']==1)
              {
                  $price   =   $value['Price'];
              }
              else
              {
                  $price   =   0;
              }


              $created_by = $userId;
              try{


                  $state_id = company_profile::select('state_id')
                      ->where('company_id',Auth::user()->company_id)->get();

                  foreach ($sales_excel_data AS $key=>$value)
                  {
                    

                     $day   =  date("d",strtotime($value['Date']));
                     $month =  date("m", strtotime($value['Month']));
                     $year  =  date("Y", strtotime($value['Year']));

                     // echo $month;
                     // exit;
                     
                     $invoiceddate  =  $day.'-'.$month.'-'.$year;
///////////////////////Code to Product detail from system/////////////////////////////////////////////////////
                     $uniqueno   =  '';
                    $uniquelabel = '';
                    

                    $productid = product::select('product_id','product_system_barcode')
                                          ->whereIn('item_type',array(1,3));
                              
                     if($company_data['bill_excel_column_check']==1)
                     {
                         $uniqueno    =  $value['Product Code'];
                         $uniquelabel =  'Product Code';
                         $productid->where('product_code',$value['Product Code']);
                     }
                     else
                     {
                         $uniqueno   =  $value['Barcode'];
                         $uniquelabel =  'Barcode';
                         $productid->where('product_system_barcode',$value['Barcode']);
                         $productid->orWhere('supplier_barcode',$value['Barcode']);
                     }           

                     $productid   =   $productid->with('reportprice_master')
                                      ->whereHas('reportprice_master',function ($q) {
                                              $q->where('company_id',Auth::user()->company_id);
                                       })->first();



 //////////////////////////////////////////////////////////////////end/////////////////////////////////////////////                                     
                    
                    
//////////////////////////////////////////////////code to check if Bill No already exist///////////////////////////

                      $sales_id = sales_bill::select('sales_bill_id')
                                  ->where('company_id',Auth::user()->company_id)
                                  ->where('order_no',$value['Order ID/PO NO'])
                                  ->first();

                        $productdetail     =    array();

 //////////////////////////////////////if exist then update amount in same order billno./////////////////////////////////////////   
                        if($sales_id!='')
                        {
                            $sales_bill_id   =    $sales_id->sales_bill_id;
///////////////////////////////////////if the product is already exist in same order no before to prevent duplication//////////////
                            $salesproduct_check   =    sales_product_detail::where('company_id',Auth::user()->company_id)  
                                                      ->where('sales_bill_id',$sales_id['sales_bill_id']) 
                                                      ->where('product_id',$productid['product_id'])
                                                      ->where('deleted_at',NULL)
                                                      ->first();
                             if($salesproduct_check !='')
                             {
                                   return json_encode(array("Success" => "False", "Message" => " ".$uniquelabel." " . $uniqueno . " already exist with Order No. ".$value['Order ID/PO NO']." "));
                                    exit;
                             }
                             else
                             {


                             $priceid  = price_master::select('price_master_id','selling_gst_percent')
                                         ->where('product_id',$productid['product_id'])
                                         ->where('company_id',Auth::user()->company_id)
                                         ->where('product_qty','>',0)
                                         ->orderBy('price_master_id','ASC')
                                         ->first();  

                            if($priceid=='') 
                            {


                              return json_encode(array("Success" => "False", "Message" => "No Stock Avaible for the ".$uniquelabel." " . $uniqueno . " in System. Kindly re-upload Excel from the Row Containing this product No.!"));
                              exit;
                            }   

                           else
                            {   

                                       $sellgst         =    0;
                                       $mrp             =    0;
                                       $gstamt          =    0;
                                       $gstamount       =    0;
                                       $halfgstamount   =    0;
                                       $halfgstper      =    0;

                                       $sellingprice    =    0;     

                                     if($company_data['bill_calculation']==1)
                                     {   
                                         $sellgst         =    $priceid['selling_gst_percent'];   
                                         $mrp             =    $price  / $value['Order Qty'];
                                         $gstamt          =    ($mrp/($sellgst+100)) * $sellgst;
                                         $gstamount       =     $gstamt * $value['Order Qty'];
                                         $halfgstamount   =     $gstamount /2;
                                         $halfgstper      =     $priceid['selling_gst_percent'] /2;

                                         $sellingprice    =     $mrp - $gstamt;
                                       }

                           
                        
                              
                              
                              $productdetail['product_id']                           =    $productid['product_id'];
                              $productdetail['price_master_id']                      =    $priceid['price_master_id'];
                              $productdetail['qty']                                  =    $value['Order Qty'];
                              $productdetail['mrp']                                  =    $mrp;
                              $productdetail['sellingprice_before_discount']         =    $sellingprice;
                              $productdetail['discount_percent']                     =    0;
                              $productdetail['discount_amount']                      =    0;
                              $productdetail['sellingprice_after_discount']          =    $sellingprice;
                              $productdetail['overalldiscount_percent']              =    0;
                              $productdetail['overalldiscount_amount']               =    0;
                              $productdetail['sellingprice_afteroverall_discount']   =    $sellingprice;
                              $productdetail['cgst_percent']                         =    $halfgstper;
                              $productdetail['cgst_amount']                          =    $halfgstamount;
                              $productdetail['sgst_percent']                         =    $halfgstper;
                              $productdetail['sgst_amount']                          =    $halfgstamount;
                              $productdetail['igst_percent']                         =    $sellgst;
                              $productdetail['igst_amount']                          =    $gstamount;
                              $productdetail['total_amount']                         =    $price;
                              $productdetail['product_type']                         =     1;
                              $productdetail['created_by']                           =     Auth::User()->user_id;

                              price_master::where('price_master_id',$priceid['price_master_id'])->update(array(
                              'modified_by' => Auth::User()->user_id,
                              'updated_at' => date('Y-m-d H:i:s'),
                              'product_qty' => DB::raw('product_qty - '.$value['Order Qty'])
                              ));    

                                   /////FIFO logic

                                           $ccount    =   0;  
                                           $icount    =   0;
                                           $pcount    =   0;
                                           $done      =   0;
                                           $firstout  =   0;
                                           $restqty   =   $value['Order Qty']; 
                                           $inwardids    =  '';
                                           $inwardqtys   =  '';           

                                      if($value['Order Qty']>0)
                                      {
                                         

                                           $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                                                                ->where('product_id',$productid['product_id'])
                                                                ->where('company_id',Auth::user()->company_id)
                                                                ->where('pending_return_qty','!=',0);

                                          $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                                           if(sizeof($inwarddetail)==0)
                                          {
                                            
                                                    
                                                    return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details for ".$uniquelabel." ".$uniqueno." "));
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
                                                                  'pending_return_qty' => DB::raw('pending_return_qty - '.$value['Order Qty'])
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

                                    
                                    if($inwardids!='')
                                    {
                                      $productdetail['inwardids']                          =    $inwardids;
                                      $productdetail['inwardqtys']                         =    $inwardqtys;
                                    }   
                                    else
                                    {  
                                      $productdetail['inwardids']                          =    NULL;
                                      $productdetail['inwardqtys']                         =    NULL;
                                    }


                ///end of FIFO Logic


                              $billproductdetail = sales_product_detail::updateOrCreate(
                               ['sales_bill_id' => $sales_bill_id,
                                'company_id'=>$company_id,'sales_products_detail_id'=>'',],
                               $productdetail); 

                             

                             $sales =  sales_bill::where('sales_bill_id',$sales_bill_id)->update(array(
                              'modified_by' => Auth::User()->user_id,
                              'updated_at' => date('Y-m-d H:i:s'),
                              'total_qty' => DB::raw('total_qty + '.$value['Order Qty']),
                              'sellingprice_before_discount' => DB::raw('sellingprice_before_discount + '.$sellingprice),
                              'discount_percent'=>0,
                              'discount_amount'=>0,
                              'productwise_discounttotal'=>0,
                              'sellingprice_after_discount' => DB::raw('sellingprice_after_discount + '.$sellingprice),
                              'totalbillamount_before_discount' => DB::raw('totalbillamount_before_discount + '.$sellingprice),
                              'total_igst_amount' => DB::raw('total_igst_amount + '.$gstamount),
                              'total_cgst_amount' => DB::raw('total_cgst_amount + '.$halfgstamount),
                              'total_sgst_amount' => DB::raw('total_sgst_amount + '.$halfgstamount),
                              'gross_total' => DB::raw('gross_total + '.$price),
                              'shipping_charges'=>0,
                              'total_bill_amount'=>DB::raw('total_bill_amount + '.$price)
                              )); 
                             
                             if($company_data['bill_calculation']==1)
                             {
                                  sales_bill_payment_detail::where('sales_bill_id',$sales_bill_id)->update(array(
                                  'modified_by' => Auth::User()->user_id,
                                  'updated_at' => date('Y-m-d H:i:s'),
                                  'total_bill_amount' => DB::raw('total_bill_amount + '.$price)
                                  )); 
                                  customer_creditaccount::where('sales_bill_id',$sales_bill_id)->update(array(
                                  'modified_by' => Auth::User()->user_id,
                                  'updated_at' => date('Y-m-d H:i:s'),
                                  'credit_amount' => DB::raw('credit_amount + '.$price),
                                  'balance_amount' => DB::raw('balance_amount + '.$price)
                                  ));  
                             } 
                            
                          }
                        }
                        
                             
                        }
                        ////would create a new bill
                        else
                        {
                            
                            

                            
                             $priceid  = price_master::select('price_master_id','selling_gst_percent')
                                         ->where('product_id',$productid['product_id'])
                                         ->where('company_id',Auth::user()->company_id)
                                         ->where('product_qty','>',0)
                                         ->orderBy('price_master_id','ASC')
                                         ->first(); 
                                    
                               if($priceid=='') 
                              {


                                return json_encode(array("Success" => "False", "Message" => "No Stock Avaible for the ".$uniquelabel." " . $uniqueno . " in System. Kindly re-upload Excel from the Row Containing this product No.!"));
                                exit;
                              }   

                             else
                              {      
                                       $sellgst         =    0;
                                       $mrp             =    0;
                                       $gstamt          =    0;
                                       $gstamount       =    0;
                                       $halfgstamount   =    0;
                                       $halfgstper      =    0;

                                       $sellingprice    =    0;     

                                     if($company_data['bill_calculation']==1)
                                     {
                                           $sellgst         =    $priceid['selling_gst_percent'];
                                           $mrp             =    $price  / $value['Order Qty'];
                                           $gstamt          =    ($mrp/($sellgst+100)) * $sellgst;
                                           $gstamount       =     $gstamt * $value['Order Qty'];
                                           $halfgstamount   =     $gstamount /2;
                                           $halfgstper      =     $sellgst /2;

                                           $sellingprice    =     $mrp - $gstamt;
                                     }
                                     
                                     

                                     $refid   = reference::select('reference_id')
                                               ->where('reference_name',$value['Portal'])
                                               ->where('company_id',Auth::user()->company_id)
                                               ->first();

                                     $stateid = state::select('state_id')
                                               ->where('state_name',$value['State'])
                                               ->first();
                                     $companyprofile = company_profile::select('state_id')
                                     ->where('company_id',Auth::user()->company_id)
                                     ->first();


                                      $showcustomer_id = customer::select('customer_id')
                                                                  ->where('customer_name',$value['Name'])
                                                                  ->where('customer_mobile',$value['CONTACT NO'])
                                                                  ->where('company_id',Auth::user()->company_id)->first(); 

                                      if($showcustomer_id!='')
                                      {
                                          $customer_id = $showcustomer_id->customer_id;
                                      } 
                                      else
                                      {
                                            $dial_code = '';
                                                  if($value['CONTACT NO'] != '')
                                                  {
                                                      
                                                          $dial_code = company_profile::select('company_mobile_dial_code')
                                                              ->where('company_id',Auth::user()->company_id)->first();

                                                          $code = explode(',',$dial_code['company_mobile_dial_code']);


                                                          $dial_code = $code[0];
                                                     
                                                  } 

                                                $customer = customer::updateOrCreate(
                                                  ['customer_id' => '', 'company_id' => $company_id,],
                                                  [
                                                      'created_by' => $created_by,
                                                      'company_id' => $company_id,
                                                      'customer_name' => (isset($value['Name']) ? $value['Name'] : ''),
                                                      'customer_mobile_dial_code' => (isset($dial_code) ? $dial_code : ''),
                                                      'customer_mobile' => (isset($value['CONTACT NO']) && $value['CONTACT NO'] != '' ? $value['CONTACT NO'] : NULL),
                                                      'customer_email' => NULL,
                                                      'is_active' => "1"
                                                  ]
                                               );

                                               $customer_id = $customer->customer_id;
                                               $customer_address = customer_address_detail::updateOrCreate(
                                              ['customer_id' => $customer_id,
                                               'company_id'=>$company_id,],
                                              [
                                                  'created_by' =>$created_by,
                                                  'customer_gstin' => (isset($value['GST NO'])?$value['GST NO'] : ''),
                                                  'customer_address_type' => '1',
                                                  'customer_address' => '',
                                                  'customer_area' => '',
                                                  'customer_city' => (isset($value['City '])?$value['City '] : ''),
                                                  'customer_pincode' =>'',
                                                  'state_id' => (isset($value['State']) && $value['State'] != ''?$stateid['state_id'] : $companyprofile['state_id']),
                                                  'country_id' => 102,
                                                  'is_active' => "1"
                                               ]
                                             );
                                      }                           

                                      
                                   $invoice_no    = '';
                                     $sales = sales_bill::updateOrCreate(
                                    ['sales_bill_id' => '', 'company_id'=>$company_id,],
                                    ['customer_id'=>$customer_id,
                                        'bill_no'=>$invoice_no,
                                        'order_no'=>$value['Order ID/PO NO'],
                                        'bill_date'=>$invoiceddate,
                                        'state_id'=>$stateid['state_id'],
                                        'reference_id'=>$refid['reference_id'],
                                        'total_qty'=>$value['Order Qty'],
                                        'sellingprice_before_discount'=>$sellingprice,
                                        'discount_percent'=>0,
                                        'discount_amount'=>0,
                                        'productwise_discounttotal'=>0,
                                        'sellingprice_after_discount'=>$sellingprice,
                                        'totalbillamount_before_discount'=>$sellingprice,
                                        'total_igst_amount'=>$gstamount,
                                        'total_cgst_amount'=>$halfgstamount,
                                        'total_sgst_amount'=>$halfgstamount,
                                        'gross_total'=>$price,
                                        'shipping_charges'=>0,
                                        'total_bill_amount'=>$price,
                                        'created_by' =>$created_by,
                                        'is_active' => "1"
                                    ]
                                );

                                   $sales_bill_id = $sales->sales_bill_id;

                                    $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
                                    $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');


                                      $newseries  =  sales_bill::select('bill_series')
                                                    ->where('sales_bill_id','<',$sales_bill_id)
                                                    ->where('company_id',Auth::user()->company_id)
                                                    ->orderBy('sales_bill_id','DESC')
                                                    ->take('1')
                                                    ->first();    

                                      $billseries   = $newseries['bill_series']+1;

                                      $finalinvoiceno   =  $company_data['bill_number_prefix'].$billseries.'/'.$f1.'-'.$f2; 
                                   
                                    

                                     sales_bill::where('sales_bill_id',$sales_bill_id)->update(array(
                                        'bill_no' => $finalinvoiceno,
                                        'bill_series' => $billseries
                                     ));


                                
                                      
                                     
                                      $productdetail['product_id']                           =    $productid['product_id'];
                                      $productdetail['price_master_id']                      =    $priceid['price_master_id'];
                                      $productdetail['qty']                                  =    $value['Order Qty'];
                                      $productdetail['mrp']                                  =    $mrp;
                                      $productdetail['sellingprice_before_discount']         =    $sellingprice;
                                      $productdetail['discount_percent']                     =    0;
                                      $productdetail['discount_amount']                      =    0;
                                      $productdetail['sellingprice_after_discount']          =    $sellingprice;
                                      $productdetail['overalldiscount_percent']              =    0;
                                      $productdetail['overalldiscount_amount']               =    0;
                                      $productdetail['sellingprice_afteroverall_discount']   =    $sellingprice;
                                      $productdetail['cgst_percent']                         =    $halfgstper;
                                      $productdetail['cgst_amount']                          =    $halfgstamount;
                                      $productdetail['sgst_percent']                         =    $halfgstper;
                                      $productdetail['sgst_amount']                          =    $halfgstamount;
                                      $productdetail['igst_percent']                         =    $sellgst;
                                      $productdetail['igst_amount']                          =    $gstamount;
                                      $productdetail['total_amount']                         =    $price;
                                      $productdetail['product_type']                         =     1;
                                      $productdetail['created_by']                           =     Auth::User()->user_id;

                                  price_master::where('price_master_id',$priceid['price_master_id'])->update(array(
                                  'modified_by' => Auth::User()->user_id,
                                  'updated_at' => date('Y-m-d H:i:s'),
                                  'product_qty' => DB::raw('product_qty - '.$value['Order Qty'])
                                  ));   

                               /////FIFO logic

                               $ccount    =   0;  
                               $icount    =   0;
                               $pcount    =   0;
                               $done      =   0;
                               $firstout  =   0;
                               $restqty   =   $value['Order Qty']; 
                               $inwardids    =  '';
                               $inwardqtys   =  '';           

                          if($value['Order Qty']>0)
                          {
                             

                               $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                                                    ->where('product_id',$productid['product_id'])
                                                    ->where('company_id',Auth::user()->company_id)
                                                    ->where('pending_return_qty','!=',0);

                              $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                               if(sizeof($inwarddetail)==0)
                                {
                                  
                                          
                                          return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details for ".$uniquelabel." ".$uniqueno." "));
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
                                                      'pending_return_qty' => DB::raw('pending_return_qty - '.$value['Order Qty'])
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

                        
                        if($inwardids!='')
                        {
                          $productdetail['inwardids']                          =    $inwardids;
                          $productdetail['inwardqtys']                         =    $inwardqtys;
                        }   
                        else
                        {  
                          $productdetail['inwardids']                          =    NULL;
                          $productdetail['inwardqtys']                         =    NULL;
                        }


                        ///end of FIFO Logic
                                      $billproductdetail = sales_product_detail::updateOrCreate(
                                       ['sales_bill_id' => $sales_bill_id,
                                        'company_id'=>$company_id,'sales_products_detail_id'=>'',],
                                       $productdetail); 

                                      if($company_data['bill_calculation']==1)
                                      { 
                                             $sales_payment = sales_bill_payment_detail::updateOrCreate(
                                            ['sales_bill_payment_detail_id' => ''],
                                            ['sales_bill_id'=>$sales_bill_id,
                                                'total_bill_amount'=>$price,
                                                'payment_method_id'=>6,
                                                'created_by' =>$created_by,
                                                'is_active' => "1"
                                            ]
                                          );  
                                           $sales_credit = customer_creditaccount::updateOrCreate(
                                            ['sales_bill_id' => $sales_bill_id, 'company_id'=>$company_id,],
                                            ['customer_id'=>$customer_id,
                                                'bill_date'=>$invoiceddate,
                                                'credit_amount'=>$price,
                                                'balance_amount'=>$price,
                                                'created_by' =>$created_by,
                                                'deleted_at' =>NULL,
                                                'deleted_by' =>NULL,
                                                'is_active' => "1"
                                                ]
                                            );
                                      }

                                      

                                }
                                                                

                        }


       



                      
                      if ($sales)
                      {
                          if(!next( $sales_excel_data ))
                          {

                              return json_encode(array("Success" => "True", "Message" => "Sales has been successfully Added."));
                          }
                      }
                      else
                      {
                          return json_encode(array("Success" => "False", "Message" => "Something Went Wrong"));
                          exit;
                      }
                  }
              }catch (\Exception $e)
              {
                  return json_encode(array("Success" => "False", "Message" => $e->getMessage()));
                  exit;
              }
          }
        }
    }




}
