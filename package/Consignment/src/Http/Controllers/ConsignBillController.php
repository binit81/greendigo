<?php

namespace Retailcore\Consignment\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Products\Models\product\product_image;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Consignment\Models\consign_bill;
use Retailcore\Consignment\Models\consign_products_detail;
use Retailcore\Consignment\Models\consign_payment_detail;
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
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\SalesReturn\Models\returnbill_product;
use Retailcore\SalesReturn\Models\return_bill_payment;
use Auth;
use DB;
use Log;
class ConsignBillController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $ppvalues = array();
        $state    = state::all();
        $country  = country::all();

        
      
       $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->orderBy('payment_order','ASC')->get();
       $cstate_id = company_profile::select('state_id','billtype','bill_number_prefix','tax_type','billprint_type','series_type','bill_number_prefix','country_id')->where('company_id',Auth::user()->company_id)->get(); 
        $last_invoice_id = consign_bill::where('company_id',Auth::user()->company_id)->get()->max('consign_bill_id');

      

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

                  $newseries  =  consign_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') >= '$newmonth'")
                                            ->where('consign_bill_id','<',$last_invoice_id)
                                            ->orderBy('consign_bill_id','DESC')
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
                $newseries  =  consign_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') <= '$todate'")
                                            ->where('consign_bill_id','<',$last_invoice_id)
                                            ->orderBy('consign_bill_id','DESC')
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



       
        return view('consignment::consign_challan',compact('payment_methods','invoiceno','state','country','chargeslist','ppvalues','customer_source'));
    }
    public function consignbill_create(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

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

          consign_bill::where('consign_bill_id',$data[1]['sales_bill_id'])->update(array(
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

        $sales = consign_bill::updateOrCreate(
            ['consign_bill_id' => $data[1]['sales_bill_id'], 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
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


       $sales_bill_id = $sales->consign_bill_id;


  //////////////////////////////////// To make Bill series Month Wise and Year wise as per the value selected from Company Profile......

        $todate       =    date('Y-m-d');
        
        $newyear      =   date('Y-04-01');
        
        $newmonth     =   date('Y-m-01');

//////////////////For Bill series Year Wise 
        if($cstate_id[0]['series_type']==1)
        {

            $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
            $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

            $finalinvoiceno          =       $sales_bill_id.'/'.$f1.'-'.$f2;  
            $billseries              =       NULL;
                
             
        }

 //////////////////For Bill series Month Wise        
        else
        {
            if($todate>=$newmonth)
              {

                  $newseries  =  consign_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') >= '$newmonth'")
                                            ->where('consign_bill_id','<',$sales_bill_id)
                                            ->orderBy('consign_bill_id','DESC')
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
                $newseries  =  consign_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') <= '$todate'")
                                            ->where('consign_bill_id','<',$sales_bill_id)
                                            ->orderBy('consign_bill_id','DESC')
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
              
              $finalinvoiceno = $dd.''.$id1;
        }   
              

        

        if($data[1]['sales_bill_id']=='' || $data[1]['sales_bill_id']==null)
        {  

         consign_bill::where('consign_bill_id',$sales_bill_id)->update(array(
            'bill_no' => $finalinvoiceno,
            'bill_series' => $billseries
         ));
       }
    

       consign_products_detail::where('consign_bill_id',$sales_bill_id)->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));

    
        $productdetail     =    array();

       

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
                                            ->where('company_id',Auth::user()->company_id)
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
                        
                                
                                return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details"));
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

                $billproductdetail = consign_products_detail::updateOrCreate(
                   ['consign_bill_id' => $sales_bill_id,
                    'company_id'=>$company_id,'consign_products_detail_id'=>$billvalue['sales_product_id'],],
                   $productdetail);

              
      }
     
     
            
     }


          

      consign_payment_detail::where('consign_bill_id',$sales_bill_id)->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
            'total_bill_amount'=>0
        ));

    $creditnoteamount   = 0;

      foreach($data[2] AS $pkey=>$pvalue)
      {
         if($pvalue['id']!=6)
          {
                $creditnoteamount  +=   $pvalue['value'];
          } 
            
     }
   // if($creditnoteamount > 0)
   // {

   //          $last_invoice_id = customer_creditnote::where('company_id',Auth::user()->company_id)->get()->max('customer_creditnote_id');
   //              $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
   //              $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

   //             if($last_invoice_id == '')
   //              {
   //                  $last_invoice_id = 1;
   //              }
   //              else
   //              {
   //                  $last_invoice_id = $last_invoice_id  + 1;
   //              }
                
   //              $creditnote_no          =       'CRE-'.$last_invoice_id.'/'.$f1.'-'.$f2;  

   //               customer_creditnote::where('customer_creditnote_id',$data[1]['editcreditnoteid'])->update(array(
   //                  'modified_by' => Auth::User()->user_id,
   //                  'updated_at' => date('Y-m-d H:i:s')
   //                ));

   //               // $credit_amount  = $data[1]['ggrand_total'];
   //                  $credit_amount  = $creditnoteamount;
   //           if($creditnoteamount > 0)
   //           {
   //               $creditid = customer_creditnote::updateOrCreate(
   //                  ['customer_creditnote_id' => $data[1]['editcreditnoteid'], 'company_id'=>$company_id,],
   //                  ['customer_id'=>$data[1]['customer_id'],
   //                  'consign_bill_id'=>$sales_bill_id,
   //                  'creditnote_type'=>2,
   //                  'creditnote_no'=>$creditnote_no,
   //                      'creditnote_date'=>$invoice_date,
   //                      'creditnote_amount'=>$credit_amount,
   //                      'balance_amount'=>$credit_amount,
   //                      'created_by' =>$created_by,
   //                      'is_active' => "1"
   //                  ]
   //              );



   //             $customer_creditnote_id = $creditid->customer_creditnote_id;


   //                    $todate       =    date('Y-m-d');
                      
   //                    $newyear      =   date('Y-04-01');
                      
   //                    $newmonth     =   date('Y-m-01');

   //      //////////////////For Credit Number series Year Wise 
   //              if($cstate_id[0]['series_type']==1)
   //              {

   //                  $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
   //                  $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

   //                  $finalinvoiceno          =       $cstate_id[0]['credit_receipt_prefix'].$customer_creditnote_id.'/'.$f1.'-'.$f2;  
   //                  $billseries              =       NULL;
                        
                     
   //              }

   //       //////////////////For Bill series Month Wise        
   //              else
   //              {
   //                  if($todate>=$newmonth)
   //                    {

   //                        $newseries  =  customer_creditnote::select('creditno_series')
   //                                                  ->whereRaw("STR_TO_DATE(customer_creditnotes.creditnote_date,'%d-%m-%Y') >= '$newmonth'")
   //                                                  ->where('customer_creditnote_id','<',$customer_creditnote_id)
   //                                                  ->orderBy('customer_creditnote_id','DESC')
   //                                                  ->take('1')
   //                                                  ->first();
                               
                       
   //                        if($newseries=='')
   //                        {
   //                            $billseries  =  1;
   //                        }
   //                        else
   //                        {
   //                            $billseries   = $newseries['creditno_series']+1;
                              
   //                        }
                         
                       
   //                    }
   //                    else
   //                    {
   //                      $newseries  =  customer_creditnote::select('creditno_series')
   //                                                  ->whereRaw("STR_TO_DATE(customer_creditnotes.creditnote_date,'%d-%m-%Y') <= '$todate'")
   //                                                  ->where('customer_creditnote_id','<',$customer_creditnote_id)
   //                                                  ->orderBy('customer_creditnote_id','DESC')
   //                                                  ->take('1')
   //                                                  ->first();
   //                            $billseries   = $newseries['creditno_series']+1;

                        
   //                    }

   //                    $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
   //                    $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');
                     
   //                    $co     =     strlen($billseries);  
            
   //                    if($co<=2)
   //                    $id1  = '00'.$billseries; 
   //                    elseif($co<=3)
   //                    $id1  = '0'.$billseries;      
   //                    elseif($co<=4)
   //                    $id1  = $billseries;
   //                    $dd   = date('my');
                      
   //                    $finalinvoiceno = $cstate_id[0]['credit_receipt_prefix'].$dd.''.$id1;
   //              } 

   //              customer_creditnote::where('customer_creditnote_id',$customer_creditnote_id)->update(array(
   //                  'creditnote_no' => $finalinvoiceno,
   //                  'creditno_series' => $billseries
   //               ));
   //  }



    $paymentanswers     =    array();
    

         foreach($data[2] AS $key=>$value2)
          {
            
             if($value2['id']==3)
              {
                $paymentanswers['bankname']    =   $data[1]['bankname'];
                $paymentanswers['chequeno']    =   $data[1]['chequeno'];
              }
              elseif($value2['id']==7)
              {
                $paymentanswers['bankname']    =     $data[1]['netbankname'];
                $paymentanswers['chequeno']    =     '';
              }
              elseif($value2['id']==6)
              {
                $paymentanswers['bankname']    =     $data[1]['duedate'];
                $paymentanswers['chequeno']    =     '';
              }
              elseif($value2['id']==8)
              {
               
                $paymentanswers['bankname']='';
                $paymentanswers['chequeno'] =  '';

              }
              else
              {
                $paymentanswers['bankname']='';
                $paymentanswers['chequeno'] =  '';
              }
           
                
                $paymentanswers['consign_bill_id']               =  $sales_bill_id;
                $paymentanswers['total_bill_amount']             =  $value2['value'];
                $paymentanswers['payment_method_id']             =  $value2['id'];
                $paymentanswers['created_by']                    =  Auth::User()->user_id;
                $paymentanswers['deleted_at'] =  NULL;
                $paymentanswers['deleted_by'] =  NULL;
                
            
           
       
           $paymentdetail = consign_payment_detail::updateOrCreate(
               ['consign_bill_id' => $sales_bill_id,'consign_payment_detail_id'=>$value2['sales_payment_id'],],
               $paymentanswers);


            
            }
       // /}



     DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }


        if($billproductdetail)
        {
            
           if($data[1]['sales_bill_id'] != '')
          {   
              return json_encode(array("Success"=>"True","Message"=>"Billing successfully Update!","url"=>"consign_challan"));
          }
          else
          {
              return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>"consign_challan"));
          }

               

           
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
        //return back()->withInput();

    }

