(function($) {
    "use strict";

    $(document).ready(function() {

        $(":input").inputmask();
        $("#update_quote_freight").maskMoney({thousands:'.', decimal:','});
        $("#update_quote_vat_rate").maskMoney({thousands:'.', decimal:','});

        $('#update_quote_form').submit(function (e){
            e.preventDefault();
            updateQuote();
        });

        $('#add_note_form').submit(function (e){
            e.preventDefault();
            addNote();
        });

        $('#update_note_form').submit(function (e){
            e.preventDefault();
            updateNote();
        });

    });

    $(window).load(async function() {
        checkLogin();
        checkRole();

        let sale_id = getPathVariable('order-confirmation-print');
        await initContact(1, sale_id);
        await initSale(sale_id);
        await initBankInfoSelect();
        await getOwnersAddSelectId('owners');
        document.getElementById('owners').value = 1;
    });

})(window.jQuery);
let short_code;

function checkRole(){
    return true;
}

function printOffer(){
    window.print();
}

async function changeOwner(){
    let owner = document.getElementById('owners').value;
    let sale_id = getPathVariable('order-confirmation-print');
    await initContact(owner, sale_id);
    await initSale(sale_id);
}

async function initContact(contact_id, sale_id){

    let data = await serviceGetContactById(contact_id);
    let contact = data.contact;
    short_code = contact.short_code;

    $('#order-confirmation-print #logo img').remove();
    $('#order-confirmation-print #logo').append('<img src="'+ contact.logo +'">');

    $('#print-footer img').remove();
    $('#print-footer').append('<img src="'+ contact.footer +'" alt="" class="w-100">');

    let today = new Date();
    let dd = String(today.getDate()).padStart(2, '0');
    let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    let yyyy = today.getFullYear();
    today = dd + '/' + mm + '/' + yyyy;
    $('#order-confirmation-print .logo-header .date').text(Lang.get("strings.Date") +': '+ today);
    // $('#order-confirmation-print .logo-header .offer-id').append(sale_id);
    $('.company-signature .name').text(contact.authorized_name);
    document.getElementById('signature').src = contact.signature;

    $('#order-confirmation-print .contact-col address').text('');
    $('#order-confirmation-print .contact-col address').append('<strong>'+ contact.name +'</strong><br><b>'+ Lang.get("strings.Registration No") +' :</b> '+ contact.registration_no +'<br><b>'+ Lang.get("strings.Address") +'</b><br>'+ contact.address +'<br><b>'+ Lang.get("strings.Phone") +':</b> '+ contact.phone +'<br><b>'+ Lang.get("strings.Email") +':</b> '+ contact.email +'');

}

