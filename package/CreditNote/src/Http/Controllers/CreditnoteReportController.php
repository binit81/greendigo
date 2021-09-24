<?php

namespace Retailcore\CreditNote\Http\Controllers;

use Retailcore\CreditNote\Models\creditnote_report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Retailcore\CreditNote\Models\customer_creditnote;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\SalesReturn\Models\return_bill_payment;
use Retailcore\Products\Models\product\product;
use Retailcore\Customer\Models\customer\customer;
use Retailcore\Customer\Models\customer\customer_address_detail;
use Retailcore\Sales\Models\payment_method;
use App\state;
use App\country;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\company;
use Retailcore\CreditNote\Models\creditnote_payment;
use Auth;
use DB;
use Log;


class CreditnoteReportController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

          $compname       =   company::where('company_id',Auth::user()->company_id)
                                        ->first();
          $companyname    =   $compname['company_name'];

        $creditnotepayment  =  array();
         $receipts = customer_creditnote::select("*",DB::raw("(SELECT SUM(creditnote_payments.used_amount) FROM creditnote_payments WHERE creditnote_payments.customer_creditnote_id = customer_creditnotes.customer_creditnote_id and deleted_at IS NULL GROUP BY creditnote_payments.customer_creditnote_id) as usedamount"))->where('company_id',Auth::user()->company_id)->where('deleted_at','=',NULL)->orderBy('customer_creditnote_id', 'DESC')->with('customer')->paginate(10);


        return view('creditnote::creditnote_report',compact('receipts','creditnotepayment','get_store','companyname'));
    }
    function datewise_cuscreditnotedetail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {

            $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();
            $data            =      $request->all();
            $creditnotepayment = array();
            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = isset($data['query']) ? $data['query']  : '';

             if(isset($query) && $query != '' && isset($query['store_name']) && $query['store_name'] != '')
            {
                 $company_id      =   $query['store_name'];
                 $compname        =   company_profile::where('company_profile_id',$company_id)
                                            ->first();
                 $companyname    =   $compname['full_name'];
            }
            else
            {
                $company_id      =   Auth::user()->company_id;
                $compname        =   company::where('company_id',$company_id)
                                            ->first();
                 $companyname    =   $compname['company_name'];
            }


            $cquery        =      customer_creditnote::select("*",DB::raw("(SELECT SUM(creditnote_payments.used_amount) FROM creditnote_payments WHERE creditnote_payments.customer_creditnote_id = customer_creditnotes.customer_creditnote_id and deleted_at IS NULL GROUP BY creditnote_payments.customer_creditnote_id) as usedamount"))->where('company_id',$company_id);


            if(isset($query) && $query != '' && $query['customerid'] != '')
            {

                if(strpos($query['customerid'], '_') !== false)
                {
                    $cusname   =   explode('_',$query['customerid']);
                    $cus_name   =  $cusname[0];
                    $cus_mobile  =  $cusname[1];
                }
                else
                {
                    $cus_name   =   $query['customerid'];
                    $cus_mobile =   $query['customerid'];
                }

                 $result = customer::select('customer_id')
                 ->where('company_id',$company_id)
                 ->where('deleted_at','=',NULL)
                 ->where('customer_name', 'LIKE', "%$cus_name%")
                 ->orwhere('customer_mobile', 'LIKE', "%$cus_mobile%")
                 ->get();

                 $cquery->whereIn('customer_id',$result);
            }
            if(isset($query) && $query != '' && $query['billno'] != '')
            {
                $cquery->where('creditnote_no', 'like', '%'.$query['billno'].'%');

            }
            if(isset($query) && $query != '' && $query['from_date'] != '' && $query['to_date'] != '')
            {

                 $rstart           =      date("Y-m-d",strtotime($query['from_date']));
                 $rend             =      date("Y-m-d",strtotime($query['to_date']));

                $cquery->whereRaw("STR_TO_DATE(creditnote_date,'%d-%m-%Y') between '$rstart' and '$rend'");
                // $cquery->where('creditnote_date','>=',$query['from_date'])->where('creditnote_date','<=',$query['to_date']);

            }



            $receipts = $cquery->orderBy($sort_by, $sort_type)->where('deleted_by','=',NULL)->with('customer')->paginate(10);



            return view('creditnote::creditnote_reportdata',compact('receipts','creditnotepayment','get_store','companyname'));
        }


    }
  public function view_creditnote_popup(Request $request)
  {
      Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {

            $creditnotepayment = creditnote_payment::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->where('customer_creditnote_id','=',$request->billno)
                ->select('*')
                ->with('sales_bill')
                ->with('return_bill')
                ->with('customer')
                ->get();


                  return view('creditnote::creditnote_popup',compact('creditnotepayment'));

         }

    }



}
