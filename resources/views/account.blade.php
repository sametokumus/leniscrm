@include('include.header')
<?php
$extra_js='
<script src="services/account.js"></script>
';
?>

<!--app-content open-->
<div class="main-content app-content">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="row">
                <div class="col-md-12">
                    <h3 class="page-header">
                        Bilgileri Güncelle
                    </h3>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6">
                    <div class="card border-theme mb-3">
                        <div class="card-body">
                            <form method="post" action="#" id="update_account_form">
                                <div class="row p-3">

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Profil fotoğrafını <a href="#" id="update_admin_current_profile_photo" target="_blank">görüntülemek için tıklayınız...</a></label>
                                        <input type="file" class="form-control" id="update_admin_profile_photo" />
                                    </div>

                                    <div class="row mb-4">
                                        <label class="col-md-3 form-label">E-posta :</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="update_admin_email" required>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <label class="col-md-3 form-label">Ad :</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="update_admin_name" required>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <label class="col-md-3 form-label">Soyad :</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="update_admin_surname" required>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <label class="col-md-3 form-label">Telefon :</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="update_admin_phone" required>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <label class="col-md-3 form-label">Şifre :</label>
                                        <div class="col-md-9">
                                            <input type="password" class="form-control" id="update_admin_password">
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <button type="submit" class="btn btn-theme w-100">Kaydet</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-arrow">
                            <div class="card-arrow-top-left"></div>
                            <div class="card-arrow-top-right"></div>
                            <div class="card-arrow-bottom-left"></div>
                            <div class="card-arrow-bottom-right"></div>
                        </div>
                    </div>
                </div>

            </div>



        </div>
        <!-- CONTAINER END -->
    </div>
</div>
<!--app-content close-->

@include('include.footer')
