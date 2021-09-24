<?php

/**
 *
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 24/5/19
 * Time: 5:47 PM
 */

$tax_title = 'GST';

if($nav_type[0]['tax_type'] == 1)
{
    $tax_title = $nav_type[0]['tax_title'];
}

$show_dynamic_feature = '';

?>
<table id="batchnorecordtable" class="table table-bordered table-hover  mb-0"   data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-minimap data-tablesaw-mode-switch>
    <thead>
    <tr class="blue_Head">
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Debit Receipt No.</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Product Name</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Product Code</th>

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
        ?>

        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist"><?php echo $feature_value['product_features_name']?></th>
        <?php } ?>
        <?php

        }
        }
        }
        ?>

        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">UQC</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Supplier Name</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Return Qty</th>
        <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Total Cost Rate</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Total <?php echo $tax_title ?></th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Debit Amt</th>
        <?php } ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Remarks</th>
    </tr>
    </thead>
    <tbody>
    @foreach($debit_product_detail AS $report_key=>$report_value)
        <?php if($report_key % 2 == 0)
        {
            $tblclass = 'even';
        }
        else
        {
            $tblclass = 'odd';
        }

        $uqc_name = '';
        if($report_value['product']['uqc_id'] != '' && $report_value['product']['uqc_id'] != null && $report_value['product']['uqc_id'] != 0)
        {
            $uqc_name = $report_value['product']['uqc']['uqc_shortname'];
        }

        $product_code = '';
        if($report_value['product']['product_code'] != '' && $report_value['product']['product_code'] != null )
        {
            $product_code = $report_value['product']['product_code'];
        }

        $feature_show_val = "";
        if($show_dynamic_feature != '')
        {
            $feature = explode(',',$show_dynamic_feature);

            foreach($feature AS $fea_key=>$fea_val)
            {
                $feature_show_val .= '<td>'.$report_value['product'][$fea_val].'</td>';
            }
        }

        ?>
        <tr id="{{$report_value->debit_product_detail_id}}" class="<?php echo $tblclass ?>">
            <td>{{$report_value->debit_note->debit_no}}</td>
            <td>{{$report_value->product->product_name}}</td>
            <td><?php echo $product_code?></td>
            <?php
            echo $feature_show_val;
            ?>
            <td><?php echo $uqc_name?></td>
            <td>{{$report_value->debit_note->supplier_gstdetail->supplier_company_info->supplier_first_name}}</td>
            <td>{{$report_value->return_qty}}</td>
            <?php if($nav_type[0]['inward_calculation'] != 3) { ?>
            <td>{{$report_value->total_cost_rate}}</td>
            <td>{{$report_value->total_gst}}</td>
            <td>{{$report_value->total_cost_price}}</td>
            <?php } ?>
            <td>{{$report_value->remarks}}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="17" class="paginateui">
            {!! $debit_product_detail->links() !!}
        </td>
    </tr>
    </tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="debit_product_detail_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="debit_no_wise_search_record" />

<script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<script type="text/javascript">
    $(".PagecountResult").html('{{$debit_product_detail->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>

