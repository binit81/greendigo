<link href="https://hencework.com/theme/mintos/dist/css/style.css" rel="stylesheet" type="text/css">
<link href='https://fonts.googleapis.com/css?family=Roboto Condensed' rel='stylesheet'>
<link rel="shortcut icon" href="favicon.png">

    <link rel="icon" type="image/png" href="favicon.png" />
<style>
    body{
        font-family: 'Roboto Condensed';
    }
</style>
<title>Login - Retailcore Omni</title>
@section('content')

<!-- HK Wrapper -->
    <div class="hk-wrapper">

        <!-- Main Content -->
        <div class="hk-pg-wrapper hk-auth-wrapper">
            <header class="d-flex justify-content-between align-items-center">
                <a class="d-flex auth-brand" href="#">
                    <img class="brand-img" src="{{URL::to('/')}}/public/images/RC-LOGO-White.png" width="250" alt="brand" />
                </a>
                <div class="btn-group btn-group-sm">
                    <a href="https://www.retailcore.in/" target="_blank" class="btn btn-outline-secondary">About Us</a>
                </div>
            </header>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-5 pa-0">
                        <div id="owl_demo_1" class="owl-carousel dots-on-item owl-theme">
                            <div class="fadeOut item auth-cover-img overlay-wrap" style="background-image:url(public/images/rc-banner.jpg);">
                                <div class="auth-cover-info py-xl-0 pt-100 pb-50">
                                    <div class="auth-cover-content text-center w-xxl-75 w-sm-90 w-xs-100">
                                        <h1 class="display-3 text-white mb-20">Dedicated<br>to Service...</h1><!-- 
                                        <a href="https://www.retailcore.in/software-demo" target="_blank"><button type="submit" class="btn btn-primary">Click here</button></a> -->
                                    </div>
                                </div>
                                <div class="bg-overlay bg-trans-dark-50"></div>
                            </div>
                            <!-- <div class="fadeOut item auth-cover-img overlay-wrap">
                                <div class="auth-cover-info py-xl-0 pt-100 pb-50">
                                    <div class="auth-cover-content text-center w-xxl-75 w-sm-90 w-xs-100">
                                        <h1 class="display-3 text-white mb-20">Don't have login? Want free trial?</h1>
                                        <a href="https://www.retailcore.in/software-demo" target="_blank"><button type="submit" class="btn btn-primary">Click here</button></a>
                                    </div>
                                </div>
                                <div class="bg-overlay bg-trans-dark-50"></div>
                            </div> -->
                            
                        </div>
                    </div>
                    <div class="col-xl-7 pa-0">
                        <div class="auth-form-wrap py-xl-0 py-50">
                            <div class="auth-form w-xxl-55 w-xl-75 w-sm-90 w-xs-100">
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <h1 class="display-4 mb-10">Welcome Back :)</h1>
                                    <p class="mb-30">Sign in to your account.</p>
                                    <div class="form-group">
                                        <input id="email" type="email" placeholder="E-Mail Address" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input id="password" placeholder="Password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                                            
                                        </div>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-25">
                                        <input class="form-check-input" type="checkbox" name="" id="" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                    {{ __('Login') }}
                                </button>

                                <br />
                                <small>By using RetailCore Software you agree to our <a id="iagreeClick" style="color:#0011E9; cursor:pointer; text-decoration:underline;">software service level agreement (SLA)</a></small>
                                    
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Main Content -->

    </div>
    <!-- /HK Wrapper -->
    
    <!-- jQuery -->
    <script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="{{URL::to('/')}}/public/template/popper.js/dist/umd/popper.min.js"></script>
    <script src="{{URL::to('/')}}/public/template/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Slimscroll JavaScript -->
    <script src="{{URL::to('/')}}/public/dist/js/jquery.slimscroll.js"></script>

    <!-- Fancy Dropdown JS -->
    <script src="{{URL::to('/')}}/public/dist/js/dropdown-bootstrap-extended.js"></script>

    <!-- Owl JavaScript -->
    <script src="{{URL::to('/')}}/public/template/owl.carousel/dist/owl.carousel.min.js"></script>

    <!-- FeatherIcons JavaScript -->
    <script src="{{URL::to('/')}}/public/dist/js/feather.min.js"></script>
    
    <!-- Init JavaScript -->
    <script src="{{URL::to('/')}}/public/dist/js/init.js"></script>
    <script>/*Login Init*/
 
"use strict"; 
$('#owl_demo_1').owlCarousel({
    items: 1,
    animateOut: 'fadeOut',
    loop: true,
    margin: 10,
    autoplay: true,
    mouseDrag: false

});</script>

<style type="text/css">
.iagreebox{
    background: #ffffff;
    text-align: center;
    padding: 10px 0;
    width: 100%;
    position: fixed;
    bottom: 0;
    font-size: 13px;
}
.iagreePopup{
    width: 80%;
    margin: 50px auto;
    left: 10%;
    background: #ffffff;
    padding: 20px;
    position: fixed;
    top: 0;
    height: 90%;
    overflow: auto;
    z-index: 1000000;
}
.closeBtn{
    width: 80%;
    margin: 50px auto;
    left: 10%;
    background: #ffffff;
    padding: 20px;
    position: fixed;
    top: 0;
    text-align: right;
    overflow: auto;
    z-index: 1000000;
    cursor: pointer;
}
</style>

<div class="iagreePopup" style="display:none;">
    <br clear="all" /><br clear="all" />
<?php
    $iagree     =   DEFAULT_COMPANY_URL.'rc_terms_and_conditions.html';
    echo '<iframe width="100%" height="100%" src="'.$iagree.'"></iframe>';
    if (file_exists($iagree))
    {
        
    }
    else
    {
        $tc_url     =   TC_URL.'rc_terms_and_conditions.html';
        $read_    =   @file_get_contents($tc_url);

        if($read_!='')
        {
            $t_and_c_url    =   DEFAULT_COMPANY_URL.'rc_terms_and_conditions.html';

            $fp_ = fopen($t_and_c_url, "w");
            fwrite($fp_,$read_);
            fclose($fp_);

            echo '<iframe width="100%" height="100%" src="'.$iagree.'"></iframe>';
            //@include($iagree);
        }
    }
?>
</div>
<div class="closeBtn" style="display:none;"><b>Close [x]</b></div>

<script src="{{URL::to('/')}}/public/template/jquery/dist/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(e){
    $('#iagreeClick').click(function(e){
        $('.iagreePopup').show();
        $('.closeBtn').show();
    })
    $('.closeBtn').click(function(e){
        $('.iagreePopup').hide();
        $('.closeBtn').hide();
    })
})
</script>
</body>

</html>



