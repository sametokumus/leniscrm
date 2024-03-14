@include('include.header')
<?php
$extra_js='
<script src="services/sw-step1.js"></script>
';
?>

    <!--app-content open-->
<div class="main-content app-content">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="row">
                <div class="col-md-12">
                    <nav class="nav nav-wizards-3 mb-2">
                        <div class="nav-item col">
                            <a class="nav-link active" href="#">
                                <div class="nav-dot"></div>
                                <div class="nav-title">Adım 1</div>
                                <div class="nav-text">Firma / İstek Seçimi</div>
                            </a>
                        </div>
                        <div class="nav-item col">
                            <a class="nav-link disabled" href="#">
                                <div class="nav-dot"></div>
                                <div class="nav-title">Adım 2</div>
                                <div class="nav-text">Teklif Oluşturma</div>
                            </a>
                        </div>
                        <div class="nav-item col">
                            <a class="nav-link disabled" href="#">
                                <div class="nav-dot"></div>
                                <div class="nav-title">Adım 3</div>
                                <div class="nav-text">Fiyat Güncelleme</div>
                            </a>
                        </div>
                    </nav>
                </div>
            </div>

            <div class="row">

                <div class="col-md-12">
                    <div class="card border-theme mb-3">
                        <div class="card-body p-3">
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Müşteri</label>
                                    <select class="form-control" id="select_sales_company" onchange="onChangeCompany();">

                                    </select>
                                </div>
                            </div>
                            <table id="offer-requests-datatable" class="table table-bordered text-nowrap key-buttons border-bottom">
                                <thead>
                                <tr>
                                    <th class="border-bottom-0" data-priority="1">ID</th>
                                    <th class="border-bottom-0">Talep Numarası</th>
                                    <th class="border-bottom-0">Yetkili Satış Temsilcisi</th>
                                    <th class="border-bottom-0">Firma</th>
                                    <th class="border-bottom-0">Firma Yetkilisi</th>
                                    <th class="border-bottom-0">Ürün Adedi</th>
                                    <th class="border-bottom-0" data-priority="2">İşlem</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
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
