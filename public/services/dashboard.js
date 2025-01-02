(function($) {
    "use strict";

	 $(document).ready(function() {

         reLoadGrid();
	});

	$(window).load(async function() {

		checkLogin();

        await getDashboardOwnersAddSelectId('dash_owner');

        dash_owner = localStorage.getItem('dash_owner');
        if (dash_owner == null){
            dash_owner = '0';
            localStorage.setItem('dash_owner', '0');
            document.getElementById('dash_owner').value = '0';
        }else{
            document.getElementById('dash_owner').value = dash_owner;
        }

        dash_currency = localStorage.getItem('dash_currency');
        if (dash_currency == null){
            dash_currency = 'TRY';
            localStorage.setItem('dash_currency', 'TRY');
            document.getElementById('dash_currency').value = 'TRY';
        }else{
            document.getElementById('dash_currency').value = dash_currency;
        }

        getTotalSales();
        getTotalProfitRate();
        getDashboardStats();
        getLastMonthSales();
        getApprovedMonthlySales();
        getCompletedMonthlySales();
        getPotentialMonthlySales();
        getCancelledMonthlySales();
        getMonthlyProfitRates();
        getMonthlyTurningRates();
        getAdminsSales();
        initTopSaledProducts();
        getBestCustomers();
        getBestStaffs();
        getCustomerByNotSaleLongTimes();
        getCustomerByNotSale();
        getBestSalesLastNinetyDays();
        initSaleHistory();

	});

})(window.jQuery);

let dash_currency;
let dash_owner;
function reLoadGrid(){
    $('.masonry-layout-1').masonry({
        itemSelector: '.grid-item',
        columnWidth: '.grid-sizer',
        gutter: '.gutter-sizer',
        percentPosition: true
    });

    $('.masonry-layout-2').masonry({
        itemSelector: '.grid-item',
        columnWidth: '.grid-sizer',
        gutter: '.gutter-sizer',
        percentPosition: true
    });
}
function changeDashCurrency(){
    dash_currency = document.getElementById('dash_currency').value;
    localStorage.setItem('dash_currency', dash_currency);
    location.reload();
}
function changeDashOwner(){
    dash_owner = document.getElementById('dash_owner').value;
    localStorage.setItem('dash_owner', dash_owner);
    location.reload();
}

async function getTotalSales(){

    let data = await serviceGetTotalSales(dash_owner);
    let sales = data.sales;

    let approved_sale = 0;
    let completed_sale = 0;
    let continue_sale = 0;
    let cancelled_sale = 0;
    if (dash_currency == 'TRY'){
        approved_sale = sales.approved.try_sale;
        completed_sale = sales.completed.try_sale;
        continue_sale = sales.continue.try_sale;
        cancelled_sale = sales.cancelled.try_sale;
    }else if (dash_currency == 'USD'){
        approved_sale = sales.approved.usd_sale;
        completed_sale = sales.completed.usd_sale;
        continue_sale = sales.continue.usd_sale;
        cancelled_sale = sales.cancelled.usd_sale;
    }else if (dash_currency == 'EUR'){
        approved_sale = sales.approved.eur_sale;
        completed_sale = sales.completed.eur_sale;
        continue_sale = sales.continue.eur_sale;
        cancelled_sale = sales.cancelled.eur_sale;
    }else if (dash_currency == 'GBP'){
        approved_sale = sales.approved.gbp_sale;
        completed_sale = sales.completed.gbp_sale;
        continue_sale = sales.continue.gbp_sale;
        cancelled_sale = sales.cancelled.gbp_sale;
    }

    $('#approved-box .spinners').remove();
    $('#completed-box .spinners').remove();
    $('#potential-box .spinners').remove();
    $('#cancelled-box .spinners').remove();

    $('#approved-box h5').append(changeCommasToDecimal(approved_sale) + ' ' + dash_currency);
    $('#completed-box h5').append(changeCommasToDecimal(completed_sale) + ' ' + dash_currency);
    $('#potential-box h5').append(changeCommasToDecimal(continue_sale) + ' ' + dash_currency);
    $('#cancelled-box h5').append(changeCommasToDecimal(cancelled_sale) + ' ' + dash_currency);

}

