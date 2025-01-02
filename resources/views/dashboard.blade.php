@include('include.header')
<?php
$extra_js='
<script src="plugins/jvectormap-next/jquery-jvectormap.min.js"></script>
<script src="plugins/jvectormap-next/jquery-jvectormap-world-mill.js"></script>
<script src="plugins/apexcharts/dist/apexcharts.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/4.2.2/masonry.pkgd.min.js"></script>
<script src="services/dashboard.js"></script>
';
?>

<div id="content" class="app-content">
    <div class="">

        <div class="card bg-light-200 border-dark mb-3">

            <div class="card-body p-3">

                <div class="row">
                    <div class="col-6">
                        <select class="form-control" id="dash_owner" onchange="changeDashOwner();">
                        </select>
                    </div>
                    <div class="col-6">
                        <select class="form-control" id="dash_currency" onchange="changeDashCurrency();">
                            <option value="TRY">TRY</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                        </select>
                    </div>
                </div>

            </div>


            <div class="card-arrow">
                <div class="card-arrow-top-left"></div>
                <div class="card-arrow-top-right"></div>
                <div class="card-arrow-bottom-left"></div>
                <div class="card-arrow-bottom-right"></div>
            </div>

        </div>
    </div>

    <div class="row">

        <div class="col-xl-2 col-lg-2">

            <div class="card bg-theme-200 mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">TOPLAM ONAYLANAN</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>


                    <div class="row align-items-center mb-2" id="approved-box">
                        <div class="col-12">
                            <h5 class="mb-0"></h5>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-success"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-success"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-success"></div>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>


        <div class="col-xl-2 col-lg-2">

            <div class="card bg-warning-200 mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">TOPLAM TAMAMLANAN</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>


                    <div class="row align-items-center mb-2" id="completed-box">
                        <div class="col-12">
                            <h5 class="mb-0"></h5>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-warning"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-warning"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-warning"></div>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>


        <div class="col-xl-2 col-lg-2">

            <div class="card bg-info-200 mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">TOPLAM POTANSİYEL</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>


                    <div class="row align-items-center mb-2" id="potential-box">
                        <div class="col-12">
                            <h5 class="mb-0"></h5>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-info"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-info"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-info"></div>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>


        <div class="col-xl-2 col-lg-2">

            <div class="card bg-danger-200 mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">TOPLAM İPTAL EDİLEN</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>


                    <div class="row align-items-center mb-2" id="cancelled-box">
                        <div class="col-12">
                            <h5 class="mb-0"></h5>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-danger"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-danger"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-danger"></div>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>


        <div class="col-xl-2 col-lg-2">

            <div class="card mb-3">

                <div class="card-body">

                    <div class="flex-fill">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-1">TOPLAM KARLILIK</span>
                            <a href="#" data-toggle="card-expand"
                               class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                        </div>

                        <div class="row align-items-center mb-2" id="total-profit-box">
                            <div class="col-12">
                                <h5 class="mb-0"></h5>
                                <div class="spinners">
                                    <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dash-icon" id="total-profit-box-icon">

                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>


        <div class="col-xl-2 col-lg-2">

            <div class="card mb-3">

                <div class="card-body d-flex align-items-center bg-white bg-opacity-15">

                    <div class="flex-fill">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-1">TOPLAM TALEP <small>(Bu Ay - Yıl)</small></span>
                            <a href="#" data-toggle="card-expand"
                               class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                        </div>

                        <div class="row align-items-center mb-2" id="total-request-box">
                            <div class="col-12">
                                <h5 class="mb-0"></h5>
                                <div class="spinners">
                                    <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dash-icon" id="total-request-box-icon">

                    </div>

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

    <div class="row sparkboxes">

        <div class="col-xl-2 col-lg-2">

            <div class="card mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">BU AY ONAYLANAN</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="row align-items-center mb-2" id="monthly-approved-box">
                        <div class="col-12">
                            <h5 class="mb-0"></h5>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-success"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-success"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-success"></div>
                            </div>
                        </div>
                    </div>

                    <div class="box box1">
                        <div id="spark1"></div>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>

        <div class="col-xl-2 col-lg-2">

            <div class="card mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">BU AY TAMAMLANAN</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="row align-items-center mb-2" id="monthly-completed-box">
                        <div class="col-12">
                            <h5 class="mb-0"></h5>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-warning"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-warning"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-warning"></div>
                            </div>
                        </div>
                    </div>

                    <div class="box box2">
                        <div id="spark2"></div>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>

        <div class="col-xl-2 col-lg-2">

            <div class="card mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">BU AY POTANSİYEL</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="row align-items-center mb-2" id="monthly-continue-box">
                        <div class="col-12">
                            <h5 class="mb-0"></h5>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-info"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-info"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-info"></div>
                            </div>
                        </div>
                    </div>

                    <div class="box box3">
                        <div id="spark3"></div>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>

        <div class="col-xl-2 col-lg-2">

            <div class="card mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">BU AY İPTAL EDİLEN</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="row align-items-center mb-2" id="monthly-cancelled-box">
                        <div class="col-12">
                            <h5 class="mb-0"></h5>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-danger"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-danger"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-danger"></div>
                            </div>
                        </div>
                    </div>

                    <div class="box box4">
                        <div id="spark4"></div>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>


        <div class="col-xl-2 col-lg-2">

            <div class="card mb-3">

                <div class="card-body">

                    <div class="flex-fill">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-1">TEKLİF ONAYLANMA ORANI</span>
                            <a href="#" data-toggle="card-expand"
                               class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                        </div>

                        <div class="row align-items-center mb-2" id="offer-turning-box">
                            <div class="col-12">
                                <h5 class="mb-0"></h5>
                                <div class="spinners">
                                    <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dash-icon" id="offer-turning-box-icon">

                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

            <div class="card mb-3">

                <div class="card-body">

                    <div class="flex-fill">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-1">ÖNCEKİ AYA GÖRE CİRO ORANI</span>
                            <a href="#" data-toggle="card-expand"
                               class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                        </div>

                        <div class="row align-items-center mb-2" id="turnover-box">
                            <div class="col-12">
                                <h5 class="mb-0"></h5>
                                <div class="spinners">
                                    <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dash-icon" id="turnover-box-icon">

                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>


        <div class="col-xl-2 col-lg-2">

            <div class="card mb-3">

                <div class="card-body d-flex align-items-center bg-white bg-opacity-15">

                    <div class="flex-fill">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-1">TOPLAM SİPARİŞ <small>(Bu Ay - Yıl)</small></span>
                            <a href="#" data-toggle="card-expand"
                               class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                        </div>

                        <div class="row align-items-center mb-2" id="total-sale-box">
                            <div class="col-12">
                                <h5 class="mb-0"></h5>
                                <div class="spinners">
                                    <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dash-icon" id="total-sale-box-icon">

                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

            <div class="card mb-3">

                <div class="card-body d-flex align-items-center bg-white bg-opacity-15">

                    <div class="flex-fill">
                        <div class="d-flex fw-bold small mb-3">
                            <span class="flex-grow-1">TOPLAM GÖRÜŞME <small>(Bu Ay - Yıl)</small></span>
                            <a href="#" data-toggle="card-expand"
                               class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                        </div>

                        <div class="row align-items-center mb-2" id="total-activity-box">
                            <div class="col-12">
                                <h5 class="mb-0"></h5>
                                <div class="spinners">
                                    <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                    <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dash-icon" id="total-activity-box-icon">

                    </div>

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
        <div class="col-xl-6">

            <div class="masonry-layout-1">
                <div class="grid-sizer"></div>
                <div class="gutter-sizer"></div>

                <div class="grid-item">

                    <!-- onaylanan satışlar -->
                    <div class="card mb-3">

                        <div class="card-body">

                            <div class="d-flex fw-bold small mb-3">
                                <span class="flex-grow-1">ONAYLANAN SATIŞLAR</span>
                                <a href="#" data-toggle="card-expand"
                                   class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                            </div>


                            <div class="mb-3">
                                <div id="chart-approved-monthly">

                                    <div class="spinners">
                                        <div class="spinner spinner-grow spinner-grow-1 m-1 text-success"></div>
                                        <div class="spinner spinner-grow spinner-grow-2 m-1 text-success"></div>
                                        <div class="spinner spinner-grow spinner-grow-3 m-1 text-success"></div>
                                    </div>
                                </div>
                            </div>


                        </div>


                        <div class="card-arrow">
                            <div class="card-arrow-top-left"></div>
                            <div class="card-arrow-top-right"></div>
                            <div class="card-arrow-bottom-left"></div>
                            <div class="card-arrow-bottom-right"></div>
                        </div>

                    </div>

                </div>

                <div class="grid-item">

                    <!-- tamamlanan satışlar -->
                    <div class="card mb-3">

                        <div class="card-body">

                            <div class="d-flex fw-bold small mb-3">
                                <span class="flex-grow-1">TAMAMLANAN SATIŞLAR</span>
                                <a href="#" data-toggle="card-expand"
                                   class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                            </div>


                            <div class="mb-3">
                                <div id="chart-completed-monthly">
                                    <div class="spinners">
                                        <div class="spinner spinner-grow spinner-grow-1 m-1 text-warning"></div>
                                        <div class="spinner spinner-grow spinner-grow-2 m-1 text-warning"></div>
                                        <div class="spinner spinner-grow spinner-grow-3 m-1 text-warning"></div>
                                    </div>
                                </div>
                            </div>


                        </div>


                        <div class="card-arrow">
                            <div class="card-arrow-top-left"></div>
                            <div class="card-arrow-top-right"></div>
                            <div class="card-arrow-bottom-left"></div>
                            <div class="card-arrow-bottom-right"></div>
                        </div>

                    </div>

                </div>

                <div class="grid-item">

                    <!-- potansiyel satışlar -->
                    <div class="card mb-3">

                        <div class="card-body">

                            <div class="d-flex fw-bold small mb-3">
                                <span class="flex-grow-1">POTANSİYEL SATIŞLAR</span>
                                <a href="#" data-toggle="card-expand"
                                   class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                            </div>


                            <div class="mb-3">
                                <div id="chart-potential-sales">
                                    <div class="spinners">
                                        <div class="spinner spinner-grow spinner-grow-1 m-1 text-info"></div>
                                        <div class="spinner spinner-grow spinner-grow-2 m-1 text-info"></div>
                                        <div class="spinner spinner-grow spinner-grow-3 m-1 text-info"></div>
                                    </div>
                                </div>
                            </div>


                        </div>


                        <div class="card-arrow">
                            <div class="card-arrow-top-left"></div>
                            <div class="card-arrow-top-right"></div>
                            <div class="card-arrow-bottom-left"></div>
                            <div class="card-arrow-bottom-right"></div>
                        </div>

                    </div>

                </div>

                <div class="grid-item">

                    <!-- iptal edilen satışlar -->
                    <div class="card mb-3">

                        <div class="card-body">

                            <div class="d-flex fw-bold small mb-3">
                                <span class="flex-grow-1">İPTAL EDİLEN SATIŞLAR</span>
                                <a href="#" data-toggle="card-expand"
                                   class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                            </div>


                            <div class="mb-3">
                                <div id="chart-cancelled-potential-sales">
                                    <div class="spinners">
                                        <div class="spinner spinner-grow spinner-grow-1 m-1 text-danger"></div>
                                        <div class="spinner spinner-grow spinner-grow-2 m-1 text-danger"></div>
                                        <div class="spinner spinner-grow spinner-grow-3 m-1 text-danger"></div>
                                    </div>
                                </div>
                            </div>


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
        <div class="col-xl-6">

            <div class="masonry-layout-2">
                <div class="grid-sizer"></div>
                <div class="gutter-sizer"></div>

                <div class="grid-item grid-item--width2">

                    <!-- aylık karlılık -->
                    <div class="card mb-3">

                        <div class="card-body">

                            <div class="d-flex fw-bold small mb-3">
                                <span class="flex-grow-1">AYLIK KARLILIK</span>
                                <a href="#" data-toggle="card-expand"
                                   class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                            </div>


                            <div class="mb-3">
                                <div id="chart-profit-rates-monthly">

                                    <div class="spinners">
                                        <div class="spinner spinner-grow spinner-grow-1 m-1 text-theme"></div>
                                        <div class="spinner spinner-grow spinner-grow-2 m-1 text-theme"></div>
                                        <div class="spinner spinner-grow spinner-grow-3 m-1 text-theme"></div>
                                    </div>
                                </div>
                            </div>


                        </div>


                        <div class="card-arrow">
                            <div class="card-arrow-top-left"></div>
                            <div class="card-arrow-top-right"></div>
                            <div class="card-arrow-bottom-left"></div>
                            <div class="card-arrow-bottom-right"></div>
                        </div>

                    </div>

                </div>

                <div class="grid-item grid-item--width2">

                    <!-- aylık karlılık -->
                    <div class="card mb-3">

                        <div class="card-body">

                            <div class="d-flex fw-bold small mb-3">
                                <span class="flex-grow-1">AYLIK TEKLİF ONAYLANMA ORANI</span>
                                <a href="#" data-toggle="card-expand"
                                   class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                            </div>


                            <div class="mb-3">
                                <div id="chart-turning-rates-monthly">

                                    <div class="spinners">
                                        <div class="spinner spinner-grow spinner-grow-1 m-1 text-theme"></div>
                                        <div class="spinner spinner-grow spinner-grow-2 m-1 text-theme"></div>
                                        <div class="spinner spinner-grow spinner-grow-3 m-1 text-theme"></div>
                                    </div>
                                </div>
                            </div>


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
    </div>
    <div class="row">
        <div class="col-12">

            <!-- en iyi müşteri -->
            <div class="card mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">EN İYİ MÜŞTERİ</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="best-customers-table" class="table table-striped table-borderless mb-2px small text-nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Müşteri</th>
                                <th>İkili ilişki katsayısı (%25)</th>
                                <th>Sipariş/Teklif (Adet) (%22)</th>
                                <th>Karlılık (%20)</th>
                                <th>Toplam İş Hacmi (Tutar) 3 Ay (%18)</th>
                                <th>Ödeme Yöntemi (%15)</th>
                                <th>Genel Puan</th>
                            </tr>
                            </thead>
                            <tbody>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                            </div>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>
        <div class="col-12">

            <!-- en iyi personel -->
            <div class="card mb-3">

                <div class="card-body">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">EN İYİ PERSONEL</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="best-staffs-table" class="table table-striped table-borderless mb-2px small text-nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Ad Soyad</th>
                                <th>Sipariş/Teklif (Adet) (%17)</th>
                                <th>Toplam İş Hacmi (Tutar) 3 Ay (%15)</th>
                                <th>Karlılık (%14)</th>
                                <th>Ödeme Yöntemi (%12)</th>
                                <th>Yönetici Değerlendirmesi (%11)</th>
                                <th>Aynı Müşteriye Devamlı Satış (%9)</th>
                                <th>Yapılan Görüşme Sayısı Yüz Yüze (%7)</th>
                                <th>İhracat Satışı (%6)</th>
                                <th>İlk Satış (%5)</th>
                                <th>Sisteme Katılan Müşteri Sayısı (%4)</th>
                                <th>Genel Puan</th>
                            </tr>
                            </thead>
                            <tbody>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                            </div>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>
        <div class="col-4">

            <!-- ekip satışları -->
            <div class="card mb-3">

                <div class="card-body fixed-table-card">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">EKİP SATIŞLARI (Bu Yıl)</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="admins-table" class="table table-striped table-borderless mb-2px small text-nowrap">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Ad Soyad</th>
                                <th>Satış Adedi</th>
                                <th>Tutar</th>
                            </tr>
                            </thead>
                            <tbody>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                            </div>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>
        <div class="col-4">

            <!-- en yüksek rakamlı satışlar -->
            <div class="card mb-3">

                <div class="card-body fixed-table-card">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">EN YÜKSEK RAKAMLI SATIŞLAR (Son 90 Gün)</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="best-sales-by-price-table" class="table table-striped table-borderless mb-2px small text-nowrap">
                            <thead>
                            <tr>
                                <th>Satış</th>
                                <th>Tutar</th>
                            </tr>
                            </thead>
                            <tbody>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                            </div>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>
        <div class="col-4">

            <!-- en karlı satışlar 90 gün -->
            <div class="card mb-3">

                <div class="card-body fixed-table-card">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">EN KARLI SATIŞLAR (Son 90 Gün)</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="best-sales-by-profit-table" class="table table-striped table-borderless mb-2px small text-nowrap">
                            <thead>
                            <tr>
                                <th>Satış</th>
                                <th>Kar Oranı</th>
                            </tr>
                            </thead>
                            <tbody>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                            </div>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>
        <div class="col-4">

            <!-- siparişi olmayan müşteriler (zamansal) -->
            <div class="card mb-3">

                <div class="card-body fixed-table-card">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">UZUN SÜREDİR SİPARİŞİ OLMAYAN MÜŞTERİLER (Zamansal)</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="customer-not-sale-timely-table" class="table table-striped table-fixed table-borderless mb-2px small text-nowrap">
                            <thead>
                            <tr>
                                <th style="max-width: 30px;">#</th>
                                <th>Müşteri</th>
                                <th class="text-nowrap">Son Sipariş</th>
                            </tr>
                            </thead>
                            <tbody>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                            </div>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>
        <div class="col-4">

            <!-- siparişi olmayan müşteriler -->
            <div class="card mb-3">

                <div class="card-body fixed-table-card">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">HİÇ SİPARİŞİ OLMAYAN MÜŞTERİLER</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="customer-not-sale-table" class="table table-striped table-fixed table-borderless mb-2px small text-nowrap">
                            <thead>
                            <tr>
                                <th style="max-width: 30px;">#</th>
                                <th>Müşteri</th>
                            </tr>
                            </thead>
                            <tbody>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                            </div>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>
        <div class="col-4">

            <!-- en çok satılan ürünler -->
            <div class="card mb-3">

                <div class="card-body fixed-table-card">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">EN ÇOK SATILAN ÜRÜNLER</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="top-products-table" class="table table-striped table-borderless mb-2px small text-nowrap">
                            <thead>
                            <tr>
                                <th>Ürün Adı</th>
                                <th>Adet</th>
                            </tr>
                            </thead>
                            <tbody>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                            </div>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>

            </div>

        </div>


        <div class="col-12">

            <!-- sipariş durum değişiklikleri -->
            <div class="card mb-3">

                <div class="card-body fixed-table-card">

                    <div class="d-flex fw-bold small mb-3">
                        <span class="flex-grow-1">SON SİPARİŞ İŞLEMLERİ</span>
                        <a href="#" data-toggle="card-expand"
                           class="text-white text-muted text-decoration-none"><i class="bi bi-fullscreen"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table id="sales-history-table" class="table table-striped table-borderless mb-2px small text-nowrap">
                            <thead>
                            <tr>
                                <th>Müşteri</th>
                                <th>Kullanıcı</th>
                                <th>İşlem Tarihi</th>
                                <th class="text-right">Önceki Durum</th>
                                <th><i class="bi bi-arrow-90deg-right"></i> Güncel Durum</th>
                            </tr>
                            </thead>
                            <tbody>
                            <div class="spinners">
                                <div class="spinner spinner-grow spinner-grow-1 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-2 m-1 text-dark"></div>
                                <div class="spinner spinner-grow spinner-grow-3 m-1 text-dark"></div>
                            </div>
                            </tbody>
                        </table>
                    </div>

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


@include('include.footer')
