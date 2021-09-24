<?php

namespace Retailcore\Products\Http\Controllers\product;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\size;
use Illuminate\Http\Request;
use Auth;
class SizeController extends Controller
{

    public function index()
    {
        $size = size::where('company_id',Auth::user()->company_id)->get();

        return view('size.size_show',compact('size'));
    }
    public function get_size()
    {
        $size= size::where('company_id',Auth::user()->company_id)->get();
        return response()->json(array("Success"=>"True","Data"=>$size));
    }


    public function size_create(Request $request)
    {
        $data = $request->all();
        $sizedata =  array();
        parse_str($data['formdata'], $sizedata);
        $sizedata = preg_replace('/\s+/', ' ', $sizedata);
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;

        $size_id =$request->size_id;


        $created_by = $userId;
        $size = size::updateOrCreate(
            ['size_id' => $size_id, 'company_id'=>$company_id,
            ],
            [
                'created_by' =>$created_by,
                'company_id'=>$company_id,
                'size_name' => $sizedata['size_name'],
                'is_active' => '1',
            ]
        );



        if($size)
        {
            if ($request->size_id != null)
            {
                return json_encode(array("Success"=>"True","Message"=>"Size has been successfully updated."));
            } else {

                return json_encode(array("Success"=>"True","Message"=>"Size has been successfully added.","size_id"=>$size->size_id));
            }

        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));

        }
        return back()->withInput();
    }



    public function size_edit(Request $request)
    {
        $size_id= decrypt($request->size_id);
        $sizedata= size::where([['sizes.size_id','=',$size_id]])
            ->select('sizes.*')
            ->first();

        return $sizedata;
    }



}
