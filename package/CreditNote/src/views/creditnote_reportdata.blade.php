
<table id="view_creditnote_recorddata" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>
<thead>
<tr class="blue_Head">
 <?php
     if(sizeof($get_store)!=0)
     {
     ?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Location</th>
    <?php
    }
    ?>
<th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Credit No.</th>
<th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Date</th>
<th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Customer</th>
<th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Returned Invoice</th>
<th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Credit Amount</span></th>
<th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Used Amount</span></th>
<th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Balance Amount</th>
<th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Action</th>
</tr>
</thead>
<tbody>

<?php

 if(sizeof($receipts) != 0)
 {

?>

@foreach($receipts AS $receiptkey=>$receipt_value)
<?php if ($receiptkey % 2 == 0) {
    $tblclass = 'even';
} else {
    $tblclass = 'odd';
}
$remarks  =  $receipt_value->remarks!=''?$receipt_value->remarks:'- - - - -';
$usedamount  =  $receipt_value['usedamount']!=''?$receipt_value['usedamount']:0;
?>

<tr>

    <?php
     if(sizeof($get_store)!=0)
     {
     ?>
     <td>{{$companyname}}</td>
     <?php
     }
     ?>
    <td>{{$receipt_value->creditnote_no}}</td>
    <td>{{$receipt_value->creditnote_date}}</td>
    <td>{{$receipt_value['customer']['customer_name']}}</td>
    <td>{{$receipt_value['sales_bill']['bill_no']}}</td>
    <td class="rightAlign">{{number_format($receipt_value->creditnote_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign">{{number_format($usedamount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign">{{number_format($receipt_value->balance_amount,$nav_type[0]['decimalpoints_forview'])}}</td>


  <td class="rightAlign">
    <a href="{{URL::to('print_creditnote')}}?id={{encrypt($receipt_value->return_bill_id)}}" style="text-decoration:none !important;" target="_blank" title="Print"><i class="fa fa-print" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
    <a href="{{URL::to('thermalprint_creditnote')}}?id={{encrypt($receipt_value->return_bill_id)}}" style="text-decoration:none !important;" target="_blank" title="Thermal Print"><img src="http://localhost/retailcore_v/public/images/thermalprint_icon" title="Thermal Print" width="25" class=""></a>
    <a  id="viewreceipt_{{$receipt_value->customer_creditnote_id}}"  onclick="return viewcreditnote(this);" style="text-decoration:none !important;" target="_blank" title="View Details"><i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
</td>

</tr>

@endforeach
<tr>
    <td colspan="17" align="center">
        {!! $receipts->links() !!}
    </td>
</tr>

<?php
}

else
{
        ?>
            <tr>
            <td colspan="18" class="leftAlign">
            <b style="font-size:16px;">No Records Found!</b>
            </td>
            </tr>
        <?php
}
?>
<script type="text/javascript">
$(document).ready(function(e){





});
</script>
</tbody>
</table>
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="customer_creditnote_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_cuscreditnotedetail" />
<script type="text/javascript">
    $(".PagecountResult").html('{{$receipts->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>