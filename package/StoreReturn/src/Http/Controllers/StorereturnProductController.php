<?php

namespace Retailcore\StoreReturn\Http\Controllers;

use Retailcore\StoreReturn\Models\storereturn_product;
use Retailcore\StoreReturn\Models\store_return;
use Retailcore\SalesReturn\Models\return_bill;
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

class StorereturnProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

      
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $state = state::all();
        $country = country::all();
        
         $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->orderBy('payment_order','ASC')->get();

         
       
        
         return view('storereturn::store_return',compact('payment_methods','state','country'));
    }

    public function return_issueno_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->search_val !='')
        {

            $json = [];
            $result = inward_stock::select('inward_stock_id','po_no')
                ->whereNotNull('po_no')
                ->where('company_id',Auth::user()->company_id)->get();

           
           

            if(!empty($result))
            {
           
                foreach($result as $billkey=>$billvalue){

                       $json[$billkey]['label'] = $billvalue['po_no'];
                       $json[$billkey]['inward_stock_id'] = $billvalue['inward_stock_id'];

                      
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
    public function storereturnissueno_detail(Request $request)
    {
      
     
            $inward_stock_id   =  $request->inward_stock_id;
            $todaydate    =  date("Y-m-d");

            $query = inward_stock::select('inward_stock_id')
                    ->with([
                    'inward_product_detail' => function($fquery)  {
                        $fquery->select('inward_product_detail_id','batch_no','product_id','inward_stock_id','product_id','offer_price','sell_price','pending_return_qty');
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->with([
                                'product' => function($q)  {
                                    $q->select('product_id','product_name','uqc_id','product_system_barcode','supplier_barcode');
                                }
                            ]);                      

                        }
                    ])
                    ->where('company_id',Auth::user()->company_id)
                    ->where('inward_stock_id',$inward_stock_id);
                  
                

              $result  =  $query->get();
             

               if(sizeof($result) != 0)
               {
                   foreach($result[0]['inward_product_detail'] as $skey=>$svalue)
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
                 

               
                    return json_encode(array("Success"=>"True","Data"=>$result));
                   
                 
               }
               else
               {
                   return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));
               }
                

    }

    public function storereturnproduct_search(Request $request)
    {
        
        if($request->search_val !='')
        {

            $json = [];

            
            $result = product::select('product_name','product_system_barcode','supplier_barcode','product_id','product_code','product_id')
                ->where('item_type','!=',2)
                ->where(function($query) use ($request)
                {
                    $query->where('product_name', 'LIKE', "%$request->search_val%")
                        ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
                        ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%");
                })
                ->with('product_price_master')
                ->whereHas('product_price_master',function ($q) {
                  $q->where('company_id',Auth::user()->company_id);
                 })
                ->with([
                    'inward_product_detail' => function($fquery)  {
                        $fquery->select('inward_product_detail_id','batch_no','product_id','inward_stock_id');
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->with([
                                'inward_stock' => function($q)  {
                                    $q->select('inward_stock_id','po_no');
                                    $q->where('company_id',Auth::user()->company_id);
                                }
                            ]);                      

                    }
                ])
                ->take(10)->get();

               
           
            $ik = 0;      
             if(sizeof($result) != 0) 
             {
                foreach($result as $sproductkey=>$sproductvalue){

                        foreach($sproductvalue['inward_product_detail'] as $psproductkey=>$psproductvalue){
                         

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
                             $batch_no   =   '_'.$psproductvalue['batch_no'];
                          }
                          else
                          {
                             $batch_no   =  '';
                          }

 
                                $json[$sproductkey][$psproductkey]['label'] = $showbarcode.'_'.$sproductvalue['product_name'].$batch_no.'_'.$psproductvalue['inward_stock']['po_no'];
                                $json[$sproductkey][$psproductkey]['inward_product_detail_id'] = $psproductvalue['inward_product_detail_id'];
                                
                           

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
    public function storereturnproduct_detail(Request $request)
    {
      
     
            $inward_product_detail_id   =  $request->inward_product_detail_id;
            $todaydate    =  date("Y-m-d");

            $query = inward_product_detail::select('inward_product_detail_id','offer_price','sell_price','pending_return_qty','product_id','batch_no')
                    ->with('product','product.uqc')
                    ->where('inward_product_detail_id',$inward_product_detail_id);
                  
                

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

               
                    return json_encode(array("Success"=>"True","Data"=>$result));
                   
                 
               }
               else
               {
                   return json_encode(array("Success"=>"False","Message"=>"There is something wrong with the Product selected"));
               }
                

    }

public function storereturn_create(Request $request)
{
    Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
    $data = $request->all();
 //////////*************************Save Normal and Franchise Bills*********************************************
    
        $cstate_id = company_profile::select('state_id','billtype','bill_number_prefix','tax_type','billprint_type','series_type','bill_number_prefix')->where('company_id',Auth::user()->company_id)->get(); 
                

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;


        $created_by = $userId;

        $return_date    =  $data[1]['return_date'];
        $warehouse_id   =  company_relationship_tree::where('store_id',Auth::User()->company_id)
                                                      ->select('warehouse_id')->first();  

        
                
    
          store_return::where('store_return_id',$data[1]['store_return_id'])->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
          ));

     try {
            DB::beginTransaction();    

        

        $storereturn = store_return::updateOrCreate(
            ['store_return_id' => $data[1]['store_return_id'], 'company_id'=>$company_id,],
            ['warehouse_id'=>$warehouse_id['warehouse_id'],
            'return_no'=>'return_no',
                'return_date'=>$return_date,
                'total_qty'=>$data[1]['overallqty'],
                'remarks'=>$data[1]['official_note'],
                'created_by' =>$created_by,
                'is_active' => "1"
            ]
        );

      $store_return_id = $storereturn->store_return_id;
        


       
       $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
       $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

      

  //////////////////////////////////// To make Bill series Month Wise and Year wise as per the value selected from Company Profile......

        $todate       =    date('Y-m-d');
        
        $newyear      =   date('Y-04-01');
        
        $newmonth     =   date('Y-m-01');

//////////////////For Bill series Year Wise 

       

            $newseries  =  store_return::select('return_series')
                          ->where('store_return_id','<',$store_return_id)
                          ->where('company_id',Auth::user()->company_id)
                          ->orderBy('store_return_id','DESC')
                          ->take('1')
                          ->first();    

            $billseries   = $newseries['return_series']+1;

            $finalinvoiceno          =       'RT-'.$billseries.'/'.$f1.'-'.$f2; 
                
    

        

        if($data[1]['store_return_id']=='' || $data[1]['store_return_id']==null)
        {  

         store_return::where('store_return_id',$store_return_id)->update(array(
            'return_no' => $finalinvoiceno,
            'return_series' => $billseries
         ));
       }
    

       storereturn_product::where('store_return_id',$store_return_id)->update(array(
            'modified_by' => Auth::User()->user_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));

    
        $productdetail        =    array();
       

         foreach($data[0] AS $billkey=>$billvalue)
          {
              
               if($billvalue['barcodesel']!='')              {

               

                      $productdetail['storereturn_product_id']               =    $billvalue['storereturn_product_id'];
                      $productdetail['product_id']                           =    $billvalue['product_id'];
                      $productdetail['inward_product_detail_id']             =    $billvalue['inward_product_detail_id'];
                      $productdetail['qty']                                  =    $billvalue['qty'];
                      //$productdetail['oldqty']                               =    $billvalue['oldqty'];
                      $productdetail['created_by']                           =    Auth::User()->user_id;





                if($billvalue['oldqty'] != ''){

                   inward_product_detail::where('inward_product_detail_id',$billvalue['inward_product_detail_id'])->update(array(
                    'modified_by' => Auth::User()->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'pending_return_qty' => DB::raw('pending_return_qty + '.$billvalue['oldqty'])
                    ));
                    price_master::where('price_master_id',$billvalue['price_master_id'])->update(array(
                      'modified_by' => Auth::User()->user_id,
                      'updated_at' => date('Y-m-d H:i:s'),
                      'product_qty' => DB::raw('product_qty + '.$billvalue['oldqty'])
                      ));
                 
                  }

                   inward_product_detail::where('inward_product_detail_id',$billvalue['inward_product_detail_id'])->update(array(
                  'modified_by' => Auth::User()->user_id,
                  'updated_at' => date('Y-m-d H:i:s'),
                  'pending_return_qty' => DB::raw('pending_return_qty - '.$billvalue['qty'])
                  ));


                  $qquery    =         inward_product_detail::select('inward_product_detail_id','pending_return_qty','offer_price','batch_no')
                                      ->where('inward_product_detail_id',$billvalue['inward_product_detail_id'])
                                      ->where('company_id',Auth::user()->company_id)
                                      ->where('pending_return_qty','>',0)->first();  

                  $prodtype    =        product::select('product_type')
                                        ->where('product_id',$billvalue['product_id'])->first();

                  $prid      =         price_master::select('price_master_id')
                                                    ->where('company_id',Auth::user()->company_id)
                                                    ->where('product_id',$billvalue['product_id'])
                                                    ->where('product_qty','>',$billvalue['qty']);


                    if($cstate_id[0]['billtype']==3)
                    {
                          $prid->where('batch_no',$qquery['batch_no']);
                    }
                    if($prodtype['product_type']==1)
                    {
                          $prid->where('offer_price',$qquery['offer_price']);
                    }

                    $pricedetail  =  $prid->where('deleted_at','=',NULL)->first();



                    if($pricedetail=='')
                    {
                        
                                
                                return json_encode(array("Success"=>"False","Message"=>"Product cannot be saved as there is no Entry Found in Price Master against Barcode No. ".$billvalue['barcodesel']." "));
                                exit;
                           
                      }



                       price_master::where('price_master_id',$pricedetail['price_master_id'])->update(array(
                      'modified_by' => Auth::User()->user_id,
                      'updated_at' => date('Y-m-d H:i:s'),
                      'product_qty' => DB::raw('product_qty - '.$billvalue['qty'])
                      ));

                $productdetail['price_master_id']           =    $pricedetail['price_master_id'];

                $billproductdetail = storereturn_product::updateOrCreate(
                   ['store_return_id' => $store_return_id,
                    'company_id'=>$company_id,'warehouse_id'=>$warehouse_id['warehouse_id'],'storereturn_product_id'=>$billvalue['storereturn_product_id'],],
                   $productdetail);

        
              
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
            
           
                 if($data[1]['store_return_id'] != '')
                {   
                    return json_encode(array("Success"=>"True","Message"=>"Billing successfully Update!","url"=>"store_return"));
                }
                else
                {
                    return json_encode(array("Success"=>"True","Message"=>"Billing has been successfully added.","url"=>"store_return"));
                }
           
          
           
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }

 

}


 public function edit_storerreturnbill(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $store_return_id = decrypt($request->store_return_id);
       
        $bill_data = store_return::where([
            ['store_return_id','=',$store_return_id],
            ['company_id',Auth::user()->company_id]])
            ->with('storereturn_product','storereturn_product.product','storereturn_product.inward_product_detail')->first();

             $product_features =  ProductFeatures::getproduct_feature('');
             foreach($bill_data['storereturn_product'] as $ss=>$bval)
             {
                
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
    

              return json_encode(array("Success"=>"True","Data"=>$bill_data,"url"=>"store_return"));
          


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
     * @param  \App\storereturn_product  $storereturn_product
     * @return \Illuminate\Http\Response
     */
    public function show(storereturn_product $storereturn_product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\storereturn_product  $storereturn_product
     * @return \Illuminate\Http\Response
     */
    public function edit(storereturn_product $storereturn_product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\storereturn_product  $storereturn_product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, storereturn_product $storereturn_product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\storereturn_product  $storereturn_product
     * @return \Illuminate\Http\Response
     */
    public function destroy(storereturn_product $storereturn_product)
    {
        //
    }
}
