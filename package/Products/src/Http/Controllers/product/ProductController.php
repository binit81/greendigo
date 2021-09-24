<?php

namespace Retailcore\Products\Http\Controllers\product;
use App\Http\Controllers\Controller;
use Retailcore\Consignment\Models\consign_bill;
use Retailcore\Consignment\Models\consign_products_detail;
use Retailcore\Products\Models\product\product_summary;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\DamageProducts\Models\damageproducts\damage_product_detail;
use Retailcore\Debit_Note\Models\debit_note\debit_product_detail;
use Retailcore\PO\Models\purchase_order\purchase_order_detail;
use Retailcore\Products\Models\product\price_master;

use Retailcore\Products\Models\product\brand;
use Retailcore\Products\Models\product\colour;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\product_features_data;
use Retailcore\Products\Models\product\product_image;
use Retailcore\Products\Models\product\category;
use Retailcore\Products\Models\product\product_export;
use Retailcore\Products\Models\product\product_update_export;
use Retailcore\Products\Models\product\size;
use Retailcore\Products\Models\product\subcategory;
use Retailcore\Products\Models\product\uqc;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Products\Models\product\product_features_relationship;
use Retailcore\Products\Models\product\product_template;
use Faker\Provider\Barcode;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\SalesReturn\Models\returnbill_product;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use function foo\func;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Auth;
use phpDocumentor\Reflection\Types\Null_;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

use Carbon\Carbon;
use Prophecy\Prophecy\RevealerInterface;
use DB;
//ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('max_execution_time', 1000000000000000);

