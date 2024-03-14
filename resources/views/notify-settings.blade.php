@include('include.header')
<?php
$extra_js='
<script src="services/notify-settings.js"></script>
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
                        Bildirim Ekle
                    </h1>
                </div>
            </div>

            <form method="post" action="#" id="add_notify_form">
                <div class="row">

                    <div class="col-md-12">
                        <div class="card border-theme mb-3">
                            <div class="card-body">
                                <div class="row p-3">
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Sipariş Durumu</label>
                                        <select class="form-control" id="add_notify_status_id" required>

                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Yetki</label>
                                        <select class="form-control" id="add_notify_role_id">

                                        </select>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">Personel</label>
                                        <select name="add_notify_staff_id[]" class="form-control form-select select2" multiple="multiple" id="add_notify_staff_id">
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label d-block">Uyarı Türü Seçiniz</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" value="" id="add_notify_to_notification" />
                                            <label class="form-check-label" for="add_notify_to_notification">Bildirim</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" value="" id="add_notify_to_mail" />
                                            <label class="form-check-label" for="add_notify_to_mail">Mail</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Bildirim Mesajı</label>
                                        <input type="text" class="form-control" id="add_notify_message">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <button type="submit" class="btn btn-theme w-100">Kaydet</button>
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
            </form>

            <div class="row">
                <div class="col-md-12">
                    <h1 class="page-header">
                        Durum Bildirimleri
                    </h1>
                </div>
            </div>

            <table id="notify-datatable" class="table table-bordered nowrap key-buttons border-bottom">
                <thead>
                <tr>
                    <th class="border-bottom-0" data-priority="1">N#</th>
                    <th class="border-bottom-0">Sipariş Durumu</th>
                    <th class="border-bottom-0">Rol</th>
                    <th class="border-bottom-0">Personel</th>
                    <th class="border-bottom-0">Bildirim Mesajı</th>
                    <th class="border-bottom-0">Bildirim</th>
                    <th class="border-bottom-0">Mail</th>
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
<div class="modal modal-cover fade" id="updateNotifySettingModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bildirim Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="#" id="update_notify_form">
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" value="" class="form-control" id="update_notify_id" />
                            <label class="form-label">Sipariş Durumu</label>
                            <select class="form-control" id="update_notify_status_id" required>

                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Yetki</label>
                            <select class="form-control" id="update_notify_role_id">

                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Personel</label>
                            <select name="update_notify_staff_id[]" class="form-control form-select select2" multiple="multiple" id="update_notify_staff_id">
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label d-block">Uyarı Türü Seçiniz</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" value="" id="update_notify_to_notification" />
                                <label class="form-check-label" for="update_notify_to_notification">Bildirim</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" value="" id="update_notify_to_mail" />
                                <label class="form-check-label" for="update_notify_to_mail">Mail</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Bildirim Mesajı</label>
                            <input type="text" class="form-control" id="update_notify_message">
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
