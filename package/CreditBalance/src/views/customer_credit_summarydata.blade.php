<table id="view_creditbal_recordtable" class="table tablesaw table-hover mb-0" cellspacing="10">
  <!-- class="blue_Head table-bordered "                                            -->
<thead>
<tr>

    <th scope="col" data-tablesaw-priority="persist" class="centerAlign bold">View</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2" class="bold">Customer Name</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist" class="bold">Mobile No.</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist" class="bold">No. of Invoices</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist" class="rightAlign bold">Unpaid Amount</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist" class="rightAlign bold">Received Amount</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist" class="rightAlign bold">Balance Amount</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist" class="centerAlign bold">Action</th>

</tr>
</thead>
<tbody id="view_bill_record">

@foreach($customerbaldata AS $customerbalkey=>$customerbal_value)
<?php if ($customerbalkey % 2 == 0) {
    $tblclass = 'even';
} else {
    $tblclass = 'odd';
}
$sr  =   $customerbalkey + 1;

?>

<tr id="" class="<?php echo $tblclass ?>" style="background:#EEEEEE !important;margin:0 0 10px 0 !important;border-bottom: white 15px solid;">

    <td><span id="down_{{$customerbal_value->customer_id}}" onclick="return showdetails(this);" style="font-weight:bold;font-size:14px;color:#28a745 !important;"><i class="fa fa-arrow-down"></i>Show</span><span id="up_{{$customerbal_value->customer_id}}" onclick="return hidedetails(this);" style="font-weight:bold;font-size:14px;color:#f00 !important;display:none;"><i class="fa fa-arrow-up"></i>Hide</span></td>
    <td class="leftAlign bold">{{$customerbal_value['customer']['customer_name']}}</td>
    <td class="leftAlign">{{$customerbal_value['customer']['customer_mobile']}}</td>
    <td>{{$customerbal_value['totalinvoices']}}</td>
    <td class="rightAlign">{{$customerbal_value->totalcreditamount}}</td>
    <td class="rightAlign" style="color:#006400">{{$customerbal_value->recdamt?$customerbal_value->recdamt:0}}</td>
    <td class="rightAlign bold" style="color: #FF8C00">{{$customerbal_value->totalbalance}}</td>
    <?php
    if($customerbal_value->totalbalance==0)
    {
        ?><td><span style="color:rgb(40, 167, 69) !important;font-weight:bold;">Paid</span></td><?php
    }
    else
    {
    ?>
    <td><a href="{{URL::to('customer_credit_ac')}}?id={{encrypt($customerbal_value->customer_id)}}" style="text-decoration:none !important;" target="_blank"><button type="button" id="returnbillno_{{encrypt($customerbal_value->customer_id)}}" class="btn btn-primary" style="color:#fff;padding: .15rem .35rem;line-height:1.3 !important;font-size:13px !important;"><i class="fa fa-check" style="margin:0 !important;"></i> Receive Payment</button></a></td>
    <?php
        }
    ?>
</tr>
<tr id="show_{{$customerbal_value->customer_id}}" style="display:none;">
<td colspan="8" style="hover:#ffffff !important;">

<table width="100%" border="0" style="margin:-20px 0 0 0 !important;">
<tr>
    <td width="15%" style="vertical-align:top !important;" class="rightAlign"><span class="iconify" data-icon="mdi-subdirectory-arrow-right" data-inline="false" style="font-size: 80px;margin:-30px 0 0 0 !important;color:#DDDDDD;"></span></td>
    <td width="85%">
        <table class="table mb-0 detailTable" style="width:95%;float:right;" cellpadding="4">
<thead>
    <tr>

        <th scope="col" style="width:12%;cursor: pointer;font-weight:bold;font-size:14px;" class="leftAlign">Invoice No.</th>
        <th scope="col" style="width:12%;cursor: pointer;font-weight:bold;font-size:14px;" class="leftAlign">Invoice Date</th>
        <th scope="col" style="width:12%;cursor: pointer;font-weight:bold;font-size:14px;" class="leftAlign">Due Date</th>
        <th scope="col" style="width:12%;cursor: pointer;text-align:right !important;font-weight:bold;font-size:14px;">Unpaid Amt.</th>
        <th scope="col" style="width:12%;cursor: pointer;text-align:right !important;font-weight:bold;font-size:14px;">Received Amt.</th>
        <th scope="col" style="width:12%;cursor: pointer;text-align:right !important;font-weight:bold;font-size:14px;">Balance Amt.</th>
        <th scope="col" style="width:12%;cursor: pointer;font-weight:bold;font-size:14px;" class="centerAlign">Days Left</th>
    </tr>
    </thead>
    <tbody id="showproductdetails">
    <?php
    foreach($customerbal_value['customer_creditaccount'] as $creditval=>$creditdetail)
    {
            $start_date = strtotime(date("Y-m-d"));
            $end_date =   strtotime($creditdetail['duedate']);

            $daysleft =  ($end_date - $start_date)/60/60/24;
            if($daysleft < 0)
            {
                    $daysleft  = 0;
            }
            $recdamount   =  $creditdetail['credit_amount'] - $creditdetail['balance_amount'];
            ?>
             <tr style="border-bottom:0px solid #C0C0C0 !important;" height="35">
              <td class="leftAlign" style="cursor:pointer;">
                <?php
                if($creditdetail['receiptrecdamt'] !='')
                {
                    ?>

                <a id="viewcreditreceipt_{{$creditdetail->customer_creditaccount_id}}" onclick="return viewCreditreceipt(this);" title="View Credit Receipt Details"><i class="fa fa-eye"></i>

                 <?php
                    }
                 ?>   {{$creditdetail['sales_bill']['bill_no']}}</a>


             </td>
              <td class="leftAlign">{{$creditdetail['sales_bill']['bill_date']}}</td>
              <td class="leftAlign">{{$creditdetail['duedate']}}</td>
              <td class="rightAlign">{{$creditdetail['credit_amount']}}</td>
              <td class="rightAlign">{{$recdamount}}</td>
              <td class="rightAlign">{{$creditdetail['balance_amount']}}</td>
              <td>{{$daysleft}}</td>
            </tr>
            <?php
    }
    ?>
    </tbody>
    </table>
    </td>
</tr>
</table>

</td>
</tr>
@endforeach
<tr>
    <td colspan="17" align="center">
        {!! $customerbaldata->links() !!}
    </td>
</tr>

</tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="sales_bill_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_creditbaldetail" />
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>

<script type="text/javascript">
$('.PagecountResult').html('{{$customerbaldata->total()}}');
$(".PagecountResult").addClass("itemfocus");
$('.totinvoices').html({{round($totcusinvoices,$nav_type[0]['decimal_points'])}});
$('.totunpaidamt').html({{round($totunpaidamt,$nav_type[0]['decimal_points'])}});
$('.totrecdamt').html({{round($totrecdamt,$nav_type[0]['decimal_points'])}});
$('.totbalanceamt').html({{round($totbalanceamt,$nav_type[0]['decimal_points'])}});
function showdetails(obj)
{
    var id                       =     $(obj).attr('id');
    var salesid                  =     $(obj).attr('id').split('down_')[1];
    $('#show_'+salesid).toggle();
    $('#down_'+salesid).hide();
    $('#up_'+salesid).show();

}
function hidedetails(obj)
{
    var id                       =     $(obj).attr('id');
    var salesid                  =     $(obj).attr('id').split('up_')[1];
    $('#show_'+salesid).toggle();
    $('#down_'+salesid).show();
    $('#up_'+salesid).hide();
}
</script>