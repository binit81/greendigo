<?php
/**
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 9/3/19
 * Time: 2:45 PM
 */

$tax_label = 'GST';
$tax_currency= '(&#8377)';

if($nav_type[0]['tax_type'] == 1)
{
    $tax_label = $nav_type[0]['tax_title'];
    $tax_currency = '('.$nav_type[0]['currency_title'].')';
}

?>
<table id="productrecordtable" class="table table-bordered table-hover mb-0 tablesaw" data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-no-labels>

<thead>
<tr class="blue_Head ">
    <th scope="col" class="tablesaw-swipe-cellpersist" >
        <div class="custom-control custom-checkbox checkbox-primary">
            <input type="checkbox" class="custom-control-input" id="checkallproduct" name="checkallproduct" >
            <label class="custom-control-label" for="checkallproduct"></label>
        </div>
    </th>
    <th scope="col" class="tablesaw-swipe-cellpersist center"  data-tablesaw-priority="persist"></th>
    <th scope="col" class="leftAlign itemtypeshow" data-tablesaw-sortable-col data-tablesaw-priority="persist" style="display: none">Item Type<span id="product_system_barcode_icon"></span></th>
    <th scope="col" class="leftAlign" data-tablesaw-sortable-col data-tablesaw-priority="persist" >System Barcode<span id="product_system_barcode_icon"></span></th>
    <th scope="col" class="leftAlign" data-tablesaw-sortable-col data-tablesaw-priority="persist">Product Name<span id="product_name_icon"></span></th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Supplier Barcode<span id="supplier_barcode_icon"></span></th>
    <!-- <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Category</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Sub Category</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Brand</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Colour</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Size</th> -->
     @foreach($product_features AS $product_featureskey=>$product_features_value)
      <th scope="col" data-tablesaw-sortable-col >{{$product_features_value['product_features_name']}}</th>
      @endforeach
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">UQC</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="9">Cost Rate</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="10">Cost <?php echo $tax_label?> (%)</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="11">Cost <?php echo $tax_label.' '.$tax_currency?></th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="12">Cost Price</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="12">Extra Charge</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="13">Profit(%)</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="14">Profit <?php echo $tax_currency?></th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="15">Selling Rate</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="16">Selling <?php echo $tax_label?> (%)</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="17">Selling <?php echo $tax_label.' '.$tax_currency?></th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="18">Product MRP</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="19">Offer Price</th>
    <th scope="col" class="with_calculation" data-tablesaw-sortable-col data-tablesaw-priority="20">Wholesale Price</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="21">SKU</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="22">Product Code</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="23">HSN</th>
    <?php if($inward_type == 1) { ?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="24">Alert Before Product Expiry(Days)</th>
    <?php } ?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="25">Low Stock Alert</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="26">MOQ</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="27">Note</th>
    </tr>
</thead>
<tbody id="productsalldata">

@foreach($product AS $productkey=>$product_value)
<?php if($productkey % 2 == 0)
{
    $tblclass = 'even';
}
else
{
    $tblclass = 'odd';
}

if($product_value->product_image_['product_image']!='')
{
    $product_image = PRODUCT_IMAGE_URL.$product_value->product_image_['product_image'];
}
else
{
    $product_image = default_product_image_url.'img-thumb.jpg';
}

?>
<tr id="{{$product_value->product_id}}" class="<?php echo $tblclass ?>">
    <td class="center">
         <?php if($product_value['delete_option'] == 1 && $role_permissions['permission_delete'] == 1 && $product_value['company_id'] == $nav_type[0]['company_id']) {?>
        <input type="checkbox" name="delete_product[]" value="{{$product_value->product_id }}" id="delete_product_{{$product_value->product_id }}">
        <?php } else {?>

             <button type="button" class="pa-0 ma-0  bold" style="font-size:10px;"  data-trigger="focus" data-placement="top" data-toggle="popover"  title="" data-content="This product cannot be deleted as this record is associated with other records or transactions." ><i class="fa fa-eye cursor"></i></button>
             <?php } ?>
        <?php
        if($role_permissions['permission_edit']==1 && $product_value['company_id'] == $nav_type[0]['company_id'])
        {
        ?>
            <a onclick="return editproduct('{{encrypt($product_value->product_id)}}');">
            <i class="fa fa-edit" title="Edit Product"></i></a>
        <?php
        }
        ?>

         <?php if($product_value['delete_option'] == 1 && $role_permissions['permission_delete'] == 1 && $product_value['company_id'] == $nav_type[0]['company_id']) {?>
            <a id="delete_separate_product" onclick="return delete_separate_product(this,'{{$product_value->product_id}}',event);">
                <i class="fa fa-trash" title="Delete Product"></i></a>
           <?php } ?>

             <?php if($product_value['delete_option'] == 0) { ?>
        <a title="Dependent Record" class="dependent_record" onclick="return dependent_record(this);"  data-id="{{encrypt($product_value->product_id)}}" data-url="product_dependency">
        <i class="fa fa-link" aria-hidden="true"></i>
           <?php } ?>

    </td>
    <td class="leftAlign"><div class="media-img-wrap d-flex mr-10 cursor" onclick="productImages('{{$product_value->product_id}}')">
    <div class="avatar"><img src="{{$product_image}}" class="img-fluid img-thumbnail" alt="img"></div>
