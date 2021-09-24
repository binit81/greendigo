$("#return_date").datepicker({
    format: 'dd-mm-yyyy',
    orientation: "bottom",
    autoclose: true

}).on('keypress paste', function (e) {
    e.preventDefault();
    return false;
});
$("#manualbill_no").typeahead({
   source: function(request, process) {
       var url = "return_issueno_search";
       var type = "post";
       var dataType = "json";
       var data = {
           search_val: $("#manualbill_no").val(),
           term: request.term
       };

       callroute(url, type, dataType, data, function (data) {
           $("#manualbill_no").val()
           objects = [];
           map = {};
           if ($("#manualbill_no").val() != '') {
               $.each(data, function (i, object) {
                   map[object.label] = object;
                   objects.push(object.label);
               });
               
               process(objects);              

           } else {
               $(".dropdown-menu").hide();
           }
       });

    },

    minLength: 1,
   
     afterSelect: function (item) {


    
        var value = item;
        var inward_stock_id = map[item]['inward_stock_id'];
       
        //$('#productsearch').attr('readonly', true); 
        $('#manualbill_no').val(''); 
        //$('#manualbill_no').css("color", "black");
        //$('#resetfilter').attr('disabled',true);
        storereturnissuenodetail(inward_stock_id);

    }

});

