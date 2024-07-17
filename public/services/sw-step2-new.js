(function($) {
    "use strict";

	$(document).ready(function() {
        $(":input").inputmask();
        $("#update_currency_rate_usd").maskMoney({thousands:'.', decimal:','});
        $("#update_currency_rate_eur").maskMoney({thousands:'.', decimal:','});
        $("#update_currency_rate_gbp").maskMoney({thousands:'.', decimal:','});

        $('#update_currency_rate_form').submit(function (e){
            e.preventDefault();
            updateCurrencyRate();
        });


	});

	$(window).load(async function() {

		checkLogin();
		checkRole();
        await checkCurrencyLog();
        await initRequestDetail();
        await initSaleDetail();

	});

})(window.jQuery);
let sale_currency;

function checkRole(){
	return true;
}

async function initRequestDetail(){
    let request_id = getPathVariable('sw-2-new');
    let data = await serviceGetOfferRequestById(request_id);
    $('#sw_customer_name').text(data.offer_request.company.name);
}
async function initSaleDetail(){
    let request_id = getPathVariable('sw-2-new');
    let data = await serviceGetSaleByRequestId(request_id);
    let sale = data.sale;

    if (sale.currency != null){
        document.getElementById('update_currency_rate_currency').value = sale.currency;
    }
    if (sale.usd_rate != null){
        document.getElementById('update_currency_rate_usd').value = changeCommasToDecimal(sale.usd_rate);
    }
    if (sale.eur_rate != null){
        document.getElementById('update_currency_rate_eur').value = changeCommasToDecimal(sale.eur_rate);
    }
    if (sale.gbp_rate != null){
        document.getElementById('update_currency_rate_gbp').value = changeCommasToDecimal(sale.gbp_rate);
    }
}

async function checkCurrencyLog(){
    let request_id = getPathVariable('sw-2-new');
    let data = await serviceGetCheckSaleCurrencyLog(request_id);
    console.log(data)
    if (data.has_currency == true){
        let sale = data.sale;
        sale_currency = sale.currency;
        document.getElementById('update_currency_rate_currency').value = sale_currency;
        document.getElementById('update_currency_rate_usd').value = changeCommasToDecimal(sale.usd_rate);
        document.getElementById('update_currency_rate_eur').value = changeCommasToDecimal(sale.eur_rate);
        document.getElementById('update_currency_rate_gbp').value = changeCommasToDecimal(sale.gbp_rate);

        await initNewOfferDetail();
        await initOfferDetail();
    }else{
        $('#sw-2-message').html('');
        $('#sw-2-message').append('<span class="text-danger">Lütfen öncelikle döviz kurunu onaylayınız.</span>');
        await initEmptyTables();
    }
}

async function initEmptyTables(){
    if ($.fn.DataTable.isDataTable("#sales-detail")) {
        $("#sales-detail").DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable("#offer-detail")) {
        $("#offer-detail").DataTable().destroy();
    }
    $('#offer-detail').DataTable({
        responsive: false,
        columnDefs: [
            {responsivePriority: 1, targets: 0},
            {responsivePriority: 2, targets: -1}
        ],
        dom: 'Bfrtip',
        buttons: [],
        paging: false,
        scrollX: true,
        language: {
            url: "services/Turkish.json"
        },
        order: [[0, 'asc']]
    });
    $('#sales-detail').DataTable({
        responsive: false,
        columnDefs: [
            {responsivePriority: 1, targets: 0},
            {responsivePriority: 2, targets: -1}
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                text: 'Teklif Oluştur',
                action: function (e, dt, node, config) {
                    addSale();
                }
            }
        ],
        paging: false,
        scrollX: true,
        language: {
            url: "services/Turkish.json"
        },
        order: [[0, 'asc']]
    });
}