</div></td>
    <?php
    $item_type = 'Regular';
    if($product_value['item_type'] == 2)
        {
            $item_type = 'Service';
        }
    if($product_value['item_type'] == 3)
    {
        $item_type = 'Unique';
    }

    ?>
    <td class="leftAlign itemtypeshow" style="display:none"><?php echo $item_type?></td>
    <td class="leftAlign" >{{$product_value->product_system_barcode}}</td>
    <td class="leftAlign">{{$product_value->product_name}}</td>
    <td class="leftAlign">{{$product_value->supplier_barcode}}</td>

    @foreach($product_features AS $product_featureskey=>$product_features_value)
        <?php
        $feature_name_value = '';
        $dynamic_name =$product_features_value['html_id'];

        if(isset($product_value[$dynamic_name]))
            {
                $feature_name_value = $product_value[$dynamic_name];
            }
           ?>
            <td class="leftAlign"><?php echo $feature_name_value ?></td>
      @endforeach

    <?php if($product_value['uqc_id'] != NULL) {$uqc_shortname = $product_value->uqc->uqc_shortname;} else{ $uqc_shortname = ''; }?>
    <td class="leftAlign">{{$uqc_shortname}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->cost_rate,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->cost_gst_percent,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->cost_gst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->cost_price,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->extra_charge,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->profit_percent,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->profit_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->selling_price,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->sell_gst_percent,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->sell_gst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->product_mrp,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->offer_price,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign with_calculation">{{number_format($product_value->wholesale_price,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="leftAlign">{{$product_value->sku_code}}</td>
    <td class="leftAlign">{{$product_value->product_code}}</td>
    <td class="leftAlign">{{$product_value->hsn_sac_code}}</td>
    <?php if($inward_type == 1) { ?>
    <td class="leftAlign">{{$product_value->days_before_product_expiry}}</td>
    <?php } ?>
    <td class="leftAlign">{{$product_value->alert_product_qty}}</td>
    <td class="leftAlign">{{$product_value->default_qty}}</td>
    <td class="leftAlign">{{$product_value->note}}</td>

    {{--<td><a data-product_id="{{encrypt($product_value->product_id)}}" id="editproduct"><i class="fa fa-edit"></i></a></td>--}}
</tr>
@endforeach
<tr>
    <td colspan="28" class="paginateui">
        {!! $product->links() !!}
    </td>
</tr>
</tbody>
    </table>
    <input type="hidden" name="hidden_page" id="hidden_page" value="1" />
    <input type="hidden" name="hidden_column_name" id="hidden_column_name" value="product_id" />
    <input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="desc" />
    <input type="hidden" name="fetch_data_url" id="fetch_data_url" value="product_fetch_data" />


    <script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script>
    var system_barcode_final = '<?php echo $system_barcode_final?>';


    <?php
    if($nav_type[0]['product_calculation'] == 3)
        { ?>
        $(".with_calculation").hide();
     <?php } else { ?>
     $(".with_calculation").show();
     <?php } ?>

    $('.PagecountResult').html('{{$product->total()}}');
    $(".PagecountResult").addClass("itemfocus");
    $("#product_system_barcode").val(system_barcode_final);

    <?php if(strpos($nav_type[0]['product_item_type'],',') == true) { ?>
        $(".itemtypeshow").show();
    <?php } ?>
</script>