function storereturnissuenodetail(inward_stock_id)
{

   jQuery.noConflict();
   var columnid   =   columnid;
   var type = "POST";
   var dataType = "";
   var url = 'storereturnissueno_detail';
   var data = {
       
       "inward_stock_id":inward_stock_id
   }
   callroute(url,type,dataType,data,function(data)
   {

        var product_data = JSON.parse(data,true);


        if(product_data['Success'] == "True")
        {

            var product_html = '';
            var inwardvalue  = product_data['Data'][0];

            var skucode = '';
            var pricehtml = '';
            var pcount    = 0;
            var sellingprice  = 0;
            var stock = 0;
            var gst_per = 0;
            var sr  = 1;
            var costprice = 0;
            var modifiedofferprice = 0;

           
           $('.loaderContainer').hide();


           $.each(inwardvalue['inward_product_detail'],function (billkey,billvalue)
           {
                var product_id      = billvalue['inward_product_detail_id'];
                var sales_type      =   $('#sales_type').val();
          

                      var samerow = 0;
                      $("#sproduct_detail_record tr").each(function()
                      {
                         var row_product_id = $(this).attr('id').split('_')[1];
                         if(row_product_id == product_id)
                         {
                             var qty = $("#qty_"+product_id).val();
                             var product_qty = ((Number(qty)) + (Number(1)));
                             $("#qty_"+product_id).val(product_qty);
                             samerow = 1;
                              calqty($('#qty_'+product_id));
                             return false;
                         }
                      });

                      if(samerow == 0)
                      {
                          var srrno  = 0;
                          $('.totqty').each(function(e){
                              var ssrno  = 0;
                              if($(this).val()!='')
                              {
                                  srrno++;
                              }
                          });
                          sr  =  sr + srrno;

                          if(Number(sr)==1)
                          {
                            $('.plural').html('Item');
                          }
                          else if(Number(sr)>1)
                          {
                            $('.plural').html('Items');
                          }

                          $('.titems').html(sr);
                           
                               
                          var product_html = '';   
                          var pcount    = 0;
                          var sellingprice  = 0;
                          var stock = 0;
                          var costprice = 0;
                                

                             

                              var pricehtml = '';  
                              var consignqty = '';
                                  
                               

                                          

                                          if(billvalue['product']['supplier_barcode']!='' && billvalue['product']['supplier_barcode']!=null)
                                          {
                                            var barcode     =     billvalue['product']['supplier_barcode'];
                                          }
                                          else
                                          {
                                            var barcode     =     billvalue['product']['product_system_barcode'];
                                          }

                                          
                                          if(billvalue['product']['uqc']!=null)
                                          {
                                              uqc_name   = billvalue['product']['uqc']['uqc_name'];
                                          }
                                          else
                                          {
                                              uqc_name   = '';
                                          }

                                          var batch_html = '';
                                          if(Number(bill_type)==3)
                                          {
                                              batch_html = '<td id="batchno_'+product_id+'">'+billvalue['batch_no']+'</td>';
                                          }

                                          var feature_show_val = "";
                                          if(bill_show_dynamic_feature != '')
                                          {
                                              var feature = bill_show_dynamic_feature.split(',');

                                              $.each(feature,function(fea_key,fea_val)
                                              {
                                                  var feature_name = '';                               

                                                  if(typeof(billvalue['product'][fea_val]) != "undefined" && billvalue['product'][fea_val] !== null) {

                                                      feature_name = billvalue['product'][fea_val];
                                                      //console.log(feature_name);
                                                  }

                                                  feature_show_val += '<td>' + feature_name + '</td>';
                                              })
                                          }

                                        
                                  
                             product_html += '<tr id="product_' + product_id + '">' +
                             '<td class="pt-15 pb-15" id="product_name_'+product_id+'" name="product_name[]"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);"><span class="informative">'+billvalue['product']['product_name']+'</span></a></td>'+ 
                                      '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+barcode+'</a></td>';
                                      product_html += feature_show_val;
                                      product_html += batch_html;
                                      product_html += '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+uqc_name+'</a></td>'+
                                      '<td id="roomnoval_'+product_id+'" style="display:none;">'+
                                      '<input value="'+billvalue['product']['product_system_barcode']+'" type="hidden" id="barcodesel_'+product_id+'" name="barcode_sel[]">'+
                                      '<input value="" type="hidden" id="storereturn_product_id_'+product_id+'" name="storereturn_product_id[]" class="" >'+
                                      '<input value="'+billvalue['inward_product_detail_id']+'" type="hidden" id="inward_product_detail_id_'+product_id+'" name="inward_product_detail_id[]" class="" >'+
                                      '<input value="'+billvalue['product_id']+'" type="hidden" id="productid_'+product_id+'" name="productid[]" class="allbarcode" >'+
                                      '<input value="" type="hidden" id="price_master_id_'+product_id+'" name="price_master_id[]" class="allbarcode" >'+
                                      '</td>'+
                                      
                                      '<td id="offerprice_'+product_id+'" class="centerAlign">'+billvalue['offer_price']+'</td>'+
                                      '<td id="sellprice_'+product_id+'" class="centerAlign">'+billvalue['sell_price']+'</td>'+
                                      '<td id="inqty_'+product_id+'" class="centerAlign" style="font-weight:bold;">'+billvalue['pending_return_qty']+'</td>'+
                                      '<td id="stockqty_'+product_id+'" class="centerAlign" style="font-weight:bold;display:none;">'+billvalue['pending_return_qty']+'</td>'+
                                      '<td id="retqty_'+product_id+'" style="font-weight:bold;text-align:right !important;" class="billing_calculation_case">'+'<input value="1" class="floating-input tarifform-control number totqty" type="text" id="qty_'+product_id+'" name="qty[]" onkeyup="return calqty(this);">'+'<input value="" class="floating-input tarifform-control number" type="hidden" id="oldqty_'+product_id+'" name="oldqty[]">'+'</td>'+
                                      '<td onclick="removerow(' + product_id + ');" class="rightAlign"><i class="fa fa-close"></i></td>' +
                                      '</tr>';

                                      $("#sproduct_detail_record").prepend(product_html);
                                                          
                              }
                   });

            }
            else
            {
                $('.loaderContainer').hide();
                toastr.error(product_data['Message']);
                return false;
            }

        // $("#sproduct_detail_record").prepend(product_html);

        
           
        if(Number(bill_calculation)==1)
         {
            $('.billing_calculation_case').show();
         }
         else
         {
            $('.billing_calculation_case').hide();
         }
        totalcalculation();
     
      
   });
}

