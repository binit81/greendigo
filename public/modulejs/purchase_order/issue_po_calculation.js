function addqty(obj)
{
    var tbl_row = $(obj).closest('tr').data('id');
    $("#po_product_detail_record").find("tr[data-id='" + tbl_row + "']").each(function ()
    {
        var product_id = $(obj).attr('id').split('qty_')[1];

        var qty = Number($(this).find('#qty_' + product_id).html());
        if (qty == '' || isNaN(qty))
        {
            qty = 0;
        }

        var costrate = Number($(this).find("#cost_rate_" +product_id).html());
        var gst_percent = Number($(this).find("#cost_gst_percent_" +product_id).html());
        var gstamt = ((Number(costrate)) * (Number(gst_percent)) / (Number(100)));
        $(this).find("#cost_gst_amount_" +product_id).html(gstamt.toFixed(4));
        var total_cost_without_gst = ((Number(costrate)) * (Number(qty)));
        $(this).find("#total_cost_without_gst_" + product_id).html(total_cost_without_gst.toFixed(4));
        var gst_amount = $(this).find("#cost_gst_amount_" + product_id).html();
        var total_gst = ((Number(gst_amount)) * (Number(qty)));
        $(this).find("#total_gst_" + product_id).html(total_gst.toFixed(4));
        var total_cost_with_gst = (((Number(costrate)) + (Number(gst_amount))) * (Number(qty)));
        $(this).find("#total_cost_with_gst_" + product_id).html(total_cost_with_gst.toFixed(4));
        gettotalqty();
    });
}

function costrate(obj)
{
    var product_id = $(obj).attr('id').split('cost_rate_')[1];

    var cost_rate = $("#cost_rate_"+product_id).html();

    var cost_gst_percent = $("#cost_gst_percent_"+product_id).html();

    var cost_gst_amount = ((Number(cost_rate)) * (Number(cost_gst_percent)) /(Number(100)));

    $("#cost_gst_amount_"+product_id).html(cost_gst_amount.toFixed(4));

    var qty  = $("#qty_"+product_id).html();

    var total_cost_without_gst = ((Number(cost_rate)) * (Number(qty)));

    $("#total_cost_without_gst_"+product_id).html(total_cost_without_gst.toFixed(4));

    var gst_amount = $("#cost_gst_amount_"+product_id).html();

    var total_gst = ((Number(gst_amount)) * (Number(qty)));

    $("#total_gst_"+product_id).html(total_gst.toFixed(4));

    var total_cost_with_gst = (((Number(cost_rate)) + (Number(gst_amount)))*(Number(qty)));

    $("#total_cost_with_gst_"+product_id).html(total_cost_with_gst.toFixed(4));

    gettotalqty();
}

function gettotalqty()
{
    var total_qty = 0;
    var totalcostrate = 0;
    var totalgst = 0;
    var totalcostprice = 0;
    $("#po_product_detail_record tr").each(function (index,e)
    {
        var product_id = $(this).attr('id').split('product_')[1];

        $(this).find('td').each(function ()
        {
            if($(this).attr('id') == "qty_"+product_id)
            {
                var totalqty  = $(this).html();
                if(totalqty == '')
                {
                    totalqty = 0;
                }
                total_qty += (Number(totalqty));
            }
            if($(this).attr('id') == "total_cost_without_gst_"+product_id)
            {
                var costrate = $(this).html();

                if ($.isNumeric(costrate))
                {
                    totalcostrate += (Number(costrate));
                }
            }
            if($(this).attr('id') == "total_gst_"+product_id)
            {
                var gst = $(this).html();

                if ($.isNumeric(gst))
                {
                    totalgst += (Number(gst));
                }
            }
            if($(this).attr('id') == "total_cost_with_gst_"+product_id)
            {
                var costprice = $(this).html();

                if ($.isNumeric(costprice))
                {
                    totalcostprice += (Number(costprice));
                }
            }
        });
    });
    $("#total_qty").val(total_qty);
    $("#total_cost_rate").val(totalcostrate.toFixed(decimal_points));
    $("#total_gst").val(totalgst.toFixed(decimal_points));
    $("#total_cost_price").val(totalcostprice.toFixed(decimal_points));
}


