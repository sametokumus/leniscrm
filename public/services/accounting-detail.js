(function($) {
    "use strict";

	$(document).ready(function() {

        $('#addPaymentModal').modal({
            backdrop: 'static', // Prevent closing when clicking outside
            keyboard: false // Prevent closing with the keyboard ESC key
        });

        $('#updatePaymentModal').modal({
            backdrop: 'static', // Prevent closing when clicking outside
            keyboard: false // Prevent closing with the keyboard ESC key
        });

        $("#add_payment_payment_price").maskMoney({thousands:'.', decimal:','});
        $("#update_payment_payment_price").maskMoney({thousands:'.', decimal:','});

        $('#add_payment_payment_term').on('keyup', function (e){
            e.preventDefault();
            let val = document.getElementById('add_payment_payment_term').value;
            if (val < 1){
                document.getElementById('add_payment_payment_term').value = 1;
                val = 1;
            }

            let currentDate = new Date();
            let dueDate = new Date();
            dueDate.setDate(currentDate.getDate() + parseInt(val));
            dueDate = dueDate.toLocaleDateString('tr-TR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            dueDate = formatDateSplit(dueDate, '-', '.');
            document.getElementById('add_payment_due_date').value = dueDate;
        });

        $('#update_payment_payment_term').on('keyup', function (e){
            e.preventDefault();
            let val = document.getElementById('update_payment_payment_term').value;
            if (val < 1){
                document.getElementById('update_payment_payment_term').value = 1;
                val = 1;
            }

            let currentDate = new Date();
            let dueDate = new Date();
            dueDate.setDate(currentDate.getDate() + parseInt(val));
            dueDate = dueDate.toLocaleDateString('tr-TR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            dueDate = formatDateSplit(dueDate, '-', '.');
            document.getElementById('update_payment_due_date').value = dueDate;
        });

        $("#add_payment_form").submit(function( event ) {
            event.preventDefault();

            addPayment();

        });

        $("#update_payment_form").submit(function( event ) {
            event.preventDefault();

            updatePayment();

        });

        $("#update_status_form").submit(function( event ) {
            event.preventDefault();

            updateStatus();

        });

	});

	$(window).load(async function() {

		checkLogin();
        checkRole();
        let sale_id = getPathVariable('accounting-detail');
        await initSaleStats(sale_id);
        await initPayments(sale_id);

    });

})(window.jQuery);

function checkRole(){
    return true;
}

async function initSaleStats(sale_id){
    let data = await serviceGetSaleDetailInfo(sale_id);
    let sale = data.sale;
    console.log(sale)


    $('#sale-code').text(sale.owner.short_code+'-'+sale.id);
    $('#customer-name').html('<a href="/customer-dashboard/'+sale.request.company.id+'" class="text-decoration-none text-dark">'+sale.request.company.name+'</a>');
    if (sale.request.company_employee != null) {
        $('#customer-employee').text('Müşteri Yetkilisi: ' + sale.request.company_employee.name);
    }
    if (sale.request.authorized_personnel != null) {
        $('#owner-employee').text('Firma Yetkilisi: '+sale.request.authorized_personnel.name+' '+sale.request.authorized_personnel.surname);
    }

    $('#total-price').text(changeCommasToDecimal(sale.total_price) + ' ' + sale.currency);
    $('#advance-price').text('Avans Tutarı: ' + changeCommasToDecimal(sale.advance_price) + ' ' + sale.currency);
    $('#remaining-price').text(changeCommasToDecimal(sale.remaining_price) + ' ' + sale.currency);


    $('#sale-date').text(formatDateAndTimeDESC(sale.created_at, '/'));

    // $('#total-sale').text(stats.total_sale);
    // $('#active-sale').text(stats.active_sale);
    // $('#total-product').text(stats.total_product);

    let remaining_message = '';
    if (parseInt(sale.total_product_count) > sale.packing_count) {
        remaining_message = '<span class="text-danger"><b>Paketlenmeyen ürünler var.</b></span>';
    } else {
        remaining_message = '<span class="text-theme"><b>Paketleme tamamlandı.</b></span>';
    }
    $('#packing-message').html(remaining_message);

}

async function initPayments(sale_id){
    let data = await serviceGetAccountingPayments(sale_id);
    let packing_lists = data.packing_lists;
    console.log(data)
    $("#payments").dataTable().fnDestroy();
    $('#payments tbody > tr').remove();

    if (packing_lists != null) {
        $.each(packing_lists, function (i, packing_list) {
            let payment = packing_list.transaction;
            let packing_list_id = packing_list.packing_list_id;
            let payment_id = '';
            let payment_type = '';
            let payment_method = '';
            let invoice_number = '';
            let invoice_date = '';
            let payment_term = '';
            let due_date = '';
            let payment_price = '';
            let payment_tax_rate = '';
            let payment_tax = '';
            let payment_total = '';
            let currency = '';
            let status_span = '';
            let waybill = '';
            let buttons = '';
            if (payment != null) {
                payment_id = payment.payment_id;
                payment_type = checkNull(payment.payment_type);
                payment_method = checkNull(payment.payment_method);
                invoice_number = checkNull(payment.invoice_number);
                invoice_date = formatDateASC(payment.invoice_date, "-");
                payment_term = checkNull(payment.payment_term);
                due_date = formatDateASC(payment.due_date, "-");
                payment_price = changeCommasToDecimal(payment.payment_price);
                payment_tax_rate = payment.payment_tax_rate;
                payment_tax = changeCommasToDecimal(payment.payment_tax);
                payment_total = changeCommasToDecimal(payment.payment_total);

                currency = checkNull(payment.currency);
                if (payment.payment_status_id == 1) {
                    status_span = '<span style="cursor:pointer;" class="badge border border-danger text-danger px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center" onclick="openStatusModal(\'' + payment.payment_id + '\', 1)"><i class="fa fa-circle fs-9px fa-fw me-5px"></i> Ödeme bekleniyor</span>';
                } else if (payment.payment_status_id == 2) {
                    status_span = '<span style="cursor:pointer;" class="badge border border-green text-green px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center" onclick="openStatusModal(\'' + payment.payment_id + '\', 2)"><i class="fa fa-circle fs-9px fa-fw me-5px"></i> Ödeme tamamlandı</span>';
                }
                buttons += '<button id="bEdit" type="button" class="btn btn-sm btn-theme" onclick="openUpdatePaymentModal(\'' + payment_id + '\')">\n' +
                    '           <span class="fe fe-edit"> </span> Ödemeyi Düzenle\n' +
                    '       </button>\n';
                buttons += '<a href="/packing-list-print/'+ packing_list_id +'" target="_blank" class="btn btn-sm btn-theme">\n' +
                    '           <span class="fe fe-edit"> </span> Packing List PDF\n' +
                    '       </a>\n';
                buttons += '<a href="/pl-invoice-print/'+ packing_list_id +'" target="_blank" class="btn btn-sm btn-theme">\n' +
                    '           <span class="fe fe-edit"> </span> Inv. PDF\n' +
                    '       </a>\n';
            }else{
                buttons += '<button id="bEdit" type="button" class="btn btn-sm btn-theme" onclick="openAddPaymentModal(\'' + packing_list_id + '\', \'' + packing_list.list_grand_total + '\', \'' + packing_list.currency + '\')">\n' +
                    '           <span class="fe fe-edit"> </span> Ödeme Ekle\n' +
                    '       </button>\n';
                buttons += '<a href="/packing-list-print/'+ packing_list_id +'" target="_blank" class="btn btn-sm btn-theme">\n' +
                    '           <span class="fe fe-edit"> </span> Packing List PDF\n' +
                    '       </a>\n';
            }

            if (packing_list.waybill == 0){
                waybill = '<span style="cursor:pointer;" class="badge border border-warning text-warning px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center" onclick="setWaybill(\'' + sale_id + '\', \'' + packing_list.packing_list_id + '\', 1)"><i class="fa fa-circle fs-9px fa-fw me-5px"></i> İrsaliye Kesilmedi</span>';
            }else{
                waybill = '<span style="cursor:pointer;" class="badge border border-theme text-theme px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center" onclick="setWaybill(\'' + sale_id + '\', \'' + packing_list.packing_list_id + '\', 0)"><i class="fa fa-circle fs-9px fa-fw me-5px"></i> İrsaliye Kesildi</span>';
            }

            let item = '<tr>\n' +
                '              <td>' + (i + 1) + '</td>\n' +
                '              <td class="d-none">' + packing_list.packing_list_id + '</td>\n' +
                '              <td>' + packing_list.count + '</td>\n' +
                '              <td>' + packing_list.list_grand_total + '</td>\n' +
                '              <td>' + packing_list.currency + '</td>\n' +
                '              <td>' + payment_type + '</td>\n' +
                '              <td>' + payment_method + '</td>\n' +
                '              <td>' + invoice_number + '</td>\n' +
                '              <td>' + invoice_date + '</td>\n' +
                '              <td>' + payment_term + '</td>\n' +
                '              <td>' + due_date + '</td>\n' +
                '              <td>' + payment_price + '</td>\n' +
                '              <td>' + payment_tax_rate + '</td>\n' +
                '              <td>' + payment_tax + '</td>\n' +
                '              <td>' + payment_total + '</td>\n' +
                '              <td>' + currency + '</td>\n' +
                '              <td>'+ status_span +'</td>\n' +
                '              <td>'+ waybill +'</td>\n' +
                '              <td>\n' +
                '                  <div class="btn-list">\n' +
                '                      '+ buttons +'\n' +
                '                  </div>\n' +
                '              </td>\n' +
                '          </tr>';
            $('#payments tbody').append(item);
        });
    }

    $('#payments').DataTable({
        responsive: false,
        buttons: [],
        columnDefs: [
            {responsivePriority: 1, targets: 0},
            {responsivePriority: 2, targets: -1}
        ],
        dom: 'Bfrtip',
        paging: false,
        scrollX: true,
        language: {
            url: "services/Turkish.json"
        },
        order: [[0, 'asc']]
    });

}

async function openAddPaymentModal(packing_list_id, price, currency){
    $('#addPaymentModal').modal('show');
    await getPaymentTypesAddSelectId('add_payment_payment_type');
    await getPaymentMethodsAddSelectId('add_payment_payment_method');
    document.getElementById('add_payment_payment_price').value = price;
    document.getElementById('add_payment_currency').value = currency;
    document.getElementById('add_payment_packing_list_id').value = packing_list_id;

    let sale_id = getPathVariable('accounting-detail');
    let data = await serviceGetAccountingPaymentType(sale_id);
    console.log(data)
    let term = data.term;
    if (term.length != 0){
        document.getElementById('add_payment_payment_type').value = term.payment_type_id;
        addPaymentPaymentTermWithButton(term.expiry);
    }
}
async function addPayment(){
    let sale_id = getPathVariable('accounting-detail');
    let payment_type = document.getElementById('add_payment_payment_type').value;
    let payment_method = document.getElementById('add_payment_payment_method').value;
    let payment_term = document.getElementById('add_payment_payment_term').value;
    let invoice_date = document.getElementById('add_payment_invoice_date').value;
    let due_date = document.getElementById('add_payment_due_date').value;
    let payment_price = document.getElementById('add_payment_payment_price').value;
    let payment_tax_rate = document.getElementById('add_payment_tax_rate').value;
    let payment_tax = document.getElementById('add_payment_price_with_tax').value;
    let currency = document.getElementById('add_payment_currency').value;
    let invoice_number = document.getElementById('add_payment_invoice_number').value;
    let packing_list_id = document.getElementById('add_payment_packing_list_id').value;
    console.log(due_date)
    let formData = JSON.stringify({
        "sale_id": sale_id,
        "payment_type": payment_type,
        "payment_method": payment_method,
        "payment_term": payment_term,
        "invoice_number": invoice_number,
        "invoice_date": formatDateDESC2(invoice_date, "-", "-"),
        "due_date": formatDateDESC2(due_date, "-", "-"),
        "payment_price": changePriceToDecimal(payment_price),
        "currency": currency,
        "packing_list_id": packing_list_id,
        "payment_tax_rate": payment_tax_rate,
        "payment_tax": changePriceToDecimal(payment_tax),
    });
    console.log(formData)
    let returned = await servicePostAddAccountingPayment(formData);
    if(returned){
        // $("#add_payment_form").trigger("reset");
        // $('#addPaymentModal').modal('hide');
        // initPayments(sale_id);
        location.reload();
    }
}


async function openUpdatePaymentModal(payment_id){
    $('#updatePaymentModal').modal('show');
    await getPaymentTypesAddSelectId('update_payment_payment_type');
    await getPaymentMethodsAddSelectId('update_payment_payment_method');

    let data = await serviceGetAccountingPaymentById(payment_id);
    console.log(data)
    let payment = data.payment;
    document.getElementById('update_payment_id').value = payment_id;
    document.getElementById('update_payment_payment_type').value = payment.payment_type;
    document.getElementById('update_payment_payment_method').value = payment.payment_method;
    document.getElementById('update_payment_invoice_number').value = checkNull(payment.invoice_number);
    document.getElementById('update_payment_invoice_date').value = formatDateASC(payment.invoice_date, "-");
    document.getElementById('update_payment_payment_term').value = payment.payment_term;
    document.getElementById('update_payment_due_date').value = formatDateASC(payment.due_date, "-");
    document.getElementById('update_payment_payment_price').value = changeCommasToDecimal(payment.payment_price);
    document.getElementById('update_payment_currency').value = payment.currency;
    document.getElementById('update_payment_packing_list_id').value = payment.packing_list_id;
    document.getElementById('update_payment_tax_rate').value = payment.payment_tax_rate;
    document.getElementById('update_payment_price_with_tax').value = changeCommasToDecimal(payment.payment_tax);
}
async function updatePayment(){
    let sale_id = getPathVariable('accounting-detail');
    let payment_id = document.getElementById('update_payment_id').value;
    let payment_type = document.getElementById('update_payment_payment_type').value;
    let payment_method = document.getElementById('update_payment_payment_method').value;
    let payment_term = document.getElementById('update_payment_payment_term').value;
    let invoice_date = formatDateDESC2(document.getElementById('update_payment_invoice_date').value, "-", "-");
    let due_date = formatDateDESC2(document.getElementById('update_payment_due_date').value, "-", "-");
    let payment_price = document.getElementById('update_payment_payment_price').value;
    let payment_tax_rate = document.getElementById('update_payment_tax_rate').value;
    let payment_tax = document.getElementById('update_payment_price_with_tax').value;
    let currency = document.getElementById('update_payment_currency').value;
    let invoice_number = document.getElementById('update_payment_invoice_number').value;
    console.log(due_date)
    let formData = JSON.stringify({
        "payment_id": payment_id,
        "sale_id": sale_id,
        "payment_type": payment_type,
        "payment_method": payment_method,
        "payment_term": payment_term,
        "invoice_number": invoice_number,
        "invoice_date": invoice_date,
        "due_date": due_date,
        "payment_price": changePriceToDecimal(payment_price),
        "currency": currency,
        "payment_tax_rate": payment_tax_rate,
        "payment_tax": changePriceToDecimal(payment_tax),
    });
    console.log(formData)
    let returned = await servicePostUpdateAccountingPayment(formData);
    if(returned){
        // $("#add_payment_form").trigger("reset");
        // $('#addPaymentModal').modal('hide');
        // initPayments(sale_id);
        location.reload();
    }
}


async function addPaymentInvoiceDateToday(){
    let currentDate = new Date();
    currentDate = currentDate.toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    currentDate = formatDateSplit(currentDate, '-', '.');
    document.getElementById('add_payment_invoice_date').value = currentDate;
}
async function addPaymentPaymentTermWithButton(day){
    document.getElementById('add_payment_payment_term').value = day;
    let currentDate;
    let invoice_date = document.getElementById('add_payment_invoice_date').value;
    if (invoice_date == '') {
        currentDate = new Date();
    }else {
        let [day, month, year] = invoice_date.split('-');
        currentDate = new Date(year, month - 1, day);
    }

    let dueDate = new Date(currentDate);
    dueDate.setDate(currentDate.getDate() + parseInt(day));
    dueDate = dueDate.toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    dueDate = formatDateSplit(dueDate, '-', '.');
    document.getElementById('add_payment_due_date').value = dueDate;
}
async function updatePaymentInvoiceDateToday(){
    let currentDate = new Date();
    currentDate = currentDate.toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    currentDate = formatDateSplit(currentDate, '-', '.');
    document.getElementById('update_payment_invoice_date').value = currentDate;
}
async function updatePaymentPaymentTermWithButton(day){
    document.getElementById('update_payment_payment_term').value = day;
    let currentDate;
    let invoice_date = document.getElementById('update_payment_invoice_date').value;
    if (invoice_date == '') {
        currentDate = new Date();
    }else {
        let [day, month, year] = invoice_date.split('-');
        currentDate = new Date(year, month - 1, day);
    }
    let dueDate = new Date();
    dueDate.setDate(currentDate.getDate() + parseInt(day));
    dueDate = dueDate.toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    dueDate = formatDateSplit(dueDate, '-', '.');
    document.getElementById('update_payment_due_date').value = dueDate;
}


async function addPaymentTaxWithButton(rate){
    let price = document.getElementById('add_payment_payment_price').value;
    price = changePriceToDecimal(price);
    let price_tax = '';
    if (rate != 0) {
        price_tax = price / 100 * rate;
        let price_total = parseFloat(price) + parseFloat(price_tax);
        document.getElementById('add_payment_tax_rate').value = rate;
        document.getElementById('add_payment_price_with_tax').value = changeCommasToDecimal(parseFloat(price_tax).toFixed(2));
        document.getElementById('add_payment_price_total').value = changeCommasToDecimal(parseFloat(price_total).toFixed(2));
    }else{
        document.getElementById('add_payment_tax_rate').value = rate;
    }

}
async function updatePaymentTaxWithButton(rate){
    let price = document.getElementById('update_payment_payment_price').value;
    price = changePriceToDecimal(price);
    let price_tax = '';
    if (rate != 0) {
        price_tax = price / 100 * rate;
        document.getElementById('update_payment_tax_rate').value = rate;
        document.getElementById('update_payment_price_with_tax').value = changeCommasToDecimal(parseFloat(price_tax).toFixed(2));
    }else{
        document.getElementById('update_payment_tax_rate').value = rate;
    }
}

function openStatusModal(payment_id, status_id){
    $('#updateStatusModal').modal('show');
    document.getElementById('update_status_payment_id').value = payment_id;
    document.getElementById('update_payment_status').value = status_id;
}
async function updateStatus(){
    let sale_id = getPathVariable('accounting-detail');
    let status_id = document.getElementById('update_payment_status').value;
    let payment_id = document.getElementById('update_status_payment_id').value;
    let formData = JSON.stringify({
        "payment_id": payment_id,
        "status_id": status_id
    });
    let returned = await servicePostUpdateAccountingPaymentStatus(formData);
    if(returned){
        $("#update_status_form").trigger("reset");
        $('#updateStatusModal').modal('hide');
        initSaleStats(sale_id);
        await initPayments(sale_id);
    }
}


async function setWaybill(sale_id, packing_list_id, status_id){
    let formData = JSON.stringify({
        "packing_list_id": packing_list_id,
        "status_id": status_id
    });
    let returned = await servicePostUpdateAccountingWaybill(formData);
    if(returned){
        await initPayments(sale_id);
    }
}
