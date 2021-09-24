$("body").on("click", "#uploadpo", function () {
    $("#uploadpo").attr('disabled', true);

    $(".loaderContainer").show();

    var fileUpload = $("#pofileUpload")[0];

    var ext = fileUpload.value.split('.').pop();

    var validImageTypes = ['xls', 'xlsx'];

    if (validImageTypes.includes(ext)) {

        var file_name = $('#pofileUpload')[0].files[0]['name'];

        var type_of_po = $("#po_with_unique_barcode").val();

        var name_of_file = '';
        var file_length = '';

        if (type_of_po == 0) {
            name_of_file = 'po_template';
            file_length = 11;
        }

        if (type_of_po == 1) {
            name_of_file = 'po_unique_barcode_template';
            file_length = 26;

        }

        if (name_of_file != '' && file_name.substr(0, file_length) == name_of_file) {

            if (typeof (FileReader) != "undefined") {
                var reader = new FileReader();

                if (reader.readAsBinaryString) {
                    reader.onload = function (e) {
                        Process_Po_Excel(e.target.result);
                    };
                    reader.readAsBinaryString(fileUpload.files[0]);
                } else {
                    reader.onload = function (e) {
                        var data = "";
                        var bytes = new Uint8Array(e.target.result);
                        for (var i = 0; i < bytes.byteLength; i++) {
                            data += String.fromCharCode(bytes[i]);
                        }
                        Process_Po_Excel(data);
                    };
                    reader.readAsArrayBuffer(fileUpload.files[0]);
                }
            } else {
                $("#uploadpo").attr('disabled', false);
                $(".loaderContainer").hide();
                alert("This browser does not support HTML5.");
            }
        } else {
            $("#upload").attr('disabled', false);
            $(".loaderContainer").hide();
            alert("Please upload " + name_of_file + " excel file");
            $("#fileUpload").val('');
        }
    } else {
        $("#uploadpo").attr('disabled', false);
        $(".loaderContainer").hide();
        alert("Please upload a valid Excel file.");
    }
});

function Process_Po_Excel(data) {
    var result;
    var workbook = XLSX.read(data,
        {
            type: 'binary'
        });
    var firstSheet = workbook.SheetNames[0];
    var excelRows = XLSX.utils.sheet_to_json(workbook.Sheets[firstSheet], {defval: ''});

    var final_poarr = [];
    $.each(excelRows, function (key, value) {
        var final_data = {};
        $.each(value, function (arrkwy, arrval) {
            if (!arrkwy.match("^__EMPTY")) {
                final_data[arrkwy] = arrval;
            }
        });
        final_poarr.push(final_data);
    });

    var error = 0;

    $.each(final_poarr, function (validate_pkey, validate_pvalue) {

        if (validate_pvalue['Barcode'] == '') {
            error = 1;
            toastr.error("Barcode can not be empty!");
        }
//po_calculation 1 = withcalculation and 2= without calculation
        if (po_calculation == 1) {
            if (validate_pvalue['Cost Rate'] == '') {
                error = 1;
                toastr.error("Cost Rate can not be empty!");
            }

            if (validate_pvalue['Cost Rate'] != "" && !$.isNumeric(validate_pvalue['Cost Rate'])) {
                error = 1;
                toastr.error("Cost Rate must be numeric!");
            }

            var cost_gst_label_validation = 'Cost GST %';

            if (tax_type == 1) {
                cost_gst_label_validation = 'Cost ' + tax_title + ' %';
            }

            if (validate_pvalue[cost_gst_label_validation] == "") {
                error = 1;
                toastr.error("" + cost_gst_label_validation + "" + " can not be empty!");
            }

            if (validate_pvalue[cost_gst_label_validation] != "" && !$.isNumeric(validate_pvalue[cost_gst_label_validation])) {
                error = 1;
                toastr.error("" + cost_gst_label_validation + "" + " must be numeric!");
            }
        }

        if (validate_pvalue['Qty'] == '') {
            error = 1;
            toastr.error("Qty can not be empty");
        }
        if (validate_pvalue['Qty'] != '' && !$.isNumeric(validate_pvalue['Qty'])) {
            error = 1;
            toastr.error("Qty must be numeric!");
        }
//without calculation
        if (po_calculation == 2) {

        }
        if ($("#po_with_unique_barcode").val() == 1) {
//CHECKING MFG DATE,MONTH AND YEAR DIGIT AND PROPER DATE FORMAT VALIDATION
            if (validate_pvalue['Mfg date(DD)'] != '' || validate_pvalue['Mfg month(MM)'] != '' || validate_pvalue['Mfg year(YYYY)'] != '') {
                if (separtate_date_format_validation(validate_pvalue['Mfg date(DD)'], validate_pvalue['Mfg month(MM)'], validate_pvalue['Mfg year(YYYY)'], "mfg") == 0) {
                    error = 1;
                }
            }
//END OF CHECKING MFG DATE FORMAT VALIDATION


//CHECKING EXPIRY DATE VALIDATION
            if (validate_pvalue['Expiry date(DD)'] != '' || validate_pvalue['Expiry month(MM)'] != '' || validate_pvalue['Expiry year(YYYY)'] != '') {
                if (separtate_date_format_validation(validate_pvalue['Expiry date(DD)'], validate_pvalue['Expiry month(MM)'], validate_pvalue['Expiry year(YYYY)'], "expiry") == 0) {
                    error = 1;
                }
            }
//END OF CHECK EXPIRY DATE VALIDATION
        }


        if (error == 1) {
            $("#uploadpo").attr('disabled', false);
            $(".loaderContainer").hide();
            return false;
        }
    });

    if (error == 0) {
        checkpo(final_poarr);
    }
}


