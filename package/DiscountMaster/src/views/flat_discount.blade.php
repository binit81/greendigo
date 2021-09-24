 <div class="row">
<div class="col-md-8 pl-5">
<section class="hk-sec-wrapper " id="filterarea_block">
        <div id="">
            <div class="hk-row common-search">
                <div class="col-md-3 pb-10">
                    <div class="form-group">
                        <input type="text" name-attr="product_name" maxlength="50" autocomplete="off" name="product_name_filter" id="product_name_filter" value="" class="form-control form-inputtext" placeholder="Product Name">
                    </div>
                </div>            
                <div class="col-md-3">
                     <div class="form-group">
                        <input type="text" name-attr="from_barcode" maxlength="50" autocomplete="off" name="from_barcode_filter" id="from_barcode_filter" value="" class="form-control form-inputtext" placeholder="From Barcode">
                    </div>
                   
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                         <input type="text" name-attr="to_barcode" maxlength="50" autocomplete="off" name="to_barcode_filter" id="to_barcode_filter" value="" class="form-control form-inputtext" placeholder="To Barcode">
                    </div>
                </div>
                 @foreach($product_features AS  $product_features_key=>$product_features_value)
                <div class="col-md-3">
                     <div class="form-group">
                            <!-- <label class="form-label">{{$product_features_value->product_features_name}}</label> -->
                            <select class="form-control form-inputtext" name-attr="{{$product_features_value->html_id}}" id="{{$product_features_value->html_id}}" name="{{$product_features_value->html_name}}" value="" >
                                <option value="">Select {{$product_features_value->product_features_name}} </option>
                                @foreach($product_features_value->product_features_data AS  $kk=>$vv)
                                <option value="{{$vv->product_features_data_id}}">{{$vv->product_features_data_value}}</option>

                                @endforeach
                            </select>
                     </div>
                </div>
                         @endforeach               
                <div class="col-md-3">
                    <div class="form-group">
                        <select name-attr="uqc_id" class="form-control form-inputtext" name="uqc_id_filter" id="uqc_id_filter"></select>
                    </div>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6 rightAlign mt-10">
                    <button type="button" class="btn btn-info searchBtn search_data"  id="search_product"><i class="fa fa-search"></i>Search</button>
                    <button type="button" name="resetfilter" onclick="resetproductfilterdata();" class="btn btn-info resetbtn" id="resetfilter" data-container="body" data-toggle="popover" data-placement="bottom" data-content="" data-original-title="" title="">Reset</button>
                </div>
            </div>
        </div>
        
</section>
</div>
<div class="col-md-4 pa-0">
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
                 <div class="col-md-6 pb-10">
                    <div class="form-group">
                       <label class="leftAlign">Flat Discount(%)</label>
                       <input type="text" class="form-control number" name="flat_discount_per" id="flat_discount_per" value="" onkeyup="applyFlatdiscountper();">
                    </div>
                </div>
                <div class="col-md-6 pb-10">
                    <div class="form-group">
                       <label class="leftAlign">Flat Discount Amt.</label>
                       <input type="text" class="form-control number" name="flat_discount_amt" id="flat_discount_amt" value="" onchange="applyFlatdiscountamt();">
                    </div>
                </div>
              
            </div>
        </div>
        
</section>
</div>
</div>
<!-- Searched Flat discount products Area-->
<div class="col-xl-12 pa-0" >
 <div class="hk-row">
    <div class="col-md-12">
    
    <div class="table-wrap">
    
            <div class="table-responsive pa-0 ma-0"  id="searchflatproductrecord" style="overflow-y:auto !important;max-height:600px !important;">
            @include('discountmaster::searchFlatProduct_area')
            </div><!--table-wrap-->
        </div><!--table-responsive-->
      </div>
    </div>
</div>