(function($) {
    "use strict";

	$(document).ready(function() {
        $(":input").inputmask();
        $("#add_target_target").maskMoney({thousands:'.', decimal:','});

        $('#add_activity_form').submit(function (e){
            e.preventDefault();
            addStaffTarget();
        });
        $('#update_activity_form').submit(function (e){
            e.preventDefault();
            updateStaffTarget();
        });

	});

	$(window).load(async function() {

		checkLogin();
		checkRole();

        getAdminsAddSelectId('add_target_admin_id');
        getStaffTargetTypesAddSelectId('add_target_type_id');
        initStaffTargets();

	});

})(window.jQuery);

function checkRole(){
	return true;
}

async function initStaffTargets(){

    let data = await serviceGetStaffTargets();

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
            '           <td>'+ target.target +' '+ target.currency +'</td>\n' +
            '           <td>'+ target.month +'</td>\n' +
            '           <td>'+ target.year +'</td>\n' +
            '           <td></td>\n' +
            '           <td>'+ actions +'</td>\n' +
            '       </tr>';
        $('#targets-datatable tbody').append(item);
    });

	$('#targets-datatable').DataTable({
		responsive: false,
		columnDefs: [
            {
                targets: 3,
                className: 'ellipsis',
                render: function(data, type, row, meta) {
                    return type === 'display' && data.length > 30 ?
                        data.substr(0, 30) + '...' :
                        data;
                }
            },
            {
                type: 'date',
                targets: 6,
                render: function(data, type, row) {
                    return moment(data, 'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm');
                }
            },
            {
                type: 'date',
                targets: 7,
                render: function(data, type, row) {
                    return moment(data, 'DD/MM/YYYY HH:mm').format('YYYY-MM-DD HH:mm');
                }
            }
		],
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

async function addStaffTarget(){
    let company_id = document.getElementById('add_activity_company_id').value;
    let user_id = localStorage.getItem('userId');

    let task_count = document.getElementById('add-activity-new-task-count').value;
    let tasks = [];
    for (let i = 1; i <= task_count; i++) {
        let title = document.getElementById('add_activity_new_task_'+i+'_label').textContent;
        let is_completed = 0;
        let task_id = document.getElementById('add_activity_new_task_'+i).getAttribute('data-task-id');
        let item = {
            "task_id": parseInt(task_id),
            "title": title,
            "is_completed": parseInt(is_completed)
        }
        tasks.push(item);
    }

    let start = formatDateDESC2(document.getElementById('add_activity_start_date').value, "-", "-") + " " + document.getElementById('add_activity_start_time').value + ":00";
    let end = formatDateDESC2(document.getElementById('add_activity_end_date').value, "-", "-")  + " " + document.getElementById('add_activity_end_time').value + ":00";

    let formData = JSON.stringify({
        "user_id": parseInt(user_id),
        "type_id": document.getElementById('add_activity_type_id').value,
        "title": document.getElementById('add_activity_title').value,
        "description": document.getElementById('add_activity_description').value,
        "company_id": company_id,
        "employee_id": document.getElementById('add_activity_employee_id').value,
        "start": start,
        "end": end,
        "tasks": tasks
    });

    console.log(formData);

    let returned = await servicePostAddActivity(formData);
    if (returned){
        $("#add_activity_form").trigger("reset");
        $("#addCompanyActivityModal").modal('hide');
        initActivities();
    }else{
        alert("Hata Oluştu");
    }
}


async function openUpdateStaffTargetModal(target_id){
    // getAdminsAddSelectId('update_target_admin_id');
    getStaffTargetTypesAddSelectId('update_target_type_id');
    $("#updateStaffTargetModal").modal('show');
    initUpdateStaffTargetModal(target_id)
}
async function initUpdateStaffTargetModal(target_id){
    document.getElementById('update_activity_form').reset();
    $('#update-activity-tasks-body .form-check').remove();
    document.getElementById('update_activity_id').value = activity_id;
    let data = await serviceGetActivityById(activity_id);
    let activity = data.activity;
    document.getElementById('update_activity_type_id').value = activity.type_id;
    document.getElementById('update_activity_title').value = activity.title;
    document.getElementById('update_activity_description').value = activity.description;
    document.getElementById('update_activity_start_date').value = formatDateASC(activity.start, "-");
    document.getElementById('update_activity_start_time').value = formatTime(activity.start);
    document.getElementById('update_activity_end_date').value = formatDateASC(activity.end, "-");
    document.getElementById('update_activity_end_time').value = formatTime(activity.end);
    document.getElementById('update_activity_company_id').value = activity.company_id;
    await getEmployeesAddSelectId(activity.company_id, 'update_activity_employee_id');
    document.getElementById('update_activity_employee_id').value = activity.employee_id;

    let count = 0;
    $.each(activity.tasks, function (i, task) {
        count += 1;
        let is_completed = '';
        let task_status = 1;
        if (task.is_completed == 1){
            is_completed = 'checked';
            task_status = 0;
        }
        let checkInput = '<div class="form-check" id="form-check-'+ task.id +'">\n' +
            '                 <input class="form-check-input" type="checkbox" value="1" data-task-id="'+ task.id +'" id="update_activity_task_'+ count +'" '+ is_completed +' onclick="changeTaskStatus(this, '+ task.id +' ,'+ task_status +')" />\n' +
            '                 <label class="form-check-label" for="add_activity_task_'+ count +'" id="add_activity_task_'+ count +'_label">'+ task.title +'</label>\n' +
            '                 (<a href="#" onclick="deleteActivityTask(event, '+ task.id +');">Görevi Sil</a>)\n' +
            '             </div>';
        $('#update-activity-tasks-body').append(checkInput);
    });
    document.getElementById('update-activity-task-count').value = count;
}
async function updateStaffTarget(){
    let company_id = document.getElementById('update_activity_company_id').value;
    let user_id = localStorage.getItem('userId');
    let activity_id = document.getElementById('update_activity_id').value;

    let start = formatDateDESC2(document.getElementById('update_activity_start_date').value, "-", "-") + " " + document.getElementById('update_activity_start_time').value + ":00";
    let end = formatDateDESC2(document.getElementById('update_activity_end_date').value, "-", "-")  + " " + document.getElementById('update_activity_end_time').value + ":00";
    let formData = JSON.stringify({
        "user_id": parseInt(user_id),
        "type_id": document.getElementById('update_activity_type_id').value,
        "title": document.getElementById('update_activity_title').value,
        "description": document.getElementById('update_activity_description').value,
        "company_id": company_id,
        "employee_id": document.getElementById('update_activity_employee_id').value,
        "start": start,
        "end": end
    });
    let returned = await servicePostUpdateActivity(formData, activity_id);
    if (returned){
        $("#update_activity_form").trigger("reset");
        $("#updateCompanyActivityModal").modal('hide');
        initActivities();
    }else{
        alert('Güncelleme yapılırken bir hata oluştu. Lütfen tekrar deneyiniz!');
    }
}
async function deleteStaffTarget(target_id){
    let returned = await serviceGetDeleteStaffTarget(target_id);
    if(returned){
        initStaffTargets();
    }
}
async function deleteActivityTask(event, task_id){
    event.preventDefault();
    console.log(task_id)
    let returned = await serviceGetDeleteActivityTask(task_id);
    if(returned){
        $('#form-check-'+task_id).remove();
    }
}
async function changeTaskStatus(element, task_id, task_status){
    $(element).attr('disabled', 'disabled');
    let returned;
    let new_status;
    if (task_status == 0){
        returned = await serviceGetUnCompleteActivityTask(task_id);
        new_status = 1;
    }else{
        returned = await serviceGetCompleteActivityTask(task_id);
        new_status = 0;
    }
    if(returned){
        $(element).attr('onclick', "changeTaskStatus(this, '"+ task_id + "' ,'"+ new_status +"')");
        $(element).removeAttr('disabled');
    }
}
async function updateActivityNewTask(){
    let task = document.getElementById('update-activity-task').value;
    if (task == ''){
        alert('Lütfen görev için içerik ekleyiniz.')
    }else{

        let formData = JSON.stringify({
            "activity_id": parseInt(document.getElementById('update_activity_id').value),
            "title": task
        });

        let returned = await servicePostAddActivityTask(formData);
        if (returned == false) {
            alert('Görev eklenirken bir hata oluştu. Lütfen tekrar deneyiniz!');
        }else{
            let count = document.getElementById('update-activity-task-count').value;
            count = parseInt(count) + 1;
            document.getElementById('update-activity-task-count').value = count;
            let checkInput = '<div class="form-check" id="form-check-'+ returned.task_id +'">\n' +
                '                 <input class="form-check-input" type="checkbox" value="1" data-task-id="'+ returned.task_id +'" id="update_activity_task_'+ count +'" onclick="changeTaskStatus(this, '+ returned.task_id +', 1)" />\n' +
                '                 <label class="form-check-label" for="add_activity_task_'+ count +'" id="add_activity_task_'+ count +'_label">'+ task +'</label>\n' +
                '                 (<a href="#" onclick="deleteActivityTask(event, '+ returned.task_id +');">Görevi Sil</a>)\n' +
                '             </div>';
            $('#update-activity-tasks-body').append(checkInput);
            document.getElementById('update-activity-task').value = '';
            $('#update-activity-new-tasks-input').addClass('d-none');
        }
    }
}
