<div class="row">
                        <div class="col-sm">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Customer Name</label>
                                    <input type="text" maxlength="50" autocomplete="off" name="customer_name" id="pcustomer_name" value="" class="form-control form-inputtext invalid" placeholder="" autofocus>
                                     <input type="hidden" name="customer_id" id="pcustomer_id" value="">
                                     <input type="hidden" name="customer_address_detail_id" id="pcustomer_address_detail_id" value="">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Mobile No.</label>
                                    <input type="tel" autocomplete="off" name="customer_mobile" id="pcustomer_mobile" value="" maxlength="15" class="form-control form-inputtext invalid mobileregax" placeholder=""
                                    style="width:235px !important;">
                                    <input type="hidden" name="customer_mobile_dial_code" id="customer_mobile_dial_code" value="">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input type="text" autocomplete="off" maxlength="50" name="customer_email" id="pcustomer_email" value=""  class="form-control form-inputtext" placeholder="">
                                </div>
                                <?php
                                $tax_lable = "GSTIN";
                                if($nav_type[0]['tax_type']== 1) {
                                    $tax_lable = $nav_type[0]['tax_title'];
                                }
                                ?>
                                <div class="col-md-4">
                                    <label class="form-label"><?php echo $tax_lable?></label>
                                    <input type="text" maxlength="15" name="customer_gstin" id="pcustomer_gstin" value=""  class="form-control form-inputtext" placeholder="">
                                </div>

                                <div class="col-md-4 ">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="text" maxlength="15" name="customer_date_of_birth" id="pcustomer_date_of_birth" value=""  class="form-control form-inputtext" placeholder="">
                                </div>

                                <div class="col-md-4 ">
                                    <label class="form-label">Address</label>
                                    <input type="text" maxlength="100" name="customer_address" id="pcustomer_address" value=""  class="form-control form-inputtext" placeholder="">
                                </div>

                                <div class="col-md-4 ">
                                    <label class="form-label">Area</label>
                                    <input type="text" maxlength="100" name="customer_area" id="pcustomer_area" value=""  class="form-control form-inputtext" placeholder="">
                                </div>

                                <div class="col-md-4 ">
                                    <label class="form-label">City / Town</label>
                                    <input type="text" maxlength="100" name="customer_city" id="pcustomer_city" value=""  class="form-control form-inputtext" placeholder="">
                                </div>

                                <div class="col-md-4 ">
                                    <label class="form-label">Pin / Zip Code</label>
                                    <input type="text" maxlength="20" name="customer_pincode" id="pcustomer_pincode" value=""  class="form-control form-inputtext" placeholder="">
                                </div>

                                <div class="col-md-4 ">
                                    <label class="form-label">State / Region</label>
                                    <select name="state_id" id="pstate_id" class="form-control form-inputtext">
                                        <option value="0">Select State</option>
                                        @foreach($state AS $statekey=>$statevalue)
                                            <option value="{{$statevalue->state_id}}">{{$statevalue->state_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 ">
                                    <label class="form-label">Country</label>
                                    <select name="country_id" id="pcountry_id" class="form-control form-inputtext">
                                        <option value="">Select Country</option>
                                        @foreach($country AS $countrykey=>$countryvalue)
                                            <option <?php if($countryvalue['country_id'] == $nav_type[0]['country_id']) echo "selected"  ?> value="{{$countryvalue->country_id}}">{{$countryvalue->country_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4"><label class="form-label">Credit Period(days)</label>
                                     <input type="text" maxlength="20" name="outstanding_duedays" id="poutstanding_duedays" value=""  class="form-control form-inputtext number" placeholder=""></div>
                                 <div class="col-md-4 ">
                                <label class="form-label">How did you came to know about us?<br></label>
                                <select name="source" id="psource" class="form-control form-inputtext">
                                    <option value="">Select Source</option>
                                    @foreach($customer_source AS $source_key=>$source_value)
                                        <option value="{{$source_value->customer_source_id}}">{{$source_value->source_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                                 <div class="col-md-4 ">
                                  <label class="form-label">Note(for internal use)</label>
                                  <textarea  class="form-control form-inputtext" name="customer_note" id="pcustomer_note" ></textarea>
                                </div>
                                 <div class="col-md-4 mt-25">
                                    <input type="hidden" class="alertStatus" value="0" />
                                    <button type="button" id="savecustomer" name="savecustomer" class="btn btn-info saveBtn btn-block"><i class="fa fa-save"></i>Save</button></div>
                                </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">&nbsp;
                            </div>
                        </div>

                    </div>