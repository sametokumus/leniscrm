@include('include.header')
<?php
$extra_js='
<script src="services/email-layouts.js"></script>
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
                        E-Posta Şablonu Oluştur
                    </h1>
                </div>
            </div>

            <form method="post" action="#" id="add_layout_form">
                <div class="row">

                    <div class="col-md-12">
                        <div class="card border-theme mb-3">
                            <div class="card-body">
                                <div class="row p-3">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Şablon Adı</label>
                                        <input type="text" class="form-control" id="add_layout_name">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Konu</label>
                                        <input type="text" class="form-control" id="add_layout_subject">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <textarea name="text" class="summernote" id="add_layout_text" title="Contents"></textarea>
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
                        Şablonlar
                    </h1>
                </div>
            </div>

            <table id="layout-datatable" class="table table-bordered nowrap key-buttons border-bottom">
                <thead>
                <tr>
                    <th class="border-bottom-0" data-priority="1">N#</th>
                    <th class="border-bottom-0">Şablon İsmi</th>
                    <th class="border-bottom-0">Konu</th>
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
<div class="modal modal-cover fade" id="updateLayoutModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">E-Posta Şablonu Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="#" id="update_layout_form">
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" value="" class="form-control" id="update_layout_id" />
                            <label class="form-label">Şablon Adı</label>
                            <input type="text" class="form-control" id="update_layout_name">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Konu</label>
                            <input type="text" class="form-control" id="update_layout_subject">
                        </div>
                        <div class="col-md-12 mb-3">
                            <textarea name="text" class="summernote" id="update_layout_text" title="Contents"></textarea>
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
