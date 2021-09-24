<?php

if(sizeof($result)!=0)
{
$show_dynamic_feature = $_REQUEST['show_dynamic_feature'];
$footer_cnt = $_REQUEST['footer_cnt'];
?>

@foreach($result AS $resultkey=>$result_value)
<?php if ($resultkey % 2 == 0)
{
    $tblclass = 'even';
}
else
{
    $tblclass = 'odd';
}
$get_id_val = '$get_id_val';

if($qty_search == 0)
    {
        $get_id_val = 'product_id';
        $barcode = $result_value->product_system_barcode;
        $qty = 0;
        $batch_no_remarks = '';
        $fetch_arr_name = $result_value;
        $cost_price = $result_value->cost_price;
        $offer_price = $result_value->offer_price;
        $fetch_arr = $result_value;
    }
else
    {
if($search_fileter_with == 1)
    {
        $get_id_val = 'inward_product_detail_id';
        $barcode = $result_value->product->product_system_barcode;
        $qty = $result_value->product_qty;
        $batch_no_remarks = '';
        $cost_price = $result_value->cost_price;
        $offer_price = $result_value->offer_price;
        $fetch_arr_name = $result_value->product;
        $fetch_arr = $result_value;
    }
if($search_fileter_with == 2)
    {
        $get_id_val = 'purchase_order_detail_id';
        $barcode = $result_value->unique_barcode;
        $qty = $result_value->qty;
        $batch_no_remarks = $result_value->remarks;
        $cost_price = $result_value->total_cost_with_gst;
        $fetch_arr_name = $result_value->product;
        $fetch_arr = $result_value;
        $offer_price = $result_value->offer_price;
    }
}

$feature_show_val = "";

if($show_dynamic_feature != '')
{
    $feature = explode(',',$show_dynamic_feature);

    foreach($feature AS $fea_key=>$fea_val)
    {
        $feature_show_val .= '<td>'.$fetch_arr_name[$fea_val].'</td>';
    }
}

?>



<tr class="<?php echo $tblclass ?>" id="printTbl_{{$fetch_arr->$get_id_val}}">
    <td class="leftAlign">{{$fetch_arr_name->product_name}}</td>
    <td class="leftAlign"><?php echo $barcode?></td>
    <td class="leftAlign">{{$fetch_arr_name->supplier_barcode}}</td>
    <td class="leftAlign">{{$fetch_arr_name->product_code}}</td>
    <td class="leftAlign">{{$fetch_arr_name->sku_code}}</td>
    <td class="leftAlign">{{$fetch_arr_name->mfg_date}}</td>
    <td class="leftAlign">{{$fetch_arr_name->expiry_date}}</td>
    <?php
    echo $feature_show_val;
    ?>
    <td><input name="pack_size" id="pack_size" type="text" class="form-control" size="1" value="<?php echo $qty?>" readonly="readonly" /></td>
    <td class="leftAlign"><?php echo $batch_no_remarks?></td>
    <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
    <td class="rightAlign"><?php echo number_format($cost_price,0) ?></td>
    <td class="rightAlign">{{$fetch_arr->offer_price}}</td>
    <?php } ?>
    <td id="fetchval_{{$fetch_arr->$get_id_val}}"><input name="printStock[]" id="printStock_{{$fetch_arr->$get_id_val}}" type="text" class="form-control printStock" onkeyup="getTotalPrintQty()" value="1" style="width:50px;" />
        <input name="productid[]" id="productid_{{$fetch_arr->$get_id_val}}" type="hidden" class="form-control printStockId" value="{{$fetch_arr->product_id}}" style="width:50px;" />
        <input name="inwardid[]" id="inwardid_{{$fetch_arr->$get_id_val}}" type="hidden" class="form-control printStockId" value="{{$fetch_arr->$get_id_val}}" style="width:50px;" />
    </td>
    <td class="leftAlign">
        <span class="fa fa-remove cursor" id="removeTbl_{{$fetch_arr->$get_id_val}}" onClick="RemovePrintTbl(this)"></span>
        <input name="" type="hidden" class="form-control totalResults" size="1" value="1" readonly="readonly" /></td>
</tr>

<script type="text/javascript">
$(document).ready(function(e){

    var printStock  =   0;
    $('.printStock').each(function (index,e){
        printStock   +=   parseFloat($(this).val());
    });

    $('#barcodeTotalQty').html(printStock);
    $('#barcodeTotalQty_text').val(printStock);
    $('#search_fileter_with').val("<?php echo $search_fileter_with?>");
});
</script>

@endforeach

@foreach($result1 AS $resultkey1=>$result_value1)

<script type="text/javascript">
$(document).ready(function(e)
{
    $('.totalSearchCount').html(' (<?php echo $result_value1->totalCount?>)');
});
</script>

@endforeach

<?php } else {

    if(!isset($footer_cnt)){
        $footer_cnt = $_REQUEST['footer_cnt'];
    }
    else{
         $footer_cnt = $footer_cnt;
    }
    ?>

<?php }?>
