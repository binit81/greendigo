<?php
$ftr_cnt = 0;
?>
<table id="view_billgst_recorddata" class="table tablesaw table-bordered table-hover table-striped mb-0"
       data-tablesaw-sortable  data-tablesaw-no-labels>

    <thead>
    <tr class="blue_Head">
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Invoice No.</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Inward Date</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Supplier</th>
        @foreach($gst_slabs AS $gstkey=>$gst_value)
            <?php
            $ftr_cnt += 4;
            $taxable = 0;
            if ($gst_value['cost_igst_percent'] == 0 &&  $gst_value['cost_sgst_percent'] !=0)
            {
                $taxable = $gst_value['cost_sgst_percent'] * 2;
            } else {
                $taxable = $gst_value['cost_igst_percent'];
            }
            ?>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5"><?php echo $taxable?>%<br>Taxable</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">{{$gst_value['cost_cgst_percent']}}%<br>CGST</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">{{$gst_value['cost_sgst_percent']}}%<br>SGST</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">{{$gst_value['cost_igst_percent']}}%<br>IGST</th>
        @endforeach
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Total Taxable</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Total CGST</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Total SGST</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Total IGST</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Total Amount</th>
    </tr>
    </thead>
    <tbody>
    @foreach($inward_stock AS $key=>$inward_value)
        <?php
        $supplier_company_name = '';
            if(isset($inward_value['supplier_gstdetail']) && $inward_value['supplier_gstdetail'] != null)
                {
                    $supplier_company_name = $inward_value['supplier_gstdetail']['supplier_company_info']['supplier_company_name'];
                }

        ?>
        <tr id="">
            <td class="leftAlign">{{$inward_value->invoice_no}}</td>
            <td class="leftAlign">{{$inward_value->inward_date}}</td>
            <td class="leftAlign"><?php echo $supplier_company_name?></td>
            <?php
            foreach($gst_slabs AS $gstkey=>$gst_value)
            {
                $count = 0;
                $inward_cgst = 0;
                $inward_sgst = 0;
                $inward_igst = 0;
                $total_cost = 0;

            foreach ($inward_value->inward_product_detail AS $inward_key => $inward_product_value)
            {
                $cost_rate = 0;
                if ($gst_value->cost_igst_percent == $inward_product_value->cost_igst_percent)
                {
                    $cost_rate += $inward_value->cost_rate;
                    $inward_cgst += $inward_product_value->cost_cgst_amount;
                    $inward_sgst += $inward_product_value->cost_sgst_amount;
                    $inward_igst += $inward_product_value->cost_igst_amount;
                    $total_cost += $inward_product_value->total_cost;
                    $count++;
                }
            }

            if($count == 0)
            {
            ?>
                <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
            <?php
            }
            else
            {
            ?>
                <td style="text-align:right !important;">{{number_format($cost_rate,$nav_type[0]['decimalpoints_forview'])}}</td>
                <?php
                if($inward_value['state_id'] == $company_state)
                {
                ?>
                <td style="text-align:right !important;">{{number_format($inward_cgst,$nav_type[0]['decimalpoints_forview'])}}</td>
                <td style="text-align:right !important;">{{number_format($inward_sgst,$nav_type[0]['decimalpoints_forview'])}}</td>
                <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
            <?php
            }
            else
            {
            ?>
            <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
            <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
            <td style="text-align:right !important;">{{number_format($inward_igst,$nav_type[0]['decimalpoints_forview'])}}</td>
            <?php
            }
            }
            }
            ?>

            <td style="text-align:right !important;">{{number_format($inward_value->cost_rate,$nav_type[0]['decimalpoints_forview'])}}</td>
            <?php
            if($inward_value['state_id'] == $company_state)
            {
            ?>
            <td style="text-align:right !important;">{{number_format($inward_value->total_cost_cgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
            <td style="text-align:right !important;">{{number_format($inward_value->total_cost_sgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
            <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
            <?php
            }
            else
            {
            ?>
            <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
            <td style="text-align:right !important;">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
            <td style="text-align:right !important;">{{number_format($inward_value->total_cost_igst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
            <?php
            }
            ?>
            <td style="text-align:right !important;" class="bold">{{number_format($inward_value->total_gross,$nav_type[0]['decimalpoints_forview'])}}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="<?php echo 8+$ftr_cnt?>" align="center">
            {!! $inward_stock->links() !!}
        </td>
    </tr>

    </tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1"/>
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="inward_stock_id"/>
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC"/>
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="inward_gstperwise_search"/>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>

<script>

    $(".PagecountResult").html('{{$inward_stock->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>
