<?php

namespace Retailcore\ecommerce\Http\Controllers\ecommerce;

// use App\BarcodePrinting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Products\Models\product\product_image;
use Retailcore\Products\Models\product\product_features_data;
use Retailcore\Products\Models\product\product_features_relationship;
use Retailcore\ecommerce\Models\ecommerce\cart;
use Auth;
use DB;
use Hash;
use Illuminate\Validation\Rule;
use Retailcore\Customer\Models\customer\customer;
use Illuminate\Support\Facades\Schema;
use Log;
class ecommerceController extends Controller
{

    public function index()
    {

    }

    public function fetch_Main_Navigation(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $Top_Nav  =   ProductFeatures::select('feature_url','product_features_id','product_features_name','feature_type')
        ->where('is_active','1')->whereNull('deleted_by')->where('feature_location','1')->orderby('ordering','ASC')->get();

        $Main_Nav  =   ProductFeatures::select('feature_url','product_features_id','product_features_name','feature_type')
        ->where('is_active','1')->whereNull('deleted_by')->where('feature_location','2')
        ->with('product_features_data')->orderby('ordering','ASC')->get();

        $School_list    =   ProductFeatures::select('feature_url','product_features_id','product_features_name','feature_type')
        ->where('is_active','1')->whereNull('deleted_by')->where('feature_type','1')->with('product_features_data')->orderby('ordering','ASC')->get();

        $homeAbout  =   ProductFeatures::select('feature_content','product_features_id')->where('feature_url','about-us')->first();

        return json_encode(array("Success"=>"True","Data"=>$Main_Nav,"Top_Nav"=>$Top_Nav,"School_list"=>$School_list,"homeAbout"=>$homeAbout));
    }

