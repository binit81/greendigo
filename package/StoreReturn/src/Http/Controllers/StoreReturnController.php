<?php

namespace Retailcore\StoreReturn\Http\Controllers;

use Retailcore\StoreReturn\Models\storereturn_product;
use Retailcore\StoreReturn\Models\store_return;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\returnbill_product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Products\Models\product\product;
use Retailcore\Sales\Models\payment_method;
use App\state;
use App\country;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Auth;
use Retailcore\Products\Models\product\ProductFeatures;
use DB;
use Log;

class StoreReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        

        
        $maxsales_id = '';
        $minsales_id = '';
        $date  =  date("Y-m-d");
        $store_return = store_return::where('company_id',Auth::user()->company_id)
                        ->whereRaw("STR_TO_DATE(store_returns.return_date,'%d-%m-%Y') between '$date' and '$date'")
                        ->withCount([
                                'storereturn_product as totalreturnqty' => function($fquery) {
                                    $fquery->select(DB::raw('count(storereturn_product_id)'));
                                    $fquery->where('company_id',Auth::user()->company_id);
                                    
                                }
                            ])
                        ->orderBy('store_return_id','DESC')
                        ->paginate(10);






        return view('storereturn::view_storereturn',compact('store_return','maxsales_id','minsales_id'));
    }

    public function datewise_storereturnproduct_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        
        $data            =      $request->all();
        $query = isset($data['query']) ? $data['query']  : '';
        
        $maxsales_id = '';
        $minsales_id = '';
        $date  =  date("Y-m-d");
        $squery = store_return::where('company_id',Auth::user()->company_id)
                        ->withCount([
                                'storereturn_product as totalreturnqty' => function($fquery) {
                                    $fquery->select(DB::raw('count(storereturn_product_id)'));
                                    $fquery->where('company_id',Auth::user()->company_id);
                                    
                                }
                            ]);

            if(isset($query) && $query != '' && $query['from_date'] != '' && $query['to_date'] != '')
            {
                
                 $rstart           =      date("Y-m-d",strtotime($query['from_date']));
                 $rend             =      date("Y-m-d",strtotime($query['to_date']));
                 $squery->whereRaw("STR_TO_DATE(store_returns.return_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                 
                
            }                
            else
            {
                 $rstart           =      date("Y-m-d");
                 $rend             =      date("Y-m-d");
                 $squery->whereRaw("STR_TO_DATE(store_returns.return_date,'%d-%m-%Y') between '$rstart' and '$rend'");
            }


            $store_return   =   $squery->orderBy('store_return_id','DESC')->paginate(10);



        return view('storereturn::view_storereturn_data',compact('store_return','maxsales_id','minsales_id'));
    }

  public function view_storereturn_popup(Request $request)
  {
      Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            
             $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';

            $store_return = store_return::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('store_return_id','=',$request->store_return_id)
                ->select('*')
                ->with('storereturn_product.product')
                ->with('company')
                ->with('user')
                ->get();

               //echo $request->store_return_id;
                  $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($store_return[0]['storereturn_product'] AS $key=>$v) {

                

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
                
                 
               $maxsales_id   =  store_return::max('store_return_id');
               $minsales_id   =  store_return::min('store_return_id');

              
                 return view('storereturn::view_storereturn_popup',compact('store_return','maxsales_id','minsales_id'));

         }
        
    }
 public function previous_storereturn(Request $request)
  {
       Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            
             $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';

            $store_return = store_return::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('store_return_id','<',$request->store_return_id)
                ->select('*')
                ->with('storereturn_product.product')
                ->with('company')
                ->with('user')
                ->orderBy('store_return_id','DESC')
                ->take(1)
                ->get();

               //echo $request->store_return_id;
                  $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($store_return[0]['storereturn_product'] AS $key=>$v) {

                

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
                 // print_r($store_return);
                 // exit;
               $maxsales_id   =  store_return::max('store_return_id');
               $minsales_id   =  store_return::min('store_return_id');

              
                 return view('storereturn::view_storereturn_popup',compact('store_return','maxsales_id','minsales_id'));

           
                
        }
        
    }
 public function next_storereturn(Request $request)
   {
       Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            
             $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
            $company_state   = $state_id[0]['state_id'];
            $tax_type        = $state_id[0]['tax_type'];
            $tax_title       = $state_id[0]['tax_title'];
            $taxname         = $tax_type==1?$tax_title:'IGST';

            $store_return = store_return::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('store_return_id','>',$request->store_return_id)
                ->select('*')
                ->with('storereturn_product.product')
                ->with('company')
                ->with('user')
                ->orderBy('store_return_id','ASC')
                ->take(1)
                ->get();

               //echo $request->store_return_id;
                  $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($store_return[0]['storereturn_product'] AS $key=>$v) {

                

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
                
                 
               $maxsales_id   =  store_return::max('store_return_id');
               $minsales_id   =  store_return::min('store_return_id');

              
                 return view('storereturn::view_storereturn_popup',compact('store_return','maxsales_id','minsales_id'));
            

        }
        
    }
  public function manage_storereturn()
  {
      Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

            $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

            
        

            $store_return = storereturn_product::where('warehouse_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->orderBy('storereturn_product_id','ASC')
                ->with('product')
                ->with('store_return')
                ->withCount([
                    'returnbill_product as totalrdqty' => function($fquery) {
                        $fquery->select(DB::raw('SUM(restockqty+damageqty)'));
                        $fquery->whereRaw("company_id = storereturn_products.company_id");                    
                    }
                    ])
                ->where('stockstatus',0)
                ->paginate(10);

                // echo '<pre>';
                // print_r($store_return);
                // exit;

               //echo $request->store_return_id;
                  $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($store_return AS $key=>$v) {

                

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
                
                 
           

              
                 return view('storereturn::manage_storereturn',compact('store_return','get_store'));

        
        
    }
  public function datewise_manage_storereturn(Request $request)
  {
      Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

            $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

            
            $data            =      $request->all();
            $query = isset($data['query']) ? $data['query']  : '';



            $squery = storereturn_product::where('warehouse_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->with('product')
                ->with('store_return');
                

            if(isset($query) && $query != '' && isset($query['store_name']) && $query['store_name'] != '')
            {

                 $squery->where('company_id',$query['store_name']);
            }   
            if(isset($query) && $query != '' && $query['from_date'] != '' && $query['to_date'] != '')
            {

                 $rstart           =      date("Y-m-d",strtotime($query['from_date']));
                 $rend             =      date("Y-m-d",strtotime($query['to_date']));

                 $squery->with('store_return')->whereHas('store_return',function ($q) use ($rstart,$rend){
                   $q->whereRaw("STR_TO_DATE(store_returns.return_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                  });
                
            }

            $store_return   =  $squery->withCount([
                    'returnbill_product as totalrdqty' => function($fquery) {
                        $fquery->select(DB::raw('SUM(restockqty+damageqty)'));
                        $fquery->whereRaw("company_id = storereturn_products.company_id");                          
                    }
                    ])->orderBy('storereturn_product_id','ASC')
                    ->where('stockstatus',0)
                    ->paginate(10);


                  $product_features =  ProductFeatures::getproduct_feature('');
                  foreach ($store_return AS $key=>$v) {

                

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
                
                 
           

              
                 return view('storereturn::manage_storereturn_data',compact('store_return','get_store'));

        
        
    }
    public function storerestock_products(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
         $data = $request->all();
         $userId = Auth::User()->user_id;
         $company_id = $data[0]['storecompanyid'];
         $created_by = $userId; 

         $returndate      =   date("d-m-Y");

         $restockstatus  =   $data[0]['restockqty']!=0?1:0;
         $damagestatus   =   $data[0]['damageqty']!=0?1:0;

      
      try {
      DB::beginTransaction(); 


        $totalreturnqty     =    $data[0]['restockqty'] + $data[0]['damageqty'];

        if($data[0]['returnqty'] == $totalreturnqty)
        {
          
            storereturn_product::where('storereturn_product_id',$data[0]['storereturn_product_id'])->update(array(
                            'stockstatus' => 1
                        ));
        }


          if($data[0]['restockqty']>0)
          {

               $productqty    =  price_master::select('product_qty')
                        ->where('price_master_id',$data[0]['price_master_id'])
                        //->where('company_id',Auth::user()->company_id)
                        ->get();


                    $updateqty   =    $productqty[0]['product_qty'] +  $data[0]['restockqty'];

              price_master::where('price_master_id',$data[0]['price_master_id'])->update(array(
                          'product_qty' => $updateqty
              )); 

           

               inward_product_detail::where('inward_product_detail_id',$data[0]['inwardids'])
              ->update(array(
                  'modified_by' => Auth::User()->user_id,
                  'updated_at' => date('Y-m-d H:i:s'),
                  'pending_return_qty' => DB::raw('pending_return_qty + '.$data[0]['restockqty'])
                  ));
          
           }

          $totalqty    =  $data[0]['restockqty'] + $data[0]['damageqty'];

          $rinwardids        =  $data[0]['inwardids'].',';
          $rinwardqtys       =  $data[0]['restockqty'].',';

          $returnbillproduct = returnbill_product::updateOrCreate(
            ['returnbill_product_id' => $data[0]['returnbill_product_id'], 'company_id'=>$data[0]['storecompanyid'],],
            ['return_date'=>$returndate,
            'storereturn_product_id'=>$data[0]['storereturn_product_id'],
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


             $sellingpricedata    =   price_master::select('offer_price','sell_price','inward_stock_id')->where('price_master_id','=',$data[0]['price_master_id'])->where('company_id',$data[0]['storecompanyid'])->where('deleted_at','=',NULL)->get();

             $costpricedata            =   inward_product_detail::select('cost_rate','cost_igst_percent','cost_cgst_percent','cost_sgst_percent','cost_price','offer_price')->where('inward_product_detail_id','=',$data[0]['inwardids'])->where('company_id',$data[0]['storecompanyid'])->where('deleted_at','=',NULL)->get();

              
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




             $productdata            =   product::select('product_system_barcode')->where('product_id','=',$data[0]['product_id'])->where('deleted_at','=',NULL)->get();


      

              $last_damage_id = damage_product::where('company_id',$data[0]['storecompanyid'])->get()->max('damage_product_id');

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
                            ['damage_product_id' => '','company_id'=>$data[0]['storecompanyid'],],
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
                            'company_id'=>$data[0]['storecompanyid'],'damage_product_detail_id'=>'',],
                            ['product_id'=> $data[0]['product_id'],
                            'inward_product_detail_id'=> $data[0]['inwardids'],
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
          DB::commit();
        } catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }
         
         return json_encode(array("Success"=>"True","Message"=>"Products Restocked Successfully!"));



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
     * @param  \App\store_return  $store_return
     * @return \Illuminate\Http\Response
     */
    public function show(store_return $store_return)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\store_return  $store_return
     * @return \Illuminate\Http\Response
     */
    public function edit(store_return $store_return)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\store_return  $store_return
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, store_return $store_return)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\store_return  $store_return
     * @return \Illuminate\Http\Response
     */
    public function destroy(store_return $store_return)
    {
        //
    }
}
