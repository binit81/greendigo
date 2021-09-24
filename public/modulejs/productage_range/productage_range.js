$("#addagerange").click(function (e) {
    if (validate_agerange('productagerangeform')) {
        $("#addagerange").prop('disabled', true);

        var data = {
            "formdata": $("#productagerangeform").serialize(),
        };
        var url = "agerange_create";
        var dataType = "";
        var type = "POST";
        callroute(url, type, dataType, data, function (data) {
            var dta = JSON.parse(data);

            if (dta['Success'] == "True") {
                toastr.success(dta['Message']);
                $("#productagerangeform").trigger('reset');
                $("#productage_range_id").val('');
                resettable('age_range_data', 'tableagerangerecord');
            } else {
                toastr.error(dta['Message']);
            }
            $("#addagerange").prop('disabled', false);
        })

    }
    //$(this).prop('disabled', false);
    e.preventDefault();
});

function validate_agerange(frmid) {
    var error = 0;

    if ($("#range_from").val() == '') {
        error = 1;
        toastr.error("Enter From Range!");
        return false;
    }
    if ($("#range_to").val() == '') {
        error = 1;
        toastr.error("Enter To Range!");
        return false;
    }

   
    var range_from = $("#range_from").val();
    var range_to = $("#range_to").val();


    if (Number(range_from) > Number(range_to)) {
        error = 1;
        toastr.error("To Range can not be less than from Range!");
        return false;
    }

    if (error == 1) {
        return false;
    } else {
        return true;
    }
}


//edit product

function editproductage_range(agerangeid) {
    $(this).prop('disable', true);
    var url = "agerange_edit";
    var type = "POST";
    var dataType = "";
    var data = {
        "productage_range_id": agerangeid
    }
    callroute(url, type, dataType, data, function (data) {
        $(this).prop('disable', false);
        var agerange_response = JSON.parse(data);

        if (agerange_response['Success'] == "True") {

            var agerange_data = agerange_response['Data'];

            $("#productage_range_id").val(agerange_data['productage_range_id']);
            $("#range_from").val(agerange_data['range_from']);
            $("#range_to").val(agerange_data['range_to']);
            
        }
    });
}


$("#deleteagerange").click(function () {
    if (confirm("Are You Sure want to delete this Product Age Range?")) {

        var ids = [];

        $('input[name="delete_productage_range[]"]:checked').each(function () {
            ids.push($(this).val());
        });


        if (ids.length > 0) {
            var data = {
                "deleted_id": ids
            };
            var url = "agerange_delete";
            var dataType = "";
            var type = "POST";
            callroute(url, type, dataType, data, function (data) {
                var dta = JSON.parse(data);

                if (dta['Success'] == "True") {
                    toastr.success(dta['Message']);
                    $("#productagerangeform").trigger('reset');
                     $("#productage_range_id").val('');
                resettable('age_range_data', 'tableagerangerecord');
                } else {
                    toastr.error(dta['Message']);
                }
            })
        } else {
            return false;
        }
    } else {
        return false;
    }
});


$("#cancelagerange").click(function () {
    $("#range_from").val('');
    $("#range_to").val('');
    $("#productage_range_id").val('');
});


$('#checkall').change(function () {
    if ($(this).is(":checked")) {
        $("#tableagerangerecord tr").each(function () {
            var id = $(this).attr('id');

            $(this).find('td').each(function () {
                $("#delete_productage_range" + id).prop('checked', true);
            });

        })
    } else {
        $("#tableagerangerecord tr").each(function () {
            var id = $(this).attr('id');
            $(this).find('td').each(function () {
                $("#delete_productage_range" + id).prop('checked', false);
            });

        })
    }
});


