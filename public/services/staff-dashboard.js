(function($) {
    "use strict";

	 $(document).ready(function() {

	});

	$(window).load(async function() {

		checkLogin();
        staff_dash_currency = 'TRY';
        let user_id = getPathVariable('staff-dashboard');

        await getAdminsAddSelectId('dash_staff');

        if (user_id != undefined) {
            document.getElementById('dash_staff').value = user_id;
            $('#staff-dashboard-card').removeClass('d-none');

            initAdmin(user_id);
            initStaffTargets(user_id);
            initStaffNotifies(user_id);
            initStaffCompanies(user_id);
            initStaffStats(user_id);
            getLastMonthSales(user_id);
            getApprovedMonthlySales(user_id);
            getCompletedMonthlySales(user_id);
            getPotentialMonthlySales(user_id);
            getCancelledMonthlySales(user_id);
        }else{
            $('#staffs-card').removeClass('d-none');
            initStaffs();
        }

	});

})(window.jQuery);
let staff_dash_currency;
let dash_staff;
function changeDashStaff(){
    dash_staff = document.getElementById('dash_staff').value;

    initAdmin(dash_staff);
    initStaffTargets(dash_staff);
    initStaffNotifies(dash_staff);
    initStaffCompanies(dash_staff);
    initStaffStats(dash_staff);
    getLastMonthSales(dash_staff);
    getApprovedMonthlySales(dash_staff);
    getCompletedMonthlySales(dash_staff);
    getPotentialMonthlySales(dash_staff);
    getCancelledMonthlySales(dash_staff);
}

async function initAdmin(user_id){
    let data = await serviceGetAdminById(user_id);
    let admin = data.admin;
    $('#staff-name').text(admin.name + ' ' + admin.surname);
    $('#staff-email').html('<i class="fa fa-envelope fa-fw text-inverse text-opacity-50"></i>' + admin.email);
    $('#staff-phone').html('<i class="fa fa-phone fa-fw text-inverse text-opacity-50"></i>' + admin.phone_number);

    let profile_photo = '/img/user/null-profile-picture.png';
    if (admin.profile_photo != null && admin.profile_photo != ''){
        profile_photo = admin.profile_photo;
    }
    $('#staff-image').attr('src', profile_photo);
}


