(function($) {
    "use strict";

	$(document).ready(function() {

        $('#update_detail_form').submit(function (e){
            e.preventDefault();
            updateDetail();
        });

	});

	$(window).load(async function() {
		checkLogin();
		checkRole();

        let offer_id = getPathVariable('offer-print');
        await initOffer(offer_id);
        await initDetail(offer_id);
	});

})(window.jQuery);
let short_code;

function checkRole(){
	return true;
}

function printOffer(){
	window.print();
}

async function generatePDF(){
    let lang = document.getElementById('lang').value;
    let owner_id = document.getElementById('owners').value;
    let offer_id = getPathVariable('offer-print');

    // Fetch the PDF data
    const pdfData = await serviceGetGenerateRfqPDF(lang, owner_id, offer_id);

    // Create a link element to download the PDF
    const link = document.createElement('a');
    link.href = `${pdfData.object.file_url}`;
    link.target = '_blank';
    link.download = `${pdfData.object.file_name}`;
    link.textContent = 'Download PDF';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function changeOwner(){
	let owner = document.getElementById('owners').value;
    let offer_id = getPathVariable('offer-print');
    // await initContact(owner, offer_id);
}

async function initContact(contact_id, offer_id){


    let data = await serviceGetContactById(contact_id);
    let contact = data.contact;
    console.log(contact)
    short_code = contact.short_code;
    let width = '150px';
    if (contact_id == 3){
        width = '250px';
    }

    $('#offer-print #logo img').remove();
    $('#offer-print #logo').append('<img style="width: '+width+'" src="'+ contact.logo +'">');

    if (contact_id == 1){
        $('#print-footer').addClass('lenis-footer');
        $('.footer-spacer').addClass('lenis-spacer');
    }
    if (contact_id == 3){
        $('#print-footer').addClass('semy-footer');
        $('.footer-spacer').addClass('semy-spacer');
    }
    $('#print-footer img').remove();
    $('#print-footer').append('<img src="'+ contact.footer +'" alt="" class="w-100">');

    let today = new Date();
    let dd = String(today.getDate()).padStart(2, '0');
    let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    let yyyy = today.getFullYear();
    today = dd + '/' + mm + '/' + yyyy;


    $('#offer-print .logo-header .date').text(Lang.get("strings.Date") +': '+ today);

    $('#offer-print .contact-col address').text('');

    $('#offer-print .contact-col address').append('<strong>'+ contact.name +'</strong><br>');
    if (contact.registration_no != '') {
        $('#offer-print .contact-col address').append('<b>' + Lang.get("strings.Registration No") + ' :</b> ' + contact.registration_no + '&nbsp;&nbsp;&nbsp;');
    }
    let lang = Lang.getLocale();
    if (contact.registration_office != '' && lang != 'en') {
        $('#offer-print .contact-col address').append('<b>' + Lang.get("strings.Registration Office") + ' :</b> ' + contact.registration_office);
    }
    $('#offer-print .contact-col address').append('<br>');
    $('#offer-print .contact-col address').append('<b>'+ Lang.get("strings.Address") +'</b><br>'+ contact.address +'<br>');
    $('#offer-print .contact-col address').append('<b>'+ Lang.get("strings.Phone") +':</b> '+ contact.phone +'<br>');
    $('#offer-print .contact-col address').append('<b>'+ Lang.get("strings.Email") +':</b> '+ contact.email +'');

}

async function initOffer(offer_id){

    let data = await serviceGetOfferById(offer_id);
    let offer = data.offer;
    let company = offer.company;
    console.log(offer);

    await initContact(offer.owner_id, offer_id);
    await getOwnersAddSelectId('owners');
    document.getElementById('owners').value = offer.owner_id;

    $('#offer-print .supplier-col address').text('');
    $('#offer-print .supplier-col address').append('<strong>'+ company.name +'</strong><br>'+ company.address +'<br>'+ company.phone +'<br>'+ company.email +'');
    $('#offer-print .logo-header .offer-id').text(short_code+'-RFQ-'+offer.global_id);

    $('#offer-detail tbody > tr').remove();

    $.each(offer.products, function (i, product) {
        let measurement_name = '';
        if (Lang.getLocale() == 'tr'){
            measurement_name = product.measurement_name_tr;
        }else{
            measurement_name = product.measurement_name_en;
        }
        let item = '<tr>\n' +
            '           <td class="text-center">' + (i+1) + '</td>\n' +
            '           <td class="text-capitalize">' + checkNull(product.ref_code) + '</td>\n' +
            '           <td class="text-capitalize">' + checkNull(product.product_name) + '</td>\n' +
            '           <td class="text-center">' + checkNull(product.quantity) + '</td>\n' +
            '           <td class="text-center text-capitalize">' + checkNull(measurement_name) + '</td>\n' +
            '           <td class="text-center"></td>\n' +
            '           <td class="text-center"></td>\n' +
            '           <td class="text-center"></td>\n' +
            '       </tr>';
        $('#offer-detail tbody').append(item);
    });




    $('#no-pdf').addClass('d-none');
    $('#has-pdf').addClass('d-none');
    if (offer.rfq_url == null){
        $('#no-pdf').removeClass('d-none');
    }else{
        $('#has-pdf').removeClass('d-none');
        $('#showPdf').attr('href', offer.rfq_url);
    }

}

async function initDetail(offer_id){
    let data = await serviceGetRfqDetailById(offer_id);
    let detail = data.rfq_detail;

    if (detail != null) {
        $('#update_offer_note').summernote('code', checkNull(detail.note));
        document.getElementById('note').innerHTML = checkNull(detail.note);
    }
}

async function updateDetail(){
    let offer_id = getPathVariable('offer-print');
    let note = $('#update_offer_note').summernote('code');

    let formData = JSON.stringify({
        "offer_id": offer_id,
        "note": note
    });
    let returned = await servicePostUpdateRfqDetail(formData);

    if (returned){
        await initDetail(offer_id);
    }else{
        alert("Hata Oluştu");
    }
}
