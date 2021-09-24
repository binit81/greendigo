<?php

namespace Retailcore\Debit_Note\Http\Controllers\debit_note;
use Retailcore\Debit_Note\Models\debit_note\debit_note;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Retailcore\Debit_Note\Models\debit_note\debit_product_detail;
use Retailcore\Debit_Note\Models\debit_note\debit_note_report_excel;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Products\Models\product\price_master;
use Illuminate\Http\Request;
use Auth;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\ProductFeatures;
use Log;
class DebitProductDetailController extends Controller
{

    public function edit_debit_note(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $debit_note_id = decrypt($request->debit_note_id);

        $debit_note = debit_note::where([
            ['debit_note_id','=',$debit_note_id],
            ['company_id',Auth::user()->company_id]])
            ->with('debit_product_details.product.product_features_relationship')
            ->with('inward_stock')
            ->with('supplier_gstdetail.supplier_company_info')
            ->select('*')
            ->first();
      $inward_stock_id =  isset($debit_note) && $debit_note != ''?$debit_note['inward_stock_id'] : '';
        $supplier_gst_id =  isset($debit_note) && $debit_note != ''?$debit_note['supplier_gst_id'] : '';

        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($debit_note['debit_product_details'] AS $key=>$value)
        {

            $price = price_master::where('product_id',$value['product_id'])
                ->whereNull('deleted_at')
                ->where('company_id', Auth::user()->company_id)
                ->sum('product_qty');

            $debit_note['debit_product_details'][$key]['in_stock'] = $price;

          $inward_product_detail =   inward_product_detail::select('product_mrp','offer_price','batch_no','inward_product_detail_id','base_price','product_qty','free_qty','pending_return_qty')->where('product_id',$value['product_id'])
                ->whereNull('deleted_at')
                ->where('company_id', Auth::user()->company_id)
                //->where('inward_stock_id',$inward_stock_id)
                ->where('product_id',$value['product_id'])
                ->where('supplier_gst_id',$supplier_gst_id)->first();


            $debit_note['debit_product_details'][$key]['inward_product_detail'] =$inward_product_detail;



                    foreach ($product_features AS $kk => $vv)
                    {
                        $html_id = $vv['html_id'];
                        if ($value['product']['product_features_relationship'][$html_id] != '' && $value['product']['product_features_relationship'][$html_id] != NULL) {
                            $nm = product::feature_value($vv['product_features_id'], $value['product']['product_features_relationship'][$html_id]);
                            $value['product'][$html_id] = $nm;
                        }

            }
        }
        $data = json_encode($debit_note);

        $url = 'debit_note';




        return json_encode(array("Success"=>"True","Data"=>$data,"url"=>$url));
    }

    public function debit_note_report()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $sort_by = 'debit_product_detail_id';
        $sort_type = 'DESC';
        $debit_product_detail =  debit_product_detail::where('company_id',Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->with('debit_note')
            ->with('product.product_features_relationship')
            ->orderBy($sort_by, $sort_type)
            ->paginate(10);
        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($debit_product_detail AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $debit_product_detail[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }

       return view('debit_note::debit_note/debit_note_report',compact('debit_product_detail'));
    }


    //FILTER FOR DEBIT NOTE REPORT

    public function debit_no_wise_search_record(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();
        $sort_by = $data['sortby'];
        $sort_type = $data['sorttype'];
        $query = (isset($data['query']) ? $data['query'] : '');

        $from_date = isset($query['from_date']) ? $query['from_date'] : '';
        $to_date = isset($query['to_date']) ? $query['to_date'] : '';
        $debit_no = isset($query['debit_no']) ? $query['debit_no'] : '';
        $product_code = isset($query['product_code']) ? $query['product_code'] : '';


        $query =  debit_product_detail::where('company_id',Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->with('debit_note')
            ->with('product');


        if ($from_date != '')
        {
            $start_date = date("Y-m-d",strtotime($from_date));
            $end_date  =  date("Y-m-d",strtotime($to_date));
            $query->whereHas('debit_note',function ($q) use($start_date,$end_date)
            {
                //$q->whereBetween('debit_date', [$from_date,$to_date]);
                $q->whereRaw("STR_TO_DATE(debit_date,'%d-%m-%Y') between '$start_date' and '$end_date'");
            });
        }

        if ($debit_no != '')
        {
            $query->whereHas('debit_note',function ($q) use($debit_no)
            {
                $q->whereRaw("debit_notes.debit_no='" . $debit_no . "'");
            });
        }

        if ($product_code != '')
        {
            $query->whereHas('product',function ($q) use($product_code)
            {
                $q->whereRaw("product_code='" . $product_code . "'");
            });
        }

        $debit_product_detail = $query->orderBy($sort_by,$sort_type)->paginate(10);

        return view('debit_note::debit_note/debit_note_report_data',compact('debit_product_detail'))->render();
    }


    public function debitnote_report_export(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        return Excel::download(new debit_note_report_excel($request->from_date,$request->to_date,$request->debit_no,$request->product_code),'Debit_note_report.xlsx');
    }


}
