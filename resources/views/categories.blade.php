@include('include.header')
<?php
$extra_js='
<script src="services/categories.js"></script>
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
                        Ürün Grupları
                    </h1>
                </div>
            </div>

            <form method="post" action="#" id="add_category_form">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-theme mb-3">
                            <div class="card-header">
                                <h5 class="card-title">Ürün Grubu Ekle</h5>
                            </div>
                            <div class="card-body">
                                <div class="row p-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ana Grup</label>
                                        <select name="parent_category" class="form-control form-select select2" id="parent_category" required>

                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Adı</label>
                                        <input type="text" class="form-control" id="category_name">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">&nbsp;</label>
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
                <!-- col -->
                <div class="col-lg-4">
                    <ul id="category_view">

                    </ul>
                </div>
                <!-- /col -->
            </div>

        </div>
        <!-- CONTAINER END -->
    </div>
</div>
<!--app-content close-->

<div class="modal modal-cover fade" id="updateCategoryModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ÜRÜN GRUBU GÜNCELLEME</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="#" id="update_category_form">
                <div class="modal-body">
                    <div class="row mb-4">
                        <label class="col-md-3 form-label">Ana Kategori :</label>
                        <div class="col-md-9">
                            <select name="update_parent_category" class="form-control form-select" id="update_parent_category" required>

                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-md-3 form-label">Adı :</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="update_category_name" required>
                            <input type="hidden" class="form-control" id="update_category_id" required>
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
