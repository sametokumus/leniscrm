(function($) {
    "use strict";

	 $(document).ready(function() {



	});

	$(window).load(async function() {

		checkLogin();
        staff_dash_currency = 'TRY';
        let user_id = localStorage.getItem('userId');

        // staff_dash_currency = localStorage.getItem('staff_dash_currency');
        // if (staff_dash_currency == null){
        //     staff_dash_currency = 'TRY';
        //     localStorage.setItem('staff_dash_currency', 'TRY');
        //     document.getElementById('staff_dash_currency').value = 'TRY';
        // }else{
        //     document.getElementById('staff_dash_currency').value = staff_dash_currency;
        // }

        initStaffTargets(user_id);
        getLastMonthSales(user_id);
        getApprovedMonthlySales(user_id);
        getCompletedMonthlySales(user_id);
        getPotentialMonthlySales(user_id);
        getCancelledMonthlySales(user_id);

	});

})(window.jQuery);
let staff_dash_currency;
function changeDashCurrency(){
    staff_dash_currency = document.getElementById('staff_dash_currency').value;
    localStorage.setItem('staff_dash_currency', staff_dash_currency);
    location.reload();
}

async function getLastMonthSales(user_id){
    let data = await serviceGetLastMonthSalesByAdmin(user_id);
    let sales = data.sales;
    let continue_data = sales.continue.continue_serie_try.map(parseFloat);
    let approved_data = sales.approved.approved_serie_try.map(parseFloat);
    let completed_data = sales.completed.completed_serie_try.map(parseFloat);
    let cancelled_data = sales.cancelled.cancelled_serie_try.map(parseFloat);
    let day_count = sales.day_count;

    $('#monthly-approved-box h4').append(changeCommasToDecimal(sales.approved.try_sale) + ' TRY');
    let text1 = '<i class="fa fa-dollar-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.approved.usd_sale) +'<br/>\n' +
        '       <i class="fa fa-euro-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.approved.eur_sale);
    $('#monthly-approved-text').append(text1);

    $('#monthly-completed-box h4').append(changeCommasToDecimal(sales.completed.try_sale) + ' TRY');
    let text2 = '<i class="fa fa-dollar-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.completed.usd_sale) +'<br/>\n' +
        '       <i class="fa fa-euro-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.completed.eur_sale);
    $('#monthly-completed-text').append(text2);

    $('#monthly-continue-box h4').append(changeCommasToDecimal(sales.continue.try_sale) + ' TRY');
    let text3 = '<i class="fa fa-dollar-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.continue.usd_sale) +'<br/>\n' +
        '       <i class="fa fa-euro-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.continue.eur_sale);
    $('#monthly-continue-text').append(text3);

    $('#monthly-cancelled-box h4').append(changeCommasToDecimal(sales.cancelled.try_sale) + ' TRY');
    let text4 = '<i class="fa fa-dollar-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.cancelled.usd_sale) +'<br/>\n' +
        '       <i class="fa fa-euro-sign fa-fw me-1"></i> '+ changeCommasToDecimal(sales.cancelled.eur_sale);
    $('#monthly-cancelled-text').append(text4);

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
    console.log(data)
    let sales = data.sales.reverse();
    console.log(sales)

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
    console.log(xAxisArray)
    console.log(yAxisArray)

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

    console.log(data)
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
            '           <td>'+ target.admin.name +' '+ target.admin.surname +'</td>\n' +
            '           <td>'+ target.type_name +'</td>\n' +
            '           <td>'+ changeCommasToDecimal(target.target) +' '+ target.currency +'</td>\n' +
            '           <td>'+ target.month_name +'</td>\n' +
            '           <td>'+ target.year +'</td>\n' +
            '           <td>'+ target.status.rate +'%</td>\n' +
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