    public function searchSchool(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data       =   $request->all();
        $searchval  =   $data["search_val"];

        if($searchval!='')
        {
            $result     =   product_features_data::where('product_features_data_value', 'LIKE', "%$searchval%")
            ->where('parent',0)->where('is_active',1)->where('product_features_id',1)->get();

            if(!empty($result))
            {
                foreach($result as $key=>$value)
                {
                      $json[] = $value['product_features_data_value'];
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

    public function searchSchool_header(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $searchval    =  $request->search_val;

        if($request->search_val !='')
        {

            $json = [];
            $result = product_features_data::where('product_features_data_value', 'LIKE', "%$searchval%")
                     ->with('product_features')
                     ->where('parent',0)
                     ->where('is_active',1)
                     ->where('product_features_id',1)
                     ->get();



             if(sizeof($result) != 0)
            {

                foreach($result as $productkey=>$productvalue){


                      $json[$productkey]['label'] = $productvalue['product_features_data_value'];
                      $json[$productkey]['label_url'] = $productvalue['product_features_data_url'];
                      $json[$productkey]['feature_url'] = $productvalue['product_features']['feature_url'];


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

    public function fetch_schools(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data           =   $request->all();
        $searchSchool   =   $data["searchSchool"];
        $result     =   product_features_data::where('product_features_data_value', $searchSchool)->with('product_features')->get();
        return json_encode(array("Success"=>"True","School_list"=>$result));
    }

    public function getPageType(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();
        $result =   ProductFeatures::select('feature_type','product_features_id')->where('feature_url',$data['level1'])->get();
        return json_encode(array("Success"=>"True","result"=>$result));
    }

    public function getSchoolHeader(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();

        $page_type  =   $data['page_type'];
        $level1     =   $data['level1'];
        $level2     =   $data['level2'];
        $level3     =   $data['level3'];
        $level4     =   $data['level4'];
        $level5     =   $data['level5'];
        $sortby     =   $data['selectProductSort'];

        if($page_type==1)
        {
            $result     =   product_features_data::where('product_features_data_url', $level2)->with('product_features')->first();

        $categories     =   product_features_data::select('product_features_data_value','product_features_data_url','product_features_data_image','feature_content','product_features_data_id')->where('parent',$result->product_features_data_id)->where('is_active','1')->get();

        $search_attr    =   ProductFeatures::select('product_features_name','product_features_id','html_id','feature_url')->where('feature_type',1)
        ->where('feature_location',0)->where('is_active',1)->with('product_features_data')->orderby('ordering','ASC')->whereNull('deleted_at')->get();

        if($sortby=='price:asc')
        {
            $orderby    =   'offer_price';
            $order_val    =  'ASC';
        }
        elseif($sortby=='price:desc')
        {
            $orderby    =   'offer_price';
            $order_val    =  'DESC';
        }
        elseif($sortby=='name:asc')
        {
            $orderby    =   'product_name';
            $order_val    =  'ASC';
        }
        elseif($sortby=='name:desc')
        {
            $orderby    =   'product_name';
            $order_val    =  'DESC';
        }
        else
        {
            $orderby    =   'offer_price';
            $order_val    =  'DESC';
        }

        if($sortby=='in:stock')
        {
            $show   =   1;
        }
        else
        {
            $show   =   0;
        }

        if($level3!='')
        {
            $getFeature_id   =  product_features_data::select('product_features_id','product_features_data_id')->where('product_features_data_url',$level3)->first();
            $getFieldname    =  ProductFeatures::select('html_id')->where('product_features_id',$getFeature_id->product_features_id)->first();

            $html_id          =   $getFieldname->html_id;

            $products   =   product_features_relationship::where($html_id,$getFeature_id->product_features_data_id)->whereNull('deleted_by')
            ->with('product')->with('product_image')->get();
            // ->whereHas('product',function ($q){
            //     $q->with('price_master')
            //     ->whereHas('price_master',function ($q){
            //           $q->whereRaw('sum(product_qty)>0');
            //       });
            //    })


            $showingResult   =   product_features_relationship::select('product_id')->where($html_id,$getFeature_id->product_features_data_id)->whereNull('deleted_by')->orderby($orderby,$order_val)->count('product_id');

            $product_detail     =   product::where('product_id',$level5)->with('product_image','pricemaster')->get();
        }
        else
        {
            $products   =   array();
            $showingResult  =   array();
            $product_detail     =   array();
        }

        // echo '<pre>'; print_r($products); exit;

        // $products   =   product::select('product_id','product_name','offer_price','sku_code')->whereNull('deleted_by')
        // ->with('product_image','pricemaster')->orderby($orderby,$order_val)->get();



        $breadcrumb_1     =     product_features_data::select('product_features_data_value')->where('product_features_data_url',$level2)->get();
        $breadcrumb_2     =     product_features_data::select('product_features_data_value')->where('product_features_data_url',$level3)->get();
        $breadcrumb_3     =     product::select('product_name')->where('product_id',$level5)->get();

        return json_encode(array("Success"=>"True","School"=>$result,"categories"=>$categories,"SearchAttr"=>$search_attr,"products"=>$products,"product_detail"=>$product_detail,"breadcrumb_1"=>$breadcrumb_1,"breadcrumb_2"=>$breadcrumb_2,"breadcrumb_3"=>$breadcrumb_3,"show"=>$show,"showingResult"=>$showingResult));
        }


    }

    public function addtocart(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        session_start();
        $data   =   $request->all();

        $product_id         =   $data['product_id'];
        $price_master_id    =   $data['price_master_id'];
        $request_quantity   =   $data['request_quantity'];
        $product_mrp        =   $data['product_mrp'];

        $session_id         =   session_id();

        $product    =   cart::select('*')->where('session_id',$session_id)->where('product_id',$product_id)->first();

        if(is_null($product))
        {
            $total_mrp  =   $request_quantity * $product_mrp;
            $cart = cart::updateOrCreate(
                ['cart_id' => '',
                ],
                [
                    'session_id' =>$session_id,
                    'product_id' =>$product_id,
                    'price_master_id' =>$price_master_id,
                    'request_quantity' =>$request_quantity,
                    'product_mrp' => $product_mrp,
                    'total_mrp' => $total_mrp,
                ]
            );
        }
        else
        {
            $cart_id    =   $product->cart_id;
            $qty        =   $product->request_quantity;
            $new_qty    =   $qty + $request_quantity;
            $total_mrp  =   $new_qty * $product_mrp;

            $cart = cart::updateOrCreate(
                ['cart_id' => $cart_id,
                ],
                [
                    'session_id' =>$session_id,
                    'product_id' =>$product_id,
                    'price_master_id' =>$price_master_id,
                    'request_quantity' =>$new_qty,
                    'product_mrp' => $product_mrp,
                    'total_mrp' => $total_mrp,
                ]
            );

        }

        return json_encode(array("Success"=>"True","url"=>'mycart'));
    }

    public function getCartItems(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        session_start();
        $session_id     =   session_id();

        $result     =   cart::where('session_id',$session_id)->with('product','price_master','product_image')->get();
        $cartTotalAmount    =   cart::select('total_mrp')->where('session_id',$session_id)->sum('total_mrp');
        $cartCount    =   cart::select('cart_id')->where('session_id',$session_id)->count('cart_id');

        return json_encode(array("Data"=>$result,"cartTotalAmount"=>$cartTotalAmount,"cartCount"=>$cartCount));
    }

    public function getTotalCartQty(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        session_start();
        $session_id     =   session_id();

        $result     =   cart::where('session_id',$session_id)->sum('total_mrp');

        print_r($result);
    }

    public function ajaxProducts(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data           =   $request->all();

        $level1         =   $data['level1'];
        $level2         =   $data['level2'];
        $level3         =   $data['level3'];
        $level4         =   $data['level4'];
        $level5         =   $data['level5'];

        $level2_name    =   product_features_data::select('product_features_data_id')->where('product_features_data_url',$level2)->first();
        $level3_name    =   product_features_data::select('product_features_data_id')->where('product_features_data_url',$level3)->first();

        $dynamic_Schools    =   $level2_name->product_features_data_id;
        $dynamic_Gender     =   $level3_name->product_features_data_id;

        $searchAttr     =   $data['searchAttr_Values'];     /////////////////////// SEARCH VALUES IN (,)

        $exp            =   explode(',',$searchAttr);

        $val            =   '';

        $result   =   product_features_relationship::whereNull('deleted_by')
        ->where('dynamic_Schools',$dynamic_Schools)
        ->where('dynamic_Gender',$dynamic_Gender);

        if(!empty($searchAttr))
        {
            foreach($exp as $key=>$value)
            {
                $val_exp    =   explode('__',$value);
                $result->where($val_exp[0],$val_exp[1]);
            }
        }

        $ajaxProducts   =   $result->with('product')->with('product_image')->get();

        return json_encode(array("Success"=>"True","ajaxProducts"=>$ajaxProducts));
    }

    public function addtoBasket(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        session_start();
        $session_id         =   session_id();
        $data               =   $request->all();
        $product_id         =   $data['product_id'];
        $request_quantity   =   1;

        $product    =   cart::select('cart_id','request_quantity','product_id')->where('session_id',$session_id)
        ->where('product_id',$product_id)->first();

        if(is_null($product))
        {
            $cart = cart::updateOrCreate(
                ['cart_id' => '',
                ],
                [
                    'session_id' =>$session_id,
                    'product_id' =>$product_id,
                    'request_quantity' =>$request_quantity,
                ]
            );
        }
        else
        {
            $cart_id    =   $product->cart_id;
            $qty        =   $product->request_quantity;
            $new_qty    =   $qty + $request_quantity;

            $cart = cart::updateOrCreate(
                ['cart_id' => $cart_id,
                ],
                [
                    'session_id' =>$session_id,
                    'product_id' =>$product_id,
                    'request_quantity' =>$new_qty,
                ]
            );
        }

        $myBasket   =   cart::select('cart_id','request_quantity','product_id')
        ->where('session_id',$session_id)
        ->with('product','product_image')
        ->groupBy('product_id')->orderBy('cart_id','DESC')
        ->get();

        return json_encode(array("Success"=>"True","myBasket"=>$myBasket));
    }

    public function myBasket_items(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        session_start();
        $session_id     =   session_id();

        $result     =   cart::where('session_id',$session_id)->with('product','product_image')->orderBy('cart_id','DESC')->get();
        $basketCount    =   cart::select('cart_id')->where('session_id',$session_id)->count('cart_id');

        return json_encode(array("Success"=>"True","Data"=>$result,"basketCount"=>$basketCount));
    }

    public function removeBasket(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        session_start();
        $session_id     =   session_id();

        $data           =   $request->all();
        $cart_id        =   $data['cart_id'];

        $cart = cart::where('session_id',$session_id)->where('cart_id',$cart_id)->delete();

        return json_encode(array("Success"=>"True"));
    }

    public function customer_register_form(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data                   =   $request->all();

        $company_id             =   1;

        $customerdata =  array();

        parse_str($data['formdata'], $customerdata);

        $customerdata = preg_replace('/\s+/', ' ', $customerdata);

        $validate_error = \Validator::make($customerdata,
            [
                'customer_mobile' => [Rule::unique('customers')->ignore($customerdata['customer_id'], 'customer_id')->whereNull('deleted_at')->whereNotNull('customer_mobile')],
                'customer_email' => [Rule::unique('customers')->ignore($customerdata['customer_id'], 'customer_id')->whereNull('deleted_at')->whereNotNull('customer_email')],
            ]);

        if($validate_error-> fails())
        {
            return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>$validate_error->messages()));
            exit;
        }

        try{
            DB::beginTransaction();

            $password               =   $customerdata['password']==''?'':bcrypt($customerdata['password']);
            $encrypt_password       =   $customerdata['encrypt_password']==''?'':bcrypt($customerdata['encrypt_password']);

        $customer = customer::updateOrCreate(
            ['customer_id' => $customerdata['customer_id'], 'company_id'=>$company_id,],
            [
                'company_id'=>$company_id,
                'customer_name' => (isset($customerdata['customer_name'])?$customerdata['customer_name'] : ''),
                'customer_type' => (isset($customerdata['customer_type'])?$customerdata['customer_type'] : ''),
                'customer_mobile' => (isset($customerdata['customer_mobile']) && $customerdata['customer_mobile'] != '' ?$customerdata['customer_mobile'] : NULL),
                'customer_email' => (isset($customerdata['customer_email']) && $customerdata['customer_email'] != '' ?$customerdata['customer_email'] : NULL),
                'customer_source_id' => (NULL),
                'is_active' => "1",
                'customer_kids'=>(isset($customerdata['customer_kids'])?$customerdata['customer_kids'] : ''),
                'password'=> $password,
                'encrypt_password'=>$encrypt_password
            ]
        );

            DB::commit();
        }
        catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }

        if($customer)
        {
            return json_encode(array("Success"=>"True","Message"=>"<b>Thank you for registering with us.</b>"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }

    }

    public function customer_login_form(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        session_start();
        $data       =   $request->all();

        $customer_email     =   $data['user_email'];
        $password           =   $data['user_password'];
        $redirectURL        =   $data['redirectURL'];
        $homeURL            =   $data['homeURL'];

        // print_r($redirectURL); exit;

        if($redirectURL=='msg=verificationsuccess')
        {

           $redirectURL    =   $homeURL.'my-inquiry-basket';

        }
        else if($redirectURL=='')
        {
            $redirectURL    =   $homeURL;
        }
        else
        {
            $redirectURL    =   $redirectURL;
        }

        $result     =   customer::select('*')->where('customer_email',$customer_email)->get();

        if(sizeof($result)!=0)
        {
            if ((Hash::check($password, $result[0]->encrypt_password)))
            {
                $_SESSION['ECOMMERCE_ID']   =   $result[0]->customer_id;
                return json_encode(array("Success"=>"True","Message"=>"<b>Login Successful</b>","URL"=>$redirectURL));
            }
            else
            {
                return json_encode(array("Success"=>"False","Message"=>"<b>Incorrect login!</b>"));
            }
        }
    }

    public function getBasketItems(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        session_start();
        $session_id     =   session_id();

        $result     =   cart::where('session_id',$session_id)->with('product','product_image')->get();
        $cartCount    =   cart::select('cart_id')->where('session_id',$session_id)->count('cart_id');

        return json_encode(array("Data"=>$result,"cartCount"=>$cartCount));
    }

    public function getPageContent(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $a_value   =   $request->all();
        $result     =   ProductFeatures::where('feature_url',$a_value['level1'])->first();

        return json_encode(array("Data"=>$result));
        // print_r($result); exit;
    }


    ////////////////////////////////
    ///////////////////////////////
    ////////////////////////////////
    ////////////////////////////////
    ///////////////////////////////

    public function page_manager()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $company_id             =   Auth::user()->company_id;
        $result     =   ProductFeatures::where('feature_type',2)->where('company_id',$company_id)->orderBy('ordering','ASC')->whereNull('deleted_by')->get();
        return view('ecommerce::ecommerce/page_manager',compact('result'));
    }

    public function EditPagePopup(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();
        $result     =   ProductFeatures::where('product_features_id',$data['product_features_id'])->first();
        return json_encode(array("Success"=>"True","Data"=>$result));
    }

    public function addEditPage(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data                   =   $request->all();
        $pagedata =  array();
        parse_str($data['formdata'], $pagedata);

        $pagedata = preg_replace('/\s+/', ' ', $pagedata);

        $company_id             =   Auth::user()->company_id;

        $product_features_id            =   $pagedata['product_features_id'];
        $product_features_name          =   $pagedata['product_features_name'];
        $feature_url                    =   $pagedata['feature_url'];
        $feature_content                =   $pagedata['feature_content'];
        $html_name                      =   $pagedata['html_name'];
        $html_id                        =   $pagedata['html_id'];

        $ProductFeatures = ProductFeatures::updateOrCreate(
            ['product_features_id' => $product_features_id, 'company_id'=>$company_id,],
            [
                'product_features_name' => (isset($product_features_name)?$product_features_name : ''),
                'feature_url' => (isset($feature_url)?$feature_url:''),
                'feature_content' => (isset($feature_content)?$feature_content:''),
                'html_name' => (isset($html_name)?$html_name:''),
                'html_id' => (isset($html_id)?$html_id:'')
            ]
        );

        if($product_features_id=='')
        {
            return json_encode(array("Success"=>"True","Message"=>"Page added Successfully...","url"=>"page_manager"));
        }
        else
        {
            return json_encode(array("Success"=>"True","Message"=>"Page updated Successfully...","url"=>"page_manager"));
        }

    }

    public function product_features()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $company_id             =   Auth::user()->company_id;
        $result     =   ProductFeatures::where('company_id',$company_id)->orderBy('feature_type','ASC')->whereNull('deleted_by')->with('product_features_data_1')->get();
        return view('ecommerce::ecommerce/product_features',compact('result'));
    }

    public function addEditFeature(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data                   =   $request->all();

        $feature_type               =   $data['feature_type'];

        if($feature_type==1)
        {
            $feature_location       =   '';
        }
        else
        {
            $feature_location       =   $data['feature_location']==''?0:$data['feature_location'];
        }

        $parentChild                =   $data['parentChild'];
        $html_name                  =   $data['html_name'];
        $html_id                    =   $data['html_id'];
        $product_features_name      =   $data['product_features_name'];
        $feature_url                =   $data['feature_url'];
        $feature_content            =   $data['feature_content'];

        $product_features_id        =   $data['product_features_id'];
        $product_features_data_id   =   $data['product_features_data_id'];

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $created_by = $userId;

        if($parentChild=='')
        {

            if($feature_type==1)
            {
                $explode    =   explode('_',$html_id);

                if($explode[0]=='dynamic')
                {
                    if(Schema::hasColumn('product_features_relationships', ''.$html_id.''))
                    {

                    }
                    else
                    {
                        DB::select(DB::raw('ALTER TABLE `product_features_relationships` ADD `'.$html_id.'` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `product_id`;'));
                    }
                }
            }

            $ProductFeatures = ProductFeatures::updateOrCreate(
                ['product_features_id' => $product_features_id, 'company_id'=>$company_id,],
                [
                    'product_features_name' => (isset($product_features_name)?$product_features_name : ''),
                    'feature_url' => (isset($feature_url)?$feature_url:''),
                    'feature_content' => (isset($feature_content)?$feature_content:''),
                    'html_name' => (isset($html_name)?$html_name:''),
                    'html_id' => (isset($html_id)?$html_id:''),
                    'feature_type' => $feature_type,
                    'feature_location' => $feature_location,
                    'is_active' => 1,
                    'created_by' => $created_by
                ]
            );
        }
        else
        {
            if($request->file('product_features_data_image')=='')
            {
                $product_features_data_image    =   '';
                $image_name                     =   '';
            }
            else
            {

                $product_features_data_image         =   $request->file('product_features_data_image');

                $image_name = str_replace(' ', '_', rand().$request->file('product_features_data_image')->getClientOriginalName());

                $request->file('product_features_data_image')->move(public_path(PRODUCT_IMAGE_URL_CONTROLLER), $image_name);
            }

            if($request->file('product_features_banner_image')=='')
            {
                $product_features_banner_image     =   '';
                $image_name_banner                  =   '';
            }
            else
            {
                $product_features_banner_image         =   $request->file('product_features_banner_image');

                $image_name_banner = str_replace(' ', '_', rand().$request->file('product_features_banner_image')->getClientOriginalName());

                $request->file('product_features_banner_image')->move(public_path(PRODUCT_IMAGE_URL_CONTROLLER), $image_name_banner);
            }

            $ProductFeatures = product_features_data::updateOrCreate(
                ['product_features_data_id' => $product_features_data_id,],
                [
                    'product_features_id' => $parentChild,
                    'company_id'=>$company_id,
                    'product_features_data_value' => (isset($product_features_name)?$product_features_name : ''),
                    'product_features_data_url' => (isset($feature_url)?$feature_url:''),
                    'product_features_data_image' => (isset($image_name)?$image_name:''),
                    'product_features_banner_image' => (isset($image_name_banner)?$image_name_banner:''),
                    'feature_content' => (isset($feature_content)?$feature_content:''),
                    'is_active' => 1,
                    'created_by' => $created_by
                ]
            );
        }

        // echo '<pre>'; print_r($data); exit;

        return json_encode(array("Success"=>"True","Message"=>"Feature added Successfully...","url"=>"product_features"));

    }

    public function EditFeaturePopup(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();
        $result     =   ProductFeatures::where('product_features_id',$data['product_features_id'])->first();
        return json_encode(array("Success"=>"True","Data"=>$result));
    }

    public function EditSubFeaturePopup(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();
        $result     =   product_features_data::where('product_features_data_id',$data['product_features_data_id'])->with('product_features')->first();
        return json_encode(array("Success"=>"True","Data"=>$result));
    }

    public function deleteFeature(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $created_by = $userId;

        $ProductFeatures = ProductFeatures::updateOrCreate(
            ['product_features_id' => $data['product_features_id'],],
            [
                'deleted_by' => $userId,
                'deleted_at'=> date('Y-m-d H:i:s')
            ]
        );

        return json_encode(array("Success"=>"True","Message"=>"Feature deleted Successfully...","URL"=>"product_features"));

    }

    public function deleteSubFeature(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $created_by = $userId;

        $ProductFeatures = product_features_data::updateOrCreate(
            ['product_features_data_id' => $data['product_features_data_id'],],
            [
                'deleted_by' => $userId,
                'deleted_at'=> date('Y-m-d H:i:s')
            ]
        );

        return json_encode(array("Success"=>"True","Message"=>"Feature deleted Successfully...","URL"=>"product_features"));

    }

    public function FeatureActive(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();

        $ProductFeatures = ProductFeatures::updateOrCreate(
            ['product_features_id' => $data['product_features_id'],],
            [
                'is_active' => $data['status'],
                'updated_at'=> date('Y-m-d H:i:s')
            ]
        );

        return json_encode(array("Success"=>"True","Message"=>"Feature updated Successfully...","URL"=>"product_features"));

    }

    public function FeatureSubActive(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();

        $ProductFeatures = product_features_data::updateOrCreate(
            ['product_features_data_id' => $data['product_features_data_id'],],
            [
                'is_active' => $data['status'],
                'updated_at'=> date('Y-m-d H:i:s')
            ]
        );

        return json_encode(array("Success"=>"True","Message"=>"Feature updated Successfully...","URL"=>"product_features"));

    }

    public function UpdateOrdering(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();

        $ProductFeatures = ProductFeatures::updateOrCreate(
            ['product_features_id' => $data['product_features_id'],],
            [
                'ordering' => $data['enteredVal'],
                'updated_at'=> date('Y-m-d H:i:s')
            ]
        );

        return json_encode(array("Success"=>"True","Message"=>"ordering updated Successfully..."));

    }

    public function UpdateOrderingSub(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();

        $ProductFeatures = product_features_data::updateOrCreate(
            ['product_features_data_id' => $data['product_features_data_id'],],
            [
                'ordering' => $data['enteredVal'],
                'updated_at'=> date('Y-m-d H:i:s')
            ]
        );

        return json_encode(array("Success"=>"True","Message"=>"ordering updated Successfully..."));

    }

    public function verifyAccount(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();

        $customers =    DB::statement("UPDATE customers set is_active='1' where MD5(customer_email)='".$data['id']."'");

        return json_encode(array("Success"=>"True"));
    }





}
