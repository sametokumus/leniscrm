(function($) {
    "use strict";

	$(document).ready(function() {

		$('#update_status_form').submit(function (e){
			e.preventDefault();
            updateStatus();
		});
	});

	$(window).load( function() {

		checkLogin();
		checkRole();
        initSales();

	});

})(window.jQuery);

function checkRole(){
	return true;
}

async function initSales(){
	let data = await serviceGetSales();
	$("#sales-datatable").dataTable().fnDestroy();
	$('#sales-datatable tbody > tr').remove();

	console.log(data)
	$.each(data.sales, function (i, sale) {
        let updated_at = "-";
        if (sale.updated_at != null){
            updated_at = formatDateAndTimeDESC(sale.updated_at, "/");
        }
        let status_class = "";
        let btn_list = '';
        if (sale.status_id == 1){
            status_class = "border-danger text-danger";
            btn_list = '<div class="btn-list">\n' +
                '           <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="openStatusModal(\''+ sale.sale_id +'\', \''+ sale.status_id +'\')">\n' +
                '               <span class="fe fe-refresh-cw"> Durum Değiştir\n' +
                '           </button>\n' +
                '           <a href="offer-request/'+ sale.request_id +'" class="btn btn-sm btn-danger"><span class="fe fe-edit"> Talebi Güncelle</span></a>\n' +
                '           <a href="offer/'+ sale.request_id +'" class="btn btn-sm btn-danger"><span class="fe fe-edit"> Tedarikçi Teklifi Oluştur</span></a>\n' +
                '       </div>';
        }else if (sale.status_id == 2){
            status_class = "border-warning text-warning";
            btn_list = '<div class="btn-list">\n' +
                '           <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="openStatusModal(\''+ sale.sale_id +'\', \''+ sale.status_id +'\')">\n' +
                '               <span class="fe fe-refresh-cw"> Durum Değiştir\n' +
                '           </button>\n' +
                '           <a href="offer-request/'+ sale.request_id +'" class="btn btn-sm btn-warning"><span class="fe fe-edit"> Talebi Güncelle</span></a>\n' +
                '           <a href="offer/'+ sale.request_id +'" class="btn btn-sm btn-warning"><span class="fe fe-edit"> Tedarikçi Tekliflerini Güncelle</span></a>\n' +
                '       </div>';
        }else if (sale.status_id == 3){
            status_class = "border-primary text-primary";
            btn_list = '<div class="btn-list">\n' +
                '           <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="openStatusModal(\''+ sale.sale_id +'\', \''+ sale.status_id +'\')">\n' +
                '               <span class="fe fe-refresh-cw"> Durum Değiştir\n' +
                '           </button>\n' +
                '           <a href="sw-2/'+ sale.request_id +'" class="btn btn-sm btn-primary"><span class="fe fe-edit"> Teklif Oluştur</span></a>\n' +
                '       </div>';
        }else if (sale.status_id == 4){
            status_class = "border-yellow text-yellow";
            btn_list = '<div class="btn-list">\n' +
                '           <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="openStatusModal(\''+ sale.sale_id +'\', \''+ sale.status_id +'\')">\n' +
                '               <span class="fe fe-refresh-cw"> Durum Değiştir\n' +
                '           </button>\n' +
                '           <a href="sw-3/'+ sale.sale_id +'" class="btn btn-sm btn-yellow"><span class="fe fe-edit"> Fiyatları Güncelle</span></a>\n' +
                '       </div>';
        }else if (sale.status_id == 5){
            status_class = "border-success text-success";
            btn_list = '<div class="btn-list">\n' +
                '           <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="openStatusModal(\''+ sale.sale_id +'\', \''+ sale.status_id +'\')">\n' +
                '               <span class="fe fe-refresh-cw"> Durum Değiştir\n' +
                '           </button>\n' +
                '           <a href="sale-detail/'+ sale.sale_id +'" class="btn btn-sm btn-success"><span class="fe fe-edit"> Satış Detayı</span></a>\n' +
                '           <a href="quote-print/'+ sale.sale_id +'" class="btn btn-sm btn-success"><span class="fe fe-edit"> Quatotion PDF</span></a>\n' +
                '       </div>';
        }else{
            status_class = "border-theme text-theme";
            btn_list = '<div class="btn-list">\n' +
                '           <button id="bDel" type="button" class="btn btn-sm btn-theme" onclick="openStatusModal(\''+ sale.sale_id +'\', \''+ sale.status_id +'\')">\n' +
                '               <span class="fe fe-refresh-cw"> Durum Değiştir\n' +
                '           </button>\n' +
                '           <a href="sale-detail/'+ sale.sale_id +'" class="btn btn-sm btn-theme"><span class="fe fe-edit"> Satış Detayı</span></a>\n' +
                '           <a href="sw-3/'+ sale.sale_id +'" class="btn btn-sm btn-theme"><span class="fe fe-edit"> PDF</span></a>\n' +
                '       </div>';
        }

        let status = '<span class="badge border '+ status_class +' px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center"><i class="fa fa-circle fs-9px fa-fw me-5px"></i> '+ sale.status_name +'</span>';

        let saleItem = '<tr>\n' +
			'              <td>'+ sale.id +'</td>\n' +
			'              <td>'+ sale.sale_id +'</td>\n' +
			'              <td>'+ sale.request.authorized_personnel.name +' '+ sale.request.authorized_personnel.surname +'</td>\n' +
			'              <td>'+ sale.request.company.name +'</td>\n' +
			'              <td>'+ sale.request.company_employee.name +'</td>\n' +
			'              <td>'+ sale.request.product_count +'</td>\n' +
			'              <td>'+ status +'</td>\n' +
			'              <td>'+ formatDateAndTimeDESC(sale.created_at, "/") +'</td>\n' +
			'              <td>'+ updated_at +'</td>\n' +
			'              <td>\n' +
			'                  '+ btn_list +'\n' +
			'              </td>\n' +
			'          </tr>';
		$('#sales-datatable tbody').append(saleItem);
	});
	$('#sales-datatable').DataTable({
		responsive: true,
		columnDefs: [
			{ responsivePriority: 1, targets: 0 },
			{ responsivePriority: 2, targets: -1 },
			{ responsivePriority: 3, targets: 6 },
			{ responsivePriority: 4, targets: 7 },
			{ responsivePriority: 5, targets: 8 },
			{ responsivePriority: 6, targets: 1 }
		],
		dom: 'Bfrtip',
		buttons: [
            'excel',
            'pdf',
            {
                text: 'Talep Oluştur',
                action: function ( e, dt, node, config ) {
                    window.location = '/offer-request';
                }
            }
        ],
		pageLength : 20,
		language: {
			url: "services/Turkish.json"
		},
		order: [[0, 'desc']],
	});
}

function openStatusModal(sale_id, status_id){
    $('#updateStatusModal').modal('show');
    initStatusModal(sale_id, status_id);
}
async function updateStatus(){
    let status_id = document.getElementById('update_sale_status').value;
    let sale_id = document.getElementById('update_sale_id').value;
    let formData = JSON.stringify({
        "sale_id": sale_id,
        "status_id": status_id
    });
    let returned = await servicePostUpdateSaleStatus(formData);
    if(returned){
        $("#update_status_form").trigger("reset");
        $('#updateStatusModal').modal('hide');
        initSales();
    }
}
async function initStatusModal(sale_id, status_id){
    let data = await serviceGetStatuses();
    console.log(data)
    let statuses = data.statuses;
    $('#update_sale_status option').remove();
    $.each(statuses, function (i, status){
        let selected = '';
        if(status.id == status_id){selected = 'selected';}
        $('#update_sale_status').append('<option value="'+ status.id +'" '+ selected +'>'+ status.name +'</option>');
    });
    document.getElementById('update_sale_id').value = sale_id;
}
