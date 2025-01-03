@include('include.header')
<?php
$extra_js='
<script src="services/pending-accounting.js"></script>
';
?>

    <!--app-content open-->
<div class="main-content app-content">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid overflow-auto">

            <div class="row">
                <div class="col-md-12">
                    <h1 class="page-header">
                        Fatura Bekleyen Satışlar
                    </h1>
                </div>
            </div>

            <table id="sales-datatable" class="table table-bordered nowrap key-buttons border-bottom">
                <thead>
                <tr>
                    <th class="border-bottom-0 bg-dark-100" data-priority="1">N#</th>
                    <th class="border-bottom-0 bg-dark-100">Satış Kodu</th>
                    <th class="border-bottom-0 bg-dark-100">Müşteri</th>
                    <th class="border-bottom-0 bg-dark-100">Satış Temsilcisi</th>
                    <th class="border-bottom-0">Müşteri Yetkilisi</th>
                    <th class="border-bottom-0">Ürün Adedi</th>
                    <th class="border-bottom-0">Teklif Fiyatı</th>
                    <th class="border-bottom-0">Para Birimi</th>
                    <th class="border-bottom-0">Durum</th>
                    <th class="border-bottom-0 d-none">Talep Tarihi</th>
                    <th class="border-bottom-0">Son Güncelleme</th>
                    <th class="border-bottom-0">Son İşlem</th>
                    <th class="border-bottom-0" data-priority="2">İşlem</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

        </div>
        <!-- CONTAINER END -->
    </div>
</div>
<!--app-content close-->

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
                            <input type="hidden" class="form-control" id="update_sale_id" required>
                            <select name="update_sale_status" id="update_sale_status" class="form-control form-control-md" required>

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

<div class="modal modal-cover fade" id="addCancelNoteModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">İPTAL SEBEBİ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="#" id="add_cancel_note_form">
                <div class="modal-body">
                    <div class="row mb-4">
                        <label class="col-md-3 form-label">Açıklama :</label>
                        <div class="col-md-9">
                            <input type="hidden" class="form-control" id="cancel_sale_id" required>
                            <textarea class="form-control" rows="3" id="cancel_sale_note" placeholder="Not" required=""></textarea>
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

<div class="modal modal-cover fade" id="addSaleNoteModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SİPARİŞ NOTLARI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
                <div class="modal-body">

                    <div class="row mb-3">
                        <div class="table-responsive">
                            <table id="sales-notes-table" class="table table-striped table-borderless mb-2px small">
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <form method="post" action="#" id="add_sale_note_form">
                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <input type="hidden" class="form-control" id="add_note_sale_id" required>
                                <label class="form-label">Yeni Not Ekle</label>
                                <textarea name="text" class="summernote" id="add_sale_note_description" title="Contents"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <button type="submit" class="btn btn-outline-theme">Kaydet</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">Kapat</button>
                </div>
        </div>
    </div>
</div>

<div class="modal modal-cover fade" id="deleteSaleModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SİPARİŞ SİLME</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
                <div class="modal-body">
                    <form method="post" action="#" id="delete_sale_form">
                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <input type="hidden" class="form-control" id="delete_sale_id" required>
                                <label class="form-label">Siparişi silmek istediğinize emin misiniz?</label>
                            </div>
                            <div class="col-md-12 mb-3">
                                <button type="submit" class="btn btn-danger">Sil</button>
                            </div>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</div>

@include('include.footer')