async function initOfferDetail(){

    let request_id = getPathVariable('sw-2-new');
    let data = await serviceGetOffersByRequestId(request_id);
    console.log(data)

        let offers = data.offers;
        console.log(offers)
        if ($.fn.DataTable.isDataTable("#sales-detail")) {
            $("#sales-detail").DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable("#offer-detail")) {
            $("#offer-detail").DataTable().destroy();
        }
        $('#offer-detail tbody > tr').remove();
        let rowNo = 0;
        $.each(offers, function (i, offer) {
            $.each(offer.products, function (j, product) {
                rowNo ++;
                let cheap_fast = '';
                if (product.cheapest){
                    cheap_fast += '<span class="badge border border-warning bg-warning text-dark-600 px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center">En Ucuz</span>';
                }
                if (product.fastest){
                    cheap_fast += '<span class="badge border border-lime bg-lime text-dark-600 px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center">En Hızlı</span>';
                }

                let measurement_name = '';
                if (Lang.getLocale() == 'tr'){
                    measurement_name = product.measurement_name_tr;
                }else{
                    measurement_name = product.measurement_name_tr;
                }
                let item = '<tr id="productRow' + product.id + '">\n' +
                    '           <td>' + product.sequence + '</td>\n' +
                    '           <td>' + product.id + '</td>\n' +
                    '              <td>\n' +
                    '                  <div class="btn-list">\n' +
                    '                      <button type="button" id="sale_btn_'+ product.id +'" onclick="addSaleTableProduct(this);" class="btn btn-sm btn-theme"><span class="fe fe-edit"> Teklife Ekle</span></button>\n' +
                    '                  </div>\n' +
                    '              </td>\n' +
                    '           <td>'+ cheap_fast +'</td>\n' +
                    '           <td class="d-none">' + offer.offer_id + '</td>\n' +
                    '           <td class="d-none">' + product.product_detail.id + '</td>\n' +
                    '           <td class="d-none">' + offer.supplier_id + '</td>\n' +
                    '           <td>' + offer.company_name + '</td>\n' +
                    '           <td>' + checkNull(product.product_detail.product_name) + '</td>\n' +
                    '           <td>' + checkNull(product.product_detail.ref_code) + '</td>\n' +
                    '           <td>' + checkNull(product.lead_time) + '</td>\n' +
                    '           <td>' + checkNull(measurement_name) + '</td>\n' +
                    '           <td>' + changeCommasToDecimal(product.pcs_price) + '</td>\n' +
                    '           <td>' + changeCommasToDecimal(product.total_price) + '</td>\n' +
                    '           <td>' + checkNull(product.discount_rate) + '</td>\n' +
                    '           <td>' + changeCommasToDecimal(product.discounted_price) + '</td>\n' +
                    '           <td>' + checkNull(product.vat_rate) + '</td>\n' +
                    '           <td>' + checkNull(product.currency) + '</td>\n' +
                    '           <td>' + checkNull(product.request_quantity) + '</td>\n' +
                    '           <td>' + checkNull(product.quantity) + '</td>\n' +
                    '           <td class="d-none">' + changeCommasToDecimal(product.converted_price) + '</td>\n' +
                    '           <td class="d-none">' + product.converted_currency + '</td>\n' +
                    '       </tr>';
                $('#offer-detail tbody').append(item);
            });
        });

        $('#offer-detail').DataTable({
            responsive: false,
            columnDefs: [
                {responsivePriority: 1, targets: 0},
                {responsivePriority: 2, targets: -1}
            ],
            dom: 'Bfrtip',
            buttons: [],
            paging: false,
            scrollX: true,
            language: {
                url: "services/Turkish.json"
            },
            order: [[0, 'asc']]
        });
        $('#sales-detail').DataTable({
            responsive: false,
            columnDefs: [
                {responsivePriority: 1, targets: 0},
                {responsivePriority: 2, targets: -1}
            ],
            dom: 'Bfrtip',
            buttons: [
                {
                    text: 'Teklif Oluştur',
                    action: function (e, dt, node, config) {
                        addSale();
                    }
                }
            ],
            paging: false,
            scrollX: true,
            language: {
                url: "services/Turkish.json"
            },
            order: [[0, 'asc']]
        });

}

