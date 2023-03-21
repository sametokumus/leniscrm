(function($) {
    "use strict";

	$(document).ready(function() {

        $(":input").inputmask();
        $("#add_offer_product_pcs_price").maskMoney({thousands:'.', decimal:','});
        $("#add_offer_product_total_price").maskMoney({thousands:'.', decimal:','});
        $("#add_offer_product_discounted_price").maskMoney({thousands:'.', decimal:','});
        $("#update_offer_product_pcs_price").maskMoney({thousands:'.', decimal:','});
        $("#update_offer_product_total_price").maskMoney({thousands:'.', decimal:','});
        $("#update_offer_product_discounted_price").maskMoney({thousands:'.', decimal:','});

		$('#add_offer_form').submit(function (e){
			e.preventDefault();
            addOffer();
		});

        $('#add_offer_request_product_button').click(function (e){
            e.preventDefault();
            let refcode = document.getElementById('add_offer_request_product_refcode').value;
            let product_name = document.getElementById('add_offer_request_product_name').value;
            let quantity = document.getElementById('add_offer_request_product_quantity').value;
            if (refcode == '' || product_name == '' || quantity == "0"){
                alert('Formu Doldurunuz');
                return false;
            }
            addProductToTable(refcode, product_name, quantity);
        });

        $('#add_offer_product_form').submit(function (e){
            e.preventDefault();
            addOfferProduct();
        });

        $('#update_offer_product_form').submit(function (e){
            e.preventDefault();
            updateOfferProduct();
        });
	});

	$(window).load(async function() {

		checkLogin();
		checkRole();
		// await initPage();
        await initOfferRequest();
        await initOffers();

	});

})(window.jQuery);
let short_code;
let global_id;

function checkRole(){
	return true;
}

async function initPage(){
    await getAdminsAddSelectId('update_offer_request_authorized_personnel');
    await getCompaniesAddSelectId('update_offer_request_company');
}

async function initEmployeeSelect(){
    let company_id = document.getElementById('update_offer_request_company').value;
    await getEmployeesAddSelectId(company_id, 'update_offer_request_company_employee');
}

async function openAddOfferModal(){
    $("#addOfferModal").modal('show');
    await getSuppliersAddSelectId('add_offer_company');
}


async function initOfferRequest(){
    let request_id = getPathVariable('offer');
    let data = await serviceGetOfferRequestById(request_id);
    let offer_request = data.offer_request;

    $("#offer-request-products").dataTable().fnDestroy();
    $('#offer-request-products tbody > tr').remove();

    $.each(offer_request.products, function (i, product) {
        let item = '<tr id="productRow' + product.id + '">\n' +
            '           <td>' + product.id + '</td>\n' +
            '           <td>' + product.ref_code + '</td>\n' +
            '           <td>' + product.product_name + '</td>\n' +
            '           <td>' + product.quantity + '</td>\n' +
            '           <td>' + product.measurement_name + '</td>\n' +
            '       </tr>';
        $('#offer-request-products tbody').append(item);
    });

    let productDatatable = $('#offer-request-products').DataTable({
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: -1 }
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                text: 'Seçili Ürünler için Teklif İste',
                action: function ( e, dt, node, config ) {
                    openAddOfferModal(productDatatable.rows( { selected: true } ));
                }
            },
            'selectAll',
            'selectNone',
            // 'excel',
            // 'pdf'
        ],
        pageLength : 20,
        language: {
            url: "services/Turkish.json"
        },
        order: [[0, 'asc']],
        select: {
            style: 'multi'
        }
    });

    let data2 = await serviceGetContactById(offer_request.owner_id);
    let contact = data2.contact;
    short_code = contact.short_code;
    global_id = offer_request.global_id;
}

async function addOffer(){
    let user_id = localStorage.getItem('userId');
    let request_id = getPathVariable('offer');
    let supplier_id = document.getElementById('add_offer_company').value;
    let table = $('#offer-request-products').DataTable();
    let rows = table.rows({ selected: true } );

    let products = [];
    if (rows.count() === 0){
        alert("Öncelikle seçim yapmalısınız.");
        $('#addOfferModal').modal('hide');
        $("#add_offer_form").trigger("reset");
    }else {
        rows.every(function (rowIdx, tableLoop, rowLoop) {
            let item = {
                "request_product_id": this.data()[0]
            }
            products.push(item);
        });

        let formData = JSON.stringify({
            "user_id": parseInt(user_id),
            "request_id": request_id,
            "supplier_id": supplier_id,
            "products": products
        });

        console.log(formData);

        let returned = await servicePostAddOffer(formData);
        console.log(returned)
        if (returned){
            $('#offer-request-products-body tr').removeClass('selected');
            $("#add_offer_form").trigger("reset");
            $('#addOfferModal').modal('hide');
            initOffers();

            let products = $('#offer-request-products').DataTable();
            products.rows().deselect();
        }else{
            alert("Hata Oluştu");
        }

    }
}