function checkpo(poarr) {

    $(".loaderContainer").show();

    var no_matching_product = '';
    $.each(poarr, function (key, value) {
        var url = "po_check";
        var type = "POST";
        var dataType = "";
        var item_type = '1';
        if($("#po_with_unique_barcode").val() == 1)
        {
            item_type = '3';
        }

        var data = {
            barcode: value['Barcode'],
            item_type : item_type
        };
        var ids = '';
        callroute(url, type,dataType, data, function (data) {
            var response = JSON.parse(data);

            if (response['Success'] == "True") {
                if (response['product_id'] != '' && response['product_id'] != undefined && response['product_id'] != 'NULL') {

                }

            } else {
                ids = value['Barcode'];

            }

        })
        if (ids != '') {
            if (no_matching_product == "") {
                no_matching_product = ids;
            } else {
                no_matching_product = no_matching_product + "," + ids;
            }

        }
    });

    if (no_matching_product != undefined && no_matching_product != '')
    {
        var count = no_matching_product.split(',').length;
        var no_product = JSON.stringify(no_matching_product);
        var po_all_data = JSON.stringify(poarr);

        var message = " " + count + " product not found in our database" +
            "<a' id='showmoredata' onclick='show_no_product(this," + no_product + ")';  data-attr='" + po_all_data + "' ><strong style='color: #123f'>Click Here for view Detail</strong></a> ";

        swal({
                title: "info",
                text: message,
                type: "warning",
                html: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes!",
                showCancelButton: true,
                closeOnConfirm: true,
                closeOnCancel: true
            },
            function (isConfirm)
            {
                if (isConfirm)
                {
                    $("#uploadpo").attr('disabled', false);
                    return false;
                    //get_import_product_detail(no_matching_product, poarr);
                }
                else
                {
                    $("#uploadpo").attr('disabled', false);
                    return false;
                }

            });

    } else {
        get_import_product_detail(no_matching_product, poarr);
    }

    $(".loaderContainer").hide();


}