$('#productsearch').typeahead({

               source: function(request, process) {
               var url = "storereturnproduct_search";
               var type = "post";
               var dataType = "json";

       
                  var data = {
                   search_val: $("#productsearch").val(),
                   term: request.term
                  };
               

               callroute(url, type, dataType, data, function (data) {
                   $("#productsearch").val()

                        objects = [];

                        map = {};

                        if($("#productsearch").val()!='')
                          {

                             $.each(data, function(i, object)
                            {
                                 if(Array.isArray(object))
                                  {

                                      $.each(object, function(j, oobject)
                                      {
                                        map[oobject.label] = oobject;
                                        objects.push(oobject.label);

                                      });
                                  }
                                 else
                                 {
                                      map[object.label] = object;
                                      objects.push(object.label);
                                 }
                            });
                            process(objects);
                           


                          }
                          else
                          {
                            $(".dropdown-menu").hide();
                          }


             });
            },

            //minLength: 1,
            autoselect:true,
           // typeahead-select-on-exact="true"
             afterSelect: function (item) {
             $('.loaderContainer').show();
                var value = item;

                if(map[item] == undefined)
                {
                    $('.loaderContainer').hide();
                    toastr.error("Wrong Product Scanned Please Scan the same Product again !");
                    $("#productsearch").val('');

                }
                else
                {

                    
                    var inward_product_detail_id = map[item]['inward_product_detail_id'];

                    storereturnproductdetail(inward_product_detail_id);
                    $(".dropdown-menu").hide();
                    $("#productsearch").val('');
                }

            }
      

});