async function getLastMonthSales(user_id){
    let data = await serviceGetLastMonthSalesByAdmin(user_id);
    let sales = data.sales;
    let continue_data = sales.continue.continue_serie_try.map(parseFloat);
    let approved_data = sales.approved.approved_serie_try.map(parseFloat);
    let completed_data = sales.completed.completed_serie_try.map(parseFloat);
    let cancelled_data = sales.cancelled.cancelled_serie_try.map(parseFloat);
    let day_count = sales.day_count;

    $('#monthly-approved-box h4').html(changeCommasToDecimal(sales.approved.try_sale) + ' TRY');
    let text1 = '<i class="fa fa-dollar-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.approved.usd_sale) +'<br/>\n' +
        '       <i class="fa fa-euro-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.approved.eur_sale);
    $('#monthly-approved-text').html(text1);

    $('#monthly-completed-box h4').html(changeCommasToDecimal(sales.completed.try_sale) + ' TRY');
    let text2 = '<i class="fa fa-dollar-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.completed.usd_sale) +'<br/>\n' +
        '       <i class="fa fa-euro-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.completed.eur_sale);
    $('#monthly-completed-text').html(text2);

    $('#monthly-continue-box h4').html(changeCommasToDecimal(sales.continue.try_sale) + ' TRY');
    let text3 = '<i class="fa fa-dollar-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.continue.usd_sale) +'<br/>\n' +
        '       <i class="fa fa-euro-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.continue.eur_sale);
    $('#monthly-continue-text').html(text3);

    $('#monthly-cancelled-box h4').html(changeCommasToDecimal(sales.cancelled.try_sale) + ' TRY');
    let text4 = '<i class="fa fa-dollar-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.cancelled.usd_sale) +'<br/>\n' +
        '       <i class="fa fa-euro-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.cancelled.eur_sale);
    $('#monthly-cancelled-text').html(text4);

    var spark1 = {
        chart: {
            id: 'sparkline1',
            group: 'sparklines',
            type: 'area',
            height: 100,
            sparkline: {
                enabled: true
            },
        },
        stroke: {
            curve: 'straight',
            width: 1
        },
        fill: {
            opacity: 0.3,
        },
        series: [{
            name: 'Sales',
            data: approved_data
        }],
        labels: [...Array(day_count).keys()].map(n => `2018-09-0${n+1}`),
        yaxis: {
            min: 0
        },
        xaxis: {
            type: 'datetime',
        },
        colors: ['rgb(144, 238, 126)'],
    }

    var spark2 = {
        chart: {
            id: 'sparkline2',
            group: 'sparklines',
            type: 'area',
            height: 100,
            sparkline: {
                enabled: true
            },
        },
        stroke: {
            curve: 'straight',
            width: 1
        },
        fill: {
            opacity: 0.3,
        },
        series: [{
            name: 'Sales',
            data: completed_data
        }],
        labels: [...Array(day_count).keys()].map(n => `2018-09-0${n+1}`),
        yaxis: {
            min: 0
        },
        xaxis: {
            type: 'datetime',
        },
        colors: ['rgb(254, 176, 25)'],
    }

    var spark3 = {
        chart: {
            id: 'sparkline3',
            group: 'sparklines',
            type: 'area',
            height: 100,
            sparkline: {
                enabled: true
            },
        },
        stroke: {
            curve: 'straight',
            width: 1
        },
        fill: {
            opacity: 0.3,
        },
        series: [{
            name: 'Sales',
            data: continue_data
        }],
        labels: [...Array(day_count).keys()].map(n => `2018-09-0${n+1}`),
        yaxis: {
            min: 0
        },
        xaxis: {
            type: 'datetime',
        },
        colors: ['rgb(78, 205, 196)'],
    }

    var spark4 = {
        chart: {
            id: 'sparkline4',
            group: 'sparklines',
            type: 'area',
            height: 100,
            sparkline: {
                enabled: true
            },
        },
        stroke: {
            curve: 'straight',
            width: 1
        },
        fill: {
            opacity: 0.3,
        },
        series: [{
            name: 'Sales',
            data: cancelled_data
        }],
        labels: [...Array(day_count).keys()].map(n => `2018-09-0${n+1}`),
        yaxis: {
            min: 0
        },
        xaxis: {
            type: 'datetime',
        },
        colors: ['rgb(255, 69, 96)'],
    }

    new ApexCharts(document.querySelector("#spark1"), spark1).render();
    new ApexCharts(document.querySelector("#spark2"), spark2).render();
    new ApexCharts(document.querySelector("#spark3"), spark3).render();
    new ApexCharts(document.querySelector("#spark4"), spark4).render();
}

