<?php

namespace Retailcore\Debit_Note\Http\Controllers\debit_note;
use Retailcore\Debit_Note\Models\debit_note\debit_note;
use App\Http\Controllers\Controller;

use Retailcore\Debit_Note\Models\debit_note\view_debit_note;
use Retailcore\Debit_Note\Models\debit_note\debit_product_detail;
use Illuminate\Http\Request;
use Auth;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\ProductFeatures;
use Log;
class ViewDebitNoteController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
       $debit_note = debit_note::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->orderBy('debit_note_id', 'DESC')
            ->with('inward_stock')
            ->paginate(10);

       return view('debit_note::debit_note/view_debit_note',compact('debit_note'));
    }


    public function view_debit_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $debit_note_id = decrypt($request->debit_note_id);

        $debit_detail = debit_note::where('debit_note_id','=',$debit_note_id)
            ->where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->with('debit_product_details.product.product_features_relationship')
            ->with('supplier_gstdetail')
            ->get();


        $product_features =  ProductFeatures::getproduct_feature('');



        foreach ($debit_detail[0]['debit_product_details'] AS $key=>$v) {

            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '') {
                foreach ($product_features AS $kk => $vv) {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL) {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $v['product'][$html_id] = $nm;
                    }
                }
            }
        }


        $data = json_encode($debit_detail);


       //TO GET NEXT ID OF SELECTED ID IN POPUP
        $next_id = debit_note::where('debit_note_id', '>', $debit_note_id)->min('debit_note_id');


         $next = '';
         if(isset($next_id) && $next_id != '')
         {
            $next = encrypt($next_id);
         }

            //TO GET PREVIOUS ID OF SELECTED ID IN POPUP
            $previous = debit_note::where('debit_note_id', '<', $debit_note_id)->max('debit_note_id');

            $prev = '';
            if(isset($previous) && $previous != '')
            {
                $prev = encrypt($previous);
            }


        return json_encode(array("Success"=>"True","Data"=>$data,"next"=>$next,"previous"=>$prev));
         }


    public function debit_note_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $debit_note = debit_note::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->orderBy('debit_note_id', 'DESC')
                ->with('inward_stock')
                ->paginate(10);


            return view('debit_note::debit_note/view_debit_note_data', compact('debit_note'))->render();
        }
    }


    function debit_note_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $data = $request->all();
            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = isset($data['query']) ? $data['query'] : '';

            $query = str_replace(" ", "%", $query);

            $debit_note = debit_note::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->orderBy('debit_note_id', 'DESC')
                ->with('inward_stock');

                if($query != '')
                {
                    if ($query['debit_no'] != '') {
                        $debit_note->where('debit_no', '=', $query['debit_no'])->where('company_id',Auth::user()->company_id);
                    }
                    if ($query['supplier_gst_id'] != '')
                    {
                        $debit_note->where('supplier_gst_id', '=', $query['supplier_gst_id'])->where('company_id',Auth::user()->company_id);
                    }
                }

               $debit_note =  $debit_note->orderBy($sort_by, $sort_type)->paginate(10);

            return view('debit_note::debit_note/view_debit_note_data', compact('debit_note'))->render();
        }
    }

    function debit_number_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = debit_note::select('debit_no')
            ->where('company_id',Auth::user()->company_id)
            ->where('debit_no','LIKE',"%$request->search_val%")
            ->whereNull('deleted_at')
            ->get();
        return json_encode(array("Success"=>"True","Data"=>$result));
    }
}
