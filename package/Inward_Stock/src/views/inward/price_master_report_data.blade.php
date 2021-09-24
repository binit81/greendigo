<?php
/**
 *
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 28/3/19
 * Time: 5:47 PM
 */

$tax_currency = '&#8377;';
$tax_title = 'GST';
$footer_cnt = 0;
if($nav_type[0]['tax_type'] == 1)
{
    $tax_title = $nav_type[0]['tax_title'];
    $tax_currency = $nav_type[0]['currency_title'];
}
$show_dynamic_feature = '';
?>
    <table id="pricemasterreport" class="table tablesaw table-bordered table-hover  mb-0"   data-tablesaw-sortable data-tablesaw-sortable-switch  data-tablesaw-no-labels>
    <thead>
    <tr class="blue_Head">
        <th class="garment_case_hide unique_show" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Batch No<span id="batch_no_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Barcode</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Product Name</th>

        <?php
        if (isset($product_features) && $product_features != '' && !empty($product_features))
        {

        foreach ($product_features AS $feature_key => $feature_value)
        {

        if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
        {
        $search =$urlData['breadcrumb'][0]['nav_url'];

        if (strstr($feature_value['show_feature_url'],$search) )
        {
        if($show_dynamic_feature == '')
        {
            $show_dynamic_feature =$feature_value['html_id'];
        }
        else
        {
            $show_dynamic_feature = $show_dynamic_feature.','.$feature_value['html_id'];
        }
        $footer_cnt++;
        ?>

        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist"><?php echo $feature_value['product_features_name']?></th>
        <?php } ?>
        <?php
        }
        }
        }
        ?>


        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">UQC</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Qty<span id="product_qty_icon"></span></th>
        <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Selling Price <?php echo $tax_currency?><span id="sell_price_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Selling <?php echo $tax_title ?> %<span id="selling_gst_percent_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Selling <?php echo $tax_title .' '.$tax_currency?><span id="selling_gst_amount_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Offer Price <?php echo $tax_currency?><span id="offer_price_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Product MRP <?php echo $tax_currency?><span id="product_mrp_icon"></span></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Wholesaler Price <?php echo $tax_currency?><span id="wholesaler_price_icon"></span></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody >


@foreach($price_master AS $price_key=>$price_value)
    <?php if($price_key % 2 == 0)
    {
        $tblclass = 'even';
    }
    else
    {
        $tblclass = 'odd';
    }

    $barcode = '';

    if($price_value['product']['supplier_barcode'] != '')
    {
        $barcode =  $price_value['product']['supplier_barcode'];
    }
    else
    {
        $barcode = $price_value['product']['product_system_barcode'];
    }

    $feature_show_val = "";

    if($show_dynamic_feature != '')
    {
        $feature = explode(',',$show_dynamic_feature);

        foreach($feature AS $fea_key=>$fea_val)
        {
            $feature_show_val .= '<td>'.$price_value['product'][$fea_val].'</td>';
        }
    }


    $uqc_name = '';
    if($price_value['product']['uqc_id'] != '' && $price_value['product']['uqc_id'] != null && $price_value['product']['uqc_id'] != 0)
    {
        $uqc_name = $price_value['product']['uqc']['uqc_shortname'];
    }

    ?>
    <tr id="{{$price_value->price_master_id}}" class="<?php echo $tblclass ?>">
        <td class="leftAlign garment_case_hide unique_show">{{$price_value->batch_no}}</td>
        <td class="leftAlign"><?php echo $barcode ?></td>
        <td class="leftAlign">{{$price_value->product->product_name}}</td>
        <?php

        echo $feature_show_val;

        ?>
        <td class="leftAlign"><?php echo $uqc_name?></td>
        <td class="rightAlign">{{$price_value->product_qty}}</td>
        <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
        <td class="rightAlign">{{number_format($price_value->sell_price,$nav_type[0]['decimalpoints_forview'])}}</td>
        <td class="rightAlign">{{number_format($price_value->selling_gst_percent,$nav_type[0]['decimalpoints_forview'])}}</td>
        <td class="rightAlign">{{number_format($price_value->selling_gst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
        <td class="rightAlign">{{number_format($price_value->offer_price,$nav_type[0]['decimalpoints_forview'])}}</td>
        <td class="rightAlign">{{number_format($price_value->product_mrp,$nav_type[0]['decimalpoints_forview'])}}</td>
        <td class="rightAlign">{{number_format($price_value->wholesaler_price,$nav_type[0]['decimalpoints_forview'])}}</td>
        <?php } ?>
    </tr>
@endforeach
<tr>
    <td colspan="<?php echo $footer_cnt+11?>" class="paginateui">
        {!! $price_master->links() !!}
    </td>
</tr>
    </tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="updated_at" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="desc" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="price_master_record" />

<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script type="text/javascript">
    $(".PagecountResult").html('{{$price_master->total()}}');
    $(".PagecountResult").addClass("itemfocus");

    <?php if($inward_type == 2) { ?>
    $(".garment_case_hide").hide();

    <?php
    if($nav_type[0]['inward_unique_batch_no_value'] != 'null' && $nav_type[0]['inward_unique_batch_no_value'] != 'NULL' && $nav_type[0]['inward_unique_batch_no_value'] != '')
    { ?>
        $(".unique_show").show();


    <?php } ?>


    <?php } else { ?>
    $(".garment_case_hide").show();
    <?php } ?>
</script>
