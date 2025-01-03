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
        $('#add_product_count_form').submit(function (e){
            e.preventDefault();
            addProductCountToTable();
        });
        $('#update_status_form').submit(function (e){
            e.preventDefault();
            updateStatus();
        });
        $('#add_packing_address_form').submit(function (e){
            e.preventDefault();
            addPackingDeliveryAddress();
        });
        $('#update_packing_address_form').submit(function (e){
            e.preventDefault();
            updatePackingDeliveryAddress();
        });
	});

	$(window).load(async function() {

		checkLogin();
		checkRole();
        await initOfferDetail();
        await initPackingLists();

	});

})(window.jQuery);

function checkRole(){
	return true;
}

async function initOfferDetail(){

    let sale_id = getPathVariable('packing-list');
    let data = await serviceGetPackingableProductsBySaleId(sale_id);
    console.log(data)

        let offers = data.sale.sale_offers;
        console.log(offers)
        // $("#packing-list-detail").dataTable().fnDestroy();
        $("#packingable-list-detail").dataTable().fnDestroy();
        $('#packingable-list-detail tbody > tr').remove();

        $.each(offers, function (i, offer) {
            let btn = '';
            let kalan = 0;
            if (offer.offer_quantity != offer.packing_count){
                kalan = offer.offer_quantity - offer.packing_count;
                btn = '<button type="button" onclick="addPackingListTableProduct(this, ' + offer.id + ', '+ kalan +');" class="btn btn-sm btn-theme"><span class="fe fe-edit"> Listeye Ekle</span></button>';
            }
                let item = '<tr id="productRow' + offer.id + '">\n' +
                    '           <td>' + offer.sequence + '</td>\n' +
                    '           <td>' + offer.id + '</td>\n' +
                    '              <td>\n' +
                    '                  <div class="btn-list">\n' +
                    '                      '+ btn +'\n' +
                    // '                      <button type="button" onclick="openAddProductCountModal(' + offer.id + ');" class="btn btn-sm btn-theme"><span class="fe fe-edit"> Listeye Ekle</span></button>\n' +
                    '                  </div>\n' +
                    '              </td>\n' +
                    '           <td>' + checkNull(offer.product_name) + '</td>\n' +
                    '           <td>' + checkNull(offer.product_ref_code) + '</td>\n' +
                    '           <td>' + checkNull(offer.offer_lead_time) + '</td>\n' +
                    '           <td>' + checkNull(offer.offer_quantity) + '</td>\n' +
                    '           <td>' + offer.packing_count + '</td>\n' +
                    '           <td>' + kalan + '</td>\n' +
                    '           <td class="d-none"></td>\n' +
                    '       </tr>';
                $('#packingable-list-detail tbody').append(item);
        });

        $('#packingable-list-detail').DataTable({
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
        $('#packing-list-detail').DataTable({
            responsive: false,
            columnDefs: [
                {responsivePriority: 1, targets: 0},
                {responsivePriority: 2, targets: -1}
            ],
            dom: 'Bfrtip',
            buttons: [
                {
                    text: 'Gönderi Listesini Kaydet',
                    action: function (e, dt, node, config) {
                        addPackingList();
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

async function addProductCountToTable(){
    let id = document.getElementById('add_product_id').value;
    let count = document.getElementById('add_product_count').value;
    console.log(id, count)
    $('#productRow'+id+' td:last-child').text(count);
    $("#addProductCountModal").modal('hide');
    $("#add_product_count_form").trigger('reset');
}

async function addPackingListTableProduct(el, id, kalan){
    let tablePackingList = $("#packing-list-detail").DataTable();
    let tablePackingableList = $("#packingable-list-detail").DataTable();
    $(el).attr('onclick','removePackingListTableProduct(this,'+ id +');');
    $(el).text('Listeden Çıkar');
    let row = tablePackingableList.row( $(el).parents('tr') );
    let rowNode = row.node();
    row.remove();

    tablePackingList
        .row.add( rowNode )
        .draw();


    $("#addProductCountModal").modal('show');
    $('.modal-backdrop').css('background-color', 'transparent');
    document.getElementById('add_product_id').value = id;
    document.getElementById('add_product_count').value = kalan;
}

async function removePackingListTableProduct(el, id){
    let tablePackingList = $("#packing-list-detail").DataTable();
    let tablePackingableList = $("#packingable-list-detail").DataTable();
    $(el).attr('onclick','addPackingListTableProduct(this, '+ id +');');
    $(el).text('Listeye Ekle');
    let row = tablePackingList.row( $(el).parents('tr') );
    let rowNode = row.node();
    row.remove();

    tablePackingableList
        .row.add( rowNode )
        .draw();
}

async function addPackingList(){
    let user_id = localStorage.getItem('userId');
    let sale_id = getPathVariable('packing-list');
    let table = $('#packing-list-detail').DataTable();
    let rows = table.rows();

    let packing_list = [];
    if (rows.count() === 0){
        alert("Öncelikle seçim yapmalısınız.");
    }else {
        rows.every(function (rowIdx, tableLoop, rowLoop) {
            let item = {
                "sale_offer_id": this.data()[1],
                "quantity": $('#productRow'+this.data()[1] + ' td:last-child').text(),
            }
            packing_list.push(item);
        });
        console.log(packing_list)

        let formData = JSON.stringify({
            "user_id": parseInt(user_id),
            "sale_id": sale_id,
            "packing_list": packing_list
        });

        console.log(formData);

        // let data = await servicePostAddSale(formData);
        let data = await servicePostAddPackingList(formData);
        if (data) {
            window.location.reload();
        }else{
            alert("Hata Oluştu");
        }

    }
}

async function initPackingLists(){

    let sale_id = getPathVariable('packing-list');
    let data = await serviceGetPackingListsBySaleId(sale_id);
    console.log(data)

    let packing_lists = data.packing_lists;
    console.log(packing_lists)
    $("#packing-lists").dataTable().fnDestroy();
    $('#packing-lists tbody > tr').remove();

    $.each(packing_lists, function (i, packing_list) {
        let status_class = "border-theme text-theme";
        let status = '<span class="badge border '+ status_class +' px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center" onclick="openStatusModal(\''+ packing_list.packing_list_id +'\', \''+ packing_list.packing_status_id +'\')"><i class="fa fa-circle fs-9px fa-fw me-5px"></i> '+ packing_list.status.name +'</span>';

        let address_status = '';
        let address_btn = '';
        if (packing_list.address_id == null){
            address_status = '<button onclick="openAddPackingDeliveryAddressModal(\''+ packing_list.packing_list_id +'\', '+ packing_list.company_id +')" class="btn btn-sm btn-theme">Sevkiyat Adresi Seç</button>';
            address_btn = '';
        }else{
            address_status = packing_list.address;
            address_btn = '<button onclick="openUpdatePackingDeliveryAddressModal(\''+ packing_list.packing_list_id +'\', '+ packing_list.company_id +')" class="btn btn-sm btn-theme">Sevkiyat Adresi Değiştir</button>';
        }

        let item = '<tr>\n' +
            '           <td>' + packing_list.packing_list_id + '</td>\n' +
            '           <td>' + packing_list.count + '</td>\n' +
            '           <td>' + status + '</td>\n' +
            '           <td>' + address_status + '</td>\n' +
            '              <td>\n' +
            '                  <div class="btn-list">\n' +
            '                      <a href="packing-list-print/'+ packing_list.packing_list_id +'" class="btn btn-sm btn-indigo">Packing List PDF</a>\n' +
            '                      '+ address_btn +'\n' +
            '                      <button onclick="deletePackingList(\''+ packing_list.packing_list_id +'\')" class="btn btn-sm btn-danger">Sil</button>\n' +
            '                  </div>\n' +
            '              </td>\n' +
            '       </tr>';
        $('#packing-lists tbody').append(item);
    });

    $('#packing-lists').DataTable({
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
        }
    });

}


async function deletePackingList(packing_list_id){
    let returned = await serviceGetDeletePackingList(packing_list_id);
    if(returned){
        await initPackingLists();
        await initOfferDetail();
    }
}


function openStatusModal(packing_list_id, packing_status_id){
    $('#updateStatusModal').modal('show');
    initStatusModal(packing_list_id, packing_status_id);
}
async function updateStatus(){
    let status_id = document.getElementById('update_packing_status').value;
    let packing_list_id = document.getElementById('update_packing_list_id').value;
    let formData = JSON.stringify({
        "packing_list_id": packing_list_id,
        "status_id": status_id
    });
    let data = await servicePostUpdatePackingListStatus(formData);
    if(data.status == "success"){
        $("#update_status_form").trigger("reset");
        $('#updateStatusModal').modal('hide');
        initPackingLists();
    }
}
async function initStatusModal(packing_list_id, packing_status_id){
    let data = await serviceGetPackingStatuses();
    let statuses = data.statuses;
    $('#update_packing_status option').remove();
    $.each(statuses, function (i, status){
        let selected = '';
        if(status.id == packing_status_id){selected = 'selected';}
        $('#update_packing_status').append('<option value="'+ status.id +'" '+ selected +'>'+ status.name +'</option>');
    });
    document.getElementById('update_packing_list_id').value = packing_list_id;
}


function openAddPackingDeliveryAddressModal(packing_list_id, customer_id){
    $('#addPackingAddressModal').modal('show');
    document.getElementById('add_packing_address_packing_list_id').value = packing_list_id;
    getAddressesAddSelectId('add_packing_address_addresses', customer_id);
}
async function addPackingDeliveryAddress(){
    let address_id = document.getElementById('add_packing_address_addresses').value;
    let packing_list_id = document.getElementById('add_packing_address_packing_list_id').value;
    let formData = JSON.stringify({
        "packing_list_id": packing_list_id,
        "address_id": address_id
    });
    let data = await servicePostAddPackingDeliveryAddress(formData);
    if(data.status == "success"){
        $("#add_packing_address_form").trigger("reset");
        $('#addPackingAddressModal').modal('hide');
        initPackingLists();
    }
}

function openUpdatePackingDeliveryAddressModal(packing_list_id, customer_id){
    $('#addPackingAddressModal').modal('show');
    document.getElementById('update_packing_address_packing_list_id').value = packing_list_id;
    getAddressesAddSelectId('update_packing_address_addresses', customer_id);
}
async function updatePackingDeliveryAddress(){
    let address_id = document.getElementById('update_packing_address_addresses').value;
    let packing_list_id = document.getElementById('update_packing_address_packing_list_id').value;
    let formData = JSON.stringify({
        "packing_list_id": packing_list_id,
        "address_id": address_id
    });
    let data = await servicePostUpdatePackingDeliveryAddress(formData);
    if(data.status == "success"){
        $("#update_packing_address_form").trigger("reset");
        $('#updatePackingAddressModal').modal('hide');
        initPackingLists();
    }
}