public function consignbillprint_create(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

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

          consign_bill::where('consign_bill_id',$data[1]['sales_bill_id'])->update(array(
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

        $sales = consign_bill::updateOrCreate(
            ['consign_bill_id' => $data[1]['sales_bill_id'], 'company_id'=>$company_id,],
            ['customer_id'=>$data[1]['customer_id'],
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


       $sales_bill_id = $sales->consign_bill_id;


  //////////////////////////////////// To make Bill series Month Wise and Year wise as per the value selected from Company Profile......

        $todate       =    date('Y-m-d');
        
        $newyear      =   date('Y-04-01');
        
        $newmonth     =   date('Y-m-01');

//////////////////For Bill series Year Wise 
        if($cstate_id[0]['series_type']==1)
        {

            $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
            $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

            $finalinvoiceno          =       $sales_bill_id.'/'.$f1.'-'.$f2;  
            $billseries              =       NULL;
                
             
        }

 //////////////////For Bill series Month Wise        
        else
        {
            if($todate>=$newmonth)
              {

                  $newseries  =  consign_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') >= '$newmonth'")
                                            ->where('consign_bill_id','<',$sales_bill_id)
                                            ->orderBy('consign_bill_id','DESC')
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
                $newseries  =  consign_bill::select('bill_series')
                                            ->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') <= '$todate'")
                                            ->where('consign_bill_id','<',$sales_bill_id)
                                            ->orderBy('consign_bill_id','DESC')
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
              
              $finalinvoiceno = $dd.''.$id1;
        }   
              

        

        if($data[1]['sales_bill_id']=='' || $data[1]['sales_bill_id']==null)
        {  

         consign_bill::where('consign_bill_id',$sales_bill_id)->update(array(
            'bill_no' => $finalinvoiceno,
            'bill_series' => $billseries
         ));
       }
    

       consign_products_detail::where('consign_bill_id',$sales_bill_id)->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));

    
        $productdetail     =    array();

       

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
                                            ->where('company_id',Auth::user()->company_id)
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
                        
                                
                                return json_encode(array("Success"=>"False","Message"=>"Bill Cannot be saved as there is no Entry Found in Inward Product Details"));
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

                $billproductdetail = consign_products_detail::updateOrCreate(
                   ['consign_bill_id' => $sales_bill_id,
                    'company_id'=>$company_id,'consign_products_detail_id'=>$billvalue['sales_product_id'],],
                   $productdetail);

              
      }
     
     
            
     }


          

      consign_payment_detail::where('consign_bill_id',$sales_bill_id)->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s'),
            'total_bill_amount'=>0
        ));

    $creditnoteamount   = 0;

      foreach($data[2] AS $pkey=>$pvalue)
      {
         if($pvalue['id']!=6)
          {
                $creditnoteamount  +=   $pvalue['value'];
          } 
            
     }
   // if($creditnoteamount > 0)
   // {

   //          $last_invoice_id = customer_creditnote::where('company_id',Auth::user()->company_id)->get()->max('customer_creditnote_id');
   //              $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
   //              $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

   //             if($last_invoice_id == '')
   //              {
   //                  $last_invoice_id = 1;
   //              }
   //              else
   //              {
   //                  $last_invoice_id = $last_invoice_id  + 1;
   //              }
                
   //              $creditnote_no          =       'CRE-'.$last_invoice_id.'/'.$f1.'-'.$f2;  

   //               customer_creditnote::where('customer_creditnote_id',$data[1]['editcreditnoteid'])->update(array(
   //                  'modified_by' => Auth::User()->user_id,
   //                  'updated_at' => date('Y-m-d H:i:s')
   //                ));

   //               // $credit_amount  = $data[1]['ggrand_total'];
   //                  $credit_amount  = $creditnoteamount;
   //           if($creditnoteamount > 0)
   //           {
   //               $creditid = customer_creditnote::updateOrCreate(
   //                  ['customer_creditnote_id' => $data[1]['editcreditnoteid'], 'company_id'=>$company_id,],
   //                  ['customer_id'=>$data[1]['customer_id'],
   //                  'consign_bill_id'=>$sales_bill_id,
   //                  'creditnote_type'=>2,
   //                  'creditnote_no'=>$creditnote_no,
   //                      'creditnote_date'=>$invoice_date,
   //                      'creditnote_amount'=>$credit_amount,
   //                      'balance_amount'=>$credit_amount,
   //                      'created_by' =>$created_by,
   //                      'is_active' => "1"
   //                  ]
   //              );



   //             $customer_creditnote_id = $creditid->customer_creditnote_id;


   //                    $todate       =    date('Y-m-d');
                      
   //                    $newyear      =   date('Y-04-01');
                      
   //                    $newmonth     =   date('Y-m-01');

   //      //////////////////For Credit Number series Year Wise 
   //              if($cstate_id[0]['series_type']==1)
   //              {

   //                  $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
   //                  $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

   //                  $finalinvoiceno          =       $cstate_id[0]['credit_receipt_prefix'].$customer_creditnote_id.'/'.$f1.'-'.$f2;  
   //                  $billseries              =       NULL;
                        
                     
   //              }

   //       //////////////////For Bill series Month Wise        
   //              else
   //              {
   //                  if($todate>=$newmonth)
   //                    {

   //                        $newseries  =  customer_creditnote::select('creditno_series')
   //                                                  ->whereRaw("STR_TO_DATE(customer_creditnotes.creditnote_date,'%d-%m-%Y') >= '$newmonth'")
   //                                                  ->where('customer_creditnote_id','<',$customer_creditnote_id)
   //                                                  ->orderBy('customer_creditnote_id','DESC')
   //                                                  ->take('1')
   //                                                  ->first();
                               
                       
   //                        if($newseries=='')
   //                        {
   //                            $billseries  =  1;
   //                        }
   //                        else
   //                        {
   //                            $billseries   = $newseries['creditno_series']+1;
                              
   //                        }
                         
                       
   //                    }
   //                    else
   //                    {
   //                      $newseries  =  customer_creditnote::select('creditno_series')
   //                                                  ->whereRaw("STR_TO_DATE(customer_creditnotes.creditnote_date,'%d-%m-%Y') <= '$todate'")
   //                                                  ->where('customer_creditnote_id','<',$customer_creditnote_id)
   //                                                  ->orderBy('customer_creditnote_id','DESC')
   //                                                  ->take('1')
   //                                                  ->first();
   //                            $billseries   = $newseries['creditno_series']+1;

                        
   //                    }

   //                    $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
   //                    $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');
                     
   //                    $co     =     strlen($billseries);  
            
   //                    if($co<=2)
   //                    $id1  = '00'.$billseries; 
   //                    elseif($co<=3)
   //                    $id1  = '0'.$billseries;      
   //                    elseif($co<=4)
   //                    $id1  = $billseries;
   //                    $dd   = date('my');
                      
   //                    $finalinvoiceno = $cstate_id[0]['credit_receipt_prefix'].$dd.''.$id1;
   //              } 

   //              customer_creditnote::where('customer_creditnote_id',$customer_creditnote_id)->update(array(
   //                  'creditnote_no' => $finalinvoiceno,
   //                  'creditno_series' => $billseries
   //               ));
   //  }



    $paymentanswers     =    array();
    

         foreach($data[2] AS $key=>$value2)
          {
            
             if($value2['id']==3)
              {
                $paymentanswers['bankname']    =   $data[1]['bankname'];
                $paymentanswers['chequeno']    =   $data[1]['chequeno'];
              }
              elseif($value2['id']==7)
              {
                $paymentanswers['bankname']    =     $data[1]['netbankname'];
                $paymentanswers['chequeno']    =     '';
              }
              elseif($value2['id']==6)
              {
                $paymentanswers['bankname']    =     $data[1]['duedate'];
                $paymentanswers['chequeno']    =     '';
              }
              elseif($value2['id']==8)
              {
               
                $paymentanswers['bankname']='';
                $paymentanswers['chequeno'] =  '';

              }
              else
              {
                $paymentanswers['bankname']='';
                $paymentanswers['chequeno'] =  '';
              }
           
                
                $paymentanswers['consign_bill_id']               =  $sales_bill_id;
                $paymentanswers['total_bill_amount']             =  $value2['value'];
                $paymentanswers['payment_method_id']             =  $value2['id'];
                $paymentanswers['created_by']                    =  Auth::User()->user_id;
                $paymentanswers['deleted_at'] =  NULL;
                $paymentanswers['deleted_by'] =  NULL;
                
            
           
       
           $paymentdetail = consign_payment_detail::updateOrCreate(
               ['consign_bill_id' => $sales_bill_id,'consign_payment_detail_id'=>$value2['sales_payment_id'],],
               $paymentanswers);


            
            }
       // /}

     DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }
  

        if($billproductdetail)
        {
         
         if($data[1]['sales_bill_id'] != '')
          {   

            if($cstate_id[0]['billprint_type']==1)
            {
                return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>route('printconsign_challan', ['id' => encrypt($sales_bill_id)]),"burl"=>"sales_bill"));
            }
            else
            {
                return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>route('thermalconsign_challan', ['id' => encrypt($sales_bill_id)]),"burl"=>"sales_bill"));
            }
              
          }
          else
          {
            if($cstate_id[0]['billprint_type']==1)
            {
              return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>route('printconsign_challan', ['id' => encrypt($sales_bill_id)]),"burl"=>"sales_bill"));
            }
            else
            {
              return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>route('thermalconsign_challan', ['id' => encrypt($sales_bill_id)]),"burl"=>"sales_bill"));
            }
          }

           
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
        

    }
    public function consignno_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {

            $json = [];
            $result = consign_bill::select('bill_no')
                ->where('bill_no', 'LIKE', "%$request->search_val%")
                ->where('company_id',Auth::user()->company_id)->get();

           
           

            if(!empty($result))
            {
           
                foreach($result as $billkey=>$billvalue){


                      $json[] = $billvalue['bill_no'];
                      
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

    public function view_consignment()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $maxsales_id   =  '';
        $minsales_id   =  '';
        $rmaxsales_id   =  '';
        $rminsales_id   =  '';
        $returnsales   =  array();
        $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';

        $date      =   date("Y-m-d");
      

        $squery = consign_bill::select("consign_bills.*",DB::raw("(SELECT SUM(consign_products_details.discount_amount + consign_products_details.overalldiscount_amount) FROM consign_products_details WHERE consign_products_details.consign_bill_id = consign_bills.consign_bill_id GROUP BY consign_products_details.consign_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(consign_products_details.mrp) FROM consign_products_details WHERE consign_products_details.consign_bill_id = consign_bills.consign_bill_id and product_type=2 GROUP BY consign_products_details.consign_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(consign_products_details.igst_amount) FROM consign_products_details WHERE consign_products_details.consign_bill_id = consign_bills.consign_bill_id and product_type=2 GROUP BY consign_products_details.consign_bill_id)  as chargesgst"))
            ->with('reference')
            ->with('consign_payment_detail')
            ->where('company_id',Auth::user()->company_id)
           ->with([
                      'consign_products_detail' => function($fquery) {
                          $fquery->select('consign_bill_id');
                          $fquery->withCount(['sales_product_detail as totalconsignqty' => function($fquery) {
                          $fquery->select(DB::raw('SUM(qty)'));                       
                      }
                  ]);
                          $fquery->withCount(['return_product_detail as totalconsignreturnqty' => function($fquery) {
                          $fquery->select(DB::raw('SUM(qty)'));                       
                      }
                  ]);
                  }])           
            ->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$date' and '$date'")
            ->where('deleted_at','=',NULL)
            ->where('is_active','=',1)
            ->orderBy('consign_bill_id', 'DESC');

            $scustom   =   collect();
            $sdata     =   $scustom->merge($squery->get());
            $sales     =   $squery->paginate(10);

            $rquery = return_bill::select("return_bills.*",DB::raw("(SELECT SUM(return_product_details.discount_amount + return_product_details.overalldiscount_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id GROUP BY return_product_details.return_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(return_product_details.mrp) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(return_product_details.igst_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as chargesgst"))
            ->whereNull('sales_bill_id')
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
                foreach($totsales['consign_payment_detail'] as $paymentvalue)
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
           

           
        return view('consignment::view_consignchallan',compact('sales','payment_methods','count','todaytaxable','todaycgst','todaysgst','todayigst','todaygrand','maxsales_id','minsales_id','company_state','tax_type','taxname','max_date','min_date','cash','card','cheque','wallet','unpaidamt','netbanking','creditnote','returnbill','returnsales'))->render();

        
    }
    function view_datewise_consigndata(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
         
            $returnsales   =  array();   
            $payment_methods =      payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
            $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';
            $data            =      $request->all();
           

            // $sort_by = $data['sortby'];
            // $sort_type = $data['sorttype'];
            $query = isset($data['query']) ? $data['query']  : '';
            // print_r($query);


          $squery = consign_bill::select("consign_bills.*",DB::raw("(SELECT SUM(consign_products_details.discount_amount + consign_products_details.overalldiscount_amount) FROM consign_products_details WHERE consign_products_details.consign_bill_id = consign_bills.consign_bill_id GROUP BY consign_products_details.consign_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(consign_products_details.mrp) FROM consign_products_details WHERE consign_products_details.consign_bill_id = consign_bills.consign_bill_id and product_type=2 GROUP BY consign_products_details.consign_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(consign_products_details.igst_amount) FROM consign_products_details WHERE consign_products_details.consign_bill_id = consign_bills.consign_bill_id and product_type=2 GROUP BY consign_products_details.consign_bill_id)  as chargesgst"))
            ->with('reference')
            ->with('consign_payment_detail')
            ->with([
                      'consign_products_detail' => function($fquery) {
                          $fquery->select('consign_bill_id');
                          $fquery->withCount(['sales_product_detail as totalconsignqty' => function($fquery) {
                          $fquery->select(DB::raw('SUM(qty)'));                       
                      }
                  ]);
                          $fquery->withCount(['return_product_detail as totalconsignreturnqty' => function($fquery) {
                          $fquery->select(DB::raw('SUM(qty)'));                       
                      }
                  ]);
                  }])
           
            ->where('company_id',Auth::user()->company_id)->where('deleted_at','=',NULL)->where('is_active','=',1);


           $rquery = return_bill::select("return_bills.*",DB::raw("(SELECT SUM(return_product_details.discount_amount + return_product_details.overalldiscount_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id GROUP BY return_product_details.return_bill_id)  as totaldiscount"),DB::raw("(SELECT SUM(return_product_details.mrp) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as totalcharges"),DB::raw("(SELECT SUM(return_product_details.igst_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id and product_type=2 GROUP BY return_product_details.return_bill_id)  as chargesgst"))
             ->whereNull('sales_bill_id')->with('reference')->with('return_bill_payment')->where('company_id',Auth::user()->company_id)->where('deleted_by','=',NULL);


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
                 ->where('company_id',Auth::user()->company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('customer_name', 'LIKE', "%$cus_name%")
                 ->orwhere('customer_mobile', 'LIKE', "%$cus_mobile%")
                 ->get();

                 $squery->whereIn('customer_id',$result);
                 
            }
            if(isset($query) && $query != '' && $query['reference_name'] != '')
            {
                $ref_name =  $query['reference_name'];
                 $rresult = reference::select('reference_id')
                 ->where('company_id',Auth::user()->company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('reference_name', 'LIKE', "%$ref_name%")
                 ->get();

                 $squery->whereIn('reference_id',$rresult);
                
            }
            if(isset($query) && $query != '' && $query['billno'] != '')
            {
                 $squery->where('bill_no', 'like', '%'.$query['billno'].'%');

                 $tbill_no  =  consign_bill::select('consign_bill_id')->where('bill_no', 'like', '%'.$query['billno'].'%')->where('company_id',Auth::user()->company_id)->get();
                
            }
            if(isset($query) && $query != '' && $query['from_date'] != '' && $query['to_date'] != '')
            {
                
                 $rstart           =      date("Y-m-d",strtotime($query['from_date']));
                 $rend             =      date("Y-m-d",strtotime($query['to_date']));
                 $squery->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                
            }
            if($query['from_date'] == '' && $query['to_date'] == '' && $query['reference_name'] == '' && $query['billno'] == '' && $query['customerid'] == '')
            {
                 $rstart           =      date("Y-m-d");
                 $rend             =      date("Y-m-d");
                 $squery->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                
            }
            
            // $squery->whereRaw("Date(sales_bills.created_at) between '$rstart' and '$rend'");
            // $rquery->whereRaw("Date(return_bills.created_at) between '$rstart' and '$rend'");

           
           


            $scustom   =   collect();
            $sdata     =   $scustom->merge($squery->get());

            $sales     =   $squery->orderBy('consign_bill_id', 'DESC')
                           ->paginate(10);

            $rcustom   =   collect();
            $rdata     =   $rcustom->merge($rquery->get());

            $returnbill  =  $rquery->orderBy('return_bill_id', 'DESC')->paginate(10); 

          // echo '<pre>';
          // print_r($sales);
          // exit;

           
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
                foreach($totsales['consign_payment_detail'] as $paymentvalue)
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
          
           
          
        
              return view('consignment::view_consignchallan_data',compact('sales','payment_methods','count','todaytaxable','todaycgst','todaysgst','todaygrand','company_state','todayigst','tax_type','taxname','max_date','min_date','cash','card','cheque','wallet','unpaidamt','netbanking','creditnote','returnbill','returnsales'))->render();
        }
            
                
    }

  public function view_consignment_popup(Request $request)
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

            $sales = consign_bill::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('consign_bill_id','=',$request->billno)
                ->select('*')
                ->with('consign_products_detail.product')
                ->with([
                      'consign_products_detail' => function($fquery) {
                          $fquery->withCount(['sales_product_detail as totalconsignqty' => function($fquery) {
                          $fquery->select(DB::raw('SUM(qty)'));                       
                      }
                  ]);
                  }])                
                ->with('consign_payment_detail.payment_method')
                ->with('customer')
                ->with('customer_address_detail')
                ->with('reference')
                ->with('company')
                ->with('state')
                ->with('user')
                ->with('return_bill')
                ->get();

               $maxsales_id   =  consign_bill::max('consign_bill_id');
               $minsales_id   =  consign_bill::min('consign_bill_id');

             
              
                 return view('consignment::view_consignchallan_popup',compact('sales','maxsales_id','minsales_id','tax_type','taxname'));

         }
        
    }
  public function previous_consignment(Request $request)
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

            $sales = consign_bill::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('consign_bill_id','<',$request->billno)
                ->select('*')
                ->with('consign_products_detail.product')
                ->with('consign_payment_detail.payment_method')
                ->with('customer')
                ->with('customer_address_detail')
                ->with('reference')
                ->with('company')
                ->with('state')            
                ->orderBy('consign_bill_id','DESC')
                ->take(1)
                ->get();

              $maxsales_id   =  consign_bill::max('consign_bill_id');
              $minsales_id   =  consign_bill::min('consign_bill_id');

          
              return view('consignment::view_consignchallan_popup',compact('sales','maxsales_id','minsales_id','tax_type','taxname'));

        }
        
    }
    public function next_consignment(Request $request)
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

            $sales = consign_bill::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('consign_bill_id','>',$request->billno)
                ->select('*')
                ->with('consign_products_detail.product')
                ->with('consign_payment_detail.payment_method')
                ->with('customer')
                ->with('customer_address_detail')
                ->with('reference')
                ->with('company')
                ->with('state')            
                ->orderBy('consign_bill_id','ASC')
                ->take(1)
                ->get();

               
                $maxsales_id   =  consign_bill::max('consign_bill_id');
                $minsales_id   =  consign_bill::min('consign_bill_id');

          
             return view('consignment::view_consignchallan_popup',compact('sales','maxsales_id','minsales_id','tax_type','taxname'));
            

        }
        
    }
  public function view_returnconsignment_popup(Request $request)
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
                ->with('consign_bill')
                ->whereNull('sales_bill_id')
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
               
               $rmaxsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->whereNull('sales_bill_id')->max('return_bill_id');
               $rminsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->whereNull('sales_bill_id')->min('return_bill_id');

              
                  return view('consignment::view_returnconsignchallan_popup',compact('returnsales','rmaxsales_id','rminsales_id','tax_type','taxname'));

         }
        
    }
  public function rprevious_consignment(Request $request)
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
                ->with('consign_bill')
                ->whereNull('sales_bill_id')
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
              $rmaxsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->whereNull('sales_bill_id')->max('return_bill_id');
              $rminsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->whereNull('sales_bill_id')->min('return_bill_id');

          
              return view('consignment::view_returnconsignchallan_popup',compact('returnsales','rmaxsales_id','rminsales_id','tax_type','taxname'));

        }
        
    }
    public function rnext_consignment(Request $request)
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
                ->with('consign_bill')
                ->whereNull('sales_bill_id')
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

               
                $rmaxsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->whereNull('sales_bill_id')->max('return_bill_id');
               $rminsales_id   =  return_bill::where('company_id',Auth::user()->company_id)->whereNull('sales_bill_id')->min('return_bill_id');

          
              return view('consignment::view_returnconsignchallan_popup',compact('returnsales','rmaxsales_id','rminsales_id','tax_type','taxname'));
            

        }
        
    }

  public function edit_consignbill(Request $request)
    {
        $bill_id = decrypt($request->bill_id);
       
        $bill_data = consign_bill::where([
            ['consign_bill_id','=',$bill_id],
            ['company_id',Auth::user()->company_id]])
            ->with('customer')
            ->with('reference')
            ->with('customer_address_detail')
            ->with([
                    'consign_products_detail.product.editprice_master' => function($fquery) {
                         $fquery->select('*',DB::raw("(SELECT cost_rate FROM inward_product_details WHERE inward_product_details.product_id = price_masters.product_id order by inward_product_detail_id LIMIT 1) as costprice"));
                    }
                    ])
            //->with('sales_product_detail.product.editprice_master')
            ->with([
            'consign_products_detail.batchprice_master' => function($fquery) {
                 $fquery->select('*',DB::raw("(SELECT cost_rate FROM inward_product_details WHERE inward_product_details.product_id = price_masters.product_id order by inward_product_detail_id LIMIT 1) as costprice"));
            }])
            //->with('sales_product_detail.batchprice_master')
            ->with('consign_products_detail.product','consign_products_detail.product.uqc')
            ->with('consign_payment_detail.payment_method')
            ->with('customer_creditnote')
            ->select('*')
            ->first();

            $product_features =  ProductFeatures::getproduct_feature('');
             foreach($bill_data['consign_products_detail'] as $ss=>$bval)
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
          
      //dd($bill_data);
        return json_encode(array("Success"=>"True","Data"=>$bill_data,"url"=>"consign_challan"));


    }
    public function makeconsignment_bill(Request $request)
    {
        $consign_id = decrypt($request->consign_id);
       
        $bill_data = consign_bill::where([
            ['consign_bill_id','=',$consign_id],
            ['company_id',Auth::user()->company_id]])
            ->with('customer')
            ->with('reference')
            ->with('customer_address_detail')
            ->with([
            'consign_products_detail.batchprice_master' => function($fquery) {
                 $fquery->select('*',DB::raw("(SELECT cost_rate FROM inward_product_details WHERE inward_product_details.product_id = price_masters.product_id order by inward_product_detail_id LIMIT 1) as costprice"));
            }])
            ->with('consign_products_detail.product','consign_products_detail.product.uqc','consign_products_detail.consign_bill')
            ->with('consign_payment_detail.payment_method')
            ->with('sales_bill.advance_payment')
            ->with('customer_creditnote')
            ->select('*')
            ->first();
            
            $product_features =  ProductFeatures::getproduct_feature('');
             foreach($bill_data['consign_products_detail'] as $ss=>$bval)
             {
                
                $bval['consignsoldqty']   =   sales_product_detail::where('company_id',Auth::user()->company_id)
                                                            ->where('consign_products_detail_id',$bval['consign_products_detail_id'])
                                                            ->sum('qty');

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
      
        return json_encode(array("Success"=>"True","Data"=>$bill_data,"url"=>"sales_bill"));


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\consign_bill  $consign_bill
     * @return \Illuminate\Http\Response
     */
    public function show(consign_bill $consign_bill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\consign_bill  $consign_bill
     * @return \Illuminate\Http\Response
     */
    public function edit(consign_bill $consign_bill)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\consign_bill  $consign_bill
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, consign_bill $consign_bill)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\consign_bill  $consign_bill
     * @return \Illuminate\Http\Response
     */
    public function destroy(consign_bill $consign_bill)
    {
        //
    }
}
