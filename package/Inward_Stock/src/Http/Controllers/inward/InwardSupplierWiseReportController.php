<?php

namespace Retailcore\Inward_Stock\Http\Controllers\inward;

use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Inward_Stock\Models\inward\inward_supplier_wise_report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Log;
class InwardSupplierWiseReportController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $sort_by = 'inward_stock_id';
        $sort_type = 'desc';
        $supplier_wise_report = inward_stock::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            //->where('warehouse_id','=',NULL)
            //->where('supplier_gst_id','!=',NULL)
            ->with('supplier_gstdetail')
            ->with('inward_product_detail')
            ->orderBy($sort_by, $sort_type)->paginate(10);



        return view('inward_stock::inward/supplier_wise_report',compact('supplier_wise_report'));
    }


    public function supplier_wise_record(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();


        $sort_by = $data['sortby'];
        $sort_type = $data['sorttype'];
        $query = (isset($data['query']) ? $data['query'] : '');

        $query = str_replace(" ", "", $query);
        if($request->ajax())
        {
            $supplier_wise_report = inward_stock::where('company_id', Auth::user()->company_id)
                ->with('inward_product_detail')
                ->with('supplier_gstdetail');

            if(!empty($query) && $query != '') {


                    if ($query['from_date'] != '' && $query['to_date'] != '')
                    {

                        $start_date = date("Y-m-d", strtotime($query['from_date']));
                        $end_date = date("Y-m-d", strtotime($query['to_date']));
                        $supplier_wise_report->
                        whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$start_date' and '$end_date'");
                    }
                    if ($query['supplier_name'] != '') {
                        $supplier_wise_report
                            ->whereHas('supplier_gstdetail', function ($q) use ($query) {
                                $q->where('supplier_gst_id', '=', $query['supplier_name']);
                            });
                    }

                if ($query['invoice_no'] != '') {
                    $supplier_wise_report->where('invoice_no', '=', $query['invoice_no']);
                }

            }

            $supplier_wise_report =   $supplier_wise_report->orderBy($sort_by, $sort_type)->paginate(10);


            return view('inward_stock::inward/supplier_wise_report_data', compact('supplier_wise_report'))->render();
        }
    }
}
