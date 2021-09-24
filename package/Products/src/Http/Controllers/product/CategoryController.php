<?php

namespace Retailcore\Products\Http\Controllers\product;
use App\Http\Controllers\Controller;

use Retailcore\Products\Models\product\category;
use Illuminate\Http\Request;
use Auth;

class CategoryController extends Controller
{

    public function __construct () {
        $this->middleware('auth');

    }

    public function index()
    {
        $category= category::where('company_id',Auth::user()->company_id)->get();

        return view('category.category_show',compact('category'));
    }

    public function get_category()
    {
        $category = category::where('company_id',Auth::user()->company_id)->get();
        return response()->json(array("Success"=>"True","Data"=>$category));
    }


    public function category_create(Request $request)
    {
        $data = $request->all();
        $categorydata =  array();
        parse_str($data['formdata'], $categorydata);
        $categorydata = preg_replace('/\s+/', ' ', $categorydata);
        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;

        $category_id =$request->category_id;
        $modified_by = NULL;
        if(isset($category_id) && $category_id != '')
        {
            $modified_by = $userId;
        }
       /* else
        {
            $created_by = $userId;
        }*/
        $created_by = $userId;
        $category = category::updateOrCreate(
            ['category_id' => $category_id,
             'company_id'=>$company_id,
            ],
            [
                'created_by' =>$created_by,
                'company_id'=>$company_id,
                'category_name' => $categorydata['category_name'],
                'is_active' => '1',
                'modified_by' => $modified_by,
            ]
        );

        if($category)
        {
            if ($request->$category_id != null)
            {
                return json_encode(array("Success"=>"True","Message"=>"Category has been successfully updated."));
            } else {

                return json_encode(array("Success"=>"True","Message"=>"Category has been successfully added.","category_id"=>$category->category_id));
            }

        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
        return back()->withInput();

    }



    public function category_edit(Request $request)
    {
        $category_id= decrypt($request->category_id);
        $categorydata= category::where([['categories.category_id','=',$category_id]])
            ->select('categories.*')
            ->first();

        return $categorydata;

    }


    public function category_delete(Request $request)
    {
        $category_id= decrypt($request->category_id);
        //category::softDeletes();
         category::find($category_id)->softDeletes();

        return "Category has been successfully Delete.";
    }
}
