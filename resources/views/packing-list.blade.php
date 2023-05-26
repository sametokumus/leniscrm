@include('include.header')
<?php
$extra_js='
<script src="services/packing-list.js"></script>
';
?>

    <!--app-content open-->
<div class="main-content app-content">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="row">
                <div class="col-md-12">
                    <h1 class="page-header">
                        Gönderi Listeleri
                    </h1>
                </div>
            </div>

            <div class="row">

                <div class="col-md-12">
                    <div class="card border-theme mb-3">
                        <div class="card-body p-3 overflow-auto">
                            <table id="packing-lists" class="table table-bordered text-nowrap key-buttons border-bottom w-100">
                                <thead>
                                <tr>
                                    <th class="border-bottom-0">ID</th>
                                    <th class="border-bottom-0">Ürün Kalemi</th>
                                    <th class="border-bottom-0"></th>
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

            <div class="row">
                <div class="col-md-12">
                    <h1 class="page-header">
                        Gönderi Listesi
                    </h1>
                </div>
            </div>

            <div class="row">

                <div class="col-md-12">
                    <div class="card border-theme mb-3">
                        <div class="card-body p-3 overflow-auto">
                            <table id="packing-list-detail" class="table table-bordered text-nowrap key-buttons border-bottom w-100">
                                <thead>
                                <tr>
                                    <th class="border-bottom-0">N#</th>
                                    <th class="border-bottom-0">ID</th>
                                    <th class="border-bottom-0"></th>
                                    <th class="border-bottom-0">Ürün Adı</th>
                                    <th class="border-bottom-0">Ref. Code</th>
                                    <th class="border-bottom-0">Teslimat Süresi</th>
                                    <th class="border-bottom-0">Teklif Miktar</th>
                                </tr>
                                </thead>
                                <tbody id="sales-detail-body">

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

            <div class="row">
                <div class="col-md-12">
                    <h1 class="page-header">
                        Gönderilmeyen Ürünler
                    </h1>
                </div>
            </div>

            <div class="row">

                <div class="col-md-12">
                    <div class="card border-theme mb-3">
                        <div class="card-body p-3 overflow-auto">
                            <table id="packingable-list-detail" class="table table-bordered text-nowrap key-buttons border-bottom w-100">
                                <thead>
                                <tr>
                                    <th class="border-bottom-0">N#</th>
                                    <th class="border-bottom-0">ID</th>
                                    <th class="border-bottom-0"></th>
                                    <th class="border-bottom-0">Ürün Adı</th>
                                    <th class="border-bottom-0">Ref. Code</th>
                                    <th class="border-bottom-0">Teslimat Süresi</th>
                                    <th class="border-bottom-0">Teklif Miktar</th>
                                </tr>
                                </thead>
                                <tbody id="offer-detail-body">

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
