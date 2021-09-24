<?php
 $tax_label = 'GST';

 if($nav_type[0]['tax_type'] == 1)
    {
        $tax_label = $nav_type[0]['tax_title'];
    }
$show_inward_dynamic_feature = '';

?>
<div class="table-wrap">
<div class="table-responsive">
{{--
<table id="inwardtable" class="table tablesaw   table-bordered table-hover  mb-0"  data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-no-labels>
--}}
<table id="inwardtable" class="table  table-bordered table-hover  mb-0" data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-no-labels>
    <thead>
    <tr class="blue_Head">
        <th><i class="fa fa-remove"></i></th>
        <th scope="col" >Add Qty <input style="display: none;" type="checkbox" class="" name="get_all_qty" id="get_all_qty">   </th>
        <th class="garment_case_hide" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="19">Free Qty</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Barcode</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Prod Name</th>
	    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Prod Code</th>
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
        if($show_inward_dynamic_feature == '')
        {
            $show_inward_dynamic_feature =$feature_value['html_id'];
        }
        else
        {
            $show_inward_dynamic_feature = $show_inward_dynamic_feature.','.$feature_value['html_id'];
        }
        ?>

        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3"><?php echo $feature_value['product_features_name']?></th>
        <?php } ?>
        <?php
        }
        }
        }
        ?>
        <th class="garment_case_hide show_in_unique" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4"><?php echo $nav_type[0]['unique_barcode_name']?></th>
        <th class="inward_calculation_case" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Base Price</th>
        <th class="inward_calculation_case" colspan="2" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Disc % & Amt</th>
        <th class="garment_case_hide inward_calculation_case" colspan="2" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7" >Scheme % & Amt</th>
        <th class="garment_case_hide inward_calculation_case" colspan="2" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8" >Free % & Amt</th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="9">Cost Price</th>
        <th colspan="2" class="inward_calculation_case" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10"><?php echo $tax_label?> % & Amt</th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="11">Extra Charge</th>
        <th colspan="2" class="inward_calculation_case" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12" >Profit % & Amt</th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="13">SellPrice</th>
        <th colspan="2" class="inward_calculation_case" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="14"><?php echo $tax_label?> % & Amt</th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="15">Offer Price</th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="16">MRP</th>
        <th id="po_pending_show" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="17">Pending Qty for this PO</th>
        <th class="garment_case_hide" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="20">Mfg Date</th>
        <th class="garment_case_hide" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="21">Exp Date</th>
        <th scope="col" class="inward_calculation_case" data-tablesaw-sortable-col data-tablesaw-priority="22">Total Cost</th>
        <th id="pending_qty_return" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="23">InStock Qty for this Inward</th>
    </tr>
    </thead>
    <tbody id="product_detail_record">
    </tbody>
</table>
</div>
</div>
<script>
    $("#show_inward_dynamic_feature").val('<?php echo $show_inward_dynamic_feature ?>');
</script>
