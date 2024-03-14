<?php
ob_start();
session_start();
if(isset($_SESSION['userLogin'])){
    if($_SESSION['userLogin'] != false && $_SESSION['userLogin'] != null){
        $isLogin = true;
    }else{
        $isLogin = false;
    }
}else{
    $isLogin = true;
}

$extra_js="";
?>

    <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <title>S-CRM | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content=""/>
    <meta name="author" content=""/>
    <meta name="current-locale" content="{{ app()->getLocale() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

{{--    <base href="http://127.0.0.1:8000/">--}}
    <base href="/">
    <link href="css/vendor.min.css" rel="stylesheet"/>
    <link href="css/app.min.css" rel="stylesheet"/>
    <!-- required css -->
    <link href="plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="plugins/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet" />
    <link href="plugins/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css" rel="stylesheet" />

    <link href="https://cdn.datatables.net/select/1.4.0/css/select.dataTables.min.css" />
    <link href="https://cdn.datatables.net/rowreorder/1.2.8/css/rowReorder.dataTables.min.css" rel="stylesheet" />
    <link href="plugins/datatables.net/extensions/Editor/css/editor.dataTables.min.css" rel="stylesheet" />

    <link href="plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" />

    <link href="plugins/summernote/dist/summernote-lite.css" rel="stylesheet" />
    <link href="plugins/select-picker/dist/picker.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="plugins/tag-it/css/jquery.tagit.css" rel="stylesheet">

    <link href="css/customize.css" rel="stylesheet"/>


    <link href="plugins/jvectormap-next/jquery-jvectormap.css" rel="stylesheet"/>

</head>
<body>

