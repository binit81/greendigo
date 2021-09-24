<table id="flatsearchProducttable" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>
<thead>
<tr class="blue_Head">
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Product Name</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Barcode</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">HSN</th>
    <?php
    $show_dynamic_feature = '';
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
    ?>

    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist"><?php echo $feature_value['product_features_name']?></th>
    <?php } ?>
    <?php

    }
    }
    }
    ?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">UQC</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">OfferPrice</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Disc%</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Disc.Amt.</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Discounted Price</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Start Date</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">End Date</th> 
    
    <th>&nbsp;</th>
</tr>
</thead>

<tbody id="flatproductdetail">
 <?php
if($message !='')
{
    ?>
    <tr>
        <td colspan="13" class="leftAlign" height="50" style="white-space: unset !important;color:#f00;">
            <b style="font-size:16px;">{{$message}}</b>
        </td>
        
    </tr>
    <?php
}
else
{



 if(sizeof($product)!=0)
 {
 ?>   
    @foreach($product AS $productkey=>$product_value)
        <?php if($productkey % 2 == 0)
        {
            $tblclass = 'even';
        }
        else
        {
            $tblclass = 'odd';
        }
        $offer_price = 0;

        if(sizeof($product_value['inward_product_detail'])!=0)
        {
          
            $offer_price = $product_value['inward_product_detail'][0]['offer_price'];
        }

    
    $feature_show_val = "";
    if($show_dynamic_feature != '')
    {
        $feature = explode(',',$show_dynamic_feature);

        foreach($feature AS $fea_key=>$fea_val)
        {
            
            $feature_show_val .= '<td>'.$product_value[$fea_val].'</td>';
        }
    }
 ?>

     
<tr id="product_{{$product_value->product_id}}" class="<?php echo $tblclass ?>">
    <td class="leftAlign">{{$product_value->product_name}}</td>
    <td class="leftAlign" >{{$product_value->product_system_barcode}}</td>
    <td class="leftAlign">{{$product_value->hsn_sac_code!=0?$product_value->hsn_sac_code:''}}</td>
     <?php
        echo $feature_show_val;
     ?>
    <?php if($product_value['uqc_id'] != NULL) {$uqc_shortname = $product_value->uqc->uqc_shortname;} else{ $uqc_shortname = ''; }?>
    <td class="leftAlign">{{$uqc_shortname}}</td>
    <td class="rightAlign" id="showmrp_{{$product_value->product_id}}"><span id="mrp_{{$product_value->product_id}}">{{$offer_price}}</span></td>
    <td class="rightAlign" id="caldiscountper_{{$product_value->product_id}}"><input type="text" class="form-control" id="flatdiscountper_{{$product_value->product_id}}" style="width:60px !important;" onkeyup="return productflatdiscper(this);"></td>
    <td class="rightAlign" id="caldiscountamt_{{$product_value->product_id}}"><input type="text" class="form-control" id="flatdiscountamt_{{$product_value->product_id}}" style="width:80px !important;" onkeyup="return productflatdiscamt(this);"></td>
    <td class="rightAlign" id="showoffer_{{$product_value->product_id}}"><span id="offerprice_{{$product_value->product_id}}">{{$offer_price}}</span>
    <input type="hidden" id="productid_{{$product_value->product_id}}" class="productid" value="{{$product_value->product_id}}"></td>
    <td class="leftAlign"><span id="startdate_{{$product_value->product_id}}" class="startdate"></span></td>
    <td class="leftAlign"><span id="enddate_{{$product_value->product_id}}" class="enddate"></span></td>
    
    <td onclick="removerow({{$product_value['product_id']}});"><i class="fa fa-close"></i></td>
   
</tr>
@endforeach
<?php
}

}
?>

</tbody>
</table>

<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="product_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="desc" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="search_flatproduct_data" />
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>