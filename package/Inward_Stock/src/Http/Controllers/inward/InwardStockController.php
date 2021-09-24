<?php

namespace Retailcore\Inward_Stock\Http\Controllers\inward;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail;

use App\Http\Controllers\Controller;

use App\Http\Controllers\product\ProductImageController;
use App\Http\Middleware\EncryptCookies;
use function foo\func;
use Retailcore\Inward_Stock\Models\inward\inward;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Sales\Models\payment_method;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Products\Models\product\product;
use Retailcore\PO\Models\purchase_order\purchase_order;
use Retailcore\PO\Models\purchase_order\purchase_order_detail;
use Retailcore\Supplier\Models\supplier\supplier_company_info;
use Retailcore\Supplier\Models\supplier\supplier_gst;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Supplier\Models\supplier\supplier_payment_detail;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Debit_Note\Models\debit_note\debit_note;
use Retailcore\Debit_Note\Models\debit_note\supplier_debit_payment_detail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;
use DB;
use Log;
class InwardStockController extends Controller
{
    public function fmcg_inward_stock_show()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $payment_methods = payment_method::where('deleted_at','=',NULL)
                            ->orderBy('payment_order', 'ASC')
                            ->get();

        $company_state = company_profile::select('state_id','billtype','inward_calculation')
                            ->where('company_id',Auth::user()->company_id)
                            ->get()
                            ->first();

        $company_state_id = $company_state['state_id'];
        $bill_type = $company_state['billtype'];
        $inward_calculation = $company_state['inward_calculation'];

        $inward_stock = inward_stock::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->with('product')
            ->orderBy('inward_stock_id','DESC')
            ->paginate(10);

