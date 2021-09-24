<?php

namespace Retailcore\DamageProducts\Http\Controllers\damageproducts;

// use App\DamageUsedProducts;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\colour;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Products\Models\product\size;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\DamageProducts\Models\damageproducts\damage_product;
use Retailcore\DamageProducts\Models\damageproducts\damage_type;
use Retailcore\DamageProducts\Models\damageproducts\damage_export;
use Retailcore\DamageProducts\Models\damageproducts\damage_product_export;
use App\User;
use Retailcore\DamageProducts\Models\damageproducts\damage_product_detail;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
class DamageUsedProductsController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $damage_types          =   damage_type::where('is_active','=',1)->get();

        $company_profile = company_profile::where('company_id',Auth::user()->company_id)->get();
        $company_state = $company_profile[0]['state_id'];
        $billtype  =        $company_profile[0]['billtype'];
        $tax_type  =        $company_profile[0]['tax_type'];

        $last_damage_id = damage_product::where('company_id',Auth::user()->company_id)->get()->max('damage_product_id');

        $f1     =    (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
        $f2     =    (date('m')>'03') ? date('y',strtotime('+1 year')) : date('y');

        if($last_damage_id == '')
        {
            $last_damage_id = 1;
        }
        else
        {
            $last_damage_id = $last_damage_id  + 1;
        }

        $damage_no  =   'DAM/'.$last_damage_id.'/'.$f1.'-'.$f2;

        return view('damageproducts::damageproducts/damage-used-products',compact('damage_types','damage_no','billtype','company_state','tax_type'));
    }

    //DAMAGE AND USED REPORT
    public function damage_used_report()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        //DAMAGE TYPE FOR IN FILTER GIVE SELECTION OPTION
        $damage_types          =   damage_type::where('is_active','=',1)->get();
        //END OF GETTING DAMAGE TYPE
        $date      =   date('Y-m-d',strtotime(date("d-m-Y")));

        $result    =   damage_product::where('deleted_at','=',NULL)
                                     ->where('company_id',Auth::user()->company_id)
                                    ->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') between '$date' and '$date'")
                                     ->with('damage_types')
                                     ->orderBy('damage_product_id','DESC')
                                     ->paginate(10);

        return view('damageproducts::damageproducts/damage-used-report',compact('result','damage_types'));
    }

    //DAMAGE AND USED REPORT PRODUCT WISE
    public function damage_used_product_wise()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $damage_types = damage_type::where('is_active','=',1)->get();

        $date      =   date('d-m-Y');

        $result    =   damage_product_detail::where('deleted_at','=',NULL)
                       ->where('company_id',Auth::user()->company_id)
                       ->with('product.product_features_relationship')
                       ->with('inward_product_detail')
                       ->with('damage_product')
                       ->whereHas('damage_product', function ($q) use ($date)
                       {
                          $q->whereBetween('damage_date', [$date,$date]);
                        })
                       ->orderBy('damage_product_detail_id','DESC')
                       ->paginate(10);

        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($result AS $key=>$v) {
            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $result[$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }

        return view('damageproducts::damageproducts/damage-used-product-wise',compact('result','damage_types'));
    }


    //THIS FUNCTION USED FOR FILTER IN DAMAGE AND USED PRODUCT WISE REPORT
    public function searchDamageProductReport(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $company_id         =   Auth::User()->company_id;

        if($request->ajax())
        {
            $data  =   $request->all();

            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = (isset($data['query']) ? $data['query'] : '');

            $from_date   =  isset($query['from_date']) ? date("Y-m-d",strtotime($query['from_date'])) : '';
            $to_date   =  isset($query['to_date'])? date("Y-m-d",strtotime($query['to_date'])) : '';
            $damageproductsearch   =  isset($query['damageproductsearch'])?$query['damageproductsearch'] : '';
            $batch_no   =  isset($query['damage_batch_no'])?$query['damage_batch_no'] : '';
            $DamageIds  =   isset($query['DamageIds'])?$query['DamageIds'] : '';
            $product_code  =   isset($query['product_code'])?$query['product_code'] : '';

            $query = damage_product_detail::select('*')
                ->whereRaw('company_id='.$company_id)
                ->where('deleted_at','=',NULL)
                ->with('product.product_features_relationship')
                ->with('damage_types')
                ->with('damage_product');

            if($from_date!='')
            {
                $query->whereHas('damage_product',function ($q) use($from_date,$to_date)
                {
                    //$q->whereBetween('damage_date', [$from_date,$to_date]);
                    $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                });
            }
            else{

                $date      =   date('Y-m-d',strtotime(date("d-m-Y")));

                $query->whereHas('damage_product',function ($q) use($date)
                {
                    $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') between '$date' and '$date'");
                });
            }


            if($DamageIds!='')
            {

                $query->whereHas('damage_product', function($q) use ($DamageIds)
                    {
                        $q->whereRaw("FIND_IN_SET('".$DamageIds."',damage_type_id)");
                    });
            }

            if($damageproductsearch!='')
            {

                 $query->whereRaw("product_id='" . $damageproductsearch . "'");
            }

            if($batch_no!='')
            {

                $query->whereHas('inward_product_detail',function ($q) use($batch_no){
                    $q->whereRaw("batch_no='" . $batch_no . "'");
                });
            }
            if($product_code!='')
            {

                $query->whereHas('product',function ($q) use($product_code){
                    $q->whereRaw("product_code='" . $product_code . "'");
                });
            }

            $result  = $query->orderBy($sort_by,$sort_type)->paginate(10);




            $product_features =  ProductFeatures::getproduct_feature('');


            foreach ($result AS $key=>$v) {
                if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '')
                {
                    foreach($product_features AS $kk => $vv)
                    {
                        $html_id = $vv['html_id'];

                        if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL)
                        {

                            $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                            $result[$key]['product'][$html_id] = $nm;
                        }
                    }
                }
            }


            $company_info  =  company_profile::select('tax_type','tax_title','currency_title')->where('company_id',Auth::user()->company_id)->first();
            $tax_type        = $company_info['tax_type'];
            $tax_title       = $company_info['tax_title'];
            $tax_currency         = $company_info['currency_title'];

            return view('damageproducts::damageproducts/view_damagereport_data',compact('result','tax_type','tax_title','tax_currency'))->render();
        }
    }

    //THIS FUNCTION USED FOR FILTER IN DAMAGE AND USED REPORT
    public function searchDamageProductReportGroup(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $company_id         =   Auth::User()->company_id;

        if($request->ajax())
        {
            $data                   =   $request->all();
            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = (isset($data['query']) ? $data['query'] : '');



            $from_date   =  isset($query['from_date']) ? date("Y-m-d",strtotime($query['from_date'])) : '';
            $to_date   =  isset($query['to_date'])?date("Y-m-d",strtotime($query['to_date'])) : '';

            $DamageIds  =   isset($query['DamageIds'])?$query['DamageIds'] : '';
            $damage_no_search  =  isset($query['damage_no_search'])?$query['damage_no_search'] : '';

            $query = damage_product::select('*')
            ->whereRaw('company_id='.$company_id)
            ->where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL);

            if($from_date!='')
            {
                //$query->whereRaw("Date(damage_products.damage_date) between '$from_date' and '$to_date'");
                $query->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
            }

            $exp1    =   explode(',',$DamageIds);

            if($DamageIds!='')
            {
                foreach($exp1 as $key=>$value)
                {
                    if($key==0)
                    {
                        if($damage_no_search!='')
                        {
                            $query->whereRaw("damage_type_id='".$value."' and damage_no='".$damage_no_search."'");
                        }
                        else
                        {
                            $query->whereRaw("damage_type_id='".$value."'");
                        }
                    }
                    else
                    {
                        if($damage_no_search!='')
                        {
                            $query->orwhereRaw("damage_type_id='".$value."' and damage_no='".$damage_no_search."'");
                        }
                        else
                        {
                            $query->orwhereRaw("damage_type_id='".$value."'");
                        }

                    }
                }
            }
            else
            {
                if($damage_no_search!='')
                {
                    $query->whereRaw("damage_no='".$damage_no_search."'");
                }
            }

            $result  = $query->orderBy($sort_by, $sort_type)->paginate(10);

            return view('damageproducts::damageproducts/view_damage_productreport_data',compact('result'))->render();
        }
    }


    //THIS FUNCTION USED IN DAMAGE AND USED PRODUCT WISE REPORT.IN FILTERATION BLOCK FOR FIND DAMAGE PRODUCT BY NAME AND BARCODE
    public function damage_product_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {
            $json = [];

            $sresult = product::where('company_id', Auth::user()->company_id)
                            ->select('product_name','product_system_barcode','supplier_barcode','product_id')
                            ->where('deleted_at', '=', NULL)
                            ->where('product_system_barcode', 'like', '%'.$request->search_val.'%')
                            ->orWhere('supplier_barcode', 'like', '%'.$request->search_val.'%')
                            ->orWhere('product_name', 'like', '%'.$request->search_val.'%')
                            ->groupBy('product_id')
                            ->with('price_master')
                            ->whereHas('price_master',function ($q) use($request)
                            {
                                $q->select('batch_no');
                                $q->where('product_qty','>',0);
                             })->take(10)->get();

           if(sizeof($sresult) != 0)
            {
                foreach($sresult as $sproductkey=>$sproductvalue){

                        foreach($sproductvalue['price_master'] as $psproductkey=>$psproductvalue){

                          if($sproductvalue['supplier_barcode']!='' || $sproductvalue['supplier_barcode']!=null)
                          {
                             $showbarcode   =   $sproductvalue['supplier_barcode'];
                          }
                          else
                          {
                             $showbarcode   =   $sproductvalue['product_system_barcode'];
                          }

                            if($psproductvalue['batch_no']!='' || $psproductvalue['batch_no']!=null)
                              {

                                $json[$psproductkey]['label'] = $showbarcode.'_'.$sproductvalue['product_name'].'_'.$psproductvalue['batch_no'];
                                $json[$psproductkey]['product_id'] = $sproductvalue['product_id'];
                            }
                            else
                            {
                                $json[$psproductkey]['label'] = $showbarcode.'_'.$sproductvalue['product_name'];
                                    $json[$psproductkey]['product_id'] = $sproductvalue['product_id'];
                            }

                        }
                }
            }
           return json_encode($json);
        }
        else
        {
          $json = [];
          return json_encode($json);
        }
    }

    /*public function batch_damage_product_search(Request $request)
    {

        if($request->search_val !='')
        {

            $json = [];

               $result = price_master::where('company_id', Auth::user()->company_id)
                  ->where('deleted_at', '=', NULL)
                  ->where('batch_no', 'LIKE', "%$request->search_val%")
                  ->where('product_qty','>',0)
                  // ->with('inward_product_details')
                  // ->with('inward_product_details.inward_stock')
                  ->with('product')
                  ->whereHas('product',function ($q) use($request){
                          $q->select('product_name','product_system_barcode','supplier_barcode');
                    })->take(10)->get();


                    if(sizeof($result) != 0)
                    {

                        foreach($result as $productkey=>$productvalue){

                              if($productvalue['supplier_barcode']!='' || $productvalue['supplier_barcode']!=null)
                              {
                                  $json[] = $productvalue['product']['supplier_barcode'].'_'.$productvalue['product']['product_name'].'_'.$productvalue['batch_no'];
                              }
                              else
                              {
                                $json[] = $productvalue['product']['product_system_barcode'].'_'.$productvalue['product']['product_name'].'_'.$productvalue['batch_no'];
                              }



                        }
                    }


                  if(sizeof($result) == 0)
                  {

                      $sresult = product::where('company_id', Auth::user()->company_id)
                        ->select('product_name','product_system_barcode','supplier_barcode','product_id')
                        ->where('deleted_at', '=', NULL)
                        ->where('product_system_barcode', 'like', '%'.$request->search_val.'%')
                        ->orWhere('supplier_barcode', 'like', '%'.$request->search_val.'%')
                        ->orWhere('product_name', 'like', '%'.$request->search_val.'%')
                        ->groupBy('product_id')
                        ->with('inward_product_detail')
                        ->with('inward_product_detail.inward_stock')
                        // ->whereHas('price_master',function ($q) use($request){
                        //         $q->select('batch_no');
                        //         $q->where('batch_no','!=',NULL);
                        //     })
                        ->take(10)->get();

                        if(sizeof($sresult) != 0)
                        {

                           foreach($sresult as $sproductkey=>$sproductvalue)
                           {

                                // return json_encode($sproductvalue['inward_product_detail']);
                                    foreach($sproductvalue['inward_product_detail'] as $psproductkey=>$psproductvalue)
                                    {


                                        if($sproductvalue['supplier_barcode']!='' || $sproductvalue['supplier_barcode']!=null)
                                        {
                                            $showbarcode   =   $sproductvalue['supplier_barcode'];
                                        }
                                        else
                                        {
                                            $showbarcode   =   $sproductvalue['product_system_barcode'];
                                        }

                                        if($psproductvalue['batch_no']!='' || $psproductvalue['batch_no']!=null)
                                        {

                                            $json[] = $showbarcode.'_'.$sproductvalue['product_name'].'_'.$psproductvalue['batch_no'].'_'.$psproductvalue['inward_stock']['invoice_no'];
                                        }
                                        else
                                        {
                                            $json[] = $showbarcode.'_'.$sproductvalue['product_name'].'_'.$psproductvalue['batch_no'].'_'.$psproductvalue['inward_stock']['invoice_no'];
                                        }


                                    }


                            }

                        }
                    }

             return json_encode($json);
        }
        else
        {
          $json = [];
          return json_encode($json);
        }
    }*/

    //THIS FUNCTION IS USED AT TIME OF DAMAGE screen
    public function normal_damage_product_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {
            $result = product::
                select('product_name','product_system_barcode','product_id','supplier_barcode','product_code')
                ->whereIn('item_type',array(1,3))
                ->with('inward_product_detail_for_damage.inward_stock')
                ->where(function ($q) use($request)
                {
                    $q->where('product_name', 'LIKE', "%$request->search_val%")
                        ->orWhere('product_system_barcode', 'LIKE', "%$request->search_val%")
                        ->orWhere('supplier_barcode', 'LIKE', "%$request->search_val%")
                        ->orWhere('product_code', 'LIKE', "%$request->search_val%");
                })
                ->with('product_price_master')
                ->whereHas('product_price_master',function ($q)
                {
                    $q->where('company_id',Auth::user()->company_id);
                })
                ->whereHas('inward_product_detail_for_damage',function($q){
                    $q->where('company_id',Auth::user()->company_id);
                })
                ->take(10)->get();


            $json = [];
            if(sizeof($result) != 0)
            {
                foreach($result as $productkey=>$productvalue)
                {
                    foreach($productvalue['inward_product_detail_for_damage'] as $pproductkey=>$pproductvalue)
                    {
                        if($productvalue['supplier_barcode']=='')
                        {
                            $barcode    =   $productvalue['product_system_barcode'];
                        }
                        else
                        {
                            $barcode    =   $productvalue['supplier_barcode'];
                        }

                        if($pproductvalue['inward_stock']['invoice_no']!='')
                        {
                            if($productvalue['product_code'] == '')
                            {
                                $productvalue['product_code'] = '';
                            }

                            $json[$pproductkey]['label'] = $barcode.'_'.$productvalue['product_name'].'_'.$productvalue['product_code'].'_'.$pproductvalue['inward_stock']['invoice_no'].'_'.$pproductvalue['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'];
                            $json[$pproductkey]['supplier_gst_id'] = $pproductvalue['inward_stock']['supplier_gst_id'];
                            $json[$pproductkey]['invoice_no'] = $pproductvalue['inward_stock']['invoice_no'];
                            $json[$pproductkey]['barcode'] = $barcode;
                            $json[$pproductkey]['product_name'] = $productvalue['product_name'];
                        }
                        else
                        {
                           // $json[$productkey]['label'] = $barcode.'_'.$productvalue['product_name'];
                          //  $json[$productkey]['supplier_gst_id'] = $pproductvalue['inward_stock']['supplier_gst_id'];
                        }
                    }
                }
            }
            return json_encode($json);
        }
        else
        {
          $json = [];
          return json_encode($json);
        }

    }

    //THIS FUNCTION IS USED IN DAMAGE AND USED REPORT FILTARATION BLOCK FOR DAMAGE NO SEARCH
    public function damage_no_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->search_val !='')
        {
            $json = [];

            $result = damage_product::select('damage_no')
                    ->where('company_id',Auth::user()->company_id)
                    ->Where('deleted_at', '=', NULL)->get();

            if(!empty($result))
            {
                foreach($result as $damagekey=>$damagevalue){
                    $json[] = $damagevalue['damage_no'];
                }
            }

            return json_encode($json);
        }
    }

    //THIS FUNCTION IS USED FOR SAVE DAMAGE PRODUCTS ENTRY
    public function SaveDamageProducts(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data  = $request->all();

        $damage_detail = $data['damage_detail'];

        $damage_product_detail = $data['damage_product_detail'];

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;

        $damage_detail['created_by'] = $userId;
        $damage_detail['company_id'] = $company_id;

       $damage_product = damage_product::updateOrCreate(
            ['damage_product_id' => $damage_detail['damage_product_id'],
             'company_id'=>$company_id,],
            $damage_detail
        );
        $damage_product_id = $damage_product->damage_product_id;

        foreach($damage_product_detail AS $damageproductkey=>$damageproductvalue)
        {
            //UPDATE PENDING RETURN QTY IN INWARD PRODUCT DETAIL TABLE

            $inward_product_detail = inward_product_detail::select('inward_stock_id','product_id','batch_no','offer_price','pending_return_qty')
                ->where('inward_product_detail_id',$damageproductvalue['inward_product_detail_id'])
                ->first();

            if($damageproductvalue['damage_product_detail_id'] == '')
            {
                $pending_return_qty = $inward_product_detail['pending_return_qty'] - $damageproductvalue['product_damage_qty'];

                inward_product_detail::where('inward_product_detail_id',$damageproductvalue['inward_product_detail_id'])->update(array(
                    'modified_by' => $userId,
                    'pending_return_qty' => $pending_return_qty
                ));

                price_master::where('product_id',$damageproductvalue['product_id'])
                    ->where('batch_no',$inward_product_detail['batch_no'])
                    ->where('company_id',$company_id)
                    ->where('offer_price',$inward_product_detail['offer_price'])->update(array(
                        'modified_by' => $userId,
                        'product_qty' => DB::raw('product_qty - '.$damageproductvalue['product_damage_qty'])
                    ));
            }
            else {
                $damage_qty_detail = damage_product_detail::select('product_damage_qty','image')
                    ->where('damage_product_detail_id', $damageproductvalue['damage_product_detail_id'])
                    ->first();

                $pending_return_qty = ($inward_product_detail['pending_return_qty'] + $damage_qty_detail['product_damage_qty'] - ($damageproductvalue['product_damage_qty']));

                inward_product_detail::where('inward_product_detail_id',$damageproductvalue['inward_product_detail_id'])->update(array(
                    'modified_by' => $userId,
                    'pending_return_qty' => $pending_return_qty
                ));

                $qtys = price_master::select('product_qty','price_master_id')
                    ->where('batch_no',$inward_product_detail['batch_no'])
                    ->where('product_id', '=', $damageproductvalue['product_id'])
                    ->where('offer_price',$inward_product_detail['offer_price'])
                    ->where('company_id', Auth::user()->company_id)->first();

                $price_master_qty_update = (($qtys['product_qty'] + $damage_qty_detail['product_damage_qty']) - $damageproductvalue['product_damage_qty']);

                price_master::where('product_id',$damageproductvalue['product_id'])
                    ->where('batch_no',$inward_product_detail['batch_no'])
                    ->where('company_id',$company_id)
                    ->where('offer_price',$inward_product_detail['offer_price'])->update(array(
                        'modified_by' => $userId,
                        'product_qty' => $price_master_qty_update
                    ));
            }

            //for save damge used product
            if($damageproductvalue['image_name'] != '' && $damageproductvalue['image_name'] != 'NULL' && $damageproductvalue['image_name'] != 'null')
            {
                if ($damageproductvalue['image'] != '' && $damageproductvalue['image'] != 'NULL' && $damageproductvalue['image'] != 'null')
                {
                    if (!file_exists(DAMAGE_USED_PRODUCT_IMAGE))
                    {
                        mkdir(DAMAGE_USED_PRODUCT_IMAGE, 0777, true);
                    }

                    //end of delete previous image
                    $image_parts = explode(";base64,", $damageproductvalue['image']);
                    if(isset($image_parts) && $image_parts != '')
                    {
                        //delete previous image if any
                        if(isset($damage_qty_detail) && isset($damage_qty_detail['image']) && $damage_qty_detail['image'] != '')
                        {
                            if(file_exists(DAMAGE_USED_PRODUCT_IMAGE.$damage_qty_detail['image']))
                            {
                                unlink(DAMAGE_USED_PRODUCT_IMAGE.$damage_qty_detail['image']);
                            }
                        }
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];
                        $image_base64 = base64_decode($image_parts[1]);
                        $file_name = uniqid() . '.png';
                        $file = DAMAGE_USED_PRODUCT_IMAGE . $file_name;
                        file_put_contents($file, $image_base64);
                        chmod($file, 0664);
                        $damageproductvalue['image'] = $file_name;
                    }
                }
                else
                {
                    $damageproductvalue['image'] = $damageproductvalue['image_name'];
                }
            }
            else
            {
                //delete previous image if any
                $damageproductvalue['image'] = 'NULL';
                if(isset($damage_qty_detail) && isset($damage_qty_detail['image']) && $damage_qty_detail['image'] != '' && $damage_qty_detail['image'] != 'NULL')
                {
                    if(file_exists(DAMAGE_USED_PRODUCT_IMAGE.$damage_qty_detail['image']))
                    {
                        unlink(DAMAGE_USED_PRODUCT_IMAGE.$damage_qty_detail['image']);
                    }

                }
                //end of delete previous image
            }

            unset($damageproductvalue['image_name']);

            $damageproducts  =  damage_product_detail::updateOrCreate(
            ['damage_product_id' => $damage_product_id,
            'company_id'=>$company_id,
            'damage_product_detail_id'=>$damageproductvalue['damage_product_detail_id'],],
            $damageproductvalue);
        }

        if($damage_detail['damage_product_id'] != '')
        {
            return json_encode(array("Success"=>"True","Message"=>"Damage and Used updated successfully!","url"=>"damage-used-report"));
        }
        else
        {
            return json_encode(array("Success"=>"True","Message"=>"Damage and Used added successfully!","url"=>''));
        }
    }


    /*public function damage_product_detail_batchno(Request $request)
    {

        $barcode   =  $request->barcode;
        $presult   =  array();
        $ppresult  =  array();

        if(strpos($barcode, '_') !== false)
        {
            $prodbarcode        =   explode('_',$barcode);
            $prod_barcode       =   $prodbarcode[0];
            $prod_name          =   $prodbarcode[1];
            $batch_no           =   $prodbarcode[2];
            $invoice_no         =   $prodbarcode[3];
        }
        else
        {
            $prod_barcode       =   $barcode;
            $prod_name          =   $barcode;
            $batch_no           =   $barcode;
            $invoice_no         =   $barcode;
        }

        $presult = product::select('product_id')->where('product_system_barcode',$prod_barcode)
        ->where('product_name',$prod_name)
        ->orWhere('supplier_barcode',$prod_barcode)
        ->where('company_id',Auth::user()->company_id)
        ->get();


        if($invoice_no!='')
        {
            $invresult  =   inward_stock::select('inward_stock_id')->where('invoice_no','=',$invoice_no)
            ->where('company_id',Auth::user()->company_id)->get();
        }

        if(sizeof($presult) != 0)
        {
            $query = inward_product_detail::where('product_id',$presult[0]['product_id'])
            ->where('company_id',Auth::user()->company_id)
            ->where('inward_stock_id','=',$invresult[0]['inward_stock_id']);

            if($batch_no!='')
            {
                $query->where('batch_no',$batch_no);
            }

            $query->with('inward_stock','product')
            ->with('product.colour','product.size')
            ->orderBy('inward_stock_id','ASC');

            $result     =   $query->get();
        }

      return json_encode(array("Success"=>"True","Data"=>$result));
    }*/

    //THIS FUNCTION IS USED IN DAMAGE SCREEN AFTER SELECT PRODUCT FOR DAMAGE
    public function damage_product_detail_normal(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $barcode   =  $request->barcode;
        $prod_name   =  $request->product_name;
        $invoice_no   =  $request->invoice_no;
        $supplier_gst_id   =  $request->supplier_gst_id;

        $presult = product::select('product_id')
                    ->where(function($q) use($barcode,$prod_name){
                        $q->where('product_system_barcode',$barcode)
                            ->where('product_name',$prod_name)
                            ->orWhere('supplier_barcode',$barcode);
                    })
                    ->with('product_price_master')
                    ->whereHas('product_price_master',function ($q) {
                        $q->where('company_id',Auth::user()->company_id);
                       })
                    ->get();

        if($invoice_no!='')
        {
            $invresult  = inward_stock::select('inward_stock_id')
                         ->where('invoice_no','=',$invoice_no)
                         ->where('supplier_gst_id','=',$supplier_gst_id)
                         ->where('company_id',Auth::user()->company_id)
                ->whereNull('deleted_at')
                         ->get();
        }
        $result = '';
        if(sizeof($presult) != 0)
        {
            $result = inward_product_detail::where('product_id',$presult[0]['product_id'])
                            ->where('company_id',Auth::user()->company_id)
                            ->where('inward_stock_id','=',$invresult[0]['inward_stock_id'])
                            ->with('inward_stock.supplier_gstdetail.supplier_company_info','product')
                            ->with('product.product_features_relationship')
                            ->orderBy('inward_stock_id','ASC')
                             ->get();

            $product_features =  ProductFeatures::getproduct_feature('');

            if(isset($result[0]['product']['product_features_relationship']) && $result[0]['product']['product_features_relationship'] != '')
            {
                foreach ($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if($result[0]['product']['product_features_relationship'][$html_id] != '' && $result[0]['product']['product_features_relationship'][$html_id] != NULL)
                    {
                        $nm =  product::feature_value($vv['product_features_id'],$result[0]['product']['product_features_relationship'][$html_id]);
                        $result[0]['product'][$html_id] =$nm;
                    }
                }
            }
        }



        return json_encode(array("Success"=>"True","Data"=>$result));
    }

   /* public function damage_search_pricedetail(Request $request)
    {
        $result = price_master::select('sell_price','inward_stock_id','offer_price','product_id','price_master_id','product_qty')->where('price_master_id',$request->price_id)
        ->where('company_id',Auth::user()->company_id)->with([
                    'inward_product_details' => function($fquery) {
                        $fquery->select('cost_price','inward_stock_id');
                    }
                ])
        ->get();
      return json_encode(array("Success"=>"True","Data"=>$result));
    }*/

   //THIS FUNCTION USED IN DAMAGE AND USED REPORT FOR EXPORT
    public function exportdamage_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        return Excel::download(new damage_export($request->from_date,$request->to_date,$request->damage_no_search,$request->DamageType), 'Damage-Export.xlsx');
    }


    //THIS FUNCTION USED IN DAMAGE AND USED PRODUCT WISE REPORT FOR EXPORT
    public function exportdamageproduct_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        return Excel::download(new damage_product_export($request->from_date,$request->to_date,$request->damageproductsearch,$request->DamageType,$request->product_code), 'Damage-Product-Export.xlsx');
    }

    //THIS FUNCTION GET DATA OF DAMAGE PRODUCT AT EDIT TIME
    public function editDamage(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $damage_product_id  =   decrypt($request->damage_product_id);
        $company_id         =   Auth::User()->company_id;

        $result = damage_product::where('company_id',$company_id)
            ->where('deleted_at','=',NULL)
            ->where('damage_product_id','=',$damage_product_id)
            ->with(['damageproduct_detail' => function($q){
                $q->where('product_damage_qty','>',0);
                $q->with('product.product_features_relationship');
                $q->with('inward_product_detail.inward_stock.supplier_gstdetail.supplier_company_info');
            }])
            //->with('damageproduct_detail.product.product_features_relationship')
            //->with('damageproduct_detail.inward_product_detail.inward_stock.supplier_gstdetail.supplier_company_info')
            ->get();



        $product_features =  ProductFeatures::getproduct_feature('');
        foreach ($result[0]['damageproduct_detail'] AS $key=>$value)
        {
            foreach ($product_features AS $kk => $vv)
            {
                $html_id = $vv['html_id'];
                if ($value['product']['product_features_relationship'][$html_id] != '' && $value['product']['product_features_relationship'][$html_id] != NULL) {
                    $nm = product::feature_value($vv['product_features_id'], $value['product']['product_features_relationship'][$html_id]);
                    $value['product'][$html_id] = $nm;
                }
            }
        }

        return json_encode(array("Success"=>"True","Data"=>$result,"url"=>"damage-used-products"));
    }

    //THIS FUNCTION USED FOR DELETE DAMAGE AND USED PRODUCT
    public function delDamage(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $damage_product_id  =   decrypt($request->damage_product_id);
        $company_id         =   Auth::User()->company_id;

        try {
            DB::beginTransaction();

        $result = damage_product_detail::select('*')
        ->where('company_id',$company_id)
        ->where('deleted_at','=',NULL)
        ->where('damage_product_id','=',$damage_product_id)
        ->get();



        damage_product::where('damage_product_id',$damage_product_id)->update(array(
        'deleted_by' => Auth::User()->user_id,
        'deleted_at' => date('Y-m-d H:i:s')));

        $array  =   [];

        foreach($result as $key=>$value)
        {
            inward_product_detail::where('inward_product_detail_id',$value['inward_product_detail_id'])->update(array(
                'pending_return_qty' => DB::raw('pending_return_qty + '.$value['product_damage_qty'])
            ));


            if($value['batch_no']!='')
            {
                price_master::where('offer_price',$value['product_mrp'])
                ->where('product_id',$value['product_id'])
                        ->where('company_id',$company_id)
                ->where('batch_no',$value['batch_no'])->update(array(
                'product_qty' => DB::raw('product_qty + '.$value['product_damage_qty'])
                ));
            }
            else
            {
                price_master::where('offer_price',$value['product_mrp'])
                        ->where('company_id',$company_id)
                ->where('product_id',$value['product_id'])->update(array(
                'product_qty' => DB::raw('product_qty + '.$value['product_damage_qty'])
                ));
            }

        }

        damage_product_detail::where('damage_product_id',$damage_product_id)->update(array(
        'deleted_by' => Auth::User()->user_id,
        'deleted_at' => date('Y-m-d H:i:s')));

            DB::commit();
        }catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }
        return json_encode(array("Success"=>"True","Message"=>"Damage deleted successfully"));

    }



}
