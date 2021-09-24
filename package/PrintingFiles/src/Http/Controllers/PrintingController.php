<?php

namespace Retailcore\PrintingFiles\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\SalesReturn\Models\return_bill_payment;
use Retailcore\CreditBalance\Models\customer_creditaccount;
use Retailcore\CreditBalance\Models\customer_creditreceipt;
use Retailcore\CreditBalance\Models\customer_creditreceipt_detail;
use Retailcore\Products\Models\product\product;
use Retailcore\GST_Slabs\Models\GST_Slabs\gst_slabs_master;
use Retailcore\Customer\Models\customer\customer;
use Retailcore\Customer\Models\customer\customer_address_detail;
use Retailcore\Sales\Models\payment_method;
use App\state;
use App\country;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\CreditNote\Models\customer_creditnote;
use Retailcore\CreditNote\Models\creditnote_payment;
use Retailcore\Consignment\Models\consign_bill;
use Retailcore\Consignment\Models\consign_products_detail;
use Retailcore\Consignment\Models\consign_payment_detail;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer;
use Auth;
use DB;



class PrintingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function index()
    {


    }




    public function print_bill(Request $request)
    {
        $billid  = decrypt($request->id);

        $state = state::all();
        $country = country::all();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title','bill_calculation')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';
        $currtitle       = $state_id[0]['currency_title'];
        $bill_calculation = $state_id[0]['bill_calculation'];

        if($tax_type==1)
        {
                $currency_title  = $currtitle==''||$currtitle==NULL?'INR':$currtitle;
        }
        else
        {
                $currency_title  = 'INR';
        }



       $payment_methods = payment_method::where('is_active','=','1')->get();

        $billingdata = sales_bill::where([
            ['sales_bill_id','=',$billid],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->with('reference')
            ->with('sales_bill_payment_detail')
            ->with('customer')
            ->with('customer_address_detail')
            ->with('company')
            ->with('user')
            ->withCount([
                    'sales_product_detail as overalldiscount' => function($fquery) {
                        $fquery->select(DB::raw('SUM(discount_amount + overalldiscount_amount )'));
                    }
                ])
            ->first();

            $customer_id  = $billingdata['customer_id'];
            $previouscreditamount  =  customer_creditaccount::where('company_id',Auth::user()->company_id)
                                                    ->where('customer_id',$customer_id)
                                                    ->where('balance_amount','>',0)
                                                    ->where('sales_bill_id','!=',$billingdata['sales_bill_id'])
                                                    ->sum('balance_amount');

            $currentcreditamount  =  customer_creditaccount::where('company_id',Auth::user()->company_id)
                                    ->where('customer_id',$customer_id)
                                    ->where('balance_amount','>',0)
                                    ->where('sales_bill_id','=',$billingdata['sales_bill_id'])
                                    ->sum('balance_amount');

          

             $billingproductdata = sales_product_detail::
             where('company_id',Auth::user()->company_id)
            ->where('sales_bill_id','=',$billid)
            ->where('qty','!=',0)
            ->with('product.product_features_relationship')
            ->with('batchprice_master')
            ->get();

           
            $productcount = sales_product_detail::where('company_id',Auth::user()->company_id)
            ->where('sales_bill_id','=',$billid)
            ->count();

             $gstdata = sales_product_detail::select('cgst_percent','sgst_percent','igst_percent',DB::raw("SUM(sales_product_details.sellingprice_afteroverall_discount) as tottaxablevalue"),DB::raw("SUM(sales_product_details.cgst_amount) as totcgstamount"),DB::raw("SUM(sales_product_details.sgst_amount) as totsgstamount"),DB::raw("SUM(sales_product_details.igst_amount) as totigstamount"),DB::raw("SUM(sales_product_details.total_amount) as totgrand"))->where('sales_bill_id','=',$billid)->groupBy('igst_percent')->get();

        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($billingproductdata AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {
                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);
                        $billingproductdata[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }



        return view('printingfiles::sales/print_bill',compact('payment_methods','state','country','billingdata','billingproductdata','productcount','gstdata','tax_type','taxname','currency_title','bill_calculation','customer_id','previouscreditamount','currentcreditamount'));

    }
    public function thermalprint_bill(Request $request)
    {

        $billid  = decrypt($request->id);

        $state = state::all();
        $country = country::all();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';
        $currtitle       = $state_id[0]['currency_title'];
        if($tax_type==1)
        {
                $currency_title  = $currtitle==''||$currtitle==NULL?'&#x20b9':$currtitle;
        }
        else
        {
                $currency_title  = '&#x20b9';
        }


       $payment_methods = payment_method::where('is_active','=','1')->get();

        $billingdata = sales_bill::where([
            ['sales_bill_id','=',$billid],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->with('reference')
            ->with('sales_bill_payment_detail')
            ->with('customer')
            ->with('customer_address_detail')
            ->with('company')
            ->with('user')
            ->first();

            $customer_id  = $billingdata['customer_id'];
            $previouscreditamount  =  customer_creditaccount::where('company_id',Auth::user()->company_id)
                                                    ->where('customer_id',$customer_id)
                                                    ->where('balance_amount','>',0)
                                                    ->where('sales_bill_id','!=',$billingdata['sales_bill_id'])
                                                    ->sum('balance_amount');

            $currentcreditamount  =  customer_creditaccount::where('company_id',Auth::user()->company_id)
                                    ->where('customer_id',$customer_id)
                                    ->where('balance_amount','>',0)
                                    ->where('sales_bill_id','=',$billingdata['sales_bill_id'])
                                    ->sum('balance_amount');

             $billingproductdata = sales_product_detail::where('company_id',Auth::user()->company_id)
            ->where('sales_bill_id','=',$billid)
            ->where('qty','!=',0)
            ->with('product')
            ->get();

            $gstdata = sales_product_detail::select('cgst_percent','sgst_percent','igst_percent',DB::raw("SUM(sales_product_details.sellingprice_afteroverall_discount) as tottaxablevalue"),DB::raw("SUM(sales_product_details.cgst_amount) as totcgstamount"),DB::raw("SUM(sales_product_details.sgst_amount) as totsgstamount"),DB::raw("SUM(sales_product_details.igst_amount) as totigstamount"),DB::raw("SUM(sales_product_details.total_amount) as totgrand"))->where('sales_bill_id','=',$billid)->groupBy('igst_percent')->get();



        return view('printingfiles::sales/thermalprint_bill',compact('payment_methods','state','country','billingdata','billingproductdata','gstdata','tax_type','taxname','currency_title','customer_id','previouscreditamount','currentcreditamount'));

    }
    public function printconsign_challan(Request $request)
    {

        $billid  = decrypt($request->id);

        $state = state::all();
        $country = country::all();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title','bill_calculation')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';
        $currtitle       = $state_id[0]['currency_title'];
        $bill_calculation = $state_id[0]['bill_calculation'];

        if($tax_type==1)
        {
                $currency_title  = $currtitle==''||$currtitle==NULL?'INR':$currtitle;
        }
        else
        {
                $currency_title  = 'INR';
        }



       $payment_methods = payment_method::where('is_active','=','1')->get();

        $billingdata = consign_bill::where([
            ['consign_bill_id','=',$billid],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->with('reference')
            ->with('consign_payment_detail')
            ->with('customer')
            ->with('customer_address_detail')
            ->with('company')
            ->with('user')
            ->withCount([
                    'consign_products_detail as overalldiscount' => function($fquery) {
                        $fquery->select(DB::raw('SUM(discount_amount + overalldiscount_amount )'));
                    }
                ])
            ->first();

          

          

             $billingproductdata = consign_products_detail::
             where('company_id',Auth::user()->company_id)
            ->where('consign_bill_id','=',$billid)
            ->where('qty','!=',0)
            ->with('product.product_features_relationship')
            ->with('batchprice_master')
            ->get();

           
            $productcount = consign_products_detail::where('company_id',Auth::user()->company_id)
            ->where('consign_bill_id','=',$billid)
            ->count();

             $gstdata = consign_products_detail::select('cgst_percent','sgst_percent','igst_percent',DB::raw("SUM(consign_products_details.sellingprice_afteroverall_discount) as tottaxablevalue"),DB::raw("SUM(consign_products_details.cgst_amount) as totcgstamount"),DB::raw("SUM(consign_products_details.sgst_amount) as totsgstamount"),DB::raw("SUM(consign_products_details.igst_amount) as totigstamount"),DB::raw("SUM(consign_products_details.total_amount) as totgrand"))->where('consign_bill_id','=',$billid)->groupBy('igst_percent')->get();

        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($billingproductdata AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {
                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);
                        $billingproductdata[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }



        return view('printingfiles::sales/printconsign_challan',compact('payment_methods','state','country','billingdata','billingproductdata','productcount','gstdata','tax_type','taxname','currency_title','bill_calculation'));

    }
    public function thermalconsign_challan(Request $request)
    {

        $billid  = decrypt($request->id);

        $state = state::all();
        $country = country::all();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';
        $currtitle       = $state_id[0]['currency_title'];
        if($tax_type==1)
        {
                $currency_title  = $currtitle==''||$currtitle==NULL?'&#x20b9':$currtitle;
        }
        else
        {
                $currency_title  = '&#x20b9';
        }


       $payment_methods = payment_method::where('is_active','=','1')->get();

        $billingdata = consign_bill::where([
            ['consign_bill_id','=',$billid],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->with('reference')
            ->with('consign_payment_detail')
            ->with('customer')
            ->with('customer_address_detail')
            ->with('company')
            ->with('user')
            ->first();

            

             $billingproductdata = consign_products_detail::where('company_id',Auth::user()->company_id)
            ->where('consign_bill_id','=',$billid)
            ->where('qty','!=',0)
            ->with('product')
            ->get();

            $gstdata = consign_products_detail::select('cgst_percent','sgst_percent','igst_percent',DB::raw("SUM(consign_products_details.sellingprice_afteroverall_discount) as tottaxablevalue"),DB::raw("SUM(consign_products_details.cgst_amount) as totcgstamount"),DB::raw("SUM(consign_products_details.sgst_amount) as totsgstamount"),DB::raw("SUM(consign_products_details.igst_amount) as totigstamount"),DB::raw("SUM(consign_products_details.total_amount) as totgrand"))->where('consign_bill_id','=',$billid)->groupBy('igst_percent')->get();



        return view('printingfiles::sales/thermalconsign_challan',compact('payment_methods','state','country','billingdata','billingproductdata','gstdata','tax_type','taxname','currency_title'));

    }
    public function print_creditnote(Request $request)
    {

        $billid  = decrypt($request->id);

        $state = state::all();
        $country = country::all();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';
        $currtitle       = $state_id[0]['currency_title'];
        if($tax_type==1)
        {
                $currency_title  = $currtitle==''||$currtitle==NULL?'&#x20b9':$currtitle;
        }
        else
        {
                $currency_title  = '&#x20b9';
        }


       $payment_methods = payment_method::where('is_active','=','1')->get();

        $billingdata = return_bill::where([
            ['return_bill_id','=',$billid],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->with('return_bill_payment')
            ->with('customer')
            ->with('customer_address_detail')
            ->with('company')
            ->withCount([
                    'return_product_detail as overalldiscount' => function($fquery) {
                        $fquery->select(DB::raw('SUM(discount_amount + overalldiscount_amount )'));
                    }
                ])
            ->first();

             $billingproductdata = return_product_detail::where('company_id',Auth::user()->company_id)
            ->where('return_bill_id','=',$billid)
            ->where('qty','!=',0)
            ->with('product')
            ->get();


            $productcount = return_product_detail::where('company_id',Auth::user()->company_id)
            ->where('return_bill_id','=',$billid)
            ->count();

            $gstdata = return_product_detail::select('cgst_percent','sgst_percent','igst_percent',DB::raw("SUM(return_product_details.sellingprice_afteroverall_discount) as tottaxablevalue"),DB::raw("SUM(return_product_details.cgst_amount) as totcgstamount"),DB::raw("SUM(return_product_details.sgst_amount) as totsgstamount"),DB::raw("SUM(return_product_details.igst_amount) as totigstamount"),DB::raw("SUM(return_product_details.total_amount) as totgrand"))->where('return_bill_id','=',$billid)->groupBy('igst_percent')->get();



        return view('printingfiles::creditnote/print_creditnote',compact('payment_methods','state','country','billingdata','billingproductdata','productcount','gstdata','tax_type','taxname','currency_title'));

    }
    public function thermalprint_creditnote(Request $request)
    {

        $billid  = decrypt($request->id);

        $state = state::all();
        $country = country::all();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';
        $currtitle       = $state_id[0]['currency_title'];
        if($tax_type==1)
        {
                $currency_title  = $currtitle==''||$currtitle==NULL?'&#x20b9':$currtitle;
        }
        else
        {
                $currency_title  = '&#x20b9';
        }


       $payment_methods = payment_method::where('is_active','=','1')->get();

        $billingdata = return_bill::where([
            ['return_bill_id','=',$billid],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->with('return_bill_payment')
            ->with('customer')
            ->with('customer_address_detail')
            ->with('company')
            ->withCount([
                    'return_product_detail as overalldiscount' => function($fquery) {
                        $fquery->select(DB::raw('SUM(discount_amount + overalldiscount_amount )'));
                    }
                ])
            ->first();

             $billingproductdata = return_product_detail::where('company_id',Auth::user()->company_id)
            ->where('return_bill_id','=',$billid)
            ->where('qty','!=',0)
            ->with('product')
            ->get();


            $productcount = return_product_detail::where('company_id',Auth::user()->company_id)
            ->where('return_bill_id','=',$billid)
            ->count();

            $gstdata = return_product_detail::select('cgst_percent','sgst_percent','igst_percent',DB::raw("SUM(return_product_details.sellingprice_afteroverall_discount) as tottaxablevalue"),DB::raw("SUM(return_product_details.cgst_amount) as totcgstamount"),DB::raw("SUM(return_product_details.sgst_amount) as totsgstamount"),DB::raw("SUM(return_product_details.igst_amount) as totigstamount"),DB::raw("SUM(return_product_details.total_amount) as totgrand"))->where('return_bill_id','=',$billid)->groupBy('igst_percent')->get();



        return view('printingfiles::creditnote/thermalprint_creditnote',compact('payment_methods','state','country','billingdata','billingproductdata','productcount','gstdata','tax_type','taxname','currency_title'));

    }
    public function print_creditreceipt(Request $request)
    {

        $billid  = decrypt($request->id);

        $state = state::all();
        $country = country::all();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';
        $currtitle       = $state_id[0]['currency_title'];
        if($tax_type==1)
        {
                $currency_title  = $currtitle==''||$currtitle==NULL?'&#x20b9':$currtitle;
        }
        else
        {
                $currency_title  = '&#x20b9';
        }


       $payment_methods = payment_method::where('is_active','=','1')->get();

        $billingdata = customer_creditreceipt::where([
            ['customer_creditreceipt_id','=',$billid],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->with('customer')
            ->with('customer_address_detail')
            ->with('customer_crerecp_payment')
            ->with('company')
            ->get();

             $billingproductdata = customer_creditreceipt_detail::where('customer_creditreceipt_id','=',$billid)
            ->get();


        return view('printingfiles::creditreceipt/print_creditreceipt',compact('payment_methods','state','country','billingdata','billingproductdata','tax_type','taxname','currency_title'));

    }
    public function stransferprint_bill(Request $request)
    {
        $billid  = decrypt($request->id);

        $state = state::all();
        $country = country::all();
        $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title','bill_calculation')->where('company_id',Auth::user()->company_id)->get();
        $company_state   = $state_id[0]['state_id'];
        $tax_type        = $state_id[0]['tax_type'];
        $tax_title       = $state_id[0]['tax_title'];
        $taxname         = $tax_type==1?$tax_title:'IGST';
        $currtitle       = $state_id[0]['currency_title'];
        $bill_calculation = $state_id[0]['bill_calculation'];

        if($tax_type==1)
        {
                $currency_title  = $currtitle==''||$currtitle==NULL?'INR':$currtitle;
        }
        else
        {
                $currency_title  = 'INR';
        }




        $billingdata = stock_transfer::where([
            ['stock_transfer_id','=',$billid],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->with('store_name')
            ->with('company')
            ->first();

          

             $billingproductdata = stock_transfer_detail::
             where('company_id',Auth::user()->company_id)
            ->where('stock_transfer_id','=',$billid)
            ->where('product_qty','!=',0)
            ->with('product.product_features_relationship')
            ->get();

           
            $productcount = stock_transfer_detail::where('company_id',Auth::user()->company_id)
            ->where('stock_transfer_id','=',$billid)
            ->count();


        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($billingproductdata AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {
                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);
                        $billingproductdata[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }



        return view('printingfiles::stocktransfer/stransferprint_bill',compact('billingdata','billingproductdata','productcount','bill_calculation','currency_title','tax_type'));

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\sales_bill  $sales_bill
     * @return \Illuminate\Http\Response
     */
    public function show(sales_bill $sales_bill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\sales_bill  $sales_bill
     * @return \Illuminate\Http\Response
     */
    public function edit(sales_bill $sales_bill)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\sales_bill  $sales_bill
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, sales_bill $sales_bill)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\sales_bill  $sales_bill
     * @return \Illuminate\Http\Response
     */
    public function destroy(sales_bill $sales_bill)
    {
        //
    }
}
