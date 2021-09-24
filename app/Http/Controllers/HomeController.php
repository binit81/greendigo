<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\state;
use App\company;
use App\country;
use App\home_navigation;
use App\home_navigations_data;
use Retailcore\CreditBalance\Models\customer_creditaccount;
use Retailcore\Sales\Models\payment_method;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\return_bill_payment;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\SalesReturn\Models\return_product_detail;
use Auth;
use DateTime;
use DateInterval;
use DatePeriod;
use Hash;
use App\User;
use DB;
use Log;
class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function license_key_(Request $request)
    {
        $data   =   $request->all();

        $decode =   base64_decode($data['apiKey']);
        $exp    =   explode('_',$decode);

        if(strpos($decode, '_') !== false)
        {
            $client_id          =   $exp[0];
            $project_id         =   $exp[1];
            $installation_id    =   $exp[2];
            $url    =   CLIENT_URL.'client_'.$client_id.'_'.$project_id.'_'.$installation_id.'.txt';
            $read    =   @file_get_contents($url);

            if($read=='')
            {
                return json_encode(array("Success"=>"False","Message"=>"In-valid License key, use the correct one to proceed further","url"=>""));
            }
            else
            {
                // Check mac address
                $remote_database = DB::connection('mysql2')->select("SELECT id,client_mac_address FROM tbl_software_master where cust_id='$client_id' and project_id='$project_id' and sw_installation_id='$installation_id'");

                $software_id    =   $remote_database[0]->id;
                $software_mac   =   $remote_database[0]->client_mac_address;

                if($software_mac!='')
                {
                    return json_encode(array("Success"=>"False","Message"=>"your License Key already in use"));
                }
                else
                {
                    $client_url     =   DEFAULT_COMPANY_URL.$client_id.".txt";

                    //////////// MAC ADDRESS /////////////
                    ob_start();
                    system('ipconfig/all');
                    $mycom  =   ob_get_contents();
                    ob_clean();
                    $findme =   "Physical";
                    $pmac   =   strpos($mycom, $findme);
                    $mac    =   substr($mycom,($pmac+36),17);
                    //////////// MAC ADDRESS /////////////

                    $remote_database = DB::connection('mysql2')->select("UPDATE tbl_software_master set client_mac_address='$mac' where cust_id='$client_id' and project_id='$project_id' and sw_installation_id='$installation_id'");

                    $fp = fopen($client_url, "w");
                    fwrite($fp,$read);
                    fclose($fp);
                    
                    ////////////////////// TERMS & CONDITIONS ///////////////////////////////

                    $tc_url     =   TC_URL.'rc_terms_and_conditions.html';
                    $read_    =   @file_get_contents($tc_url);

                    if($read_!='')
                    {
                        $t_and_c_url    =   DEFAULT_COMPANY_URL.'rc_terms_and_conditions.html';

                        $fp_ = fopen($t_and_c_url, "w");
                        fwrite($fp_,$read_);
                        fclose($fp_);
                    }

                    ////////////////////// TERMS & CONDITIONS ///////////////////////////////

                    $query  =   company::where('company_id', Auth::User()->company_id)
                    ->update([
                       'company_code' => $client_id,
                       'company_project_code' => $project_id,
                       'company_installation_code' => $installation_id,
                    ]);

                    $values         =   file_get_contents($client_url);
                    $exp_values     =   explode(',',$values);


                    $tax_type               =   str_replace('tax_type=','',$exp_values[0]);
                    $decimal_points         =   str_replace('decimal_points=','',$exp_values[1]);
                    $billtype               =   str_replace('billtype=','',$exp_values[2]);
                    $billprint_type         =   str_replace('billprint_type=','',$exp_values[3]);
                    $series_type            =   str_replace('series_type=','',$exp_values[4]);
                    $navigation_type        =   str_replace('navigation_type=','',$exp_values[5]);
                    $inward_type            =   str_replace('inward_type=','',$exp_values[6]);
                    $inward_calculation     =   str_replace('inward_calculation=','',$exp_values[7]);

                    $result     =   company_profile::updateOrCreate([
                        'company_profile_id' => '',
                        'company_id' => Auth::User()->company_id,
                        'tax_type' => $tax_type,
                        'decimal_points' => $decimal_points,
                        'billtype' => $billtype,
                        'series_type' => $series_type,
                        'billprint_type' => $billprint_type,
                        'navigation_type' => $navigation_type,
                        'inward_calculation' => $inward_calculation,
                        'inward_type' => $inward_type,
                        'state_id' => NULL,
                        'country_id' => '102',
                        'created_by' => Auth::User()->user_id,
                        'modified_by' => NULL,

                    ]);

                    if($inward_type ==1)
                    {
                        //fmcg
                        $hide[0]['module_name'] = 'inward_stock_show';
                        $hide[0]['active'] = 0;

                        $hide[1]['module_name'] = 'inward_stock';
                        $hide[1]['active'] = 1;

                        $hide[2]['module_name'] = 'batch_no_wise_report';
                        $hide[2]['active'] = 1;

                    }
                    if($inward_type ==2)
                    {
                        //garment
                        $hide[0]['module_name'] = 'inward_stock';
                        $hide[0]['active'] = 0;

                        $hide[1]['module_name'] = 'inward_stock_show';
                        $hide[1]['active'] = 1;

                        $hide[2]['module_name'] = 'batch_no_wise_report';
                        $hide[2]['active'] = 0;
                    }

                    if($hide != '')
                    {
                        foreach ($hide as $item) {
                            home_navigations_data::where('company_id',Auth::User()->company_id)
                            ->where('nav_url',$item['module_name'])
                            ->update(array(
                               'is_active' => $item['active'],
                                'modified_by' => Auth::User()->user_id
                            ));
                        }
                    }

                    // $license                =   str_replace('license=','',$exp_values[8]);
                    // $installation           =   str_replace('installation=','',$exp_values[9]);

                    return json_encode(array("Success"=>"True","Message"=>"Valid License key","url"=>"company_profile"));
                }
            }
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"In-valid License key, use the correct one to proceed further","url"=>""));
        }

    }

    public function index()
    {

        $userId             =   Auth::User()->user_id;
        $users      =   user::select('*')->where('user_id',$userId)->whereNull('deleted_at')->get();

        $state = state::all();
        $country = country::all();
        $company_profile = company_profile::where('company_id',Auth::user()->company_id)->first();

        $payment_methods = payment_method::where('is_active','=','1')->orderBy('payment_order','ASC')->get();

         $last_invoice_id = sales_bill::select('sales_bill_id')->where('company_id',Auth::user()->company_id)->orderBy('sales_bill_id', 'desc')->first();

        $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
        $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

        if($last_invoice_id == '')
        {
            $last_invoice_id = 1;
        }
        else
        {
            $last_invoice_id = $last_invoice_id['sales_bill_id']  + 1;
        }


        $invoiceno          =       $last_invoice_id.'/'.$f1.'-'.$f2;
        $chargeslist      =   product::select('product_id','product_name')
            ->where('company_id',Auth::user()->company_id)
            ->where('item_type','=',2)
            ->get();

        if($company_profile != ''  && $company_profile != null && $company_profile['company_name'] != '')
            {
                $billtype    =        $company_profile['billtype'];
                $billprefix  =        $company_profile['bill_number_prefix'];

                if($billprefix != '' || $billprefix!= null)
                {
                    $invoiceno          =       $billprefix.$invoiceno;
                }
                else
                {
                  $invoiceno           =       $invoiceno;
                }

                 session(['ccompany_profile'=>1]);
                 $company_profile    =    session('ccompany_profile');


                return view('dashboard',compact('users'));
            }
            else
            {
                 session(['ccompany_profile'=>0]);
                 $company_profile    =    session('ccompany_profile');

                return view('company_profile::company_profile/company_profile',compact('company_profile','state','country','users'));
            }

    }

    public function logout()
    {
        auth()->logout();
        return redirect('/login');
    }

    public function dashboard()
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $userId             =   Auth::User()->user_id;
        $company_id         =   Auth::user()->company_id;

        $state = state::all();
        $country = country::all();

        ////CHECK FOR COMPANY PROFILE
        $company_profile = company_profile::where('company_id',Auth::user()->company_id)->first();


        if($company_profile == ''  || $company_profile == null )
        {
            session(['ccompany_profile'=>0]);
            $company_profile    =    session('ccompany_profile');

            return view('company_profile::company_profile/company_profile',compact('company_profile','state','country'));
        }
        else {


            ///END OF CHECK COMPANY PROFILE


            //////////////////////////// TODAY SALES /////////////////////////////

            $today = date('Y-m-d');

            $todaySales = sales_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$today' and '$today'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $todayReturn = return_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$today' and '$today'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $finalTodaySales = $todaySales - $todayReturn;

            ///////////////////////////// YESTERDAY SALES //////////////////////////////

            $yesterday  =   date('Y-m-d',strtotime("-1 days"));

            $yesterday_Sales = sales_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$yesterday' and '$yesterday'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $yesterday_Return = return_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$yesterday' and '$yesterday'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $finalyesterday_Sales = $yesterday_Sales - $yesterday_Return;

            if($finalTodaySales>$finalyesterday_Sales)
            {
                $today_arrow    =   1;  // Positive
            }
            else
            {
                $today_arrow    =   0;  // Negative
            }

            /////////////////////////// MONTH SALES //////////////////////////////

            $firstDay = date('Y-m-01', strtotime($today));
            $lastDay = date('Y-m-t', strtotime($today));

            $monthSales = sales_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$firstDay' and '$lastDay'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $monthReturn = return_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$firstDay' and '$lastDay'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $finalMonthSales = $monthSales - $monthReturn;

            ///////////////////////////// LAST MONTH SALES //////////////////////////////

            $lastmonth  =   date('Y-m-d',strtotime("-1 months"));

            $firstDay_lastmonth = date('Y-m-01', strtotime($lastmonth));
            $lastDay_lastmonth = date('Y-m-t', strtotime($lastmonth));

            $lastmonth_monthSales = sales_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$firstDay_lastmonth' and '$lastDay_lastmonth'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $lastmonth_monthReturn = return_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$firstDay_lastmonth' and '$lastDay_lastmonth'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $lastmonth_finalMonthSales = $lastmonth_monthSales - $lastmonth_monthReturn;

            if($finalMonthSales>$lastmonth_finalMonthSales)
            {
                $month_arrow    =   1;  // Positive
            }
            else
            {
                $month_arrow    =   0;  // Negative
            }

            //////////////////////////// YEAR SALES ///////////////////////////////

            if (date('m') > 4) {
                $year = date('Y') + 1;
                $fdate = date('Y')."-04-01";
                $tdate = $year. "-03-".date('t');
            } else {
                $year = date('Y') - 1;
                $fdate = $year."-04-01";
                $tdate = date('Y') . "-03-" . date('t');
            }

            $yearSales = sales_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$fdate' and '$tdate'")
                // ->whereRaw("Date(sales_bills.created_at) between '$fdate' and '$tdate'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $yearReturn = return_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$fdate' and '$tdate'")
                // ->whereRaw("Date(return_bills.created_at) between '$fdate' and '$tdate'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $finalYearSales = $yearSales - $yearReturn;

            ///////////////////////////// LAST YEAR SALES //////////////////////////////

            $lastyear  =   date('Y-m-d',strtotime("-1 years"));

            if (date('m',strtotime("-1 years"))> 4) {
                $last_year = date('Y',strtotime("-1 years")) + 1;
                $last_fdate = date('Y',strtotime("-1 years"))."-04-01";
                $last_tdate = $last_year. "-03-".date('t',strtotime("-1 years"));
            } else {
                $last_year = date('Y',strtotime("-1 years")) - 1;
                $last_fdate = $last_year."-04-01";
                $last_tdate = date('Y',strtotime("-1 years")) . "-03-" . date('t',strtotime("-1 years"));
            }

            $lastyear_yearSales = sales_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$last_fdate' and '$last_tdate'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $lastyear_yearReturn = return_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$last_fdate' and '$last_tdate'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

            $lastyear_finalYearSales = $lastyear_yearSales - $lastyear_yearReturn;

            if($finalYearSales>$lastyear_finalYearSales)
            {
                $year_arrow    =   1;  // Positive
            }
            else
            {
                $year_arrow    =   0;  // Negative
            }

            ////////////////////////////// TOTAL SALES TODAY COUNT /////////////////////////////

            $salesCount_today = sales_bill::select('sales_bill_id')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$today' and '$today'")
                // ->whereRaw("Date(sales_bills.created_at) between '$fdate' and '$tdate'")
                ->where('deleted_at','=',NULL)
                ->count('sales_bill_id');

            ////////////////////////////// TOTAL SALES MONTH COUNT /////////////////////////////

            $salesCount_month = sales_bill::select('sales_bill_id')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$firstDay' and '$lastDay'")
                // ->whereRaw("Date(sales_bills.created_at) between '$fdate' and '$tdate'")
                ->where('deleted_at','=',NULL)
                ->count('sales_bill_id');

            ////////////////////////////// TOTAL SALES YEAR COUNT /////////////////////////////

            $salesCount_year = sales_bill::select('sales_bill_id')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$fdate' and '$tdate'")
                // ->whereRaw("Date(sales_bills.created_at) between '$fdate' and '$tdate'")
                ->where('deleted_at','=',NULL)
                ->count('sales_bill_id');

            /////////////////////////////// LOW OUT OF STOCK PRODUCTS ////////////////////////////////

            //, price_masters.product_qty, count(products.product_id) as totalCount

            // $lowStock = DB::select(DB::raw("(SELECT p.* FROM `products` as p WHERE p.alert_product_qty > (SELECT SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = p.product_id))"));

            // $lowStockCount = product::select('count()')
            // ->where('products.alert_product_qty', '>' ,DB::raw("(SELECT SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = products.product_id)"))
            //  ->withCount([
            //         'price_master as totalstock' => function($fquery)  {
            //             $fquery->select(DB::raw('SUM(product_qty)'));
            //         }
            //     ])->take(5)->get();

            $lquery = product::select('*')->where('company_id', Auth::User()->company_id)
            ->where('products.alert_product_qty', '>' ,DB::raw("(SELECT SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = products.product_id)"))
             ->withCount([
                    'price_master as totalstock' => function($fquery)  {
                        $fquery->select(DB::raw('SUM(product_qty)'));
                    }
                ]);

             $lowStockcount  =  $lquery->count();
             $lowStock       =  $lquery->take(5)->orderBy('product_id','DESC')->get();

            // echo '<pre>';
            // print_r($lowStock); exit;

            /////////////////////////////// CUSTOMER OUTSTANDING PAYMENTS ////////////////////////////////

            $customerbaldata = customer_creditaccount::select("*", DB::raw("SUM(credit_amount) as totalcreditamount"), DB::raw("SUM(balance_amount) as totalbalance"), DB::raw("(SELECT SUM(customer_creditreceipt_details.payment_amount) FROM customer_creditreceipt_details WHERE customer_creditreceipt_details.customer_id = customer_creditaccounts.customer_id and deleted_at IS NULL GROUP BY customer_creditreceipt_details.customer_id) as recdamt"))->groupBy('customer_id')->orderBy('customer_creditaccount_id', 'DESC')->where('deleted_at', '=', NULL)->whereRaw('balance_amount!=0')->with('customer')->take(5)->get();


            //ADDED BY HEMAXI..FOR SHOW NEAR EXPIRY DATE PRODUCT ACCORDING TO ALERT BEFORE PRODUCT EXPIRY DATE(FROM PRODUCT MODULE)
		 $expiry_near_product = inward_product_detail::whereNotNull('expiry_date')
                ->where('pending_return_qty','!=',0)
                ->with('product')
		        ->whereHas('product',function ($q)
                {
                    $q->where('products.days_before_product_expiry','!=',0);
		        })
                ->groupBy('batch_no','product_id')->take(5)->get();
            //END OF CODE OF HEMAXI FOR GETTING PRODUCT WHICH IS NEAREST TO ALERT DAYS


            // DAYS LOOP FOR CURRENT MONTH

            $days   =   '';
            $days_1 =   '';
            $daySales_  =   '';
            $start  =   $month = strtotime($firstDay);
            $end    =   strtotime($lastDay);
            while($month <= $end)
            {
                 $days   .=   '"'.date('d', $month).'",';
                 $days_1  =   date('Y-m-d', $month);
                 $month = strtotime("+1 day", $month);

                 $daySales = sales_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$days_1' and '$days_1'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

                $dayReturn = return_bill::select('total_bill_amount')->where('company_id', $company_id)
                ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$days_1' and '$days_1'")
                ->where('deleted_at','=',NULL)
                ->sum('total_bill_amount');

                $daySales_  .=   ''.$daySales-$dayReturn.',';
            }

            $days_          =   substr($days,0,-1);
            $daySales__     =   substr($daySales_,0,-1);

            /// PAYMENT METHODS MONTHLY

            $payment_method_day     =   payment_method::select('payment_method_name','payment_method_id')->where('is_active',1)->get();

            foreach($payment_method_day as $Key_day=>$mode_day)
            {

                 $payment_sales_day  =   sales_bill_payment_detail::whereNull('deleted_by')->where('payment_method_id',$mode_day->payment_method_id)
                    ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($company_id,$today){
                       $q->whereRaw('company_id='.$company_id.'');
                       $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y')='$today'");
                    })
                  ->sum('total_bill_amount');

                $payment_sales_return_day  =   return_bill_payment::whereNull('deleted_by')->where('payment_method_id',$mode_day->payment_method_id)
                ->with('return_bill')->whereHas('return_bill',function ($q) use ($company_id,$today){
                       $q->where('company_id',$company_id);
                       $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y')='$today'");
                    })
                ->sum('total_bill_amount');

                $final_sales_day    =   $payment_sales_day - $payment_sales_return_day;

                $payments_day[]   =   array("mode"=>$mode_day->payment_method_name,"amounts"=>$final_sales_day);

            }

            /// PAYMENT METHODS MONTHLY

            $payment_method     =   payment_method::select('payment_method_name','payment_method_id')->where('is_active',1)->get();

            foreach($payment_method as $Key=>$mode)
            {

                 $payment_sales  =   sales_bill_payment_detail::whereNull('deleted_by')->where('payment_method_id',$mode->payment_method_id)
                    ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($company_id,$firstDay,$lastDay){
                       $q->whereRaw('company_id='.$company_id.'');
                       $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$firstDay' and '$lastDay'");
                    })
                  ->sum('total_bill_amount');

                $payment_sales_return  =   return_bill_payment::whereNull('deleted_by')->where('payment_method_id',$mode->payment_method_id)
                ->with('return_bill')->whereHas('return_bill',function ($q) use ($company_id,$firstDay,$lastDay){
                       $q->where('company_id',$company_id);
                       $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$firstDay' and '$lastDay'");
                    })
                ->sum('total_bill_amount');

                $final_sales    =   $payment_sales - $payment_sales_return;

                $payments[]   =   array("mode"=>$mode->payment_method_name,"amounts"=>$final_sales);

            }

            ////// MONTH WISE FILTER SUMMARY FOR GRAPH

            $start    = new DateTime($fdate);
            $start->modify('first day of this month');
            $end      = new DateTime($today);
            $end->modify('first day of next month');
            $interval = DateInterval::createFromDateString('1 month');
            $period   = new DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                $months[]     =    array('key'=>$dt->format("m"),'month'=>$dt->format("M"));
            }
            
            $months_    =   [];

            $nextmonth  =  date('Y-m-d', strtotime('+1 month', strtotime($today)));
            $start_    = new DateTime($nextmonth);
            $start_->modify('first day of this month');
            $end_      = new DateTime($tdate);
            $end_->modify('first day of next month');
            $interval_ = DateInterval::createFromDateString('1 month');
            $period_   = new DatePeriod($start_, $interval_, $end_);

            foreach ($period_ as $dt_) {
                $months_[]     =    array('key'=>$dt_->format("m"),'month'=>$dt_->format("M"));
            }

            ///////////// Latest 5 Bills


            $bills = sales_bill::select("sales_bills.*",DB::raw("(SELECT SUM(sales_product_details.discount_amount + sales_product_details.overalldiscount_amount) FROM sales_product_details WHERE sales_product_details.sales_bill_id = sales_bills.sales_bill_id GROUP BY sales_product_details.sales_bill_id)  as totaldiscount"))->with('customer')
                ->where('company_id',Auth::user()->company_id)
                ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$today' and '$today'")
                ->where('deleted_at','=',NULL)
                ->where('is_active','=',1)
                ->orderBy('sales_bill_id', 'DESC');

            $salescount  =  $bills->count();
            $sales     =   $bills->take(5)->get();

            $return = return_bill::select("return_bills.*",DB::raw("(SELECT SUM(return_product_details.discount_amount + return_product_details.overalldiscount_amount) FROM return_product_details WHERE return_product_details.return_bill_id = return_bills.return_bill_id GROUP BY return_product_details.return_bill_id)  as totaldiscount"))
            ->with('reference')
            ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$today' and '$today'")
            ->with('sales_bill')
            // ->with('return_bill_payment')
            ->with('customer')
            ->where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->orderBy('return_bill_id', 'DESC');

            $returncount  =  $return->count();
            $returnbill   =  $return->take(5)->get();

            $topSelling =   sales_product_detail::select('sales_products_detail_id','product_id','total_amount',DB::raw("sum(qty)"))->groupBy('product_id')
            ->orderBy('sum(qty)','DESC')->with('product')->take(5)->get();

            $topReturn =   return_product_detail::select('return_product_detail_id','product_id','total_amount',DB::raw("sum(qty)"))->groupBy('product_id')
            ->orderBy('sum(qty)','DESC')->with('product')->take(5)->get();

            //////////////////////////////Profit calculation Todays ///////////////////////////////

            $productdetails =  sales_product_detail::select('*')
                  ->where('company_id',Auth::user()->company_id)
                  ->where('deleted_by','=',NULL)
                  ->where('qty','!=',0)
                  ->where('product_type',1)
                  ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($today){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$today' and '$today'");
                    })
                  ->get();

                  $totalsalesprofit = 0;  
                  foreach($productdetails AS $saleskey=>$sales_value)
                  { 
                    
                    $total_price = 0;

                    if($sales_value['inwardids'] !='' || $sales_value['inwardids'] !=null)
                    {

                       $inwardids  = explode(',' ,substr($sales_value['inwardids'],0,-1));
                       $inwardqtys = explode(',' ,substr($sales_value['inwardqtys'],0,-1));


                        foreach($inwardids as $inidkey=>$inids)
                        {
                              $cost_price = inward_product_detail::select('cost_rate')->find($inids);

                              $total_price += $cost_price['cost_rate'] * $inwardqtys[$inidkey];
                        }
                        $averagecost      =   ($total_price / $sales_value['qty']) * $sales_value['qty'];
                        $profitamt        =   $sales_value->sellingprice_afteroverall_discount  - $averagecost;
                        $totalsalesprofit   +=  $profitamt;
                       
                    }
                    else
                    {
                        $averagecost      =   0;
                        $profitamt        =   $sales_value->sellingprice_afteroverall_discount  - $averagecost;
                        $totalsalesprofit   +=  $profitamt;
                        
                    }
                }

                 $rtproductdetails =  return_product_detail::select('*')
                  ->where('company_id',Auth::user()->company_id)
                  ->where('deleted_by','=',NULL)
                  ->where('qty','!=',0)
                  ->where('product_type',1)
                  ->with('return_bill')->whereHas('return_bill',function ($q) use ($today){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$today' and '$today'");
                    })
                  ->get();

                  $totalreturnprofit = 0;  
                  foreach($rtproductdetails AS $returnkey=>$return_value)
                  { 
                    
                    $rtotal_price = 0;
                    // print_r($return_value['inwardids']);

                    if($return_value['inwardids'] !='' || $return_value['inwardids'] !=null)
                    {

                       $rinwardids  = explode(',' ,substr($return_value['inwardids'],0,-1));
                       $rinwardqtys = explode(',' ,substr($return_value['inwardqtys'],0,-1));


                        foreach($rinwardids as $rinidkey=>$rinids)
                        {
                              $rcost_price = inward_product_detail::select('cost_rate')->find($rinids);

                              $rtotal_price += $rcost_price['cost_rate'] * $rinwardqtys[$rinidkey];
                        }
                        $averagecost      =   ($rtotal_price / $return_value['qty']) * $return_value['qty'];
                        $profitamt        =   $return_value->sellingprice_afteroverall_discount  - $averagecost;
                        $totalreturnprofit   +=  $profitamt;
                       
                    }
                    else
                    {
                        $averagecost      =   0;
                        $profitamt        =   $return_value->sellingprice_afteroverall_discount  - $averagecost;
                        $totalreturnprofit   +=  $profitamt;
                        
                    }
                }

              $todayprofit   =     $totalsalesprofit - $totalreturnprofit;

              ///////////// Profit Amount this MOnth


                $productdetails_month =  sales_product_detail::select('*')
                  ->where('company_id',Auth::user()->company_id)
                  ->where('deleted_by','=',NULL)
                  ->where('qty','!=',0)
                  ->where('product_type',1)
                  ->with('sales_bill')->whereHas('sales_bill',function ($q) use ($firstDay,$lastDay){
                   $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$firstDay' and '$lastDay'");
                    })
                  ->get();

                  $totalsalesprofit_month = 0;  
                  foreach($productdetails_month AS $saleskey=>$sales_value)
                  { 
                    
                    $total_price = 0;

                    if($sales_value['inwardids'] !='' || $sales_value['inwardids'] !=null)
                    {

                       $inwardids  = explode(',' ,substr($sales_value['inwardids'],0,-1));
                       $inwardqtys = explode(',' ,substr($sales_value['inwardqtys'],0,-1));


                        foreach($inwardids as $inidkey=>$inids)
                        {
                              $cost_price = inward_product_detail::select('cost_rate')->find($inids);

                              $total_price += $cost_price['cost_rate'] * $inwardqtys[$inidkey];
                        }
                        $averagecost      =   ($total_price / $sales_value['qty']) * $sales_value['qty'];
                        $profitamt        =   $sales_value->sellingprice_afteroverall_discount  - $averagecost;
                        $totalsalesprofit_month   +=  $profitamt;
                       
                    }
                    else
                    {
                        $averagecost      =   0;
                        $profitamt        =   $sales_value->sellingprice_afteroverall_discount  - $averagecost;
                        $totalsalesprofit_month   +=  $profitamt;
                        
                    }
                }

                 $rtproductdetails =  return_product_detail::select('*')
                  ->where('company_id',Auth::user()->company_id)
                  ->where('deleted_by','=',NULL)
                  ->where('qty','!=',0)
                  ->where('product_type',1)
                  ->with('return_bill')->whereHas('return_bill',function ($q) use ($firstDay,$lastDay){
                   $q->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$firstDay' and '$lastDay'");
                    })
                  ->get();

                  $totalreturnprofit_month = 0;  
                  foreach($rtproductdetails AS $returnkey=>$return_value)
                  { 
                    
                    $rtotal_price = 0;
                    // print_r($return_value['inwardids']);

                    if($return_value['inwardids'] !='' || $return_value['inwardids'] !=null)
                    {

                       $rinwardids  = explode(',' ,substr($return_value['inwardids'],0,-1));
                       $rinwardqtys = explode(',' ,substr($return_value['inwardqtys'],0,-1));


                        foreach($rinwardids as $rinidkey=>$rinids)
                        {
                              $rcost_price = inward_product_detail::select('cost_rate')->find($rinids);

                              $rtotal_price += $rcost_price['cost_rate'] * $rinwardqtys[$rinidkey];
                        }
                        $averagecost_month      =   ($rtotal_price / $return_value['qty']) * $return_value['qty'];
                        $profitamt_month        =   $return_value->sellingprice_afteroverall_discount  - $averagecost_month;
                        $totalreturnprofit_month   +=  $profitamt_month;
                       
                    }
                    else
                    {
                        $averagecost_month      =   0;
                        $profitamt_month        =   $return_value->sellingprice_afteroverall_discount  - $averagecost_month;
                        $totalreturnprofit_month   +=  $profitamt_month;
                        
                    }
                }

              $todayprofit_month   =     $totalsalesprofit_month - $totalreturnprofit_month;
              

            // $todayProfit    =   sales_product_detail::select('product_id','')

            // echo '<pre>'; print_r($payments_day); exit;

            return view('dashboard', compact('finalTodaySales', 'finalMonthSales', 'finalYearSales', 'salesCount_year', 'lowStock','lowStockcount', 'customerbaldata','expiry_near_product','salesCount_today','salesCount_month','days_','daySales__','todayReturn','monthReturn','yearReturn','payments','today_arrow','month_arrow','year_arrow','months','months_','sales','returnbill','topSelling','payments_day','salescount','returncount','topReturn','todayprofit','todayprofit_month'));
        }
    }

    public function dashboard_sort(Request $request)
    {
        $data   =   $request->all();

        $url   =   DASHBOARD_SORT_URL.DASHBOARD_SORT_FILE;

        $fp = fopen($url, 'w');
        fwrite($fp, $data['sorting']);
        fclose($fp);

        // print_r($fp); //
    }

    public function graph_values(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $userId             =   Auth::User()->user_id;
        $company_id         =   Auth::user()->company_id;

        $month       =   $request->all();
        $today = date('Y-'.$month['month'].'-d');

        $firstDay   =   date('Y-m-01', strtotime($today));
        $lastDay    =   date('Y-m-t', strtotime($today));

        // DAYS LOOP FOR CURRENT MONTH

        $days   =   '';
        $days_1 =   '';
        $daySales_  =   '';
        $start  =   $month = strtotime($firstDay);
        $end    =   strtotime($lastDay);
        while($month <= $end)
        {
             $days   .=   ''.date('d', $month).',';
             $days_1  =   date('Y-m-d', $month);
             $month = strtotime("+1 day", $month);

             $daySales = sales_bill::select('total_bill_amount')->where('company_id', $company_id)
            ->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$days_1' and '$days_1'")
            ->where('deleted_at','=',NULL)
            ->sum('total_bill_amount');

            $dayReturn = return_bill::select('total_bill_amount')->where('company_id', $company_id)
            ->whereRaw("STR_TO_DATE(return_bills.bill_date,'%d-%m-%Y') between '$days_1' and '$days_1'")
            ->where('deleted_at','=',NULL)
            ->sum('total_bill_amount');

            $daySales_  .=   ''.($daySales-$dayReturn).',';
        }

        $days_          =   substr($days,0,-1);
        $daySales__     =   substr($daySales_,0,-1);

        // echo '<pre>'; print_r($daySales_); exit;

        return json_encode(array("Success"=>"True","days"=>$days_,"daySales"=>$daySales__));


    }

    public function showChangePasswordForm()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        return view('auth.changePassword');
    }

    public function login()
    {
        return view('auth.login');
    }

    public function my_profile()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $userId             =   Auth::User()->user_id;

        $state = state::all();
        $country = country::all();

        $result     =   user::where('user_id',$userId)->whereNull('deleted_at')->get();
        return view('auth.my_profile',compact('result','state','country'));
    }


    public function changePassword(Request $request){
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            // The passwords matches
            return redirect()->back()->with("error","Your current password does not matches with the password you provided. Please try again.");
        }
        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
            //Current password and new password are same
            return redirect()->back()->with("error","New Password cannot be same as your current password. Please choose a different password.");
        }
        $validatedData = $request->validate([
            'current-password' => 'required',
            'new-password' => 'required|string|min:6|confirmed',
        ]);
        //Change Password
        $user = Auth::user();
        $user->password = bcrypt($request->get('new-password'));
        $user->save();
        auth()->logout();
        return redirect('/')->with("success","Password changed successfully !");
    }

    public function universal_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $universalKeyword        =   $request->search_val;

        $result     =   home_navigations_data::select('home_navigation_id','nav_tab_display_name','nav_url')->where('nav_keywords','LIKE','%'.$universalKeyword.'%')
        ->with('home_navigation')->get();

        return json_encode(array("Success"=>"True","Data"=>$result));

    }
}
