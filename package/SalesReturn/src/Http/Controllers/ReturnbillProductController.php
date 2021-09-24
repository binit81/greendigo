<?php

namespace Retailcore\SalesReturn\Http\Controllers;

use Retailcore\SalesReturn\Models\returnbill_product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\Products\Models\product\product;
use Retailcore\Sales\Models\sales\sales_product_detail;
use Retailcore\Products\Models\product\price_master;
use Retailcore\DamageProducts\Models\damageproducts\damage_product;
use Retailcore\DamageProducts\Models\damageproducts\damage_type;
use Retailcore\DamageProducts\Models\damageproducts\damage_product_detail;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Auth;
use DB;
use Log;
class ReturnbillProductController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $returnproducts = return_product_detail::where('company_id',Auth::user()->company_id)->where('stockstatus','=',0)->with('return_bill')
        ->withCount([
                    'returnbill_product as totalrdqty' => function($fquery) {
                        $fquery->select(DB::raw('SUM(restockqty+damageqty)'));
                        $fquery->where('company_id',Auth::user()->company_id);                        
                    }
                ])
        ->where('deleted_at',NULL)->paginate(10);

        return view('salesreturn::returned_products',compact('returnproducts'));
    }
    public function datewise_returnproduct_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        //echo Auth::user()->company_id;
        $data            =      $request->all();
        $returnproducts = return_product_detail::where('company_id',Auth::user()->company_id)->where('stockstatus','=',0)->with('return_bill')
        ->withCount([
                    'returnbill_product as totalrdqty' => function($fquery) {
                        $fquery->select(DB::raw('SUM(restockqty+damageqty)'));
                        $fquery->where('company_id',Auth::user()->company_id);                        
                    }
                ])
        ->where('deleted_at',NULL)->paginate(10);

        // echo '<pre>';
        // print_r($returnproducts);
        // exit;

        return view('salesreturn::returned_products_data',compact('returnproducts'));
    }
    public function viewreturn_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

         if($request->ajax())
         {
                 $returnproducts = return_product_detail::where('company_id',Auth::user()->company_id)->where('stockstatus','=',0)->with('return_bill')->where('deleted_at',NULL)->paginate(5);
               
           return view('salesreturn::returned_products_data',compact('returnproducts'));
           

        }
        
    }

    public function restock_products(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
         $data = $request->all();
         $userId = Auth::User()->user_id;
         $company_id = Auth::User()->company_id;
         $created_by = $userId; 

         $returndate      =   date("d-m-Y");

         $restockstatus  =   $data[0]['restockqty']!=0?1:0;
         $damagestatus   =   $data[0]['damageqty']!=0?1:0;

        

        $rinwardqtys        =    '';
        $rinwardids         =    '';

        $totalreturnqty     =    $data[0]['restockqty'] + $data[0]['damageqty'];

        if($data[0]['returnqty'] == $totalreturnqty)
        {
          
            return_product_detail::where('return_product_detail_id',$data[0]['return_product_detail_id'])->update(array(
                            'stockstatus' => 1
                        ));
        }


          if($data[0]['restockqty']>0)
          {

               $productqty    =  price_master::select('product_qty')
                        ->where('price_master_id',$data[0]['price_master_id'])
                        ->where('company_id',Auth::user()->company_id)
                        ->get();


                    $updateqty   =    $productqty[0]['product_qty'] +  $data[0]['restockqty'];

              price_master::where('price_master_id',$data[0]['price_master_id'])->update(array(
                          'product_qty' => $updateqty
              )); 

              $oldinwardids       =     explode(',',substr($data[0]['inwardids'],0,-1));
              $oldinwardqtys      =     explode(',',substr($data[0]['inwardqtys'],0,-1));
                      //print_r($oldinwardids);

                      $restqty            =    $data[0]['restockqty'];
                      $ccount             =    0;  
                       $icount            =    0;
                       $pcount            =    0;
                       $done              =    0;
                       $firstout          =    0;
                     

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

                                           inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$oldinwardids[$l])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty + '.$restqty)
                                              ));
                                      
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
                                       inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$oldinwardids[$l])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty + '.$oldinwardqtys[$l])
                                              ));
                                     
                                  }
                                  else
                                  {
                                    //echo 'ccc';
                                    //echo $restqty;
                                      $rinwardids    .=   $oldinwardids[$l].',';
                                      $rinwardqtys   .=   $restqty.',';
                                      $ccount         =   $restqty  - $oldinwardqtys[$l];
                                       inward_product_detail::where('company_id',Auth::user()->company_id)
                                          ->where('inward_product_detail_id',$oldinwardids[$l])
                                          ->update(array(
                                              'modified_by' => Auth::User()->user_id,
                                              'updated_at' => date('Y-m-d H:i:s'),
                                              'pending_return_qty' => DB::raw('pending_return_qty + '.$oldinwardqtys[$l])
                                              ));
                                      
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


          }

          $totalqty    =  $data[0]['restockqty'] + $data[0]['damageqty'];

          $returnbillproduct = returnbill_product::updateOrCreate(
            ['returnbill_product_id' => $data[0]['returnbill_product_id'], 'company_id'=>$company_id,],
            ['return_date'=>$returndate,
            'return_product_detail_id'=>$data[0]['return_product_detail_id'],
            'price_master_id'=>$data[0]['price_master_id'],
            'product_id'=>$data[0]['product_id'],
                'qty'=>$totalqty,
                'restockstatus' => $restockstatus,
                'damagestatus'  => $damagestatus,
                'restockqty' => $data[0]['restockqty'],
                'rinwardids' =>  $rinwardids,
                'rinwardqtys' => $rinwardqtys,
                'damageqty' => $data[0]['damageqty'],
                'returnstatus' => 1,
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );
        
        $returnbill_product_id   =   $returnbillproduct->returnbill_product_id;

         if($data[0]['damageqty']>0)
         {

              $oldinwardids       =     explode(',',substr($data[0]['inwardids'],0,-1));
              $oldinwardqtys      =     explode(',',substr($data[0]['inwardqtys'],0,-1));

              foreach($oldinwardids as $o=>$oval)
              {
                    $inwarddetailid   =  $oldinwardids[$o];
              }
             

             $sellingpricedata    =   price_master::select('offer_price','sell_price','inward_stock_id')->where('price_master_id','=',$data[0]['price_master_id'])->where('company_id',Auth::user()->company_id)->where('deleted_at','=',NULL)->get();

             $costpricedata            =   inward_product_detail::select('cost_rate','cost_igst_percent','cost_cgst_percent','cost_sgst_percent','cost_price','offer_price')->where('inward_product_detail_id','=',$inwarddetailid)->where('company_id',Auth::user()->company_id)->where('deleted_at','=',NULL)->get();

              
              $product_cost_igst_percent        =  $costpricedata[0]['cost_igst_percent'];
              

              $product_cost_cgst_percent        =   $costpricedata[0]['cost_cgst_percent'];
              $product_cost_sgst_percent        =   $costpricedata[0]['cost_sgst_percent'];

              $product_cost_rate                =   $costpricedata[0]['cost_rate'];
              
              $product_cost_cgst_amount         =   $product_cost_rate  * $product_cost_cgst_percent /100;  
              
              $product_cost_sgst_amount         =   $product_cost_rate  * $product_cost_sgst_percent /100;
              $product_cost_igst_amount         =   $product_cost_rate  * $product_cost_igst_percent /100;

              $product_total_cost_rate          =    $product_cost_rate * $data[0]['damageqty'];
              $product_total_gst_amount         =    ($product_cost_cgst_amount +$product_cost_sgst_amount + $product_cost_igst_amount) * $data[0]['damageqty'];
              $product_cost_cgst_amount_with_qty=    $product_cost_cgst_amount * $data[0]['damageqty'];
              $product_cost_sgst_amount_with_qty=    $product_cost_sgst_amount * $data[0]['damageqty'];
              $product_cost_igst_amount_with_qty=    $product_cost_igst_amount * $data[0]['damageqty'];
              $product_total_cost_price         =    ($product_cost_rate  +  $product_cost_cgst_amount +$product_cost_sgst_amount + $product_cost_igst_amount) * $data[0]['damageqty'];
              $product_mrp                      =    $sellingpricedata[0]['offer_price'];




             $productdata            =   product::select('product_system_barcode')->where('product_id','=',$data[0]['product_id'])->where('company_id',Auth::user()->company_id)->where('deleted_at','=',NULL)->get();


      

              $last_damage_id = damage_product::where('company_id',Auth::user()->company_id)->get()->max('damage_product_id');

                $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
                $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');


                if($last_damage_id == '')
                {
                    $last_damage_id = 1;
                }
                else
                {
                    $last_damage_id = $last_damage_id  + 1;
                }

                $db_damage_no          =       'DAM/'.$last_damage_id.'/'.$f1.'-'.$f2; 

                        $damage_product = damage_product::updateOrCreate(
                            ['damage_product_id' => '','company_id'=>$company_id,],
                            ['damage_no'=>$db_damage_no,
                            'damage_type_id'=>3,
                            'damage_total_qty'=>$data[0]['damageqty'],
                            'damage_total_cost_rate'=>$product_total_cost_rate,
                            'damage_total_gst'=>$product_total_gst_amount,
                            'damage_total_cost_price'=>$product_total_cost_price,
                            'created_by' =>$created_by]
                        );


                        $damage_product_id = $damage_product->damage_product_id;


                        $DamageProductData = damage_product_detail::updateOrCreate(
                            ['damage_product_id' => $damage_product_id,
                            'company_id'=>$company_id,'damage_product_detail_id'=>'',],
                            ['product_id'=> $data[0]['product_id'],
                            'inward_product_detail_id'=> $inwarddetailid,
                            'product_cost_rate'=> $product_cost_rate,
                            'product_cost_cgst_percent' => $product_cost_cgst_percent,
                            'product_cost_cgst_amount'=>$product_cost_cgst_amount,
                            'product_cost_sgst_percent'=> $product_cost_sgst_percent,
                            'product_cost_sgst_amount'=>$product_cost_sgst_amount,
                            'product_cost_igst_percent'=> $product_cost_igst_percent,
                            'product_cost_igst_amount'=>$product_cost_igst_amount,
                            'product_total_cost_rate'=>$product_total_cost_rate,
                            'product_total_gst_amount'=>$product_total_gst_amount,
                            'product_cost_cgst_amount_with_qty'=>$product_cost_cgst_amount_with_qty,
                            'product_cost_sgst_amount_with_qty'=>$product_cost_sgst_amount_with_qty,
                            'product_cost_igst_amount_with_qty'=>$product_cost_igst_amount_with_qty,
                            'product_total_cost_price'=>$product_total_cost_price,
                            'product_mrp'=>$product_mrp,
                            'product_damage_qty'=>$data[0]['damageqty'],
                            'product_notes'=>$data[0]['remarks'],
                            'created_by' =>$created_by]
                        );

                        returnbill_product::where('returnbill_product_id',$returnbill_product_id)->update(array(
                            'damage_product_detail_id' => $DamageProductData->damage_product_detail_id
                        ));

          
         }
         
         return json_encode(array("Success"=>"True","Message"=>"Products Restocked Successfully!"));



    }




}
