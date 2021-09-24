$("#addgstslab").click(function (e) {
    if (validate_gstslabs('gstslabsform')) {
        $("#addgstslab").prop('disabled', true);

        var data = {
            "formdata": $("#gstslabsform").serialize(),
        };
        var url = "gstslabs_create";
        var dataType = "";
        var type = "POST";
        callroute(url, type, dataType, data, function (data) {
            var dta = JSON.parse(data);

            if (dta['Success'] == "True") {
                toastr.success(dta['Message']);
                $("#gstslabsform").trigger('reset');
                $("#gst_slabs_master_id").val('');
                resettable('gst_slabs_data', 'tablegstrecord');
            } else {
                toastr.error(dta['Message']);
            }
            $("#addgstslab").prop('disabled', false);
        })

    }
    //$(this).prop('disabled', false);
    e.preventDefault();
});

function validate_gstslabs(frmid) {
    var error = 0;

    if ($("#selling_price_from").val() == '') {
        error = 1;
        toastr.error("Enter From Selling Price!");
        return false;
    }
    if ($("#selling_price_to").val() == '') {
        error = 1;
        toastr.error("Enter To Selling Price!");
        return false;
    }

    if ($("#percentage").val() == '') {
        error = 1;
        toastr.error("Enter GST Percentage!");
        return false;
    }

    var sellingfrom = $("#selling_price_from").val();
    var sellingto = $("#selling_price_to").val();


    if (Number(sellingto) < Number(sellingfrom)) {
        error = 1;
        toastr.error("To selling price can not be less than from selling price!");
        return false;
    }

    if (error == 1) {
        return false;
    } else {
        return true;
    }
}


//edit product

function editgstslabs(gstslabid) {
    $(this).prop('disable', true);
    var url = "gstslab_edit";
    var type = "POST";
    var dataType = "";
    var data = {
        "gst_slabs_master_id": gstslabid
    }
    callroute(url, type, dataType, data, function (data) {
        $(this).prop('disable', false);
        var gstslabs_response = JSON.parse(data);

        if (gstslabs_response['Success'] == "True") {

            var gstslabs_data = gstslabs_response['Data'];

            $("#gst_slabs_master_id").val(gstslabs_data['gst_slabs_master_id']);
            $("#selling_price_from").val(gstslabs_data['selling_price_from']);
            $("#selling_price_to").val(gstslabs_data['selling_price_to']);
            $("#percentage").val(gstslabs_data['percentage']);
            $("#gst_note").val(gstslabs_data['note']);
        }
    });
}


$("#deletegstslabs").click(function () {
    // if (confirm("Are You Sure want to delete this GST slabs?")) {

        var ids = [];

        $('input[name="delete_gstslabs[]"]:checked').each(function () {
            ids.push($(this).val());
        });


        if (ids.length > 0) {

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
            var url = "gstslabs_delete";
            var dataType = "";
            var type = "POST";
            callroute(url, type, dataType, data, function (data) {
                var dta = JSON.parse(data);

                if (dta['Success'] == "True") {
                    toastr.success(dta['Message']);
                    $("#gstslabsform").trigger('reset');
                    $("#gst_slabs_master_id").val('');
                    resettable('gst_slabs_data', 'tablegstrecord');
                } else {
                    toastr.error(dta['Message']);
                }
            })
        } else {
            return false;
        }
    // }
    })
        }
    else {
        return false;
    }
});
function delete_separate_gstslabs(obj,gstslabs_id,event)
{
    $(obj).closest('td').find('[type=checkbox]').prop('checked',true);
    event.preventDefault();
    if($(obj).closest('td').find('[type=checkbox]').prop('checked') == true)
    {
        setTimeout(
            function()
            {
                 $('#deletegstslabs')[0].click();
            }, 300);
    }

}

$("#cancelgstslab").click(function () {
    $("#selling_price_from").val('');
    $("#selling_price_to").val('');
    $("#percentage").val('');
    $("#gst_slabs_master_id").val('');
});


$('#checkall').change(function () {
    if ($(this).is(":checked")) {
        $("#gstslabrecord tr").each(function () {
            var id = $(this).attr('id');

            $(this).find('td').each(function () {
                $("#delete_gstslabs" + id).prop('checked', true);
            });

        })
    } else {
        $("#gstslabrecord tr").each(function () {
            var id = $(this).attr('id');
            $(this).find('td').each(function () {
                $("#delete_gstslabs" + id).prop('checked', false);
            });

        })
    }
});


$("#check_empty_primaster").click(function () {
    var url = 'test_check_price_master';
    var data = '';
    var type = "POST";
    var dataType = '';
    callroute(url, type, dataType, data, function (data) {
        var dta = JSON.parse(data);
        if (dta['Success'] == "True") {
            var cnt = dta['cnt'];
            if (cnt != 0) {
                toastr.error("Remove all data from price master and try again");
            } else {
                toastr.success("Now you can transfer inward product details to price master");
                $("#inward_to_price").show();
                $("#check_empty_primaster").hide();
            }
        }
    })
});


$("#inward_to_price").click(function () {
    $(".loaderContainer").show();
    $(this).attr('disabled', 'true');
    var url = 'transfer_to_price_master';
    var data = '';
    var type = "POST";
    var dataType = '';
    callroute(url, type, dataType, data, function (data) {
        $(".loaderContainer").show();
        var dta = JSON.parse(data);
        if (dta['Success'] == "True") {
            toastr.success(dta['Message']);
        } else {
            toastr.error(dta['Message']);
        }
        $("#inward_to_price").hide();
        $("#check_empty_primaster").show();

        $(".loaderContainer").hide();
    })
});

