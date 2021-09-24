<?php

namespace Retailcore\Supplier\Http\Controllers\supplier;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\Http\Controllers\Controller;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Supplier\Models\supplier\supplier_debitreceipts;
use Retailcore\Supplier\Models\supplier\supplier_payment_detail;
use Retailcore\Supplier\Models\supplier\supplier_company_info;
use Retailcore\Sales\Models\payment_method;
use Retailcore\Supplier\Models\supplier\supplier_payment_summary_export;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Log;
class SupplierOutstandingDetailController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $inward_stock = inward_stock::where('company_id',Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->where('is_payment_clear','=','0')
            ->whereNull('warehouse_id')
            ->with('supplier_gstdetail')
            ->select('inward_stock_id','supplier_gst_id',DB::raw('SUM(total_gross) as totalamt'))
            ->selectRaw('GROUP_CONCAT(inward_stock_id) as inward_stock')
            ->groupBy('supplier_gst_id')
            ->get();

        $outstanding_payment = array();
        if(isset($inward_stock)  && $inward_stock != '')
        {
            foreach ($inward_stock AS $key=>$value) {

                $outstanding = supplier_payment_detail::where('company_id', Auth::user()->company_id)
                    ->selectRaw('inward_stock_id,
                    GROUP_CONCAT(amount) AS amount,
                    GROUP_CONCAT(outstanding_payment) AS outstanding_payment,
                    GROUP_CONCAT(inward_stock_id) AS inward_stock_id,
                    GROUP_CONCAT(supplier_payment_detail_id) AS supplier_payment_detail_id')
                    ->where('outstanding_payment', '!=', NULL)
                    ->where('outstanding_payment', '>', 0)
                    ->whereRaw("find_in_set(inward_stock_id,'" . $value['inward_stock'] . "')")
                    ->get();



                if(isset($outstanding) && $outstanding != '')
                {
                    $outstanding_payment[$key]['supplier_gst_id'] = $value['supplier_gst_id'];
                    $outstanding_payment[$key] = $outstanding;
                }
                 $outstanding_payment[$key]['totalamt'] = $value['totalamt'];
            }
        }
        $total_invoice = inward_stock::where('company_id',Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->where('is_payment_clear','=','0')
            ->get()->count();

        $outstanding_detail = supplier_payment_detail::where('company_id',Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->where('outstanding_payment', '!=', NULL)
            ->where('outstanding_payment', '>', '0')
            ->sum('amount');
        $amount_payable = supplier_payment_detail::where('company_id',Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->sum('outstanding_payment');

        $get_store = company_relationship_tree::where('warehouse_id', '=', Auth::user()->company_id)
               // ->with('company_profile','storeUsers')
               // ->toSql();
                ->get();
               

       return view('supplier::supplier/supplier_payment',compact('outstanding_payment','total_invoice','outstanding_detail','amount_payable','get_store'));
    }

    public function list_outstanding_payment(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $supplier_gst_id = decrypt($request->supplier_gst_id);

        $inward_stock = inward_stock::where('supplier_gst_id', $supplier_gst_id)
            //->where('company_id',Auth::user()->company_id)
            ->groupBy('supplier_gst_id')
            ->select('inward_stock_id')
            ->selectRaw('GROUP_CONCAT(inward_stock_id) as inward_stock')
            ->get();

        $outstanding_detail = '';

        if(isset($inward_stock) &&  isset($inward_stock[0]) && $inward_stock[0]['inward_stock'] != '') {
            $outstanding_detail = supplier_payment_detail::whereNull('deleted_at')
                ->where('company_id',Auth::user()->company_id)
                ->with('inward_stock')
                ->where('outstanding_payment', '!=', NULL)
                ->where('outstanding_payment', '>', 0)
                ->whereRaw("find_in_set(inward_stock_id,'" . $inward_stock[0]['inward_stock'] . "')")
                ->get();
        }
        $payment_methods = payment_method::where('is_active','=','1')->orderBy('payment_order','ASC')->get();


        $prefix = company_profile::select('debit_receipt_prefix')->where('company_id',Auth::user()->company_id)->first();


        $receipt_prefix = $prefix['debit_receipt_prefix'];
        $last_receipt = supplier_debitreceipts::where('company_id',Auth::user()->company_id)->whereNull('deleted_at')->max('supplier_debitreceipt_id');

       $last_receipt++;

       $final_receipt_no = isset($receipt_prefix) && $receipt_prefix != '' && $receipt_prefix != NULL ? $receipt_prefix.$last_receipt : $last_receipt;


        return view('supplier::supplier/view_debitnote_detail',compact('outstanding_detail','payment_methods','final_receipt_no'));
    }


    public function view_amount_payable_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $supplier_gst_id = decrypt($request->gst_id);
         $supplier_company_id = (isset($request->company_id) && $request->company_id != "") ? $request->company_id : Auth::user()->company_id;

        $inward_stock = inward_stock::where('supplier_gst_id', $supplier_gst_id)
           ->where('company_id',$supplier_company_id)
            ->groupBy('supplier_gst_id')
            ->select('inward_stock_id')
            ->selectRaw('GROUP_CONCAT(inward_stock_id) as inward_stock')
            ->get();

        $outstanding_detail = '';

        if(isset($inward_stock) &&  isset($inward_stock[0]) && $inward_stock[0]['inward_stock'] != '') {
            $outstanding_detail = supplier_payment_detail::whereNull('deleted_at')
                ->where('company_id',$supplier_company_id)
                ->with('inward_stock')
                ->where('outstanding_payment', '!=', NULL)
                ->where('outstanding_payment', '>', 0)
                ->whereRaw("find_in_set(inward_stock_id,'" . $inward_stock[0]['inward_stock'] . "')")
                ->get();
        }



    return json_encode(array("Success"=>"True","Data"=>$outstanding_detail));
       //return view('supplier::supplier/view_amount_payable_detail',compact('outstanding_detail','payment_methods','final_receipt_no'));
    }

    function datewise_supplierpaymentdetail(Request $request)
    {
       
         Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $data = $request->all();
            $sort_by =  (isset($data->sort_by) && $data->sort_by != "") ? $data->sort_by : "supplier_gst_id";
            $sort_type = (isset($data['sort_type']) && $data['sort_type'] != "") ? $data['sort_type'] : "DESC";
            
            $query = (isset($data['query']) && $data['query'] != "") ? $data['query'] : "";;

            $query = str_replace(" ", "%", $query);
            $supplier_company_id = (isset($query['company_id']) && $query['company_id'] != "") ? $query['company_id'] : Auth::user()->company_id;
            $inward_stock = inward_stock::
            //where('company_id',Auth::user()->company_id)
               whereNull('deleted_at')
                ->where('is_payment_clear','=','0')
                ->whereNull('warehouse_id')
                ->with('supplier_gstdetail')
                ->select('inward_stock_id','supplier_gst_id',DB::raw('SUM(total_gross) as totalamt'))
                ->selectRaw('GROUP_CONCAT(inward_stock_id) as inward_stock');

                 if (isset($query['company_id']) && $query['company_id']!= '')
                    {
                        $inward_stock->where('company_id', '=', $query['company_id']);
                    }
                    else {
                     $inward_stock->where('company_id',Auth::user()->company_id);  
                    }
                
                 if($query != '')
                {
                    if ($query['supplier_gst_id'] != '')
                    {
                        $inward_stock->where('supplier_gst_id', '=', $query['supplier_gst_id']);
                    }
                }

          $inward_stock =  $inward_stock->groupBy('supplier_gst_id')->orderBy($sort_by,$sort_type)->get();        
         
            $outstanding_payment = array();
            if(isset($inward_stock)  && $inward_stock != '')
                {
                    foreach ($inward_stock AS $key=>$value) {

                        $outstanding = supplier_payment_detail::
                        //where('company_id', Auth::user()->company_id)
                           selectRaw('inward_stock_id,
                            GROUP_CONCAT(amount) AS amount,
                            GROUP_CONCAT(outstanding_payment) AS outstanding_payment,
                            GROUP_CONCAT(inward_stock_id) AS inward_stock_id,
                            GROUP_CONCAT(supplier_payment_detail_id) AS supplier_payment_detail_id')
                            ->where('outstanding_payment', '!=', NULL)
                            ->where('outstanding_payment', '>', 0)
                            ->whereRaw("find_in_set(inward_stock_id,'" . $value['inward_stock'] . "')")
                            ->get();



                        if(isset($outstanding) && $outstanding != '')
                        {
                            $outstanding_payment[$key]['supplier_gst_id'] = $value['supplier_gst_id'];
                            $outstanding_payment[$key] = $outstanding;
                        }
                         $outstanding_payment[$key]['totalamt'] = $value['totalamt'];
                    }
                }
                
            $total_invoice = inward_stock::
            //where('company_id',Auth::user()->company_id)
                whereNull('deleted_at')
                ->where('is_payment_clear','=','0')
                ->where('company_id', $supplier_company_id);
               

                if(isset($query['supplier_gst_id']) && $query['supplier_gst_id'] != ''){
                    $total_invoice->where('supplier_gst_id', '=', $query['supplier_gst_id']);
                }
               $total_invoice = $total_invoice
                ->get()
                ->count();
               

            $outstanding_detail = supplier_payment_detail::
            orwhere('company_id',$supplier_company_id)
                ->whereNull('deleted_at')
                ->where('outstanding_payment', '!=', NULL)
                ->where('outstanding_payment', '>', '0')
                ->with('inward_stock')
                ->with('supplier_gstdetail');

                if(isset($query['supplier_gst_id'])) {
                  $outstanding_detail->whereHas('inward_stock',function($q) use($query,$supplier_company_id){
                          $q->where('supplier_gst_id', '=', $query['supplier_gst_id']);
                           $q->orwhere('company_id', $supplier_company_id);
                         });
                }
               
                $outstanding_detail = $outstanding_detail->sum('amount');
                
            $amount_payable = supplier_payment_detail::
            orwhere('company_id',$supplier_company_id)
                ->whereNull('deleted_at')
                ->with('inward_stock')
                ->with('supplier_gstdetail');
                if(isset($query['supplier_gst_id'])) {
                $amount_payable->whereHas('inward_stock',function($q) use($query,$supplier_company_id){
                          $q->where('supplier_gst_id', '=', $query['supplier_gst_id']);
                           $q->orwhere('company_id', $supplier_company_id);
                         });
            }

                $amount_payable = $amount_payable->sum('outstanding_payment');
                
            return view('supplier::supplier/supplier_payment_data',compact('outstanding_payment','total_invoice','outstanding_detail','amount_payable'))->render();
        }   

    }


    public function exportpaymentsummary_data(Request $request)
    {
    
       Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
              $data            =      $request->all();
             $query      =      $data['supplier'];
          
            
           $inward_stock = inward_stock::where('company_id',Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->where('is_payment_clear','=','0')
            ->whereNull('warehouse_id')
            ->with('supplier_gstdetail')
            ->select('inward_stock_id','supplier_gst_id',DB::raw('SUM(total_gross) as totalamt'))
            ->selectRaw('GROUP_CONCAT(inward_stock_id) as inward_stock');
            
                if($query!='')
                  {
                    $inward_stock->where('supplier_gst_id', '=', $query)->where('company_id',Auth::user()->company_id);
                    
                  }

             $inward_stock =  $inward_stock->groupBy('supplier_gst_id')->get();

        $outstanding_payment = array();
        if(isset($inward_stock)  && $inward_stock != '')
        {
            foreach ($inward_stock AS $key=>$value) {

                $outstanding = supplier_payment_detail::where('company_id', Auth::user()->company_id)
                    ->selectRaw('inward_stock_id,
                    GROUP_CONCAT(amount) AS amount,
                    GROUP_CONCAT(outstanding_payment) AS outstanding_payment,
                    GROUP_CONCAT(inward_stock_id) AS inward_stock_id,
                    GROUP_CONCAT(supplier_payment_detail_id) AS supplier_payment_detail_id')
                    ->where('outstanding_payment', '!=', NULL)
                    ->where('outstanding_payment', '>', 0)
                    ->whereRaw("find_in_set(inward_stock_id,'" . $value['inward_stock'] . "')")
                    ->get();

                if(isset($outstanding) && $outstanding != '')
                {
                    $outstanding_payment[$key]['supplier_gst_id'] = $value['supplier_gst_id'];
                    $outstanding_payment[$key] = $outstanding;
                }
                 $outstanding_payment[$key]['totalamt'] = $value['totalamt'];
            

            $inward_stock = inward_stock::where('supplier_gst_id', $value['supplier_gst_id'])
            ->where('company_id',Auth::user()->company_id)
            ->groupBy('supplier_gst_id')
            ->select('inward_stock_id')
            ->selectRaw('GROUP_CONCAT(inward_stock_id) as inward_stock')
            ->get();

        $outstanding_detail = '';

        if(isset($inward_stock) &&  isset($inward_stock[0]) && $inward_stock[0]['inward_stock'] != '') {
            $outstanding_detail = supplier_payment_detail::whereNull('deleted_at')
                ->where('company_id',Auth::user()->company_id)
                ->with('inward_stock')
                ->where('outstanding_payment', '!=', NULL)
                ->where('outstanding_payment', '>', 0)
                ->whereRaw("find_in_set(inward_stock_id,'" . $inward_stock[0]['inward_stock'] . "')")
                ->get();
        }
        
        

        $outstanding_payment[$key]['payment_summary'] = $outstanding_detail;

            }

        }

                   $header       = [];
                   $header[]  =  'Company Name';
                   $header[]  =  'Supplier first Name';
                   $header[]  =  'Supplier last Name';
                   $header[]  =  'Supplier Mobile no.'; 
                   $header[]  =  'Outstanding Amount';
                   $header[]  =  'Paid Amount';
                   $header[]  =  'Amount Payable';
                   $header[]  =  'Invoice No.';
                   $header[]  =  'Invoice Date';
                   $header[]  =  'Invoice Due Date';
                   $header[]  =  'Ground Amount';
                   $header[]  =  'Paid Amount';
                   $header[]  =  'Remaining Amount';

             $excel = Excel::download(new supplier_payment_summary_export($outstanding_payment, $header), "Supplier_PaymentSummary-Export.xlsx");
             return $excel;
    }
}