async function initOffers(){
    let request_id = getPathVariable('offer');
    let data = await serviceGetOffersByRequestId(request_id);
    let offers = data.offers;

    $("#offers").dataTable().fnDestroy();
    $('#offers tbody > tr').remove();

    $.each(offers, function (i, offer) {
        let bg_color = '';
        if (offer.products != null){
            if (offer.products[0].total_price != null){
                bg_color = 'bg-secondary';
            }
        }

        let item = '<tr id="offerRow' + offer.id + '" class="'+ bg_color +'">\n' +
            '           <td>' + offer.id + '</td>\n' +
            '           <td>' + short_code + '-RFQ-' + global_id + '</td>\n' +
            '           <td>' + offer.company_name + '</td>\n' +
            '           <td>' + offer.product_count + '</td>\n' +
            '              <td>\n' +
            '                  <div class="btn-list">\n' +
            '                      <button onclick="openOfferDetailModal(\'' + offer.offer_id + '\');" class="btn btn-sm btn-theme"><span class="fe fe-edit"> Fiyatları Gir</span></button>\n' +
            '                      <a href="offer-print/'+ offer.offer_id +'" class="btn btn-sm btn-theme"><span class="fe fe-edit"> RFQ PDF</span></a>\n' +
            '                  </div>\n' +
            '              </td>\n' +
            '       </tr>';
        $('#offers tbody').append(item);
    });

    $('#offers').DataTable({
        responsive: true,
        columnDefs: [
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: -1 }
        ],
        dom: 'Bfrtip',
        buttons: [
            'excel',
            'pdf'
        ],
        pageLength : 20,
        scrollX: true,
        language: {
            url: "services/Turkish.json"
        },
        order: [[0, 'asc']]
    });
}

async function openOfferDetailModal(offer_id){
    $("#offerDetailModal").modal('show');
    await initOfferDetailModal(offer_id);
}

function initMaskMoney() {
    $('input[id^="DTE_Field_pcs_price"]').maskMoney({thousands:'.', decimal:','});
    $('input[id^="DTE_Field_total_price"]').maskMoney({thousands:'.', decimal:','});
    $('input[id^="DTE_Field_discount_rate"]').maskMoney({thousands:'.', decimal:','});
    $('input[id^="DTE_Field_discounted_price"]').maskMoney({thousands:'.', decimal:','});
    $('input[id^="DTE_Field_vat_rate"]').maskMoney({thousands:'.', decimal:','});
}

let editor;
let table;
// Activate an inline edit on click of a table cell
$('#offer-detail').on( 'click', 'tbody td.row-edit', function (e) {
    editor.inline( table.cells(this.parentNode, '*').nodes(), {
        submitTrigger: -1,
        submitHtml: '<i class="fas fa-lg fa-fw me-2 fa-save"/>'
    } );
    initMaskMoney();
} );

