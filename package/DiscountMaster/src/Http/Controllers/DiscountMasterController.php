<?php
namespace Retailcore\DiscountMaster\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Retailcore\DiscountMaster\Models\discount_master;
use Retailcore\Products\Models\product\price_master;

use Retailcore\Products\Models\product\brand;
use Retailcore\Products\Models\product\colour;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\product_image;
use Retailcore\Products\Models\product\category;
use Retailcore\Products\Models\product\product_export;
use Retailcore\Products\Models\product\size;
use Retailcore\Products\Models\product\subcategory;
use Retailcore\Products\Models\product\uqc;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Products\Models\product\ProductFeatures;
use Auth;
use DB;
use Log;
class DiscountMasterController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $product   =  array();
        $message   =  '';
        return view('discountmaster::discount_master',compact('product','message'));
    }
    public function view_flatproducts()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $product   =  array();
        $message   =  '';

        $discount_master   =   discount_master::where('company_id',Auth::user()->company_id)
                                                ->where('deleted_at',NULL)
                                                ->paginate(10);

        $product_features =  ProductFeatures::getproduct_feature('');


        foreach ($discount_master AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $discount_master[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }


        return view('discountmaster::view_flatproducts',compact('discount_master'));
    }
    public function datewise_flatdiscount_detail(request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $product   =  array();
        $message   =  '';

        $discount_master   =   discount_master::where('company_id',Auth::user()->company_id)
                                                ->where('deleted_at',NULL)
                                                ->paginate(10);

        $product_features =  ProductFeatures::getproduct_feature('');


        foreach ($discount_master AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $discount_master[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }


        return view('discountmaster::view_flatproducts_data',compact('discount_master'));
    }
    public function flatdiscount_delete(request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $userId = Auth::User()->user_id;


     try {
        DB::beginTransaction();

                    $discount_delete =  discount_master::where('discount_master_id', $request->deleted_id)
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


        if($discount_delete)
        {
            return json_encode(array("Success"=>"True","Message"=>"Flat Discount Scheme has been successfully deleted.!"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong!"));
        }

    }
    public function search_flatproduct_data(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
           $data = $request->all();

            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = isset($data['query']) ? $data['query']  : '';
            $todaydate = date("Y-m-d");

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


            $product = product::where('deleted_at','=',NULL)->whereIn('item_type',array(1,3))->where('company_id',Auth::user()->company_id);

            if(isset($query) && $query != '' && $query['product_name'] != '')
            {
                $product->where('product_name', 'like', '%'.$query['product_name'].'%');
            }
            if(isset($query) && $query != '' && $query['from_barcode'] != '' && $query['to_barcode']!='')
            {
                $product->whereRaw('product_system_barcode between '.$query['from_barcode'].' and '.$query['to_barcode'].'');
                //$product->orWhere('supplier_barcode', 'like', '%'.$query['barcode'].'%');
            }
            if(isset($query) && $query != '' && $query['from_barcode'] != '' && $query['to_barcode']=='')
            {
                $product->where('product_system_barcode', 'like', '%'.$query['from_barcode'].'%');
                $product->orWhere('supplier_barcode', 'like', '%'.$query['from_barcode'].'%');
            }
            if(isset($query) && $query != '' && $query['to_barcode'] != '' && $query['to_barcode']=='')
            {
                $product->where('product_system_barcode', 'like', '%'.$query['to_barcode'].'%');
                $product->orWhere('supplier_barcode', 'like', '%'.$query['to_barcode'].'%');
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

            $product = $product->with([
                    'inward_product_detail' => function($fquery) {
                         $fquery->select('offer_price','product_id');
                         $fquery->orderBy('inward_product_detail_id','DESC');
                    }
                    ])->with([
                    'discount_master' => function($ffquery) use($todaydate){
                         $ffquery->select('discount_master_id','product_id');
                         $ffquery->whereRaw("STR_TO_DATE(discount_masters.to_date,'%d-%m-%Y') > '$todaydate'");;
                    }
                    ])
                    ->orderBy($sort_by,$sort_type)->get();

                    $product_features =  ProductFeatures::getproduct_feature('');
                     foreach($product as $pp=>$pval)
                     {
                        // echo '<pre>';
                        // print_r($bval);
                      if(isset($pval['product_features_relationship']) && $pval['product_features_relationship'] != '')
                      {
                          foreach ($product_features AS $kk => $vv)
                          {
                              $html_id = $vv['html_id'];

                              if($pval['product_features_relationship'][$html_id] != '' && $pval['product_features_relationship'][$html_id] != NULL)
                              {
                                  $nm =  product::feature_value($vv['product_features_id'],$pval['product_features_relationship'][$html_id]);
                                  $pval[$html_id] =$nm;
                              }
                          }
                      }
                    }

            // echo '<pre>';
            // print_r($product);
            // exit;
                    $barcode  = '';
                    $message  =  '';
                    foreach($product as $productkey=>$productvalue)
                    {
                        //echo $productvalue['discount_master'][0];
                        //sizeof($productvalue['discount_master'])!=0
                         if($productvalue['discount_master']!='')
                         {
                            $productid      =   $productvalue['discount_master']['product_id'];
                            $barcodeseries  =   product::select('product_system_barcode')->find($productid);
                            $barcode        .=   $barcodeseries['product_system_barcode'].' ,';
                         }
                    }
                    if($barcode != '')
                    {
                        $message    =   "These Barcodes '".$barcode."' already exist in the same offer";
                    }



            return view('discountmaster::searchFlatProduct_area', compact('product','message'));
    }


    public function flatdiscount_create(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;


        $created_by = $userId;


        try {
            DB::beginTransaction();




        $productdetail     =    array();



         foreach($data[0] AS $productkey=>$productvalue)
          {

                      $productdetail['product_id']                           =    $productvalue['product_id'];
                      $productdetail['discount_percent']                     =    $productvalue['discount_percent'];
                      $productdetail['discount_amount']                      =    $productvalue['discount_amount'];
                      $productdetail['mrp']                                  =    $productvalue['mrp'];
                      $productdetail['offer_price']                          =    $productvalue['offer_price'];
                      $productdetail['from_date']                            =    $data[1]['from_date'];
                      $productdetail['to_date']                              =    $data[1]['to_date'];

                      $productdetail['created_by']                           =     Auth::User()->user_id;

                    $billproductdetail = discount_master::updateOrCreate(
                       ['company_id'=>$company_id,'discount_master_id'=>'',],
                       $productdetail);


     }

     DB::commit();
      } catch (\Illuminate\Database\QueryException $e)
      {
          DB::rollback();
          return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
      }


        if($billproductdetail)
        {

            return json_encode(array("Success"=>"True","Message"=>"Flat Discount Master applied successfully!","url"=>"discount_master"));

        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
        //return back()->withInput();

    }



}
