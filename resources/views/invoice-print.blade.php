@include('include.header')
<?php
$extra_js='
<script src="services/invoice-print.js"></script>
';
?>

    <!--app-content open-->
<div class="main-content app-content">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid printable">

            <div class="row justify-content-center mb-3 no-print">
                <div class="col-xl-9 col-lg-12 col-md-12">
                    <div class="row mb-3 d-none" id="no-pdf">
                        <div class="col-12">
                            <button id="generatePdf" onclick="generatePDF();" class="btn btn-theme btn-block w-100 no-print">PDF Oluştur</button>
                        </div>
                    </div>
                    <div class="row mb-3 d-none" id="has-pdf">
                        <div class="col-6">
                            <a id="showPdf" target="_blank" href="" class="btn btn-theme btn-block w-100 no-print">Mevcut PDF'i Görüntüle</a>
                        </div>
                        <div class="col-6">
                            <button id="generatePdf" onclick="generatePDF();" class="btn btn-theme btn-block w-100 no-print">Yeni PDF Oluştur</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Dil</label>
                            <select class="form-control" id="lang">
                                <option value="tr" @if(app()->getLocale() == 'tr') selected="selected" @endif>Türkçe</option>
                                <option value="en" @if(app()->getLocale() == 'en') selected="selected" @endif>English</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Firma</label>
                            <select class="form-control" id="owners" onchange="changeOwner();">

                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mb-3 no-print">

                <div class="col-xl-9 col-lg-12 col-md-12">
                    <div class="card border-theme mb-3">
                        <div class="card-body p-3">


                            <form method="post" action="#" id="bank_info_form">
                                <div class="row mb-4">
                                    <h5 class="px-2">
                                        Banka Bilgisi
                                    </h5>
                                    <div class="col-sm-12">
                                        <select name="select_bank_info" class="form-control form-select" id="select_bank_info" required>
                                            <option value="0">Bilgi Yok</option>
                                        </select>
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

            <div class="row justify-content-center mb-3 no-print">

                <div class="col-xl-9 col-lg-12 col-md-12">
                    <div class="card border-theme mb-3">
                        <div class="card-body p-3">


                            <form method="post" action="#" id="update_detail_form">
                                <div class="row mb-4">
                                    <h5 class="px-2">
                                        Fatura Detayları
                                    </h5>
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label">Kargo Bedeli</label>
                                        <input type="text" class="form-control" id="update_sale_shipping_price">
                                    </div>
                                    <div class="col-sm-12">
                                        <label class="form-label">Fatura Notu</label>
                                        <textarea name="text" class="summernote" id="update_sale_note" title="Contents"></textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <button type="submit" class="btn btn-outline-theme d-block w-100">Kaydet</button>
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

            <div class="row justify-content-center mb-3 no-print">

                <div class="col-xl-9 col-lg-12 col-md-12">
                    <div class="card border-theme mb-3">
                        <div class="card-body p-3">

                            <form method="post" action="#" id="update_quote_form">
                                <div class="row mb-4">
                                    <h5 class="px-2">
                                        Teklif Detayları
                                    </h5>
                                    <input type="hidden" class="form-control" id="update_quote_id">
                                    <div class="col-sm-3 mb-3">
                                        <label class="form-label">Teklif Geçerlilik Tarihi</label>
                                        <input type="text" class="form-control datepicker" id="update_quote_expiry_date" placeholder="dd-mm-yyyy" />
                                    </div>
                                    <div class="col-sm-3 mb-3">
                                        <label class="form-label">Payment Term</label>
                                        <select class="form-control" id="update_quote_payment_term" onchange="checkAdvancePrice();">

                                        </select>
                                    </div>
                                    <div class="col-sm-3 mb-3">
                                        <label class="form-label">Avans Tutarı</label>
                                        <input type="text" class="form-control" id="update_quote_advance_price" value="0,00" readonly>
                                    </div>
                                    <div class="col-sm-3 mb-3">
                                        <label class="form-label">Delivery Terms</label>
                                        <select class="form-control" id="update_quote_delivery_term">

                                        </select>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <label class="form-label">Insurance</label>
                                        <input type="text" class="form-control" id="update_quote_lead_time">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <label class="form-label">Country of Destination</label>
                                        <input type="text" class="form-control" id="update_quote_country_of_destination">
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <label class="form-label">Freight Price</label>
                                        <input type="text" class="form-control" id="update_quote_freight">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Note</label>
                                        <textarea name="text" class="summernote" id="update_quote_note" title="Contents"></textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <button type="submit" class="btn btn-outline-theme d-block w-100">Kaydet</button>
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

            <div class="row justify-content-center d-none">
                <div class="col-xl-9 col-lg-12 col-md-12 bg-white p-md-50">

                    <div class="wrapper">

                        <table class="table table-borderless">
                            <tbody>
                            <tr>
                                <td class="border-0">

                                    <!-- Main content -->
                                    <section id="quote-print" class="print-color">
                                        <!-- title row -->
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="contact-col">
            {{--                                        <h6>{{__('Supplier')}}</h6>--}}
                                                    <address>

                                                    </address>
                                                </div>
                                                <h1 class="page-header">
                                                    {{__('Invoice')}}
                                                </h1>
                                                <div class="buyer-col">
{{--                                                    <h6>{{__('Customer')}}</h6>--}}
                                                    <address>
                                                        <span id="buyer_name"></span><br>
                                                        <span id="buyer_registration_number"></span><br>
                                                        <span id="buyer_address"></span><br>
                                                        <span id="buyer_phone"></span><br>
                                                        <span id="buyer_email" class="d-block"></span><br>
                                                        <span id="payment_term"></span><br>
                                                    </address>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h2 class="logo-header">
                                                    <div id="logo"></div>
                                                    <small class="date"></small>
                                                    <div class="offer-id"></div>
                                                </h2>
                                            </div>
                                            <!-- /.col -->
                                        </div>
                                        <!-- info row -->

                                        <!-- Table row -->
                                        <div class="row">
                                            <div class="col-12 table-responsive">
                                                <table id="sale-detail" class="table table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th class="text-center">N#</th>
                                                        <th class="text-center">{{__('Ref. Code')}}</th>
                                                        <th class="text-center">{{__('Product Name')}}</th>
                                                        <th class="text-center">{{__('Qty')}}</th>
                                                        <th class="text-center">{{__('Unit')}}</th>
                                                        <th class="text-center">{{__('Unit Price')}}</th>
                                                        <th class="text-center">{{__('Total Price')}}</th>
{{--                                                        <th class="text-center">{{__('Lead Time')}}</th>--}}
                                                    </tr>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                </table>
                                            </div>
                                            <!-- /.col -->
                                        </div>
                                        <!-- /.row -->

                                        <div class="row">
                                            <div class="col-md-12">
                                                <p style="margin-top: 15px; margin-bottom: 0;">
                                                    <strong>{{__('Note')}}:</strong>
                                                    <button id="addNoteBtn" type="button" class="btn btn-outline-secondary btn-sm no-print d-none" onclick="openAddNoteModal();">Not Ekle</button>
                                                    <button id="updateNoteBtn" type="button" class="btn btn-outline-secondary btn-sm no-print d-none" onclick="openUpdateNoteModal();">Not Güncelle</button>
                                                </p>
                                                <div id="note" class="text-muted">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p style="margin-top: 15px; margin-bottom: 0;">
                                                    <strong>{{__('Bank Details')}}:</strong>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm no-print" onclick="openAddBankInfoModal();">Banka Bilgisi Ekle</button>
                                                </p>
                                                <div id="bank-details" class="text-muted">

                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                    <div class="row">
                                        <div class="col-12 mt-3">
                                            <button onclick="openUpdateDetailModal();" class="btn btn-theme btn-block w-100 mb-2 no-print">Bilgileri Güncelle</button>
                                            <button onclick="printOffer();" class="btn btn-theme btn-block w-100 no-print">Yazdır</button>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <div id="print-footer">

                                            </div>
                                        </div>
                                    </div>


                                </td>
                            </tr>
                            </tbody>

                            <tfoot>
                            <tr>
                                <td class="border-0">
                                    <div class="footer-spacer"></div>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>

        </div>
        <!-- CONTAINER END -->
    </div>
</div>
<!--app-content close-->

@include('include.footer')