function show_no_product(obj, new_product) {

    var rec = $(obj).data('attr');

//var newproduct  = new_product.split(",");

    var swal_html = '<div class="row">';

    if (rec !== '') {
        $.each(rec, function (key, value) {
            if (new_product.indexOf(value['Barcode']) != -1) {
                swal_html += '<table style="border: 1px solid #000000;margin-right: 25px"><tr><th>' + key + '</th></tr>';
                swal_html += '';

                swal_html += '<tr style="border-bottom: 1px solid #000000"><td style="width: 7px;">' + value['Barcode'] + '</td></tr>';

                swal_html += '</table>';
            }
        })
    }
    swal_html += '</div>';
    swal({
            title: "Detail",
            text: swal_html,
            type: "info",
            html: true,
            showConfirmButton: true,
            showCancelButton: false,
        },
        function (isConfirm) {
            if (isConfirm) {
                get_import_product_detail(new_product, rec);
            }
        });
}


function get_import_product_detail(no_found_arr, po_arr)
{
    $("#upload_po_popup").modal('hide');

    $(".loaderContainer").show();

    if ($("#po_with_unique_barcode").val() == 1) {
        unique_po_data(no_found_arr, po_arr);
        return false;
    }

    $.each(po_arr, function (kk, vv) {
// var split_str = no_found_arr.split(",");

        if (no_found_arr.indexOf(vv['Barcode']) != -1) {

        } else {
            var type = "POST";
            var dataType = "";
            var url = 'po_barcode_detail';
            var data = {
                "barcode": vv['Barcode']
            }

            callroute(url, type,dataType, data, function (data) {
                var product_data = JSON.parse(data, true);

                if (product_data['Success'] == "True") {
                    var product_html = '';
                    var product_detail = product_data['Data'][0];
                    var hsncode = '';
                    var cost_gst_percent = '0';
                    var cost_gst_amount = '0';
                    var cost_rate = '0';
                    var in_stock = '0';

                    if (product_detail['hsn_sac_code'] != null || product_detail['hsn_sac_code'] != undefined) {
                        hsncode = product_detail['hsn_sac_code'];
                    }
                    if (typeof vv['Cost Rate'] !== 'undefined') {
                        cost_rate = vv['Cost Rate'];
                    }

                    var mfg_date = '';
                    var exp_date = '';

                    if ((typeof vv['Mfg date(DD)'] !== 'undefined' && vv['Mfg date(DD)'] != '' && vv['Mfg date(DD)'] != null) || typeof vv['Mfg month(MM)'] !== 'undefined' && (vv['Mfg month(MM)'] != '' && vv['Mfg month(MM)'] != null) || typeof vv['Mfg year(YYYY)'] !== 'undefined' && (vv['Mfg year(YYYY)'] != '' && vv['Mfg year(YYYY)'] != null)) {
                        mfg_date = leadingZero(vv['Mfg date(DD)']) + '-' + leadingZero(vv['Mfg month(MM)']) + '-' + vv['Mfg year(YYYY)'];
                    }


                    if ((typeof vv['Expiry date(DD)'] !== 'undefined' && vv['Expiry date(DD)'] != '' && vv['Expiry date(DD)'] != null) || typeof vv['Expiry month(MM)'] !== 'undefined' && (vv['Expiry month(MM)'] != '' && vv['Expiry month(MM)'] != null) || typeof vv['Expiry year(YYYY)'] !== 'undefined' && (vv['Expiry year(YYYY)'] != '' && vv['Expiry year(YYYY)'] != null)) {
                        exp_date = leadingZero(vv['Expiry date(DD)']) + '-' + leadingZero(vv['Expiry month(MM)']) + '-' + vv['Expiry year(YYYY)'];
                    }


                    if (vv['Cost GST %'] != null || vv['Cost GST %'] != undefined) {
                        cost_gst_percent = vv['Cost GST %'];
                    }

                    if (product_detail['in_stock'] != null || product_detail['in_stock'] != undefined) {
                        in_stock = product_detail['in_stock'];
                    }

                    var uqc_name = '';
                    if (product_detail['uqc_id'] != '' && product_detail['uqc_id'] != null && product_detail['uqc_id'] != 0) {
                        uqc_name = product_detail['uqc']['uqc_shortname'];
                    }

                    var feature_show_val = "";
                    if(po_show_dynamic_feature != '')
                    {
                        var feature = po_show_dynamic_feature.split(',');

                        $.each(feature,function(fea_key,fea_val)
                        {
                            var feature_name = '';
                            if(typeof(product_detail[fea_val]) != "undefined" && product_detail[fea_val] !== null) {

                                feature_name = product_detail[fea_val];
                            }

                            feature_show_val += '<td>' + feature_name + '</td>';
                        })
                    }



                    var rowCount = $('#po_product_detail_record tr').length;
                    rowCount++;

                    var product_id = product_detail['product_id'];
                    var samerow = 0;
//IF PO WITH SAME BARCODE IS 1 THEN SAME PRODUCT REPEAT OTHERWISE INCREMENT OF PRODUCT QUNTITY
                    if ($("#po_with_unique_barcode").val() == 0) {
                        $("#po_product_detail_record tr").each(function () {
                            var row_product_id = $(this).attr('id').split('_')[1];
                            if (row_product_id == product_id) {
                                var qty = $("#qty_" + product_id).html();
                                var product_qty = ((Number(qty)) + (Number(1)));
                                $("#qty_" + product_id).html(product_qty);
                                samerow = 1;

                                var costrate = $("#cost_rate_" + product_id).html();

                                var gst_percent = $("#cost_gst_percent_" + product_id).html();

                                var gstamt = ((Number(costrate)) * (Number(gst_percent)) / (Number(100)));

                                $("#cost_gst_amount_" + product_id).html(gstamt.toFixed(4));

                                var total_cost_without_gst = ((Number(costrate)) * (Number(product_qty)));

                                $("#total_cost_without_gst_" + product_id).html(total_cost_without_gst.toFixed(4));
                                var gst_amount = $("#cost_gst_amount_" + product_id).html();
                                var total_gst = ((Number(gst_amount)) * (Number(product_qty)));
                                $("#total_gst_" + product_id).html(total_gst.toFixed(4));
                                var total_cost_with_gst = (((Number(costrate)) + (Number(gst_amount))) * (Number(product_qty)));
                                $("#total_cost_with_gst_" + product_id).html(total_cost_with_gst.toFixed(4));

                                gettotalqty();
                                return false;
                            }
                        });
                    }

                    if (samerow == 0) {
                        var barcode = '';
                        if (product_detail['supplier_barcode'] != " " && product_detail['supplier_barcode'] != undefined && product_detail['supplier_barcode'] != null) {

                            barcode = product_detail['supplier_barcode'];
                        } else {
                            barcode = product_detail['product_system_barcode'];
                        }

                        if (product_html == '') {
                            product_html += '<tr id="product_' + product_id + '" data-id="' + rowCount + '">' +
                                '<input type="hidden" name="purchase_order_detail_id_' + product_id + '" id="purchase_order_detail_id_' + product_id + '" value="">' +
                                '<td>' + barcode + '</td>' +
                                '<td>' + product_detail['product_name'] + '</td>' +
                                '<td>' + hsncode + '</td>' +
                                '<td class="po_with_batch_no" id="unique_barcode_' + product_id + '"></td>';
                            product_html += feature_show_val;

                            product_html += '<td >' + uqc_name + '</td>' +
                                '<td>' + in_stock + '</td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea with_calculation" contenteditable="true" style="color: black;"  onkeyup="costrate(this);" id="cost_rate_' + product_id + '">' + cost_rate + '</td>' +
                                '<td class="with_calculation" readonly  id="cost_gst_percent_' + product_id + '">' + cost_gst_percent + '</td>' +
                                '<td class="with_calculation"  readonly  id="cost_gst_amount_' + product_id + '"></td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addqty(this);" id="qty_' + product_id + '">' + vv['Qty'] + '</td>' +
                                '<td class="with_calculation" readonly id="total_cost_without_gst_' + product_id + '">0</td>' +
                                '<td class="with_calculation" readonly id="total_gst_' + product_id + '">0</td>' +
                                '<td class="with_calculation" readonly id="total_cost_with_gst_' + product_id + '">0</td>' +
                                '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;"  onclick="return getdatepickerpo(\'mfg_date_' + product_id + '\');" id="mfg_date_' + product_id + '">' + mfg_date + '</td>' +
                                '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;" onclick="return getdatepickerpo(\'expiry_date_' + product_id + '\');" id="expiry_date_' + product_id + '">' + exp_date + '</td>' +
                                '<td  id="poremarksentry_' + product_id + '"><textarea name="remarks_' + product_id + '" id="remarks_' + product_id + '">' + vv['Remarks'] + '</textarea></td>' +
                                '<td onclick="removeporow(' + product_detail['product_id'] + ');"><i class="fa fa-close"></i></td>' +
                                '</tr>';
                        } else {
                            product_html += product_html + '<tr id="product_' + product_id + '" data-id="' + rowCount + '">' +
                                '<input type="hidden" name="purchase_order_detail_id_' + product_id + '" id="purchase_order_detail_id_' + product_id + '" value="">' +
                                '<td>' + barcode + '</td>' +
                                '<td>' + product_detail['product_name'] + '</td>' +
                                '<td>' + hsncode + '</td>' +
                                '<td class="po_with_batch_no" id="unique_barcode_' + product_id + '"></td>';
                            product_html += feature_show_val;

                            product_html += '<td>  ' + uqc_name + '</td>' +
                                '<td>' + in_stock + '</td>' +
                                '<td   onkeypress = "return testCharacter(event);" class="number editablearea with_calculation" contenteditable="true" style="color: black;"  onkeyup="costrate(this);" id="cost_rate_' + product_id + '">' + cost_rate + '</td>' +
                                '<td class="with_calculation"  readonly  id="cost_gst_percent_' + product_id + '">' + cost_gst_percent + '</td>' +
                                '<td class="with_calculation" readonly  id="cost_gst_amount_' + product_id + '"></td>' +
                                '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addqty(this);" id="qty_' + product_id + '">' + vv['Qty'] + '</td>' +
                                '<td class="with_calculation" readonly id="total_cost_without_gst_' + product_id + '">0</td>' +
                                '<td class="with_calculation" readonly id="total_gst_' + product_id + '">0</td>' +
                                '<td class="with_calculation" readonly id="total_cost_with_gst_' + product_id + '">0</td>' +
                                '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;"  onclick="return getdatepickerpo(\'mfg_date_' + product_id + '\');" id="mfg_date_' + product_id + '">' + mfg_date + '</td>' +
                                '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;" onclick="return getdatepickerpo(\'expiry_date_' + product_id + '\');" id="expiry_date_' + product_id + '">' + exp_date + '</td>' +
                                '<td  id="poremarksentry_' + product_id + '"><textarea name="remarks_' + product_id + '" id="remarks_' + product_id + '">' + vv['Remarks'] + '</textarea></td>' +
                                '<td onclick="removeporow(' + product_detail['product_id'] + ');"><i class="fa fa-close"></i></td>' +
                                '</tr>';
                        }
                    }
                }

                $("#productsearch").val('');
                $(".odd").hide();
                $("#po_product_detail_record").prepend(product_html);

                $("#po_product_detail_record").find('tr[data-id=' + rowCount + ']').find("#qty_" + product_id).keyup();


                if ($("#po_with_unique_barcode").val() == 0) {
                    $(".po_with_batch_no").hide();
                } else {
                    $(".po_with_batch_no").show();
                }

                if (po_calculation == 2) {
                    $(".with_calculation").hide();
                } else {
                    $(".with_calculation").show();
                }

                var totalqty = $("#po_product_detail_record tr").length;

                $(".pototalitems").html(totalqty);

            });
        }
    });
    $(".loaderContainer").hide();
}

