
<table class="table  table-bordered table-hover  mb-0" data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-no-labels>

    
<thead>
<tr class="blue_Head">
<th class="pa-10 leftAlign" width="10%">Store Name</th>
<th class="pa-10 leftAlign" width="9%">Return No.</th>
<th width="9%">Return Date</th>
<th width="11%">Barcode</th>
<th width="15%">Product Name</th>
<th width="8%">PCode</th>
<th width="5%">Qty</th>
<th width="12%">Restock/Damage</th>
<th width="11%">Remarks</th>
<th width="8%">Action</th>

</tr>
</thead>
<tbody id="view_bill_record">


@foreach($store_return AS $returnkey=>$return_value)

<?php

 if ($returnkey % 2 == 0) {
    $tblclass = 'even';
} else {
    $tblclass = 'odd';
}
    
 if($return_value['product']['supplier_barcode']!='' && $return_value['product']['supplier_barcode'] != NULL)  
 {
        $barcode      =    $return_value['product']['supplier_barcode'];
 } 
 else
 {
        $barcode      =    $return_value['product']['product_system_barcode'];
 }
 $restqty    =   $return_value['qty']  - $return_value['totalrdqty'];

?>

<tr id="viewbill_{{$return_value['storereturn_product_id']}}" class="<?php echo $tblclass ?>">

   
    <td class="leftAlign">{{$return_value['company']['full_name']}}</td>
    <td class="leftAlign">{{$return_value['store_return']['return_no']}}</td>
    <td class="leftAlign">{{$return_value['store_return']['return_date']}}</td>
    <td class="leftAlign">{{$barcode}}</td>
    <td class="leftAlign">{{$return_value['product']['product_name']}}</td>
    <td class="leftAlign">{{$return_value['product']['product_code']}}</td>
    <td class="rightAlign" id="returnqty_{{$return_value['storereturn_product_id']}}">{{$restqty}}</td>
    <td class="leftAlign" style="text-align:center !important;">
        <input type="hidden" id="inwardids_{{$return_value['storereturn_product_id']}}" value="{{$return_value['inward_product_detail_id']}}">
        <input type="text" id="restock_{{$return_value['storereturn_product_id']}}" onkeyup="return storerestockqty(this);" class="form-control mt-15" style="width:48% !important;margin:0 3px 0 0;">
        <input type="text" id="damage_{{$return_value['storereturn_product_id']}}" onkeyup="return storedamageqty(this);" class="form-control mt-15" style="width:48%  !important;">
        <input type="hidden" id="pricemasterid_{{$return_value['storereturn_product_id']}}" class="form-control mt-15" style="width:49%  !important;" value="{{$return_value['price_master_id']}}">
        <input type="hidden" id="productid_{{$return_value['storereturn_product_id']}}" class="form-control mt-15" style="width:49%  !important;" value="{{$return_value['product_id']}}">
        <input type="hidden" id="storecompanyid_{{$return_value['storereturn_product_id']}}" class="form-control mt-15" style="width:49%  !important;" value="{{$return_value['company_id']}}">
    <td class="leftAlign" style="text-align:center !important;">
        <textarea id="remarks_{{$return_value['storereturn_product_id']}}" rows="2" style="width:100%;border-radius:5px;border:1px solid #ced4da;"></textarea>
    </td>
     <td class="leftAlign" style="text-align:center !important;"><button type="button" class="btn btn-info" name="addbilling" id="add_storereturnproducts_{{$return_value['storereturn_product_id']}}" onclick="return storesavereturn(this);" style="padding: 0rem .5rem !important;">Update</button></td>
    
</tr>


@endforeach
<tr>
    <td colspan="12" align="center">
        {!! $store_return->links() !!}
    </td>
</tr>

</tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="storereturn_product_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_manage_storereturn" />
<script type="text/javascript">
    $(".PagecountResult").html('{{$store_return->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>