function storereturnproductdetail(inward_product_detail_id)
{

   jQuery.noConflict();
   var columnid   =   columnid;
   var type = "POST";
   var dataType = "";
   var url = 'storereturnproduct_detail';
   var data = {
       
       "inward_product_detail_id":inward_product_detail_id
   }
   callroute(url,type,dataType,data,function(data)
   {

        var product_data = JSON.parse(data,true);


        if(product_data['Success'] == "True")
        {

            var product_html = '';
            var billvalue  = product_data['Data'][0];

            var skucode = '';
            var pricehtml = '';
            var pcount    = 0;
            var sellingprice  = 0;
            var stock = 0;
            var gst_per = 0;
            var sr  = 1;
            var costprice = 0;
            var modifiedofferprice = 0;

            console.log(billvalue);
         
                $('.loaderContainer').hide();
                var product_id      = billvalue['inward_product_detail_id'];
                var sales_type      =   $('#sales_type').val();
          

            var samerow = 0;
            $("#sproduct_detail_record tr").each(function()
            {
               var row_product_id = $(this).attr('id').split('_')[1];
               if(row_product_id == product_id)
               {
                   var qty = $("#qty_"+product_id).val();
                   var product_qty = ((Number(qty)) + (Number(1)));
                   $("#qty_"+product_id).val(product_qty);
                   samerow = 1;
                    calqty($('#qty_'+product_id));
                   return false;
               }
            });

            if(samerow == 0)
            {
                var srrno  = 0;
                $('.totqty').each(function(e){
                    var ssrno  = 0;
                    if($(this).val()!='')
                    {
                        srrno++;
                    }
                });
                sr  =  sr + srrno;

                if(Number(sr)==1)
                {
                  $('.plural').html('Item');
                }
                else if(Number(sr)>1)
                {
                  $('.plural').html('Items');
                }

                $('.titems').html(sr);
                 
                     
                var product_html = '';   
                var pcount    = 0;
                var sellingprice  = 0;
                var stock = 0;
                var costprice = 0;
                      

                   

                    var pricehtml = '';  
                    var consignqty = '';
                        
                     

                                

                                if(billvalue['product']['supplier_barcode']!='' && billvalue['product']['supplier_barcode']!=null)
                                {
                                  var barcode     =     billvalue['product']['supplier_barcode'];
                                }
                                else
                                {
                                  var barcode     =     billvalue['product']['product_system_barcode'];
                                }

                                
                                if(billvalue['product']['uqc']!=null)
                                {
                                    uqc_name   = billvalue['product']['uqc']['uqc_name'];
                                }
                                else
                                {
                                    uqc_name   = '';
                                }

                                var batch_html = '';
                                if(Number(bill_type)==3)
                                {
                                    batch_html = '<td id="batchno_'+product_id+'">'+billvalue['batch_no']+'</td>';
                                }

                                var feature_show_val = "";
                                if(bill_show_dynamic_feature != '')
                                {
                                    var feature = bill_show_dynamic_feature.split(',');

                                    $.each(feature,function(fea_key,fea_val)
                                    {
                                        var feature_name = '';                               

                                        if(typeof(billvalue['product'][fea_val]) != "undefined" && billvalue['product'][fea_val] !== null) {

                                            feature_name = billvalue['product'][fea_val];
                                            //console.log(feature_name);
                                        }

                                        feature_show_val += '<td>' + feature_name + '</td>';
                                    })
                                }

                   
                        
                   product_html += '<tr id="product_' + product_id + '">' +
                   '<td class="pt-15 pb-15" id="product_name_'+product_id+'" name="product_name[]"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);"><span class="informative">'+billvalue['product']['product_name']+'</span></a></td>'+ 
                            '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+barcode+'</a></td>';
                            product_html += feature_show_val;
                            product_html += batch_html;
                            product_html += '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+uqc_name+'</a></td>'+
                            '<td id="roomnoval_'+product_id+'" style="display:none;">'+
                            '<input value="'+billvalue['product']['product_system_barcode']+'" type="hidden" id="barcodesel_'+product_id+'" name="barcode_sel[]">'+
                            '<input value="'+billvalue['storereturn_product_id']+'" type="hidden" id="storereturn_product_id_'+product_id+'" name="storereturn_product_id[]" class="" >'+
                            '<input value="'+billvalue['inward_product_detail_id']+'" type="hidden" id="inward_product_detail_id_'+product_id+'" name="inward_product_detail_id[]" class="" >'+
                            '<input value="'+billvalue['product_id']+'" type="hidden" id="productid_'+product_id+'" name="productid[]" class="allbarcode" >'+
                            '<input value="" type="hidden" id="price_master_id_'+product_id+'" name="price_master_id[]" >'+
                            '</td>'+
                            
                            '<td id="offerprice_'+product_id+'" class="centerAlign">'+billvalue['offer_price']+'</td>'+
                            '<td id="sellprice_'+product_id+'" class="centerAlign">'+billvalue['sell_price']+'</td>'+
                            '<td id="inqty_'+product_id+'" class="centerAlign" style="font-weight:bold;">'+billvalue['pending_return_qty']+'</td>'+
                            '<td id="stockqty_'+product_id+'" class="centerAlign" style="font-weight:bold;display:none;">'+billvalue['pending_return_qty']+'</td>'+
                            '<td id="retqty_'+product_id+'" style="font-weight:bold;text-align:right !important;" class="billing_calculation_case">'+'<input value="1" class="floating-input tarifform-control number totqty" type="text" id="qty_'+product_id+'" name="qty[]" onkeyup="return calqty(this);">'+'<input value="" class="floating-input tarifform-control number" type="hidden" id="oldqty_'+product_id+'" name="oldqty[]">'+'</td>'+
                            '<td onclick="removerow(' + product_id + ');" class="rightAlign"><i class="fa fa-close"></i></td>' +
                            '</tr>';
                                                
                    }
                   

            }
            else
            {
                $('.loaderContainer').hide();
                toastr.error(product_data['Message']);
                return false;
            }

        $("#sproduct_detail_record").prepend(product_html);

        
           
        if(Number(bill_calculation)==1)
         {
            $('.billing_calculation_case').show();
         }
         else
         {
            $('.billing_calculation_case').hide();
         }
        totalcalculation();
     
      
   });
}

