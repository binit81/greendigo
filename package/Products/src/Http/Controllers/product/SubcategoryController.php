<?php

namespace Retailcore\Products\Http\Controllers\product;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\subcategory;
use Retailcore\Products\Models\product\category;
use Illuminate\Http\Request;
use Auth;
class SubcategoryController extends Controller
{

    public function index()
    {
        $subcategory = subcategory::where('company_id',Auth::user()->company_id)->get();

        $category = category::where('company_id',Auth::user()->company_id)->get();

        return view('subcategory.subcategory_show',compact('subcategory','category'));
    }


    public function get_subcategory(Request $request)
    {

        $subcategory = subcategory::where('category_id',$request->category_ID)->get();
        return response()->json(array("Success"=>"True","Data"=>$subcategory));
    }



    public function subcategory_create(Request $request)
    {
        $data = $request->all();
        $subcategorydata = array();
        parse_str($data['formdata'],$subcategorydata);
        $subcategorydata = preg_replace('/\s+/', ' ', $subcategorydata);
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;

        $subcategory_id =$request->subcategory_id;


        $created_by = $userId;
        $subcategory = subcategory::updateOrCreate(
            ['subcategory_id' => $subcategory_id, 'company_id'=>$company_id,
            ],
            [
                'created_by' =>$created_by,
                'company_id'=>$company_id,
                'category_id' => $subcategorydata['popcategory_id'],
                'subcategory_name' => $subcategorydata['subcategory_name'],
                'is_active' => '1',
            ]
        );
        if($subcategory)
        {
            if ($request->subcategory_id != null)
            {
                return json_encode(array("Success"=>"True","Message"=>"Subcategory has been successfully updated."));
            } else {

                return json_encode(array("Success"=>"True","Message"=>"Subcategory has been successfully added.","subcategory_id"=>$subcategory->subcategory_id,"category_id"=>$subcategorydata['popcategory_id']));

            }
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
        return back()->withInput();
    }


    public function subcategory_edit(Request $request)
    {
        $subcategory_id= decrypt($request->subcategory_id);
        $subcategorydata= subcategory::where([['subcategories.subcategory_id','=',$subcategory_id]])
            ->select('subcategories.*')
            ->first();

        return $subcategorydata;
    }


}