async function initSale(sale_id){
    let data = await serviceGetSaleById(sale_id);
    console.log(data);
    let sale = data.sale;
    let company = sale.request.company;
    document.getElementById('buyer_name').innerHTML = '<b>'+ Lang.get("strings.Customer") +' :</b> '+ company.name;
    document.getElementById('buyer_registration_number').innerHTML = '<b>'+ Lang.get("strings.Registration No") +' :</b> '+ checkNull(company.registration_number);
    document.getElementById('buyer_address').innerHTML = '<b>'+ Lang.get("strings.Address") +' :</b> '+ company.address;
    document.getElementById('buyer_phone').innerHTML = '<b>'+ Lang.get("strings.Phone") +' :</b> '+ company.phone;
    document.getElementById('buyer_email').innerHTML = '<b>'+ Lang.get("strings.Email") +' :</b> '+ company.email;

    $('#order-confirmation-print .logo-header .offer-id').text(short_code+'-OC-'+sale.id);

    $('#sale-detail tbody > tr').remove();

    let currency = '';
    $.each(sale.sale_offers, function (i, product) {
        currency = product.currency;
        let item = '<tr>\n' +
            '           <td>' + (i+1) + '</td>\n' +
            '           <td>' + checkNull(product.product_ref_code) + '</td>\n' +
            '           <td>' + checkNull(product.product_name) + '</td>\n' +
            '           <td>' + checkNull(product.offer_quantity) + ' ' + checkNull(product.measurement_name) + '</td>\n' +
            '           <td>' + changeCommasToDecimal(product.offer_pcs_price) + ' '+ currency +'</td>\n' +
            '           <td>' + changeCommasToDecimal(product.offer_price) + ' '+ currency +'</td>\n' +
            '       </tr>';
        $('#sale-detail tbody').append(item);
    });

    if (sale.sub_total != null) {
        let item = '<tr>\n' +
            '           <td colspan="5" class="fw-800 text-right text-uppercase">' + Lang.get("strings.Sub Total") + '</td>\n' +
            '           <td>' + changeCommasToDecimal(sale.sub_total) + ' '+ currency +'</td>\n' +
            '       </tr>';
        $('#sale-detail tbody').append(item);
    }

    if (sale.freight != null) {
        let item = '<tr>\n' +
            '           <td colspan="5" class="fw-800 text-right text-uppercase">' + Lang.get("strings.Freight") + '</td>\n' +
            '           <td>' + changeCommasToDecimal(sale.freight) + ' '+ currency +'</td>\n' +
            '       </tr>';
        $('#sale-detail tbody').append(item);
    }

    if (sale.vat != null) {
        let item = '<tr>\n' +
            '           <td colspan="5" class="fw-800 text-right text-uppercase">' + Lang.get("strings.Vat") + '</td>\n' +
            '           <td>' + changeCommasToDecimal(sale.vat) + ' '+ currency +'</td>\n' +
            '       </tr>';
        $('#sale-detail tbody').append(item);
    }

    if (sale.grand_total != null) {
        let item = '<tr>\n' +
            '           <td colspan="5" class="fw-800 text-right text-uppercase">' + Lang.get("strings.Grand Total") + '</td>\n' +
            '           <td>' + changeCommasToDecimal(sale.grand_total) + ' '+ currency +'</td>\n' +
            '       </tr>';
        $('#sale-detail tbody').append(item);
    }

}

async function openAddNoteModal(){
    $("#addNoteModal").modal('show');
}
async function addNote(){
    let sale_id = getPathVariable('order-confirmation-print');
    let note = $('#add_order_confirmation_note').summernote('code');
    let formData = JSON.stringify({
        "sale_id": sale_id,
        "note": note
    });
    let returned = await servicePostAddOrderConfirmationDetail(formData);
    if (returned){
        $("#add_note_form").trigger("reset");
        $("#addNoteModal").modal('hide');
        initSale(sale_id);
    }
}

async function openUpdateNoteModal(){
    $("#updateNoteModal").modal('show');
    await initUpdateNoteModal();
}
async function initUpdateNoteModal(){
    $("#updateNoteModal").modal('show');
    let sale_id = getPathVariable('order-confirmation-print');
    let data = await serviceGetOrderConfirmationDetailById(sale_id);
    let order_confirmation_detail = data.order_confirmation_detail;
    $('#update_order_confirmation_note').summernote('code', order_confirmation_detail.note);
}
async function updateNote(){
    let sale_id = getPathVariable('order-confirmation-print');
    let note = $('#update_purchasing_order_note').summernote('code');
    let formData = JSON.stringify({
        "sale_id": sale_id,
        "note": note
    });
    let returned = await servicePostUpdateOrderConfirmationDetail(formData);
    if (returned){
        $("#update_note_form").trigger("reset");
        $('#note *').remove();
        $("#updateNoteModal").modal('hide');
        initSale(sale_id);
    }
}

async function initBankInfoSelect(){
    let data = await serviceGetBankInfos();
    let bank_infos = data.bank_infos;

    $.each(bank_infos, function (i, info) {
        let item = '<option value="'+ info.id +'">'+ info.name +'</option>';
        $('#select_bank_info').append(item);
    });

}
async function openAddBankInfoModal(){
    $("#addBankInfoModal").modal('show');
}
async function changeBankInfo(){
    $('#bank-details *').remove();

    let bank_id = document.getElementById('select_bank_info').value;
    if(bank_id == 0){
        return false;
    }else{
        let data = await serviceGetBankInfoById(bank_id);
        let info = data.bank_info;
        $('#bank-details').append(info.detail);
    }

    $("#addBankInfoModal").modal('hide');
}
