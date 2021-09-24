<?php

namespace Retailcore\Inward_Stock\Http\Controllers\inward;
use App\Http\Controllers\Controller;
use Retailcore\Inward_Stock\Models\inward\inward_product_wise_report;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Illuminate\Http\Request;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use DB;
use Auth;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\ProductFeatures;
use Log;
class InwardProductWiseReportController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $sort_by = 'inward_product_detail_id';
        $sort_type = 'desc';

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type')->first();

        $inward_type = 1;
        if(isset($inward_type_from_comp) && !empty($inward_type_from_comp) && $inward_type_from_comp['inward_type'] != '')
        {
            $inward_type = $inward_type_from_comp['inward_type'];
        }

        $product_wise_report = inward_product_detail::where('company_id',Auth::user()->company_id)
                                ->where('deleted_at','=',NULL)
                                ->with('product_detail.product_features_relationship')
                                //->with('inward_stock')
                                ->orderBy($sort_by,$sort_type)->paginate(40);

        $total = inward_product_detail::where('company_id',Auth::user()->company_id)
                            ->where('deleted_at','=',NULL);
        $total_cost_rate = $total->sum('total_cost_rate_with_qty');
        $igst_qty =$total->sum('total_igst_amount_with_qty');
        $cgst_qty =$total->sum('total_cgst_amount_with_qty');
        $sgst_qty =$total->sum('total_sgst_amount_with_qty');

        $total_total_cost = $total->sum('total_cost');

        $without_taxable_amount = $total->sum(DB::raw('pending_return_qty * cost_rate'));
        $with_tax_amount = $total->sum(DB::raw('pending_return_qty * cost_price'));


        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($product_wise_report AS $key=>$v) {
            if (isset($v['product_detail']['product_features_relationship']) && $v['product_detail']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product_detail']['product_features_relationship'][$html_id] != '' && $v['product_detail']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product_detail']['product_features_relationship'][$html_id]);

                        $product_wise_report[$key]['product_detail'][$html_id] = $nm;
                    }
                }
            }
        }
        return view('inward_stock::inward/product_wise_report',compact('product_wise_report','total_cost_rate','total_total_cost','igst_qty','cgst_qty','sgst_qty','inward_type','without_taxable_amount','with_tax_amount'));
    }

    public function product_wise_record(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

        $sort_by = $data['sortby'];
        $sort_type = $data['sorttype'];
        $query = (isset($data['query']) ? $data['query'] : '');
        //$query = str_replace(" ", "", $query);

        if($request->ajax())
        {

            if(!empty($query) && $query != '')
            {
                if($query['from_date'] != '' || $query['to_date'] != '' || $query['barcode'] != '' || $query['product_name'] != '' || $query['batch_no'] != '' || $query['invoice_no'] != '' || $query['product_code'] != '')
                {
                    $start_date = date("Y-m-d",strtotime($query['from_date']));
                    $end_date  =  date("Y-m-d",strtotime($query['to_date']));

                    if ($query['from_date'] != '' && $query['to_date'] != '')
                    {
                        $product_wise_report = inward_product_detail::where('company_id', Auth::user()->company_id)
                            ->where('deleted_at', '=', NULL)
                            ->with('inward_stock')
                            ->with('product_detail')
                            ->whereHas('inward_stock', function ($q) use ($start_date,$end_date)
                            {
                               // $q->whereBetween('inward_date', [$query['from_date'], $query['to_date']]);
                                $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$start_date' and '$end_date'");
                            });
                    }
                    if ($query['barcode'] != '')
                    {
                        $product_wise_report = inward_product_detail::where('company_id', Auth::user()->company_id)
                            ->where('deleted_at', '=', NULL)
                            ->with('inward_stock')
                            ->with('product_detail')
                            ->whereHas('product_detail', function ($q) use ($query)
                            {
                                $q->where('product_system_barcode', 'LIKE', '%' . $query['barcode'] . '%');
                                $q->orWhere('supplier_barcode', 'LIKE', '%' . $query['barcode'] . '%');
                            });
                    }
                    if ($query['product_name'] != '')
                    {
                        $product_wise_report = inward_product_detail::where('company_id', Auth::user()->company_id)
                            ->where('deleted_at', '=', NULL)
                            ->with('inward_stock')
                            ->with('product_detail')
                            ->whereHas('product_detail', function ($q) use ($query) {
                                $q->where('product_name', 'LIKE', '%' . $query['product_name'] . '%');
                            });
                    }
                    if ($query['batch_no'] != '') {
                        $product_wise_report = inward_product_detail::where('company_id', Auth::user()->company_id)
                            ->where('deleted_at', '=', NULL)
                            ->with('inward_stock')
                            ->with('product_detail')
                            ->where('batch_no', 'LIKE', '%' . $query['batch_no'] . '%');

                    }
                    if ($query['invoice_no'] != '')
                    {
                        $product_wise_report = inward_product_detail::where('company_id', Auth::user()->company_id)
                            ->where('deleted_at', '=', NULL)
                            ->with('inward_stock')
                            ->with('product_detail')
                            ->whereHas('inward_stock',function ($q) use($query)
                            {
                                $q->where('invoice_no',$query['invoice_no']);
                            });
                    }
                    if ($query['product_code'] != '')
                    {

                        $product_wise_report = inward_product_detail::where('company_id', Auth::user()->company_id)
                            ->where('deleted_at', '=', NULL)
                            ->with('inward_stock')
                            ->with('product_detail')
                            ->whereHas('product_detail', function ($q) use ($query) {
                                $q->where('product_code', '=',$query['product_code']);
                            });
                    }
                }
                else
                {
                    $product_wise_report = inward_product_detail::where('company_id',Auth::user()->company_id)
                        ->where('deleted_at','=',NULL)
                        ->with('product_detail');
                }

            }
            else
            {
                $product_wise_report = inward_product_detail::where('company_id',Auth::user()->company_id)
                    ->where('deleted_at','=',NULL)
                    ->with('product_detail');
            }


            $igst_qty =$product_wise_report->sum('total_igst_amount_with_qty');
            $cgst_qty =$product_wise_report->sum('total_cgst_amount_with_qty');
            $sgst_qty =$product_wise_report->sum('total_sgst_amount_with_qty');


            $total_cost_rate = $product_wise_report->sum('cost_rate');

            $total_total_cost = $product_wise_report->sum('total_cost');




            $total = inward_product_detail::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL);

            $without_taxable_amount = $product_wise_report->sum(DB::raw('pending_return_qty * cost_rate'));
            $with_tax_amount = $product_wise_report->sum(DB::raw('pending_return_qty * cost_price'));





            $product_wise_report = $product_wise_report->orderBy($sort_by, $sort_type)->paginate(40);


            $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type')->first();

            $inward_type = 1;
            if(isset($inward_type_from_comp) && !empty($inward_type_from_comp) && $inward_type_from_comp['inward_type'] != '')
            {
                $inward_type = $inward_type_from_comp['inward_type'];
            }

            return view('inward_stock::inward/product_wise_report_data',compact('product_wise_report', 'total_cost_rate','total_total_cost','igst_qty','cgst_qty','sgst_qty','inward_type','without_taxable_amount','with_tax_amount'))->render();
        }
    }
}