async function initNewOfferDetail(){

    let request_id = getPathVariable('sw-2-new');
    let data = await serviceGetNewOffersByRequestId(request_id);
    console.log(data)

    $('#new-offer-detail').siblings('.card-loading-row').remove();

    let products = data.products;
    let companies = data.companies;
    // $("#new-offer-detail").dataTable().fnDestroy();
    $('#new-offer-detail tbody > tr').remove();

    let head_first = '<th rowspan="2" class="border-bottom-0 bg-dark-100">N#</th>\n' +
        '             <th rowspan="2" class="border-bottom-0 bg-dark-100" style="border-right-width: 5px; border-right-color: rgba(0, 0, 0, 0.6);">Ürün Adı</th>';
    let head_second = '';


    let total_row = '<tr>';
    total_row += '<td colspan="2" class="border-bottom-0 bg-dark-100" style="border-right-width: 5px; border-right-color: rgba(0, 0, 0, 0.6);"></td>\n';
    $.each(companies, function (i, company) {
        head_first += '<th colspan="6" class="border-bottom-0 bg-dark-100" style="border-right-width: 5px; border-right-color: rgba(0, 0, 0, 0.6);">'+ company.company_name +'</th>';
        head_second += '<th class="border-bottom-0"></th>\n' +
            '           <th class="border-bottom-0">Ucuz, Hızlı</th>\n' +
            '           <th class="border-bottom-0">Birim</th>\n' +
            '           <th class="border-bottom-0">Toplam</th>\n' +
            '           <th class="border-bottom-0">Miktar</th>\n' +
            '           <th class="border-bottom-0" style="border-right-width: 5px; border-right-color: rgba(0, 0, 0, 0.6);">T. Süresi</th>';


        let footer_text = '';
        footer_text += 'Tedarik Fiyatı: '+ changeDecimalToPrice(company.supply_price) + ' ' + sale_currency;
        total_row += '<td colspan="6" class="border-bottom-0 fw-bold" style="border-right-width: 5px; border-right-color: rgba(0, 0, 0, 0.6);">'+ footer_text +'</td>\n';
    });

    let head = '<tr>\n' +
        '           '+ head_first +
        '       </tr>\n' +
        '       <tr>\n' +
        '           '+ head_second +
        '       </tr>';

    $('#new-offer-detail thead').append(head);

    $.each(products, function (i, product) {
        let item = '<tr>';

        item += '   <td class="bg-dark-100">' + checkNull(product.sequence) + '</td>\n' +
        '           <td class="bg-dark-100" style="border-right-width: 5px; border-right-color: rgba(0, 0, 0, 0.6); max-width: 500px; overflow: hidden;">' + checkNull(product.product_name) + '</td>\n';

        $.each(product.companies, function (i, company) {
            if (company.offer_product == null){
                item += '   <td></td>\n' +
                    '       <td></td>\n' +
                    '       <td></td>\n' +
                    '       <td></td>\n' +
                    '       <td></td>\n' +
                    '       <td style="border-right-width: 5px; border-right-color: rgba(0, 0, 0, 0.6);"></td>\n';
            }else{
                let cheap_fast = '';
                if (company.offer_product.cheapest){
                    cheap_fast += '<span class="badge border border-warning bg-warning text-dark-600 px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center">En Ucuz</span>';
                }
                if (company.offer_product.fastest){
                    cheap_fast += '<span class="badge border border-lime bg-lime text-dark-600 px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center">En Hızlı</span>';
                }

                item += '   <td>\n' +
                    '           <div class="btn-list">\n' +
                    '               <button type="button" onclick="triggerOfferButton(this, '+ company.offer_product.id +');" class="btn btn-sm btn-theme"><span class="fe fe-edit">Teklife Ekle</span></button>\n' +
                    '           </div>\n' +
                    '       </td>\n' +
                    '       <td>' + cheap_fast + '</td>\n' +
                    '       <td>' + changeCommasToDecimal(company.offer_product.pcs_price) + checkNull(company.offer_product.currency) + '</td>\n' +
                    '       <td>' + changeCommasToDecimal(company.offer_product.total_price) + checkNull(company.offer_product.currency) + ' (' + changeCommasToDecimal(company.offer_product.converted_price) + checkNull(company.offer_product.converted_currency) + ')</td>\n' +
                    '       <td>RQ:' + checkNull(product.request_quantity) + ' - OQ:' + checkNull(company.offer_product.quantity) + '</td>\n' +
                    '       <td style="border-right-width: 5px; border-right-color: rgba(0, 0, 0, 0.6);">' + checkNull(company.offer_product.lead_time) + '</td>\n';

            }
        });

        item += '</tr>';
        $('#new-offer-detail tbody').append(item);

    });

    total_row += '</tr>';
    $("#new-offer-detail tfoot").append(total_row);

    $('#new-offer-detail').DataTable({
        responsive: false,
        dom: 'Bfrtip',
        paging: false,
        scrollX: true,
        language: {
            url: "services/Turkish.json"
        },
        order: [[0, 'asc']],
        fixedColumns: {
            left: 2
        }
    });

}


function triggerOfferButton(element, id){
    let text = $(element).text();
    if (text == 'Teklife Ekle'){
        $(element).text('Tekliften Çıkar');
    }else{
        $(element).text('Teklife Ekle');
    }
    $('#sale_btn_'+id).click();
}




