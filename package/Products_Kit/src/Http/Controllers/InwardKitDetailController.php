<?php

namespace Retailcore\Products_Kit\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Retailcore\Products_Kit\Models\inward_kit_detail;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Products_Kit\Models\combo_products_detail;
use Retailcore\Products\Models\product\product_image;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Auth;
use DB;
use Log;

class InwardKitDetailController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
       return view('products_kit::inward_productskit');
    }

    public function inwardproductskit(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $product_id = decrypt($request->product_id);

        $product_data = product::where([
            ['product_id','=',$product_id],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->first();

      //dd($bill_data);
        return json_encode(array("Success"=>"True","Data"=>$product_data,"url"=>"inward_productskit"));


    }

    public function view_kitinward()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
       $inward_stock = inward_stock::where('company_id',Auth::user()->company_id)
            ->where('inward_type','=',3)
            ->where('deleted_at','=',NULL)
            ->orderBy('inward_stock_id', 'DESC')
            ->with('kitinward_product_detail.product')
            ->paginate(10);


       return view('products_kit::view_kitinward',compact('inward_stock'))->render();
    }

    public function edit_kitinward(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $inward_stock_id = decrypt($request->inward_stock_id);

        $inward_data = inward_stock::where([
            ['inward_stock_id','=',$inward_stock_id],
            ['company_id',Auth::user()->company_id]])
            ->where('inward_type',3)
             ->with([
                    'kitinward_product_detail' => function($fquery)
                    {
                        $fquery->select('*');
                        $fquery->with(['product' => function($q){
                            $q->select('product_name','product_system_barcode','product_id');
                        }]);
                    }
                ])
            ->with('inward_kit_detail.kitproduct','inward_kit_detail.itemproduct.colour','inward_kit_detail.itemproduct.size','inward_kit_detail.itemproduct.uqc','inward_kit_detail.editprice_master')
            ->select('*')
            ->get();

      //  echo '<pre>';
      // print_r($inward_data);
      // exit;
        return json_encode(array("Success"=>"True","Data"=>$inward_data,"url"=>"inward_productskit"));


    }
   public function inwardkit_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {

            $json = [];
            $result = product::select('product_name','supplier_barcode','product_system_barcode','product_id')
                ->whereRaw("(product_name LIKE '%$request->search_val%' or supplier_barcode LIKE '%$request->search_val%')")
                ->where('item_type','=',3)   
                ->where('company_id',Auth::user()->company_id)               
                ->take(10)->get();

             if(sizeof($result) != 0) 
            {
                foreach($result as $productkey=>$productvalue){
                    $json[$productkey]['label'] = $productvalue['supplier_barcode'].'_'.$productvalue['product_name'];
                      $json[$productkey]['barcode'] = $productvalue['supplier_barcode'];
                      $json[$productkey]['product_name'] = $productvalue['product_name'];
                      $json[$productkey]['systembarcode'] = $productvalue['product_system_barcode'];
                      
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
    public function productkit_detail(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
            $prod_barcode   =  $request->barcode;
            $prod_name      =  $request->product_name;

            $query = product::select('*')
                    ->with('combo_products_detail','combo_products_detail.product','combo_products_detail.product.size','combo_products_detail.product.colour','combo_products_detail.product.uqc','combo_products_detail.price_master')
                    ->where('product_name','LIKE',"%$request->product_name%")
                    ->orWhere('supplier_barcode', 'LIKE', "%$request->barcode%");


              $result  =  $query->where('company_id',Auth::user()->company_id)->get();

             
               if(sizeof($result) != 0)
               {
                   
                  return json_encode(array("Success"=>"True","Data"=>$result));
               }
               else
               {
                   return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));
               }
                

    }
    public function createcombo_inward(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;


        $created_by = $userId;


        $cstate_id = company_profile::select('state_id')
                ->where('company_id',Auth::user()->company_id)->get(); 

        $product_data   =   product::where('company_id',Auth::user()->company_id)
                            ->where('product_id',$data[1]['product_id'])
                            ->get();

         $sell_gst_percent   =   $data[1]['sell_gst_percent'];  
         $selling_price      =   $data[1]['selling_price'];  
         $offer_price        =   $data[1]['offer_price'];

        if($data[1]['update_offer_price'] == 0)
        {
          if (isset($data[1]['batch_no']) && $data[1]['batch_no'] != '')
          {
              $same_batch_no = price_master::select('price_master_id', 'offer_price')
                  ->where('company_id', Auth::user()->company_id)
                  ->where('batch_no', '=', $data[1]['batch_no'])
                  ->where('product_id', '=', $data[1]['product_id'])
                  ->where('deleted_at', '=', NULL)
                  ->where('offer_price', '!=', $data[1]['offer_price'])
                  ->get()->first();
              if (isset($same_batch_no) && $same_batch_no['price_master_id'] != '')
              {
                  $barcode = product::select('supplier_barcode','product_system_barcode')->where('product_id',$data[1]['product_id'])->where('company_id',Auth::user()->company_id)->first();
                  $show_barcode = '';
                  if(isset($barcode))
                  {
                      if($barcode->supplier_barcode != '' && $barcode->supplier_barcode != NULL)
                      {
                          $show_barcode = $barcode->supplier_barcode;
                      }
                      else
                      {
                          $show_barcode = $barcode->product_system_barcode;
                      }
                  }

                  return json_encode(array("Success" => "False", "status_code" => 410, "Message" => "Sorry,Barcode '" .$show_barcode. "',Batch No. '" . $data[1]['batch_no'] . "' already present with Rs.'" . $same_batch_no['offer_price'] . "' offer price.Are you sure want to change offer price! "));
                  exit;
              }
          }
        }


         $comboid            =   inward_stock::where('company_id',Auth::user()->company_id)->get()->max('inward_stock_id');
         $invoiceno          =   'COMBO-'.$comboid;


       try {
        DB::beginTransaction();    


        $inward_stock = inward_stock::updateOrCreate(
            ['inward_stock_id' => $data[1]['inward_stock_id'], 'company_id'=>$company_id,],
            ['supplier_gst_id'=>$company_id,
            'state_id'=>$cstate_id[0]['state_id'],
            'inward_type'=>3,
                'invoice_no'=>$invoiceno,
                'invoice_date'=>$data[1]['inward_date'],
                'inward_date'=>$data[1]['inward_date'],
                'total_qty'=>$data[1]['inward_qty'],
                'total_gross'=>0,
                'total_grand_amount'=>0,                
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );

        $inward_stock_id  = $inward_stock->inward_stock_id;

         $productdetail     =    array();

         $averagecost  =  0;
         $kitcostprice =  0;
        foreach($data[0] AS $billkey=>$billvalue)
          {
              $inwardids    =  '';
              $inwardqtys   =  '';
               if($billvalue['kitproductid']!='')
              {

                      $productdetail['inward_stock_id']                      =    $inward_stock_id; 
                      $productdetail['product_id']                           =    $billvalue['itemproductid'];
                      $productdetail['price_master_id']                      =    $billvalue['price_master_id'];
                      $productdetail['kitproduct_id']                        =    $billvalue['kitproductid'];
                      $productdetail['qty']                                  =    $billvalue['totalqty'];                      
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
                  'product_qty' => DB::raw('product_qty - '.$billvalue['totalqty'])
                  ));

          
              

///////////////////First In First Out Logic//////////////////////////////////////////////////////////////

                    $oldinwardids       =     explode(',',substr($billvalue['inwardids'],0,-1));
                    $oldinwardqtys      =     explode(',',substr($billvalue['inwardqtys'],0,-1));

                
                       $ccount    =   0;  
                       $icount    =   0;
                       $pcount    =   0;
                       $done      =   0;
                       $firstout  =   0;
                       $inwardcostprice = 0;
                       $restqty   =   $billvalue['totalqty'];


                         

              if($billvalue['totalqty']!=$billvalue['oldqty'])  
               {    

                   if($billvalue['inward_kit_detail_id'] !='')
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
                       
                  if($billvalue['totalqty']>0)
                  {
                      

                       $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty')
                                            ->where('product_id',$billvalue['itemproductid'])
                                            ->where('company_id',Auth::user()->company_id)
                                            ->where('pending_return_qty','>',0);

                     
                      
                      $inwarddetail  =  $qquery->where('deleted_at','=',NULL)->orderBy('inward_product_detail_id','ASC')->get();

                 
                                  
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
                                              'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['totalqty'])
                                              ));

                                          // $costprice  =  inward_product_detail::select('cost_rate')
                                          //               ->where('company_id',Auth::user()->company_id)
                                          //               ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                          //               ->first();
                                          // $inwardcostprice  +=  $costprice['cost_rate'] * $billvalue['totalqty'];


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

                                       // $costprice  =  inward_product_detail::select('cost_rate')
                                       //                  ->where('company_id',Auth::user()->company_id)
                                       //                  ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                       //                  ->first();
                                       //    $inwardcostprice  +=  $costprice['cost_rate'] * $inwarddata['pending_return_qty'];

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

                                      // $costprice  =  inward_product_detail::select('cost_rate')
                                      //                   ->where('company_id',Auth::user()->company_id)
                                      //                   ->where('inward_product_detail_id',$inwarddata['inward_product_detail_id'])
                                      //                   ->first();
                                      //     $inwardcostprice  +=  $costprice['cost_rate'] * $restqty;
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
                   $inwardids                                          =    $billvalue['inwardids'];
                   $inwardqtys                                         =    $billvalue['inwardqtys'];
                  $productdetail['inwardids']                          =    $billvalue['inwardids'];
                  $productdetail['inwardqtys']                         =    $billvalue['inwardqtys'];
                }

                
                
                $total_price  = 0;
                 if($inwardids !='' || $inwardids !=null)
                {

                   $cinwardids  = explode(',' ,substr($inwardids,0,-1));
                   $cinwardqtys = explode(',' ,substr($inwardqtys,0,-1));
                   

                    foreach($cinwardids as $inidkey=>$inids)
                    {
                          $cost_price =  inward_product_detail::select('cost_rate')->find($inids);
                           
                          $total_price += $cost_price['cost_rate'] * $cinwardqtys[$inidkey];            
                    } 
                    $averagecost      +=   $total_price;
                    
                }
                else
                {
                    $averagecost      =   0;
                   
                }
                

              

                $billproductdetail = inward_kit_detail::updateOrCreate(
                   ['inward_kit_detail_id' => $billvalue['inward_kit_detail_id'],
                    'company_id'=>$company_id],
                   $productdetail);

              
      }
     
     
            
     }
   $kitcostprice        =  $averagecost / $data[1]['inward_qty'];
   $totalaveragecost    =  $averagecost / $data[1]['inward_qty'];
   $total_cost_rate_with_qty = $totalaveragecost * $data[1]['inward_qty'];
   $total_cost = $totalaveragecost * $data[1]['inward_qty'];

  
   $profitamt    = $data[1]['selling_price'] - $kitcostprice;
       
   $profitpercent = (100 * $profitamt) / $kitcostprice;



  
       $inward_stock_insert  = inward_stock::where('inward_stock_id',$inward_stock_id)
            ->where('company_id', Auth::user()->company_id)
            ->update(array(
                'cost_rate' => $totalaveragecost,
                'total_gross' => $totalaveragecost,
                'total_grand_amount' => $totalaveragecost
            ));

          if($data[1]['inward_product_detail_id']!='')
          {
              $kitpending_return_qty    =  $data[1]['pending_qty'];
          }
          else
          {
              $kitpending_return_qty    =  $data[1]['inward_qty'];
          }

        $inward_product_detail_array = array();
        $price_master_array = array();
/////If inward type would be garment.
      if($data[1]['inward_type']==2)
      {
         
              $price_master_id  =  price_master::where('product_id',$data[1]['product_id'])
                                                ->where('company_id', Auth::user()->company_id)
                                                ->first();

          if($data[1]['inward_product_detail_id']!='')
          {
                                                
                         price_master::where('price_master_id',$price_master_id['price_master_id'])->update(array(
                          'modified_by' => Auth::User()->user_id,
                          'updated_at' => date('Y-m-d H:i:s'),
                          'product_qty' => DB::raw('product_qty - '.$data[1]['oldinward_qty'])
                          ));
                                       
                            $price_master = price_master::updateOrCreate(
                            ['price_master_id' => $price_master_id['price_master_id'],'company_id'=>$company_id,],
                            ['product_id'=>$data[1]['product_id'],
                              'sell_price'=>$data[1]['selling_price'],
                              'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                              'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                              'offer_price'=>$data[1]['offer_price'], 
                              'product_mrp'=>$data[1]['product_mrp'], 
                              'wholesaler_price'=>$data[1]['product_mrp'], 
                              'product_qty'=>DB::raw('product_qty + '.$data[1]['inward_qty']),
                              'created_by' =>$created_by,
                              'is_active' => "1"
                            ]
                        );
             $inward_product_detail = inward_product_detail::updateOrCreate(
            ['inward_product_detail_id' => $data[1]['inward_product_detail_id'],'inward_stock_id'=>$inward_stock_id,'company_id'=>$company_id,],
            ['supplier_gst_id'=>$company_id,
            'product_id'=>$data[1]['product_id'],
                'base_price'=>$totalaveragecost,
                'cost_rate'=>$totalaveragecost,
                'cost_price'=>$totalaveragecost,
                'profit_percent'=>$profitpercent,
                'profit_amount'=>$profitamt,
                'sell_price'=>$data[1]['selling_price'],
                'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                'offer_price'=>$data[1]['offer_price'], 
                'product_mrp'=>$data[1]['product_mrp'],  
                'product_qty'=>$data[1]['inward_qty'], 
                'free_qty'=>0,  
                'pending_return_qty'=>$kitpending_return_qty,  
                'total_cost_rate_with_qty'=>$total_cost_rate_with_qty,
                'total_igst_amount_with_qty'=>0,
                'total_cgst_amount_with_qty'=>0,
                'total_sgst_amount_with_qty'=>0,
                'total_cost'=>$total_cost,  
                'expiry_date' => $data[1]['expiry_date'],
                'mfg_date' => $data[1]['mfg_date'],        
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );      

        }
        else
        {
            $priceid    =  '';
            $kitinwardqty   = $data[1]['inward_qty'];
            if($price_master_id['price_master_id']!='')
            {
                $priceid        = $price_master_id['price_master_id'];
                $kitinwardqty   = $data[1]['inward_qty'] + $price_master_id['product_qty'];
            }
           

            $price_master = price_master::updateOrCreate(
                  ['price_master_id' => $priceid,'company_id'=>$company_id,],
                  ['product_id'=>$data[1]['product_id'],
                    'sell_price'=>$data[1]['selling_price'],
                    'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                    'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                    'offer_price'=>$data[1]['offer_price'], 
                    'product_mrp'=>$data[1]['product_mrp'], 
                    'wholesaler_price'=>$data[1]['product_mrp'], 
                    'product_qty'=>$kitinwardqty,
                    'created_by' =>$created_by,
                    'is_active' => "1"
                  ]
              );

            $inward_product_detail = inward_product_detail::updateOrCreate(
            ['inward_product_detail_id' => $data[1]['inward_product_detail_id'],'inward_stock_id'=>$inward_stock_id,'company_id'=>$company_id,],
            ['supplier_gst_id'=>$company_id,
            'product_id'=>$data[1]['product_id'],
                'base_price'=>$totalaveragecost,
                'cost_rate'=>$totalaveragecost,
                'cost_price'=>$totalaveragecost,
                'profit_percent'=>$profitpercent,
                'profit_amount'=>$profitamt,
                'sell_price'=>$data[1]['selling_price'],
                'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                'offer_price'=>$data[1]['offer_price'], 
                'product_mrp'=>$data[1]['product_mrp'],  
                'product_qty'=>$data[1]['inward_qty'], 
                'free_qty'=>0,  
                'pending_return_qty'=>$kitpending_return_qty,  
                'total_cost_rate_with_qty'=>$total_cost_rate_with_qty,
                'total_igst_amount_with_qty'=>0,
                'total_cgst_amount_with_qty'=>0,
                'total_sgst_amount_with_qty'=>0,
                'total_cost'=>$total_cost,  
                'expiry_date' => $data[1]['expiry_date'],
                'mfg_date' => $data[1]['mfg_date'],        
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );
          }
      }  
///if inward type is FMCG
    else
    {
//if the entry is being modified
            if($data[1]['inward_product_detail_id']!='')
            {


               $qtyp = inward_product_detail::select('product_qty','free_qty','inward_product_detail_id','product_mrp','sell_price','selling_gst_percent','selling_gst_amount','offer_price','batch_no')
                        ->where('product_id', '=', $data[1]['product_id'])
                        ->where('inward_stock_id', '=',$data[1]['inward_stock_id'])
                        ->where('inward_product_detail_id','=',$data[1]['inward_product_detail_id'])
                        ->where('company_id', Auth::user()->company_id)->get();

                if(isset($qtyp) && $qtyp != '' && !empty($qtyp) && isset($qtyp[0]))
                {
                    $product_qty_minus  = (($qtyp[0]['product_qty'] + $qtyp[0]['free_qty'])) ;
                }
                else
                {
                    $product_qty_minus = 0;
                }

                $qtys = price_master::select('product_qty','batch_no', 'price_master_id', 'product_mrp','offer_price','inward_stock_id')
                    ->where('batch_no', '=', $qtyp[0]['batch_no'])
                    ->where('offer_price', '=', $qtyp[0]['offer_price'])
                    ->where('product_id', '=', $data[1]['product_id'])
                    ->where('company_id', Auth::user()->company_id)->get();

                if (isset($qtys) && $qtys != '' && isset($qtys[0]['offer_price']))
                {
                    $qty_with_minus = (($qtys[0]['product_qty']) - ($product_qty_minus));

                   
                    $price_master_minus_qty['product_qty'] = $qty_with_minus;
                    $price_master_minus_qty['product_mrp'] = $qtyp[0]['product_mrp'];
                    $price_master_minus_qty['offer_price'] = $qtyp[0]['offer_price'];
                    $price_master_minus_qty['sell_price'] = $qtyp[0]['sell_price'];
                    $price_master_minus_qty['selling_gst_percent'] = $qtyp[0]['selling_gst_percent'];
                    $price_master_minus_qty['selling_gst_amount'] = $qtyp[0]['selling_gst_amount'];
                    $price_master_minus_qty['price_master_id'] = $qtys[0]['price_master_id'];
                    $price_master_minus_qty['batch_no'] = $qtyp[0]['batch_no'];
                    $price_master_minus_qty['deleted_at'] = NULL;
                    $price_master_minus_qty['deleted_by'] = NULL;
                    $price_master_minus_qty['inward_stock_id'] = $qtys[0]['inward_stock_id'];
                    $price_master_id = '';

                    $price_master_array['inward_stock_id'] = $qtys[0]['inward_stock_id'];

                    //minus qty from existing row
                 $price_master = price_master::updateOrCreate(
                        ['inward_stock_id' => $qtys[0]['inward_stock_id'],
                            'product_id' => $data[1]['product_id'],
                            'offer_price' => $qtyp[0]['offer_price'],
                            'batch_no' => $qtyp[0]['batch_no'],
                            'company_id' => $company_id,
                            'price_master_id' => $qtys[0]['price_master_id'],],
                        $price_master_minus_qty);

                  if($data[1]['batch_no'] == '' )
                 {
                        if ($qtys[0]['offer_price'] == $data[1]['offer_price'])
                        {

                            $price_master_id = $qtys[0]['price_master_id'];
                            $total_qty = (($qtys[0]['product_qty'] - $product_qty_minus) + ($data[1]['inward_qty']));
                            $price_master_array['product_qty'] = $total_qty;
                        } else {

                            $qty_price = price_master::select('product_qty', 'price_master_id', 'offer_price')
                                //->where('batch_no', '=', $price_master_array['batch_no'])
                                ->where('offer_price', '=', $data[1]['offer_price'])
                                ->where('product_id', '=', $data[1]['product_id'])
                                ->where('company_id', Auth::user()->company_id)->get();

                            if (isset($qty_price) && $qty_price != '' && isset($qty_price[0]['offer_price']))
                            {
                                $price_master_id = $qty_price[0]['price_master_id'];
                                $total_qty = ($data[1]['inward_qty'] + $qty_price[0]['product_qty']);
                            } else {
                                $price_master_id = $qtys[0]['price_master_id'];
                                $total_qty = ($data[1]['inward_qty']);
                            }
                            $price_master_array['product_qty'] = $total_qty;
                        }
                    }else {

                        /*  if ($qtyp[0]['batch_no'] != $price_master_array['batch_no'])
                          {*/

                        if($data[1]['update_offer_price'] == 0) {

                            $qtty = price_master::select('product_qty', 'batch_no', 'price_master_id', 'product_mrp', 'offer_price')
                                ->where('batch_no', '=', $data[1]['batch_no'])
                                ->where('offer_price', '=', $qtyp[0]['offer_price'])
                                ->where('product_id', '=', $data[1]['product_id'])
                                ->where('company_id', Auth::user()->company_id)->get();
                        }else
                        {

                            $qtty = price_master::select('product_qty', 'batch_no', 'price_master_id', 'product_mrp', 'offer_price')
                                ->where('batch_no', '=', $data[1]['batch_no'])
                               // ->where('offer_price', '=', $qtyp[0]['offer_price'])
                                ->where('product_id', '=', $data[1]['product_id'])
                                ->where('company_id', Auth::user()->company_id)->get();
                        }
                        if (isset($qtty) && $qtty != '' && isset($qtty[0]['offer_price']))
                        {
                            $price_master_id = $qtty[0]['price_master_id'];
                            $total_qty = ($qtty[0]['product_qty'] + $data[1]['inward_qty']);

                        } else {
                            $price_master_id = '';
                            $total_qty = $data[1]['inward_qty'];
                        }

                        $price_master_array['product_qty'] = $total_qty;
                    }

                    if($data[1]['update_offer_price'] == 0) {
                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $data[1]['product_id'],
                                'offer_price' => $data[1]['offer_price'],
                                'batch_no' => $data[1]['batch_no'],
                                'company_id' => $company_id,
                                'price_master_id' => $price_master_id,
                            ],
                            ['inward_stock_id'=>$inward_stock_id,
                              'sell_price'=>$data[1]['selling_price'],
                              'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                              'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                              'offer_price'=>$data[1]['offer_price'], 
                              'product_mrp'=>$data[1]['product_mrp'], 
                              'wholesaler_price'=>$data[1]['product_mrp'], 
                              'product_qty'=>$total_qty,
                              'created_by' =>$created_by,
                              'is_active' => "1"
                            ]);
                    }else
                    {
                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $data[1]['product_id'],
                               // 'offer_price' => $inward_product_detail_value['offer_price'],
                                'batch_no' => $data[1]['batch_no'],
                                'company_id' => $company_id,
                                'price_master_id' => $price_master_id,
                            ],
                             ['inward_stock_id'=>$inward_stock_id,
                              'sell_price'=>$data[1]['selling_price'],
                              'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                              'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                              'offer_price'=>$data[1]['offer_price'], 
                              'product_mrp'=>$data[1]['product_mrp'], 
                              'wholesaler_price'=>$data[1]['product_mrp'], 
                              'product_qty'=>$total_qty,
                              'created_by' =>$created_by,
                              'is_active' => "1"
                            ]);
                    }
                }
                else
                {
                    $price_master = price_master::updateOrCreate(
                        [
                            'product_id' => $data[1]['product_id'],
                            'offer_price' => $data[1]['offer_price'],
                            'batch_no' => $data[1]['offer_price'],
                            'company_id'=>$company_id,
                        ],
                        ['inward_stock_id'=>$inward_stock_id,
                            'sell_price'=>$data[1]['selling_price'],
                            'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                            'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                            'offer_price'=>$data[1]['offer_price'], 
                            'product_mrp'=>$data[1]['product_mrp'], 
                            'wholesaler_price'=>$data[1]['product_mrp'], 
                            'product_qty'=>$total_qty,
                            'created_by' =>$created_by,
                            'is_active' => "1"
                          ]);
                }

                $pending_return_qty = (isset($data[1]['pending_qty'])) ? $data[1]['pending_qty'] : ($data[1]['inward_qty']);

                //if product have two offer price and user confirm to change offer price that case update that product offer price and related field to inward product detail.
                if($data[1]['update_offer_price'] == 1){
                    inward_product_detail::where('company_id',Auth::user()->company_id)
                        ->where('product_id',$data[1]['product_id'])
                        ->update([
                            'offer_price' => $data[1]['offer_price'],
                            'product_mrp' => $data[1]['product_mrp'],
                            'sell_price' => $data[1]['selling_price'],
                            'selling_gst_percent' => $data[1]['sell_gst_percent'],
                            'selling_gst_amount' => $data[1]['sell_gst_amount'],
                            'profit_percent' => $profitpercent,
                            'profit_amount' => $profitamt,
                            'expiry_date' => $data[1]['expiry_date'],
                            'mfg_date' => $data[1]['mfg_date'],
                        ]);
                }
                //end of update this product offer price in all inward

                $inward_product_detail_insert = inward_product_detail::updateOrCreate(
                    ['inward_stock_id' => $data[1]['inward_stock_id'],
                        'company_id' => $company_id,
                        'product_id' => $data[1]['product_id'],
                        'inward_product_detail_id' => $data[1]['inward_product_detail_id'],
                    ],
                            ['offer_price' => $data[1]['offer_price'],
                            'batch_no' => $data[1]['batch_no'],
                            'product_mrp' => $data[1]['product_mrp'],
                            'sell_price' => $data[1]['selling_price'],
                            'selling_gst_percent' => $data[1]['sell_gst_percent'],
                            'selling_gst_amount' => $data[1]['sell_gst_amount'],
                            'profit_percent' => $profitpercent,
                            'profit_amount' => $profitamt,
                            'product_qty'=>$data[1]['inward_qty'],
                            'pending_return_qty'=>$kitpending_return_qty,
                            'expiry_date' => $data[1]['expiry_date'],
                            'mfg_date' => $data[1]['mfg_date']
                    ]);

            }
            else
            {
                //if product have two offer price and user confirm to change offer price that case update that product offer price and related field to inward product detail.
                if($data[1]['update_offer_price'] == 1){
                    inward_product_detail::where('company_id',Auth::user()->company_id)
                        ->where('product_id',$data[1]['product_id'])
                        ->update([
                            'offer_price' => $data[1]['offer_price'],
                            'product_mrp' => $data[1]['product_mrp'],
                            'sell_price' => $data[1]['selling_price'],
                            'selling_gst_percent' => $data[1]['sell_gst_percent'],
                            'selling_gst_amount' => $data[1]['sell_gst_amount'],
                            'profit_percent' => $profitpercent,
                            'profit_amount' => $profitamt,
                            'expiry_date' => $data[1]['expiry_date'],
                            'mfg_date' => $data[1]['mfg_date'],
                        ]);
                }
                //end of update offer price in inward product detail

                $pending_return_qty = $data[1]['inward_qty'];
                     $inward_product_detail = inward_product_detail::updateOrCreate(
                    ['inward_product_detail_id' => $data[1]['inward_product_detail_id'],'inward_stock_id'=>$inward_stock_id,'company_id'=>$company_id,],
                    ['supplier_gst_id'=>$company_id,
                    'product_id'=>$data[1]['product_id'],
                        'batch_no'=>$data[1]['batch_no'],
                        'base_price'=>$totalaveragecost,
                        'cost_rate'=>$totalaveragecost,
                        'cost_price'=>$totalaveragecost,
                        'profit_percent'=>$profitpercent,
                        'profit_amount'=>$profitamt,
                        'sell_price'=>$data[1]['selling_price'],
                        'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                        'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                        'offer_price'=>$data[1]['offer_price'], 
                        'product_mrp'=>$data[1]['product_mrp'],  
                        'product_qty'=>$data[1]['inward_qty'], 
                        'free_qty'=>0,  
                        'pending_return_qty'=>$kitpending_return_qty,  
                        'total_cost_rate_with_qty'=>$total_cost_rate_with_qty,
                        'total_igst_amount_with_qty'=>0,
                        'total_cgst_amount_with_qty'=>0,
                        'total_sgst_amount_with_qty'=>0,
                        'total_cost'=>$total_cost,  
                        'expiry_date' => $data[1]['expiry_date'],
                        'mfg_date' => $data[1]['mfg_date'],        
                        'created_by' =>$created_by,
                        'is_active' => "1"
                    ]
                );

                if($data[1]['update_offer_price'] == 0)
                {
                    $qtys = price_master::select('product_qty', 'price_master_id', 'product_mrp','offer_price')
                        ->where('batch_no', '=', $data[1]['batch_no'])
                        ->where('offer_price', '=', $data[1]['offer_price'])
                        ->where('product_id', '=', $data[1]['product_id'])
                        ->where('company_id', Auth::user()->company_id)->get();
                }
                else
                {
                    $qtys = price_master::select('product_qty', 'price_master_id', 'product_mrp','offer_price')
                        ->where('batch_no', '=', $data[1]['batch_no'])
                      //  ->where('offer_price', '=', $inward_product_detail_array['offer_price'])
                        ->where('product_id', '=', $data[1]['product_id'])
                        ->where('company_id', Auth::user()->company_id)->get();
                }

               

                if (isset($qtys) && $qtys != '' && isset($qtys[0]['offer_price']))
                {

                    //INCREMENT QUNTITY
                    $total_qty =  ($qtys[0]['product_qty'] + $data[1]['inward_qty']);
                    $price_master_array['product_qty'] = $total_qty;

                    if($data[1]['update_offer_price'] == 0)
                    {
                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $data[1]['product_id'],
                                'offer_price' => $data[1]['offer_price'],
                                'company_id' => $company_id,
                                'price_master_id' => $qtys[0]['price_master_id'],

                            ],
                           ['inward_stock_id'=>$inward_stock_id,
                            'sell_price'=>$data[1]['selling_price'],
                            'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                            'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                            'offer_price'=>$data[1]['offer_price'], 
                            'product_mrp'=>$data[1]['product_mrp'], 
                            'wholesaler_price'=>$data[1]['product_mrp'], 
                            'product_qty'=>$total_qty,
                            'created_by' =>$created_by,
                            'is_active' => "1"
                          ]);
                    }else
                    {
                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $data[1]['product_id'],
                                //'offer_price' => $inward_product_detail_value['offer_price'],
                                'company_id' => $company_id,
                                'price_master_id' => $qtys[0]['price_master_id'],
                            ],
                            ['inward_stock_id'=>$inward_stock_id,
                            'sell_price'=>$data[1]['selling_price'],
                            'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                            'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                            'offer_price'=>$data[1]['offer_price'], 
                            'product_mrp'=>$data[1]['product_mrp'], 
                            'wholesaler_price'=>$data[1]['product_mrp'], 
                            'product_qty'=>$total_qty,
                            'created_by' =>$created_by,
                            'is_active' => "1"
                          ]
                         );
                    }
                }
                else
                {

                    $price_master = price_master::updateOrCreate(
                        [
                            'product_id' => $data[1]['product_id'],
                            'offer_price' => $data[1]['offer_price'],
                            'batch_no' => $data[1]['batch_no'],
                            'company_id'=>$company_id,
                        ],
                         ['inward_stock_id'=>$inward_stock_id,
                        'sell_price'=>$data[1]['selling_price'],
                        'selling_gst_percent'=>$data[1]['sell_gst_percent'],
                        'selling_gst_amount'=>$data[1]['sell_gst_amount'],   
                        'offer_price'=>$data[1]['offer_price'], 
                        'product_mrp'=>$data[1]['product_mrp'], 
                        'wholesaler_price'=>$data[1]['product_mrp'], 
                        'product_qty'=>$data[1]['inward_qty'],
                        'created_by' =>$created_by,
                        'is_active' => "1"
                      ]
                        );
                }
            }
         
        
  }
  

    

    

         //print_r($inward_stock_id);


       DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }


        if($inward_stock_insert)
        {
            if($data[1]['inward_stock_id'] != '')
            {
                return json_encode(array("Success"=>"True","Message"=>"Stock successfully Update!","url"=>"view_kitinward"));
            }
            else
            {
                
                return json_encode(array("Success"=>"True","Message"=>"Stock successfully inward!","url"=>"view_kitinward"));
            }

        }
    }




}
