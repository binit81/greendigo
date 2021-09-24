$('#checkalldebitreceipt').change(function()
{
    if($(this).is(":checked")) {
        $("#debit_receipt_table tr").each(function()
        {
            var id = $(this).attr('id');

            $(this).find('td').each(function ()
            {
                $("#delete_debit_receipt"+id).prop('checked',true);
            });
        })
    }
    else
    {
        $("#debit_receipt_table tr").each(function(){
            var id = $(this).attr('id');
            $(this).find('td').each(function ()
            {
                $("#delete_debit_receipt"+id).prop('checked',false);
            });

        })
    }
});


$("#delete_supplier_payment").click(function ()
{
    // if(confirm("Are You Sure want to delete this debit note?")) {

        var ids = [];

        $('input[name="delete_debit_receipt[]"]:checked').each(function()
        {
            ids.push($(this).val());
        });


        if(ids.length > 0)
        {
            var errmsg = "Are You Sure want to delete this supplier payment receipt?";
        swal({
                title: errmsg,
                type: "warning",
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes!",
                showCancelButton: true,
                closeOnConfirm: true,
                closeOnCancel: true
            },


        function (isConfirm) {
                if (isConfirm) {

            var data = {
                "deleted_id": ids
            };
            var url = "supplier_payment_delete";
            var type = "POST";
            var dataType = "";
            callroute(url, type,dataType, data, function (data)
            {
                var dta = JSON.parse(data);

                if (dta['Success'] == "True")
                {
                    toastr.success(dta['Message']);
                    resettable('supplier_debit_receipt_refresh','supplier_debit_receipt_record');
                } else {
                    toastr.error(dta['Message']);
                }
            })
        }
        else
        {
            return false;
        }
    // }
    })
        }
    else
    {
        return false;
    }
});
function delete_separate_debit_receipt(obj,receipt_id,event)
{
    $(obj).closest('td').find('[type=checkbox]').prop('checked',true);
    event.preventDefault();
    if($(obj).closest('td').find('[type=checkbox]').prop('checked') == true)
    {
        setTimeout(
            function()
            {
                 $('#delete_supplier_payment')[0].click();
            }, 300);
    }

}
