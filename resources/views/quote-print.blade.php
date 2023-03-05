@include('include.header')
<?php
$extra_js='
<script src="services/quote-print.js"></script>
';
?>

    <!--app-content open-->
<div class="main-content app-content printable">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="row justify-content-center mb-3 no-print">
                <div class="col-md-7">
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

            <div class="row justify-content-center">
                <div class="col-md-7 bg-white p-md-50">

                    <div class="wrapper">
                        <!-- Main content -->
                        <section id="quote-print" class="print-color">
                            <!-- title row -->
                            <div class="row">
                                <div class="col-6">
                                    <div class="contact-col">
                                        <h6>{{__('Supplier')}}</h6>
                                        <address>

                                        </address>
                                    </div>
                                    <h1 class="page-header">
                                        {{__('Offer')}}
                                    </h1>
                                    <div class="col-sm-6 buyer-col">
                                        {{--                                    <h6>Supplier</h6>--}}
                                        <address>
                                            <span id="buyer_name"></span><br>
                                            <span id="buyer_address"></span><br>
                                            <span id="payment_term"></span><br>
                                            <span id="lead_time"></span><br>
                                            <span id="delivery_term"></span><br>
                                            <span id="country_of_destination"></span><br>
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
                                            <th>#</th>
                                            <th>{{__('Ref. Code')}}</th>
                                            <th>{{__('Product Name')}}</th>
                                            <th>{{__('Qty')}}</th>
                                            <th>{{__('Pcs. Price')}}</th>
                                            <th>{{__('Total Price')}}</th>
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
                                    <p style="margin-top: 15px; margin-bottom: 0;">{{__('Note')}}:</p>
                                    <p id="note" class="text-muted">

                                    </p>
                                </div>
                            </div>
                        </section>
                        <div class="row">
                            <div class="col-12">
                                <button onclick="openUpdateQuoteModal();" class="btn btn-theme btn-block w-100 mb-2 no-print">Bilgileri Güncelle</button>
                                <button onclick="printOffer();" class="btn btn-theme btn-block w-100 no-print">Yazdır</button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 mt-3">
                                <div id="print-footer">

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <!-- CONTAINER END -->
    </div>
</div>
<!--app-content close-->

<div class="modal modal-cover fade" id="updateQuoteModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">BİLGİ GÜNCELLEME</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="#" id="update_quote_form">
                <div class="modal-body">
                    <div class="row mb-4">
                        <input type="hidden" class="form-control" id="update_quote_id">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Payment Terms</label>
                            <input type="text" class="form-control" id="update_quote_payment_term">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Lead Time</label>
                            <input type="text" class="form-control" id="update_quote_lead_time">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Delivery Terms</label>
                            <input type="text" class="form-control" id="update_quote_delivery_term">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Country of Destination</label>
                            <input type="text" class="form-control" id="update_quote_country_of_destination">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Freight Price</label>
                            <input type="text" class="form-control" id="update_quote_freight">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Note</label>
                            <textarea class="form-control" id="update_quote_note"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-outline-theme">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('include.footer')