async function getLastMonthSales(){
    let data = await serviceGetLastMonthSales(dash_owner);
    let sales = data.sales;
    let continue_data = sales.continue.continue_serie_try.map(parseFloat);
    let approved_data = sales.approved.approved_serie_try.map(parseFloat);
    let completed_data = sales.completed.completed_serie_try.map(parseFloat);
    let cancelled_data = sales.cancelled.cancelled_serie_try.map(parseFloat);
    let day_count = sales.day_count;

    let approved_sale = 0;
    let completed_sale = 0;
    let continue_sale = 0;
    let cancelled_sale = 0;
    if (dash_currency == 'TRY'){
        approved_sale = sales.approved.try_sale;
        completed_sale = sales.completed.try_sale;
        continue_sale = sales.continue.try_sale;
        cancelled_sale = sales.cancelled.try_sale;
    }else if (dash_currency == 'USD'){
        approved_sale = sales.approved.usd_sale;
        completed_sale = sales.completed.usd_sale;
        continue_sale = sales.continue.usd_sale;
        cancelled_sale = sales.cancelled.usd_sale;
    }else if (dash_currency == 'EUR'){
        approved_sale = sales.approved.eur_sale;
        completed_sale = sales.completed.eur_sale;
        continue_sale = sales.continue.eur_sale;
        cancelled_sale = sales.cancelled.eur_sale;
    }else if (dash_currency == 'GBP'){
        approved_sale = sales.approved.gbp_sale;
        completed_sale = sales.completed.gbp_sale;
        continue_sale = sales.continue.gbp_sale;
        cancelled_sale = sales.cancelled.gbp_sale;
    }

    $('#monthly-approved-box .spinners').remove();
    $('#monthly-completed-box .spinners').remove();
    $('#monthly-continue-box .spinners').remove();
    $('#monthly-cancelled-box .spinners').remove();

    $('#monthly-approved-box h5').append(changeCommasToDecimal(approved_sale) + ' ' + dash_currency);
    $('#monthly-completed-box h5').append(changeCommasToDecimal(completed_sale) + ' ' + dash_currency);
    $('#monthly-continue-box h5').append(changeCommasToDecimal(continue_sale) + ' ' + dash_currency);
    $('#monthly-cancelled-box h5').append(changeCommasToDecimal(cancelled_sale) + ' ' + dash_currency);

    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Adding 1 because months are zero-based

    const formattedDate = year+'-'+month;

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
        labels: [...Array(day_count).keys()].map(n => formattedDate+`-0${n+1}`),
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
        labels: [...Array(day_count).keys()].map(n => formattedDate+`-0${n+1}`),
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
        labels: [...Array(day_count).keys()].map(n => formattedDate+`-0${n+1}`),
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
        labels: [...Array(day_count).keys()].map(n => formattedDate+`-0${n+1}`),
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

async function getApprovedMonthlySales(){

    let data = await serviceGetDashboardApprovedSales(dash_owner);
    let sales = data.sales;
    let previous_sales = data.previous_sales;

    let xAxisArray = [];
    let yAxisArray = [];
    let yAxisArrayPrevious = [];

    let currentYear = new Date().getFullYear();
    let previousYear = currentYear - 1;

    $.each(sales, function (i, sale) {
        xAxisArray.push(sale.month);

        if (dash_currency == 'TRY'){
            yAxisArray.push(sale.try_sale);
        }else if (dash_currency == 'USD'){
            yAxisArray.push(sale.usd_sale);
        }else if (dash_currency == 'EUR'){
            yAxisArray.push(sale.eur_sale);
        }else if (dash_currency == 'GBP'){
            yAxisArray.push(sale.gbp_sale);
        }
    });

    $.each(previous_sales, function (i, sale) {
        if (dash_currency == 'TRY'){
            yAxisArrayPrevious.push(sale.try_sale);
        }else if (dash_currency == 'USD'){
            yAxisArrayPrevious.push(sale.usd_sale);
        }else if (dash_currency == 'EUR'){
            yAxisArrayPrevious.push(sale.eur_sale);
        }else if (dash_currency == 'GBP'){
            yAxisArrayPrevious.push(sale.gbp_sale);
        }
    });

    let apexColumnChartOptions = {
        chart: {
            height: 300,
            type: 'bar'
        },
        title: {
            style: {
                fontSize: '14px',
                fontWeight: 'bold',
                fontFamily: FONT_FAMILY,
                color: COLOR_DARK
            },
        },
        legend: {
            fontFamily: FONT_FAMILY,
            labels: {
                colors: COLOR_DARK
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '80%',
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
        colors: ['#8d989d', '#90ee7e'],
        series: [
            {
                name: previousYear,
                data: yAxisArrayPrevious
            },
            {
                name: currentYear,
                data: yAxisArray
            }
        ],
        xaxis: {
            categories: xAxisArray,
            labels: {
                style: {
                    colors: COLOR_DARK,
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
                    colors: COLOR_DARK,
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
    $('#chart-approved-monthly .spinners').remove();
    var apexColumnChart = new ApexCharts(
        document.querySelector('#chart-approved-monthly'),
        apexColumnChartOptions
    );
    apexColumnChart.render();

    reLoadGrid();

}

async function getCompletedMonthlySales(){

    let data = await serviceGetDashboardCompletedSales(dash_owner);
    let sales = data.sales;
    let previous_sales = data.previous_sales;

    let xAxisArray = [];
    let yAxisArray = [];
    let yAxisArrayPrevious = [];

    let currentYear = new Date().getFullYear();
    let previousYear = currentYear - 1;

    $.each(sales, function (i, sale) {
        xAxisArray.push(sale.month);

        if (dash_currency == 'TRY'){
            yAxisArray.push(sale.try_sale);
        }else if (dash_currency == 'USD'){
            yAxisArray.push(sale.usd_sale);
        }else if (dash_currency == 'EUR'){
            yAxisArray.push(sale.eur_sale);
        }else if (dash_currency == 'GBP'){
            yAxisArray.push(sale.gbp_sale);
        }
    });

    $.each(previous_sales, function (i, sale) {
        if (dash_currency == 'TRY'){
            yAxisArrayPrevious.push(sale.try_sale);
        }else if (dash_currency == 'USD'){
            yAxisArrayPrevious.push(sale.usd_sale);
        }else if (dash_currency == 'EUR'){
            yAxisArrayPrevious.push(sale.eur_sale);
        }else if (dash_currency == 'GBP'){
            yAxisArrayPrevious.push(sale.gbp_sale);
        }
    });

    let apexColumnChartOptions = {
        chart: {
            height: 300,
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
                colors: COLOR_DARK
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '80%',
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
        colors: ['#8d989d', '#feb019d9'],
        series: [
            {
                name: previousYear,
                data: yAxisArrayPrevious
            },
            {
                name: currentYear,
                data: yAxisArray
            }
        ],
        xaxis: {
            categories: xAxisArray,
            labels: {
                style: {
                    colors: COLOR_DARK,
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
                    colors: COLOR_DARK,
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
    $('#chart-completed-monthly .spinners').remove();
    var apexColumnChart = new ApexCharts(
        document.querySelector('#chart-completed-monthly'),
        apexColumnChartOptions
    );
    apexColumnChart.render();

    reLoadGrid();

}

async function getPotentialMonthlySales(){

    let data = await serviceGetDashboardPotentialSales(dash_owner);
    let sales = data.sales;
    let previous_sales = data.previous_sales;

    let xAxisArray = [];
    let yAxisArray = [];
    let yAxisArrayPrevious = [];

    let currentYear = new Date().getFullYear();
    let previousYear = currentYear - 1;

    $.each(sales, function (i, sale) {
        xAxisArray.push(sale.month);

        if (dash_currency == 'TRY'){
            yAxisArray.push(sale.try_sale);
        }else if (dash_currency == 'USD'){
            yAxisArray.push(sale.usd_sale);
        }else if (dash_currency == 'EUR'){
            yAxisArray.push(sale.eur_sale);
        }else if (dash_currency == 'GBP'){
            yAxisArray.push(sale.gbp_sale);
        }
    });

    $.each(previous_sales, function (i, sale) {
        if (dash_currency == 'TRY'){
            yAxisArrayPrevious.push(sale.try_sale);
        }else if (dash_currency == 'USD'){
            yAxisArrayPrevious.push(sale.usd_sale);
        }else if (dash_currency == 'EUR'){
            yAxisArrayPrevious.push(sale.eur_sale);
        }else if (dash_currency == 'GBP'){
            yAxisArrayPrevious.push(sale.gbp_sale);
        }
    });

    let apexColumnChartOptions = {
        chart: {
            height: 300,
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
                colors: COLOR_DARK
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '80%',
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
        colors: ['#8d989d', '#4ecdc4'],
        series: [
            {
                name: previousYear,
                data: yAxisArrayPrevious
            },
            {
                name: currentYear,
                data: yAxisArray
            }
        ],
        xaxis: {
            categories: xAxisArray,
            labels: {
                style: {
                    colors: COLOR_DARK,
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
                    colors: COLOR_DARK,
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
    $('#chart-potential-sales .spinners').remove();
    var apexColumnChart = new ApexCharts(
        document.querySelector('#chart-potential-sales'),
        apexColumnChartOptions
    );
    apexColumnChart.render();

    reLoadGrid();

}

async function getCancelledMonthlySales(){

    let data = await serviceGetDashboardCancelledSales(dash_owner);
    let sales = data.sales;
    let previous_sales = data.previous_sales;

    let xAxisArray = [];
    let yAxisArray = [];
    let yAxisArrayPrevious = [];

    let currentYear = new Date().getFullYear();
    let previousYear = currentYear - 1;

    $.each(sales, function (i, sale) {
        xAxisArray.push(sale.month);

        if (dash_currency == 'TRY'){
            yAxisArray.push(sale.try_sale);
        }else if (dash_currency == 'USD'){
            yAxisArray.push(sale.usd_sale);
        }else if (dash_currency == 'EUR'){
            yAxisArray.push(sale.eur_sale);
        }else if (dash_currency == 'GBP'){
            yAxisArray.push(sale.gbp_sale);
        }
    });

    $.each(previous_sales, function (i, sale) {
        if (dash_currency == 'TRY'){
            yAxisArrayPrevious.push(sale.try_sale);
        }else if (dash_currency == 'USD'){
            yAxisArrayPrevious.push(sale.usd_sale);
        }else if (dash_currency == 'EUR'){
            yAxisArrayPrevious.push(sale.eur_sale);
        }else if (dash_currency == 'GBP'){
            yAxisArrayPrevious.push(sale.gbp_sale);
        }
    });

    let apexColumnChartOptions = {
        chart: {
            height: 300,
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
                colors: COLOR_DARK
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '80%',
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
        colors: ['#8d989d', '#ff4560d9'],
        series: [
            {
                name: previousYear,
                data: yAxisArrayPrevious
            },
            {
                name: currentYear,
                data: yAxisArray
            }
        ],
        xaxis: {
            categories: xAxisArray,
            labels: {
                style: {
                    colors: COLOR_DARK,
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
                    colors: COLOR_DARK,
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
    $('#chart-cancelled-potential-sales .spinners').remove();
    var apexColumnChart = new ApexCharts(
        document.querySelector('#chart-cancelled-potential-sales'),
        apexColumnChartOptions
    );
    apexColumnChart.render();

    reLoadGrid();

}

async function getAdminsSales(){

    let data = await serviceGetMonthlyApprovedSalesThisYearByAdmins(dash_owner);
    let admins = data.admins;
    admins.sort((a, b) => parseFloat(b.total_sales.try_total) - parseFloat(a.total_sales.try_total));

    $('#admins-table').siblings('.spinners').remove();

    $('#admins-table tbody tr').remove();
    $.each(admins, function (i, admin) {

        let sale_price = 0;
        if (dash_currency == 'TRY'){
            sale_price = admin.total_sales.try_total;
        }else if (dash_currency == 'USD'){
            sale_price = admin.total_sales.usd_total;
        }else if (dash_currency == 'EUR'){
            sale_price = admin.total_sales.eur_total;
        }else if (dash_currency == 'GBP'){
            sale_price = admin.total_sales.eur_total;
        }else if (dash_currency == 'GBP'){
            sale_price = admin.total_sales.gbp_total;
        }

        let item = '<tr>\n' +
            '           <td>'+ admin.id +'</td>\n' +
            '           <td>'+ admin.name +' '+ admin.surname +'</td>\n' +
            '           <td>'+ admin.total_sales.sale_count +'</td>\n' +
            '           <td>'+ changeCommasToDecimal(sale_price) +' '+ dash_currency +'</td>\n' +
            '       </tr>';
        $('#admins-table tbody').append(item);
    });

    reLoadGrid();

}

async function initTopSaledProducts(){
    let data = await serviceGetTopSaledProducts(dash_owner);
    let products = data.products;

    $('#top-products-table').siblings('.spinners').remove();

    $('#top-products-table tbody tr').remove();

    $.each(products, function (i, product) {

        let item = '<tr>\n' +
            '           <td>\n' +
            '               <span class="d-flex align-items-center">\n' +
            '                   <i class="bi bi-circle-fill fs-6px text-theme me-2"></i>\n' +
            '                   '+ product.product_detail.product_name.substring(0, 50) +'...\n' +
            '               </span>\n' +
            '           </td>\n' +
            '           <td><small>'+ product.total_quantity +' Adet</small></td>\n' +
            '       </tr>';

        $('#top-products-table tbody').append(item);
    });

    reLoadGrid();
}

async function getBestCustomers(){

    let data = await serviceGetBestCustomer();
    let companies = data.companies;

    $('#best-customers-table').siblings('.spinners').remove();

    $('#best-customers-table tbody tr').remove();
    $.each(companies, function (i, company) {
        let item = '<tr>\n' +
            '           <td>'+ (i+1) +'</td>\n' +
            '           <td>'+ company.company.name.substring(0, 30) +'...</td>\n' +
            '           <td>'+ company.c1 +'</td>\n' +
            '           <td>'+ company.c2 +'</td>\n' +
            '           <td>'+ company.c3 +'</td>\n' +
            '           <td>'+ company.c4 +'</td>\n' +
            '           <td>'+ company.c5 +'</td>\n' +
            '           <td><b>'+ company.company_rate +'</b></td>\n' +
            '       </tr>';
        $('#best-customers-table tbody').append(item);
    });

    reLoadGrid();

}

async function getBestStaffs(){

    let data = await serviceGetBestStaff();
    let staffs = data.staffs;
    console.log(staffs)

    $('#best-staffs-table').siblings('.spinners').remove();

    $('#best-staffs-table tbody tr').remove();
    $.each(staffs, function (i, staff) {
        let item = '<tr>\n' +
            '           <td>'+ (i+1) +'</td>\n' +
            '           <td>'+ staff.staff.name +' '+ staff.staff.surname +'</td>\n' +
            '           <td>'+ staff.c1 +'</td>\n' +
            '           <td>'+ staff.c2 +'</td>\n' +
            '           <td>'+ staff.c3 +'</td>\n' +
            '           <td>'+ staff.c4 +'</td>\n' +
            '           <td>'+ staff.c5 +'</td>\n' +
            '           <td>'+ staff.c6 +'</td>\n' +
            '           <td>'+ staff.c7 +'</td>\n' +
            '           <td>'+ staff.c8 +'</td>\n' +
            '           <td>'+ staff.c9 +'</td>\n' +
            '           <td>'+ staff.c10 +'</td>\n' +
            '           <td><b>'+ staff.staff_rate +'</b></td>\n' +
            '       </tr>';
        $('#best-staffs-table tbody').append(item);
    });

    reLoadGrid();
}

async function getTotalProfitRate(){

    let data = await serviceGetTotalProfitRate(dash_owner);
    let profit_rate = data.profit_rate;
    let profit_rate_icon = data.profit_rate_icon;

    $('#total-profit-box .spinners').remove();

    $('#total-profit-box h5').append(profit_rate + '%');
    if(profit_rate_icon == '-'){
        profit_rate_icon = '<img src="img/icons/grayline.png">';
    }else if (profit_rate_icon == 'up'){
        profit_rate_icon = '<img src="img/icons/greenarrow.png">';
    }else if (profit_rate_icon == 'down'){
        profit_rate_icon = '<img src="img/icons/redarrow.png">';
    }
    $('#total-profit-box-icon').append('<abbr title="Karlılık oranının bir önceki aya göre durumu" class="initialism">' + profit_rate_icon + '</abbr>');

}

async function getDashboardStats(){

    let data = await serviceGetDashboardStats(dash_owner);
    console.log(data)
    let offer_turning_rate = data.offer_turning_rate;
    let offer_turning_rate_icon = data.offer_turning_rate_icon;
    let turnover_rate = data.turnover_rate;
    let turnover_rate_icon = data.turnover_rate_icon;
    let total_request = data.total_request;
    let year_total_request = data.year_total_request;
    let total_request_icon = data.total_request_icon;
    let total_sale = data.total_sale;
    let year_total_sale = data.year_total_sale;
    let total_sale_icon = data.total_sale_icon;
    let total_activity = data.total_activity;
    let year_total_activity = data.year_total_activity;
    let total_activity_icon = data.total_activity_icon;

    $('#offer-turning-box .spinners').remove();
    $('#offer-turning-box h5').append(offer_turning_rate + '%');
    if(offer_turning_rate_icon == '-'){
        offer_turning_rate_icon = '<img src="img/icons/grayline.png">';
    }else if (offer_turning_rate_icon == 'up'){
        offer_turning_rate_icon = '<img src="img/icons/greenarrow.png">';
    }else if (offer_turning_rate_icon == 'down'){
        offer_turning_rate_icon = '<img src="img/icons/redarrow.png">';
    }
    $('#offer-turning-box-icon').append('<abbr title="Teklif onaylanma oranının bir önceki aya göre durumu" class="initialism">' + offer_turning_rate_icon + '</abbr>');

    $('#turnover-box .spinners').remove();
    $('#turnover-box h5').append(turnover_rate + '%');
    if(turnover_rate_icon == '-'){
        turnover_rate_icon = '<img src="img/icons/grayline.png">';
    }else if (turnover_rate_icon == 'up'){
        turnover_rate_icon = '<img src="img/icons/greenarrow.png">';
    }else if (turnover_rate_icon == 'down'){
        turnover_rate_icon = '<img src="img/icons/redarrow.png">';
    }
    $('#turnover-box-icon').append('<abbr title="Cironun bir önceki aya göre durumu" class="initialism">' + turnover_rate_icon + '</abbr>');

    $('#total-request-box .spinners').remove();
    $('#total-request-box h5').append(total_request + ' - ' + year_total_request);
    if(total_request_icon == '-'){
        total_request_icon = '<img src="img/icons/grayline.png">';
    }else if (total_request_icon == 'up'){
        total_request_icon = '<img src="img/icons/greenarrow.png">';
    }else if (total_request_icon == 'down'){
        total_request_icon = '<img src="img/icons/redarrow.png">';
    }
    $('#total-request-box-icon').append('<abbr title="Toplam talep sayısının bir önceki aya göre durumu" class="initialism">' + total_request_icon + '</abbr>');

    $('#total-sale-box .spinners').remove();
    $('#total-sale-box h5').append(total_sale + ' - ' + year_total_sale);
    if(total_sale_icon == '-'){
        total_sale_icon = '<img src="img/icons/grayline.png">';
    }else if (total_sale_icon == 'up'){
        total_sale_icon = '<img src="img/icons/greenarrow.png">';
    }else if (total_sale_icon == 'down'){
        total_sale_icon = '<img src="img/icons/redarrow.png">';
    }
    $('#total-sale-box-icon').append('<abbr title="Toplam sipariş sayısının bir önceki aya göre durumu" class="initialism">' + total_sale_icon + '</abbr>');

    $('#total-activity-box .spinners').remove();
    $('#total-activity-box h5').append(total_activity + ' - ' + year_total_activity);
    if(total_activity_icon == '-'){
        total_activity_icon = '<img src="img/icons/grayline.png">';
    }else if (total_activity_icon == 'up'){
        total_activity_icon = '<img src="img/icons/greenarrow.png">';
    }else if (total_activity_icon == 'down'){
        total_activity_icon = '<img src="img/icons/redarrow.png">';
    }
    $('#total-activity-box-icon').append('<abbr title="Toplam aktivite sayısının bir önceki aya göre durumu" class="initialism">' + total_activity_icon + '</abbr>');

}

async function getMonthlyProfitRates(){

    let data = await serviceGetMonthlyProfitRates(dash_owner);
    let profit_rates = data.profit_rates;
    let previous_profit_rates = data.previous_profit_rates;

    let xAxisArray = [];
    let yAxisArray = [];
    let yAxisArrayPrevious = [];

    let currentYear = new Date().getFullYear();
    let previousYear = currentYear - 1;

    $.each(profit_rates, function (i, rate) {
        xAxisArray.push(rate.month);
        yAxisArray.push(rate.profit_rate);
    });

    $.each(previous_profit_rates, function (i, rate) {
        yAxisArrayPrevious.push(rate.profit_rate);
    });

    var apexLineChartOptions = {
        chart: {
            height: 300,
            type: 'line',
            toolbar: { show: false }
        },
        title: {
            style: {
                fontSize: '14px',
                fontWeight: 'bold',
                fontFamily: FONT_FAMILY,
                color: COLOR_DARK
            },
        },
        legend: {
            show: true,
            position: 'top',
            offsetY: 0,
            horizontalAlign: 'right',
            floating: true
        },
        colors: ['#8d989d', app.color.theme],
        dataLabels: {
            enabled: true,
            offsetY: 1,
            background: {
                enabled: true,
                padding: 4,
                borderRadius: 4,
                borderWidth: 0,
                opacity: 0.9,
                dropShadow: { enabled: false }
            },
            style: {
                fontSize: '11px',
                fontFamily: app.font.bodyFontFamily,
                fontWeight: 500
            }
        },
        stroke: { curve: 'smooth', width: 3 },
        grid: {
            row: {
                colors: [app.color.bodyBg, 'transparent'],
                opacity: 0.5
            }
        },
        series: [
            { name: previousYear, data: yAxisArrayPrevious },
            { name: currentYear, data: yAxisArray }
        ],
        markers: { size: 4 },
        xaxis: { categories: xAxisArray },
        yaxis: { min: 0, max: 250 }
    };
    $('#chart-profit-rates-monthly .spinners').remove();
    var apexLineChart = new ApexCharts(
        document.querySelector('#chart-profit-rates-monthly'),
        apexLineChartOptions
    );
    apexLineChart.render();

    reLoadGrid();

}

async function getMonthlyTurningRates(){

    let data = await serviceGetMonthlyTurningRates(dash_owner);
    let turning_rates = data.turning_rates;
    let previous_turning_rates = data.previous_turning_rates;

    let xAxisArray = [];
    let yAxisArray = [];
    let yAxisArrayPrevious = [];

    let currentYear = new Date().getFullYear();
    let previousYear = currentYear - 1;

    $.each(turning_rates, function (i, rate) {
        xAxisArray.push(rate.month);
        yAxisArray.push(rate.turning_rate);
    });

    $.each(previous_turning_rates, function (i, rate) {
        yAxisArrayPrevious.push(rate.turning_rate);
    });

    var apexLineChartOptions = {
        chart: {
            height: 300,
            type: 'line',
            toolbar: { show: false }
        },
        title: {
            style: {
                fontSize: '14px',
                fontWeight: 'bold',
                fontFamily: FONT_FAMILY,
                color: COLOR_DARK
            },
        },
        legend: {
            show: true,
            position: 'top',
            offsetY: 0,
            horizontalAlign: 'right',
            floating: true
        },
        colors: ['#8d989d', app.color.indigo],
        dataLabels: {
            enabled: true,
            offsetY: 1,
            background: {
                enabled: true,
                padding: 4,
                borderRadius: 4,
                borderWidth: 0,
                opacity: 0.9,
                dropShadow: { enabled: false }
            },
            style: {
                fontSize: '11px',
                fontFamily: app.font.bodyFontFamily,
                fontWeight: 500
            }
        },
        stroke: { curve: 'smooth', width: 3 },
        grid: {
            row: {
                colors: [app.color.bodyBg, 'transparent'],
                opacity: 0.5
            }
        },
        series: [
            { name: previousYear, data: yAxisArrayPrevious },
            { name: currentYear, data: yAxisArray }
        ],
        markers: { size: 4 },
        xaxis: { categories: xAxisArray },
        yaxis: { min: 0, max: 50 }
    };
    $('#chart-turning-rates-monthly .spinners').remove();
    var apexLineChart = new ApexCharts(
        document.querySelector('#chart-turning-rates-monthly'),
        apexLineChartOptions
    );
    apexLineChart.render();

    reLoadGrid();

}

async function getCustomerByNotSaleLongTimes(){

    let data = await serviceGetCustomerByNotSaleLongTimes();
    let companies = data.companies;

    $('#customer-not-sale-timely-table').siblings('.spinners').remove();

    $("#customer-not-sale-timely-table").dataTable().fnDestroy();
    $('#customer-not-sale-timely-table tbody tr').remove();
    $.each(companies, function (i, company) {
        let item = '<tr>\n' +
            '           <td style="max-width: 30px;">'+ company.id +'</td>\n' +
            '           <td>'+ company.name +'</td>\n' +
            '           <td>'+ formatDateASC(company.last_sale_date, '-') +'</td>\n' +
            '       </tr>';
        $('#customer-not-sale-timely-table tbody').append(item);
    });

    $('#customer-not-sale-timely-table').DataTable({
        responsive: false,
        columnDefs: [],
        dom: 'Brtip',
        paging: true,
        buttons: [],
        scrollX: true,
        language: {
            url: "services/Turkish.json"
        },
        order: false,
    });
    reLoadGrid();
}

async function getCustomerByNotSale(){

    let data = await serviceGetCustomerByNotSale();
    let companies = data.companies;

    $('#customer-not-sale-table').siblings('.spinners').remove();

    $("#customer-not-sale-table").dataTable().fnDestroy();
    $('#customer-not-sale-table tbody tr').remove();
    $.each(companies, function (i, company) {
        let item = '<tr>\n' +
            '           <td style="max-width: 30px;">'+ company.id +'</td>\n' +
            '           <td>'+ company.name +'</td>\n' +
            '       </tr>';
        $('#customer-not-sale-table tbody').append(item);
    });

    $('#customer-not-sale-table').DataTable({
        responsive: false,
        columnDefs: [],
        dom: 'Brtip',
        paging: true,
        buttons: [],
        scrollX: true,
        language: {
            url: "services/Turkish.json"
        },
        order: false,
    });

    reLoadGrid();
}

async function getBestSalesLastNinetyDays(){

    let data = await serviceGetBestSalesLastNinetyDays(dash_owner);
    let by_sale_price = data.by_sale_price;
    let by_profit_rate = data.by_profit_rate;

    $('#best-sales-by-price-table').siblings('.spinners').remove();

    $('#best-sales-by-price-table tbody tr').remove();
    $.each(by_sale_price, function (i, sale) {
        let item = '<tr>\n' +
            '           <td>'+ sale.short_code +'-'+ sale.id +'</td>\n' +
            '           <td>'+ changeCommasToDecimal(sale.offer_total) +' TRY</td>\n' +
            '       </tr>';
        $('#best-sales-by-price-table tbody').append(item);
    });

    $('#best-sales-by-profit-table').siblings('.spinners').remove();

    $('#best-sales-by-profit-table tbody tr').remove();
    $.each(by_profit_rate, function (i, sale) {
        let item = '<tr>\n' +
            '           <td>'+ sale.short_code +'-'+ sale.id +'</td>\n' +
            '           <td>'+ sale.profit_rate +'</td>\n' +
            '       </tr>';
        $('#best-sales-by-profit-table tbody').append(item);
    });

    reLoadGrid();
}

async function initSaleHistory(){
    let data = await serviceGetSaleHistoryActions();
    let actions = data.actions;

    $('#sales-history-table').siblings('.spinners').remove();
    $('#sales-history-table tbody tr').remove();

    $.each(actions, function (i, action) {
        let last_time = formatDateAndTimeDESC(action.last_status.created_at, "/");

        previous_status_name = action.previous_status.status_name;
        if (action.previous_status == 0){
            previous_status_name = '-';
        }

        let item = '<tr>\n' +
            '           <td>\n' +
            '               <span class="d-flex align-items-center">\n' +
            '                   <i class="bi bi-circle-fill fs-6px text-theme me-2"></i>\n' +
            '                   '+ action.sale.customer_name +'\n' +
            '               </span>\n' +
            '           </td>\n' +
            '           <td>\n' +
            '               <span class="d-flex align-items-center">\n' +
            '                   <i class="bi bi-circle-fill fs-6px text-theme me-2"></i>\n' +
            '                   '+ action.last_status.user_name +'\n' +
            '               </span>\n' +
            '           </td>\n' +
            '           <td><small>'+ last_time +'</small></td>\n' +
            '           <td class="text-right">\n' +
            '               <span class="badge bg-theme text-theme-900 bg-opacity-50 rounded-0 pt-5px" style="min-height: 18px">'+ previous_status_name +'</span>\n' +
            '           </td>\n' +
            '           <td>\n' +
            '               <i class="bi bi-arrow-90deg-right"></i>\n' +
            '               <span class="badge bg-theme text-white rounded-0 pt-5px" style="min-height: 18px">'+ action.last_status.status_name +'</span>\n' +
            '           </td>\n' +
            '       </tr>';

        $('#sales-history-table tbody').append(item);
    });
}