async function getApprovedMonthlySales(user_id){

    let data = await serviceGetApprovedMonthlySalesByAdmin(user_id);
    let sales = data.sales.reverse();

    let xAxisArray = [];
    let yAxisArray = [];

    $.each(sales, function (i, sale) {
        xAxisArray.push(sale.month + "/" + sale.year);

        if (staff_dash_currency == 'TRY'){
            yAxisArray.push(sale.try_sale);
        }else if (staff_dash_currency == 'USD'){
            yAxisArray.push(sale.usd_sale);
        }else if (staff_dash_currency == 'EUR'){
            yAxisArray.push(sale.eur_sale);
        }
    });

    let apexColumnChartOptions = {
        chart: {
            height: 350,
            type: 'bar'
        },
        title: {
            style: {
                fontSize: '14px',
                fontWeight: 'bold',
                fontFamily: FONT_FAMILY,
                color: COLOR_WHITE
            },
        },
        legend: {
            fontFamily: FONT_FAMILY,
            labels: {
                colors: '#fff'
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '20%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        colors: ['#90ee7e'],
        series: [{
            name: staff_dash_currency,
            data: yAxisArray
        }],
        xaxis: {
            categories: xAxisArray,
            labels: {
                style: {
                    colors: '#fff',
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400,
                    cssClass: 'apexcharts-xaxis-label',
                }
            }
        },
        yaxis: {
            title: {
                text: 'Kazanç',
                style: {
                    color: hexToRgba(COLOR_WHITE, .5),
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400
                }
            },
            labels: {
                formatter: function (val) {
                    return changeCommasToDecimal(val.toFixed(2))
                },
                style: {
                    colors: '#fff',
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400,
                    cssClass: 'apexcharts-xaxis-label',
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return changeCommasToDecimal(val.toFixed(2))
                }
            }
        }
    };
    var apexColumnChart = new ApexCharts(
        document.querySelector('#chart-approved-monthly'),
        apexColumnChartOptions
    );
    apexColumnChart.render();

}

async function getCompletedMonthlySales(user_id){

    let data = await serviceGetCompletedMonthlySalesByAdmin(user_id);
    let sales = data.sales.reverse();

    let xAxisArray = [];
    let yAxisArray = [];

    $.each(sales, function (i, sale) {
        xAxisArray.push(sale.month + "/" + sale.year);

        if (staff_dash_currency == 'TRY'){
            yAxisArray.push(sale.try_sale);
        }else if (staff_dash_currency == 'USD'){
            yAxisArray.push(sale.usd_sale);
        }else if (staff_dash_currency == 'EUR'){
            yAxisArray.push(sale.eur_sale);
        }
    });

    let apexColumnChartOptions = {
        chart: {
            height: 350,
            type: 'bar'
        },
        title: {
            style: {
                fontSize: '14px',
                fontWeight: 'bold',
                fontFamily: FONT_FAMILY,
                color: COLOR_WHITE
            },
        },
        legend: {
            fontFamily: FONT_FAMILY,
            labels: {
                colors: '#fff'
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '20%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        colors: ['#feb019d9'],
        series: [{
            name: staff_dash_currency,
            data: yAxisArray
        }],
        xaxis: {
            categories: xAxisArray,
            labels: {
                style: {
                    colors: '#fff',
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400,
                    cssClass: 'apexcharts-xaxis-label',
                }
            }
        },
        yaxis: {
            title: {
                text: 'Kazanç',
                style: {
                    color: hexToRgba(COLOR_WHITE, .5),
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400
                }
            },
            labels: {
                formatter: function (val) {
                    return changeCommasToDecimal(val.toFixed(2))
                },
                style: {
                    colors: '#fff',
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400,
                    cssClass: 'apexcharts-xaxis-label',
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return changeCommasToDecimal(val.toFixed(2))
                }
            }
        }
    };
    var apexColumnChart = new ApexCharts(
        document.querySelector('#chart-completed-monthly'),
        apexColumnChartOptions
    );
    apexColumnChart.render();

}

async function getPotentialMonthlySales(user_id){

    let data = await serviceGetPotentialSalesByAdmin(user_id);
    let sales = data.sales.reverse();

    let xAxisArray = [];
    let yAxisArray = [];

    $.each(sales, function (i, sale) {
        xAxisArray.push(sale.month + "/" + sale.year);
        if (staff_dash_currency == 'TRY'){
            yAxisArray.push(sale.try_sale);
        }else if (staff_dash_currency == 'USD'){
            yAxisArray.push(sale.usd_sale);
        }else if (staff_dash_currency == 'EUR'){
            yAxisArray.push(sale.eur_sale);
        }
    });

    let apexColumnChartOptions = {
        chart: {
            height: 350,
            type: 'bar'
        },
        title: {
            style: {
                fontSize: '14px',
                fontWeight: 'bold',
                fontFamily: FONT_FAMILY,
                color: COLOR_WHITE
            },
        },
        legend: {
            fontFamily: FONT_FAMILY,
            labels: {
                colors: '#fff'
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '20%',
                endingShape: 'rounded',
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        colors: ['#4ecdc4'],
        series: [{
            name: staff_dash_currency,
            data: yAxisArray
        }],
        xaxis: {
            categories: xAxisArray,
            labels: {
                style: {
                    colors: '#fff',
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400,
                    cssClass: 'apexcharts-xaxis-label',
                }
            }
        },
        yaxis: {
            title: {
                text: 'Kazanç',
                style: {
                    color: hexToRgba(COLOR_WHITE, .7),
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400
                }
            },
            labels: {
                formatter: function (val) {
                    return changeCommasToDecimal(val.toFixed(2))
                },
                style: {
                    colors: '#fff',
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400,
                    cssClass: 'apexcharts-xaxis-label',
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return changeCommasToDecimal(val.toFixed(2))
                }
            }
        }
    };
    var apexColumnChart = new ApexCharts(
        document.querySelector('#chart-potential-sales'),
        apexColumnChartOptions
    );
    apexColumnChart.render();

}

async function getCancelledMonthlySales(user_id){

    let data = await serviceGetCancelledPotentialSalesByAdmin(user_id);
    let sales = data.sales.reverse();

    let xAxisArray = [];
    let yAxisArray = [];

    $.each(sales, function (i, sale) {
        xAxisArray.push(sale.month + "/" + sale.year);
        if (staff_dash_currency == 'TRY'){
            yAxisArray.push(sale.try_sale);
        }else if (staff_dash_currency == 'USD'){
            yAxisArray.push(sale.usd_sale);
        }else if (staff_dash_currency == 'EUR'){
            yAxisArray.push(sale.eur_sale);
        }
    });

    let apexColumnChartOptions = {
        chart: {
            height: 350,
            type: 'bar'
        },
        title: {
            style: {
                fontSize: '14px',
                fontWeight: 'bold',
                fontFamily: FONT_FAMILY,
                color: COLOR_WHITE
            },
        },
        legend: {
            fontFamily: FONT_FAMILY,
            labels: {
                colors: '#fff'
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '20%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        colors: ['#ff4560d9'],
        series: [{
            name: staff_dash_currency,
            data: yAxisArray
        }],
        xaxis: {
            categories: xAxisArray,
            labels: {
                style: {
                    colors: '#fff',
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400,
                    cssClass: 'apexcharts-xaxis-label',
                }
            }
        },
        yaxis: {
            title: {
                text: 'Kazanç',
                style: {
                    color: hexToRgba(COLOR_WHITE, .5),
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400
                }
            },
            labels: {
                formatter: function (val) {
                    return changeCommasToDecimal(val.toFixed(2))
                },
                style: {
                    colors: '#fff',
                    fontSize: '12px',
                    fontFamily: FONT_FAMILY,
                    fontWeight: 400,
                    cssClass: 'apexcharts-xaxis-label',
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return changeCommasToDecimal(val.toFixed(2))
                }
            }
        }
    };
    var apexColumnChart = new ApexCharts(
        document.querySelector('#chart-cancelled-potential-sales'),
        apexColumnChartOptions
    );
    apexColumnChart.render();

}

async function initStaffTargets(user_id){

    let data = await serviceGetStaffTargetsByStaffId(user_id);

    $("#targets-datatable").dataTable().fnDestroy();
    $('#targets-datatable tbody > tr').remove();

    $.each(data.targets, function (i, target) {

        let actions = "";
        if (true){
            actions = '<button type="button" class="btn btn-outline-secondary btn-sm" onclick="openUpdateStaffTargetModal(\''+ target.id +'\');">Düzenle</button>\n' +
                '      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deleteStaffTarget(\''+ target.id +'\');">Sil</button>\n';
        }

        let item = '<tr>\n' +
            '           <th>'+ target.id +'</th>\n' +
            // '           <td>'+ target.admin.name +' '+ target.admin.surname +'</td>\n' +
            '           <td>'+ target.type_name +'</td>\n' +
            '           <td>'+ changeCommasToDecimal(target.target) +' '+ target.currency +'</td>\n' +
            '           <td>'+ target.month_name +'</td>\n' +
            '           <td>'+ target.year +'</td>\n' +
            // '           <td>'+ target.status.rate +'%</td>\n' +
            '           <td>\n' +
            '               <div class="progress">\n' +
            '                   <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: '+ parseInt(target.status.rate) +'%"><span>'+ target.status.rate +'%</span></div>\n' +
            '               </div>\n' +
            '           </td>\n' +
            '       </tr>';
        $('#targets-datatable tbody').append(item);
    });

    $('#targets-datatable').DataTable({
        responsive: false,
        columnDefs: [],
        dom: 'Bfrtip',
        paging: false,
        buttons: [],
        scrollX: true,
        language: {
            url: "services/Turkish.json"
        },
        order: false,
    });
}

async function initStaffNotifies(user_id){

    let data = await serviceGetNotifiesByUserId(user_id);
    console.log(data)

    $('#user-notifies .list-group-item').remove();

    let notify_date = '';
    $.each(data.notifies, function (i, notify) {
        if (notify_date != formatDateASC(notify.created_at, '-')){
            notify_date = formatDateASC(notify.created_at, '-');

            let item = '<div class="list-group-item px-3 bg-dark-100" id="dash-notify-'+ notify.notify_id +'">\n' +
                '                   <div class="fs-13px mb-0 text-center"><b>'+ notify_date +'</b></div>\n' +
                '               </div>\n';
            $('#user-notifies').append(item);
        }

        let actions = "";
        let bg_color = "";
        if (notify.is_read == 0){
            actions = '     <div class="text-right">\n' +
                // '               <button type="button" onclick="markAsRead(\'' + notify.notify_id + '\')" class="btn btn-link p-0"><small class="text-inverse text-opacity-50">Okundu Olarak İşaretle</small></button>\n' +
                '           </div>\n';
            bg_color = 'bg-theme-100';
        }

        let item = '<div class="list-group-item px-3 '+ bg_color +'" id="dash-notify-'+ notify.notify_id +'">\n' +
            '           <div class="fs-13px mb-1">'+ notify.notify +'</div>\n' +
            '           ' + actions + '' +
            '       </div>\n';
        $('#user-notifies').append(item);
    });

}

async function initStaffCompanies(user_id){

    let data = await serviceGetCompaniesByStaffId(user_id);

    $.each(data.companies, function (i, company) {
        let logo = 'img/user/null-profile-picture.png';
        if (company.logo != null){
            logo = company.logo;
        }
        let type = '';
        if (company.is_supplier == 1){
            type += ' Tedarikçi,';
        }
        if (company.is_customer == 1){
            type += ' Müşteri,';
        }
        if (company.is_potential_customer == 1){
            type += ' Potansiyel Müşteri,';
        }
        type = type.slice(0, -1);

        let item = '<div>\n' +
            '                  <a href="/customer-dashboard/'+ company.id +'" class="company-info d-flex align-items-center mb-3 text-decoration-none fs-12px">\n' +
            '                  <img src="'+ logo +'" alt="" width="30" class="rounded-circle">\n' +
            '                  <div class="flex-fill px-3">\n' +
            '                      <div class="fw-bold text-truncate w-100px company-name">'+ company.name +'</div>\n' +
            '                      <div class="fs-12px text-inverse text-opacity-50">'+ type +'</div>\n' +
            '                  </div>\n' +
            '                  </a>\n' +
            '              </div>\n';
        $('#staff-companies').append(item);
    });

}

function filterCompanies() {

    let searchText = document.getElementById('search-company-text').value.toLowerCase();

    let companyItems = document.getElementById('staff-companies').getElementsByClassName('company-info');

    for (let i = 0; i < companyItems.length; i++) {
        let companyName = companyItems[i].querySelector('.company-name').textContent.toLowerCase();

        if (companyName.includes(searchText)) {
            companyItems[i].classList.add('d-flex');
            companyItems[i].classList.remove('d-none');
        } else {
            companyItems[i].classList.remove('d-flex');
            companyItems[i].classList.add('d-none');
        }
    }
}

async function markAsRead(notify_id){
    markAsReadSingleNotify(notify_id);
    $('#dash-notify-'+notify_id).removeClass('bg-theme-100');
    $('#dash-notify-'+notify_id+ ' div.text-right').remove();
}

async function initStaffStats(user_id){

    let data = await serviceGetStaffStatistics(user_id);

    $('#stat-1').html(data.total_company_count);
    $('#stat-2').html(data.add_this_month_company);
    $('#stat-3').html(data.activity_this_month);
    $('#stat-4').html(data.request_this_month);
    $('#stat-5').html(data.sale_this_month);
    $('#stat-6').html('');

    let data2 = await serviceGetStaffSituation(user_id);

    $('#stat-6').html(data2.position + '. (' + data2.staff.staff_rate + ')');

}

async function initStaffs(){
    let data = await serviceGetAdmins();
    $.each(data.admins, function(i, admin){

        let item = '<div class="col-sm-3">\n' +
            '                  <div class="card mb-3">\n' +
            '                      <a href="/staff-dashboard/'+ admin.id +'">\n' +
            '                          <div class="m-1">\n' +
            '                              <div class="p-1">\n' +
            '                                  <div class="text-dark text-left">\n' +
            '                                      <div class="row">\n' +
            '                                          <div class="col-5">\n' +
            '                                              <div class="">\n' +
            '                                                  <div class="profile-card-img" style="background-image: url(\''+ admin.profile_photo +'\');"></div>\n' +
            '                                              </div>\n' +
            '                                          </div>\n' +
            '                                          <div class="col-7">\n' +
            '                                              <div>\n' +
            '                                                  <div class="fw-bold">'+ admin.name +' '+ admin.surname +'</div>\n' +
            '                                                  <div class="small">'+ admin.admin_role_name +'</div>\n' +
            '                                                  <div class="small">'+ admin.email +'</div>\n' +
            '                                                  <div class="small">'+ admin.phone_number +'</div>\n' +
            '                                              </div>\n' +
            '                                          </div>\n' +
            '                                      </div>\n' +
            '                                  </div>\n' +
            '                              </div>\n' +
            '                          </div>\n' +
            '                      </a>\n' +
            '                      <div class="card-arrow">\n' +
            '                          <div class="card-arrow-top-left"></div>\n' +
            '                          <div class="card-arrow-top-right"></div>\n' +
            '                          <div class="card-arrow-bottom-left"></div>\n' +
            '                          <div class="card-arrow-bottom-right"></div>\n' +
            '                      </div>\n' +
            '                  </div>\n' +
            '              </div>';

        $('#staffs').append(item);
    });
}
