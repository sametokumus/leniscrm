(function($) {
    "use strict";

	$(document).ready(function() {

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

        $("#add_payment_form").submit(function( event ) {
            event.preventDefault();

            addPayment();

        });

        $("#update_payment_form").submit(function( event ) {
            event.preventDefault();

            updatePayment();

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
    let total = sale.grand_total;
    if (sale.grand_total_with_shipping != null){
        total = sale.grand_total_with_shipping;
    }
    let remaining_price = '-';

    $('#customer-name').append('<a href="/company-detail/'+sale.request.company.id+'" class="text-decoration-none text-white">'+sale.request.company.name+'</a>');
    $('#customer-employee').append('Müşteri Yetkilisi: '+sale.request.company_employee.name);
    $('#owner-employee').append('Firma Yetkilisi: '+sale.request.authorized_personnel.name+' '+sale.request.authorized_personnel.surname);

    $('#total-price').text(changeCommasToDecimal(total) + ' ' + sale.currency);
    $('#remaining-price').text(changeCommasToDecimal(remaining_price) + ' ' + sale.currency);


    $('#sale-date').append(formatDateAndTimeDESC(sale.created_at, '/'));

    // $('#total-sale').text(stats.total_sale);
    // $('#active-sale').text(stats.active_sale);
    // $('#total-product').text(stats.total_product);


}

async function initPayments(sale_id){
    let data = await serviceGetAccountingPayments(sale_id);
    let transaction = data.transaction;
    console.log(transaction)
    $("#payments").dataTable().fnDestroy();
    $('#payments tbody > tr').remove();
    $.each(transaction.payments, function (i, payment) {
        let item = '<tr>\n' +
            '              <td>'+ (i+1) +'</td>\n' +
            '              <td>'+ payment.id +'</td>\n' +
            '              <td>'+ checkNull(payment.payment_type) +'</td>\n' +
            '              <td>'+ checkNull(payment.payment_method) +'</td>\n' +
            '              <td>'+ checkNull(payment.payment_term) +'</td>\n' +
            '              <td>'+ checkNull(payment.due_date) +'</td>\n' +
            '              <td>'+ checkNull(payment.payment_price) +'</td>\n' +
            '              <td>'+ checkNull(payment.currency) +'</td>\n' +
            '              <td></td>\n' +
            '              <td>\n' +
            '                  <div class="btn-list">\n' +
            '                      <button id="bEdit" type="button" class="btn btn-sm btn-theme" onclick="openUpdatePaymentModal(\''+ payment.payment_id +'\')">\n' +
            '                          <span class="fe fe-edit"> </span> Düzenle\n' +
            '                      </button>\n' +
            '                  </div>\n' +
            '              </td>\n' +
            '          </tr>';
        $('#payments tbody').append(item);
    });

    $('#payments').DataTable({
        responsive: false,
        columnDefs: [
            {responsivePriority: 1, targets: 0},
            {responsivePriority: 2, targets: -1}
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                text: 'Ödeme Ekle',
                action: function (e, dt, node, config) {
                    openAddPaymentModal();
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

async function openAddPaymentModal(){
    $('#addPaymentModal').modal('show');
    await getPaymentTypesAddSelectId('add_payment_payment_type');
    await getPaymentMethodsAddSelectId('add_payment_payment_method');
}
async function addPayment(){
    let sale_id = document.getElementById('add_payment_sale_id').value;
    let payment_type = document.getElementById('add_payment_payment_type').value;
    let payment_method = document.getElementById('add_payment_payment_method').value;
    let payment_term = document.getElementById('add_payment_payment_term').value;
    let due_date = document.getElementById('add_payment_due_date').value;
    let payment_price = document.getElementById('add_payment_price').value;
    let currency = document.getElementById('add_payment_currency').value;
    let formData = JSON.stringify({
        "sale_id": sale_id,
        "payment_type": payment_type,
        "payment_method": payment_method,
        "payment_term": payment_term,
        "due_date": formatDateAndTime(due_date, "-"),
        "payment_price": changePriceToDecimal(payment_price),
        "currency": currency,
    });
    let returned = await servicePostAddProduct(formData);
    if(returned){
        $("#add_product_form").trigger("reset");
        $('#addProductModal').modal('hide');
        initProducts();
    }
}


async function openUpdatePaymentModal(payment_id){
    $('#updatePaymentModal').modal('show');
    await getPaymentTypesAddSelectId('update_payment_payment_type');
    await getPaymentMethodsAddSelectId('update_payment_payment_method');

    let data = await serviceGetAccountingPaymentById(payment_id);
    console.log(data)
}


async function addPaymentPaymentTermWithButton(day){
    document.getElementById('add_payment_payment_term').value = day;

    let currentDate = new Date();
    let dueDate = new Date();
    dueDate.setDate(currentDate.getDate() + parseInt(day));
    dueDate = dueDate.toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    dueDate = formatDateSplit(dueDate, '-', '.');
    document.getElementById('add_payment_due_date').value = dueDate;
}