function removerow(productid)
{
    $("#product_"+productid).remove();
    totalcalculation();
    var srrno  = 0;
    $('.totqty').each(function(e){
        var ssrno  = 0;
        if($(this).val()!='')
        {
            srrno++;
        }
    });

    if(Number(srrno)==1)
    {
      $('.plural').html('Item');
    }
    else if(Number(srrno)>1)
    {
      $('.plural').html('Items');
    }
    
    $('.titems').html(srrno);
}
function editremoverow(productid)
{
    $("#qty_"+productid).val(0);

   calqty($('#qty_'+productid));

    
}
function calqty(obj)
{
    if(obj.value != '' && obj.value != undefined)
    {
        obj.value = obj.value.replace(/[^\d.]/g, '');

    }
    var id                        =     $(obj).attr('id');
    var product_id                =     $(obj).attr('id').split('qty_')[1];
    var stock                     =     $('#inqty_'+product_id).html();
    var actualstock               =     $('#stockqty_'+product_id).html();
    var qty                       =     $('#qty_'+product_id).val();
    var oldqty                    =     $('#oldqty_'+product_id).val();

    var totalstock   =   Number(actualstock);
    var restqty      =   0;

    if(Number(qty)>Number(totalstock))
    {
        toastr.error("Entered Qty is greater than Stock");
        $('#qty_'+product_id).val(totalstock);
        restqty   =   Number(actualstock) - Number(totalstock);
        $('#inqty_'+product_id).html(Number(restqty));
        
                  totalcalculation();
            

    }
    else
    {
         restqty   =   Number(actualstock) - Number(qty);
        $('#inqty_'+product_id).html(Number(restqty));
         totalcalculation();
    }
}
function totalcalculation()
{
    
    var totalqty = 0;


    $('.totqty').each(function (index,e){
      if($(this).val()!="")
      totalqty   +=   parseFloat($(this).val());
      
    
    });
    $('#overallqty').val(totalqty);  
}

$("#addbilling").click(function (e) {

     $(this).prop('disabled', true);

  if(validate_billing('billingform'))
  {
      $("#addbilling").prop('disabled', true);



      var array = [];



      $('#sproduct_detail_record tr').has('td').each(function()
      {
          var arrayItem = {};
          $('td', $(this)).each(function(index, item)
          {
              var inputname = $(item).attr('id');

                if(inputname != undefined && inputname != '')
                {
                    var wihoutidname = inputname.split('_');
                    var nameforarray = wihoutidname[0];

                   

                        if(nameforarray == 'roomnoval')
                        {
                            arrayItem['storereturn_product_id'] =$(this).find("#storereturn_product_id_"+wihoutidname[1]).val();
                            arrayItem['product_id'] =$(this).find("#productid_"+wihoutidname[1]).val();
                            arrayItem['inward_product_detail_id'] =$(this).find("#inward_product_detail_id_"+wihoutidname[1]).val();
                            arrayItem['barcodesel'] =$(this).find("#barcodesel_"+wihoutidname[1]).val();
                            arrayItem['price_master_id'] =$(this).find("#price_master_id_"+wihoutidname[1]).val();
                        }
                        else if(nameforarray == 'retqty')
                        {
                            arrayItem['qty'] =$(this).find("#qty_"+wihoutidname[1]).val();
                            arrayItem['oldqty'] =$(this).find("#oldqty_"+wihoutidname[1]).val();
                            

                        }                        
                       
                        else
                        {
                            arrayItem[nameforarray] = $(item).html();
                        }


                }

          });
          array.push(arrayItem);
      });

      var arraydetail = [];
      arraydetail.push(array);


      var customerdetail = {};
      var paymentdetail = {};

      customerdetail['store_return_id'] = $("#store_return_id").val();
      customerdetail['return_date'] = $("#return_date").val();
      customerdetail['overallqty'] = $("#overallqty").val();
      customerdetail['official_note'] = $("#official_note").val();

      


    
      arraydetail.push(customerdetail);

 
       console.log(arraydetail);
      //return false;
   
      var data = arraydetail;

      var  url = "storereturn_create";
      var type = "POST";
      var dataType = "";
      callroute(url,type,dataType,data,function (data)
      {
          $("#addbilling").prop('disabled', true);
          var dta = JSON.parse(data);

          if(dta['Success'] == "True")
          {
            
               toastr.success(dta['Message']);
               window.location = dta['url'];
              $("#billingform").trigger('reset');
              $("#sproduct_detail_record").empty('');
              localStorage.removeItem('edit_storereturn_record');
              
          }
          else
          {
            $("#addbilling").prop('disabled', true);
               toastr.error(dta['Message']);
               

          }
      })

  }
   else
    {
        $("#addbilling").prop('disabled', false);
        return false;
    }
});