async function initOfferDetailModal(offer_id){
    console.log(offer_id)
    document.getElementById('offer-detail-modal-offer-id').value = offer_id;
    let data = await serviceGetOfferById(offer_id);
    let offer = data.offer;
    console.log(offer)

    $("#offer-detail").dataTable().fnDestroy();
    $('#offer-detail tbody > tr').remove();

    editor = new $.fn.dataTable.Editor( {
        data: offer.products,
        table: "#offer-detail",
        idSrc: "id",
        fields: [ {
            name: "id",
            type: "readonly",
            attr: {
                class: 'form-control'
            }
        },{
            name: "date_code",
            attr: {
                class: 'form-control'
            }
        },{
            name: "package_type",
            attr: {
                class: 'form-control'
            }
        },{
            name: "quantity",
            attr: {
                class: 'form-control'
            }
        },{
            name: "pcs_price",
            attr: {
                class: 'form-control'
            }
        },{
            name: "total_price",
            type: "readonly",
            attr: {
                class: 'form-control'
            }
        }, {
            name: "discount_rate",
            attr: {
                class: 'form-control'
            }
        }, {
            name: "discounted_price",
            attr: {
                class: 'form-control'
            }
        }, {
            name: "vat_rate",
            attr: {
                class: 'form-control'
            }
        }, {
            name: "currency",
            type: "select",
            options: [
                { value: 'TRY', label: 'TRY' },
                { value: 'EUR', label: 'EUR' },
                { value: 'USD', label: 'USD' },
                { value: 'GBP', label: 'GBP' }
            ],
            attr: {
                class: 'form-control'
            }
        }, {
            name: "lead_time",
            attr: {
                class: 'form-control'
            }
        }
        ]
    } );

    editor.on('preSubmit', async function(e, data, action) {
        if (action !== 'remove') {
            let id = editor.field('id').val();
            let date_code = editor.field('date_code').val();
            let package_type = editor.field('package_type').val();
            let quantity = editor.field('quantity').val();
            let pcs_price = editor.field('pcs_price').val();
            let total_price = editor.field('total_price').val();
            let discount_rate = editor.field('discount_rate').val();
            let discounted_price = editor.field('discounted_price').val();
            let vat_rate = editor.field('vat_rate').val();
            let currency = editor.field('currency').val();
            let lead_time = editor.field('lead_time').val();

            let formData = JSON.stringify({
                "id": id,
                "date_code": date_code,
                "package_type": package_type,
                "quantity": quantity,
                "pcs_price": changePriceToDecimal(pcs_price),
                "total_price": changePriceToDecimal(total_price),
                "discount_rate": changePriceToDecimal(discount_rate),
                "discounted_price": changePriceToDecimal(discounted_price),
                "vat_rate": changePriceToDecimal(vat_rate),
                "currency": currency,
                "lead_time": lead_time
            });

            let offer_id = document.getElementById('offer-detail-modal-offer-id').value;
            let returned = await servicePostUpdateOfferProduct(formData, offer_id, id);
            if (returned){
                await initOfferDetailModal(offer_id);
            }else{
                alert("Hata Oluştu");
            }

            // Submit the edited row data
            editor.submit();
        }
    });
    $( editor.field( 'pcs_price' ).input() ).on( 'keyup', function (e){
        let quantity = editor.field('quantity').val();
        let pcs_price = editor.field('pcs_price').val();
        if (pcs_price != ''){
            let total_price = quantity * parseFloat(changePriceToDecimal(pcs_price));
            document.getElementById('DTE_Field_total_price').value = changeCommasToDecimal(total_price.toFixed(2));
        }
    });
    $( editor.field( 'discount_rate' ).input() ).on( 'keyup', function (e){
        let total_price = editor.field('total_price').val();
        let pcs_price = editor.field('pcs_price').val();
        let discount_rate = editor.field('discount_rate').val();
        if (pcs_price != '' && total_price != '' && discount_rate != '' && discount_rate != '0,00'){
            let discounted_price = parseFloat(changePriceToDecimal(total_price)) - (parseFloat(changePriceToDecimal(total_price)) / 100 * parseFloat(changePriceToDecimal(discount_rate)));
            document.getElementById('DTE_Field_discounted_price').value = changeCommasToDecimal(discounted_price.toFixed(2));
        }
    });

    table = $('#offer-detail').DataTable( {
        dom: "Bfrtip",
        data: offer.products,
        columns: [
            { data: "id", editable: false },
            { data: "ref_code" },
            { data: "date_code" },
            { data: "package_type" },
            { data: "quantity" },
            { data: "measurement_name" },
            { data: "pcs_price" },
            { data: "total_price" },
            { data: "discount_rate" },
            { data: "discounted_price" },
            { data: "vat_rate" },
            { data: "currency" },
            { data: "lead_time" },
            {
                data: null,
                defaultContent: '<i class="fas fa-lg fa-fw me-2 fa-edit"/>',
                className: 'row-edit dt-center',
                orderable: false
            },
        ],
        select: {
            style: 'os',
            selector: 'td:first-child'
        },
    } );

}

async function openUpdateOfferProductModal(offer_id, product_id){
    $("#updateOfferProductModal").modal('show');
    await initUpdateOfferProductModal(offer_id, product_id);
}