async function addSaleTableProduct(el){
    let tableSales = $("#sales-detail").DataTable();
    let tableOffer = $("#offer-detail").DataTable();
    $(el).attr('onclick','addOfferTableProduct(this);');
    $(el).text('Tekliften Çıkar');
    let row = tableOffer.row( $(el).parents('tr') );
    let rowNode = row.node();
    row.remove();

    tableSales
        .row.add( rowNode )
        .draw();
    await initSaleTableFooter();
}

async function initSaleTableFooter(){
    let tableSales = $("#sales-detail").DataTable();
    let total = 0;
    let currency = '';
    tableSales.rows().every(function() {
        let data = this.data();
        // if (data[15] == ''){
        //     total += parseFloat(changePriceToDecimal(data[13]));
        // }else{
        //     total += parseFloat(changePriceToDecimal(data[15]));
        // }
        total += parseFloat(changePriceToDecimal(data[20]));
        currency = data[21];
    });

    $("#sales-detail tfoot tr").remove();
    let footer = '<tr>\n' +
        '             <th colspan="22" class="border-bottom-0">Satış Para Birimi Cinsinden Tedarik Fiyatı: '+ changeDecimalToPrice(total) +' '+ currency +'</th>\n' +
        '         </tr>';
    $("#sales-detail tfoot").append(footer);
}

async function addOfferTableProduct(el){
    let tableSales = $("#sales-detail").DataTable();
    let tableOffer = $("#offer-detail").DataTable();
    $(el).attr('onclick','addSaleTableProduct(this);');
    $(el).text('Teklife Ekle');
    let row = tableSales.row( $(el).parents('tr') );
    let rowNode = row.node();
    row.remove();

    tableOffer
        .row.add( rowNode )
        .draw();
    await initSaleTableFooter();
}

async function addSale(){
    let user_id = localStorage.getItem('userId');
    let request_id = getPathVariable('sw-2-new');
    let table = $('#sales-detail').DataTable();
    let rows = table.rows();

    let offers = [];
    if (rows.count() === 0){
        alert("Öncelikle seçim yapmalısınız.");
    }else {
        rows.every(function (rowIdx, tableLoop, rowLoop) {
            let item = {
                "offer_product_id": this.data()[1],
                "offer_id": this.data()[4],
                "product_id": this.data()[5],
                "supplier_id": this.data()[6],
                "lead_time": this.data()[10],
                "measurement": this.data()[11],
                "pcs_price": changePriceToDecimal(this.data()[12]),
                "total_price": changePriceToDecimal(this.data()[13]),
                "discount_rate": this.data()[14],
                "discounted_price": changePriceToDecimal(this.data()[15]),
                "vat_rate": this.data()[16],
                "currency": this.data()[17],
                "request_quantity": this.data()[18],
                "offer_quantity": this.data()[19],
                "converted_price": changePriceToDecimal(this.data()[20]),
                "converted_currency": this.data()[21],
            }
            offers.push(item);
        });
        console.log(offers)

        let formData = JSON.stringify({
            "user_id": parseInt(user_id),
            "request_id": request_id,
            "offers": offers
        });

        console.log(formData);

        let data = await servicePostAddSale(formData);
        console.log(data)
        if (data.status == "success") {
            window.location.href = "/sw-3/" + data.object.sale_id;
        }else{
            alert("Hata Oluştu");
        }

    }
}

async function getCurrencyLog(){
    let data = await serviceGetLastCurrencyLog();
    let log = data.currency_log;
    document.getElementById('update_currency_rate_usd').value = changeCommasToDecimal(log.usd);
    document.getElementById('update_currency_rate_eur').value = changeCommasToDecimal(log.eur);
    document.getElementById('update_currency_rate_gbp').value = changeCommasToDecimal(log.gbp);
}
async function updateCurrencyRate() {
    let request_id = getPathVariable('sw-2-new');
    console.log(request_id)

    let formData = JSON.stringify({
        "currency": document.getElementById('update_currency_rate_currency').value,
        "usd_rate": changePriceToDecimal(document.getElementById('update_currency_rate_usd').value),
        "eur_rate": changePriceToDecimal(document.getElementById('update_currency_rate_eur').value),
        "gbp_rate": changePriceToDecimal(document.getElementById('update_currency_rate_gbp').value)
    });
    console.log(formData)

    let returned = await servicePostAddSaleCurrencyLog(formData, request_id);
    if (returned){
        await checkCurrencyLog();
    }else{
        alert("Kur Eklerken Hata Oluştu")
    }
}
