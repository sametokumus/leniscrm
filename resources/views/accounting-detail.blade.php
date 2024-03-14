@include('include.header')
<?php
$extra_js='
<script src="plugins/jvectormap-next/jquery-jvectormap.min.js"></script>
<script src="plugins/jvectormap-next/jquery-jvectormap-world-mill.js"></script>
<script src="plugins/apexcharts/dist/apexcharts.min.js"></script>
<script src="js/demo/dashboard.demo.js"></script>
<script src="services/accounting-detail.js"></script>
<script>
$(".datepicker").datepicker({
    autoclose: true,
    format: "dd-mm-yyyy"
});
$(".timepicker").timepicker({
    minuteStep: 15,
    showMeridian: false,
    template: false
});
</script>
';
?>

<div id="content" class="app-content">

    <div class="row">

        <div class="col-xl-3 col-lg-6 mb-3">

            <div class="card h-100">

                <div class="card-body d-flex align-items-center text-dark m-5px bg-white bg-opacity-15">
                    <div class="flex-fill">
                        <span class="flex-grow-1 fw-600 fs-12px" id="sale-code"></span>
                        <h3 id="customer-name"></h3>
                        <div id="customer-employee"></div>
                        <div id="owner-employee"></div>
                    </div>
                    <div class="opacity-5">
                        <i class="fa fa-gem fa-4x"></i>
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

        <div class="col-xl-3 col-lg-6 mb-3">

            <div class="card h-100">

                <div class="card-body d-flex align-items-center text-dark m-5px bg-white bg-opacity-15">
                    <div class="flex-fill">
                        <span class="flex-grow-1 fw-600 fs-12px">Toplam Satış Tutarı</span>
                        <h3 id="total-price"></h3>
                        <div id="advance-price">&nbsp;</div>
                        <div>&nbsp;</div>
                    </div>
                    <div class="opacity-5">
                        <i class="fa fa-wallet fa-4x"></i>
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

        <div class="col-xl-3 col-lg-6 mb-3">

            <div class="card h-100">

                <div class="card-body d-flex align-items-center text-dark m-5px bg-white bg-opacity-15">
                    <div class="flex-fill">
                        <span class="flex-grow-1 fw-600 fs-12px">Kalan Ödeme Tutarı</span>
                        <h3 id="remaining-price"></h3>
                        <div id="packing-message">&nbsp;</div>
                        <div>&nbsp;</div>
                    </div>
                    <div class="opacity-5">
                        <i class="fa fa-wallet fa-4x"></i>
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

        <div class="col-xl-3 col-lg-6 mb-3">

            <div class="card h-100">

                <div class="card-body d-flex align-items-center text-dark m-5px bg-white bg-opacity-15">
                    <div class="flex-fill">
                        <span class="flex-grow-1 fw-600 fs-12px">Talep Tarihi</span>
                        <h3 id="sale-date"></h3>
                        <div>&nbsp;</div>
                        <div>&nbsp;</div>
                    </div>
                    <div class="opacity-5">
                        <i class="fa fa-calendar-day fa-4x"></i>
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
                    <table id="payments" class="table table-bordered text-nowrap key-buttons border-bottom w-100">
                        <thead>
                        <tr>
                            <th class="border-bottom-0">N#</th>
                            <th class="border-bottom-0 d-none">ID</th>
                            <th class="border-bottom-0">Ürün Kalemi</th>
                            <th class="border-bottom-0">Fiyat</th>
                            <th class="border-bottom-0">Para Birimi</th>
                            <th class="border-bottom-0">Ödeme Türü</th>
                            <th class="border-bottom-0">Ödeme Yöntemi</th>
                            <th class="border-bottom-0">Fatura Numarası</th>
                            <th class="border-bottom-0">Fatura Tarihi</th>
                            <th class="border-bottom-0">Vade (Gün)</th>
                            <th class="border-bottom-0">Vade Tarihi</th>
                            <th class="border-bottom-0">Ödenecek Tutar</th>
                            <th class="border-bottom-0">KDV Oranı</th>
                            <th class="border-bottom-0">KDV Tutarı</th>
                            <th class="border-bottom-0">Toplam Tutar</th>
                            <th class="border-bottom-0">Para Birimi</th>
                            <th class="border-bottom-0">Durum</th>
                            <th class="border-bottom-0">İrsaliye</th>
                            <th class="border-bottom-0"></th>
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

    <div class="modal modal-cover fade" id="addPaymentModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ödeme Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="#" id="add_payment_form">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Ödeme Türü :</label>
                            <div class="col-md-9">
                                <select class="form-control" id="add_payment_payment_type">
                                </select>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Ödeme Yöntemi :</label>
                            <div class="col-md-9">
                                <select class="form-control" id="add_payment_payment_method">
                                </select>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Fatura Numarası :</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="add_payment_invoice_number">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Fatura Tarihi :</label>
                            <div class="col-md-9">
                                <div class="btn-list mb-2">
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentInvoiceDateToday()">
                                        <span class="fe fe-refresh-cw">Bugün</span>
                                    </button>
                                </div>
                                <input type="text" class="form-control datepicker" id="add_payment_invoice_date" placeholder="dd-mm-yyyy" />
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Vade (Gün) :</label>
                            <div class="col-md-9">
                                <div class="btn-list mb-2">
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(1)">
                                        <span class="fe fe-refresh-cw">1</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(3)">
                                        <span class="fe fe-refresh-cw">3</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(5)">
                                        <span class="fe fe-refresh-cw">5</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(7)">
                                        <span class="fe fe-refresh-cw">7</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(15)">
                                        <span class="fe fe-refresh-cw">15</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(20)">
                                        <span class="fe fe-refresh-cw">20</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(30)">
                                        <span class="fe fe-refresh-cw">30</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(45)">
                                        <span class="fe fe-refresh-cw">45</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(60)">
                                        <span class="fe fe-refresh-cw">60</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentPaymentTermWithButton(90)">
                                        <span class="fe fe-refresh-cw">90</span>
                                    </button>
                                </div>
                                <input type="number" value="" class="form-control" id="add_payment_payment_term" min="1">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Vade Tarihi :</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control datepicker" id="add_payment_due_date" placeholder="dd-mm-yyyy" />
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Ödenecek Tutarı :</label>
                            <div class="col-md-9">
                                <input type="hidden" class="form-control" id="add_payment_packing_list_id">
                                <input type="text" class="form-control" id="add_payment_payment_price" readonly>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Para Birimi :</label>
                            <div class="col-md-9">
{{--                                <select class="form-control" id="add_payment_currency" required readonly="">--}}
{{--                                    <option value="TRY">TRY</option>--}}
{{--                                    <option value="EUR">EUR</option>--}}
{{--                                    <option value="USD">USD</option>--}}
{{--                                    <option value="GBP">GBP</option>--}}
{{--                                </select>--}}
                                <input type="text" class="form-control" id="add_payment_currency" readonly>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">KDV Oranı :</label>
                            <div class="col-md-9">
                                <div class="btn-list mb-2">
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentTaxWithButton(0)">
                                        <span class="fe fe-refresh-cw">0</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentTaxWithButton(1)">
                                        <span class="fe fe-refresh-cw">1</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentTaxWithButton(8)">
                                        <span class="fe fe-refresh-cw">8</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentTaxWithButton(10)">
                                        <span class="fe fe-refresh-cw">10</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentTaxWithButton(18)">
                                        <span class="fe fe-refresh-cw">18</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="addPaymentTaxWithButton(20)">
                                        <span class="fe fe-refresh-cw">20</span>
                                    </button>
                                </div>
                                <input type="number" value="" class="form-control" id="add_payment_tax_rate" min="0" readonly>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">KDV Tutarı :</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="add_payment_price_with_tax" readonly>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Toplam Tutar :</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="add_payment_price_total" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-theme">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal modal-cover fade" id="updatePaymentModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ödeme Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="#" id="update_payment_form">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Ödeme Türü :</label>
                            <div class="col-md-9">
                                <input type="hidden" class="form-control" id="update_payment_id">
                                <select class="form-control" id="update_payment_payment_type">
                                </select>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Ödeme Yöntemi :</label>
                            <div class="col-md-9">
                                <select class="form-control" id="update_payment_payment_method">
                                </select>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Fatura Numarası :</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="update_payment_invoice_number">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Fatura Tarihi :</label>
                            <div class="col-md-9">
                                <div class="btn-list mb-2">
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentInvoiceDateToday()">
                                        <span class="fe fe-refresh-cw">Bugün</span>
                                    </button>
                                </div>
                                <input type="text" class="form-control datepicker" id="update_payment_invoice_date" placeholder="dd-mm-yyyy" />
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Vade (Gün) :</label>
                            <div class="col-md-9">
                                <div class="btn-list mb-2">
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(1)">
                                        <span class="fe fe-refresh-cw">1</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(3)">
                                        <span class="fe fe-refresh-cw">3</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(5)">
                                        <span class="fe fe-refresh-cw">5</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(7)">
                                        <span class="fe fe-refresh-cw">7</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(15)">
                                        <span class="fe fe-refresh-cw">15</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(20)">
                                        <span class="fe fe-refresh-cw">20</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(30)">
                                        <span class="fe fe-refresh-cw">30</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(45)">
                                        <span class="fe fe-refresh-cw">45</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(60)">
                                        <span class="fe fe-refresh-cw">60</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentPaymentTermWithButton(90)">
                                        <span class="fe fe-refresh-cw">90</span>
                                    </button>
                                </div>
                                <input type="number" value="" class="form-control" id="update_payment_payment_term" min="1">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Vade Tarihi :</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control datepicker" id="update_payment_due_date" placeholder="dd-mm-yyyy" />
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Ödenecek Tutar :</label>
                            <div class="col-md-9">
                                <input type="hidden" class="form-control" id="update_payment_packing_list_id">
                                <input type="text" class="form-control" id="update_payment_payment_price" readonly>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Para Birimi :</label>
                            <div class="col-md-9">
{{--                                <select class="form-control" id="update_payment_currency" required>--}}
{{--                                    <option value="TRY">TRY</option>--}}
{{--                                    <option value="EUR">EUR</option>--}}
{{--                                    <option value="USD">USD</option>--}}
{{--                                    <option value="GBP">GBP</option>--}}
{{--                                </select>--}}
                                <input type="text" class="form-control" id="update_payment_currency" readonly>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">KDV Oranı :</label>
                            <div class="col-md-9">
                                <div class="btn-list mb-2">
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentTaxWithButton(0)">
                                        <span class="fe fe-refresh-cw">0</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentTaxWithButton(1)">
                                        <span class="fe fe-refresh-cw">1</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentTaxWithButton(8)">
                                        <span class="fe fe-refresh-cw">8</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentTaxWithButton(10)">
                                        <span class="fe fe-refresh-cw">10</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentTaxWithButton(18)">
                                        <span class="fe fe-refresh-cw">18</span>
                                    </button>
                                    <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="updatePaymentTaxWithButton(20)">
                                        <span class="fe fe-refresh-cw">20</span>
                                    </button>
                                </div>
                                <input type="number" value="" class="form-control" id="update_payment_tax_rate" min="0" readonly>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">KDV Tutarı :</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="update_payment_price_with_tax" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-theme">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal modal-cover fade" id="updateStatusModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">DURUM GÜNCELLE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="#" id="update_status_form">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <label class="col-md-3 form-label">Durum :</label>
                            <div class="col-md-9">
                                <input type="hidden" class="form-control" id="update_status_payment_id" required>
                                <select name="update_payment_status" id="update_payment_status" class="form-control form-control-md" required>
                                    <option value="1">Ödeme bekleniyor</option>
                                    <option value="2">Ödeme tamamlandı</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-theme">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@include('include.footer')
