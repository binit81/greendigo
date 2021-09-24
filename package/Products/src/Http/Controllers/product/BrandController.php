<?php

namespace Retailcore\Products\Http\Controllers\product;
use App\Http\Controllers\Controller;

use Retailcore\Products\Models\product\brand;
use Illuminate\Http\Request;
use Auth;
class BrandController extends Controller
{

    public function index()
    {

        $brand= brand::where('company_id',Auth::user()->company_id)->get();

        return view('brand.brand_show',compact('brand'));
    }

    public function get_brand()
    {
        $brand= brand::where('company_id',Auth::user()->company_id)->get();
        return response()->json(array("Success"=>"True","Data"=>$brand));
    }


    public function brand_create(Request $request)
    {
        $data = $request->all();
        $branddata =  array();
        parse_str($data['formdata'], $branddata);
        $branddata = preg_replace('/\s+/', ' ', $branddata);
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $brand_id =$request->brand_id;

        $created_by = $userId;
        $brand = brand::updateOrCreate(
            ['brand_id' => $brand_id, 'company_id'=>$company_id,
            ],
            [
                'created_by' =>$created_by,
                'company_id'=>$company_id,
                'brand_type' => $branddata['brand_type'],
                'is_active' => '1',
            ]
        );



        if($brand)
        {
            if ($request->brand_id != null)
            {
                return json_encode(array("Success"=>"True","Message"=>"Brand has been successfully updated."));
            }
            else
            {
                return json_encode(array("Success"=>"True","Message"=>"Brand has been successfully added.","brand_id"=>$brand->brand_id));
            }

        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
        return back()->withInput();
    }


    public function brand_edit(Request $request)
    {
        $brand_id= decrypt($request->brand_id);
        $branddata= brand::where([['brands.brand_id','=',$brand_id]])
            ->select('brands.*')
            ->first();

        return $branddata;
    }


}