function validate_billing(frmid)
{
    var error = 0;

    
          if($("#overallqty").val() ==0)
            {
                error = 1;
                toastr.error("Enter product Details to return");
                return false;
            }
 
    

    if(error == 1)
    {
        return false;
    }
    else
    {
        $('#addbilling').prop('disabled', true);
        $('#addbillingprint').prop('disabled', true);
        
        return true;
    }
}


function edit_storerreturnbill(store_return_id)
{


    var  url = "edit_storerreturnbill";
    var type = "POST";
    var dataType = "";
    var data = {
        'store_return_id' : store_return_id,
    };

    callroute(url,type,dataType,data,function (data) {
        var dta = JSON.parse(data);
        if (dta['Success'] == "True")
        {
            var url = '';
            if(dta['url'] != '' && dta['url'] != 'undefined')
            {
                 url = dta['url'];
            }
           localStorage.setItem('edit_storereturn_record',JSON.stringify(dta['Data']));

            window.location.href = url;



        }
        
    });
}

$(document).ready(function () {

if(localStorage.getItem('edit_storereturn_record'))
{


   
        

        //get a value from local storage
       var edit_data  = localStorage.getItem('edit_storereturn_record');
     //console.log(edit_data);

       if(edit_data != '' && edit_data != undefined && edit_data != null)
       {
          $('.loaderContainer').show();
          

          $('#sproduct_detail_record').html('');
           var edit_billdata = JSON.parse(edit_data);  

           
           $("#store_return_id").val(edit_billdata['store_return_id']);
           $("#official_note").val(edit_billdata['official_note']); 
           $("#overallqty").val(edit_billdata['total_qty']);    

           //fillup product detail row
           if(edit_billdata['storereturn_product'] != 'undefined' && edit_billdata['storereturn_product'] != '')
           {

                  
                var pcount    = 0;
                var sellingprice  = 0;
                var stock = 0;
                var costprice = 0;
                      
           $.each(edit_billdata['storereturn_product'],function (billkey,billvalue)
           {
              var product_html = '';
                                var product_id    =    billvalue['inward_product_detail_id'];

                                if(billvalue['product']['supplier_barcode']!='' && billvalue['product']['supplier_barcode']!=null)
                                {
                                  var barcode     =     billvalue['product']['supplier_barcode'];
                                }
                                else
                                {
                                  var barcode     =     billvalue['product']['product_system_barcode'];
                                }

                               

                                if(billvalue['product']['uqc']!=null)
                                {
                                    uqc_name   = billvalue['product']['uqc']['uqc_name'];
                                }
                                else
                                {
                                    uqc_name   = '';
                                }

                                var feature_show_val = "";
                                if(bill_show_dynamic_feature != '')
                                {
                                    var feature = bill_show_dynamic_feature.split(',');

                                    $.each(feature,function(fea_key,fea_val)
                                    {
                                        var feature_name = '';                               

                                        if(typeof(billvalue['product'][fea_val]) != "undefined" && billvalue['product'][fea_val] !== null) {

                                            feature_name = billvalue['product'][fea_val];
                                            //console.log(feature_name);
                                        }

                                        feature_show_val += '<td>' + feature_name + '</td>';
                                    })
                                }

                                var batch_html = '';
                                if(Number(bill_type)==3)
                                {
                                    batch_html = '<td id="batchno_'+product_id+'">'+billvalue['batch_no']+'</td>';
                                }
                               var stock   =   Number(billvalue['inward_product_detail']['pending_return_qty']) + Number(billvalue['qty']);

                   product_html += '<tr id="product_' + product_id + '">' +
                             '<td class="pt-15 pb-15" id="product_name_'+product_id+'" name="product_name[]"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);"><span class="informative">'+billvalue['product']['product_name']+'</span></a></td>'+ 
                                      '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+barcode+'</a></td>';
                                      product_html += feature_show_val;
                                      product_html += batch_html;
                                      product_html += '<td class="leftAlign"><a id="popupid_'+billvalue['product_id']+'" onclick="return productdetailpopup(this);">'+uqc_name+'</a></td>'+
                                      '<td id="roomnoval_'+product_id+'" style="display:none;">'+
                                      '<input value="'+billvalue['product']['product_system_barcode']+'" type="hidden" id="barcodesel_'+product_id+'" name="barcode_sel[]">'+
                                      '<input value="'+billvalue['storereturn_product_id']+'" type="hidden" id="storereturn_product_id_'+product_id+'" name="storereturn_product_id[]" class="" >'+
                                      '<input value="'+billvalue['inward_product_detail_id']+'" type="hidden" id="inward_product_detail_id_'+product_id+'" name="inward_product_detail_id[]" class="" >'+
                                      '<input value="'+billvalue['product_id']+'" type="hidden" id="productid_'+product_id+'" name="productid[]" class="allbarcode" >'+
                                      '<input value="'+billvalue['price_master_id']+'" type="hidden" id="price_master_id_'+product_id+'" name="price_master_id[]" class="allbarcode" >'+
                                      '</td>'+
                                      
                                      '<td id="offerprice_'+product_id+'" class="centerAlign">'+billvalue['inward_product_detail']['offer_price']+'</td>'+
                                      '<td id="sellprice_'+product_id+'" class="centerAlign">'+billvalue['inward_product_detail']['sell_price']+'</td>'+
                                      '<td id="inqty_'+product_id+'" class="centerAlign" style="font-weight:bold;">'+billvalue['inward_product_detail']['pending_return_qty']+'</td>'+
                                      '<td id="stockqty_'+product_id+'" class="centerAlign" style="font-weight:bold;display:none;">'+stock+'</td>'+
                                      '<td id="retqty_'+product_id+'" style="font-weight:bold;text-align:right !important;" class="billing_calculation_case">'+'<input value="'+billvalue['qty']+'" class="floating-input tarifform-control number totqty" type="text" id="qty_'+product_id+'" name="qty[]" onkeyup="return calqty(this);">'+'<input value="'+billvalue['qty']+'" class="floating-input tarifform-control number" type="hidden" id="oldqty_'+product_id+'" name="oldqty[]">'+'</td>'+
                                      '<td onclick="editremoverow(' + product_id + ');" class="rightAlign"><i class="fa fa-close"></i></td>' +
                                      '</tr>';

                                      $("#sproduct_detail_record").prepend(product_html);
                   

            });
                
              $('.loaderContainer').hide();
               
               $(".odd").hide();
              // $("#sproduct_detail_record").append(product_html);
               if(Number(bill_calculation)==1)
                 {
                    $('.billing_calculation_case').show();
                 }
                 else
                 {
                    $('.billing_calculation_case').hide();
                 }
                  var srrno  = 0;
                  $('.totqty').each(function(e){
                      var ssrno  = 0;
                      if($(this).val()!='')
                      {
                          srrno++;
                      }
                  });
                 
                  $('.titems').html(srrno);  

       }
           //end of fillup product detail row
           totalcalculation();
           $('.loaderContainer').hide();

       }



    
    }
});