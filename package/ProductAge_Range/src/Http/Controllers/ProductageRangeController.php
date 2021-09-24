<?php

namespace Retailcore\ProductAge_Range\Http\Controllers;

use App\Http\Controllers\Controller;
use Retailcore\ProductAge_Range\Models\productage_range;
use Illuminate\Http\Request;
use Auth;
use DB;
use Log;
class ProductageRangeController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $productage_range = productage_range::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->where('is_active','=','1')
            ->orderBy('productage_range_id', 'DESC')
            ->paginate(10);
        return view('productage_range::productage_range',compact('productage_range'));
    }
    public function age_range_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $productage_range = productage_range::where('company_id',Auth::user()->company_id)
                ->where('is_active','=','1')
                ->where('deleted_at','=',NULL)
                ->orderBy('productage_range_id', 'DESC')
                ->paginate(10);
            return view('productage_range::productage_rangedata',compact('productage_range'));
        }
    }
     public function agerange_create(request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();
        $agerangedata =  array();

        parse_str($data['formdata'], $agerangedata);
        $agerangedata = preg_replace('/\s+/', ' ', $agerangedata);
        $company_id = Auth::User()->company_id;

        $created_by = Auth::User()->user_id;

        $range_from = $agerangedata['range_from'];
        $range_to = $agerangedata['range_to'];

        if($agerangedata['productage_range_id'] == '')
        {
            $productage_range = productage_range::where('company_id', Auth::user()->company_id)
                ->where('is_active', '=', '1')
                ->where('deleted_at', '=', NULL)->get();
        }
        else {
            $productage_range = productage_range::where('company_id', Auth::user()->company_id)
                ->where('productage_range_id', '!=', $agerangedata['productage_range_id'])
                ->where('is_active', '=', '1')
                ->where('deleted_at', '=', NULL)->get();

        }
        if($agerangedata['productage_range_id'] == '')
        {
                foreach ($productage_range AS $key=>$value)
                {
                    if(($range_from >= $value['range_from'] && $range_from < $value['range_to']) ||($range_to >= $value['range_from'] &&  $range_to < $value['range_to']))
                    {
                        return json_encode(array("Success"=>"False","Message"=>"This Product Age Range already exists!"));
                        exit;
                    }


                }
        }


        try{
            DB::beginTransaction();
        $productage_range = productage_range::updateOrCreate(
            ['productage_range_id' => $agerangedata['productage_range_id'],
             'company_id'=>$company_id,],
            [
                'created_by' =>$created_by,
                'company_id'=>$company_id,
                'range_from' => (isset($agerangedata['range_from'])?$agerangedata['range_from'] : ''),
                'range_to' => (isset($agerangedata['range_to'])?$agerangedata['range_to'] : ''),
                'is_active' => "1"
            ]
        );
            DB::commit();
        }catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }

        if($productage_range)
        {
            if ($agerangedata['productage_range_id'] != '')
            {
                return json_encode(array("Success"=>"True","Message"=>"Product Age Range has been successfully updated."));
            }
            else
            {
                return json_encode(array("Success"=>"True","Message"=>"Product Age Range has been successfully added."));
            }
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }

    }

    public function agerange_edit(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $productage_range_id = decrypt($request->productage_range_id);

        $productage_rangedata = productage_range::where([['productage_ranges.productage_range_id','=',$productage_range_id],['company_id',Auth::user()->company_id]])
            ->select('productage_ranges.*')
            ->first();

        return json_encode(array("Success"=>"True","Data"=>$productage_rangedata));
    }
    public function agerange_delete(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $userId = Auth::User()->user_id;

        try {
            DB::beginTransaction();

            $productage_rangedelete = productage_range::whereIn('productage_range_id', $request->deleted_id)
                ->update([
                    'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);
            DB::commit();
        }catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }

        if($productage_rangedelete)
        {
            return json_encode(array("Success"=>"True","Message"=>"Product Age Range has been successfully deleted.!"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong!"));
        }
    }

}
