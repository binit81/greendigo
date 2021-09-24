<?php

namespace Retailcore\Products\Http\Controllers\product;

use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Products\Models\product\product_features_data;
use Illuminate\Http\Request;
use Auth;
use DB;
use Log;
use Illuminate\Validation\Rule;
class ProductFeaturesController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        return view('productfeatures::product_features_show');
    }


    public function productfeatures_create(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
     $data = $request->all();
        $productfeaturesdata =  array();
        parse_str($data['formdata'], $productfeaturesdata);
        $productfeaturesdata = preg_replace('/\s+/', ' ', $productfeaturesdata);
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $productfeatures_id =$productfeaturesdata['product_features_id'];
        $created_by = $userId;


        $product_features = ProductFeatures::getproduct_feature($productfeaturesdata['product_features_id']);



       /* $validate_error = \Validator::make($data,
            [
                'product_features_data_value' => [Rule::unique('product_features_datas')->whereNull('deleted_at')->whereNotNull('product_features_data_value')->where('product_features_id',$productfeatures_id)],
            ]);

        if ($validate_error->fails()) {
            return json_encode(array("Success" => "False", "status_code" => 409, "Message" => $validate_error->messages()));
            exit;
        }*/



        $productfeaturesdata['product_features_data_image'] = '';
        if(isset($productfeaturesdata['image_name']) && $productfeaturesdata['image_name'] != '' && $productfeaturesdata['image_name'] != 'NULL' && $productfeaturesdata['image_name'] != 'null')
        {
            if (!file_exists(DYNAMIC_PRODUCT_PROPERTIES_IMAGE))
                {
                    mkdir(DYNAMIC_PRODUCT_PROPERTIES_IMAGE, 0777, true);
                }

                $image_parts = explode(";base64,", $productfeaturesdata['image_json']);
                if(isset($image_parts) && $image_parts != '')
                {
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $file_name = uniqid() . '.png';
                    $file = DYNAMIC_PRODUCT_PROPERTIES_IMAGE . $file_name;
                    file_put_contents($file, $image_base64);
                    chmod($file, 0664);
                    $productfeaturesdata['product_features_data_image'] = $file_name;
                }
        }

        $productfeaturesdata['product_features_banner_image'] = '';
        if(isset($productfeaturesdata['banner_image_name']) && $productfeaturesdata['banner_image_name'] != '' && $productfeaturesdata['banner_image_name'] != 'NULL' && $productfeaturesdata['banner_image_name'] != 'null')
        {
            if (!file_exists(DYNAMIC_PRODUCT_PROPERTIES_IMAGE))
            {
                mkdir(DYNAMIC_PRODUCT_PROPERTIES_IMAGE, 0777, true);
            }

            $image_parts = explode(";base64,", $productfeaturesdata['banner_image_json']);
            if(isset($image_parts) && $image_parts != '')
            {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $file_name = uniqid() . '.png';
                $file = DYNAMIC_PRODUCT_PROPERTIES_IMAGE . $file_name;
                file_put_contents($file, $image_base64);
                chmod($file, 0664);
                $productfeaturesdata['product_features_banner_image'] = $file_name;
            }
        }

        $parent_id = (isset($productfeaturesdata['parent'])?$productfeaturesdata['parent'] : '0');

        $productfeatures_data= product_features_data::updateOrCreate(
            ['product_features_data_id' =>'', 'company_id'=>$company_id,
            ],
            [
                'product_features_id'=>$productfeatures_id,
                'created_by' =>$created_by,
                'company_id'=>$company_id,
                'product_features_data_value' => $productfeaturesdata['product_features_data_value'],
                'product_features_data_url' => isset($productfeaturesdata['product_features_data_url']) ? $productfeaturesdata['product_features_data_url'] : '' ,
                'feature_content' => isset($productfeaturesdata['feature_content']) ? $productfeaturesdata['feature_content'] : '',
                'product_features_data_image' => isset($productfeaturesdata['product_features_data_image']) ? $productfeaturesdata['product_features_data_image'] : '',
                'product_features_banner_image' => isset($productfeaturesdata['product_features_banner_image']) ? $productfeaturesdata['product_features_data_image'] : '',
                'parent' => $parent_id,
                'is_active' => '1'
            ]
        );

        $message = 'Product Features';

        if($product_features['product_features_name'] !='')
        {
            $message =$product_features['product_features_name'];
        }

       return json_encode(array("Success"=>"True","Message"=>$message ." has been successfully added.","product_features_data_id"=>$productfeatures_data->product_features_data_id));

         return back()->withInput();
    }


    public function getfeatures(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();
        $product_features_data=product_features_data::where('company_id',Auth::user()->company_id)
                             ->where('deleted_at','=',NULL)
                             ->where('product_features_id',$data['product_features_id'])
                             ->get();


        return json_encode(array("Success"=>"True","Data"=>$product_features_data));

}

    public function get_parent_of_feature(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();
        $product_features_data=product_features_data::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->where('product_features_id',$data['product_features_id'])
            ->where('parent',1)
            ->where('is_active',1)
            ->get();


        return json_encode(array("Success"=>"True","Data"=>$product_features_data));
    }



}