function unique_po_data(no_found_arr, po_arr) {
    var cnt = 1;

    $.each(po_arr, function (kk, vv) {
        if (no_found_arr.indexOf(vv['Barcode']) != -1) {

        } else {
            var type = "POST";
            var url = 'po_barcode_detail';
            var dataType = '';
            var data = {
                "barcode": vv['Barcode'],
                "item_type": 3
            }
            callroute(url, type,dataType, data, function (data) {
                var product_data = JSON.parse(data, true);
                var qty = vv['Qty'];
                var start_cnt = 1;
                if (product_data['Success'] == "True") {
                    var product_html = '';
                    var product_detail = product_data['Data'][0];
                    var hsncode = '';
                    var cost_gst_percent = '0';
                    var cost_gst_amount = '0';
                    var cost_rate = '0';
                    var in_stock = '0';

                    if (product_detail['hsn_sac_code'] != null || product_detail['hsn_sac_code'] != undefined) {
                        hsncode = product_detail['hsn_sac_code'];
                    }
                    if (typeof vv['Cost Rate'] !== 'undefined') {
                        cost_rate = vv['Cost Rate'];
                    }

                    var mfg_date = '';
                    var exp_date = '';

                    if ((typeof vv['Mfg date(DD)'] !== 'undefined' && vv['Mfg date(DD)'] != '' && vv['Mfg date(DD)'] != null) || typeof vv['Mfg month(MM)'] !== 'undefined' && (vv['Mfg month(MM)'] != '' && vv['Mfg month(MM)'] != null) || typeof vv['Mfg year(YYYY)'] !== 'undefined' && (vv['Mfg year(YYYY)'] != '' && vv['Mfg year(YYYY)'] != null)) {
                        mfg_date = leadingZero(vv['Mfg date(DD)']) + '-' + leadingZero(vv['Mfg month(MM)']) + '-' + vv['Mfg year(YYYY)'];
                    }

                    if ((typeof vv['Expiry date(DD)'] !== 'undefined' && vv['Expiry date(DD)'] != '' && vv['Expiry date(DD)'] != null) || typeof vv['Expiry month(MM)'] !== 'undefined' && (vv['Expiry month(MM)'] != '' && vv['Expiry month(MM)'] != null) || typeof vv['Expiry year(YYYY)'] !== 'undefined' && (vv['Expiry year(YYYY)'] != '' && vv['Expiry year(YYYY)'] != null)) {
                        exp_date = leadingZero(vv['Expiry date(DD)']) + '-' + leadingZero(vv['Expiry month(MM)']) + '-' + vv['Expiry year(YYYY)'];
                    }

                    if (vv['Cost GST %'] != null || vv['Cost GST %'] != undefined) {
                        cost_gst_percent = vv['Cost GST %'];
                    }

                    if (product_detail['in_stock'] != null || product_detail['in_stock'] != undefined) {
                        in_stock = product_detail['in_stock'];
                    }


                    var uqc_name = '';
                    if (product_detail['uqc_id'] != '' && product_detail['uqc_id'] != null && product_detail['uqc_id'] != 0) {
                        uqc_name = product_detail['uqc']['uqc_shortname'];
                    }

                    var feature_show_val = "";
                    if(po_show_dynamic_feature != '')
                    {
                        var feature = po_show_dynamic_feature.split(',');

                        $.each(feature,function(fea_key,fea_val)
                        {
                            var feature_name = '';
                            if(typeof(product_detail[fea_val]) != "undefined" && product_detail[fea_val] !== null) {

                                feature_name = product_detail[fea_val];
                            }

                            feature_show_val += '<td>' + feature_name + '</td>';
                        })
                    }


                // var rowCount = $('#po_product_detail_record tr').length;
                // rowCount++;

                    var product_id = product_detail['product_id'];

                    var barcode = '';
                    if (product_detail['supplier_barcode'] != " " && product_detail['supplier_barcode'] != undefined && product_detail['supplier_barcode'] != null) {

                        barcode = product_detail['supplier_barcode'];
                    } else {
                        barcode = product_detail['product_system_barcode'];
                    }

                    for (var i = start_cnt; i <= qty; i++) {
                        product_html = '<tr id="product_' + product_id + '" data-id="' + cnt + '">' +
                            '<input type="hidden" name="purchase_order_detail_id_' + product_id + '" id="purchase_order_detail_id_' + product_id + '" value="">' +
                            '<td>' + barcode + '</td>' +
                            '<td>' + product_detail['product_name'] + '</td>' +
                            '<td>' + hsncode + '</td>' +
                            '<td class="po_with_batch_no" id="unique_barcode_' + product_id + '"></td>';

                        product_html += feature_show_val;

                        product_html += '<td > ' + uqc_name + '</td>' +
                            '<td>' + in_stock + '</td>' +
                            '<td onkeypress = "return testCharacter(event);" class="number editablearea with_calculation" contenteditable="true" style="color: black;"  onkeyup="costrate(this);" id="cost_rate_' + product_id + '">' + cost_rate + '</td>' +
                            '<td class="with_calculation" readonly  id="cost_gst_percent_' + product_id + '">' + cost_gst_percent + '</td>' +
                            '<td class="with_calculation"  readonly  id="cost_gst_amount_' + product_id + '"></td>' +
                            '<td onkeypress = "return testCharacter(event);" class="number editablearea" contenteditable="true" style="color: black;" onkeyup="addqty(this);" id="qty_' + product_id + '">'+product_detail['default_qty']+'</td>' +
                            '<td class="with_calculation" readonly id="total_cost_without_gst_' + product_id + '">0</td>' +
                            '<td class="with_calculation" readonly id="total_gst_' + product_id + '">0</td>' +
                            '<td class="with_calculation" readonly id="total_cost_with_gst_' + product_id + '">0</td>' +
                            '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;"  onclick="return getdatepickerpo(\'mfg_date_' + product_id + '\');" id="mfg_date_' + product_id + '">' + mfg_date + '</td>' +
                            '<td contenteditable="true" class="editablearea po_with_batch_no" style="color: black;" onclick="return getdatepickerpo(\'expiry_date_' + product_id + '\');" id="expiry_date_' + product_id + '">' + exp_date + '</td>' +
                            '<td  id="poremarksentry_' + product_id + '"><textarea name="remarks_' + product_id + '" id="remarks_' + product_id + '">' + vv['Remarks'] + '</textarea></td>' +
                            '<td onclick="removeporow(' + product_detail['product_id'] + ');"><i class="fa fa-close"></i></td>' +
                            '</tr>';

                        $("#productsearch").val('');
                        $(".odd").hide();
                        $("#po_product_detail_record").prepend(product_html);

                        $("#po_product_detail_record").find('tr[data-id=' + cnt + ']').find("#qty_" + product_id).keyup();


                        if ($("#po_with_unique_barcode").val() == 0) {
                            $(".po_with_batch_no").hide();
                        } else {
                            $(".po_with_batch_no").show();
                        }

                        if (po_calculation == 2) {
                            $(".with_calculation").hide();
                        } else {
                            $(".with_calculation").show();
                        }

                        var totalqty = $("#po_product_detail_record tr").length;

                        $(".pototalitems").html(totalqty);

                        start_cnt++;
                        cnt++;
                    }
                }
            });
        }
    });
    $(".loaderContainer").hide();
}

