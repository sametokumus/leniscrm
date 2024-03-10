<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8" />
    <title>S-CRM | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />

    <link href="css/vendor.min.css" rel="stylesheet" />
    <link href="css/app.min.css" rel="stylesheet" />

</head>
<body class='pace-top'>

<div id="app" class="app app-full-height app-without-header">

    <div class="login">

        <div class="login-content">
            <form method="post" action="#" id="login_form">
{{--                <h1 class="text-center">Giriş</h1>--}}
                <div class="mb-50px w-50 mx-auto">
                    <img src="/img/logo/x-logo-dark.png" alt="logo" class="w-100">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{__('Email Address')}} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg bg-white bg-opacity-5" value="" placeholder="" id="login_email" />
                </div>
                <div class="mb-3">
                    <label class="form-label">{{__('Password')}} <span class="text-danger">*</span></label>
                    <input type="password" class="form-control form-control-lg bg-white bg-opacity-5" value="" placeholder="" id="login_password" />
                </div>
                <button type="submit" class="btn btn-outline-theme btn-lg d-block w-100 fw-500 mb-3">{{__('Sign In')}}</button>

            </form>
        </div>

    </div>


    <a href="#" data-toggle="scroll-to-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>

</div>


<script src="js/vendor.min.js"></script>
<script src="js/app.min.js"></script>

<!-- VENDOR -->
<script src="vendor/fileupload.js"></script>
<script src="vendor/bCrypt.js"></script>
<script src="vendor/inputmask/jquery.inputmask.bundle.js"></script>

<!-- SERVICE JS -->
<script src="messages.js"></script>
<script src="services/service.js"></script>
<script src="services/login.js"></script>

</body>

</html>