<div id="app" class="app">

    <div id="header" class="app-header no-print">

        <div class="desktop-toggler">
            <button type="button" class="menu-toggler" data-toggle-class="app-sidebar-collapsed"
                    data-dismiss-class="app-sidebar-toggled" data-toggle-target=".app">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>


        <div class="mobile-toggler">
            <button type="button" class="menu-toggler" data-toggle-class="app-sidebar-mobile-toggled"
                    data-toggle-target=".app">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>


        <div class="brand">
            <a href="/my-dashboard" class="brand-logo">
                <img src="/img/logo/semy-logo.png" class="img-responsive brand-img">
            </a>
        </div>


        <div class="menu">
            <div class="menu-item mx-1" id="header-btn">

            </div>
            <div class="menu-item mx-1">
                <button class="btn btn-theme btn-sm" type="button" onclick="triggerPipeman();"><span>Animasyon</span></button>
            </div>
            <div class="menu-item mx-1">
                <button class="btn btn-theme btn-sm" type="button" onclick="triggerPipeman2();"><span>Animasyon 2</span></button>
            </div>
            <div class="menu-item">
                <a href="/my-dashboard" id="header-notify" data-bs-display="static" class="menu-link">
                    <div class="menu-icon"><i class="bi bi-bell nav-icon"></i></div>
                    <span class="badge bg-danger d-none"></span>
                </a>
            </div>
            <div class="menu-item dropdown dropdown-mobile-full">
                <a href="#" data-bs-toggle="dropdown" data-bs-display="static" class="menu-link">
                    <div class="menu-link">
{{--                        <div class="menu-icon"><i class="bi bi-people nav-icon"></i></div>--}}
                        <div class="menu-img">
                            <img src="img/user/null-profile-picture.png" id="header_user_image" alt="Profile" height="60">
                        </div>
                        <div class="menu-text d-sm-block d-none mx-2" id="header_user_name"></div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end me-lg-3 fs-11px mt-1">
                    <a class="dropdown-item d-flex align-items-center" href="/my-dashboard">{{__('My Profile')}} <i
                            class="bi bi-people ms-auto text-theme fs-16px my-n1"></i></a>
{{--                    <a class="dropdown-item d-flex align-items-center" href="#">INBOX <i--}}
{{--                            class="bi bi-envelope ms-auto text-theme fs-16px my-n1"></i></a>--}}
{{--                    <a class="dropdown-item d-flex align-items-center" href="#">CALENDAR <i--}}
{{--                            class="bi bi-calendar ms-auto text-theme fs-16px my-n1"></i></a>--}}
{{--                    <a class="dropdown-item d-flex align-items-center" href="#">SETTINGS <i--}}
{{--                            class="bi bi-gear ms-auto text-theme fs-16px my-n1"></i></a>--}}
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item d-flex align-items-center navbar-logout" href="#">{{__('Logout')}} <i
                            class="bi bi-toggle-off ms-auto text-theme fs-16px my-n1"></i></a>
                </div>
            </div>
        </div>




    </div>


    <div id="sidebar" class="app-sidebar">

        <div class="app-sidebar-content" data-scrollbar="true" data-height="100%">

            <div class="menu">
                @if( Request::segment(1) != 'dashboard' )
                <div class="p-3 d-flex align-items-center">
                    <a href="javascript:window.history.back();" class="btn btn-warning btn-sm text-nowrap px-3 py-1"><i class="fa fa-arrow-left me-1 ms-n1"></i> Önceki Sayfa</a>
                </div>
                @endif

                <div class="menu-header">{{__('News Feed')}}</div>
                <div id="nav-my-dashboard" class="menu-item @if( Request::segment(1)== 'my-dashboard' ) active @endif">
                    <a href="/my-dashboard" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-person-fill"></i></span>
                        <span class="menu-text">{{__('My Profile')}}</span>
                    </a>
                </div>
                <div id="nav-dashboard" class="d-none menu-item @if( Request::segment(1)== 'dashboard' ) active @endif">
                    <a href="/dashboard" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-bar-chart-fill"></i></span>
                        <span class="menu-text">{{__('Dashboard')}}</span>
                    </a>
                </div>
                <div id="nav-staff-dashboard" class="d-none menu-item @if( Request::segment(1)== 'staff-dashboard' ) active @endif">
                    <a href="/staff-dashboard" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-people-fill"></i></span>
                        <span class="menu-text">{{__('Staff Profiles')}}</span>
                    </a>
                </div>
{{--                <div id="nav-news-feed" class="d-none menu-item @if( Request::segment(1)== 'news-feed' ) active @endif">--}}
{{--                    <a href="/news-feed" class="menu-link">--}}
{{--                        <span class="menu-icon"><i class="bi bi-columns-gap"></i></span>--}}
{{--                        <span class="menu-text">Haber Kaynağı</span>--}}
{{--                    </a>--}}
{{--                </div>--}}
                <div class="menu-header">{{__('Sale Management')}}</div>
                <div id="nav-new-request" class="d-none menu-item @if( Request::segment(1)== 'offer-request' ) active @endif">
                    <a href="/new-offer-request" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-gem"></i></span>
                        <span class="menu-text">Talep Oluştur</span>
                    </a>
                </div>
                <div id="nav-sales" class="d-none menu-item @if( Request::segment(1)== 'sales' ) active @endif">
                    <a href="/sales" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-gem"></i></span>
                        <span class="menu-text">Satışlar</span>
                    </a>
                </div>
                <div id="nav-sales-cancelled" class="d-none menu-item @if( Request::segment(1)== 'sales-cancelled' ) active @endif">
                    <a href="/sales-cancelled" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-gem"></i></span>
                        <span class="menu-text">İptal Edilen Satışlar</span>
                    </a>
                </div>
                <div id="nav-approved-sales" class="d-none menu-item @if( Request::segment(1)== 'approved-sales' ) active @endif">
                    <a href="/approved-sales" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-gem"></i></span>
                        <span class="menu-text">Onaylanan Satışlar</span>
                    </a>
                </div>

                    <div class="menu-header">Diğer İşlemler</div>

                <div class="menu-item has-sub @if( Request::segment(1)== 'customers' || Request::segment(1)== 'suppliers' || Request::segment(1)== 'potential-customers' ) active @endif">
                    <a href="#" class="menu-link">
                    <span class="menu-icon">
                    <i class="bi bi-gem"></i>
                    </span>
                        <span class="menu-text">Firma Yönetimi</span>
                        <span class="menu-caret"><b class="caret"></b></span>
                    </a>
                    <div class="menu-submenu">
                        <div id="nav-customers" class="d-none menu-item @if( Request::segment(1)== 'customers' ) active @endif">
                            <a href="/customers" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-gem"></i></span>
                                <span class="menu-text">Müşteriler</span>
                            </a>
                        </div>
                        <div id="nav-suppliers" class="d-none menu-item @if( Request::segment(1)== 'suppliers' ) active @endif">
                            <a href="/suppliers" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-gem"></i></span>
                                <span class="menu-text">Tedarikçiler</span>
                            </a>
                        </div>
                        <div id="nav-potential-customers" class="d-none menu-item @if( Request::segment(1)== 'potential-customers' ) active @endif">
                            <a href="/potential-customers" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-gem"></i></span>
                                <span class="menu-text">Potansiyel Müşteriler</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item has-sub @if( Request::segment(1)== 'accounting-dashboard' || Request::segment(1)== 'pending-accounting' || Request::segment(1)== 'ongoing-accounting' || Request::segment(1)== 'completed-accounting' ) active @endif">
                    <a href="#" class="menu-link">
                    <span class="menu-icon">
                    <i class="bi bi-cash"></i>
                    </span>
                        <span class="menu-text">Muhasebe Yönetimi</span>
                        <span class="menu-caret"><b class="caret"></b></span>
                    </a>
                    <div class="menu-submenu">
                        <div id="nav-accounting-dashboard" class="d-none menu-item @if( Request::segment(1)== 'accounting-dashboard' ) active @endif">
                            <a href="/accounting-dashboard" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-cash"></i></span>
                                <span class="menu-text">Muhasebe Dashboard</span>
                            </a>
                        </div>
                        <div id="nav-pending-accounting" class="d-none menu-item @if( Request::segment(1)== 'pending-accounting' ) active @endif">
                            <a href="/pending-accounting" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-cash"></i></span>
                                <span class="menu-text">Fatura Bekleyenler</span>
                            </a>
                        </div>
                        <div id="nav-ongoing-accounting" class="d-none menu-item @if( Request::segment(1)== 'ongoing-accounting' ) active @endif">
                            <a href="/ongoing-accounting" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-cash"></i></span>
                                <span class="menu-text">Bekleyen Ödemeler</span>
                            </a>
                        </div>
                        <div id="nav-completed-accounting" class="d-none menu-item @if( Request::segment(1)== 'completed-accounting' ) active @endif">
                            <a href="/completed-accounting" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-cash"></i></span>
                                <span class="menu-text">Tahsil Edilen Ödemeler</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item has-sub @if( Request::segment(1)== 'activities' || Request::segment(1)== 'past-activities' ) active @endif">
                    <a href="#" class="menu-link">
                    <span class="menu-icon">
                    <i class="bi bi-calendar"></i>
                    </span>
                        <span class="menu-text">Aktiviteler</span>
                        <span class="menu-caret"><b class="caret"></b></span>
                    </a>
                    <div class="menu-submenu">
                        <div id="nav-activities" class="d-none menu-item @if( Request::segment(1)== 'activities' ) active @endif">
                            <a href="/activities" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-calendar"></i></span>
                                <span class="menu-text">Gelecek Aktiviteler</span>
                            </a>
                        </div>
                        <div id="nav-past-activities" class="d-none menu-item @if( Request::segment(1)== 'past-activities' ) active @endif">
                            <a href="/past-activities" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-calendar"></i></span>
                                <span class="menu-text">Geçmiş Aktiviteler</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item has-sub @if( Request::segment(1)== 'products' || Request::segment(1)== 'brands' || Request::segment(1)== 'categories' ) active @endif">
                    <a href="#" class="menu-link">
                <span class="menu-icon">
                <i class="bi bi-gear"></i>
                </span>
                        <span class="menu-text">Ürün Yönetimi</span>
                        <span class="menu-caret"><b class="caret"></b></span>
                    </a>
                    <div class="menu-submenu">
                        <div id="nav-products" class="d-none menu-item @if( Request::segment(1)== 'products' ) active @endif">
                            <a href="/products" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-gear"></i></span>
                                <span class="menu-text">Ürünler</span>
                            </a>
                        </div>
                        <div id="nav-brands" class="d-none menu-item @if( Request::segment(1)== 'brands' ) active @endif">
                            <a href="/brands" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-gear"></i></span>
                                <span class="menu-text">Markalar</span>
                            </a>
                        </div>
                        <div id="nav-categories" class="d-none menu-item @if( Request::segment(1)== 'categories' ) active @endif">
                            <a href="/categories" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-gear"></i></span>
                                <span class="menu-text">Ürün Grupları</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item has-sub @if( Request::segment(1)== 'staff-targets' || Request::segment(1)== 'target-dashboard' ) active @endif">
                    <a href="#" class="menu-link">
                    <span class="menu-icon">
                    <i class="bi bi-pie-chart"></i>
                    </span>
                        <span class="menu-text">Hedef Yönetimi</span>
                        <span class="menu-caret"><b class="caret"></b></span>
                    </a>

                    <div class="menu-submenu">
                        <div id="nav-target-dashboard" class="d-none menu-item @if( Request::segment(1)== 'target-dashboard' ) active @endif">
                            <a href="/target-dashboard" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-bar-chart"></i></span>
                                <span class="menu-text">Hedef Grafikleri</span>
                            </a>
                        </div>
                        <div id="nav-staff-targets" class="d-none menu-item @if( Request::segment(1)== 'staff-targets' ) active @endif">
                            <a href="/staff-targets" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-bar-chart-steps"></i></span>
                                <span class="menu-text">Tüm Hedefler</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item has-sub @if( Request::segment(1)== 'settings' || Request::segment(1)== 'contacts' || Request::segment(1)== 'currency-logs' ) active @endif">
                    <a href="#" class="menu-link">
                    <span class="menu-icon">
                    <i class="bi bi-gear"></i>
                    </span>
                        <span class="menu-text">Ayarlar</span>
                        <span class="menu-caret"><b class="caret"></b></span>
                    </a>
                    <div class="menu-submenu">
                        <div id="nav-settings" class="d-none menu-item @if( Request::segment(1)== 'settings' ) active @endif">
                            <a href="/settings" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-gear"></i></span>
                                <span class="menu-text">Genel Ayarlar</span>
                            </a>
                        </div>
                        <div id="nav-contact-infos" class="d-none menu-item @if( Request::segment(1)== 'contacts' ) active @endif">
                            <a href="/contacts" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-gear"></i></span>
                                <span class="menu-text">Firma Bilgileri</span>
                            </a>
                        </div>
                        <div id="nav-currency-logs" class="d-none menu-item @if( Request::segment(1)== 'currency-logs' ) active @endif">
                            <a href="/currency-logs" class="menu-link">
                                <span class="menu-icon"><i class="bi bi-gear"></i></span>
                                <span class="menu-text">Döviz Geçmişi</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div id="nav-role-management" class="d-none menu-item has-sub @if( Request::segment(1)== 'teams' || Request::segment(1)== 'roles' ) active @endif">
                    <a href="#" class="menu-link">
                        <span class="menu-icon">
                        <i class="bi bi-person-circle"></i>
                        </span>
                        <span class="menu-text">Kullanıcılar</span>
                        <span class="menu-caret"><b class="caret"></b></span>
                    </a>
                    <div class="menu-submenu">
                        <div class="menu-item @if( Request::segment(1)== 'teams' ) active @endif">
                            <a href="/teams" class="menu-link">
                                <span class="menu-text">Ekip</span>
                            </a>
                        </div>
                        <div class="menu-item @if( Request::segment(1)== 'roles' ) active @endif">
                            <a href="/roles" class="menu-link">
                                <span class="menu-text">Roller</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div id="nav-notify-settings" class="d-none menu-item @if( Request::segment(1)== 'notify-settings' ) active @endif">
                    <a href="/notify-settings" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-info-circle"></i></span>
                        <span class="menu-text">Bildirim Ayarları</span>
                    </a>
                </div>

                <div id="nav-email-layouts" class="d-none menu-item @if( Request::segment(1)== 'email-layouts' ) active @endif">
                    <a href="/email-layouts" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-mailbox"></i></span>
                        <span class="menu-text">E-Posta Şablonları</span>
                    </a>
                </div>


            </div>

{{--            <div class="p-3 px-4 mt-auto">--}}
{{--                <a href="documentation/index.html" target="_blank" class="btn d-block btn-outline-theme">--}}
{{--                    <i class="fa fa-code-branch me-2 ms-n2 opacity-5"></i> Documentation--}}
{{--                </a>--}}
{{--            </div>--}}
        </div>

    </div>


    <button class="app-sidebar-mobile-backdrop" data-toggle-target=".app"
            data-toggle-class="app-sidebar-mobile-toggled"></button>
