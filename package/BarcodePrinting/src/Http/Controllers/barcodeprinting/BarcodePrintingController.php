<?php

namespace Retailcore\BarcodePrinting\Http\Controllers\barcodeprinting;

// use App\BarcodePrinting;
use App\Http\Controllers\Controller;
use App\secret_code;
use Illuminate\Http\Request;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\PO\Models\purchase_order\purchase_order;
use Retailcore\PO\Models\purchase_order\purchase_order_detail;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\colour;
use Retailcore\Products\Models\product\category;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Products\Models\product\subcategory;
use Retailcore\Products\Models\product\brand;
use Retailcore\Products\Models\product\size;
use Retailcore\BarcodePrinting\Models\barcodeprinting\barcode_sheet;
use App\User;
use Retailcore\BarcodePrinting\Models\barcodeprinting\barcode_template;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Auth;
use Milon\Barcode\DNS1D;
use DB;
use Log;
class BarcodePrintingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $barcode_sheet          =   barcode_sheet::where('is_active','=',1)->get();
        $barcode_template       =   barcode_template::where('deleted_at','=',NULL)->with('barcode_sheet')->get();
        $barcode_template_id    =   Auth::User()->barcode_template_id;

        $template_name_          =   Auth::User()['barcode_template']['template_name'];

        //
		$result = array();
		return view('barcodeprinting::barcodeprinting/barcode-printing',compact('result','barcode_sheet','barcode_template','barcode_template_id','template_name_'));
    }

    public function deleteTemplate(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $barcode_template_id        =   $request->barcode_template_id;
        $userId                     =   Auth::User()->user_id;
        $created_by                 =   $userId;

        $result =  barcode_template::where('barcode_template_id', $barcode_template_id)
        ->update([
        'deleted_by' => $userId,
        'deleted_at' => date('Y-m-d H:i:s')
        ]);

        return json_encode(array("Success"=>"True","Data"=>$result,"url"=>"barcode-printing"));
    }

    public function editTemplate(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $barcode_template_id        =      $request->barcode_template_id;
        $userId                     =   Auth::User()->user_id;
        $created_by                 =   $userId;

        $result = barcode_template::select('*')
            ->where('barcode_template_id', '=', $barcode_template_id)
            ->where('deleted_at','=',NULL)
            ->where('created_by','=',$created_by)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

	public function bar_product_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = product::select('product_name','product_system_barcode','product_id')
            ->where('product_name', 'LIKE', "%$request->search_val%")
            ->with('product_price_master')
            ->whereHas('product_price_master',function($q){
                $q->where('company_id',Auth::user()->company_id);
            })
            ->take(10)->get();


        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

	public function barcode_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = product::select('product_name','product_system_barcode','product_id')
            ->where('product_system_barcode', 'LIKE', "%$request->search_val%")
            ->with('product_price_master')
            ->whereHas('product_price_master',function ($q){
                $q->where('company_id',Auth::user()->company_id);
            })
            ->take(10)->get();



        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function supplier_barcode_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = product::select('supplier_barcode')
            ->where('company_id',Auth::user()->company_id)
            ->Where('supplier_barcode', 'LIKE', "%$request->search_val%")->take(10)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function category_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = category::select('category_name')
            ->where('company_id',Auth::user()->company_id)
            ->Where('category_name', 'LIKE', "%$request->search_val%")->take(10)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function brand_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = brand::select('brand_type')
            ->where('company_id',Auth::user()->company_id)
            ->Where('brand_type', 'LIKE', "%$request->search_val%")->take(10)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function size_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = size::select('size_name')
            ->where('company_id',Auth::user()->company_id)
            ->Where('size_name', 'LIKE', "%$request->search_val%")->take(10)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function colour_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = colour::select('colour_name')
            ->where('company_id',Auth::user()->company_id)
            ->Where('colour_name', 'LIKE', "%$request->search_val%")->take(10)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function sku_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = product::select('sku_code')
            ->where('company_id',Auth::user()->company_id)
            ->Where('sku_code', 'LIKE', "%$request->search_val%")->take(10)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

	public function product_code(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = product::select('product_code')
            ->where('company_id',Auth::user()->company_id)
            ->Where('product_code', 'LIKE', "%$request->search_val%")->take(10)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

	public function invoice_no(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = inward_stock::select('invoice_no')
            ->where('company_id',Auth::user()->company_id)
            ->Where('invoice_no', 'LIKE', "%$request->search_val%")->take(10)->get();

        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    public function BarcodePrintingSticker()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        return view('barcodeprinting::barcodeprinting/barcode-sticker');
    }

    public function fetchBarcodeLabels(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data            =      $request->MasterType;

        $BarcodeData = barcode_sheet::select('barcode_sheet_id','label_name','label_tagline')
        ->where('barcode_id', '=', $data)
        ->where('is_active', '=', '1')
        ->get();

        return json_encode(array("Success"=>"True","Data"=>$BarcodeData) );

    }

    public function template_save(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

        $userId = Auth::User()->user_id;
        $created_by = $userId;

        // $template = barcode_template::updateOrCreate(

        $template = barcode_template::updateOrCreate(
            ['barcode_template_id' => '', 'barcode_sheet_id' => $data[0]['PrintBarcodeSheets'],'company_id'=>Auth::user()->company_id,],
            ['template_name'=>$data[0]['template_name'],
            'barcode_type'=>$data[0]['PrintBarcodeType'],
            'template_data'=>$data[0]['template_data'],
            'template_label_width'=>$data[0]['label_width'],
            'template_label_height'=>$data[0]['label_height'],
            'template_label_font_size'=>$data[0]['label_font_size'],
            'template_label_margin_top'=>$data[0]['label_margin_top'],
            'template_label_margin_right'=>$data[0]['label_margin_right'],
            'template_label_margin_bottom'=>$data[0]['label_margin_bottom'],
            'template_label_margin_left'=>$data[0]['label_margin_left'],
            'template_label_size_type'=>$data[0]['label_size_type'],
            'is_active'=>'1',
            'created_by' =>$created_by
            ]
        );

        return json_encode(array("Success"=>"True","Message"=>"Template successfully generated.","url"=>"barcode-printing"));

    }

    public function edit_template_save(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

        $userId = Auth::User()->user_id;
        $created_by = $userId;

        $template = barcode_template::updateOrCreate(
            ['barcode_template_id' => $data[0]['barcode_template_id'],],
            ['company_id' => Auth::user()->company_id,
            'barcode_sheet_id' => $data[0]['PrintBarcodeSheets'],
            'template_name'=>$data[0]['template_name'],
            'barcode_type'=>$data[0]['PrintBarcodeType'],
            'template_data'=>$data[0]['template_data'],
            'template_label_width'=>$data[0]['label_width'],
            'template_label_height'=>$data[0]['label_height'],
            'template_label_font_size'=>$data[0]['label_font_size'],
            'template_label_margin_top'=>$data[0]['label_margin_top'],
            'template_label_margin_right'=>$data[0]['label_margin_right'],
            'template_label_margin_bottom'=>$data[0]['label_margin_bottom'],
            'template_label_margin_left'=>$data[0]['label_margin_left'],
            'template_label_size_type'=>$data[0]['label_size_type'],
            'is_active'=>'1',
            'created_by' =>$created_by
            ]
        );

        return json_encode(array("Success"=>"True","Message"=>"Template updated successfully.","url"=>"barcode-printing"));

    }

    public function saveBarcodeTemplateToUser(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data               =   $request->barcode_template_id;
        $userId             =   Auth::User()->user_id;
        $created_by         =   $userId;

        user::where('user_id',$created_by)->update(array(
            'barcode_template_id' => $data
        ));

        return json_encode(array("Success"=>"True","Data"=>$data,"url"=>"barcode-printing"));

    }

    public function allBarcodeTemplates(Request $request)
    {

    }

	public function searchBarcodePrintProduct(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {

            $data   =  $request->all();
            $from_date  = date("Y-m-d",strtotime($data['from_date']));
            $to_date =   date("Y-m-d",strtotime($data['to_date']));
            $productName  =   isset($data['productName']) ? $data['productName'] : '';
            $fBarcode =   isset($data['fBarcode']) ? $data['fBarcode'] : '';
            $tBarcode =   isset($data['tBarcode']) ? $data['tBarcode'] : '';
            $productCode  =  isset($data['productCode']) ? $data['productCode'] : '';
			$invoiceNo =  isset($data['invoiceNo']) ? $data['invoiceNo'] : '';
			$supplier_barcode = isset($data['supplier_barcode']) ? $data['supplier_barcode'] : '';
            $skucode   =      isset($data['skucode']) ? $data['skucode'] : '';


            $p_feature = 'product_features_relationship';
            if($data['qty_search'] == 1)
            {
                $p_feature = 'product.product_features_relationship';
            }

            //po code added by hemaxi..27 August 2019
             $po_no   =   isset($data['po_no']) ? $data['po_no'] : '';
            $search_fileter_with = 1;
            //$search_fileter_with === 1= with inward stock , 2= with po
            if($po_no != '')
            {
                $search_fileter_with = 2;
            }
            else
            {
                $search_fileter_with = 1;
            }

            if($productName!='')
            {
                $product_id = product::select('product_id')
                    ->where('deleted_at','=',NULL)
                ->where('product_name', 'LIKE', "%$productName%")
                ->with('product_price_master')
                ->whereHas('product_price_master',function($q){
                    $q->where('company_id',Auth::user()->company_id);
                })
                ->get();
            }

             if($fBarcode!='' and $tBarcode!='')
             {
                $product_id_barcode = product::select(DB::raw('group_concat(product_id) AS products_id'))
                    ->where('deleted_at','=',NULL)
                // ->whereRaw('product_system_barcode','>=',$fBarcode)
                  ->whereBetween('product_system_barcode',array($fBarcode,$tBarcode))
                 //->whereRaw('product_system_barcode','<=',$tBarcode)
                    ->with('product_price_master')
                    ->whereHas('product_price_master',function($q){
                        $q->where('company_id',Auth::user()->company_id);
                    })
                 ->get();

                 $product_id_barcode =  explode(',',$product_id_barcode[0]['products_id']);
             }

             if($supplier_barcode!='')
             {
                $product_id_supplier_barcode = product::select(DB::raw('group_concat(product_id) AS products_id'))
                 ->where('deleted_at','=',NULL)
                 ->where('supplier_barcode','=',$supplier_barcode)
                    ->with('product_price_master')
                    ->whereHas('product_price_master',function($q){
                        $q->where('company_id',Auth::user()->company_id);
                    })
                 ->get();
                 $product_id_supplier_barcode =  explode(',',$product_id_supplier_barcode[0]['products_id']);
             }

             if($productCode!='')
             {
                $product_code_id = product::select('product_id')
                ->where('deleted_at','=',NULL)
                ->where('product_code',$productCode)
                    ->with('product_price_master')
                    ->whereHas('product_price_master',function($q){
                        $q->where('company_id',Auth::user()->company_id);
                    })
                ->get();
             }

             if($skucode!='')
             {
                $product_sku_code_id = product::select('product_id')
                ->where('deleted_at','=',NULL)
                ->where('sku_code',$skucode)
                    ->with('product_price_master')
                    ->whereHas('product_price_master',function($q){
                        $q->where('company_id',Auth::user()->company_id);
                    })
                ->get();
             }

             if($data['qty_search'] == 1)
             {
                 if ($invoiceNo != '')
                 {
                     $inward_id = inward_stock::select('inward_stock_id')
                         ->where('deleted_at', '=', NULL)
                         ->where('invoice_no',$invoiceNo)
                         // ->with('product_price_master')
                         // ->whereHas('product_price_master',function($q){
                         //     $q->where('company_id',Auth::user()->company_id);
                         // })
                         ->get();
                 }
             }


           /*if(isset($data['dynamic_filter']) && $data['dynamic_filter'] !='' &&  !empty($data['dynamic_filter']))
            {
                $search_fil = $data['dynamic_filter'];
                product::with($p_feature)
                    ->whereHas($p_feature,function ($q) use($search_fil)
                    {
                        foreach($search_fil AS $k=>$v)
                        {
                            if($v != '') {
                                $q->where(DB::raw($k), $v);
                            }
                        }
                    });
            }*/

            if($data['qty_search'] == 1)
            {
                if ($po_no != '') {
                    $purchase_order_id = purchase_order::select('purchase_order_id')
                        ->where('company_id', Auth::user()->company_id)
                        ->where('deleted_at', '=', NULL)
                        ->where('po_no', $po_no)
                        ->get();
                }

                if ($po_no != '')
                {
                    $query = purchase_order_detail::select('*')
                        ->whereRaw("company_id='" . Auth::user()->company_id . "'")
                        ->where('deleted_at', '=', NULL)
                        ->with('product.product_features_relationship')
                        ->with('purchase_order');
                } else {

                    $query = inward_product_detail::select('*')
                        ->whereRaw("company_id='" . Auth::user()->company_id . "'")
                        ->where('deleted_at', '=', NULL)
                        ->with('inward_stock')
                        ->with('product.product_features_relationship');
                }
            }
            else
            {
                $query = product::select('*')
                    ->where('deleted_at', '=', NULL)
                    ->with('product_features_relationship')
                    ->whereHas('product_price_master',function($q){
                        $q->where('company_id',Auth::user()->company_id);
                    });
            }


            if($data['from_date']!='' and $data['to_date']!='' && $data['po_no'] != '')
            {
                $query->whereHas('purchase_order',function ($q) use ($from_date,$to_date)
                {
                    $q->whereRaw("STR_TO_DATE(po_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                });
            }

            if($data['from_date']!='' and $data['to_date']!='' && $po_no == '')
            {
                //$query->whereRaw("Date(inward_product_details.created_at) between '$from_date' and '$to_date'");

                $query->whereHas('inward_stock',function ($q) use ($from_date,$to_date)
                {
                    $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                });
            }

            if($productName!='')
            {
                $query->whereIn('product_id',$product_id);
            }

            if($fBarcode!='' and $tBarcode!='')
            {
                $query->whereIn('product_id',$product_id_barcode);
            }


            if($supplier_barcode!='')
            {
                $query->whereIn('product_id',$product_id_supplier_barcode);
            }

            if($productCode!='')
            {
                $query->whereIn('product_id',$product_code_id);
            }

            if($skucode!='')
            {
                $query->whereIn('product_id',$product_sku_code_id);
            }
            if(isset($data['dynamic_filter']) && $data['dynamic_filter'] !='' &&  !empty($data['dynamic_filter']))
            {
                $search_fil = $data['dynamic_filter'];

                $query->with($p_feature)
                    ->whereHas($p_feature,function ($q) use($search_fil)
                    {
                        foreach($search_fil AS $k=>$v)
                        {
                            if($v != '') {
                                $q->where(DB::raw($k), $v);
                            }
                        }
                    });
            }



            if($data['qty_search'] == 1)
            {
                if ($invoiceNo != '')
                {
                    $query->whereIn('inward_stock_id', $inward_id);
                }

                if ($po_no != '')
                {
                    $query->whereIn('purchase_order_id', $purchase_order_id);
                }


                if ($po_no != '') {
                    $query1 = purchase_order_detail::select("purchase_order_details.*",
                        DB::raw("count(purchase_order_detail_id) as totalCount"))
                        ->whereRaw("company_id='" . Auth::user()->company_id . "'")
                        ->where('deleted_at', '=', NULL)
                        ->with('purchase_order')
                        ->with('product.product_features_relationship');
                } else {
                    $query1 = inward_product_detail::select("inward_product_details.*",
                        DB::raw("count(inward_product_detail_id) as totalCount"))
                        ->whereRaw("company_id='" . Auth::user()->company_id . "'")
                        ->where('deleted_at', '=', NULL)
                        ->with('inward_stock')
                        ->with('product.product_features_relationship');
                }
            }
            else{
                $query1 = product::select('*', DB::raw("count(product_id) as totalCount"))
                    ->where('deleted_at', '=', NULL)
                    ->with('product_features_relationship')
                    ->whereHas('product_price_master',function($q){
                        $q->where('company_id',Auth::user()->company_id);
                    });
            }


            if($data['from_date']!='' and $data['to_date']!='' && $po_no != '')
            {
                $query->whereHas('purchase_order',function ($q) use ($from_date,$to_date)
                {
                    $q->whereRaw("STR_TO_DATE(po_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                });
            }


            if($data['from_date']!='' and $data['to_date']!='' && $po_no == '')
            {
                //$query1->whereRaw("Date(inward_product_details.created_at) between '$from_date' and '$to_date'");
                $query1->whereHas('inward_stock',function ($q) use ($from_date,$to_date)
                {
                    $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                });
            }

            if($productName!='')
            {
                $query1->whereIn('product_id',$product_id);
            }

            if($fBarcode!='' and $tBarcode!='')
            {
                $query1->whereIn('product_id',$product_id_barcode);
            }

            if($supplier_barcode!='')
            {
                $query1->whereIn('product_id',$product_id_supplier_barcode);
            }

            if($productCode!='')
            {
                $query1->whereIn('product_id',$product_code_id);
            }

            if($skucode!='')
            {
                $query1->whereIn('product_id',$product_sku_code_id);
            }

            if($invoiceNo!='')
            {
                $query1->whereIn('inward_stock_id',$inward_id);
            }

            if(isset($data['dynamic_filter']) && $data['dynamic_filter'] !='' &&  !empty($data['dynamic_filter']))
            {
                $search_fil = $data['dynamic_filter'];
                $query1->with($p_feature)
                    ->whereHas($p_feature,function ($q) use($search_fil)
                    {
                        foreach($search_fil AS $k=>$v)
                        {
                            if($v != '') {
                                $q->where(DB::raw($k), $v);
                            }
                        }
                    });
            }

            if($data['qty_search'] == 1){
            if($po_no!='')
            {
                $query1->whereIn('purchase_order_id',$purchase_order_id);
            }

            if($invoiceNo!='')
            {
                $query1->whereIn('inward_stock_id',$inward_id);
            }
            }

            $result         =   $query->orderBy('product_id')->get();

            $result1        =   $query1->orderBy('product_id')->get();

            $product_features =  ProductFeatures::getproduct_feature('');



            foreach ($result AS $key=>$v)
            {
                if($data['qty_search'] == 0)
                {
                    $src = $v;
                }
                else{
                    $src =   $v['product'];
                }
                if (isset($src['product_features_relationship']) && $src['product_features_relationship'] != '')
                {
                    foreach($product_features AS $kk => $vv)
                    {
                        $html_id = $vv['html_id'];

                        if ($src['product_features_relationship'][$html_id] != '' && $src['product_features_relationship'][$html_id] != NULL)
                        {
                            $nm = product::feature_value($vv['product_features_id'], $src['product_features_relationship'][$html_id]);

                            $src[$html_id] = $nm;
                        }
                    }
                }
            }
            $qty_search = $data['qty_search'];


            return view('barcodeprinting::barcodeprinting/view_printing_data',compact('result','result1','search_fileter_with','qty_search'))->render();
        }
    }


    public function barcode_product_detail(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $product_id   =  $request->product_id;
        $inward_id    =   $request->inward_id;
        $filter_type    =   $request->filter_type;
        $qty_search    =   $request->qty_search;
        //filter_type == 1=inward stock,2=po

        if($qty_search == 1) {
            $product_arr = '$v["product"]';
            if ($filter_type == 1) {
                $result = inward_product_detail::select('product_id', 'offer_price', 'product_mrp', 'expiry_date', 'product_qty', 'cost_price', 'batch_no',
                    DB::raw('DATE_FORMAT(DATE(`created_at`), "%d-%m-%Y") as created_date'))
                    ->Where('inward_product_detail_id', '=', $inward_id)
                    ->where('company_id', Auth::user()->company_id)
                    ->with('product.product_features_relationship')
                    ->get();
            }

            if ($filter_type == 2) {
                $result = purchase_order_detail::select('product_id', 'expiry_date', 'unique_barcode', 'mfg_date', 'qty', 'total_cost_with_gst',
                    DB::raw('DATE_FORMAT(DATE(`created_at`), "%d-%m-%Y") as created_date'))
                    ->Where('purchase_order_detail_id', '=', $inward_id)
                    ->where('company_id', Auth::user()->company_id)
                    ->with('product.product_features_relationship')
                    ->get();
            }
        }else
        {
            $product_arr = '$v';
            $result = product::select('*',
                DB::raw('DATE_FORMAT(DATE(`created_at`), "%d-%m-%Y") as created_date'))
                ->Where('product_id', '=', $product_id)
                ->where('company_id', Auth::user()->company_id)
                ->with('product_features_relationship')
                ->whereHas('product_price_master',function($q){
                    $q->where('company_id',Auth::user()->company_id);
                })->get();
        }
        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($result AS $key=>$v)
        {
            if($qty_search == 0)
            {
                $src = $v;
            }
            else{
                $src =   $v['product'];
            }
            if(isset($src['product_features_relationship']) && $src['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($src['product_features_relationship'][$html_id] != '' && $src['product_features_relationship'][$html_id] != NULL)
                    {
                        $nm = product::feature_value($vv['product_features_id'], $src['product_features_relationship'][$html_id]);

                        $src[$html_id] = $nm;
                    }
                }
            }
        }


        // $result = product::select('*')
        // ->Where('product_id','=',$product_id)
        // ->where('company_id',Auth::user()->company_id)
        // ->with('colour')
        // ->with('size')
        // ->with('price_master')
        // ->with('inward_product_detail')
        // ->with('subcategory')
        // ->with('category')
        // ->with('brand')
        // ->get();

        // $resultx = inward_product_detail::select('offer_price','product_mrp')
        // ->Where('inward_product_detail_id','=',$inward_id)
        // ->where('company_id',Auth::user()->company_id)
        // ->get();

        $userId             =   Auth::User()->user_id;
        $created_by         =   $userId;

        $company_id       =   Auth::user()->company_id;

        $company_name = company_profile::select('company_name')
        ->Where('company_id','=',$company_id)
        ->get();

        $CompanyName  =   $company_name[0]['company_name'];

        $secret_code = secret_code::where('is_active',1)->get();

        return json_encode(array("Success"=>"True","Data"=>$result,"CompanyName"=>$CompanyName,'secret_code'=>$secret_code));
    }

    public function fetchTemplateData(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $barcode_template_id        =   $request->BarcodeTemplateId;

        $result1 = barcode_template::select('*')
        ->Where('barcode_template_id','=',$barcode_template_id)
        ->Where('is_active','=',1)
        ->with('barcode_sheet')
        ->get();

        return json_encode(array("Success"=>"True","Data1"=>$result1));
    }

    public function GenerateBarcode(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $product_barcode    =   $request->product_barcode;
        $barcode_type       =   $request->barcode_type;

        $barcode    =   DNS1D::getBarcodeSVG($product_barcode, $barcode_type,1.6,36, true);
        return json_encode(array("Data"=>$barcode));
    }

}