use Log;
class ProductController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $product = product::select('*')
            ->where('deleted_at','=',NULL)
            ->orderBy('product_id', 'DESC')
            ->whereIn('item_type',array(1,3))
            ->with('product_image_')
            ->with('product_features_relationship')
            ->with('product_price_master')
            ->whereHas('product_price_master',function ($q)
            {
                $q->where('company_id',Auth::user()->company_id);
               })
            ->paginate(10);

        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($product AS $key=>$value)
        {
            $inward_product_detail = price_master::where('product_id',$value->product_id)
                ->whereNull('deleted_at')
                ->where('company_id',Auth::user()->company_id)
                ->where('product_qty','>',0)
                ->count();

            $product[$key]['delete_option'] = 1;

            $po_product = purchase_order_detail::where('product_id',$value->product_id)
                ->whereNull('deleted_at')
                ->where('company_id',Auth::user()->company_id)
                ->count();

            if($inward_product_detail > 0 || $po_product > 0)
            {
                $product[$key]['delete_option'] = 0;
            }

            if(isset($value['product_features_relationship']) && $value['product_features_relationship'] != '')
            {
                foreach ($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if($value['product_features_relationship'][$html_id] != '' && $value['product_features_relationship'][$html_id] != NULL)
                    {
                        $nm =  product::feature_value($vv['product_features_id'],$value['product_features_relationship'][$html_id]);
                        $product[$key][$html_id] =$nm;
                    }
                }
            }
        }

        $system_barcode = str_pad(Auth::user()->company_id,10,"0");

        $product_max_id = product::withTrashed()->where('company_id',Auth::user()->company_id) ->get()->max('product_system_barcode');

        if($product_max_id != '')
        {
            $product_max_id = $product_max_id -$system_barcode;
        }
        else{
            $product_max_id  = 0;
        }

        $product_max_id++;

        $system_barcode_final = $system_barcode + $product_max_id ;

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type')->first();

        $inward_type = $inward_type_from_comp['inward_type'];

        return view('products::product/product_show',compact('product','system_barcode_final','inward_type'));
    }

    public function get_productImages(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

        $result =   product_image::select('caption','product_image','product_id')->where('product_id',$data['product_id'])->whereNull('deleted_at')
        ->with('products')->orderBy('product_image_id','DESC')->get();

        return json_encode(array("Data"=>$result,"public_path"=>$request->root()));

    }

    public function service_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $service = product::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->orderBy('product_id', 'DESC')
            ->where('item_type','=','2')
            ->paginate(10);

            return view('product.service_data',compact('service'));
        }
    }

    public function product_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $product = product::
                where('deleted_at','=',NULL)
                ->whereIn('item_type',array(1,3))
                ->with('product_price_master')
                ->whereHas('product_price_master',function ($q)
                {
                    $q->where('company_id',Auth::user()->company_id);
                })
                ->orderBy('product_id', 'DESC')
                ->with('product_features_relationship')
                ->paginate(10);

            $product_features =  ProductFeatures::getproduct_feature('');

            foreach ($product AS $key=>$value)
            {
                $inward_product_detail = price_master::where('product_id',$value->product_id)
                    ->whereNull('deleted_at')
                    ->where('company_id',Auth::user()->company_id)
                    ->where('product_qty','>',0)
                    ->count();

                $product[$key]['delete_option'] = 1;

                $po_product = purchase_order_detail::where('product_id',$value->product_id)
                    ->whereNull('deleted_at')
                    ->where('company_id',Auth::user()->company_id)
                    ->count();


                if($inward_product_detail > 0 || $po_product > 0)
                {
                    $product[$key]['delete_option'] = 0;
                }


                if(isset($value['product_features_relationship']) && $value['product_features_relationship'] != '')
                {
                    foreach ($product_features AS $kk => $vv)
                    {
                        $html_id = $vv['html_id'];

                        if($value['product_features_relationship'][$html_id] != '' && $value['product_features_relationship'][$html_id] != NULL)
                        {
                            $nm =  product::feature_value($vv['product_features_id'],$value['product_features_relationship'][$html_id]);
                            $product[$key][$html_id] =$nm;
                        }
                    }
                }
            }

            $system_barcode = str_pad(Auth::user()->company_id,10,"0");

            $product_max_id = product::withTrashed()->where('company_id',Auth::user()->company_id)->get()->max('product_system_barcode');

            if($product_max_id != '')
            {
                $product_max_id = $product_max_id -$system_barcode;
            }
            else{
                $product_max_id  = 0;
            }

            $product_max_id++;

            $system_barcode_final = $system_barcode + $product_max_id ;

            $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type')->first();

            $inward_type = $inward_type_from_comp['inward_type'];


            return view('products::product/product_data',compact('product','system_barcode_final','inward_type','product_features'));
        }
    }


    function room_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $data = $request->all();
            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = $data['query'];

            $query = str_replace(" ", "%", $query);
            $service = product::where('product_id', 'like', '%'.$query.'%')
                ->orWhere('supplier_barcode', 'like', '%'.$query.'%')
                ->orWhere('selling_price', 'like', '%'.$query.'%')
                ->orWhere('sell_gst_percent', 'like', '%'.$query.'%')
                ->orWhere('sell_gst_amount', 'like', '%'.$query.'%')
                ->orWhere('product_mrp', 'like', '%'.$query.'%')
                ->orWhere('hsn_sac_code', 'like', '%'.$query.'%')
                ->where('item_type','=','2')
                ->where('supplier_barcode','!=','')
                ->where('deleted_at','=',NULL)
                ->orderBy($sort_by, $sort_type)
                ->paginate(10);

            return view('product.service_data', compact('service'))->render();
        }
    }


    function product_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $data = $request->all();

            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = isset($data['query']) ? $data['query']  : '';

            $dynamic_search = array();
            if($query != '')
            {
                foreach ($query as $key => $value)
                {
                    if (strpos($key,'dynamic_') === 0)
                    {
                        if($value != '')
                        {
                            $dynamic_search[$key] = $value;
                            unset($query[$key]);
                        }
                        else
                        {
                            unset($query[$key]);
                        }
                    }
                }
            }

            //$query = str_replace(" ", "", $query);
            //$query = str_replace(" ", "%", $query);


            $product = product::where('deleted_at','=',NULL)
                ->whereIn('item_type',array(1,3));

            if(isset($query) && $query != '' && $query['product_name'] != '')
            {
                $product->where('product_name', 'like', '%'.$query['product_name'].'%');
            }
            if(isset($query) && $query != '' && $query['barcode'] != '')
            {
                $product->where('product_system_barcode', 'like', '%'.$query['barcode'].'%');
                $product->orWhere('supplier_barcode', 'like', '%'.$query['barcode'].'%');
            }

            if(isset($query) && $query != '' && ($query['product_code'] != '' || $query['product_code'] != 0))
             {
                 $product->where('product_code', '=', $query['product_code']);
             }
            if(isset($query) && $query != '' && $query['sku_code'] != '' )
            {
                 $product->where('sku_code', '=', $query['sku_code']);
             }


            if(isset($query) && $query != '' && $query['uqc_id'] != '' && $query['uqc_id'] != 0)
            {
                $product->where('uqc_id', '=', $query['uqc_id']);
            }





            if(isset($dynamic_search) && $dynamic_search !='' &&  !empty($dynamic_search))
            {

                $product =  $product->with('product_features_relationship')
                    ->whereHas('product_features_relationship',function ($q) use($dynamic_search)
                    {
                     foreach($dynamic_search AS $k=>$v)
                      {
                          $q->where(DB::raw($k),$v);
                      }
                    });
            }


           $product = $product->with('product_price_master')
                                ->whereHas('product_price_master',function ($q) {
                                    $q->where('company_id',Auth::user()->company_id);
                                   })
                                ->orderBy($sort_by,$sort_type)->paginate(10);

            $product_features =  ProductFeatures::getproduct_feature('');


            foreach ($product AS $key=>$value)
            {
                $inward_product_detail = price_master::where('product_id',$value->product_id)
                    ->whereNull('deleted_at')
                    ->where('company_id',Auth::user()->company_id)
                    ->where('product_qty','>',0)
                    ->count();
                $product[$key]['delete_option'] = 1;

                $po_product = purchase_order_detail::where('product_id',$value->product_id)
                    ->whereNull('deleted_at')
                    ->where('company_id',Auth::user()->company_id)
                    ->count();


                if($inward_product_detail > 0 || $po_product > 0)
                {
                    $product[$key]['delete_option'] = 0;
                }

                if(isset($value['product_features_relationship']) && $value['product_features_relationship'] != '')
                {
                    foreach ($product_features AS $kk => $vv)
                    {
                        $html_id = $vv['html_id'];

                        if($value['product_features_relationship'][$html_id] != '' && $value['product_features_relationship'][$html_id] != NULL)
                        {
                            $nm =  product::feature_value($vv['product_features_id'],$value['product_features_relationship'][$html_id]);
                            $product[$key][$html_id] =$nm;
                        }
                    }
                }
            }

            $system_barcode = str_pad(Auth::user()->company_id,10,"0");
            $product_max_id = product::withTrashed()->where('company_id',Auth::user()->company_id)->get()->max('product_system_barcode');


            if($product_max_id != '')
            {
                $product_max_id = $product_max_id -$system_barcode;
            }
            else{
                $product_max_id  = 0;
            }

            $product_max_id++;
            $system_barcode_final = $system_barcode + $product_max_id ;

            $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type')->first();

            $inward_type = $inward_type_from_comp['inward_type'];




            return view('products::product/product_data', compact('product','system_barcode_final','inward_type','product_features'))->render();
        }
    }


    public function product_create(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();
//

       $dynamic_array = array();

       foreach ($data as $key => $value)
        {

        if (strpos($key, 'dynamic_') === 0 )
        {
            $dynamic_array[$key] = $value;
            unset($data[$key]);
        }
         if (strpos($key, 'test_dynamic_') === 0 && $value != '' )
        {

            unset($data[$key]);
        }
        }


       if ($data['type'] == 1 || $data['type'] == 3)
       {
            $validate_error = \Validator::make($data,
                [
                    //'product_name' => ['required', Rule::unique('products')->ignore($productdata['product_id'], 'product_id')->whereNull('deleted_at')],
                    'supplier_barcode' => [Rule::unique('products')->ignore($data['product_id'],'product_id')->whereNull('deleted_at')->whereNotNull('supplier_barcode')],
                ]);
        } else {
            $validate_error = \Validator::make($data,
                [
                    'supplier_barcode' => ['required', Rule::unique('products')->ignore($data['product_id'], 'product_id')->whereNull('deleted_at')],
                ]);
        }

        if ($validate_error->fails()) {
            return json_encode(array("Success" => "False", "status_code" => 409, "Message" => $validate_error->messages()));
            exit;
        }

        $same_supplier_system_barcode = product::where('product_system_barcode', '=', $data['supplier_barcode'])->whereNull('deleted_at')->count();

        if (isset($same_supplier_system_barcode) && $same_supplier_system_barcode > 0) {
            return json_encode(array("Success" => "False", "Message" => "This supplier barcode already exist in product system barcode!"));
            exit;
        }

        $same_system_supplier_barcode = product::where('supplier_barcode', '=', $data['product_system_barcode'])->whereNull('deleted_at')->count();

        if (isset($same_system_supplier_barcode) && $same_system_supplier_barcode > 0) {
            return json_encode(array("Success" => "False", "Message" => "This product system barcode already exist in supplier barcode!"));
            exit;
        }

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $created_by = $userId;

        $inward_type_from_comp = company_profile::where('company_id', Auth::user()->company_id)->select('inward_type')->first();

        $inward_type = 1;
        if (isset($inward_type_from_comp) && !empty($inward_type_from_comp) && $inward_type_from_comp['inward_type'] != '') {
            $inward_type = $inward_type_from_comp['inward_type'];
        }
        $system_barcode_final = '';
        if ($data['product_id'] == '')
        {
            $system_barcode = str_pad(Auth::user()->company_id, 10, "0");

            $product_max_id = product::withTrashed()
                            ->where('company_id', Auth::user()->company_id)
                            ->get()->max('product_system_barcode');

            if($product_max_id != '')
            {
                $product_max_id = $product_max_id -$system_barcode;
            }
            else{
                $product_max_id  = 0;
            }
            $product_max_id++;

            $system_barcode_final = $system_barcode + $product_max_id;
        }else
        {
            $edit_system_barcode = product::
                where('company_id', Auth::user()->company_id)
                ->where('product_id',$data['product_id'])
                ->whereNull('deleted_at')
                ->select('product_system_barcode')
                ->first();

            $system_barcode_final = $edit_system_barcode['product_system_barcode'];
        }

        try {
            DB::beginTransaction();
            $product = product::updateOrCreate(
                ['product_id' => $data['product_id'], 'company_id' => $company_id,],
                [
                    'created_by' => $created_by,
                    'company_id' => $company_id,
                    'product_type' => $inward_type,
                    'item_type' => $data['type'],
                    'product_name' => (isset($data['product_name']) ? $data['product_name'] : ''),
                    'note' => (isset($data['product_note']) ? $data['product_note'] : ''),
                   // 'brand_id' => (isset($data['brand_id']) && $data['brand_id'] != '0' ? $data['brand_id'] : NULL),
                   // 'category_id' => (isset($data['category_id']) && $data['category_id'] != '0' ? $data['category_id'] : NULL),
                   // 'subcategory_id' => (isset($data['subcategory_id']) && $data['subcategory_id'] != '0' ? $data['subcategory_id'] : NULL),
                  //  'colour_id' => (isset($data['colour_id']) && $data['colour_id'] != '0' ? $data['colour_id'] : NULL),
                  //  'size_id' => (isset($data['size_id']) && $data['size_id'] != '0' ? $data['size_id'] : NULL),
                    'uqc_id' => (isset($data['uqc_id']) && $data['uqc_id'] != '0' ? $data['uqc_id'] : NULL),
                    'cost_rate' => (isset($data['cost_rate']) ? $data['cost_rate'] : '0'),
                    'cost_price' => (isset($data['cost_price']) ? $data['cost_price'] : '0'),
                    'selling_price' => (isset($data['selling_price']) ? $data['selling_price'] : '0'),
                    'offer_price' => (isset($data['offer_price']) ? $data['offer_price'] : '0'),
                    'product_mrp' => (isset($data['product_mrp']) ? $data['product_mrp'] : '0'),
                    'wholesale_price' => (isset($data['wholesale_price']) ? $data['wholesale_price'] : '0'),
                    'cost_gst_percent' => (isset($data['cost_gst_percent']) ? $data['cost_gst_percent'] : '0'),
                    'cost_gst_amount' => (isset($data['cost_gst_amount']) ? $data['cost_gst_amount'] : '0'),
                    'extra_charge' => (isset($data['extra_charge']) ? $data['extra_charge'] : '0'),
                    'profit_percent' => (isset($data['profit_percent']) ? $data['profit_percent'] : '0'),
                    'profit_amount' => (isset($data['profit_amount']) ? $data['profit_amount'] : '0'),
                    'sell_gst_percent' => (isset($data['sell_gst_percent']) ? $data['sell_gst_percent'] : '0'),
                    'sell_gst_amount' => (isset($data['sell_gst_amount']) ? $data['sell_gst_amount'] : '0'),
                    //'product_system_barcode' => (isset($data['product_system_barcode']) ? $data['product_system_barcode'] : ''),
                    'product_system_barcode' => (isset($system_barcode_final) && $system_barcode_final != '' ? $system_barcode_final : $data['product_system_barcode']),
                    'supplier_barcode' => (isset($data['supplier_barcode']) && $data['supplier_barcode'] != " " && $data['supplier_barcode'] != '' ? $data['supplier_barcode'] : NULL),
                    'is_ean' => (isset($data['is_ean']) ? $data['is_ean'] : '0'),
                    'alert_product_qty' => (isset($data['alert_product_qty']) ? $data['alert_product_qty'] : '0'),
                    'product_ean_barcode' => (isset($data['product_ean_barcode']) ? $data['product_ean_barcode'] : '0'),
                    // 'minimum_qty' => (isset($data['minimum_qty'])?$data['minimum_qty']):''),
                    'sku_code' => (isset($data['sku_code']) ? $data['sku_code'] : ''),
                    'product_code' => (isset($data['product_code']) ? $data['product_code'] : ''),
                    'product_description' => (isset($data['product_description']) ? $data['product_description'] : ''),
                    'hsn_sac_code' => (isset($data['hsn_sac_code']) && !empty($data['hsn_sac_code']) ? $data['hsn_sac_code'] : NULL),
                    'days_before_product_expiry' => (isset($data['days_before_product_expiry']) && $data['days_before_product_expiry'] != '' ? $data['days_before_product_expiry'] : 0),
                    'default_qty' => (isset($data['default_qty']) && $data['default_qty'] != '' ? $data['default_qty'] : 1),
                    'is_active' => "1"
                ]
            );


            if ($product)
             {
                 $product_id = $product->product_id;
                 if($data['product_id'] == '')
                 {
                     DB::table('price_masters')->insert(array(
                     'company_id' => Auth::user()->company_id,
                     'product_id' => $product_id,
                     'product_qty' => '0',
                     'product_mrp' => (isset($data['product_mrp']) ? $data['product_mrp'] : '0'),
                     'offer_price' => (isset($data['offer_price']) ? $data['offer_price'] : '0'),
                     'is_active' => '1',
                     'created_by' => Auth::User()->user_id,
                     'created_at' => date('Y-m-d H:i:s')
                 ));
                 }
                 //update all value of product features relationship set deleted_by  and deleted_at where product_id=$product_id
                    product_features_relationship::where('product_id',$product_id)
                    ->where('company_id', Auth::user()->company_id)
                    ->update(array(
                    'deleted_by' => Auth::User()->user_id,
                    'deleted_at' => date('Y-m-d H:i:s')
                    ));
                    //end of update product features

                    //dynamic product features insert


                 $dynamic_array['created_by'] = $created_by;
                 $dynamic_array['company_id'] = $company_id;
                 $dynamic_array['product_id'] = $product_id;
                 $dynamic_array['deleted_at'] =  NULL;
                 $dynamic_array['deleted_by'] =  NULL;

                 if(isset($dynamic_array) && !empty($dynamic_array))
                 {
                     $relationship_product_insert  = product_features_relationship::updateOrCreate(
                        ['product_id' => $product_id,
                         'company_id'=>$company_id,
                        ],
                         $dynamic_array
                        );
                    }
                }

                if ($request->file('product_image'))
                {
                    foreach ($request->file('product_image') as $key => $image) {
                        $image_name = str_replace(' ', '_', $data['product_name']) . '_' . rand() . '.' . $image->getClientOriginalExtension();
                        $image->move(public_path(PRODUCT_IMAGE_URL_CONTROLLER), $image_name);

                        $product_images = product_image::updateOrCreate(
                            [
                                'product_id' => $product_id,
                                'caption' => $data['imageCaption'][$key],
                                'product_image' => $image_name,
                                'company_id' => $company_id,
                                'is_active' => '1',
                                'created_by' => $created_by,
                            ]
                        );
                    }
                }

            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return json_encode(array("Success" => "False", "Message" => $e->getMessage()));
        }
        catch (Exception $e)
        {
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }
        if ($data['product_id'] != '')
        {
            return json_encode(array("Success" => "True", "Message" => "Product has been successfully updated."));
        }
        else
        {
            return json_encode(array("Success" => "True", "Message" => "Product has been successfully added."));
        }
    }


    public function product_edit(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $product_id = decrypt($request->product_id);
        $productdata = product::select('products.*')
        ->where([['products.product_id','=',$product_id],['company_id',Auth::user()->company_id]])->with('product_images')
         ->with('product_features_relationship.product_features_data.product_features')->first();

        $product_features =  ProductFeatures::getproduct_feature('');


        return json_encode(array("Success"=>"True","Data"=>$productdata,"Product"=>$product_features,"public_path"=>$request->root()));

    }

    public function ProductremovePicture(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data               =   $request->all();
        $product_image_id   =   $data['product_image_id'];

        $result         =   product_image::select('product_image')->where('product_image_id',$product_image_id)->get();
        $image_path     =   public_path(PRODUCT_IMAGE_URL_CONTROLLER).'/'.$result[0]->product_image;

        @unlink($image_path);   // REMOVE IMAGE FROM FOLDER

        $query  =   product_image::where('product_image_id', $product_image_id)
        ->update([
           'deleted_by' => Auth::User()->user_id,
           'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        return json_encode(array("Message"=>"product picture removed successfully","picture"=>'empty'));
    }

    public function product_delete(request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $userId = Auth::User()->user_id;

        $existing_product = price_master::whereIn('product_id',$request->deleted_id)->whereNull('deleted_at')->where('company_id',Auth::User()->company_id)->where('product_qty','>',0)->count();

        if(isset($existing_product) && $existing_product > 0)
        {
            return json_encode(array("Success"=>"False","Message"=>"Unable to delete product because it's in use"));
        }
        else {

            try {
                DB::beginTransaction();

                $product_delete = product::whereIn('product_id', $request->deleted_id)->update([
                    'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);

                //delete for product features
                product_features_relationship::whereIn('product_id', $request->deleted_id)
                ->where('company_id', Auth::user()->company_id)
                ->update(array(
                    'deleted_by' => Auth::User()->user_id,
                    'deleted_at' => date('Y-m-d H:i:s')
                ));


                DB::commit();
            }catch (\Illuminate\Database\QueryException $e)
            {
                DB::rollback();
                return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
            }

            if ($product_delete) {
                return json_encode(array("Success" => "True", "Message" => "Product has been successfully deleted.!"));
            } else {
                return json_encode(array("Success" => "False", "Message" => "Something Went Wrong!"));
            }
        }
    }

    public function product_check(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $created_by = $userId;

        $exist = array();
        $record = [];

        $count = 0;
        if($data != '' && !empty($data))
        {
            $tax_info = company_profile::select('tax_type','tax_title')->where('company_id',Auth::user()->company_id)->get()->first();
            if(isset($data['confirm']) && $data['confirm'] == 1)
            {
                foreach ($data['formdata'] AS $key=>$value)
                {
                    if($key % 100 == 0)
                    {
                        sleep(1);
                    }


                    /*if($value['Barcode'] == '')
                    {*/
                        if($value['Barcode'] != '')
                        {
                            $barcode = $value['Barcode'];
                            $product = product::select('product_id','product_system_barcode')
                                ->where('supplier_barcode','=',$barcode)
                                ->orWhere('product_system_barcode','=',$barcode)
                                ->withTrashed()->first();

                            if(isset($product) && $product['product_id'] != '')
                            {
                                product::where('product_id',$product['product_id'])
                                    ->where('company_id', Auth::user()->company_id)
                                    ->withTrashed()
                                    ->update(array(
                                        'deleted_at' => NULL,
                                        'deleted_by' => NULL
                                    ));

                                $product_id = $product['product_id'];
                                $data['formdata'][$key]['product_id'] = $product->product_id;
                                $product_system_barcode = $product['product_system_barcode'];
                                continue;
                                $key++;
                            }
                            else
                            {
                                $product_id = '';
                                $product_system_barcode = $system_barcode_final;
                            }
                        }
                        else
                        {
                            $product_id = '';
                            $product_system_barcode = $system_barcode_final;
                        }

                        if($product_id == '')
                        {
                            $cost_tax_label = 'Cost gst %';
                            $sell_tax_label = 'Sell gst %';
                            if($tax_info['tax_type'] == 1)
                            {
                                $cost_tax_label = 'Cost '.$tax_info['tax_title']. ' %';
                                $sell_tax_label = 'Sell '.$tax_info['tax_title']. ' %';
                            }

                            $cost_gst = number_format(($value['Base price/cost rate'] * $value[$cost_tax_label]) / (100),4);
                            $cost_gst_amt = str_replace(',', '', $cost_gst);

                            if($value['Offer price'] == '')
                            {
                                if($value['Profit %'] != '' && $value['Selling price'] == '')
                                {
                                    $profitamount = number_format($value['Base price/cost rate'] * $value['Profit %'] / (100));

                                    $value['Offer price'] = number_format($value['Base price/cost rate'] + ($profitamount) + ($value['Extra charge']));
                                }
                                if($value['Profit %'] == '' && $value['Selling price'] != '')
                                {

                                    $value['Offer price'] = $value['Selling price'];
                                }
                                if($value['Profit %'] != '' && $value['Selling price'] != '')
                                {
                                    $value['Offer price'] = $value['Selling price'];
                                }
                            }


                            $sell_gst = number_format(($value['Offer price'] * $value[$sell_tax_label]) /(100 + $value[$sell_tax_label]),4);
                            $sell_gst_amt = str_replace(',', '', $sell_gst);

                            $selling_prc = number_format($value['Offer price'] - ($sell_gst_amt),4);
                            $selling_price = str_replace(',', '', $selling_prc);

                            $cost_prc = number_format($value['Base price/cost rate'] + $cost_gst_amt,4);
                            $cost_price = str_replace(',', '', $cost_prc);

                            $profit_amt = number_format($selling_price - $value['Base price/cost rate'],4);
                            $profit_amount = str_replace(',', '', $profit_amt);

                            $profit_per = number_format(($profit_amount * (100)) /($value['Base price/cost rate']),4);
                            $profit_percent = str_replace(',', '', $profit_per);


                            if ($value['Size'] != '') {
                                if (!size::where('size_name', $value['Size'])->exists())
                                {
                                    size::updateOrCreate(
                                        ['size_id' => '', 'company_id' => $company_id,
                                        ],
                                        [
                                            'created_by' => $created_by,
                                            'company_id' => $company_id,
                                            'size_name' => $value['Size'],
                                            'is_active' => '1',
                                        ]
                                    );
                                }
                            }
                            if ($value['Colour'] != '') {
                                if (!colour::where('colour_name', $value['Colour'])->exists()) {
                                    colour::updateOrCreate(
                                        ['colour_id' => '', 'company_id' => $company_id,
                                        ],
                                        [
                                            'created_by' => $created_by,
                                            'company_id' => $company_id,
                                            'colour_name' => $value['Colour'],
                                            'is_active' => '1',
                                        ]
                                    );

                                }
                            }
                            if ($value['Category'] != '') {
                                if (!category::where('category_name', $value['Category'])->exists()) {
                                    category::updateOrCreate(
                                        ['category_id' => '',
                                            'company_id' => $company_id,
                                        ],
                                        [
                                            'created_by' => $created_by,
                                            'company_id' => $company_id,
                                            'category_name' => $value['Category'],
                                            'is_active' => '1',
                                        ]
                                    );
                                }
                            }
                            if ($value['Sub category'] != '') {
                                if (!subcategory::where('subcategory_name', $value['Sub category'])->exists()) {

                                    $category_id = category::select('category_id')->where('category_name', $value['Category'])->whereNull('deleted_at')->get();
                                    $subcategory = subcategory::updateOrCreate(
                                        ['subcategory_id' => '', 'company_id' => $company_id,
                                        ],
                                        [
                                            'created_by' => $created_by,
                                            'company_id' => $company_id,
                                            'category_id' => $category_id[0]['category_id'],
                                            'subcategory_name' => $value['Sub category'],
                                            'is_active' => '1',
                                        ]
                                    );
                                }
                            }
                            if ($value['Brand'] != '') {
                                if (!brand::where('brand_type', $value['Brand'])->exists()) {
                                    $brand = brand::updateOrCreate(
                                        ['brand_id' => '', 'company_id' => $company_id,
                                        ],
                                        [
                                            'created_by' => $created_by,
                                            'company_id' => $company_id,
                                            'brand_type' => $value['Brand'],
                                            'is_active' => '1',
                                        ]
                                    );
                                }
                            }

                            $brand_id = 0;
                            if($value['Brand'] != '')
                            {
                                $brand = brand::select('brand_id')->where('brand_type', $value['Brand'])->first();

                                if(isset($brand) && $brand != '')
                                {
                                    $brand_id = $brand['brand_id'];
                                }
                            }
                            $category_id = 0;
                            if($value['Category'] != '') {
                                $category = category::select('category_id')->where('category_name', $value['Category'])->whereNull('deleted_at')->first();

                                if (isset($category) && $category != '')
                                {
                                    $category_id = $category['category_id'];
                                }
                            }
                            $subcategory_id = 0;
                            if($value['Sub category'] != '') {
                                $subcategory = subcategory::select('subcategory_id')->where('subcategory_name', $value['Sub category'])->whereNull('deleted_at')->first();

                                if (isset($subcategory) && $subcategory != '') {
                                    $subcategory_id = $subcategory['subcategory_id'];
                                }
                            }
                            $colour_id = 0;
                            if($value['Colour'] != '') {
                                $colour = colour::select('colour_id')->where('colour_name', $value['Colour'])->whereNull('deleted_at')->first();

                                if (isset($colour) && $colour != '') {
                                    $colour_id = $colour['colour_id'];
                                }
                            }
                            $size_id = 0;
                            if($value['Size'] != '') {
                                $size = size::select('size_id')->where('size_name', $value['Size'])->whereNull('deleted_at')->first();

                                if (isset($size) && $size != '') {
                                    $size_id = $size['size_id'];
                                }
                            }
                            $uqc_id = 0;
                            if($value['UQC'] != '') {
                                $uqc = uqc::select('uqc_id')->where('uqc_shortname', $value['UQC'])->whereNull('deleted_at')->first();

                                if (isset($uqc) && $uqc != '') {
                                    $uqc_id = $uqc['uqc_id'];
                                }
                            }


                            $system_barcode = str_pad(Auth::user()->company_id,10,"0");

                            $product_max_id = product::withTrashed()->where('company_id',Auth::user()->company_id)->get()->max('product_system_barcode');

                            if($product_max_id != '')
                            {
                                $product_max_id = $product_max_id -$system_barcode;
                            }
                            else{
                                $product_max_id  = 0;
                            }

                            $product_max_id++;

                            $system_barcode_final = $system_barcode + $product_max_id ;


                            //get tax type,tax title and tax currency from company profile and check cost gst % and sell gst % tax title wise





                            /* try {
                                 DB::beginTransaction();
                                 $product = product::updateOrCreate(
                                     ['product_id' => $product_id, 'company_id' => $company_id,],
                                     [
                                         'created_by' => $created_by,
                                         'company_id' => $company_id,
                                         'product_type' => $data['product_type'],
                                         'item_type' => 1,
                                         'product_name' => (isset($value['Product name']) ? $value['Product name'] : ''),
                                         'brand_id' => (isset($brand_id) && $brand_id != '0' ? $brand_id : NULL),
                                         'category_id' => (isset($category_id) && $category_id != '0' ? $category_id : NULL),
                                         'subcategory_id' => (isset($subcategory_id) && $subcategory_id != '0' ? $subcategory_id : NULL),
                                         'colour_id' => (isset($colour_id) && $colour_id != '0' ? $colour_id : NULL),
                                         'size_id' => (isset($size_id) && $size_id != '0' ? $size_id : NULL),
                                         'uqc_id' => (isset($uqc_id) && $uqc_id != '0' ? $uqc_id : NULL),
                                         'cost_rate' => (isset($value['Base price/cost rate']) ? $value['Base price/cost rate'] : '0'),
                                         'cost_price' => (isset($cost_price) ? $cost_price : '0'),
                                         'selling_price' => (isset($selling_price) ? $selling_price : '0'),
                                         'offer_price' => (isset($value['Offer price']) ? $value['Offer price'] : '0'),
                                         'product_mrp' => (isset($value['Product mrp'])  && $value['Product mrp'] != ''? $value['Product mrp'] : $value['Offer price']),
                                         'wholesale_price' => (isset($value['wholesale price']) ? $value['wholesale price'] : '0'),
                                         'cost_gst_percent' => (isset($value[$cost_tax_label]) ? $value[$cost_tax_label] : '0'),
                                         'cost_gst_amount' => (isset($cost_gst_amt) ? $cost_gst_amt : '0'),
                                         'profit_percent' => (isset($profit_percent) ? $profit_percent : '0'),
                                         'profit_amount' => (isset($profit_amount) ? $profit_amount : '0'),
                                         'sell_gst_percent' => (isset($value[$sell_tax_label]) ? $value[$sell_tax_label] : '0'),
                                         'sell_gst_amount' => (isset($sell_gst_amt) ? $sell_gst_amt : '0'),
                                         'product_system_barcode' => $product_system_barcode,
                                         'supplier_barcode' => (isset($value['Barcode']) && $value['Barcode'] != ' ' ? $value['Barcode'] : NULL),
                                         'is_ean' => (isset($value['is_ean']) ? $value['is_ean'] : '0'),
                                         'alert_product_qty' => (isset($value['Alert product qty']) ? $value['Alert product qty'] : '0'),
                                         'product_ean_barcode' => (isset($value['product ean barcode']) ? $value['product ean barcode'] : '0'),
                                         'sku_code' => (isset($value['SKU']) ? $value['SKU'] : ''),
                                         'product_code' => (isset($value['Product code']) ? $value['Product code'] : ''),
                                         'product_description' => (isset($value['Product description']) ? $value['Product description'] : ''),
                                         'hsn_sac_code' => (isset($value['HSN']) ? $value['HSN'] : ''),
                                         'days_before_product_expiry' => (isset($value['Days before product expiry']) && $value['Days before product expiry'] != '' ? $value['Days before product expiry'] : 0),
                                         'is_active' => "1"
                                     ]);

                                 DB::commit();
                                 $data['formdata'][$key]['product_id'] = $product->product_id;
                             } catch (\Illuminate\Database\QueryException $e) {
                                 DB::rollback();
                                 return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
                             }*/
                        }
                       /* else
                        {
                            $data['formdata'][$key]['product_id'] = $product_id;
                        }*/

                    /*}
                    else
                    {
                        $barcode = $value['Barcode'];
                        $product =  product::select('product_id')->where('supplier_barcode','LIKE',"%$barcode%")
                            ->orWhere('product_system_barcode','LIKE',"%$barcode%")
                            ->whereNull('deleted_at')->first();


                        $data['formdata'][$key]['product_id'] =  $product['product_id'];
                    }*/
                }

                return json_encode(array("Success"=>"True","Message"=>"Product Created Successfully!","Data"=>$data));
            }
            else {
                foreach ($data AS $key => $value)
                {

                    if($key % 500 == 0)
                    {

                        sleep(1);
                    }

                    /*if($value['Barcode'] == '')
                    {*/
                        if($value['Barcode'] == '')
                        {
                            $exist['product'][$key] = $value['Product name'];
                        }
                        else
                        {
                             $barcode = $value['Barcode'];
                            if(!product::where('supplier_barcode','=',$barcode)
                                ->orWhere('product_system_barcode','=',$barcode)
                                ->whereNull('deleted_at')->exists())
                            {
                                $exist['product'][$key] = $value['Product name'];
                            }
                        }


                        if($value['UQC'] != '')
                        {
                            if (!uqc::where('uqc_shortname', $value['UQC'])->exists())
                            {
                                return json_encode(array("Success" => "False", "Message" =>"".$value['UQC']."  UQC not found"));
                                exit;
                            }
                        }
                       // $exist['product'][$key] = $value['Product Name'];

                        if ($value['Brand'] != '') {
                            if (!brand::where('brand_type', $value['Brand'])->exists()) {
                                $exist['brand'][$key] = $value['Brand'];
                            }
                        }
                        if ($value['Category'] != '') {
                            if (!category::where('category_name', $value['Category'])->exists()) {
                                $exist['category'][$key] = $value['Category'];
                            }
                        }
                        if ($value['Sub category'] != '') {
                            if (!subcategory::where('subcategory_name', $value['Sub category'])->exists()) {
                                $exist['sub category'][$key] = $value['Sub category'];
                            }
                        }
                        if ($value['Colour'] != '') {
                            if (!colour::where('colour_name', $value['Colour'])->exists()) {
                                $exist['colour'][$key] = $value['Colour'];
                            }
                        }
                        if ($value['Size'] != '') {
                            if (!size::where('size_name', $value['Size'])->exists()) {
                                $exist['size'][$key] = $value['Size'];
                            }
                        }
                        if ($value['UQC'] != '')
                        {
                            if (!uqc::where('uqc_name', $value['UQC'])->exists()) {
                                $exist['UQC'][$key] = $value['UQC'];
                            }
                        }
                  /*  }*/
                    /*else
                    {
                        $barcode = $value['Barcode'];
                        /*$check_product_valid =  product::select('product_id')->where('supplier_barcode','LIKE',"%$barcode%")
                          ->orWhere('product_system_barcode','LIKE',"%$barcode%")
                           ->whereNull('deleted_at')->first();

                      if(!isset($check_product_valid) && $check_product_valid == '' && !isset($check_product_valid['product_id']))
                      {
                          return json_encode(array("Success"=>"False","Message"=>"There are no product available whose supplier barcode or system barcode match with '".$barcode."' !"));
                          exit;
                      }


                        if($value['UQC'] != '')
                        {
                            if (!uqc::where('uqc_shortname', $value['UQC'])->exists())
                            {

                                return json_encode(array("Success" => "False", "Message" =>"".$value['UQC']." not found"));
                                exit;
                            }

                        }
                    }*/
                    }


               /* if(empty($exist))
                {
                    $data =  $record['confirm'] = 1;
                    self::product_check($data);

                }else
                {*/
                    array_push($record, $exist);
                    return json_encode(array("Success" => "True", "Data" => $record));
                /*}*/
            }
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"No Row!"));
        }

    }

    public function inward_product_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

      //  isset($data['supplier_barcode']) && $data['supplier_barcode'] != null ? $data['supplier_barcode'] : '';

       $product = product::select('product_id','product_system_barcode','supplier_barcode', 'product_name','hsn_sac_code')
            ->where('product_id',$data['product_id'])
            ->where('company_id',Auth::user()->company_id)
            ->WhereNull('deleted_at')->get();


        return json_encode(array("Success"=>"True","Data"=>$product));

    }

//this function is used when import excel file in inward
    public function import_inward_product_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $value = $request->all();

        $company_id =  Auth::user()->company_id;
        $userId = Auth::User()->user_id;

        $created_by = $userId;

        $product_features =  ProductFeatures::getproduct_feature('');

        //for add fmcg inward stock

      /*  foreach ($data AS $key=>$value)
        {*/

            if($value['Barcode'] != '')
            {
                $product = product::select('product_id', 'product_system_barcode', 'supplier_barcode', 'product_name', 'hsn_sac_code','product_type','offer_price')
                    ->where('supplier_barcode', '=', $value['Barcode'])
                    ->orWhere('product_system_barcode', '=', $value['Barcode'])
                    //->orWhere('product_name', '=', $value['Product name'])
                    ->where('company_id', Auth::user()->company_id)
                    ->with('product_features_relationship')
                    ->WhereNull('deleted_at')->get();

                if(isset($value['inward_type']) && $value['inward_type'] != '')
                {
                    $product_type_name = '';
                    if($value['inward_type'] == 1)
                    {
                        $product_type_name = "FMCG";
                    }
                    if($value['inward_type'] == 2)
                    {
                        $product_type_name = "GARMENT";
                    }
                }

                if(isset($product) && !empty($product) && isset($product[0])){
                        if($value['inward_type'] != $product[0]['product_type'])
                        {
                            return json_encode(array("Success"=>"False","Message"=>$value['Product name'] . " Is Not A ".$product_type_name." Product"));
                            exit;
                        }
                }
            }else
            {
                $product = '';
            }

            if (!isset($product) || empty($product) || !isset($product[0]))
            {
                $tax_info = company_profile::select('tax_type','tax_title','inward_type','inward_calculation')->where('company_id', Auth::user()->company_id)->get()->first();



                $cost_tax_label = 'Cost gst %';
                $sell_tax_label = 'Sell gst %';
                if ($tax_info['tax_type'] == 1) {
                    $cost_tax_label = 'Cost ' . $tax_info['tax_title'] . ' %';
                    $sell_tax_label = 'Sell ' . $tax_info['tax_title'] . ' %';
                }

                $product_excel_mrp = 0;
                if($tax_info['inward_calculation'] != 3)
                {
                    $cost_gst = number_format(($value['Base price/cost rate'] * $value[$cost_tax_label]) / (100), 4);
                    $cost_gst_amt = str_replace(',', '', $cost_gst);


                    if ($value['Offer price'] == '' || $value['Offer price'] == 0)
                    {

                        if ($value['Profit %'] != '' && ($value['Selling price'] == '' || $value['Selling price'] == 0))
                        {

                            $p_amount = number_format($value['Base price/cost rate'] * $value['Profit %'] / (100));

                            $profitamount = str_replace(',','',$p_amount);

                            $profit_amount = $profitamount;
                            $profit_percent = $value['Profit %'];

                            $o_price = number_format($value['Base price/cost rate'] + ($profitamount) + ($value['Extra charge']));

                            $value['Offer price'] = str_replace(',', '', $o_price);

                            if($value[$sell_tax_label] != '' && $value[$sell_tax_label] != 0)
                            {

                                $sellgstamtt_tt = number_format(($value['Offer price'] * $value[$sell_tax_label]) / number_format(100),4);
                                $sellgstamtt = str_replace(',', '', $sellgstamtt_tt);

                                $oo_price = number_format($value['Offer price'] + $sellgstamtt,4);



                                $value['Offer price'] = str_replace(',', '', $oo_price);

                            }
                        }


                        if ($value['Profit %'] == '' && $value['Selling price'] != '' && $value['Selling price'] != 0)
                        {
                            $value['Offer price'] = $value['Selling price'];
                        }
                        if ($value['Profit %'] != '' && $value['Selling price'] != '' && $value['Selling price'] != 0)
                        {
                            $value['Offer price'] = $value['Selling price'];
                        }
                    }

                    $sell_gst = number_format(($value['Offer price'] * $value[$sell_tax_label]) / (100 + $value[$sell_tax_label]), 4);
                    $sell_gst_amt = str_replace(',', '', $sell_gst);

                    $selling_prc = number_format($value['Offer price'] - ($sell_gst_amt), 4);
                    $selling_price = str_replace(',', '', $selling_prc);

                    $cost_prc = number_format($value['Base price/cost rate'] + $cost_gst_amt, 4);
                    $cost_price = str_replace(',', '', $cost_prc);
                    if ($value['Profit %'] != '') {
                        $profit_amt = number_format($selling_price - $value['Base price/cost rate'], 4);
                        $profit_amount = str_replace(',', '', $profit_amt);

                        $profit_per = number_format(($profit_amount * (100)) / ($value['Base price/cost rate']), 4);
                        $profit_percent = str_replace(',', '', $profit_per);
                    }

                    $product_excel_mrp = (isset($value['Product MRP']) && $value['Product MRP'] != '' ? $value['Product MRP'] : $value['Offer price']);
                }

                $uqc_id = 0;
                if ($value['UQC'] != '')
                {
                    $uqc = uqc::select('uqc_id')->where('uqc_shortname', $value['UQC'])->whereNull('deleted_at')->first();
                    if (isset($uqc) && $uqc != '')
                    {
                        $uqc_id = $uqc['uqc_id'];
                    }
                }

                $system_barcode = str_pad(Auth::user()->company_id, 10, "0");
                $product_max_id = product::withTrashed()->where('company_id', Auth::user()->company_id)->get()->max('product_system_barcode');


                if($product_max_id != '')
                {
                    $product_max_id = $product_max_id -$system_barcode;
                }
                else{
                    $product_max_id  = 0;
                }
                $product_max_id++;

                $system_barcode_final = $system_barcode + $product_max_id;


                try {
                    DB::beginTransaction();
                    $product = product::updateOrCreate(
                        ['product_id' => '', 'company_id' => $company_id,],
                        [
                            'created_by' => $created_by,
                            'company_id' => $company_id,
                            'product_type' => $value['inward_type'],
                            'item_type' => 1,
                            'product_name' => (isset($value['Product name']) ? $value['Product name'] : ''),
                            'uqc_id' => (isset($uqc_id) && $uqc_id != '0' ? $uqc_id : NULL),
                            'cost_rate' => (isset($value['Base price/cost rate']) ? $value['Base price/cost rate'] : '0'),
                            'cost_price' => (isset($cost_price) ? $cost_price : '0'),
                            'selling_price' => (isset($selling_price) ? $selling_price : '0'),
                            'offer_price' => (isset($value['Offer price']) ? $value['Offer price'] : '0'),
                            'product_mrp' => $product_excel_mrp,
                            'wholesale_price' => (isset($value['wholesale price']) ? $value['wholesale price'] : '0'),
                            'cost_gst_percent' => (isset($value[$cost_tax_label]) ? $value[$cost_tax_label] : '0'),
                            'cost_gst_amount' => (isset($cost_gst_amt) ? $cost_gst_amt : '0'),
                            'profit_percent' => (isset($profit_percent) ? $profit_percent : '0'),
                            'profit_amount' => (isset($profit_amount) ? $profit_amount : '0'),
                            'sell_gst_percent' => (isset($value[$sell_tax_label]) ? $value[$sell_tax_label] : '0'),
                            'sell_gst_amount' => (isset($sell_gst_amt) ? $sell_gst_amt : '0'),
                            'product_system_barcode' => $system_barcode_final,
                            'supplier_barcode' => (isset($value['Barcode']) && $value['Barcode'] != ' ' ? $value['Barcode'] : NULL),
                            'is_ean' => (isset($value['is_ean']) ? $value['is_ean'] : '0'),
                            'alert_product_qty' => (isset($value['Alert product qty']) ? $value['Alert product qty'] : '0'),
                            'product_ean_barcode' => (isset($value['product ean barcode']) ? $value['product ean barcode'] : '0'),
                            'sku_code' => (isset($value['SKU']) ? $value['SKU'] : ''),
                            'product_code' => (isset($value['Product code']) ? $value['Product code'] : ''),
                            'product_description' => (isset($value['Product description']) ? $value['Product description'] : ''),
                            'hsn_sac_code' => (isset($value['HSN']) ? $value['HSN'] : ''),
                            'days_before_product_expiry' => (isset($value['Days before product expiry']) && $value['Days before product expiry'] != '' ? $value['Days before product expiry'] : 0),
                            'default_qty' => (isset($value['MOQ']) && $value['MOQ'] != '' ? $value['MOQ'] : 1),
                            'is_active' => "1"
                        ]);

                    DB::table('price_masters')->insert(array(
                        'company_id' => Auth::user()->company_id,
                        'product_id' => $product->product_id,
                        'product_qty' => '0',
                        'product_mrp' => $product_excel_mrp,
                        'offer_price' => (isset($value['Offer price']) ? $value['Offer price'] : '0'),
                        'is_active' => '1',
                        'created_by' => Auth::User()->user_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ));

                    if (isset($product_features) && !empty($product_features)) {
                        foreach ($product_features AS $feature_key => $feature_value) {
                            if (array_key_exists($feature_value['product_features_name'], $value)) {
                                $product_id = $product->product_id;
                                $product_features_data_id = NULL;
                                if (isset($value[$feature_value['product_features_name']]) && $value[$feature_value['product_features_name']]) {
                                    $product_id = $product->product_id;
                                    $productfeatures_data = product_features_data::updateOrCreate(
                                        ['product_features_data_value' => $value[$feature_value['product_features_name']],
                                            'company_id' => $company_id,
                                            'product_features_id' => $feature_value['product_features_id'],
                                        ],
                                        [
                                            'product_features_id' => $feature_value['product_features_id'],
                                            'created_by' => $created_by,
                                            'company_id' => $company_id,
                                            'product_features_data_value' => $value[$feature_value['product_features_name']],
                                            //'product_features_data_url' => '',
                                            //'feature_content' => '',
                                            //'product_features_data_image' => '',
                                            // 'product_features_banner_image' => '',
                                            'parent' => '0',
                                            'is_active' => '1'
                                        ]
                                    );
                                    $product_features_data_id = $productfeatures_data->product_features_data_id;
                                }

                                $relationship_product_insert = product_features_relationship::updateOrCreate(
                                    ['product_id' => $product_id,
                                        'company_id' => $company_id
                                        //'product_features_data_id' => $productfeatures_data->product_features_data_id
                                    ],
                                    ['created_by' => $created_by,
                                        'company_id' => $company_id,
                                        'product_id' => $product_id,
                                        $feature_value['html_id'] => $product_features_data_id,
                                        'deleted_at' => NULL,
                                        'deleted_by' => NULL,
                                    ]
                                );
                            }
                        }
                    }

                    DB::commit();

                    $product = product::select('product_id', 'product_system_barcode', 'supplier_barcode', 'product_name', 'hsn_sac_code','offer_price')
                        ->where('product_id', '=', $product->product_id)
                        ->with('product_features_relationship')
                       /* ->orWhere('product_system_barcode', '=', $value['Barcode'])
                        ->orWhere('product_name', '=', $value['Product name'])
                        ->where('company_id', Auth::user()->company_id)
                        ->WhereNull('deleted_at')*/
                        ->get();

                } catch (\Illuminate\Database\QueryException $e) {
                    DB::rollback();
                    return json_encode(array("Success" => "False", "Message" => $e->getMessage()));
                }
            }

//            $inward_product_detail_insert = inward_product_detail::updateOrCreate(
//                ['inward_stock_id' => "1",
//                    'company_id'=>$company_id,
//                    'inward_product_detail_id'=>'',],
//                [
//                    "company_id" => "1",
//                    "inward_stock_id" => "1",
//                    "supplier_gst_id" => "1",
//                    "product_id" => $product[0]['product_id'],
//                    "batch_no" => $value['Batch no'],
//                    "base_price" => $value['Base price/cost rate'],
//                    "base_discount_percent" => 0,
//                    "base_discount_amount" => 0,
//                    "scheme_discount_percent" => 0,
//                    "scheme_discount_amount" => 0,
//                    "free_discount_percent" => 0,
//                    "free_discount_amount" => 0,
//                    "cost_rate" => 0,
//                    "extra_charge" => 0,
//                    "cost_igst_percent" => 0,
//                    "cost_igst_amount" => 0,
//                    "cost_cgst_percent" => 0,
//                    "cost_cgst_amount" => 0,
//                    "cost_sgst_percent" => 0,
//                    "cost_sgst_amount" => 0,
//                    "cost_price" => 0,
//                    "profit_percent" => 0,
//                    "profit_amount" => 0,
//                    "sell_price" => 0,
//                    "selling_gst_percent" => 0,
//                    "selling_gst_amount" => 0,
//                    "offer_price" => 0,
//                    "product_mrp" => 0,
//                    "product_qty" => 0,
//                    "free_qty" => 0,
//                    "pending_return_qty" => 0,
//                    "mfg_date" => 0,
//                    "expiry_date" => 0,
//                    "total_cost_rate_with_qty" => 0,
//                    "total_igst_amount_with_qty" => 0,
//                    "total_cgst_amount_with_qty" => 0,
//                    "total_sgst_amount_with_qty" => 0,
//                    "total_cost" => 0,
//                    "is_active" => 0,
//                    "created_by" => 1,
//                ]
//            );

           /* $qtys = price_master::select('product_qty', 'price_master_id', 'product_mrp','offer_price')
                ->where('batch_no', '=', $inward_product_detail_array['batch_no'])
                ->where('offer_price', '=', $inward_product_detail_array['offer_price'])
                ->where('product_id', '=', $inward_product_detail_array['product_id'])
                ->where('company_id', Auth::user()->company_id)->get();


            if (isset($qtys) && $qtys != '' && isset($qtys[0]['offer_price']))
            {
                //INCREMENT QUNTITY
                $total_qty =  ($qtys[0]['product_qty'] + $value['Add qty'] + $value['Free qty']);
                $price_master_array['product_qty'] = $total_qty;

                $price_master = price_master::updateOrCreate(
                    [
                        'product_id' => $product[0]['product_id'],
                        'company_id' => $company_id,
                        'price_master_id' => $qtys[0]['price_master_id'],
                    ],
                    [
                        'product_id' =>$product[0]['product_id'],
                        'batch_no' =>$value['Batch no'],
                        'product_qty' =>$value['Batch no'],
                        'product_mrp' =>$value['Batch no'],
                    ]);

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
            }*/
      /*  }*/

        if(isset($product[0]['product_features_relationship']) && $product[0]['product_features_relationship'] != '')
        {
            foreach ($product_features AS $kk => $vv)
            {
                $html_id = $vv['html_id'];

                if($product[0]['product_features_relationship'][$html_id] != '' && $product[0]['product_features_relationship'][$html_id] != NULL)
                {
                    $nm =  product::feature_value($vv['product_features_id'],$product[0]['product_features_relationship'][$html_id]);
                    $product[0][$html_id] =$nm;
                }
            }
        }



        return json_encode(array("Success"=>"True","Data"=>$product));

    }

    //for get existing supplier barcode data

   /* Public function get_existing_product_detail(Request $request)
    {
        $data = $request->all();

        if(isset($data) && isset($data['barcode']) && $data['barcode'] != '')
        {
            $product_data = product::where('supplier_barcode',$data['barcode'])
                ->where('company_id',Auth::user()->company_id)
                ->WhereNull('deleted_at')->get();

            return json_encode(array("Success"=>"True","Data"=>$product_data));
        }
        else
        {
            return json_encode(array("Success"=>"True","Data"=>''));
        }
    }*/


   public function product_export(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $query = isset($request['query']) ? $request['query']  : '';
        $dynamic_query = isset($request['dynamic_query']) ? $request['dynamic_query']  : '';

        return Excel::download(new product_export($query,$dynamic_query),'Products_data.xlsx');
    }

    public function product_update_export(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $query = isset($request['query']) ? $request['query']  : '';
        $dynamic_query = isset($request['dynamic_query']) ? $request['dynamic_query']  : '';

        return Excel::download(new product_update_export($query,$dynamic_query),'Products_update_data.xlsx');
    }

    //This function is used for search product name
    public function product_name_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = product::
            where('deleted_at','=',NULL)
            ->where('product_name','LIKE', '%'.$request->search_val.'%')
            ->select('product_name','product_id')
            ->with('product_price_master')
            ->whereHas('product_price_master',function ($q)
            {
                $q->where('company_id',Auth::user()->company_id);
            })
            ->get();

        foreach ($result AS $k=>$v)
        {

            if($v['product_id'] != '')
            {
               $encrypted_id = encrypt($v['product_id']);
               $v->encrypt_product_id = $encrypted_id;

            }

        }



        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    //This function is used for search sku code
    public function sku_code_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = product::
            where('deleted_at','=',NULL)
            ->where('sku_code','LIKE', '%'.$request->search_val.'%')
            ->select('sku_code')
            ->with('product_price_master')
            ->whereHas('product_price_master',function ($q)
            {
                $q->where('company_id',Auth::user()->company_id);
            })
            ->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    //This function is used for search product code
    public function product_code_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = product::
        where('deleted_at','=',NULL)
            ->where('product_code','LIKE', '%'.$request->search_val.'%')
            ->select('product_code')
            ->with('product_price_master')
            ->whereHas('product_price_master',function ($q)
            {
                $q->where('company_id',Auth::user()->company_id);
            })
            ->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    //This function is used for search BARCODE
    public function product_barcode_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = product::
            where('deleted_at','=',NULL)
            ->where(function($qq) use($request){
                $qq->where('product_system_barcode', 'like', '%'.$request->search_val.'%')
                    ->orWhere('supplier_barcode', 'like', '%'.$request->search_val.'%')
                    ->select('supplier_barcode','product_system_barcode','product_id');
            })
            ->with('product_price_master')
            ->whereHas('product_price_master',function ($q)
            {
                $q->where('company_id',Auth::user()->company_id);
            })
            ->get();

        return json_encode(array("Success"=>"True","Data"=>$result));
    }


    //FOR GETTING DEPENDENT RECORD

    public function product_dependency(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $product_id = decrypt($request->id);

        $dependent_array = [];

        //$product_detail = product::select('product_name')->where('company_id',Auth::User()->company_id);

            //GET PRODUCT DEPEDENCY FROM INWARD STOCK
            $inward_depedency = inward_product_detail::where('company_id',Auth::User()->company_id)
                ->whereNull('deleted_at')
                ->where('product_id',$product_id)
                ->with('inward_stock')
                ->groupBy('batch_no','product_id')->get();

            foreach ($inward_depedency AS $key=>$value)
            {
                $detail = array('Invoice No' => $value->inward_stock->invoice_no);
                $dependent_array[] = array(
                  'Module_Name'=> "Inward Stock",
                  'detail' => $detail,
                  'created_at' => $value->inward_stock->created_at,
                  'updated_at' => $value->inward_stock->updated_at
                );
            }
            //END OF INWARD STOCK DEPENDENCY

            //FOR GETTING PRODUCT DEPENDENCT FROM PURCHASE ORDER
            $po_dependency = purchase_order_detail::where('company_id',Auth::User()->company_id)
                ->whereNull('deleted_at')
                ->where('product_id',$product_id)
                ->with('purchase_order')
                ->groupBy('purchase_order_id','product_id')->get();


            foreach ($po_dependency AS $po_key=>$po_value)
            {
                $detail = array('PO No' => $po_value->purchase_order->po_no);
                $dependent_array[] = array(
                    'Module_Name'=> "Purchase Order",
                    'detail' => $detail,
                    'created_at' => $po_value->purchase_order->created_at,
                    'updated_at' => $po_value->purchase_order->updated_at
                );
            }
            //END OF GETTING PRODUCT DEPENDCY FROM PURCHASE ORDER


            //FOR GETTING PRODUCT DEPENDENCT FROM DEBIT NOTE
            $debit_dependency = debit_product_detail::where('company_id',Auth::User()->company_id)
                ->whereNull('deleted_at')
                ->where('product_id',$product_id)
                ->with('debit_note')
                ->groupBy('debit_note_id','product_id')->get();


            foreach ($debit_dependency AS $debit_key=>$debit_value)
            {
                $detail = array('Debit No' => $debit_value->debit_note->debit_no);
                $dependent_array[] = array(
                    'Module_Name'=> "Debit Note",
                    'detail' => $detail,
                    'created_at' => $debit_value->debit_note->created_at,
                    'updated_at' => $debit_value->debit_note->updated_at
                );
            }
            //END OF GETTING PRODUCT DEPENDCY FROM DEBIT NOTE


            //FOR GETTING PRODUCT DEPENDENCT FROM DAMAGE USED
            $damage_dependency = damage_product_detail::where('company_id',Auth::User()->company_id)
                ->whereNull('deleted_at')
                ->where('product_id',$product_id)
                ->with('damage_product.damage_types')
                ->with('damage_types')
                ->groupBy('damage_product_id','product_id')->get();


            foreach ($damage_dependency AS $damage_key=>$damage_value)
            {
                 $detail = array('Damage No' => $damage_value->damage_product->damage_no,
                     'Damage Type' => $damage_value->damage_product->damage_types->damage_type);
                $dependent_array[] = array(
                    'Module_Name'=> "Damage",
                    'detail' => $detail,
                    'created_at' => $damage_value->damage_product->created_at,
                    'updated_at' => $damage_value->damage_product->updated_at
                );
            }

            //END OF GETTING PRODUCT DEPENDCY FROM DEBIT NOTE

        return json_encode(array("Success"=>"True","Data"=>$dependent_array));

    }


    //FOR DOWNLOAD PRODUCT TEMPLATE
    public function product_template(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        return Excel::download(new product_template(), "product_template.xlsx");
    }


    //this function used in import product from product module
    public function import_products_check(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $datas = $request->all();
        $data = $datas['productsarr'];

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $created_by = $userId;

        if ($data != '' && !empty($data))
        {
            $tax_info = company_profile::select('tax_type', 'tax_title', 'inward_type','product_calculation')->where('company_id', Auth::user()->company_id)->get()->first();

            $product_features =  ProductFeatures::getproduct_feature('');

            if (isset($data) && $data != '')
            {
                $error = 0;
                foreach ($data AS $key => $value)
                {
                    if ($value['Supplier Barcode'] != '')
                    {
                        $barcode = $value['Supplier Barcode'];
                        $product = product::select('product_id', 'product_system_barcode')
                            ->where('supplier_barcode', '=', $barcode)
                            ->orWhere('product_system_barcode', '=', $barcode)
                            ->whereNull('deleted_at')
                            ->first();

                        if (isset($product) && $product['product_id'] != '') {
                            $error = 1;
                            return json_encode(array("Success" => "False", "Message" => "Product '" . $value['Product Name'] . "' with '" . $value['Supplier Barcode'] . "' Supplier Barcode Already Exist!"));
                            exit;
                        }
                    }
                    if ($value['UQC'] != '')
                    {
                        {
                            if (!uqc::where('uqc_shortname', $value['UQC'])->exists()) {
                                $error = 1;
                                return json_encode(array("Success" => "False", "Message" => "" . $value['UQC'] . "  UQC not found"));
                                exit;
                            }
                        }
                    }

                    if ($error == 0) {
                        foreach ($data AS $key => $value)
                        {
                            $product_excel_mrp = 0;
                            $cost_tax_label = 'Cost GST %';
                            $sell_tax_label = 'Sell GST %';
                            if ($tax_info['tax_type'] == 1) {
                                $cost_tax_label = 'Cost ' . $tax_info['tax_title'] . ' %';
                                $sell_tax_label = 'Sell ' . $tax_info['tax_title'] . ' %';
                            }
                            if($tax_info['product_calculation'] != 3)
                            {
                                $cost_gst = number_format(($value['Cost Rate'] * $value[$cost_tax_label]) / (100), 4);

                                $cost_gst_amt = str_replace(',', '', $cost_gst);

                                if ($value['Offer Price'] == '') {
                                    if ($value['Profit %'] != '' && $value['Selling Rate'] == '') {
                                        $profitamount = number_format($value['Cost Rate'] * $value['Profit %'] / (100));

                                        $value['Offer Price'] = number_format($value['Cost Rate'] + ($profitamount) + ($value['Extra Charge']));
                                    }
                                    if ($value['Profit %'] == '' && $value['Selling Rate'] != '') {
                                        $value['Offer Price'] = $value['Selling Rate'];
                                    }
                                    if ($value['Profit %'] != '' && $value['Selling Rate'] != '') {
                                        $value['Offer Price'] = $value['Selling Rate'];
                                    }
                                }

                                $sell_gst = number_format(($value['Offer Price'] * $value[$sell_tax_label]) / (100 + $value[$sell_tax_label]), 4);
                                $sell_gst_amt = str_replace(',', '', $sell_gst);

                                $selling_prc = number_format($value['Offer Price'] - ($sell_gst_amt), 4);
                                $selling_price = str_replace(',', '', $selling_prc);


                                $cost_prc = number_format($value['Cost Rate'] + $cost_gst_amt, 4);
                                $cost_price = str_replace(',', '', $cost_prc);

                                $profit_amt = number_format($selling_price - $value['Cost Rate'], 4);
                                $profit_amount = str_replace(',', '', $profit_amt);

                                $profit_per = number_format(($profit_amount * (100)) / ($value['Cost Rate']), 4);
                                $profit_percent = str_replace(',', '', $profit_per);

                                $product_excel_mrp = (isset($value['Product MRP']) && $value['Product MRP'] != '' ? $value['Product MRP'] : $value['Offer Price']);
                            }


                            $system_barcode = str_pad(Auth::user()->company_id, 10, "0");

                            $product_max_id = product::withTrashed()->where('company_id', Auth::user()->company_id)->get()->max('product_system_barcode');

                            if($product_max_id != '')
                            {
                                $product_max_id = $product_max_id -$system_barcode;
                            }
                            else{
                                $product_max_id  = 0;
                            }
                            $product_max_id++;

                            $system_barcode_final = $system_barcode + $product_max_id;


                            $uqc_id = 0;
                            if ($value['UQC'] != '') {
                                $uqc = uqc::select('uqc_id')->where('uqc_shortname', $value['UQC'])->whereNull('deleted_at')->first();

                                if (isset($uqc) && $uqc != '') {
                                    $uqc_id = $uqc['uqc_id'];
                                }
                            }
                            try {
                                DB::beginTransaction();
                                $product = product::updateOrCreate(
                                    ['product_id' => '', 'company_id' => $company_id,],
                                    [
                                        'created_by' => $created_by,
                                        'company_id' => $company_id,
                                        'product_type' => $tax_info['inward_type'],
                                        'item_type' => (isset($datas['type']) ? $datas['type'] : 1),
                                        'product_name' => (isset($value['Product Name']) ? $value['Product Name'] : ''),
                                        // 'brand_id' => (isset($brand_id) && $brand_id != '0' ? $brand_id : NULL),
                                        // 'category_id' => (isset($category_id) && $category_id != '0' ? $category_id : NULL),
                                        //  'subcategory_id' => (isset($subcategory_id) && $subcategory_id != '0' ? $subcategory_id : NULL),
                                        //  'colour_id' => (isset($colour_id) && $colour_id != '0' ? $colour_id : NULL),
                                        //  'size_id' => (isset($size_id) && $size_id != '0' ? $size_id : NULL),
                                        'uqc_id' => (isset($uqc_id) && $uqc_id != '0' ? $uqc_id : NULL),
                                        'cost_rate' => (isset($value['Cost Rate']) ? $value['Cost Rate'] : '0'),
                                        'cost_price' => (isset($cost_price) ? $cost_price : '0'),
                                        'selling_price' => (isset($selling_price) ? $selling_price : '0'),
                                        'offer_price' => (isset($value['Offer Price']) ? $value['Offer Price'] : '0'),
                                        'product_mrp' => $product_excel_mrp,
                                        'wholesale_price' => (isset($value['Wholesale Price']) ? $value['Wholesale Price'] : '0'),
                                        'cost_gst_percent' => (isset($value[$cost_tax_label]) ? $value[$cost_tax_label] : '0'),
                                        'cost_gst_amount' => (isset($cost_gst_amt) ? $cost_gst_amt : '0'),
                                        'profit_percent' => (isset($profit_percent) ? $profit_percent : '0'),
                                        'profit_amount' => (isset($profit_amount) ? $profit_amount : '0'),
                                        'sell_gst_percent' => (isset($value[$sell_tax_label]) ? $value[$sell_tax_label] : '0'),
                                        'sell_gst_amount' => (isset($sell_gst_amt) ? $sell_gst_amt : '0'),
                                        'product_system_barcode' => $system_barcode_final,
                                        'supplier_barcode' => (isset($value['Supplier Barcode']) && $value['Supplier Barcode'] != ' ' ? $value['Supplier Barcode'] : NULL),
                                        'is_ean' => (isset($value['is_ean']) ? $value['is_ean'] : '0'),
                                        'alert_product_qty' => (isset($value['Low Stock Alert']) ? $value['Low Stock Alert'] : '0'),
                                        'sku_code' => (isset($value['SKU']) ? $value['SKU'] : ''),
                                        'product_code' => (isset($value['Product Code']) ? $value['Product Code'] : ''),
                                        'product_description' => (isset($value['Product Description']) ? $value['Product Description'] : ''),
                                        'hsn_sac_code' => (isset($value['HSN']) ? $value['HSN'] : ''),
                                        'days_before_product_expiry' => (isset($value['Alert Before Product Expiry(Days)']) && $value['Alert Before Product Expiry(Days)'] != '' ? $value['Alert Before Product Expiry(Days)'] : 0),
                                        'default_qty' => (isset($value['MOQ']) && $value['MOQ'] != '' ? $value['MOQ'] : 1),
                                        'note' => (isset($value['Note']) && $value['Note'] != '' ? $value['Note'] : ''),
                                        'is_active' => "1"
                                    ]);


                                DB::table('price_masters')->insert(array(
                                    'company_id' => Auth::user()->company_id,
                                    'product_id' => $product->product_id,
                                    'product_qty' => '0',
                                    'product_mrp' => $product_excel_mrp,
                                    'offer_price' => (isset($value['Offer Price']) ? $value['Offer Price'] : '0'),
                                    'is_active' => '1',
                                    'created_by' => Auth::User()->user_id,
                                    'created_at' => date('Y-m-d H:i:s')
                                ));

                                if (isset($product_features) && !empty($product_features))
                                {

                                    foreach ($product_features AS $feature_key => $feature_value)
                                    {
                                        if (array_key_exists($feature_value['product_features_name'],$value)) {
                                            $product_id = $product->product_id;
                                            $product_features_data_id = NULL;
                                            if (isset($value[$feature_value['product_features_name']]) && $value[$feature_value['product_features_name']] != '') {
                                                $productfeatures_data = product_features_data::updateOrCreate(
                                                    ['product_features_data_value' => $value[$feature_value['product_features_name']],
                                                        'company_id' => $company_id,
                                                        'product_features_id' => $feature_value['product_features_id'],
                                                    ],
                                                    [
                                                        'product_features_id' => $feature_value['product_features_id'],
                                                        'created_by' => $created_by,
                                                        'company_id' => $company_id,
                                                        'product_features_data_value' => $value[$feature_value['product_features_name']],
                                                        //'product_features_data_url' => '',
                                                        //'feature_content' => '',
                                                        //'product_features_data_image' => '',
                                                        // 'product_features_banner_image' => '',
                                                        'parent' => '0',
                                                        'is_active' => '1'
                                                    ]
                                                );
                                                $product_features_data_id = $productfeatures_data->product_features_data_id;
                                            }


                                            $relationship_product_insert = product_features_relationship::updateOrCreate(
                                                ['product_id' => $product_id,
                                                    'company_id' => $company_id
                                                ],
                                                ['created_by' => $created_by,
                                                    'company_id' => $company_id,
                                                    'product_id' => $product_id,
                                                    $feature_value['html_id'] => $product_features_data_id,
                                                    'deleted_at' => NULL,
                                                    'deleted_by' => NULL,
                                                ]
                                            );
                                        }
                                        }


                                    }

                                DB::commit();

                            } catch (\Illuminate\Database\QueryException $e)
                            {
                                DB::rollback();
                                return json_encode(array("Success" => "False", "Message" => $e->getMessage()));
                            }
                        }

                        return json_encode(array("Success" => "True", "Message" => "Product Created Successfully!"));
                    }
                }
            } else {
                return json_encode(array("Success" => "False", "Message" => "No Row!"));
            }
        }
    }


    public function products_update_check(Request $request)
{
    Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
    $data = $request->all();

    $record = [];

    if ($data != '' && !empty($data['productsarr']))
    {
        foreach ($data['productsarr'] AS $key => $value)
        {

            if ($key % 500 == 0)
            {
                sleep(1);
            }

                if (!product::where('product_system_barcode', '=', $value['System Barcode'])
                    ->whereNull('deleted_at')
                    ->where('company_id',Auth::user()->company_id)
                    ->exists())
                {
                    return json_encode(array("Success" => "False", "Message" => "" . $value['System Barcode'] . " System Barcode  not found"));
                    exit;
                }

                if($value['Supplier Barcode'] != '') {
                    if (product::
                    where('product_system_barcode', '!=', $value['System Barcode'])
                        ->where('supplier_barcode', '=', $value['Supplier Barcode'])
                        ->whereNull('deleted_at')
                        ->where('company_id', Auth::user()->company_id)
                        ->exists()) {
                        return json_encode(array("Success" => "False", "Message" => "" . $value['Supplier Barcode'] . " Supplier Barcode Already Exists"));
                        exit;
                    }
                }


            if ($value['UQC'] != '')
            {
                if (!uqc::where('uqc_shortname', $value['UQC'])->exists())
                {
                    return json_encode(array("Success" => "False", "Message" => "" . $value['UQC'] . "  UQC not found"));
                    exit;
                }
            }

        }
        return json_encode(array("Success" => "True"));

    } else {
        return json_encode(array("Success" => "False", "Message" => "No Row!"));
    }

}


    public function update_product_data(Request $request)
{
    Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
    $data = $request->all();

    if ($data != '' && !empty($data['productsarr']))
    {
        $product_features =  ProductFeatures::getproduct_feature('');
        foreach ($data['productsarr'] AS $key => $value)
        {

            if ($key % 500 == 0)
            {
                sleep(1);
            }

            $uqc_id = 0;
            if ($value['UQC'] != '') {
                $uqc = uqc::select('uqc_id')->where('uqc_shortname', $value['UQC'])->whereNull('deleted_at')->first();

                if (isset($uqc) && $uqc != '') {
                    $uqc_id = $uqc['uqc_id'];
                }
            }

            try {
                DB::beginTransaction();
                $product = product::updateOrCreate(
                    ['product_system_barcode' => $value['System Barcode'],],
                    [
                        'modified_by' => Auth::User()->user_id,
                        'product_name' => (isset($value['Product Name']) ? $value['Product Name'] : ''),
                        'uqc_id' => (isset($uqc_id) && $uqc_id != '0' ? $uqc_id : NULL),
                        'supplier_barcode' => (isset($value['Supplier Barcode']) && $value['Supplier Barcode'] != ' ' ? $value['Supplier Barcode'] : NULL),
                        'alert_product_qty' => (isset($value['Low Stock Alert']) ? $value['Low Stock Alert'] : '0'),
                        'sku_code' => (isset($value['SKU']) ? $value['SKU'] : ''),
                        'product_code' => (isset($value['Product Code']) ? $value['Product Code'] : ''),
                        'product_description' => (isset($value['Product Description']) ? $value['Product Description'] : ''),
                        'hsn_sac_code' => (isset($value['HSN']) ? $value['HSN'] : ''),
                        'days_before_product_expiry' => (isset($value['Alert Before Product Expiry(Days)']) && $value['Alert Before Product Expiry(Days)'] != '' ? $value['Alert Before Product Expiry(Days)'] : 0),
                        'default_qty' => (isset($value['MOQ']) && $value['MOQ'] != '' ? $value['MOQ'] : 1),
                        'note' => (isset($value['Note']) && $value['Note'] != '' ? $value['Note'] : ''),
                        'is_active' => "1"
                    ]);


                if (isset($product_features) && !empty($product_features))
                {

                    foreach ($product_features AS $feature_key => $feature_value)
                    {
                        if (array_key_exists($feature_value['product_features_name'],$value))
                        {
                            $product_id = $product->product_id;
                            $product_features_data_id = NULL;
                            if (isset($value[$feature_value['product_features_name']]) && $value[$feature_value['product_features_name']] != '')
                            {
                                $productfeatures_data = product_features_data::updateOrCreate(
                                    ['product_features_data_value' => $value[$feature_value['product_features_name']],
                                        'company_id' => Auth::user()->company_id,
                                        'product_features_id' => $feature_value['product_features_id'],
                                    ],
                                    [
                                        'product_features_id' => $feature_value['product_features_id'],
                                        'created_by' => Auth::User()->user_id,
                                        'company_id' => Auth::user()->company_id,
                                        'product_features_data_value' => $value[$feature_value['product_features_name']],
                                        'parent' => '0',
                                        'is_active' => '1'
                                    ]
                                );
                                $product_features_data_id = $productfeatures_data->product_features_data_id;
                            }


                            $relationship_product_insert = product_features_relationship::updateOrCreate(
                                ['product_id' => $product_id,
                                    'company_id' => Auth::user()->company_id
                                ],
                                ['created_by' => Auth::User()->user_id,
                                    'company_id' => Auth::user()->company_id,
                                    'product_id' => $product_id,
                                    $feature_value['html_id'] => $product_features_data_id,
                                    'deleted_at' => NULL,
                                    'deleted_by' => NULL,
                                ]
                            );
                        }
                    }


                }

                DB::commit();

            } catch (\Illuminate\Database\QueryException $e)
            {
                DB::rollback();
                return json_encode(array("Success" => "False", "Message" => $e->getMessage()));
            }

        }
        return json_encode(array("Success" => "True","Message" => "Products updated successfully"));

    } else {
        return json_encode(array("Success" => "False", "Message" => "No Row!"));
    }

}

    public function product_summary(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);


        $product_id = isset($request->product_id)?decrypt($request->product_id) : '';
        $batch_no = isset($request->batch_no)?decrypt($request->batch_no) : '';
        $summary_arr = [];

        $company_id = array(Auth::user()->company_id);
        $opening = 0;
        $store_list = company_relationship_tree::where('warehouse_id', '=', $company_id)
            ->with('company_profile')
            ->orderBy('company_relationship_trees_id','DESC')
            ->get();
        $store_id = $store_list->pluck('company_profile.company_id')->toArray();

        if(isset($store_id) && $store_id != '')
        {
            foreach ($store_id AS $k=>$v)
            {
                array_push($company_id,$v);
            }
        }

        //get product summery from purchase order detail
        $purchase_summery = purchase_order_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('purchase_order')
            ->with('product');

        if(isset($batch_no) && $batch_no != ''){
            $purchase_summery->where('unique_barcode',$batch_no);
        }
        $purchase_summery = $purchase_summery->get();

        if(isset($purchase_summery) && !empty($purchase_summery) && isset($purchase_summery[0]))
        {
            foreach ($purchase_summery AS $purchase_key => $purchase_value) {
                $summary_arr[] = [
                    'module_name' => 'Purchase Order',
                    'date' => $purchase_value['purchase_order']['po_date'],
                    'batch_no' => $purchase_value['unique_barcode'],
                    'in_qty' => $purchase_value['qty'] + $purchase_value['free_qty'],
                    'pending_receive_qty' => $purchase_value['pending_qty'],
                    'inv_po_no' => $purchase_value['purchase_order']['po_no'],
                    'supplier_company_name' => $purchase_value['purchase_order']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'],
                    'supplier_gst' => $purchase_value['purchase_order']['supplier_gstdetail']['supplier_gstin'],
                    'opening' => $opening,
                ];
            }
        }

        //get product summery from inward product details
        $inward_summary = inward_product_detail::where('product_id',$product_id)
                            ->whereNull('deleted_at')
                            ->whereIn('company_id',$company_id)
                            ->with('inward_stock.supplier_gstdetail')
                            ->with('product');

        if(isset($batch_no) && $batch_no != '')
        {
            $inward_summary->where('batch_no',$batch_no);
        }

        $inward_summary = $inward_summary->get();

        if(isset($inward_summary) && !empty($inward_summary) && isset($inward_summary[0]))
           {
               foreach ($inward_summary AS $inward_key=>$inward_value)
               {
                  $time =  explode(' ',$inward_value['created_at'])[1];
                    $summary_arr[] = [
                       'module_name' => 'Inward Stock',
                       'date' => $inward_value['inward_stock']['inward_date'].' '.$time,
                        'batch_no' => $inward_value['batch_no'],
                        'in_qty' => $inward_value['product_qty']+$inward_value['free_qty'],
                        'pending_qty' => $inward_value['pending_return_qty'],
                       'inv_invoice_no' => $inward_value['inward_stock']['invoice_no'],
                       'supplier_company_name' => $inward_value['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'],
                       'supplier_gst' => $inward_value['inward_stock']['supplier_gstdetail']['supplier_gstin'],
                       'opening' => $inward_value['product_qty']+$inward_value['free_qty']+$opening,
                    ];
                   $opening = $inward_value['product_qty']+$inward_value['free_qty'];
               }
           }



         //get product summery from sales order detail

        $sales_summery = sales_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('sales_bill')
            ->whereHas('sales_bill',function ($q){
                $q->where('sales_type','=',1);
            })
            ->with('product');
        //batchprice_master

            if(isset($batch_no) && $batch_no !='')
            {
                $sales_summery->with('batchprice_master')->whereHas('batchprice_master',function ($q) use($batch_no){
                   $q->where('batch_no',$batch_no);
                });
            }
            $sales_summery = $sales_summery->get();

           if(isset($sales_summery) && !empty($sales_summery) && isset($sales_summery[0]))
           {
            foreach ($sales_summery AS $sales_key => $sales_value)
            {
                if($sales_value['inwardids'] != '')
                {
                    $inwardids  = explode(',' ,substr($sales_value['inwardids'],0,-1));
                    $invoice_no = '';
                    $res_batch_no = '';
                    if(isset($inwardids) && $inwardids != '')
                    {
                        foreach ($inwardids AS $i_k =>$i_v)
                        {
                            $invoice_entry = inward_product_detail::where('inward_product_detail_id',$i_v)
                                                    ->with('inward_stock');

                            if(isset($batch_no) && $batch_no != '')
                            {
                                $invoice_entry->where('batch_no',$batch_no);
                            }
                                 $invoice_entry = $invoice_entry->first();

                            if(isset($invoice_entry) && !empty($invoice_entry))
                            {
                                if(isset($invoice_entry['inward_stock'])) {
                                    if ($invoice_no == '') {
                                        $invoice_no = $invoice_entry['inward_stock']['invoice_no'];
                                    } else {
                                        $invoice_no = $invoice_no . ',' . $invoice_entry['inward_stock']['invoice_no'];
                                    }
                                }
                                if ($res_batch_no == '') {
                                    $res_batch_no = $invoice_entry['batch_no'];
                                } else {
                                    $res_batch_no = $res_batch_no . ',' . $invoice_entry['batch_no'];
                                }
                            }
                        }
                    }
                }

                $summary_arr[] = [
                    'module_name' => 'Sales Bill',
                    'date' => $sales_value['sales_bill']['bill_date'],
                    'batch_no' => $batch_no,
                    'out_qty' => $sales_value['qty'],
                    'inv_bill_no' => $sales_value['sales_bill']['bill_no'],
                    'inv_inward_invoice_no' =>$invoice_no,
                    'opening' => $opening -$sales_value['qty']
                ];
                $opening = $opening-$sales_value['qty'];
            }
        }

        //get product summery from debit product detail
        $debit_summery = debit_product_detail::where('product_id',$product_id)
            ->whereIn('company_id',$company_id)
            ->whereNull('deleted_at')
            ->with('debit_note.inward_stock')
            ->with('price_master_batch_wise')
            ->with('product');

           if(isset($batch_no) && $batch_no != '')
           {
               $debit_summery->whereHas('price_master_batch_wise',function ($q) use ($batch_no)
               {
                    $q->where('batch_no',$batch_no);
               });
           }
           $debit_summery = $debit_summery->get();

        if(isset($debit_summery) && !empty($debit_summery) && isset($debit_summery[0]))
        {
            foreach ($debit_summery AS $debit_key => $debit_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Debit Note(Supplier Return)',
                    'date' => $debit_value['debit_note']['debit_date'],
                    'batch_no' => $debit_value['price_master_batch_wise']['batch_no'],
                    'out_qty' => $debit_value['return_qty'],
                    'inv_debit_no' => $debit_value['debit_note']['debit_no'],
                    'supplier_company_name' => $debit_value['debit_note']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'],
                    'supplier_gst' => $debit_value['debit_note']['supplier_gstdetail']['supplier_gstin'],
                    'inv_invoice_no' => $debit_value['debit_note']['inward_stock']['invoice_no'],
                    'opening' => $opening -$debit_value['return_qty']
                ];
                $opening = $opening-$debit_value['return_qty'];
            }
        }


        //get product summery from damage product detail
        $damage_summery = damage_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('damage_product.damage_types')
            ->with('inward_product_detail.inward_stock')
            ->with('product');

            if(isset($batch_no) && $batch_no != '')
            {
                $damage_summery->whereHas('inward_product_detail',function ($q) use ($batch_no)
                {
                    $q->where('batch_no',$batch_no);
                });
            }
            $damage_summery = $damage_summery->get();

        if(isset($damage_summery) && !empty($damage_summery) && isset($damage_summery[0]))
        {
            foreach ($damage_summery AS $damage_key => $damage_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Damage Product',
                    'date' => $damage_value['damage_product']['damage_date'],
                    'batch_no' => $damage_value['inward_product_detail']['batch_no'],
                    'out_qty' => $damage_value['product_damage_qty'],
                    'inv_damage_no' => $damage_value['damage_product']['damage_no'],
                    'inv_invoice_no' => $damage_value['inward_product_detail']['inward_stock']['invoice_no'],
                    'opening' => $opening - $damage_value['product_damage_qty']
                ];
                $opening = $opening-$damage_value['product_damage_qty'];
            }
        }

        //get product summery from return product
        $return_product_summary = returnbill_product::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('return_product_detail.return_bill.sales_bill');

        if(isset($batch_no) && $batch_no != '')
        {
            $return_product_summary->whereHas('price_master_batch_wise',function ($q) use ($batch_no)
            {
                $q->where('batch_no',$batch_no);
            });
        }
        $return_product_summary = $return_product_summary->get();


        if(isset($return_product_summary) && !empty($return_product_summary) && isset($return_product_summary[0]))
        {
            foreach ($return_product_summary AS $return_product_key => $return_product_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Sales Return Product',
                    'date' => $return_product_value['return_product_detail']['return_bill']['bill_date'],
                    'inv_bill_no' => $return_product_value['return_product_detail']['return_bill']['sales_bill']['bill_no'],
                    'out_qty' => $return_product_value['qty'],
                    'opening' => $opening +$return_product_value['restockqty']
                ];
                $opening = $opening+$return_product_value['restockqty'];
            }
        }

        //get product stock transfer
        $stock_transfer_summary = stock_transfer_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->whereNull('sales_products_detail_id')
            ->with('stock_transfer');

        if(isset($batch_no) && $batch_no != '')
        {
            $stock_transfer_summary->where('batch_no',$batch_no);
        }
        $stock_transfer_summary = $stock_transfer_summary->get();

        if(isset($stock_transfer_summary) && !empty($stock_transfer_summary) && isset($stock_transfer_summary[0]))
        {
            foreach ($stock_transfer_summary AS $stock_transfer_key => $stock_transfer_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Stock Transfer Product',
                    'date' => $stock_transfer_value['stock_transfer']['stock_transfer_date'],
                    'batch_no' => $stock_transfer_value['batch_no'],
                    'inv_stock_transfer_no' => $stock_transfer_value['stock_transfer']['stock_transfer_no'],
                    'out_qty' => $stock_transfer_value['product_qty'],
                    'pending_out_qty' => $stock_transfer_value['pending_rcv_qty'],
                    'opening' => $opening -$stock_transfer_value['product_qty']
                ];
                $opening = $opening-$stock_transfer_value['product_qty'];
            }
        }

        //get consignment bill

           $consign_product_detail = consign_products_detail::where('product_id',$product_id)
                    ->whereNull('deleted_at')
                    ->whereIn('company_id',$company_id)
                    ->with('consign_bill');

         if(isset($batch_no) && $batch_no != '')
         {
             $consign_product_detail->with('batchprice_master')
             ->whereHas('batchprice_master',function ($q) use($batch_no)
             {
                $q->where('batch_no',$batch_no);
             });
         }


        $consign_product_detail = $consign_product_detail->get();

        if(isset($consign_product_detail) && !empty($consign_product_detail) && isset($consign_product_detail[0]))
        {
            foreach ($consign_product_detail AS $consign_product_key => $consign_product_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Consign Bill',
                    'inv_bill_no' => $consign_product_value['consign_bill']['bill_no'],
                    'date' => $consign_product_value['consign_bill']['bill_date'],
                    'out_qty' => $consign_product_value['qty'],
                    'opening' => $opening -$consign_product_value['qty']
                ];
                $opening = $opening-$consign_product_value['qty'];
            }
        }

        //Franchiee Bill
        $franchiee_summary = sales_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('sales_bill')
            ->whereHas('sales_bill',function ($q){
                $q->where('sales_type','=',2);
            })
            ->with('product');

        if(isset($batch_no) && $batch_no != '')
        {
            $franchiee_summary->with('batchprice_master')
                ->whereHas('batchprice_master',function ($q) use($batch_no)
                {
                    $q->where('batch_no',$batch_no);
                });
        }
        $franchiee_summary = $franchiee_summary->get();

        if(isset($franchiee_summary) && !empty($franchiee_summary) && isset($franchiee_summary[0]))
        {
            foreach ($franchiee_summary AS $franchiee_key => $franchiee_value)
            {
                if($franchiee_value['inwardids'] != '')
                {
                    $inwardids  = explode(',' ,substr($franchiee_value['inwardids'],0,-1));
                    $invoice_no = '';
                    $res_batch_no = '';
                    if(isset($inwardids) && $inwardids != '')
                    {
                        foreach ($inwardids AS $i_k =>$i_v)
                        {
                            $invoice_entry = inward_product_detail::where('inward_product_detail_id',$i_v)
                                ->with('inward_stock')
                                ->first();

                            if(isset($invoice_entry) && !empty($invoice_entry))
                            {
                                if(isset($invoice_entry['inward_stock'])){
                                if($invoice_no == '')
                                {
                                    $invoice_no = $invoice_entry['inward_stock']['invoice_no'];
                                }
                                else{
                                    $invoice_no = $invoice_no.','.$invoice_entry['inward_stock']['invoice_no'];
                                }}

                                if($res_batch_no == '')
                                {
                                    $res_batch_no = $invoice_entry['batch_no'];
                                }
                                else{
                                    $res_batch_no = $res_batch_no.','.$invoice_entry['batch_no'];
                                }
                            }
                        }
                    }
                }
                $summary_arr[] = [
                    'module_name' => 'Franchiee Bill',
                    'date' => $franchiee_value['sales_bill']['bill_date'],
                    'batch_no' => $batch_no,
                    'out_qty' => $franchiee_value['qty'],
                    'inv_bill_no' => $franchiee_value['sales_bill']['bill_no'],
                    'inv_inward_invoice_no' =>$invoice_no,
                    'opening' => $opening -$franchiee_value['qty']
                ];
                $opening = $opening-$franchiee_value['qty'];
            }
        }


        $product_detail  = product::where('product_id',$product_id)->first();

        $instock_rec = price_master::select(DB::raw('sum(product_qty) AS qty'))->where('product_id',$product_id)->first();

        $instock = $instock_rec['qty'];

        //array_multisort( array_column($summary_arr, "date"), SORT_DESC, $summary_arr );

        array_multisort(array_map('strtotime',array_column($summary_arr,'date')),
            SORT_DESC,
            $summary_arr);

        return view('products::product/product_summary',compact('summary_arr','product_detail','instock'));

    }



    public function product_summary_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $data = $request->all();
        $from_date = '';
        $to_date = '';

        if(isset($data['query']) && $data['query'] != '')
        {
            if(isset($data['query']['from_date']) && $data['query']['from_date'] != '')
            {
                $from_date = date("Y-m-d",strtotime($data['query']['from_date']));
                $to_date = date("Y-m-d",strtotime($data['query']['to_date']));
            }
            $product_id = decrypt($data['query']['product_id']);
        }

        $batch_no = (isset($data['query']['batch_no']) && $data['query']['batch_no'] != '')?decrypt($data['query']['batch_no']) : '';

        if(isset($batch_no) && $batch_no != '')
        {
            if($product_id =='')
            {
                $p_id = inward_product_detail::where('batch_no',$batch_no)
                    ->where('company_id',Auth::user()->user_id)
                    ->whereNull('deleted_at')
                    ->select('product_id')->limit('1')->first();

                if(isset($p_id) && $p_id != '' && isset($p_id['product_id']) && $p_id['product_id'] != '')
                {
                    $product_id = $p_id['product_id'];
                }
            }
        }

        $summary_arr = [];

        $company_id = array(Auth::user()->company_id);
        $opening = 0;
        $store_list = company_relationship_tree::where('warehouse_id', '=', $company_id)
            ->with('company_profile')
            ->orderBy('company_relationship_trees_id','DESC')
            ->get();
        $store_id = $store_list->pluck('company_profile.company_id')->toArray();

        if(isset($store_id) && $store_id != '')
        {
            foreach ($store_id AS $k=>$v)
            {
                array_push($company_id,$v);
            }
        }
        $inward_product_sum['opening_qty'] = 0;

        $inward_product = inward_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('inward_stock')
            ->select(DB::raw('sum(pending_return_qty) AS opening_qty' ))
            ->with('product');


        if(isset($batch_no) && $batch_no != '')
        {
            $inward_product->where('batch_no',$batch_no);
        }

        if($from_date != '')
        {
            $inward_product->whereHas('inward_stock',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') < '$from_date' ");
            });
            $inward_product_sum = $inward_product->first();
        }


        if(isset($inward_product_sum['opening_qty']) && $inward_product_sum['opening_qty'] != '')
        {
            $opening = $inward_product_sum['opening_qty'];
        }

        //get product summery from purchase order detail

        $purchase_summery = purchase_order_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('purchase_order')
            ->with('product');

        if($from_date != '')
        {
            $purchase_summery->whereHas('purchase_order',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(po_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $purchase_summery->where('unique_barcode',$batch_no);
        }
        $purchase_summery = $purchase_summery->get();
        if(isset($purchase_summery) && !empty($purchase_summery) && isset($purchase_summery[0])) {
            foreach ($purchase_summery AS $purchase_key => $purchase_value) {
                $summary_arr[] = [
                    'module_name' => 'Purchase Order',
                    'date' => $purchase_value['purchase_order']['po_date'],
                    'batch_no' => $purchase_value['unique_barcode'],
                    'in_qty' => $purchase_value['qty'] + $purchase_value['free_qty'],
                    'pending_receive_qty' => $purchase_value['pending_qty'],
                    'inv_po_no' => $purchase_value['purchase_order']['po_no'],
                    'supplier_company_name' => $purchase_value['purchase_order']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'],
                    'supplier_gst' => $purchase_value['purchase_order']['supplier_gstdetail']['supplier_gstin'],
                    'opening' =>$opening
                ];

            }
        }


        //get product summery from inward product details
        $inward_summary = inward_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('inward_stock.supplier_gstdetail')
            ->with('product');

        if($from_date != '')
        {
            $inward_summary->whereHas('inward_stock',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $inward_summary->where('batch_no',$batch_no);
        }


        $inward_summary = $inward_summary->get();

        if(isset($inward_summary) && !empty($inward_summary) && isset($inward_summary[0]))
        {
            foreach ($inward_summary AS $inward_key=>$inward_value)
            {
                $time =  explode(' ',$inward_value['created_at'])[1];
                $summary_arr[] = [
                    'module_name' => 'Inward Stock',
                    'date' => $inward_value['inward_stock']['inward_date'].' '.$time,
                    'in_qty' => $inward_value['product_qty']+$inward_value['free_qty'],
                    'pending_qty' => $inward_value['pending_return_qty'],
                    'batch_no' => $inward_value['batch_no'],
                    'inv_invoice_no' => $inward_value['inward_stock']['invoice_no'],
                    'supplier_company_name' => $inward_value['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'],
                    'supplier_gst' => $inward_value['inward_stock']['supplier_gstdetail']['supplier_gstin'],
                    'opening' => $opening + $inward_value['product_qty']+$inward_value['free_qty']
                ];

                $opening = $opening + $inward_value['product_qty']+$inward_value['free_qty'];
            }

            //array_multisort( array_column($summary_arr, "ID"), SORT_DESC, $summary_arr );
        }


        //get product summery from sales order detail

        $sales_summery = sales_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('sales_bill')
            ->whereHas('sales_bill',function ($q){
                $q->where('sales_type','=',1);
            })
            ->with('product');
        if($from_date != '')
        {
            $sales_summery->whereHas('sales_bill',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no !='')
        {
            $sales_summery->with('batchprice_master')->whereHas('batchprice_master',function ($q) use($batch_no){
                $q->where('batch_no',$batch_no);
            });
        }
        $sales_summery = $sales_summery->get();

        if(isset($sales_summery) && !empty($sales_summery) && isset($sales_summery[0]))
        {
            foreach ($sales_summery AS $sales_key => $sales_value)
            {
                if($sales_value['inwardids'] != '')
                {
                    $inwardids  = explode(',' ,substr($sales_value['inwardids'],0,-1));
                    $invoice_no = '';
                    $res_batch_no = '';
                    if(isset($inwardids) && $inwardids != '')
                    {
                        foreach ($inwardids AS $i_k =>$i_v)
                        {
                            $invoice_entry = inward_product_detail::where('inward_product_detail_id',$i_v)
                                ->with('inward_stock');

                            if(isset($batch_no) && $batch_no != '')
                            {
                                $invoice_entry->where('batch_no',$batch_no);
                            }
                            $invoice_entry = $invoice_entry->first();

                            if(isset($invoice_entry) && !empty($invoice_entry))
                            {
                                if(isset($invoice_entry['inward_stock'])) {
                                    if ($invoice_no == '') {
                                        $invoice_no = $invoice_entry['inward_stock']['invoice_no'];
                                    } else {
                                        $invoice_no = $invoice_no . ',' . $invoice_entry['inward_stock']['invoice_no'];
                                    }
                                }
                                if ($res_batch_no == '') {
                                    $res_batch_no = $invoice_entry['batch_no'];
                                } else {
                                    $res_batch_no = $res_batch_no . ',' . $invoice_entry['batch_no'];
                                }
                            }
                        }
                    }
                }

                $summary_arr[] = [
                    'module_name' => 'Sales Bill',
                    'date' => $sales_value['sales_bill']['bill_date'],
                    'batch_no' => $res_batch_no,
                    'out_qty' => $sales_value['qty'],
                    'inv_bill_no' => $sales_value['sales_bill']['bill_no'],
                    'inv_inward_invoice_no' =>$invoice_no,
                    'opening' => $opening - $sales_value['qty']
                ];
                $opening = $opening - $sales_value['qty'];
            }
        }

        //get product summery from debit product detail
        $debit_summery = debit_product_detail::where('product_id',$product_id)
            ->whereIn('company_id',$company_id)
            ->whereNull('deleted_at')
            ->with('debit_note.inward_stock')
            ->with('price_master_batch_wise')
            ->with('product');

        if($from_date != '')
        {
            $debit_summery->whereHas('debit_note',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(debit_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $debit_summery->whereHas('price_master_batch_wise',function ($q) use ($batch_no)
            {
                $q->where('batch_no',$batch_no);
            });
        }
        $debit_summery = $debit_summery->get();

        if(isset($debit_summery) && !empty($debit_summery) && isset($debit_summery[0]))
        {
            foreach ($debit_summery AS $debit_key => $debit_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Debit Note(Supplier Return)',
                    'date' => $debit_value['debit_note']['debit_date'],
                    'batch_no' => $debit_value['price_master_batch_wise']['batch_no'],
                    'out_qty' => $debit_value['return_qty'],
                    'inv_debit_no' => $debit_value['debit_note']['debit_no'],
                    'supplier_company_name' => $debit_value['debit_note']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'],
                    'supplier_gst' => $debit_value['debit_note']['supplier_gstdetail']['supplier_gstin'],
                    'inv_invoice_no' => $debit_value['debit_note']['inward_stock']['invoice_no'],
                    'opening' => $opening - $debit_value['return_qty']
                ];
                $opening = $opening - $debit_value['return_qty'];
            }
        }


        //get product summery from damage product detail
        $damage_summery = damage_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('damage_product.damage_types')
            ->with('inward_product_detail.inward_stock')
            ->with('product');

        if($from_date != '')
        {
            $damage_summery->whereHas('damage_product',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $damage_summery->whereHas('inward_product_detail',function ($q) use ($batch_no)
            {
                $q->where('batch_no',$batch_no);
            });
        }
        $damage_summery = $damage_summery->get();

        if(isset($damage_summery) && !empty($damage_summery) && isset($damage_summery[0]))
        {
            foreach ($damage_summery AS $damage_key => $damage_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Damage Product',
                    'date' => $damage_value['damage_product']['damage_date'],
                    'batch_no' => $damage_value['inward_product_detail']['batch_no'],
                    'out_qty' => $damage_value['product_damage_qty'],
                    'inv_damage_no' => $damage_value['damage_product']['damage_no'],
                    'inv_invoice_no' => $damage_value['inward_product_detail']['inward_stock']['invoice_no'],
                    'opening' => $opening - $damage_value['product_damage_qty']
                ];
                $opening = $opening - $damage_value['product_damage_qty'];
            }
        }

        //get product summery from return product
        $return_product_summary = returnbill_product::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('return_product_detail.return_bill.sales_bill');

        if($from_date != '')
        {
            $return_product_summary->whereHas('return_bill',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }

        if(isset($batch_no) && $batch_no != '')
        {
            $return_product_summary->whereHas('price_master_batch_wise',function ($q) use ($batch_no)
            {
                $q->where('batch_no',$batch_no);
            });
        }
        $return_product_summary = $return_product_summary->get();

        if(isset($return_product_summary) && !empty($return_product_summary) && isset($return_product_summary[0]))
        {
            foreach ($return_product_summary AS $return_product_key => $return_product_value)
            {

                $summary_arr[] = [
                    'module_name' => 'Sales Return Product',
                    'date' => $return_product_value['return_product_detail']['return_bill']['bill_date'],
                    'inv_bill_no' => $return_product_value['return_product_detail']['return_bill']['sales_bill']['bill_no'],
                    'out_qty' => $return_product_value['qty'],
                    'opening' => $opening + $return_product_value['restockqty']
                ];
                $opening = $opening + $return_product_value['restockqty'];
            }
        }


        //get product stock transfer
        $stock_transfer_summary = stock_transfer_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->whereNull('sales_products_detail_id')
            ->with('stock_transfer');

        if($from_date != '')
        {
            $stock_transfer_summary->whereHas('stock_transfer',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(stock_transfer_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $stock_transfer_summary->where('batch_no',$batch_no);
        }
        $stock_transfer_summary = $stock_transfer_summary->get();

        if(isset($stock_transfer_summary) && !empty($stock_transfer_summary) && isset($stock_transfer_summary[0]))
        {
            foreach ($stock_transfer_summary AS $stock_transfer_key => $stock_transfer_value)
            {

                $summary_arr[] = [
                    'module_name' => 'Stock Transfer Product',
                    'date' => $stock_transfer_value['stock_transfer']['stock_transfer_date'],
                    'batch_no' => $stock_transfer_value['batch_no'],
                    'inv_stock_transfer_no' => $stock_transfer_value['stock_transfer']['stock_transfer_no'],
                    'out_qty' => $stock_transfer_value['product_qty'],
                    'pending_out_qty' => $stock_transfer_value['pending_rcv_qty'],
                    'opening' => $opening - $stock_transfer_value['product_qty']
                ];
                $opening = $opening - $stock_transfer_value['product_qty'];
            }
        }

        //get consignment bill

        $consign_product_detail  =  consign_products_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('consign_bill');


        if($from_date != '')
        {
            $consign_product_detail->whereHas('consign_bill',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $consign_product_detail->with('batchprice_master')
                ->whereHas('batchprice_master',function ($q) use($batch_no)
                {
                    $q->where('batch_no',$batch_no);
                });
        }

        $consign_product_detail = $consign_product_detail->get();

        if(isset($consign_product_detail) && !empty($consign_product_detail) && isset($consign_product_detail[0]))
        {
            foreach ($consign_product_detail AS $consign_product_key => $consign_product_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Consign Bill',
                    'inv_bill_no' => $consign_product_value['consign_bill']['bill_no'],
                    'date' => $consign_product_value['consign_bill']['bill_date'],
                    'out_qty' => $consign_product_value['qty'],
                    'opening' => $opening -$consign_product_value['qty']
                ];
                $opening = $opening-$consign_product_value['qty'];
            }
        }

        //Franchiee Bill
        $franchiee_summary = sales_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('sales_bill')
            ->whereHas('sales_bill',function ($q){
                $q->where('sales_type','=',2);
            })
            ->with('product');


        if($from_date != '')
        {
            $franchiee_summary->whereHas('sales_bill',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $franchiee_summary->with('batchprice_master')
                ->whereHas('batchprice_master',function ($q) use($batch_no)
                {
                    $q->where('batch_no',$batch_no);
                });
        }

        $franchiee_summary = $franchiee_summary->get();



        if(isset($franchiee_summary) && !empty($franchiee_summary) && isset($franchiee_summary[0]))
        {
            foreach ($franchiee_summary AS $franchiee_key => $franchiee_value)
            {
                if($franchiee_value['inwardids'] != '')
                {
                    $inwardids  = explode(',' ,substr($franchiee_value['inwardids'],0,-1));
                    $invoice_no = '';
                    $res_batch_no = '';
                    if(isset($inwardids) && $inwardids != '')
                    {
                        foreach ($inwardids AS $i_k =>$i_v)
                        {
                            $invoice_entry = inward_product_detail::where('inward_product_detail_id',$i_v)
                                ->with('inward_stock')
                                ->first();

                            if(isset($invoice_entry) && !empty($invoice_entry))
                            {
                                if(isset($invoice_entry['inward_stock'])){
                                    if($invoice_no == '')
                                    {
                                        $invoice_no = $invoice_entry['inward_stock']['invoice_no'];
                                    }
                                    else{
                                        $invoice_no = $invoice_no.','.$invoice_entry['inward_stock']['invoice_no'];
                                    }}

                                if($res_batch_no == '')
                                {
                                    $res_batch_no = $invoice_entry['batch_no'];
                                }
                                else{
                                    $res_batch_no = $res_batch_no.','.$invoice_entry['batch_no'];
                                }
                            }
                        }
                    }
                }

                $summary_arr[] = [
                    'module_name' => 'Franchiee Bill',
                    'date' => $franchiee_value['sales_bill']['bill_date'],
                    'batch_no' => $batch_no,
                    'out_qty' => $franchiee_value['qty'],
                    'inv_bill_no' => $franchiee_value['sales_bill']['bill_no'],
                    'inv_inward_invoice_no' =>$invoice_no,
                    'opening' => $opening -$franchiee_value['qty']
                ];
                $opening = $opening-$franchiee_value['qty'];
            }
        }


        $product_detail  = product::where('product_id',$product_id)->first();

        $instock_rec = price_master::select(DB::raw('sum(product_qty) AS qty'))->where('product_id',$product_id)->first();

        $instock = $instock_rec['qty'];

        //array_multisort( array_column($summary_arr, "date"), SORT_DESC, $summary_arr );
        array_multisort(array_map('strtotime',array_column($summary_arr,'date')),
            SORT_DESC,
            $summary_arr);


        return view('products::product/product_summary_data',compact('summary_arr','product_detail','instock'))->render();

    }

    public function product_summary_export(Request $request)
    {
       // Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data  = $request->all();
        $from_date  = '';
        $to_date  = '';
        if(isset($data['from_date']) && $data['from_date'] != '')
        {
            $from_date = date("Y-m-d",strtotime($data['from_date']));
            $to_date = date("Y-m-d",strtotime($data['to_date']));
        }
        $product_id = decrypt($data['product_id']);
        $batch_no = isset($request->batch_no)?decrypt($request->batch_no) : '';

        $summary_arr = [];

        $company_id = array(Auth::user()->company_id);
        $opening = 0;
        $store_list = company_relationship_tree::where('warehouse_id', '=', $company_id)
            ->with('company_profile')
            ->orderBy('company_relationship_trees_id','DESC')
            ->get();
        $store_id = $store_list->pluck('company_profile.company_id')->toArray();

        if(isset($store_id) && $store_id != '')
        {
            foreach ($store_id AS $k=>$v)
            {
                array_push($company_id,$v);
            }
        }
        $inward_product_sum['opening_qty'] = 0;

        $inward_product = inward_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('inward_stock')
            ->select(DB::raw('sum(pending_return_qty) AS opening_qty' ))
            ->with('product');

        if(isset($batch_no) && $batch_no != ''){
            $inward_product->where('batch_no',$batch_no);
        }

        if($from_date != '')
        {
            $inward_product->whereHas('inward_stock',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') < '$from_date' ");
            });
            $inward_product_sum = $inward_product->first();
        }
        if(isset($inward_product_sum['opening_qty']) && $inward_product_sum['opening_qty'] != ''){

            $opening = $inward_product_sum['opening_qty'];
        }





        //get product summery from purchase order detail

        $purchase_summery = purchase_order_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('purchase_order')
            ->with('product');

        if($from_date != '')
        {
            $purchase_summery->whereHas('purchase_order',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(po_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != ''){
            $purchase_summery->where('unique_barcode',$batch_no);
        }
        $purchase_summery = $purchase_summery->get();
        if(isset($purchase_summery) && !empty($purchase_summery) && isset($purchase_summery[0])) {
            foreach ($purchase_summery AS $purchase_key => $purchase_value) {
                $summary_arr[] = [
                    'module_name' => 'Purchase Order',
                    'date' => $purchase_value['purchase_order']['po_date'],
                    'batch_no' => $purchase_value['unique_barcode'],
                    'in_qty' => $purchase_value['qty'] + $purchase_value['free_qty'],
                    'pending_receive_qty' => $purchase_value['pending_qty'],
                    'inv_po_no' => $purchase_value['purchase_order']['po_no'],
                    'supplier_company_name' => $purchase_value['purchase_order']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'],
                    'supplier_gst' => $purchase_value['purchase_order']['supplier_gstdetail']['supplier_gstin'],
                    'opening' =>$opening
                ];

            }
        }


        //get product summery from inward product details
        $inward_summary = inward_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('inward_stock.supplier_gstdetail')
            ->with('product');

        if($from_date != '')
        {
            $inward_summary->whereHas('inward_stock',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $inward_summary->where('batch_no',$batch_no);
        }

        $inward_summary = $inward_summary->get();

        if(isset($inward_summary) && !empty($inward_summary) && isset($inward_summary[0]))
        {
            foreach ($inward_summary AS $inward_key=>$inward_value)
            {
                $time =  explode(' ',$inward_value['created_at'])[1];
                $summary_arr[] = [
                    'module_name' => 'Inward Stock',
                    'date' => $inward_value['inward_stock']['inward_date'].' '.$time,
                    'in_qty' => $inward_value['product_qty']+$inward_value['free_qty'],
                    'batch_no' => $inward_value['batch_no'],
                    'pending_qty' => $inward_value['pending_return_qty'],
                    'inv_invoice_no' => $inward_value['inward_stock']['invoice_no'],
                    'supplier_company_name' => $inward_value['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'],
                    'supplier_gst' => $inward_value['inward_stock']['supplier_gstdetail']['supplier_gstin'],
                    'opening' => $opening + $inward_value['product_qty']+$inward_value['free_qty']
                ];

                $opening = $opening + $inward_value['product_qty']+$inward_value['free_qty'];
            }
        }


        //get product summery from sales order detail

        $sales_summery = sales_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('sales_bill')
            ->whereHas('sales_bill',function ($q){
                $q->where('sales_type','=',1);
            })
            ->with('product');
        if($from_date != '')
        {
            $sales_summery->whereHas('sales_bill',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no !='')
        {
            $sales_summery->with('batchprice_master')->whereHas('batchprice_master',function ($q) use($batch_no){
                $q->where('batch_no',$batch_no);
            });
        }
        $sales_summery = $sales_summery->get();

        if(isset($sales_summery) && !empty($sales_summery) && isset($sales_summery[0]))
        {
            foreach ($sales_summery AS $sales_key => $sales_value)
            {
                if($sales_value['inwardids'] != '')
                {
                    $inwardids  = explode(',' ,substr($sales_value['inwardids'],0,-1));
                    $invoice_no = '';
                    $res_batch_no = '';
                    if(isset($inwardids) && $inwardids != '')
                    {
                        foreach ($inwardids AS $i_k =>$i_v)
                        {
                            $invoice_entry = inward_product_detail::where('inward_product_detail_id',$i_v)
                                ->with('inward_stock');

                            if(isset($batch_no) && $batch_no != '')
                            {
                                $invoice_entry->where('batch_no',$batch_no);
                            }
                            $invoice_entry = $invoice_entry->first();

                            if(isset($invoice_entry) && !empty($invoice_entry))
                            {
                                if(isset($invoice_entry['inward_stock'])) {
                                    if ($invoice_no == '') {
                                        $invoice_no = $invoice_entry['inward_stock']['invoice_no'];
                                    } else {
                                        $invoice_no = $invoice_no . ',' . $invoice_entry['inward_stock']['invoice_no'];
                                    }
                                }
                                if ($res_batch_no == '') {
                                    $res_batch_no = $invoice_entry['batch_no'];
                                } else {
                                    $res_batch_no = $res_batch_no . ',' . $invoice_entry['batch_no'];
                                }
                            }
                        }
                    }
                }

                $summary_arr[] = [
                    'module_name' => 'Sales Bill',
                    'date' => $sales_value['sales_bill']['bill_date'],
                    'batch_no' => $res_batch_no,
                    'out_qty' => $sales_value['qty'],
                    'inv_bill_no' => $sales_value['sales_bill']['bill_no'],
                    'inv_inward_invoice_no' =>$invoice_no,
                    'opening' => $opening - $sales_value['qty']
                ];
                $opening = $opening - $sales_value['qty'];
            }
        }

        //get product summery from debit product detail
        $debit_summery = debit_product_detail::where('product_id',$product_id)
            ->whereIn('company_id',$company_id)
            ->whereNull('deleted_at')
            ->with('debit_note.inward_stock')
            ->with('price_master_batch_wise')
            ->with('product');

        if($from_date != '')
        {
            $debit_summery->whereHas('debit_note',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(debit_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $debit_summery->whereHas('price_master_batch_wise',function ($q) use ($batch_no)
            {
                $q->where('batch_no',$batch_no);
            });
        }
        $debit_summery = $debit_summery->get();

        if(isset($debit_summery) && !empty($debit_summery) && isset($debit_summery[0]))
        {
            foreach ($debit_summery AS $debit_key => $debit_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Debit Note(Supplier Return)',
                    'date' => $debit_value['debit_note']['debit_date'],
                    'batch_no' => $debit_value['price_master_batch_wise']['batch_no'],
                    'out_qty' => $debit_value['return_qty'],
                    'inv_debit_no' => $debit_value['debit_note']['debit_no'],
                    'supplier_company_name' => $debit_value['debit_note']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'],
                    'supplier_gst' => $debit_value['debit_note']['supplier_gstdetail']['supplier_gstin'],
                    'inv_invoice_no' => $debit_value['debit_note']['inward_stock']['invoice_no'],
                    'opening' => $opening - $debit_value['return_qty']
                ];
                $opening = $opening - $debit_value['return_qty'];
            }
        }


        //get product summery from damage product detail
        $damage_summery = damage_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('damage_product.damage_types')
            ->with('inward_product_detail.inward_stock')
            ->with('product');

        if($from_date != '')
        {
            $damage_summery->whereHas('damage_product',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $damage_summery->whereHas('inward_product_detail',function ($q) use ($batch_no)
            {
                $q->where('batch_no',$batch_no);
            });
        }
        $damage_summery = $damage_summery->get();

        if(isset($damage_summery) && !empty($damage_summery) && isset($damage_summery[0]))
        {
            foreach ($damage_summery AS $damage_key => $damage_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Damage Product',
                    'date' => $damage_value['damage_product']['damage_date'],
                    'batch_no' => $damage_value['inward_product_detail']['batch_no'],
                    'out_qty' => $damage_value['product_damage_qty'],
                    'inv_damage_no' => $damage_value['damage_product']['damage_no'],
                    'inv_invoice_no' => $damage_value['inward_product_detail']['inward_stock']['invoice_no'],
                    'opening' => $opening - $damage_value['product_damage_qty']
                ];
                $opening = $opening - $damage_value['product_damage_qty'];
            }
        }

        //get product summery from return product
        $return_product_summary = returnbill_product::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('return_product_detail.return_bill.sales_bill');

        if($from_date != '')
        {
            $return_product_summary->whereHas('return_bill',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $return_product_summary->whereHas('price_master_batch_wise',function ($q) use ($batch_no)
            {
                $q->where('batch_no',$batch_no);
            });
        }
        $return_product_summary = $return_product_summary->get();

        if(isset($return_product_summary) && !empty($return_product_summary) && isset($return_product_summary[0]))
        {
            foreach ($return_product_summary AS $return_product_key => $return_product_value)
            {

                $summary_arr[] = [
                    'module_name' => 'Sales Return Product',
                    'date' => $return_product_value['return_product_detail']['return_bill']['bill_date'],
                    'inv_bill_no' => $return_product_value['return_product_detail']['return_bill']['sales_bill']['bill_no'],
                    'out_qty' => $return_product_value['qty'],
                    'opening' => $opening + $return_product_value['restockqty']
                ];
                $opening = $opening + $return_product_value['restockqty'];
            }
        }

        //get product stock transfer
        $stock_transfer_summary = stock_transfer_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->whereNull('sales_products_detail_id')
            ->with('stock_transfer');

        if($from_date != '')
        {
            $stock_transfer_summary->whereHas('stock_transfer',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(stock_transfer_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $stock_transfer_summary->where('batch_no',$batch_no);
        }
        $stock_transfer_summary = $stock_transfer_summary->get();

        if(isset($stock_transfer_summary) && !empty($stock_transfer_summary) && isset($stock_transfer_summary[0]))
        {
            foreach ($stock_transfer_summary AS $stock_transfer_key => $stock_transfer_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Stock Transfer Product',
                    'date' => $stock_transfer_value['stock_transfer']['stock_transfer_date'],
                    'batch_no' => $stock_transfer_value['batch_no'],
                    'inv_stock_transfer_no' => $stock_transfer_value['stock_transfer']['stock_transfer_no'],
                    'out_qty' => $stock_transfer_value['product_qty'],
                    'pending_out_qty' => $stock_transfer_value['pending_rcv_qty'],
                    'opening' => $opening - $stock_transfer_value['product_qty']
                ];
                $opening = $opening - $stock_transfer_value['product_qty'];
            }
        }

        //get consignment bill

        $consign_product_detail  =  consign_products_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('consign_bill');
        if($from_date != '')
        {
            $consign_product_detail->whereHas('consign_bill',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $consign_product_detail->with('batchprice_master')
                ->whereHas('batchprice_master',function ($q) use($batch_no)
                {
                    $q->where('batch_no',$batch_no);
                });
        }
        $consign_product_detail = $consign_product_detail->get();

        if(isset($consign_product_detail) && !empty($consign_product_detail) && isset($consign_product_detail[0]))
        {
            foreach ($consign_product_detail AS $consign_product_key => $consign_product_value)
            {
                $summary_arr[] = [
                    'module_name' => 'Consign Bill',
                    'inv_bill_no' => $consign_product_value['consign_bill']['bill_no'],
                    'date' => $consign_product_value['consign_bill']['bill_date'],
                    'out_qty' => $consign_product_value['qty'],
                    'opening' => $opening -$consign_product_value['qty']
                ];
                $opening = $opening-$consign_product_value['qty'];
            }
        }

        //Franchiee Bill
        $franchiee_summary = sales_product_detail::where('product_id',$product_id)
            ->whereNull('deleted_at')
            ->whereIn('company_id',$company_id)
            ->with('sales_bill')
            ->whereHas('sales_bill',function ($q){
                $q->where('sales_type','=',2);
            })
            ->with('product');

        if($from_date != '')
        {
            $franchiee_summary->whereHas('sales_bill',function($q) use($from_date,$to_date)
            {
                $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            });
        }
        if(isset($batch_no) && $batch_no != '')
        {
            $franchiee_summary->with('batchprice_master')
                ->whereHas('batchprice_master',function ($q) use($batch_no)
                {
                    $q->where('batch_no',$batch_no);
                });
        }

        $franchiee_summary = $franchiee_summary->get();

        if(isset($franchiee_summary) && !empty($franchiee_summary) && isset($franchiee_summary[0]))
        {
            foreach ($franchiee_summary AS $franchiee_key => $franchiee_value)
            {
                if($franchiee_value['inwardids'] != '')
                {
                    $inwardids  = explode(',' ,substr($franchiee_value['inwardids'],0,-1));
                    $invoice_no = '';
                    $res_batch_no = '';
                    if(isset($inwardids) && $inwardids != '')
                    {
                        foreach ($inwardids AS $i_k =>$i_v)
                        {
                            $invoice_entry = inward_product_detail::where('inward_product_detail_id',$i_v)
                                ->with('inward_stock')
                                ->first();

                            if(isset($invoice_entry) && !empty($invoice_entry))
                            {
                                if(isset($invoice_entry['inward_stock'])){
                                    if($invoice_no == '')
                                    {
                                        $invoice_no = $invoice_entry['inward_stock']['invoice_no'];
                                    }
                                    else{
                                        $invoice_no = $invoice_no.','.$invoice_entry['inward_stock']['invoice_no'];
                                    }}

                                if($res_batch_no == '')
                                {
                                    $res_batch_no = $invoice_entry['batch_no'];
                                }
                                else{
                                    $res_batch_no = $res_batch_no.','.$invoice_entry['batch_no'];
                                }
                            }
                        }
                    }
                }

                $summary_arr[] = [
                    'module_name' => 'Franchiee Bill',
                    'date' => $franchiee_value['sales_bill']['bill_date'],
                    'batch_no' => $batch_no,
                    'out_qty' => $franchiee_value['qty'],
                    'inv_bill_no' => $franchiee_value['sales_bill']['bill_no'],
                    'inv_inward_invoice_no' =>$invoice_no,
                    'opening' => $opening -$franchiee_value['qty']
                ];
                $opening = $opening-$franchiee_value['qty'];
            }
        }







        //array_multisort( array_column($summary_arr, "date"), SORT_DESC, $summary_arr );

        array_multisort(array_map('strtotime',array_column($summary_arr,'date')),
            SORT_DESC,
            $summary_arr);




        return Excel::download(new product_summary($summary_arr),'Product_summary.xlsx');
    }



    public function insert_product_pricemaster(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        try {
            DB::beginTransaction();
            $product = product::doesntHave('product_price_master')->get();

            if (isset($product) && $product != '') {
                foreach ($product AS $product_key => $product_value) {
                    price_master::insert(
                        array(
                            'company_id' => Auth::user()->company_id,
                            'product_id' => $product_value['product_id'],
                            'product_qty' => 0,
                            'product_mrp' => $product_value['product_mrp'],
                            'offer_price' => $product_value['offer_price'],
                            'wholesaler_price' => $product_value['wholesale_price'],
                            'sell_price' => $product_value['selling_price'],
                            'selling_gst_percent' => $product_value['sell_gst_percent'],
                            'selling_gst_amount' => $product_value['sell_gst_amount'],
                            'is_active' => 1,
                            'created_by' => Auth::user()->user_id
                        )
                    //DB::table('users')->insert(

                    );
                }
            }
            DB::commit();
            return json_encode(array("Success"=>"True","Message"=>"Data Updated!"));
        }catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return json_encode(array("Success" => "False", "Message" => $e->getMessage()));
        }
    }

}