        $inward_type = 1;
        $unique_barcode_inward = 0;
        return view('inward_stock::inward/inward_stock_show',compact('payment_methods','inward_stock','company_state_id','inward_type','bill_type','inward_calculation','unique_barcode_inward'));
    }

    public function garment_inward_stock_show()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $payment_methods = payment_method::where('deleted_at','=',NULL)
            ->orderBy('payment_order', 'ASC')->get();

        $company_state = company_profile::select('state_id','billtype','inward_calculation')
            ->where('company_id',Auth::user()->company_id)
            ->get()->first();
        $company_state_id = $company_state['state_id'];
        $bill_type = $company_state['billtype'];
        $inward_calculation = $company_state['inward_calculation'];

        $inward_stock = inward_stock::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->with('product')
            ->orderBy('inward_stock_id', 'DESC')
            ->paginate(10);

        $inward_type = 2;
        $unique_barcode_inward = 0;
        return view('inward_stock::inward/inward_stock_show',compact('payment_methods','inward_stock','company_state_id','inward_type','bill_type','inward_calculation','unique_barcode_inward'));
    }

    public function unique_inward_stock_show()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $payment_methods = payment_method::where('deleted_at','=',NULL)
            ->orderBy('payment_order', 'ASC')->get();

        $company_state = company_profile::select('state_id','billtype','inward_calculation','inward_type')
            ->where('company_id',Auth::user()->company_id)
            ->get()->first();

        $company_state_id = $company_state['state_id'];
        $bill_type = $company_state['billtype'];
        $inward_calculation = $company_state['inward_calculation'];

        $inward_stock = inward_stock::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->with('product')
            ->orderBy('inward_stock_id', 'DESC')
            ->paginate(10);

        $url = 'inward_stock';
        $inward_type = 1;
        $unique_barcode_inward = 1;
        if(isset($company_state) && $company_state != '')
        {
            if($company_state['inward_type'] == 1)
            {
                $url = 'inward_stock';
            }
            if($company_state['inward_type'] == 2)
            {
                $url = 'inward_stock_show';
            }
            $inward_type = $company_state['inward_type'];
        }

        return view('inward_stock::inward/inward_stock_show',compact('payment_methods','inward_stock','company_state_id','inward_type','bill_type','inward_calculation','unique_barcode_inward'));
    }
    public function isproduct_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {
            if(!isset($request->product_type) && $request->product_type == '')
            {
                $inward_type = company_profile::select('inward_type')
                    ->where('company_id',Auth::user()->company_id)->first();
                $request->product_type = $inward_type['inward_type'];
            }
            $request->item_type = 1;
            if(isset($request->unique_barcode_inward) && $request->unique_barcode_inward != '' && $request->unique_barcode_inward == 1)
            {
                $request->item_type = 3;
            }

            $json = [];
            $result = product::
                select('product_name','product_system_barcode','supplier_barcode','product_id','hsn_sac_code','product_id')
                ->where('product_type',$request->product_type)
                ->where('item_type',$request->item_type)
                ->where(function($query) use ($request)
                {
                   // $query->where('product_type',$request->product_type)
                    $query->where('product_name', 'LIKE', "%$request->search_val%")
                        ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
                        ->orWhere('product_code', 'LIKE', "%$request->search_val%")
                        ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%");
                })
                ->with('product_price_master')
                ->whereHas('product_price_master',function($q)
                {
                    $q->where('company_id',Auth::user()->company_id);
                })
                ->get();


           /* $sresult = product::select('supplier_barcode','product_name','product_system_barcode','product_id')
                ->where('company_id',Auth::user()->company_id)
                ->Where('supplier_barcode', 'LIKE', "%$request->search_val%")->take(10)->get();*/

             if(sizeof($result) != 0)
            {
                foreach($result as $productkey=>$productvalue)
                {

                     /* $json[$productkey]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'];
                      $json[$productkey]['barcode'] = $productvalue['product_system_barcode'];
                      $json[$productkey]['product_name'] = $productvalue['product_name'];
                      $json[$productkey]['product_id'] = $productvalue['product_id'];*/

                     if($productvalue['supplier_barcode']!='' || $productvalue['supplier_barcode']!=null)
                    {
                        $json[$productkey]['label'] = $productvalue['supplier_barcode'].'_'.$productvalue['product_name'];
                        $json[$productkey]['barcode'] = $productvalue['supplier_barcode'];
                        $json[$productkey]['product_name'] = $productvalue['product_name'];
                        $json[$productkey]['product_id'] = $productvalue['product_id'];
                        $json[$productkey]['name_of_barcode'] = 'supplier_barcode';
                    }
                    else
                    {
                        $json[$productkey]['label'] = $productvalue['product_system_barcode'].'_'.$productvalue['product_name'];
                        $json[$productkey]['barcode'] = $productvalue['product_system_barcode'];
                        $json[$productkey]['product_name'] = $productvalue['product_name'];
                        $json[$productkey]['product_id'] = $productvalue['product_id'];
                        $json[$productkey]['name_of_barcode'] = 'product_system_barcode';
                    }
                }

            }
          /* if(sizeof($sresult) != 0)
            {
               foreach($sresult as $sproductkey=>$sproductvalue){

                      $json[$sproductkey]['label'] = $sproductvalue['supplier_barcode'].'_'.$sproductvalue['product_name'];
                      $json[$sproductkey]['barcode'] = $sproductvalue['product_system_barcode'];
                      $json[$sproductkey]['product_name'] = $sproductvalue['product_name'];
                      $json[$sproductkey]['product_id'] = $sproductvalue['product_id'];
                }
            }*/

            return json_encode($json);
        }
        else
        {
          $json = [];
          return json_encode($json);
        }


    }
    public function product_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if(!isset($request->product_type) && $request->product_type == '')
        {

            $inward_type = company_profile::select('inward_type')
                ->where('company_id',Auth::user()->company_id)->first();
            $request->product_type = $inward_type['inward_type'];
        }

        $result = product::select('item_type','product_name','product_system_barcode','product_id','supplier_barcode')
            ->where('product_type',$request->product_type)
            ->where('company_id',Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->whereNotNull('product_name')
            ->where('product_system_barcode','!=',"")
            ->where('item_type','=','1')
            ->where(function($query) use ($request) {
                $query->where('product_name', 'LIKE', "%$request->search_val%")
                    ->orWhere('product_system_barcode','LIKE', "%$request->search_val%")
                    ->orWhere('product_code', 'LIKE', "%$request->search_val%")
                    ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%");
            })->skip(0)->take(50)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function supplier_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $result = supplier_company_info::select('company_id','supplier_company_name',
            'supplier_company_info_id','supplier_first_name','supplier_last_name'   )
            ->whereNull('deleted_at')
            //->where('company_id',Auth::user()->company_id)
            ->where(function($query) use ($request)
            {
                $query->where('supplier_first_name', 'LIKE', "%$request->search_val%");
                $query->orWhere('supplier_last_name', 'LIKE', "%$request->search_val%");
                $query->orWhere('supplier_company_name', 'LIKE', "%$request->search_val%");
            })->with('supplier_gst')
            ->orWhereHas('supplier_gst',function ($q) use($request)
            {
                $q->select('supplier_gstin');
                $q->where('supplier_gstin', 'LIKE', "%$request->search_val%");
            })
            ->get();

        return json_encode(array("Success"=>"True","Data"=>$result));
    }

    public function product_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $request->item_type = 1;
        if(isset($request->unique_barcode_inward) && $request->unique_barcode_inward != '' && $request->unique_barcode_inward == 1)
        {
            $request->item_type = 3;
        }

        $result = product::
             where('product_id',$request->product_id)
            ->where('item_type',$request->item_type)
            ->with('product_features_relationship')
            ->with('product_price_master')
            ->whereHas('product_price_master',function($q){
                $q->where('company_id',Auth::user()->company_id);
            })
            ->get();
        $product_features =  ProductFeatures::getproduct_feature('');
        foreach ($result AS $key=>$value)
        {
            if(isset($value['product_features_relationship']) && $value['product_features_relationship'] != '')
            {
                foreach ($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if($value['product_features_relationship'][$html_id] != '' && $value['product_features_relationship'][$html_id] != NULL)
                    {
                        $nm =  product::feature_value($vv['product_features_id'],$value['product_features_relationship'][$html_id]);
                        $result[$key][$html_id] =$nm;
                    }
                }
            }
        }



        return json_encode(array("Success"=>"True","Data"=>$result));
    }

    /*Public function add_inward_stock(Request $request)
    {
        $data = $request->all();
        $company_id = Auth::User()->company_id;

        $inward_stock = $data['inward_stock'];
        $inward_stock[0]['invoice_date'] =  date('d-m-Y');

        //check same gst and invoice no availble or not
        $same_gst_invoice = inward_stock::where('company_id', Auth::user()->company_id)
            ->where('supplier_gst_id', '=', $inward_stock[0]['supplier_gst_id'])
            ->where('invoice_no', '=', $inward_stock[0]['invoice_no'])
            ->where('deleted_at', '=', NULL)
            ->where('inward_stock_id','!=',$data['inward_stock_id'])
            ->count();
        //end of check same gst id and invoice no

        if($same_gst_invoice > 0)
        {
            return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>"This invoice number already exist on this supplier!"));
            exit;
        }
       $inward_product_detail = $data['inward_product_detail'];
       $supplier_payment_detail = $data['supplier_payment_detail'];

       //add record to inward stock;
       $inward_stock_array = array();
       foreach($inward_stock[0] AS $inward_stock_key=>$inward_stock_value)
       {
           $inward_stock_array[$inward_stock_key] = $inward_stock_value;
       }
       $inward_stock_array['company_id'] = $company_id;
       $inward_stock_array['created_by'] =  Auth::User()->user_id;

      // $inward_stock_insert  = inward_stock::create($inward_stock_array);
       $inward_stock_insert  = inward_stock::updateOrCreate(
           ['inward_stock_id' => $data['inward_stock_id'], 'company_id'=>$company_id,],
           $inward_stock_array
       );

       //end of insert inward stock

       $inward_stock_id = $inward_stock_insert->inward_stock_id;
       $inward_product_detail_array = array();
       $price_master_array = array();

       //update all value of product detail set deleted_by  and deleted_at where inword_stock_id = $inward_stock_id
       inward_product_detail::where('inward_stock_id',$inward_stock_id)
            ->where('company_id', Auth::user()->company_id)
            ->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s')
        ));
        //end of update inward price master

        //set deleted_by and deleted_at in all entry in price master where inword_stock_id = $inward_stock_id
        price_master::where('inward_stock_id',$inward_stock_id)
            ->where('company_id', Auth::user()->company_id)
            ->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s')
        ));
        //end of price master update


       //add inward product detail and inward price master
       foreach($inward_product_detail AS $inward_product_detail_key=>$inward_product_detail_value)
           {
               $inward_product_detail_array['company_id'] = $company_id;
               $price_master_array['company_id'] = $company_id;
               $inward_product_detail_array['inward_stock_id'] = $inward_stock_id;
               $price_master_array['inward_stock_id'] = $inward_stock_id;

               foreach ($inward_product_detail_value AS $key=>$value)
               {
                   if($key != 'gst_percent' && $key != 'gst_amount'   && $key != 'price_master_id')
                   {
                       $inward_product_detail_array[$key] = $value;
                   }
                   if($key == 'batch_no' || $key == 'product_qty' || $key == 'product_mrp' || $key == 'offer_price' || $key=='wholesaler_price' || $key == 'product_id' || $key == 'price_master_id' || $key== 'sell_price' || $key== 'selling_gst_percent' || $key == 'selling_gst_amount')
                   {
                       $price_master_array[$key] = $value;
                   }
               }

               $inward_product_detail_array['created_by'] =  Auth::User()->user_id;
               //if same inward_stock_id is present then update it and also set null deleted_at and deleted_by
               $inward_product_detail_array['deleted_at'] =  NULL;
               $inward_product_detail_array['deleted_by'] =  NULL;
               //end
               $price_master_array['created_by'] =  Auth::User()->user_id;
               $price_master_array['deleted_at'] =  NULL;
               $price_master_array['deleted_by'] =  NULL;


               $inward_product_detail_insert = inward_product_detail::updateOrCreate(
                   ['inward_stock_id' => $data['inward_stock_id'],
                    'company_id'=>$company_id,'inward_product_detail_id'=>$inward_product_detail_array['inward_product_detail_id'],],
                   $inward_product_detail_array
               );


              if($data['inward_stock_id'] != '')
              {

                  $price_master = price_master::updateOrCreate(
                      ['inward_stock_id' => $data['inward_stock_id'],
                          'product_id' => $inward_product_detail_value['product_id'],
                          'company_id'=>$company_id,'price_master_id'=>$price_master_array['price_master_id'],],
                      $price_master_array);
              }
              else
              {
                  $qty = price_master::select('product_qty','price_master_id')
                      ->where('batch_no',$price_master_array['batch_no'])
                      ->where('deleted_at',NULL)
                      ->where('product_id','=',$inward_product_detail_value['product_id'])
                      ->where('company_id', Auth::user()->company_id)->get();

                  if(isset($qty) && $qty != '' && isset($qty[0]['product_qty']))
                  {

                      $total_update_qty  = $inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty'] + $qty[0]['product_qty'];

                      $price_master_array['product_qty'] = $total_update_qty;
                      $price_master = price_master::updateOrCreate(
                          ['price_master_id' => $qty[0]['price_master_id'],
                              'product_id' => $inward_product_detail_value['product_id'],
                              'company_id'=>$company_id,
                           ],
                          $price_master_array);


                  }
                  else
                  {

                      $price_master = price_master::updateOrCreate(
                          ['inward_stock_id' => $data['inward_stock_id'],
                              'product_id' => $inward_product_detail_value['product_id'],
                              'company_id'=>$company_id,'price_master_id'=>$price_master_array['price_master_id'],],
                          $price_master_array);

                  }

              }

           }


       //end of insert product detail and price master

        //set deleted_by and deleted_at in all entry where inward_stock_id match
        supplier_payment_detail::where('inward_stock_id',$inward_stock_id)->update(array(
            'deleted_by' => Auth::User()->user_id,
            'deleted_at' => date('Y-m-d H:i:s')
        ));
       //end of update


        //add record in  supplier payment table
        $payment_detail_array = array();
       foreach ($supplier_payment_detail AS $payment_key=>$payment_value)
       {
           $supplier_payment_detail_id = '';
          foreach ($payment_value AS $key=>$value)
          {
              if($key == 'supplier_payment_detail_id')
              {
                    $supplier_payment_detail_id = $value;
              }
              else {
                  $payment_detail_array[$key] = $value;
              }
          }
           $payment_detail_array['created_by'] =  Auth::User()->user_id;
           $payment_detail_array['inward_stock_id'] = $inward_stock_id;
           $payment_detail_array['company_id'] = $company_id;
           $payment_detail_array['deleted_at'] =  NULL;
           $payment_detail_array['deleted_by'] =  NULL;

           //$supplier_payment = supplier_payment_detail::create($payment_detail_array);


           $supplier_payment = supplier_payment_detail::updateOrCreate(
               ['inward_stock_id' => $data['inward_stock_id'],
                'company_id'=>$company_id,'supplier_payment_detail_id'=>$supplier_payment_detail_id,],
               $payment_detail_array);
       }
       //end of to add record in supplier payment table


      if($inward_stock_insert)
      {
          if($data['inward_stock_id'] != '')
          {
              return json_encode(array("Success"=>"True","Message"=>"Stock successfully Update!","url"=>"view_inward_stock"));
          }
          else
          {
              return json_encode(array("Success"=>"True","Message"=>"Stock successfully inward!","url"=>''));
          }

      }
    }*/

    public function validate_inward(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

        //check same gst and invoice no availble or not
        $same_gst_invoice = inward_stock::select('inward_stock_id')
            ->where('company_id',Auth::user()->company_id)
            ->where('supplier_gst_id', '=', $data['gst_id'])
            ->where('invoice_no', '=', $data['invoice_no'])
            ->where('deleted_at', '=', NULL)
            ->get()
            ->first();
        //end of check same gst id and invoice no

        if(isset($same_gst_invoice) && $same_gst_invoice != '' && $same_gst_invoice['inward_stock_id'] != '')
        {
            return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>"This invoice number already exist on this supplier.You can change invoice no or supplier!"));
            exit;
        }
        else
        {
            return json_encode(array("Success"=>"True"));
        }

    }

    public function add_fmcg_inward_stock(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();
        $company_id = Auth::User()->company_id;

        $inward_stock = $data['inward_stock'];
        //$inward_stock[0]['invoice_date'] =  date('d-m-Y');

        //check same gst and invoice no availble or not
        $same_gst_invoice = inward_stock::select('inward_stock_id')
            ->where('company_id',Auth::user()->company_id)
            ->where('supplier_gst_id', '=', $inward_stock[0]['supplier_gst_id'])
            ->where('invoice_no', '=', $inward_stock[0]['invoice_no'])
            ->where('deleted_at', '=', NULL)
            ->where('inward_stock_id','!=',$data['inward_stock_id'])
            ->get()
            ->first();
        //end of check same gst id and invoice no


        if(isset($same_gst_invoice) && $same_gst_invoice != '' && $same_gst_invoice['inward_stock_id'] != '')
        {
            //return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>"This invoice number already exist on this supplier.You Can edit this inward.you want to edit?!","edit_id"=>encrypt($same_gst_invoice['inward_stock_id'])));
            return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>"This invoice number already exist on this supplier.You can change invoice no or supplier!","edit_id"=>encrypt($same_gst_invoice['inward_stock_id'])));
            exit;
        }
        $inward_product_detail = $data['inward_product_detail'];
        $supplier_payment_detail = $data['supplier_payment_detail'];

        //check same batch number do not have multiple mrp and offer price.if confirm for update then update all value
        if($data['update_offer_price'] == 0)
        {
            foreach ($inward_product_detail AS $kk => $vv)
            {
                if (isset($vv['batch_no']) && $vv['batch_no'] != '')
                {
                    $same_batch_no = price_master::select('price_master_id', 'offer_price')
                        ->where('company_id', Auth::user()->company_id)
                        ->where('batch_no', '=', $vv['batch_no'])
                        ->where('product_id', '=', $vv['product_id'])
                        ->where('deleted_at', '=', NULL)
                        ->where('offer_price', '!=', $vv['offer_price'])
                        ->get()->first();
                    if (isset($same_batch_no) && $same_batch_no['price_master_id'] != '')
                    {
                        $barcode = product::select('supplier_barcode','product_system_barcode')->where('product_id',$vv['product_id'])->where('company_id',Auth::user()->company_id)->first();
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

                        return json_encode(array("Success" => "False", "status_code" => 410, "Message" => "Sorry,Barcode '" .$show_barcode. "',Batch No. '" . $vv['batch_no'] . "' already present with Rs.'" . $same_batch_no['offer_price'] . "' offer price.Are you sure want to change offer price! "));
                        exit;
                    }
                }
            }
        }
        //end of check same batch no have different mrp and offer price

        try
        {
            DB::beginTransaction();

        //add record to inward stock;
        $inward_stock_array = array();

        foreach($inward_stock[0] AS $inward_stock_key=>$inward_stock_value)
        {
            $inward_stock_array[$inward_stock_key] = $inward_stock_value;
        }
        $inward_stock_array['company_id'] = $company_id;
        $inward_stock_array['created_by'] =  Auth::User()->user_id;

        $inward_stock_insert  = inward_stock::updateOrCreate(
            ['inward_stock_id' => $data['inward_stock_id'], 'company_id'=>$company_id,],
            $inward_stock_array
        );

        //end of insert inward stock
        $inward_stock_id = $inward_stock_insert->inward_stock_id;
        $inward_product_detail_array = array();
        $price_master_array = array();

        //update all value of product detail set deleted_by  and deleted_at where inword_stock_id = $inward_stock_id
        inward_product_detail::where('inward_stock_id',$inward_stock_id)
            ->where('company_id', Auth::user()->company_id)
            ->update(array(
                'deleted_by' => Auth::User()->user_id,
                'deleted_at' => date('Y-m-d H:i:s')
            ));
        //end of update inward price master


        //add inward product detail and inward price master
        foreach($inward_product_detail AS $inward_product_detail_key=>$inward_product_detail_value)
        {
            $inward_product_detail_array['company_id'] = $company_id;
            $price_master_array['company_id'] = $company_id;
            $inward_product_detail_array['inward_stock_id'] = $inward_stock_id;
            //$price_master_array['inward_stock_id'] = $inward_stock_id;

            foreach ($inward_product_detail_value AS $key => $value)
            {
                if ($key != 'gst_percent' && $key != 'gst_amount' && $key != 'pending_qty' && $key != 'po_pending_show')
                {
                    $inward_product_detail_array[$key] = $value;
                }
                if ($key == 'batch_no' || $key == 'product_qty' || $key == 'free_qty' || $key == 'product_mrp' || $key == 'offer_price' || $key == 'wholesaler_price' || $key == 'product_id' || $key == 'sell_price' || $key == 'selling_gst_percent' || $key == 'selling_gst_amount') {
                    if ($key == 'free_qty')
                    {
                        $price_master_array['product_qty'] += $value;
                    }
                    else {
                        $price_master_array[$key] = $value;
                    }
                }
            }


            $inward_product_detail_array['created_by'] =  Auth::User()->user_id;
            //if same inward_stock_id is present then update it and also set null deleted_at and deleted_by
            $inward_product_detail_array['deleted_at'] =  NULL;
            $inward_product_detail_array['deleted_by'] =  NULL;
            //end
            $price_master_array['created_by'] =  Auth::User()->user_id;
            $price_master_array['deleted_at'] =  NULL;
            $price_master_array['deleted_by'] =  NULL;


            //for update po qty
            if($inward_stock[0]['po_no'] != '')
            {
                $purchase_order =  purchase_order::select('purchase_order_id')
                    ->where('company_id',Auth::user()->company_id)
                    ->where('po_no',$inward_stock[0]['po_no'])
                    ->whereNull('deleted_at')
                    ->first();

                if(isset($purchase_order) && $purchase_order != '' )
                {
                    $po_qty = purchase_order_detail::select('qty','received_qty','pending_qty','unique_barcode')
                        ->where('company_id',Auth::user()->company_id)
                        ->where('purchase_order_id',$purchase_order['purchase_order_id'])
                        ->where('product_id',$inward_product_detail_array['product_id'])
                        ->where('unique_barcode',$inward_product_detail_array['batch_no'])
                        ->first();

                    if(isset($po_qty) && $po_qty != '')
                    {
                        if($data['inward_stock_id'] == '')
                        {
                            $pending_qty = ($po_qty['pending_qty'] - $inward_product_detail_array['product_qty']);
                            $received_qty = ($po_qty['received_qty'] + $inward_product_detail_array['product_qty']);
                        }else
                        {
                         //   $received_qty = (($po_qty['received_qty'] - $po_qty['pending_qty']) + $inward_product_detail_array['product_qty']);
                          //  $pending_qty = ($po_qty['qty'] - $received_qty);

                            $pending_qty = $inward_product_detail_value['po_pending_show'];
                            $received_qty = $po_qty['qty'] - $pending_qty;
                        }

                        purchase_order_detail::
                        where('company_id',Auth::user()->company_id)
                            ->where('purchase_order_id',$purchase_order['purchase_order_id'])
                            ->where('product_id',$inward_product_detail_array['product_id'])
                            ->where('unique_barcode',$po_qty['unique_barcode'])
                            ->update([
                                'received_qty' => $received_qty,
                                'pending_qty' => $pending_qty,
                                'free_qty' => $inward_product_detail_array['free_qty']
                            ]);
                    }
                }
            }
            //end of update po qty

            if($inward_product_detail_array['inward_product_detail_id'] != '')
            {
                $qtyp = inward_product_detail::select('product_qty','free_qty','inward_product_detail_id','product_mrp','sell_price','selling_gst_percent','selling_gst_amount','offer_price','batch_no')
                    // ->where('batch_no', '=', $inward_product_detail_array['batch_no'])
                    ->where('product_id', '=', $inward_product_detail_array['product_id'])
                    ->where('inward_stock_id', '=',$data['inward_stock_id'])
                    ->where('inward_product_detail_id','=',$inward_product_detail_array['inward_product_detail_id'])
                    ->where('company_id', Auth::user()->company_id)->get();


                if(isset($qtyp) && $qtyp != '' && !empty($qtyp) && isset($qtyp[0]))
                {
                    $product_qty_minus  = (($qtyp[0]['product_qty'] + $qtyp[0]['free_qty'])) ;
                }
                else
                {
                    $product_qty_minus = 0;
                }


                //minus qty from pending_rcv_qty if stock_transfer inward
                if($inward_product_detail_array['stock_transfers_detail_id'] != '' && $inward_product_detail_array['stock_transfers_detail_id'] != 0 && $inward_product_detail_array['stock_transfers_detail_id'] != NULL)
                {
                    $stock_store_id = company_profile::where('company_id',Auth::user()->company_id)->select('company_profile_id')->first();

                    $stock_rec_qty =  stock_transfer_detail::
                    where('stock_transfers_detail_id',$inward_product_detail_array['stock_transfers_detail_id'])
                        ->select('pending_rcv_qty')
                        ->with('stock_transfer_no')
                        ->whereHas('stock_transfer_no',function($q) use($stock_store_id){
                            $q->where('store_id', $stock_store_id['company_profile_id']);
                        })
                        ->first();

                    if(isset($stock_rec_qty) && $stock_rec_qty != '' && isset($stock_rec_qty['pending_rcv_qty']) && $stock_rec_qty['pending_rcv_qty'] != '')
                    {
                        $pending_rcv_qty = $stock_rec_qty['pending_rcv_qty'] + $product_qty_minus - ($inward_product_detail_array['product_qty'] + $inward_product_detail_array['free_qty']);

                        stock_transfer_detail::
                            where('stock_transfers_detail_id', $inward_product_detail_array['stock_transfers_detail_id'])
                            ->update(array(
                                'modified_by' => Auth::User()->user_id,
                                'updated_at' => date('Y-m-d H:i:s'),
                                'pending_rcv_qty' => $pending_rcv_qty
                            ));
                    }

                }
                //end of minus qty from stock_transfer_details table

                $qtys = price_master::select('product_qty','batch_no', 'price_master_id', 'product_mrp','offer_price')
                    ->where('batch_no', '=', $qtyp[0]['batch_no'])
                    ->where('offer_price', '=', $qtyp[0]['offer_price'])
                    ->where('product_id', '=', $inward_product_detail_array['product_id'])
                    ->where('company_id', Auth::user()->company_id)->get();


                if (isset($qtys) && $qtys != '' && isset($qtys[0]['offer_price']))
                {
                    $qty_with_minus = (($qtys[0]['product_qty']) - ($product_qty_minus));

                    $price_master_minus_qty = $price_master_array;
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
                   // $price_master_minus_qty['inward_stock_id'] = $qtys[0]['inward_stock_id'];
                    $price_master_id = '';

                    //$price_master_array['inward_stock_id'] = $qtys[0]['inward_stock_id'];


                    //minus qty from existing row
                 $price_master = price_master::updateOrCreate(
                        [
                            //'inward_stock_id' => $qtys[0]['inward_stock_id'],
                            'product_id' => $inward_product_detail_value['product_id'],
                            'offer_price' => $qtyp[0]['offer_price'],
                            'batch_no' => $qtyp[0]['batch_no'],
                            'company_id' => $company_id,
                            'price_master_id' => $qtys[0]['price_master_id'],],
                        $price_master_minus_qty);



                 if($price_master_array['batch_no'] == '' )
                 {
                        if ($qtys[0]['offer_price'] == $inward_product_detail_value['offer_price'])
                        {
                            $price_master_id = $qtys[0]['price_master_id'];
                            $total_qty = (($qtys[0]['product_qty'] - $product_qty_minus) + ($inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty']));
                            $price_master_array['product_qty'] = $total_qty;
                        } else {

                            $qty_price = price_master::select('product_qty', 'price_master_id', 'offer_price')
                                //->where('batch_no', '=', $price_master_array['batch_no'])
                                ->where('offer_price', '=', $price_master_array['offer_price'])
                                ->where('product_id', '=', $inward_product_detail_array['product_id'])
                                ->where('company_id', Auth::user()->company_id)
                                ->get();

                            if (isset($qty_price) && $qty_price != '' && isset($qty_price[0]['offer_price']))
                            {
                                $price_master_id = $qty_price[0]['price_master_id'];
                                $total_qty = ($inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty'] + $qty_price[0]['product_qty']);
                            } else {
                                $price_master_id = $qtys[0]['price_master_id'];
                                $total_qty = ($inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty']);
                            }
                            $price_master_array['product_qty'] = $total_qty;
                        }
                    }else {

                        /*  if ($qtyp[0]['batch_no'] != $price_master_array['batch_no'])
                          {*/

                        if($data['update_offer_price'] == 0)
                        {
                            $qtty = price_master::select('product_qty', 'batch_no', 'price_master_id', 'product_mrp', 'offer_price')
                                ->where('batch_no', '=', $price_master_array['batch_no'])
                                ->where('offer_price', '=', $qtyp[0]['offer_price'])
                                ->where('product_id', '=', $inward_product_detail_array['product_id'])
                                ->where('company_id', Auth::user()->company_id)->get();
                        }else
                        {

                            $qtty = price_master::select('product_qty', 'batch_no', 'price_master_id', 'product_mrp', 'offer_price')
                                ->where('batch_no', '=', $price_master_array['batch_no'])
                               // ->where('offer_price', '=', $qtyp[0]['offer_price'])
                                ->where('product_id', '=', $inward_product_detail_array['product_id'])
                                ->where('company_id', Auth::user()->company_id)
                                ->take(1)->get();
                        }
                        if (isset($qtty) && $qtty != '' && isset($qtty[0]['offer_price']))
                        {
                            $price_master_id = $qtty[0]['price_master_id'];
                            $plusqty  = $inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty'];
                            if(isset($inward_product_detail_value['pending_qty']))
                            {
                                $plusqty = $inward_product_detail_value['pending_qty'];
                            }

                            $total_qty = ($qtty[0]['product_qty'] + $plusqty);
                        } else {
                            $price_master_id = '';
                            $total_qty = $price_master_array['product_qty'];
                        }

                        $price_master_array['product_qty'] = $total_qty;
                    }

                    if($data['update_offer_price'] == 0) {
                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $inward_product_detail_value['product_id'],
                                'offer_price' => $inward_product_detail_value['offer_price'],
                                'batch_no' => $inward_product_detail_value['batch_no'],
                                'company_id' => $company_id,
                                'price_master_id' => $price_master_id,
                            ],
                            $price_master_array);
                    }else
                    {

                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $inward_product_detail_value['product_id'],
                               // 'offer_price' => $inward_product_detail_value['offer_price'],
                                'batch_no' => $inward_product_detail_value['batch_no'],
                                'company_id' => $company_id,
                                'price_master_id' => $price_master_id,
                            ],
                            $price_master_array);
                    }
                }
                else
                {
                    $price_master = price_master::updateOrCreate(
                        [
                            'product_id' => $inward_product_detail_value['product_id'],
                            'offer_price' => $inward_product_detail_value['offer_price'],
                            'batch_no' => $inward_product_detail_value['offer_price'],
                            'company_id'=>$company_id,
                        ],
                        $price_master_array);
                }

                $inward_product_detail_array['pending_return_qty'] = (isset($inward_product_detail_value['pending_qty'])) ? $inward_product_detail_value['pending_qty'] : ($inward_product_detail_value['product_qty']+$inward_product_detail_value['free_qty']);

                //if product have two offer price and user confirm to change offer price that case update that product offer price and related field to inward product detail.
                if($data['update_offer_price'] == 1)
                {
                    inward_product_detail::where('company_id',Auth::user()->company_id)
                        ->where('product_id',$inward_product_detail_array['product_id'])
                        ->update([
                            'offer_price' => $inward_product_detail_value['offer_price'],
                            'product_mrp' => $inward_product_detail_value['product_mrp'],
                            'sell_price' => $inward_product_detail_array['sell_price'],
                            'selling_gst_percent' => $inward_product_detail_array['selling_gst_percent'],
                            'selling_gst_amount' => $inward_product_detail_array['selling_gst_amount'],
                            'profit_percent' => $inward_product_detail_array['profit_percent'],
                            'profit_amount' => $inward_product_detail_array['profit_amount'],
                            'expiry_date' => $inward_product_detail_array['expiry_date'],
                            'mfg_date' => $inward_product_detail_array['mfg_date'],
                        ]);
                }
                //end of update this product offer price in all inward

                $inward_product_detail_insert = inward_product_detail::updateOrCreate(
                    ['inward_stock_id' => $data['inward_stock_id'],
                        'company_id' => $company_id,
                        'product_id' => $inward_product_detail_array['product_id'],
                        'inward_product_detail_id' => $inward_product_detail_array['inward_product_detail_id'],
                    ],
                    $inward_product_detail_array);

            }
            else
            {
                //if product have two offer price and user confirm to change offer price that case update that product offer price and related field to inward product detail.
                if($data['update_offer_price'] == 1){
                    inward_product_detail::where('company_id',Auth::user()->company_id)
                        ->where('product_id',$inward_product_detail_array['product_id'])
                        ->update([
                            'offer_price' => $inward_product_detail_value['offer_price'],
                            'product_mrp' => $inward_product_detail_value['product_mrp'],
                            'sell_price' => $inward_product_detail_array['sell_price'],
                            'selling_gst_percent' => $inward_product_detail_array['selling_gst_percent'],
                            'selling_gst_amount' => $inward_product_detail_array['selling_gst_amount'],
                            'profit_percent' => $inward_product_detail_array['profit_percent'],
                            'profit_amount' => $inward_product_detail_array['profit_amount'],
                            'expiry_date' => $inward_product_detail_array['expiry_date'],
                            'mfg_date' => $inward_product_detail_array['mfg_date'],
                        ]);
                }
                //end of update offer price in inward product detail

                //minus qty from pending_rcv_qty if stock_transfer inward
                if($inward_product_detail_array['stock_transfers_detail_id'] != '' && $inward_product_detail_array['stock_transfers_detail_id'] != 0 && $inward_product_detail_array['stock_transfers_detail_id'] != NULL)
                {

                    $stock_store_id = company_profile::where('company_id',Auth::user()->company_id)->select('company_profile_id')->first();

                    $stock_rec_qty =  stock_transfer_detail::
                        where('stock_transfers_detail_id',$inward_product_detail_array['stock_transfers_detail_id'])
                        ->select('pending_rcv_qty')
                        ->with('stock_transfer_no')
                        ->whereHas('stock_transfer_no',function($q) use($stock_store_id){
                            $q->where('store_id',$stock_store_id['company_profile_id']);
                        })
                       ->first();


                   if(isset($stock_rec_qty) && $stock_rec_qty != '' && isset($stock_rec_qty['pending_rcv_qty']) && $stock_rec_qty['pending_rcv_qty'] != '')
                   {
                        $pending_rcv_qty = $stock_rec_qty['pending_rcv_qty'] - ($inward_product_detail_array['product_qty'] + $inward_product_detail_array['free_qty']);

                        stock_transfer_detail::
                           where('stock_transfers_detail_id',$inward_product_detail_array['stock_transfers_detail_id'])
                           ->update([
                               'modified_by' => Auth::User()->user_id,
                               'updated_at' => date('Y-m-d H:i:s'),
                               'pending_rcv_qty' => $pending_rcv_qty,
                           ]);

                   }


                }
                //end of minus qty from stock_transfer_details table


                $inward_product_detail_array['pending_return_qty'] = $inward_product_detail_array['product_qty']+$inward_product_detail_array['free_qty'];
                $inward_product_detail_insert = inward_product_detail::updateOrCreate(
                    ['inward_stock_id' => $data['inward_stock_id'],
                        'company_id' => $company_id,
                        'inward_product_detail_id'=>$inward_product_detail_array['inward_product_detail_id']
                    ],
                    $inward_product_detail_array);

                if($data['update_offer_price'] == 0)
                {
                    $qtys = price_master::select('product_qty', 'price_master_id', 'product_mrp','offer_price')
                        ->where('batch_no', '=', $inward_product_detail_array['batch_no'])
                        ->where('offer_price', '=', $inward_product_detail_array['offer_price'])
                        ->where('product_id', '=', $inward_product_detail_array['product_id'])
                        ->where('company_id', Auth::user()->company_id)->get();
                }
                else
                {
                    $qtys = price_master::select('product_qty', 'price_master_id', 'product_mrp','offer_price')
                        ->where('batch_no', '=', $inward_product_detail_array['batch_no'])
                      //  ->where('offer_price', '=', $inward_product_detail_array['offer_price'])
                        ->where('product_id', '=', $inward_product_detail_array['product_id'])
                        ->where('company_id', Auth::user()->company_id)
                        ->take(1)
                        ->get();
                }

                if (isset($qtys) && $qtys != '' && isset($qtys[0]['offer_price']))
                {
                    //INCREMENT QUNTITY
                    $total_qty =  ($qtys[0]['product_qty'] + $inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty']);
                    $price_master_array['product_qty'] = $total_qty;

                    if($data['update_offer_price'] == 0)
                    {
                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $inward_product_detail_value['product_id'],
                                'offer_price' => $inward_product_detail_value['offer_price'],
                                'company_id' => $company_id,
                                'price_master_id' => $qtys[0]['price_master_id'],
                            ],
                            $price_master_array);
                    }else
                    {
                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $inward_product_detail_value['product_id'],
                                //'offer_price' => $inward_product_detail_value['offer_price'],
                                'company_id' => $company_id,
                                'price_master_id' => $qtys[0]['price_master_id'],
                            ],
                            $price_master_array);
                    }
                }
                else
                {

                    $price_master = price_master::updateOrCreate(
                        [
                            'product_id' => $inward_product_detail_value['product_id'],
                            'offer_price' => $inward_product_detail_value['offer_price'],
                            'batch_no' => $inward_product_detail_value['batch_no'],
                            'company_id'=>$company_id,
                        ],
                        $price_master_array);
                }
            }
        }
        //end of insert product detail and price master

       //update max inward unique barcode in company profile
       if($data['inward_stock_id'] == '')
            {
                $max_inward_unique = '';
                if($inward_stock[0]['inward_with_unique_barcode'] == 1)
                {
                    $max_inward_unique =   max(array_column($inward_product_detail, 'batch_no'));

                    company_profile::where('company_id',$company_id)
                        ->update(array(
                            'inward_unique_batch_no_value' => $max_inward_unique
                        ));
                }

            }

        if($inward_stock_array['stock_inward_type'] ==0) {
            //set deleted_by and deleted_at in all entry where inward_stock_id match
            $supplier_previous_debit = supplier_payment_detail::where('inward_stock_id', $inward_stock_id)
                ->where('company_id', $company_id)
                ->whereNull('deleted_at')
                ->where('payment_method_id', 9)->first();

            supplier_payment_detail::where('inward_stock_id', $inward_stock_id)->update(array(
                'deleted_by' => Auth::User()->user_id,
                'deleted_at' => date('Y-m-d H:i:s')
            ));
            //end of update

            //add record in  supplier payment table
            $payment_detail_array = array();
            $debit_amount = array();
            foreach ($supplier_payment_detail AS $payment_key => $payment_value) {
                if ($payment_value['payment_method_id'] == 9) {
                    $debit_amount['amount'] = $payment_value['amount'];
                }

                $supplier_payment_detail_id = '';
                foreach ($payment_value AS $key => $value) {
                    if ($key == 'supplier_payment_detail_id') {
                        $supplier_payment_detail_id = $value;
                    } else {
                        $payment_detail_array[$key] = $value;
                    }
                }
                $payment_detail_array['created_by'] = Auth::User()->user_id;
                $payment_detail_array['inward_stock_id'] = $inward_stock_id;
                $payment_detail_array['company_id'] = $company_id;
                $payment_detail_array['deleted_at'] = NULL;
                $payment_detail_array['deleted_by'] = NULL;

                $supplier_payment = supplier_payment_detail::updateOrCreate(
                    ['inward_stock_id' => $inward_stock_id,
                        'company_id' => $company_id,
                        'supplier_payment_detail_id' => $supplier_payment_detail_id,],
                    $payment_detail_array);
            }
            $minus_debit = (isset($supplier_previous_debit) && $supplier_previous_debit['amount'] != '' ? $supplier_previous_debit['amount'] : 0);
            if (isset($debit_amount) && isset($debit_amount['amount']) && $debit_amount['amount'] != '') {
                $debit_amt_for_update = debit_note::where('company_id', $company_id)
                    ->where('debit_note_id', $data['debit_note_id'])
                    ->whereNull('deleted_at')
                    ->select('total_cost_price', 'used_amount')->first();

                debit_note::where('company_id', $company_id)
                    ->where('debit_note_id', $data['debit_note_id'])
                    ->whereNull('deleted_at')
                    ->update(array(
                        'used_amount' => (($debit_amt_for_update['used_amount'] - $minus_debit) + $debit_amount['amount']),
                        'modified_by' => Auth::User()->user_id
                    ));

                supplier_debit_payment_detail::updateOrCreate(
                    ['supplier_debit_payment_detail_id' => '',
                        'company_id' => $company_id,
                        'debit_note_id' => $data['debit_note_id'],
                    ],
                    [
                        'inward_stock_id' => $inward_stock_id,
                        'supplier_gst_id' => $inward_stock[0]['supplier_gst_id'],
                        'debit_note_amount' => $debit_amt_for_update['total_cost_price'] - $debit_amt_for_update['used_amount'],
                        'debit_note_used_amount' => $debit_amount['amount'],
                        'debit_note_balance_amount' => $debit_amt_for_update['total_cost_price'] - $debit_amount['amount'],
                        'created_by' => Auth::User()->user_id
                    ]);
            } else {
                supplier_debit_payment_detail::where('inward_stock_id', $inward_stock_id)->update(array(
                    'deleted_by' => Auth::User()->user_id,
                    'deleted_at' => date('Y-m-d H:i:s')
                ));
                debit_note::where('company_id', $company_id)
                    ->where('debit_note_id', $data['debit_note_id'])
                    ->whereNull('deleted_at')
                    ->update(array(
                        'used_amount' => DB::raw('used_amount -' . $minus_debit),
                        'modified_by' => Auth::User()->user_id
                    ));


            }
            //end of to add record in supplier payment table
        }

            DB::commit();
        }
        catch (\Illuminate\Database\QueryException $e)
        {
        DB::rollback();
        return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }

        if($inward_stock_insert)
        {
            if($data['inward_stock_id'] != '')
            {
                return json_encode(array("Success"=>"True","Message"=>"Stock successfully Update!","url"=>"view_inward_stock"));
            }
            else
            {
                if($inward_stock[0]['po_no'] != '' && $inward_stock[0]['warehouse_id'] == '')
                {
                    $url = 'view_issue_po';
                }
                elseif($inward_stock[0]['po_no'] != '' && $inward_stock[0]['warehouse_id'] != '')
                {
                    $url = 'stock_transfer_inward';
                }
                else
                {
                    $url = '';
                }
                return json_encode(array("Success"=>"True","Message"=>"Stock successfully inward!","url"=>$url));
            }

        }
    }

    public function add_garment_inward_stock(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();
        $company_id = Auth::User()->company_id;

        $inward_stock = $data['inward_stock'];
        //$inward_stock[0]['invoice_date'] =  date('d-m-Y');

        //check same gst and invoice no availble or not

        $same_gst_invoice = inward_stock::select('inward_stock_id')->where('company_id', Auth::user()->company_id)
            ->where('supplier_gst_id', '=', $inward_stock[0]['supplier_gst_id'])
            ->where('invoice_no','=', $inward_stock[0]['invoice_no'])
            ->where('deleted_at','=', NULL)
            ->where('inward_stock_id','!=',$data['inward_stock_id'])
            ->get()->first();
        //end of check same gst id and invoice no
        if(isset($same_gst_invoice) && $same_gst_invoice != '' && $same_gst_invoice['inward_stock_id'] != '')
        {
//            return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>"This invoice number already exist on this supplier.You Can edit this inward.you want to edit?!","edit_id"=>encrypt($same_gst_invoice['inward_stock_id'])));
//            exit;
            return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>"This invoice number already exist on this supplier.You can change invoice no or supplier!","edit_id"=>encrypt($same_gst_invoice['inward_stock_id'])));
            exit;
        }

        try
        {
            DB::beginTransaction();

        $inward_product_detail = $data['inward_product_detail'];
        $supplier_payment_detail = $data['supplier_payment_detail'];

        //add record to inward stock;
        $inward_stock_array = array();
        foreach($inward_stock[0] AS $inward_stock_key=>$inward_stock_value)
        {
            $inward_stock_array[$inward_stock_key] = $inward_stock_value;
        }
        $inward_stock_array['company_id'] = $company_id;
        $inward_stock_array['created_by'] =  Auth::User()->user_id;

        // $inward_stock_insert  = inward_stock::create($inward_stock_array);
        $inward_stock_insert  = inward_stock::updateOrCreate(
            ['inward_stock_id' => $data['inward_stock_id'],
                'company_id'=>$company_id,],
            $inward_stock_array
        );


        //end of insert inward stock
        $inward_stock_id = $inward_stock_insert->inward_stock_id;
        $inward_product_detail_array = array();
        $price_master_array = array();

        //update all value of product detail set deleted_by  and deleted_at where inword_stock_id = $inward_stock_id
        inward_product_detail::where('inward_stock_id',$inward_stock_id)
            ->where('company_id', Auth::user()->company_id)
            ->update(array(
                'deleted_by' => Auth::User()->user_id,
                'deleted_at' => date('Y-m-d H:i:s')
            ));
        //end of update inward price master

        //set deleted_by and deleted_at in all entry in price master where inword_stock_id = $inward_stock_id
        /*price_master::where('inward_stock_id',$inward_stock_id)
            ->where('company_id', Auth::user()->company_id)
            ->update(array(
                'deleted_by' => Auth::User()->user_id,
                'deleted_at' => date('Y-m-d H:i:s')
            ));*/
        //end of price master update


        //add inward product detail and inward price master
        foreach($inward_product_detail AS $inward_product_detail_key=>$inward_product_detail_value)
        {
            $inward_product_detail_array['company_id'] = $company_id;
            $price_master_array['company_id'] = $company_id;
            $inward_product_detail_array['inward_stock_id'] = $inward_stock_id;
            //$price_master_array['inward_stock_id'] = $inward_stock_id;

            foreach ($inward_product_detail_value AS $key=>$value)
            {
                if($key != 'gst_percent' && $key != 'gst_amount' && $key != 'pending_qty' && $key !='po_pending_show')
                {
                    $inward_product_detail_array[$key] = $value;
                }
                if($key == 'batch_no' || $key == 'product_qty' || $key == 'product_mrp' || $key == 'offer_price' || $key=='wholesaler_price' || $key == 'product_id' ||  $key== 'sell_price' || $key== 'selling_gst_percent' || $key == 'selling_gst_amount')
                {
                    $price_master_array[$key] = $value;

                }
            }

            $inward_product_detail_array['created_by'] =  Auth::User()->user_id;
            //if same inward_stock_id is present then update it and also set null deleted_at and deleted_by
            $inward_product_detail_array['deleted_at'] =  NULL;
            $inward_product_detail_array['deleted_by'] =  NULL;
            //end
            $price_master_array['created_by'] =  Auth::User()->user_id;
            $price_master_array['deleted_at'] =  NULL;
            $price_master_array['deleted_by'] =  NULL;

            //for update po qty
            if($inward_stock[0]['po_no'] != '')
            {
                $purchase_order =   purchase_order::select('purchase_order_id')
                    ->where('company_id',Auth::user()->company_id)
                    ->where('po_no',$inward_stock[0]['po_no'])
                    ->whereNull('deleted_at')
                    ->first();


                if(isset($purchase_order) && $purchase_order != '' )
                {
                    $po_qty =   purchase_order_detail::select('qty','received_qty','pending_qty','unique_barcode')
                        ->where('company_id',Auth::user()->company_id)
                        ->where('purchase_order_id',$purchase_order['purchase_order_id'])
                        ->where('product_id',$inward_product_detail_array['product_id'])
                        ->where('unique_barcode',$inward_product_detail_array['batch_no'])
                        ->first();

                    if(isset($po_qty) && $po_qty != '')
                    {

                        if($data['inward_stock_id'] == '')
                        {
                            $pending_qty = ($po_qty['pending_qty'] - $inward_product_detail_array['product_qty']);
                            $received_qty = ($po_qty['received_qty'] + $inward_product_detail_array['product_qty']);
                        }else
                        {
                            //   $received_qty = (($po_qty['received_qty'] - $po_qty['pending_qty']) + $inward_product_detail_array['product_qty']);
                            //  $pending_qty = ($po_qty['qty'] - $received_qty);

                            $pending_qty = $inward_product_detail_value['po_pending_show'];
                            $received_qty = $po_qty['qty'] - $pending_qty;
                        }



                        //$pending_qty  = ($po_qty['qty'] - $inward_product_detail_array['product_qty']);

                       // $pending_qty  = ($po_qty['pending_qty'] - $inward_product_detail_array['product_qty']);
                       // $received_qty  = ($po_qty['received_qty'] + $inward_product_detail_array['product_qty']);
                        purchase_order_detail::
                        where('company_id',Auth::user()->company_id)
                            ->where('purchase_order_id',$purchase_order['purchase_order_id'])
                            ->where('product_id',$inward_product_detail_array['product_id'])
                            ->where('unique_barcode',$po_qty['unique_barcode'])
                            ->update([
                                'received_qty' => $received_qty,
                                'pending_qty' => $pending_qty,
                                'free_qty' => $inward_product_detail_array['free_qty']
                            ]);

                    }
                }
            }
            //end of update po qty

            if($data['inward_stock_id'] != '')
            {
                $qtyp = inward_product_detail::select('product_qty','free_qty','inward_product_detail_id','product_mrp')
                    // ->where('batch_no', '=', $inward_product_detail_array['batch_no'])
                    ->where('product_id', '=', $inward_product_detail_array['product_id'])
                    ->where('inward_stock_id', '=',$data['inward_stock_id'])
                    ->where('inward_product_detail_id', '=',$inward_product_detail_array['inward_product_detail_id'])
                    ->where('company_id', Auth::user()->company_id)->get();



                if(isset($qtyp) && $qtyp != '' && !empty($qtyp) && isset($qtyp[0]))
                {
                    $product_qty_minus  = (($qtyp[0]['product_qty'] + $qtyp[0]['free_qty'])) ;
                }
                else
                {
                    $product_qty_minus = 0;
                }

                //minus qty from pending_rcv_qty if stock_transfer inward
                if($inward_product_detail_array['stock_transfers_detail_id'] != '' && $inward_product_detail_array['stock_transfers_detail_id'] != 0 && $inward_product_detail_array['stock_transfers_detail_id'] != NULL)
                {
                    $stock_store_id = company_profile::where('company_id',Auth::user()->company_id)->select('company_profile_id')->first();

                    $stock_rec_qty =  stock_transfer_detail::
                    where('stock_transfers_detail_id',$inward_product_detail_array['stock_transfers_detail_id'])
                        ->select('pending_rcv_qty')
                        ->with('stock_transfer_no')
                        ->whereHas('stock_transfer_no',function($q) use($stock_store_id){
                            $q->where('store_id',$stock_store_id['company_profile_id']);
                        })
                        ->first();


                    if(isset($stock_rec_qty) && $stock_rec_qty != '' && isset($stock_rec_qty['pending_rcv_qty']) && $stock_rec_qty['pending_rcv_qty'] != '')
                    {
                        $pending_rcv_qty = $stock_rec_qty['pending_rcv_qty'] + $product_qty_minus - ($inward_product_detail_array['product_qty'] + $inward_product_detail_array['free_qty']);
                        stock_transfer_detail::
                            where('stock_transfers_detail_id', $inward_product_detail_array['stock_transfers_detail_id'])
                            ->update(array(
                                'modified_by' => Auth::User()->user_id,
                                'updated_at' => date('Y-m-d H:i:s'),
                                'pending_rcv_qty' => $pending_rcv_qty
                            ));
                    }
                }
                //end of minus qty from stock_transfer_details table


                $qtys = price_master::select('product_qty','price_master_id','product_mrp','offer_price')
                    //->where('offer_price', '=', $qtyp[0]['offer_price'])
                    ->where('product_id', '=', $inward_product_detail_array['product_id'])
                    ->where('company_id', Auth::user()->company_id)->get();

                if (isset($qtys) && $qtys != '' && isset($qtys[0]['offer_price']))
                {
                    $qty_with_minus = (($qtys[0]['product_qty'] - $product_qty_minus) +($inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty']));

                    $price_master_minus_qty = $price_master_array;
                    $price_master_minus_qty['product_qty'] = $qty_with_minus;
                    $price_master_minus_qty['deleted_at'] = NULL;
                    $price_master_minus_qty['deleted_by'] = NULL;

                    $price_master = price_master::updateOrCreate(
                        [
                            'product_id' => $inward_product_detail_value['product_id'],
                            'company_id' => $company_id,
                            /* 'price_master_id'=>$price_master_array['price_master_id'],*/],
                        $price_master_minus_qty);
                }else
                {
                    $price_master = price_master::updateOrCreate(
                        [
                            'product_id' => $inward_product_detail_value['product_id'],
                            'company_id' => $company_id,
                            /* 'price_master_id'=>$price_master_array['price_master_id'],*/],
                        $price_master_array);
                }
            }
            else
            {

                //minus qty from pending_rcv_qty if stock_transfer inward
                if($inward_product_detail_array['stock_transfers_detail_id'] != '' && $inward_product_detail_array['stock_transfers_detail_id'] != 0 && $inward_product_detail_array['stock_transfers_detail_id'] != NULL)
                {
                    $stock_store_id = company_profile::where('company_id',Auth::user()->company_id)->select('company_profile_id')->first();


                    $stock_rec_qty =  stock_transfer_detail::
                    where('stock_transfers_detail_id',$inward_product_detail_array['stock_transfers_detail_id'])
                        ->select('pending_rcv_qty')
                        ->with('stock_transfer_no')
                        ->whereHas('stock_transfer_no',function($q) use($stock_store_id){
                            $q->where('store_id',$stock_store_id['company_profile_id']);
                        })
                        ->first();



                    if(isset($stock_rec_qty) && $stock_rec_qty != '' && isset($stock_rec_qty['pending_rcv_qty']) && $stock_rec_qty['pending_rcv_qty'] != '')
                    {
                        $pending_rcv_qty = $stock_rec_qty['pending_rcv_qty'] -  ($inward_product_detail_array['product_qty'] + $inward_product_detail_array['free_qty']);
                        stock_transfer_detail::
                            where('stock_transfers_detail_id', $inward_product_detail_array['stock_transfers_detail_id'])
                            ->update(array(
                                'modified_by' => Auth::User()->user_id,
                                'updated_at' => date('Y-m-d H:i:s'),
                                'pending_rcv_qty' => $pending_rcv_qty
                            ));
                    }
                }
                //end of minus qty from stock_transfer_details table
                $qty = price_master::select('product_mrp','product_qty','price_master_id','offer_price')
                    ->where('deleted_at',NULL)
                    ->where('product_id','=',$inward_product_detail_value['product_id'])
                    ->where('company_id', Auth::user()->company_id)->get();

                if(isset($qty) && $qty != '' && isset($qty[0]['product_qty']))
                {
                    $total_update_qty  = $inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty'] + $qty[0]['product_qty'];

                    $price_master_array['product_qty'] = $total_update_qty;
                    $price_master = price_master::updateOrCreate(
                        ['price_master_id' => $qty[0]['price_master_id'],
                            'product_id' => $inward_product_detail_value['product_id'],
                            'company_id'=>$company_id,
                        ],
                        $price_master_array);
                }
                else
                {
                    $price_master = price_master::updateOrCreate(
                        [
                            //'inward_stock_id' => $data['inward_stock_id'],
                            'product_id' => $inward_product_detail_value['product_id'],
                            'company_id'=>$company_id,
                            /*'price_master_id'=>$price_master_array['price_master_id'],*/],
                        $price_master_array);

                }
            }

            // $inward_product_detail_array['pending_return_qty'] = $inward_product_detail_array['product_qty'];
            $inward_product_detail_array['pending_return_qty'] = (isset($inward_product_detail_value['pending_qty'])) ? $inward_product_detail_value['pending_qty'] : ($inward_product_detail_value['product_qty']+$inward_product_detail_value['free_qty']);
            $inward_product_detail_insert = inward_product_detail::updateOrCreate(
                ['inward_stock_id' => $data['inward_stock_id'],
                    'company_id'=>$company_id,
                    'inward_product_detail_id'=>$inward_product_detail_array['inward_product_detail_id'],],
                $inward_product_detail_array
            );
        }
        //end of insert product detail and price master

       //update max inward unique barcode in company profile
            if($data['inward_stock_id'] == '')
            {
                $max_inward_unique = '';
                if($inward_stock[0]['inward_with_unique_barcode'] == 1)
                {
                    $max_inward_unique =   max(array_column($inward_product_detail, 'batch_no'));
                    company_profile::where('company_id',$company_id)
                        ->update(array(
                            'inward_unique_batch_no_value' => $max_inward_unique
                        ));
                }

            }

       if($inward_stock_array['stock_inward_type'] ==0) {
                $supplier_previous_debit = supplier_payment_detail::where('inward_stock_id', $inward_stock_id)
                    ->where('company_id', $company_id)
                    ->whereNull('deleted_at')
                    ->where('payment_method_id', 9)->first();

                //set deleted_by and deleted_at in all entry where inward_stock_id match
                supplier_payment_detail::where('inward_stock_id', $inward_stock_id)->update(array(
                    'deleted_by' => Auth::User()->user_id,
                    'deleted_at' => date('Y-m-d H:i:s')
                ));
                //end of update

                //add record in  supplier payment table
                $payment_detail_array = array();
                $debit_amount = array();
                foreach ($supplier_payment_detail AS $payment_key => $payment_value) {
                    if ($payment_value['payment_method_id'] == 9) {
                        $debit_amount['amount'] = $payment_value['amount'];
                    }

                    $supplier_payment_detail_id = '';

                    foreach ($payment_value AS $key => $value) {
                        if ($key == 'supplier_payment_detail_id') {
                            $supplier_payment_detail_id = $value;
                        } else {
                            $payment_detail_array[$key] = $value;
                        }
                    }
                    $payment_detail_array['created_by'] = Auth::User()->user_id;
                    $payment_detail_array['inward_stock_id'] = $inward_stock_id;
                    $payment_detail_array['company_id'] = $company_id;
                    $payment_detail_array['deleted_at'] = NULL;
                    $payment_detail_array['deleted_by'] = NULL;

                    //$supplier_payment = supplier_payment_detail::create($payment_detail_array);

                    $supplier_payment = supplier_payment_detail::updateOrCreate(
                        ['inward_stock_id' => $data['inward_stock_id'],
                            'company_id' => $company_id,
                            'supplier_payment_detail_id' => $supplier_payment_detail_id,],
                        $payment_detail_array);
                }
                $minus_debit = (isset($supplier_previous_debit) && $supplier_previous_debit['amount'] != '' ? $supplier_previous_debit['amount'] : 0);
                if (isset($debit_amount) && isset($debit_amount['amount']) && $debit_amount['amount'] != '') {
                    $debit_amt_for_update = debit_note::where('company_id', $company_id)
                        ->where('debit_note_id', $data['debit_note_id'])
                        ->whereNull('deleted_at')
                        ->select('total_cost_price', 'used_amount')->first();

                    debit_note::where('company_id', $company_id)
                        ->where('debit_note_id', $data['debit_note_id'])
                        ->whereNull('deleted_at')
                        ->update(array(
                            'used_amount' => (($debit_amt_for_update['used_amount'] - $minus_debit) + $debit_amount['amount']),
                            'modified_by' => Auth::User()->user_id
                        ));

                    supplier_debit_payment_detail::updateOrCreate(
                        ['supplier_debit_payment_detail_id' => '',
                            'company_id' => $company_id,
                            'debit_note_id' => $data['debit_note_id'],
                        ],
                        [
                            'inward_stock_id' => $inward_stock_id,
                            'supplier_gst_id' => $inward_stock[0]['supplier_gst_id'],
                            'debit_note_amount' => $debit_amt_for_update['total_cost_price'] - $debit_amt_for_update['used_amount'],
                            'debit_note_used_amount' => $debit_amount['amount'],
                            'debit_note_balance_amount' => $debit_amt_for_update['total_cost_price'] - $debit_amount['amount'],
                            'created_by' => Auth::User()->user_id
                        ]);
                } else {
                    supplier_debit_payment_detail::where('inward_stock_id', $inward_stock_id)->update(array(
                        'deleted_by' => Auth::User()->user_id,
                        'deleted_at' => date('Y-m-d H:i:s')
                    ));
                    debit_note::where('company_id', $company_id)
                        ->where('debit_note_id', $data['debit_note_id'])
                        ->whereNull('deleted_at')
                        ->update(array(
                            'used_amount' => DB::raw('used_amount -' . $minus_debit),
                            'modified_by' => Auth::User()->user_id
                        ));
                }
                //end of to add record in supplier payment table
            }



            DB::commit();
        }
        catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }
        if($inward_stock_insert)
        {

            if($data['inward_stock_id'] != '')
            {
                return json_encode(array("Success"=>"True","Message"=>"Stock successfully Update!","url"=>"view_inward_stock"));
            }
            else
            {
                if($inward_stock[0]['po_no'] != '' && $inward_stock[0]['warehouse_id'] == '')
                {
                    $url = 'view_issue_po';
                }
                elseif($inward_stock[0]['po_no'] != '' && $inward_stock[0]['warehouse_id'] != '')
                {
                    $url = 'stock_transfer_inward';
                }
                else
                {
                    $url = '';
                }

                return json_encode(array("Success"=>"True","Message"=>"Stock successfully inward!","url"=>$url));
            }
        }
    }

    public function edit_inward_stock(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $inward_stock_id = decrypt($request->inward_stock_id);

        $inward_stock = inward_stock::where([
            ['inward_stock_id','=',$inward_stock_id],
            ['company_id',Auth::user()->company_id]])
            ->with('inward_product_detail.product_detail')
            ->with('supplier_payment_details.payment_method')
            //->with('price_masters')
            ->with('warehouse')
            ->with('supplier_gstdetail.supplier_company_info')
            ->select('*')
            ->first();
        $product_features =  ProductFeatures::getproduct_feature('');
       if(isset($inward_stock['po_no']) && $inward_stock['po_no'] != '' && $inward_stock['po_no'] != null && $inward_stock['stock_inward_type'] == 0)
       {
           $purchase_order_detail = purchase_order::where('company_id',Auth::user()->company_id)
               ->where('po_no',$inward_stock['po_no'])
               ->select('purchase_order_id','po_with_unique_barcode')
               ->whereNull('deleted_at')->first();

           if(isset($purchase_order_detail) && $purchase_order_detail != '')
           {
               foreach ($inward_stock['inward_product_detail'] AS $key => $val)
               {
                   $pending_po_qty = 0;

                   if(isset($purchase_order_detail['po_with_unique_barcode']) && $purchase_order_detail['po_with_unique_barcode'] == 1)
                   {
                       $purchase_order_product_detail = purchase_order_detail::where('company_id', Auth::user()->company_id)
                           ->where('purchase_order_id', $purchase_order_detail['purchase_order_id'])
                           ->where('product_id', '=', $val['product_id'])
                           ->where('unique_barcode', '=', $val['batch_no'])
                           ->whereNull('deleted_at')->first();
                   }
                   else{
                   $purchase_order_product_detail = purchase_order_detail::where('company_id', Auth::user()->company_id)
                       ->where('purchase_order_id', $purchase_order_detail['purchase_order_id'])
                       ->where('product_id', '=', $val['product_id'])
                       ->whereNull('deleted_at')->first();
                   }

                   if (isset($purchase_order_product_detail) && $purchase_order_product_detail!='')
                   {
                       $pending_po_qty = $purchase_order_product_detail['pending_qty'];
                   }

                   $inward_stock['inward_product_detail'][$key]['pending_po_qty'] = $pending_po_qty;
               }
           }
       }


        if(isset($inward_stock['inward_product_detail']) && $inward_stock['inward_product_detail'] != '')
        {
            foreach ($inward_stock['inward_product_detail'] AS $inw_k=>$inw_v)
            {
                foreach ($product_features AS $kk => $vv) {
                    $html_id = $vv['html_id'];

                    if ($inw_v['product_detail']['product_features_relationship'][$html_id] != '' && $inw_v['product_detail']['product_features_relationship'][$html_id] != NULL) {
                        $nm = product::feature_value($vv['product_features_id'], $inw_v['product_detail']['product_features_relationship'][$html_id]);
                        $inw_v['product_detail'][$html_id] = $nm;
                    }
                }
            }
        }




        if(isset($inward_stock['warehouse_id']) && $inward_stock['warehouse_id'] != '' && $inward_stock['warehouse_id'] != null && $inward_stock['stock_inward_type'] == 2)
        {
            $stock_transfer = stock_transfer::where('store_id',Auth::user()->company_id)
                ->where('company_id',$inward_stock['warehouse_id'])
                ->where('stock_transfer_no',$inward_stock['po_no'])
                ->select('stock_transfer_id')
                ->whereNull('deleted_at')->first();



            if(isset($stock_transfer) && $stock_transfer != '')
            {
                foreach ($inward_stock['inward_product_detail'] AS $key => $val)
                {
                    $pending_po_qty = 0;
                    $stock_transfer_product_detail = stock_transfer_detail::where('company_id', $inward_stock['warehouse_id'])
                        ->where('stock_transfer_id', $stock_transfer['stock_transfer_id'])
                        ->where('product_id', '=', $val['product_id'])
                        ->whereNull('deleted_at')->first();

                    if (isset($stock_transfer_product_detail) && $stock_transfer_product_detail!='')
                    {
                        $pending_po_qty = $stock_transfer_product_detail['pending_rcv_qty'];
                    }

                    $inward_stock['inward_product_detail'][$key]['pending_po_qty'] = $pending_po_qty;
                }
            }
        }

       $data = json_encode($inward_stock);

       if(isset($request->inward_type) && $request->inward_type == 1)
        {
            $url = 'inward_stock';
        }
        else
        {
            $url = 'inward_stock_show';
        }

        return json_encode(array("Success"=>"True","Data"=>$data,"url"=>$url));
    }

    public function batch_no_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = price_master::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->where('batch_no','LIKE', '%'.$request->search_val.'%')
            ->groupBy('batch_no')
            ->select('batch_no')
            ->get();

        foreach ($result AS $k=>$v)
        {
            if($v['batch_no'] != '')
            {
                $encrypted_batch = encrypt($v['batch_no']);
                $v->encrypt_batch_no = $encrypted_batch;
            }
        }

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function delete_inward_stock(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $inward_stock_id = decrypt($request->inward_stock_id);

        $inward_product_detail  = inward_product_detail::
            where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->where('inward_stock_id',$inward_stock_id)
            ->with('inward_stock')
            ->get();

        foreach ($inward_product_detail AS $key=>$value)
        {
            if($value['inward_product_detail_id'] != '')
            {
                try {
                    DB::beginTransaction();
                    supplier_payment_detail::where('inward_stock_id', $inward_stock_id)
                        ->where('company_id', Auth::user()->company_id)
                        ->where('deleted_at', '=', NULL)
                        ->update(array(
                            'deleted_by' => Auth::User()->user_id,
                            'deleted_at' => date('Y-m-d H:i:s')
                        ));

                    if($value['inward_stock']['inward_with_unique_barcode'] == 1)
                    {
                        price_master::
                        where('company_id', Auth::user()->company_id)
                            ->where('product_id', $value['product_id'])
                            ->where('batch_no', $value['batch_no'])
                            ->where('offer_price', $value['offer_price'])
                            ->where('deleted_at', '=', NULL)
                            ->update(array(
                                'modified_by' => Auth::User()->user_id,
                                'product_qty' => DB::raw('product_qty -' . $value['pending_return_qty'])
                            ));
                    }
                    else {
                        if ($value['inward_stock']['inward_type'] == 1) {
                            price_master::
                            where('company_id', Auth::user()->company_id)
                                ->where('product_id', $value['product_id'])
                                ->where('batch_no', $value['batch_no'])
                                ->where('offer_price', $value['offer_price'])
                                ->where('deleted_at', '=', NULL)
                                ->update(array(
                                    'modified_by' => Auth::User()->user_id,
                                    'product_qty' => DB::raw('product_qty -' . $value['pending_return_qty'])
                                ));
                        }


                        if ($value['inward_stock']['inward_type'] == 2) {
                            price_master::
                            where('company_id', Auth::user()->company_id)
                                ->where('product_id', $value['product_id'])
                                ->where('deleted_at', '=', NULL)
                                ->update(array(
                                    'modified_by' => Auth::User()->user_id,
                                    'product_qty' => DB::raw('product_qty -' . $value['pending_return_qty'])
                                ));
                        }
                    }

                    inward_product_detail::where('inward_product_detail_id', $value['inward_product_detail_id'])
                        ->update(array(
                            'deleted_by' => Auth::User()->user_id,
                            'deleted_at' => date('Y-m-d H:i:s')
                        ));

                    if($value['inward_stock']['po_no'] != '')
                    {
                            if($value['inward_stock']['stock_inward_type'] == 0) {
                                $purchase_id = purchase_order::where('po_no', $value['inward_stock']['po_no'])
                                    ->where('deleted_at', '=', NULL)
                                    ->where('company_id', Auth::user()->company_id)
                                    ->select('purchase_order_id')->first();

                                if (isset($purchase_id) && isset($purchase_id['purchase_order_id']) && $purchase_id['purchase_order_id'] != '') {
                                    purchase_order_detail::
                                    where('company_id', Auth::user()->company_id)
                                        ->where('product_id', $value['product_id'])
                                        ->where('deleted_at', '=', NULL)
                                        ->where('unique_barcode', '=', $value['batch_no'])
                                        ->update(array(
                                            'modified_by' => Auth::User()->user_id,
                                            'received_qty' => DB::raw('received_qty -' . $value['pending_return_qty']),
                                            'pending_qty' => DB::raw('pending_qty +' . $value['pending_return_qty'])
                                        ));
                                }
                            }

                        if($value['inward_stock']['stock_inward_type'] == 2) {

                            $stock_transfer_id = stock_transfer::where('stock_transfer_no', $value['inward_stock']['po_no'])
                                ->where('deleted_at', '=', NULL)
                                ->where('company_id',$value['inward_stock']['warehouse_id'])
                                ->where('store_id', Auth::user()->company_id)
                                ->select('stock_transfer_id')->first();


                            if (isset($stock_transfer_id) && isset($stock_transfer_id['stock_transfer_id']) && $stock_transfer_id['stock_transfer_id'] != '')
                            {
                                stock_transfer_detail::
                                    where('product_id', $value['product_id'])
                                    ->where('deleted_at', '=', NULL)
                                    ->where('batch_no', '=', $value['batch_no'])
                                    ->update(array(
                                        'modified_by' => Auth::User()->user_id,
                                        'pending_rcv_qty' => DB::raw('pending_rcv_qty +' . $value['pending_return_qty'])
                                    ));
                            }


                        }
                    }


                    DB::commit();
                } catch (\Illuminate\Database\QueryException $e)
                {
                    DB::rollback();
                    return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
                }

            }
        }
        $inward_stock = inward_stock::where('inward_stock_id',$inward_stock_id)
            ->update(array(
                'deleted_by' => Auth::User()->user_id,
                'deleted_at' => date('Y-m-d H:i:s')
            ));

        if($inward_stock)
        {
            return json_encode(array("Success"=>"True","Message"=>"Inward has been successfully deleted.!"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong!"));
        }
    }


    public function add_unique_inward_stock(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();
        $company_id = Auth::User()->company_id;

        $inward_stock = $data['inward_stock'];


        //check same gst and invoice no availble or not
        $same_gst_invoice = inward_stock::select('inward_stock_id')
            ->where('company_id',Auth::user()->company_id)
            ->where('supplier_gst_id', '=', $inward_stock[0]['supplier_gst_id'])
            ->where('invoice_no', '=', $inward_stock[0]['invoice_no'])
            ->where('deleted_at', '=', NULL)
            ->where('inward_stock_id','!=',$data['inward_stock_id'])
            ->get()
            ->first();
        //end of check same gst id and invoice no

        if(isset($same_gst_invoice) && $same_gst_invoice != '' && $same_gst_invoice['inward_stock_id'] != '')
        {
            //return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>"This invoice number already exist on this supplier.You Can edit this inward.you want to edit?!","edit_id"=>encrypt($same_gst_invoice['inward_stock_id'])));
            return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>"This invoice number already exist on this supplier.You can change invoice no or supplier!","edit_id"=>encrypt($same_gst_invoice['inward_stock_id'])));
            exit;
        }
        $inward_product_detail = $data['inward_product_detail'];
        $supplier_payment_detail = $data['supplier_payment_detail'];

        //check same batch number do not have multiple mrp and offer price.if confirm for update then update all value
        if($data['update_offer_price'] == 0)
        {
            foreach ($inward_product_detail AS $kk => $vv)
            {
                if (isset($vv['batch_no']) && $vv['batch_no'] != '')
                {
                    $same_batch_no = price_master::select('price_master_id', 'offer_price')
                        ->where('company_id', Auth::user()->company_id)
                        ->where('batch_no', '=', $vv['batch_no'])
                        ->where('product_id', '=', $vv['product_id'])
                        ->where('deleted_at', '=', NULL)
                        ->where('offer_price', '!=', $vv['offer_price'])
                        ->get()->first();
                    if (isset($same_batch_no) && $same_batch_no['price_master_id'] != '')
                    {
                        $barcode = product::select('supplier_barcode','product_system_barcode')->where('product_id',$vv['product_id'])->where('company_id',Auth::user()->company_id)->first();
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

                        return json_encode(array("Success" => "False", "status_code" => 410, "Message" => "Sorry,Barcode '" .$show_barcode. "',Batch No. '" . $vv['batch_no'] . "' already present with Rs.'" . $same_batch_no['offer_price'] . "' offer price.Are you sure want to change offer price! "));
                        exit;
                    }
                }
            }
        }
        //end of check same batch no have different mrp and offer price

        try
        {
            DB::beginTransaction();

            //add record to inward stock;
            $inward_stock_array = array();

            foreach($inward_stock[0] AS $inward_stock_key=>$inward_stock_value)
            {
                $inward_stock_array[$inward_stock_key] = $inward_stock_value;
            }
            $inward_stock_array['company_id'] = $company_id;
            $inward_stock_array['created_by'] =  Auth::User()->user_id;



            $inward_stock_insert  = inward_stock::updateOrCreate(
                ['inward_stock_id' => $data['inward_stock_id'], 'company_id'=>$company_id,],
                $inward_stock_array
            );

            //end of insert inward stock
            $inward_stock_id = $inward_stock_insert->inward_stock_id;
            $inward_product_detail_array = array();
            $price_master_array = array();

            //update all value of product detail set deleted_by  and deleted_at where inword_stock_id = $inward_stock_id
            inward_product_detail::where('inward_stock_id',$inward_stock_id)
                ->where('company_id', Auth::user()->company_id)
                ->update(array(
                    'deleted_by' => Auth::User()->user_id,
                    'deleted_at' => date('Y-m-d H:i:s')
                ));
            //end of update inward price master


            //add inward product detail and inward price master
            foreach($inward_product_detail AS $inward_product_detail_key=>$inward_product_detail_value)
            {
                $inward_product_detail_array['company_id'] = $company_id;
                $price_master_array['company_id'] = $company_id;
                $inward_product_detail_array['inward_stock_id'] = $inward_stock_id;
                //$price_master_array['inward_stock_id'] = $inward_stock_id;

                foreach ($inward_product_detail_value AS $key => $value)
                {
                    if ($key != 'gst_percent' && $key != 'gst_amount' && $key != 'pending_qty' && $key != 'po_pending_show')
                    {
                        $inward_product_detail_array[$key] = $value;
                    }
                    if ($key == 'batch_no' || $key == 'product_qty' || $key == 'free_qty' || $key == 'product_mrp' || $key == 'offer_price' || $key == 'wholesaler_price' || $key == 'product_id' || $key == 'sell_price' || $key == 'selling_gst_percent' || $key == 'selling_gst_amount') {
                        if ($key == 'free_qty')
                        {
                            $price_master_array['product_qty'] += $value;
                        }
                        else {
                            $price_master_array[$key] = $value;
                        }
                    }
                }


                $inward_product_detail_array['created_by'] =  Auth::User()->user_id;
                //if same inward_stock_id is present then update it and also set null deleted_at and deleted_by
                $inward_product_detail_array['deleted_at'] =  NULL;
                $inward_product_detail_array['deleted_by'] =  NULL;
                //end
                $price_master_array['created_by'] =  Auth::User()->user_id;
                $price_master_array['deleted_at'] =  NULL;
                $price_master_array['deleted_by'] =  NULL;



                //for update po qty
                if($inward_stock[0]['po_no'] != '')
                {
                    $purchase_order =  purchase_order::select('purchase_order_id')
                        ->where('company_id',Auth::user()->company_id)
                        ->where('po_no',$inward_stock[0]['po_no'])
                        ->whereNull('deleted_at')
                        ->first();

                    if(isset($purchase_order) && $purchase_order != '' )
                    {
                        $po_qty = purchase_order_detail::select('qty','received_qty','pending_qty','unique_barcode')
                            ->where('company_id',Auth::user()->company_id)
                            ->where('purchase_order_id',$purchase_order['purchase_order_id'])
                            ->where('product_id',$inward_product_detail_array['product_id'])
                            ->where('unique_barcode',$inward_product_detail_array['batch_no'])
                            ->first();

                        if(isset($po_qty) && $po_qty != '')
                        {
                            if($data['inward_stock_id'] == '')
                            {
                                $pending_qty = ($po_qty['pending_qty'] - $inward_product_detail_array['product_qty']);
                                $received_qty = ($po_qty['received_qty'] + $inward_product_detail_array['product_qty']);
                            }else
                            {
                                //   $received_qty = (($po_qty['received_qty'] - $po_qty['pending_qty']) + $inward_product_detail_array['product_qty']);
                                //  $pending_qty = ($po_qty['qty'] - $received_qty);

                                $pending_qty = $inward_product_detail_value['po_pending_show'];
                                $received_qty = $po_qty['qty'] - $pending_qty;
                            }

                            purchase_order_detail::
                            where('company_id',Auth::user()->company_id)
                                ->where('purchase_order_id',$purchase_order['purchase_order_id'])
                                ->where('product_id',$inward_product_detail_array['product_id'])
                                ->where('unique_barcode',$po_qty['unique_barcode'])
                                ->update([
                                    'received_qty' => $received_qty,
                                    'pending_qty' => $pending_qty,
                                    'free_qty' => $inward_product_detail_array['free_qty']
                                ]);
                        }
                    }
                }
                //end of update po qty

                if($inward_product_detail_array['inward_product_detail_id'] != '')
                {
                    $qtyp = inward_product_detail::select('product_qty','free_qty','inward_product_detail_id','product_mrp','sell_price','selling_gst_percent','selling_gst_amount','offer_price','batch_no')
                         ->where('batch_no', '=', $inward_product_detail_array['batch_no'])
                        ->where('product_id', '=', $inward_product_detail_array['product_id'])
                        ->where('inward_stock_id', '=',$data['inward_stock_id'])
                        ->where('inward_product_detail_id','=',$inward_product_detail_array['inward_product_detail_id'])
                        ->where('company_id', Auth::user()->company_id)->get();

                    if(isset($qtyp) && $qtyp != '' && !empty($qtyp) && isset($qtyp[0]))
                    {
                        $product_qty_minus  = (($qtyp[0]['product_qty'] + $qtyp[0]['free_qty'])) ;
                    }
                    else
                    {
                        $product_qty_minus = 0;
                    }


                    //minus qty from pending_rcv_qty if stock_transfer inward
                    if($inward_product_detail_array['stock_transfers_detail_id'] != '' && $inward_product_detail_array['stock_transfers_detail_id'] != 0 && $inward_product_detail_array['stock_transfers_detail_id'] != NULL)
                    {
                        $stock_rec_qty =  stock_transfer_detail::
                        where('stock_transfers_detail_id',$inward_product_detail_array['stock_transfers_detail_id'])
                            ->select('pending_rcv_qty')
                            ->with('stock_transfer_no')
                            ->whereHas('stock_transfer_no',function($q){
                                $q->where('store_id', Auth::user()->company_id);
                            })
                            ->first();

                        if(isset($stock_rec_qty) && $stock_rec_qty != '' && isset($stock_rec_qty['pending_rcv_qty']) && $stock_rec_qty['pending_rcv_qty'] != '')
                        {
                            $pending_rcv_qty = $stock_rec_qty['pending_rcv_qty'] + $product_qty_minus - ($inward_product_detail_array['product_qty'] + $inward_product_detail_array['free_qty']);

                            stock_transfer_detail::
                            where('stock_transfers_detail_id', $inward_product_detail_array['stock_transfers_detail_id'])
                                ->update(array(
                                    'modified_by' => Auth::User()->user_id,
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'pending_rcv_qty' => $pending_rcv_qty
                                ));
                        }

                    }
                    //end of minus qty from stock_transfer_details table

                    $qtys = price_master::select('product_qty','batch_no', 'price_master_id', 'product_mrp','offer_price')
                        ->where('batch_no', '=', $qtyp[0]['batch_no'])
                        ->where('offer_price', '=', $qtyp[0]['offer_price'])
                        ->where('product_id', '=', $inward_product_detail_array['product_id'])
                        ->where('company_id', Auth::user()->company_id)->get();


                    if (isset($qtys) && $qtys != '' && isset($qtys[0]['offer_price']))
                    {
                        $qty_with_minus = (($qtys[0]['product_qty']) - ($product_qty_minus));

                        $price_master_minus_qty = $price_master_array;
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
                        // $price_master_minus_qty['inward_stock_id'] = $qtys[0]['inward_stock_id'];
                        $price_master_id = '';

                        //$price_master_array['inward_stock_id'] = $qtys[0]['inward_stock_id'];


                        //minus qty from existing row
                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $inward_product_detail_value['product_id'],
                                'offer_price' => $qtyp[0]['offer_price'],
                                'batch_no' => $qtyp[0]['batch_no'],
                                'company_id' => $company_id,
                                'price_master_id' => $qtys[0]['price_master_id'],],
                            $price_master_minus_qty);




                        if($price_master_array['batch_no'] == '' )
                        {
                            if ($qtys[0]['offer_price'] == $inward_product_detail_value['offer_price'])
                            {
                                $price_master_id = $qtys[0]['price_master_id'];
                                $total_qty = (($qtys[0]['product_qty'] - $product_qty_minus) + ($inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty']));
                                $price_master_array['product_qty'] = $total_qty;
                            } else {

                                $qty_price = price_master::select('product_qty', 'price_master_id', 'offer_price')
                                    //->where('batch_no', '=', $price_master_array['batch_no'])
                                    ->where('offer_price', '=', $price_master_array['offer_price'])
                                    ->where('product_id', '=', $inward_product_detail_array['product_id'])
                                    ->where('company_id', Auth::user()->company_id)->get();

                                if (isset($qty_price) && $qty_price != '' && isset($qty_price[0]['offer_price']))
                                {
                                    $price_master_id = $qty_price[0]['price_master_id'];
                                    $total_qty = ($inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty'] + $qty_price[0]['product_qty']);
                                } else {
                                    $price_master_id = $qtys[0]['price_master_id'];
                                    $total_qty = ($inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty']);
                                }
                                $price_master_array['product_qty'] = $total_qty;
                            }
                        }else {

                            if($data['update_offer_price'] == 0)
                            {

                                $qtty = price_master::select('product_qty', 'batch_no', 'price_master_id', 'product_mrp', 'offer_price')
                                    ->where('batch_no', '=', $price_master_array['batch_no'])
                                    ->where('offer_price', '=', $qtyp[0]['offer_price'])
                                    ->where('product_id', '=', $inward_product_detail_array['product_id'])
                                    ->where('company_id', Auth::user()->company_id)->get();
                            }else
                            {


                                $qtty = price_master::select('product_qty', 'batch_no', 'price_master_id', 'product_mrp', 'offer_price')
                                    ->where('batch_no', '=', $price_master_array['batch_no'])
                                    // ->where('offer_price', '=', $qtyp[0]['offer_price'])
                                    ->where('product_id', '=', $inward_product_detail_array['product_id'])
                                    ->where('company_id', Auth::user()->company_id)->get();

                            }
                            if (isset($qtty) && $qtty != '' && isset($qtty[0]['offer_price']))
                            {

                                $price_master_id = $qtty[0]['price_master_id'];

                                $total_qty = ($qtty[0]['product_qty'] + $inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty']);


                            } else {

                                $price_master_id = '';
                                $total_qty = $price_master_array['product_qty'];
                            }

                            $price_master_array['product_qty'] = $total_qty;
                        }

                        if($data['update_offer_price'] == 0)
                        {
                            $price_master = price_master::updateOrCreate(
                                [
                                    'product_id' => $inward_product_detail_value['product_id'],
                                    'offer_price' => $inward_product_detail_value['offer_price'],
                                    'batch_no' => $inward_product_detail_value['batch_no'],
                                    'company_id' => $company_id,
                                    'price_master_id' => $price_master_id,
                                ],
                                $price_master_array);
                        }else
                        {
                            $price_master = price_master::updateOrCreate(
                                [
                                    'product_id' => $inward_product_detail_value['product_id'],
                                    // 'offer_price' => $inward_product_detail_value['offer_price'],
                                    'batch_no' => $inward_product_detail_value['batch_no'],
                                    'company_id' => $company_id,
                                    'price_master_id' => $price_master_id,
                                ],
                                $price_master_array);
                        }
                    }
                    else
                    {
                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $inward_product_detail_value['product_id'],
                                'offer_price' => $inward_product_detail_value['offer_price'],
                                'batch_no' => $inward_product_detail_value['offer_price'],
                                'company_id'=>$company_id,
                            ],
                            $price_master_array);
                    }

                    $inward_product_detail_array['pending_return_qty'] = (isset($inward_product_detail_value['pending_qty'])) ? $inward_product_detail_value['pending_qty'] : ($inward_product_detail_value['product_qty']+$inward_product_detail_value['free_qty']);

                    //if product have two offer price and user confirm to change offer price that case update that product offer price and related field to inward product detail.
                    if($data['update_offer_price'] == 1)
                    {
                        inward_product_detail::where('company_id',Auth::user()->company_id)
                            ->where('product_id',$inward_product_detail_array['product_id'])
                            ->where('batch_no',$inward_product_detail_value['offer_price'])
                            ->update([
                                'offer_price' => $inward_product_detail_value['offer_price'],
                                'product_mrp' => $inward_product_detail_value['product_mrp'],
                                'sell_price' => $inward_product_detail_array['sell_price'],
                                'selling_gst_percent' => $inward_product_detail_array['selling_gst_percent'],
                                'selling_gst_amount' => $inward_product_detail_array['selling_gst_amount'],
                                'profit_percent' => $inward_product_detail_array['profit_percent'],
                                'profit_amount' => $inward_product_detail_array['profit_amount'],
                                'expiry_date' => $inward_product_detail_array['expiry_date'],
                                'mfg_date' => $inward_product_detail_array['mfg_date'],
                            ]);
                    }
                    //end of update this product offer price in all inward

                    $inward_product_detail_insert = inward_product_detail::updateOrCreate(
                        ['inward_stock_id' => $data['inward_stock_id'],
                            'company_id' => $company_id,
                            'product_id' => $inward_product_detail_array['product_id'],
                            'inward_product_detail_id' => $inward_product_detail_array['inward_product_detail_id'],
                        ],
                        $inward_product_detail_array);

                }
                else
                {
                    //if product have two offer price and user confirm to change offer price that case update that product offer price and related field to inward product detail.
                    if($data['update_offer_price'] == 1){
                        inward_product_detail::where('company_id',Auth::user()->company_id)
                            ->where('product_id',$inward_product_detail_array['product_id'])
                            ->where('batch_no',$inward_product_detail_value['offer_price'])
                            ->update([
                                'offer_price' => $inward_product_detail_value['offer_price'],
                                'product_mrp' => $inward_product_detail_value['product_mrp'],
                                'sell_price' => $inward_product_detail_array['sell_price'],
                                'selling_gst_percent' => $inward_product_detail_array['selling_gst_percent'],
                                'selling_gst_amount' => $inward_product_detail_array['selling_gst_amount'],
                                'profit_percent' => $inward_product_detail_array['profit_percent'],
                                'profit_amount' => $inward_product_detail_array['profit_amount'],
                                'expiry_date' => $inward_product_detail_array['expiry_date'],
                                'mfg_date' => $inward_product_detail_array['mfg_date'],
                            ]);
                    }
                    //end of update offer price in inward product detail

                    //minus qty from pending_rcv_qty if stock_transfer inward
                    if($inward_product_detail_array['stock_transfers_detail_id'] != '' && $inward_product_detail_array['stock_transfers_detail_id'] != 0 && $inward_product_detail_array['stock_transfers_detail_id'] != NULL)
                    {
                        $stock_store_id = company_profile::where('company_id',Auth::user()->company_id)->select('company_profile_id')->first();

                        $stock_rec_qty =  stock_transfer_detail::
                        where('stock_transfers_detail_id',$inward_product_detail_array['stock_transfers_detail_id'])
                            ->select('pending_rcv_qty')
                            ->with('stock_transfer_no')
                            ->whereHas('stock_transfer_no',function($q) use($stock_store_id){
                                $q->where('store_id',$stock_store_id['company_profile_id']);
                            })
                            ->first();


                        if(isset($stock_rec_qty) && $stock_rec_qty != '' && isset($stock_rec_qty['pending_rcv_qty']) && $stock_rec_qty['pending_rcv_qty'] != '')
                        {
                            $pending_rcv_qty = $stock_rec_qty['pending_rcv_qty'] - ($inward_product_detail_array['product_qty'] + $inward_product_detail_array['free_qty']);

                            stock_transfer_detail::
                            where('stock_transfers_detail_id',$inward_product_detail_array['stock_transfers_detail_id'])
                                ->update([
                                    'modified_by' => Auth::User()->user_id,
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'pending_rcv_qty' => $pending_rcv_qty,
                                ]);
                        }
                    }
                    //end of minus qty from stock_transfer_details table


                    $inward_product_detail_array['pending_return_qty'] = $inward_product_detail_array['product_qty']+$inward_product_detail_array['free_qty'];
                    $inward_product_detail_insert = inward_product_detail::updateOrCreate(
                        ['inward_stock_id' => $data['inward_stock_id'],
                            'company_id' => $company_id,
                            'inward_product_detail_id'=>$inward_product_detail_array['inward_product_detail_id']
                        ],
                        $inward_product_detail_array);

                    if($data['update_offer_price'] == 0)
                    {
                        $qtys = price_master::select('product_qty', 'price_master_id', 'product_mrp','offer_price')
                            ->where('batch_no', '=', $inward_product_detail_array['batch_no'])
                            ->where('offer_price', '=', $inward_product_detail_array['offer_price'])
                            ->where('product_id', '=', $inward_product_detail_array['product_id'])
                            ->where('company_id', Auth::user()->company_id)->get();
                    }
                    else
                    {
                        $qtys = price_master::select('product_qty', 'price_master_id', 'product_mrp','offer_price')
                            ->where('batch_no', '=', $inward_product_detail_array['batch_no'])
                            //  ->where('offer_price', '=', $inward_product_detail_array['offer_price'])
                            ->where('product_id', '=', $inward_product_detail_array['product_id'])
                            ->where('company_id', Auth::user()->company_id)->get();
                    }

                    if (isset($qtys) && $qtys != '' && isset($qtys[0]['offer_price']))
                    {
                        //INCREMENT QUNTITY
                        $total_qty =  ($qtys[0]['product_qty'] + $inward_product_detail_value['product_qty'] + $inward_product_detail_value['free_qty']);
                        $price_master_array['product_qty'] = $total_qty;

                        if($data['update_offer_price'] == 0)
                        {
                            $price_master = price_master::updateOrCreate(
                                [
                                    'product_id' => $inward_product_detail_value['product_id'],
                                    'offer_price' => $inward_product_detail_value['offer_price'],
                                    'company_id' => $company_id,
                                    'price_master_id' => $qtys[0]['price_master_id'],
                                ],
                                $price_master_array);
                        }else
                        {
                            $price_master = price_master::updateOrCreate(
                                [
                                    'product_id' => $inward_product_detail_value['product_id'],
                                    'batch_no' => $inward_product_detail_array['batch_no'],
                                    //'offer_price' => $inward_product_detail_value['offer_price'],
                                    'company_id' => $company_id,
                                    'price_master_id' => $qtys[0]['price_master_id'],
                                ],
                                $price_master_array);
                        }
                    }
                    else
                    {

                        $price_master = price_master::updateOrCreate(
                            [
                                'product_id' => $inward_product_detail_value['product_id'],
                                'offer_price' => $inward_product_detail_value['offer_price'],
                                'batch_no' => $inward_product_detail_value['batch_no'],
                                'company_id'=>$company_id,
                            ],
                            $price_master_array);
                    }
                }
            }
            //end of insert product detail and price master

            //update max inward unique barcode in company profile
            if($data['inward_stock_id'] == '')
            {
                $max_inward_unique = '';
                if($inward_stock[0]['inward_with_unique_barcode'] == 1)
                {
                    $max_inward_unique =   max(array_column($inward_product_detail, 'batch_no'));
                    company_profile::where('company_id',$company_id)
                        ->update(array(
                            'inward_unique_batch_no_value' => $max_inward_unique
                        ));
                }
            }

            if($inward_stock_array['stock_inward_type'] ==0) {
                //set deleted_by and deleted_at in all entry where inward_stock_id match
                $supplier_previous_debit = supplier_payment_detail::where('inward_stock_id', $inward_stock_id)
                    ->where('company_id', $company_id)
                    ->whereNull('deleted_at')
                    ->where('payment_method_id', 9)->first();

                supplier_payment_detail::where('inward_stock_id', $inward_stock_id)->update(array(
                    'deleted_by' => Auth::User()->user_id,
                    'deleted_at' => date('Y-m-d H:i:s')
                ));
                //end of update

                //add record in  supplier payment table
                $payment_detail_array = array();
                $debit_amount = array();
                foreach ($supplier_payment_detail AS $payment_key => $payment_value) {
                    if ($payment_value['payment_method_id'] == 9) {
                        $debit_amount['amount'] = $payment_value['amount'];
                    }

                    $supplier_payment_detail_id = '';
                    foreach ($payment_value AS $key => $value) {
                        if ($key == 'supplier_payment_detail_id') {
                            $supplier_payment_detail_id = $value;
                        } else {
                            $payment_detail_array[$key] = $value;
                        }
                    }
                    $payment_detail_array['created_by'] = Auth::User()->user_id;
                    $payment_detail_array['inward_stock_id'] = $inward_stock_id;
                    $payment_detail_array['company_id'] = $company_id;
                    $payment_detail_array['deleted_at'] = NULL;
                    $payment_detail_array['deleted_by'] = NULL;

                    $supplier_payment = supplier_payment_detail::updateOrCreate(
                        ['inward_stock_id' => $inward_stock_id,
                            'company_id' => $company_id,
                            'supplier_payment_detail_id' => $supplier_payment_detail_id,],
                        $payment_detail_array);
                }
                $minus_debit = (isset($supplier_previous_debit) && $supplier_previous_debit['amount'] != '' ? $supplier_previous_debit['amount'] : 0);
                if (isset($debit_amount) && isset($debit_amount['amount']) && $debit_amount['amount'] != '') {
                    $debit_amt_for_update = debit_note::where('company_id', $company_id)
                        ->where('debit_note_id', $data['debit_note_id'])
                        ->whereNull('deleted_at')
                        ->select('total_cost_price', 'used_amount')->first();

                    debit_note::where('company_id', $company_id)
                        ->where('debit_note_id', $data['debit_note_id'])
                        ->whereNull('deleted_at')
                        ->update(array(
                            'used_amount' => (($debit_amt_for_update['used_amount'] - $minus_debit) + $debit_amount['amount']),
                            'modified_by' => Auth::User()->user_id
                        ));

                    supplier_debit_payment_detail::updateOrCreate(
                        ['supplier_debit_payment_detail_id' => '',
                            'company_id' => $company_id,
                            'debit_note_id' => $data['debit_note_id'],
                        ],
                        [
                            'inward_stock_id' => $inward_stock_id,
                            'supplier_gst_id' => $inward_stock[0]['supplier_gst_id'],
                            'debit_note_amount' => $debit_amt_for_update['total_cost_price'] - $debit_amt_for_update['used_amount'],
                            'debit_note_used_amount' => $debit_amount['amount'],
                            'debit_note_balance_amount' => $debit_amt_for_update['total_cost_price'] - $debit_amount['amount'],
                            'created_by' => Auth::User()->user_id
                        ]);
                } else {
                    supplier_debit_payment_detail::where('inward_stock_id', $inward_stock_id)->update(array(
                        'deleted_by' => Auth::User()->user_id,
                        'deleted_at' => date('Y-m-d H:i:s')
                    ));
                    debit_note::where('company_id', $company_id)
                        ->where('debit_note_id', $data['debit_note_id'])
                        ->whereNull('deleted_at')
                        ->update(array(
                            'used_amount' => DB::raw('used_amount -' . $minus_debit),
                            'modified_by' => Auth::User()->user_id
                        ));


                }
                //end of to add record in supplier payment table
            }

            DB::commit();
        }
        catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }

        if($inward_stock_insert)
        {
            if($data['inward_stock_id'] != '')
            {
                return json_encode(array("Success"=>"True","Message"=>"Stock successfully Update!","url"=>"view_inward_stock"));
            }
            else
            {
                if($inward_stock[0]['po_no'] != '')
                {
                    $url = 'view_issue_po';
                }
                else
                {
                    $url = '';
                }
                return json_encode(array("Success"=>"True","Message"=>"Stock successfully inward!","url"=>$url));
            }

        }
    }
}


