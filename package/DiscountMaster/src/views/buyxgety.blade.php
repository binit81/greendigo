<div class="row">
<div class="col-md-4 pl-20">
<section class="hk-sec-wrapper">
        <div id="">
            <div class="hk-row">
                <div class="col-md-4 pb-10">
                    <div class="form-group">
                       <label class="leftAlign">Start Date</label>
                       <input type="text" class="form-control invoiceNo" name="start_date" id="start_date" value="" placeholder="Choose Date" style="cursor:pointer;">
                    </div>
                </div>
                <div class="col-md-4 pb-10">
                    <div class="form-group">
                       <label class="leftAlign">End Date</label>
                       <input type="text" class="form-control invoiceNo" name="end_date" id="end_date" value="" placeholder="Choose Date" style="cursor:pointer;">
                    </div>
                </div>
                <div class="col-md-4 pb-10 mt-10">
                    <div class="form-group">
                      <button type="button" class="btn btn-info savenewBtn btn-block"  id="saveFlatDiscount"><i class="fa fa-search"></i>Save</button>
                    </div>
                </div>
            
                
              
            </div>
        </div>
        
</section>
</div>
<div class="col-md-8 pl-0">

</div>
</div>
<!-- Searched Flat discount products Area-->
<div class="row" style="margin-left:0px !important;">
    <div class="col-sm-6" style="border:0px solid !important;padding-right:0px !important;">
        <div class="hk-row">
            <table width="90%" border="0" class="table-bordered table-hover table-striped mb-0">                               
            <thead>
            <tr class="blue_Head">
                <th class="pa-10 leftAlign" width="45%"> <span class="bold itemfocus"><span class="titems">0</span></span><span class="plural">Item</span></th>
                <th width="18%" class="centerAlign">Cost Price</th>                                                    
                <th width="17%" class="centerAlign">MRP</th> 
                <th width="10%" class="centerAlign">Qty</th>
                <th width="10%"></th>
            </tr>
            </thead>
           
             
            <tbody id="sproduct_detail_record">
                <tr>
                    <td><input class="form-control form-inputtext typeahead" value="" maxlength="" type="text" name="productsearch" id="productsearch" placeholder="Enter Barcode/Product Code/Product Name" data-provide="typeahead" data-items="10" data-source=""></td>
                    <td></td>
                    <td></td>
                    <td><input type="text" id="" value="" class="form-control form-inputtext number chargesamt" name="chargesamt[]"></td>
                    <td></td>
                </tr>
            </tbody>
            </table>
            <button type="button" class="btn btn-info" style="height:25px;padding:0 !important;" id="addbuyx" name="addbuyx"><i class="fa fa-plus"></i></button>
        </div>
    </div>

    <div class="col-sm-6" style="border:0px solid !important;padding-right:0px !important;">
        <div class="hk-row">
            <table width="100%" border="0" class="table-bordered table-hover table-striped mb-0">                               
            <thead>
            <tr class="blue_Head">
                <th class="pa-10 leftAlign" width="45%"> <span class="bold itemfocus"><span class="titems">0</span></span><span class="plural">Item</span></th>
                <th width="18%" class="centerAlign">Cost Price</th>                                                    
                <th width="17%" class="centerAlign">MRP</th> 
                <th width="10%" class="centerAlign">Qty</th>
                <th width="10%"></th>
            </tr>
            </thead>
           
             
            <tbody id="sproduct_detail_record">
                 <tr>
                    <td><input class="form-control form-inputtext typeahead" value="" maxlength="" type="text" name="productsearch" id="productsearch" placeholder="Enter Barcode/Product Code/Product Name" data-provide="typeahead" data-items="10" data-source=""></td>
                    <td></td>
                    <td></td>
                    <td><input type="text" id="" value="" class="form-control form-inputtext number chargesamt" name="chargesamt[]"></td>
                    <td></td>
                </tr>
            </tbody>
            </table>
        </div>
</div>