async function initUpdateOfferProductModal(offer_id, product_id){
    let data = await serviceGetOfferProductById(offer_id, product_id);
    let product = data.product;

    document.getElementById('update_offer_id').value = offer_id;
    document.getElementById('update_offer_product_id').value = product_id;

    document.getElementById('update_offer_product_ref_code').value = product.product_detail.ref_code;
    document.getElementById('update_offer_product_product_name').value = product.product_detail.product_name;
    document.getElementById('update_offer_product_date_code').value = checkNull(product.date_code);
    document.getElementById('update_offer_product_package_type').value = checkNull(product.package_type);
    document.getElementById('update_offer_product_quantity').value = checkNull(product.quantity);
    document.getElementById('update_offer_product_pcs_price').value = checkNull(changeCommasToDecimal(product.pcs_price));
    document.getElementById('update_offer_product_total_price').value = checkNull(changeCommasToDecimal(product.total_price));
    document.getElementById('update_offer_product_discount_rate').value = checkNull(changeCommasToDecimal(product.discount_rate));
    document.getElementById('update_offer_product_discounted_price').value = checkNull(changeCommasToDecimal(product.discounted_price));
    document.getElementById('update_offer_product_vat_rate').value = checkNull(changeCommasToDecimal(product.vat_rate));
    document.getElementById('update_offer_product_currency').value = checkNull(product.currency);
    document.getElementById('update_offer_product_lead_time').value = checkNull(product.lead_time);
}

async function updateOfferProduct(){
    let offer_id = document.getElementById('update_offer_id').value;
    let product_id = document.getElementById('update_offer_product_id').value;
    let ref_code = document.getElementById('update_offer_product_ref_code').value;
    let product_name = document.getElementById('update_offer_product_product_name').value;
    let date_code = document.getElementById('update_offer_product_date_code').value;
    let package_type = document.getElementById('update_offer_product_package_type').value;
    let quantity = document.getElementById('update_offer_product_quantity').value;
    let pcs_price = document.getElementById('update_offer_product_pcs_price').value;
    let total_price = document.getElementById('update_offer_product_total_price').value;
    let discount_rate = document.getElementById('update_offer_product_discount_rate').value;
    let discounted_price = document.getElementById('update_offer_product_discounted_price').value;
    let vat_rate = document.getElementById('update_offer_product_vat_rate').value;
    let currency = document.getElementById('update_offer_product_currency').value;
    let lead_time = document.getElementById('update_offer_product_lead_time').value;

    let formData = JSON.stringify({
        "ref_code": ref_code,
        "product_name": product_name,
        "date_code": date_code,
        "package_type": package_type,
        "quantity": quantity,
        "pcs_price": changePriceToDecimal(pcs_price),
        "total_price": changePriceToDecimal(total_price),
        "discount_rate": changePriceToDecimal(discount_rate),
        "discounted_price": changePriceToDecimal(discounted_price),
        "vat_rate": changePriceToDecimal(vat_rate),
        "currency": currency,
        "lead_time": lead_time
    });

    console.log(formData);

    let returned = await servicePostUpdateOfferProduct(formData, offer_id, product_id);
    if (returned){
        $("#update_offer_product_form").trigger("reset");
        $('#updateOfferProductModal').modal('hide');
        await initOfferDetailModal(offer_id);
    }else{
        alert("Hata Oluştu");
    }
}

async function openAddOfferProductModal(){
    $("#addOfferProductModal").modal('show');
}

async function addOfferProduct(){
    let offer_id = document.getElementById('offer-detail-modal-offer-id').value;
    let ref_code = document.getElementById('add_offer_product_ref_code').value;
    let product_name = document.getElementById('add_offer_product_product_name').value;
    let date_code = document.getElementById('add_offer_product_date_code').value;
    let package_type = document.getElementById('add_offer_product_package_type').value;
    let quantity = document.getElementById('add_offer_product_quantity').value;
    let pcs_price = document.getElementById('add_offer_product_pcs_price').value;
    let total_price = document.getElementById('add_offer_product_total_price').value;
    let discount_rate = document.getElementById('add_offer_product_discount_rate').value;
    let discounted_price = document.getElementById('add_offer_product_discounted_price').value;
    let vat_rate = document.getElementById('add_offer_product_vat_rate').value;
    let currency = document.getElementById('add_offer_product_currency').value;
    let lead_time = document.getElementById('add_offer_product_lead_time').value;

    let formData = JSON.stringify({
        "ref_code": ref_code,
        "product_name": product_name,
        "date_code": date_code,
        "package_type": package_type,
        "quantity": quantity,
        "pcs_price": pcs_price,
        "total_price": total_price,
        "discount_rate": discount_rate,
        "discounted_price": discounted_price,
        "vat_rate": vat_rate,
        "currency": currency,
        "lead_time": lead_time
    });

    console.log(formData);

    let returned = await servicePostAddOfferProduct(formData, offer_id);
    if (returned){
        $("#add_offer_product_form").trigger("reset");
        $('#addOfferProductModal').modal('hide');
        await initOfferDetailModal(offer_id);
    }else{
        alert("Hata Oluştu");
    }
}
