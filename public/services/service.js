(function($) {
	"use strict";

	$(document).ready(function() {

		createSession();
		if(localStorage.getItem('userLogin') == null || localStorage.getItem('userLogin') == 'false'){
            // window.location.href = "/login";
		}

		$('.navbar-logout').click(function(event){
			event.preventDefault();
			logout();

		});

    });


	$(window).on('load',async function() {
		// checkLogin();
        $('#header_user_name').text(localStorage.getItem('userName'));
        if (localStorage.getItem('userPhoto') != 'null') {
            document.getElementById('header_user_image').src = localStorage.getItem('userPhoto');
        }

        createNavbar();
	});


    let lng = $('meta[name="current-locale"]').attr('content');
    Lang.setLocale(lng);

})(window.jQuery);

const api_url = 'https://crm.semytechnology.com';

$(function() {
    // Listen for the language dropdown change event
    $('select#lang').change(function() {
        var lang = $(this).val();
        // Make an Ajax request to change the language
        Lang.setLocale(lang);

        $.ajax({
            url: '/lang',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { lang: lang },
            success: function(data) {
                // Reload the page to apply the new locale
                location.reload();
            }
        });
    });
});


async function fetchDataGet(apiURL, contentType){

	var returnData;
	var token = localStorage.getItem('appToken');
	var data = await fetch(api_url + '/api'+apiURL, {
		method: 'get',
		headers:{
			'Content-Type': contentType,
			'Authorization': 'Bearer '+token,
		}
	});

	var returnData = await data.json();
	return returnData;

}

async function fetchDataPost (apiURL, body, contentType) {

	var returnData;
	var token = localStorage.getItem('appToken');
	var data = await fetch(api_url + '/api'+apiURL, {
		method: 'post',
		headers:{
			'Content-Type': contentType,
			'Authorization': 'Bearer '+token,
		},
		body:body
	});

	var returnData = await data.json();
	return returnData;

}

function xhrDataPost (apiURL, body, callBackFunction) {

	let xhr = new XMLHttpRequest();
	var token = localStorage.getItem('appToken');
	var returnData;
	xhr.timeout = 5000;
	xhr.addEventListener("readystatechange", function() {
        console.log(this)
		if(this.readyState === 4) {
			callBackFunction(this);
		}
	});
	xhr.open("POST", api_url + '/api'+apiURL);
	xhr.setRequestHeader("Authorization", 'Bearer '+token);
	xhr.send(body);

}



/* CUSTOM FUNCTIONS */
function logout(){
    fetchDataGet('/v1/logout', 'application/json').then(data=>{

        if(data.status == "success"){
            removeSession();
            window.location.href = "/";
        }else{
            showAlert("İstek Başarısız.");
        }

    });
}

function triggerPipeman(){
    var imageContainer = document.getElementById("pipeman-image");
    imageContainer.src = "/gifs/pipeman1.gif";
    $('.pipeman-container').css('width', '300px').css('height', '300px');
    // $('.pipeman-image').css('display', 'block');
    imageContainer.style.transform = "scale(1)";
    window.setTimeout(function() {
        imageContainer.style.transform = "scale(0)";
    }, 5000);
}

function triggerPipeman2(){
    var imageMain = document.getElementById("pipeman-container");
    var imageContainer = document.getElementById("pipeman-image");
    imageMain.style.marginBottom = "0px";
    imageMain.style.marginRight = "0px";
    imageContainer.src = "/gifs/YerdekiKapak.gif";
    $('.pipeman-container').css('width', '300px').css('height', '300px');
    // $('.pipeman-image').css('display', 'block');
    window.setTimeout(function() {
        imageContainer.style.transition = "none";
        imageContainer.style.transform = "scale(.7)";
        imageMain.style.marginBottom = "40px";
        imageMain.style.marginRight = "30px";
        imageContainer.src = "/gifs/ScracthHead.gif";
    }, 1400);
    imageContainer.style.transform = "scale(1)";
    window.setTimeout(function() {
        imageContainer.style.transform = "scale(0)";
    }, 60000);
}

function triggerPipeman3(){
    var imageMain = document.getElementById("pipeman-container");
    var imageContainer = document.getElementById("pipeman-image");
    imageMain.style.marginBottom = "0px";
    imageMain.style.marginRight = "0px";
    imageContainer.src = "/gifs/YerdekiKapakA.gif";
    $('.pipeman-container').css('width', '300px').css('height', '300px');
    // $('.pipeman-image').css('display', 'block');
    window.setTimeout(function() {
        imageContainer.style.transition = "none";
        // imageContainer.style.transform = "scale(.7)";
        // imageMain.style.marginBottom = "40px";
        // imageMain.style.marginRight = "30px";
        imageContainer.src = "/gifs/KafaKasimaA.gif";
    }, 1400);
    imageContainer.style.transform = "scale(1)";
    window.setTimeout(function() {
        imageContainer.style.transform = "scale(0)";
    }, 60000);
}

function showAlert(message){
	$(".alert-container .alert").text(message);
	var alert = $(".alert-container");
	alert.show("slide", {direction: "right"}, 200);
	window.setTimeout(function() {
		alert.hide("slide", {direction: "right"}, 200);
	}, 3000);
}

async function createSession () {
	var userLogin = await localStorage.getItem('userLogin');
	if(userLogin == null){
		localStorage.setItem('userLogin','false');
	}
}

function removeSession () {
	localStorage.setItem('userLogin','false');
	localStorage.removeItem('userRole');
	localStorage.removeItem('userId');
	localStorage.removeItem('userEmail');
	localStorage.removeItem('appToken');
	localStorage.removeItem('userPhoto');
	localStorage.removeItem('chatCount');
	localStorage.removeItem('userName');
	localStorage.removeItem('dash_currency');
	localStorage.removeItem('dash_owner');
}

function getSession () {
    console.log(localStorage.getItem('userLogin'));
    console.log(localStorage.getItem('userRole'));
    console.log(localStorage.getItem('userId'));
    console.log(localStorage.getItem('userEmail'));
    console.log(localStorage.getItem('appToken'));
}

function getQueryString(param){
	var value;
	var queryString = window.location.search;
	var urlParams = new URLSearchParams(queryString);
	value = urlParams.get(param);

	return value;
}

function getURLParam(name){
	const params = new URLSearchParams(window.location.search);
	return params.get(name);
}

function getPathVariable(variable) {
    let path = location.pathname;
    let parts = path.substr(1).split('/'), value;

    while(parts.length) {
        if (parts.shift() === variable) value = parts.shift();
        else parts.shift();
    }

    return value;
}

function formatDateASC(date, slicer) {
	date = new Date(date);

	var day = ('0' + date.getDate()).slice(-2);
	var month = ('0' + (date.getMonth() + 1)).slice(-2);
	var year = date.getFullYear();

	return day + slicer + month + slicer + year;
}

function formatDateZeroASC(date, slicer, splitter) {
	var dates = date.split(splitter);
	date = dates[2]+'-'+dates[1]+'-'+dates[0];
	date = new Date(date);

	var day = ('0' + date.getDate()).slice(-2);
	var month = ('0' + (date.getMonth() + 1)).slice(-2);
	var year = date.getFullYear();

	return day + slicer + month + slicer + year;
}

function formatDateSplit(date, slicer, splitter) {
	let dates = date.split(splitter);
	return dates[0] + slicer + dates[1] + slicer + dates[2];
}

function formatDateDESC(date, slicer) {
	date = new Date(date);

	var day = ('0' + date.getDate()).slice(-2);
	var month = ('0' + (date.getMonth() + 1)).slice(-2);
	var year = date.getFullYear();

	return year + slicer + month + slicer + day;
}
function formatDateDESC2(date, slicer, splitter) {
	var dates = date.split(splitter);
	date = dates[2]+'-'+dates[1]+'-'+dates[0];
	date = new Date(date);

	var day = ('0' + date.getDate()).slice(-2);
	var month = ('0' + (date.getMonth() + 1)).slice(-2);
	var year = date.getFullYear();

	return year + slicer + month + slicer + day;
}

function formatTime(time) {
	time = new Date(time);

	var hour = ('0' + time.getHours()).slice(-2);
	var minute = ('0' + time.getMinutes()).slice(-2);

	return hour + ':' + minute;
}

function formatDateAndTime(date, slicer) {
    console.log(date)
	date = new Date(date);
    console.log(date)

	var day = ('0' + date.getDate()).slice(-2);
	var month = ('0' + (date.getMonth() + 1)).slice(-2);
	var year = date.getFullYear();
	var hour = date.getHours();
	var minute = ('0' + date.getMinutes()).slice(-2);

	return year + slicer + month + slicer + day + ' ' + hour + ':' + minute;
}

function formatDateAndTime2(dateString, slicer) {
    let dateArray = dateString.split(' ');

    let dates = dateArray[0];
    let time = dateArray[1];

    let fullDate = dates.split('-');
    let day = fullDate[0];
    let month = fullDate[1];
    let year = fullDate[2];

    let hourMinute = time.split(':');
    let hour = hourMinute[0];
    let minute = hourMinute[1];

	return year + slicer + month + slicer + day + ' ' + hour + ':' + minute;
}

function formatDateAndTimeDESC(date, slicer) {
	date = new Date(date);
	var day = ('0' + date.getDate()).slice(-2);
	var month = ('0' + (date.getMonth() + 1)).slice(-2);
	var year = date.getFullYear();
	var hour = date.getHours();
	var minute = ('0' + date.getMinutes()).slice(-2);

	return day + slicer + month + slicer + year + ' ' + hour + ':' + minute;
}

function formatDateAndTimeDESC2(date, slicer) {

    const parts = date.split(/[\-T:Z]/);
    const day = parts[2];
    const month = parts[1];
    const year = parts[0];
    const hour = parts[3];
    const minute = parts[4];

    return day + slicer + month + slicer + year + ' ' + hour + ':' + minute;
}

function checkNull(data){
	if (data == null || data == 'null'){
        return "";
    }else {
        return data;
    }
}

function cryptText(text){
	var cText = text.charAt(0);
	for(let i=2; i<=text.length; i++){
		cText = cText+'*';
	}
	return cText;
}

function xmlToJson(xml) {

	// Create the return object
	var obj = {};

	if (xml.nodeType == 1) { // element
		// do attributes
		if (xml.attributes.length > 0) {
			obj["@attributes"] = {};
			for (var j = 0; j < xml.attributes.length; j++) {
				var attribute = xml.attributes.item(j);
				obj["@attributes"][attribute.nodeName] = attribute.nodeValue;
			}
		}
	} else if (xml.nodeType == 3) { // text
		obj = xml.nodeValue;
	}

	// do children
	if (xml.hasChildNodes()) {
		for(var i = 0; i < xml.childNodes.length; i++) {
			var item = xml.childNodes.item(i);
			var nodeName = item.nodeName;
			if (typeof(obj[nodeName]) == "undefined") {
				obj[nodeName] = xmlToJson(item);
			} else {
				if (typeof(obj[nodeName].push) == "undefined") {
					var old = obj[nodeName];
					obj[nodeName] = [];
					obj[nodeName].push(old);
				}
				obj[nodeName].push(xmlToJson(item));
			}
		}
	}
	return obj;
};

function changePriceToDecimal(str) {
    if (typeof str === 'string') {
        str = str.replace(/\./g, '');
        str = str.replace(',', '.');
    }
    return str;
}
function changeDecimalToPrice(num) {
    // Convert the number to a string with a fixed number of decimal places
    const str = num.toFixed(2);

    // Replace the decimal separator with a comma
    const strWithComma = str.replace('.', ',');

    // Replace the thousands separator with a dot
    const parts = strWithComma.split(',');
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const decimalPart = parts[1];

    // Combine the integer and decimal parts with a comma separator
    return integerPart + ',' + decimalPart;
}
function changeCommasToDecimal(price) {
    if (price == null){
       return "";
    }else {
        var parts = price.toString().split(".");
        const numberPart = parts[0];
        const decimalPart = parts[1];
        const thousands = /\B(?=(\d{3})+(?!\d))/g;
        return numberPart.replace(thousands, ".") + (decimalPart ? "," + decimalPart : "");
    }
}
function changeDecimalToCommas(price) {
	var parts = price.toString().split(",");
	const numberPart = parts[0];
	const decimalPart = parts[1];
	const thousands = /\B(?=(\d{3})+(?!\d))/g;
	return numberPart.replace(thousands, ",") + (decimalPart ? "." + decimalPart : "");
}

// Select2
$('.select2').select2({
    minimumResultsForSearch: Infinity,
    width: '100%'
});

// Select2 by showing the search
$('.select2-show-search').select2({
    minimumResultsForSearch: '',
    width: '100%'
});

$('.select2').on('click', () => {
    let selectField = document.querySelectorAll('.select2-search__field')
    selectField.forEach((element, index) => {
        element.focus();
    })
});

/* SERVICE INIT DATA FUNCTIONS */

async function createNavbar(){
   let user_role = await localStorage.getItem('userRole');
    let data = await serviceGetAdminRolePermissions(user_role);
    $.each(data.role_permissions, function(i, role_permission){
        $('#nav-'+role_permission.permission_key).removeClass('d-none');
    });
    $('.menu-item.d-none').remove();

}



async function checkLogin () {
    // localStorage.setItem('userLogin', 'true');
	var userLogin = await localStorage.getItem('userLogin');
	if(userLogin == null || userLogin == 'false'){
		window.location.href = "login";
	}else{
		//verify bcrypt
		let userRole = localStorage.getItem('userRole');
		let userId = localStorage.getItem('userId');
		let userEmail = localStorage.getItem('userEmail');
		let userLogin = localStorage.getItem('userLogin');

        //user
        let data = await serviceGetAdminById(userId);
        let admin = data.admin;

        let return_role = false;
        if (admin.email == userEmail && admin.admin_role_id == userRole){
            return_role = true;
        }else{
            logout();
        }

        let hash = userRole+userId+userEmail;

        if (return_role) {
            try {
                checkpw(
                    hash,
                    userLogin,
                    function (res) {
                        if (res == false) {
                            window.location.href = "login";
                        }
                    },
                    function () {
                    });
            } catch (err) {
                //alert(err);
                return;
            }
        }
	}
}

async function getAdminsAddSelectId(selectId){
    let data = await serviceGetAdmins();
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Yetkili Seçiniz</option>');
    $.each(data.admins, function(i, admin){
        let optionRow = '<option value="'+admin.id+'">'+admin.name+' '+admin.surname+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getAdminRolesAddSelectId(select_id){
    $('#'+select_id +' option').remove();
    let data = await serviceGetAdminRoles();
    $('#'+select_id).append('<option value="0">Rol Seçiniz</option>');
    $.each(data.admin_roles, function (i, role) {
        let roleItem = '<option value="'+ role.id +'">'+ role.name +'</option>';
        $('#'+select_id).append(roleItem);
    });
}

async function getStatusesAddSelectId(selectId){
    let data = await serviceGetStatuses();
    let statuses = data.statuses;
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Durum Seçiniz</option>');
    $.each(statuses, function (i, status){
        let optionRow = '<option value="'+status.id+'">'+status.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getSaleTypesAddSelectId(selectId){
    let data = await serviceGetSaleTypes();
    $('#'+selectId+' option').remove();
    $.each(data.types, function(i, type){
        let optionRow = '<option value="'+type.id+'">'+type.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getStaffTargetTypesAddSelectId(selectId){
    let data = await serviceGetStaffTargetTypes();
    $('#'+selectId+' option').remove();
    $.each(data.target_types, function(i, target_type){
        let optionRow = '<option value="'+target_type.id+'">'+target_type.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getOwnersAddSelectId(selectId){
    let data = await serviceGetContacts();
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Firma Seçiniz</option>');
    $.each(data.contacts, function(i, contact){
        let optionRow = '<option value="'+contact.id+'">'+contact.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getDashboardOwnersAddSelectId(selectId){
    let data = await serviceGetContacts();
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Tüm Firmalar</option>');
    $.each(data.contacts, function(i, contact){
        let optionRow = '<option value="'+contact.id+'">'+contact.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getCompaniesAddSelectId(selectId){
    let data = await serviceGetCompanies();
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Müşteri Seçiniz</option>');
    $.each(data.companies, function(i, company){
        let optionRow = '<option value="'+company.id+'">'+company.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getAddressesAddSelectId(selectId, company_id){
    let data = await serviceGetAddressesByCompanyId(company_id);
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Adres Seçiniz</option>');
    $.each(data.addresses, function(i, address){
        let optionRow = '<option value="'+address.id+'">'+address.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getSuppliersAddSelectId(selectId){
    let data = await serviceGetSuppliers();
    $('#'+selectId+' option').remove();
    // $('#'+selectId).append('<option value="0">Firma Seçiniz</option>');
    $.each(data.companies, function(i, company){
        let optionRow = '<option value="'+company.id+'">'+company.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getCustomersAddSelectId(selectId){
    let data = await serviceGetCustomers();
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Firma Seçiniz</option>');
    $.each(data.companies, function(i, company){
        let optionRow = '<option value="'+company.id+'">'+company.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getCustomersAndPotentialsAddSelectId(selectId){
    let data = await serviceGetCustomers();
    let data2 = await serviceGetPotentialCustomers();
    $('#'+selectId+' option').remove();
    // $('#'+selectId).append('<option value="0">Firma Seçiniz</option>');
    $.each(data.companies, function(i, company){
        let optionRow = '<option value="'+company.id+'">'+company.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
    $.each(data2.companies, function(i, company){
        let optionRow = '<option value="'+company.id+'">'+company.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getCustomersAndPotentialsAddSelectIdWithZero(selectId){
    let data = await serviceGetCustomers();
    let data2 = await serviceGetPotentialCustomers();
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Müşteri Seçiniz</option>');
    $.each(data.companies, function(i, company){
        let optionRow = '<option value="'+company.id+'">'+company.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
    $.each(data2.companies, function(i, company){
        let optionRow = '<option value="'+company.id+'">'+company.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getEmployeesAddSelectId(companyId, selectId){
    let data = await serviceGetEmployeesByCompanyId(companyId);
    $('#'+selectId+' option').remove();
    $.each(data.employees, function(i, employee){
        let optionRow = '<option value="'+employee.id+'">'+employee.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getEmployeesAddSelectIdWithZero(companyId, selectId){
    let data = await serviceGetEmployeesByCompanyId(companyId);
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Yetkili Seçiniz</option>');
    $.each(data.employees, function(i, employee){
        let optionRow = '<option value="'+employee.id+'">'+employee.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getMeasurementsAddSelectId(selectId){
    let data = await serviceGetMeasurements();
    $('#'+selectId+' option').remove();
    $.each(data.measurements, function(i, measurement){
        let optionRow = '<option value="'+measurement.name_tr+'">'+measurement.name_tr+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getParentCategoriesAddSelectId(selectId){
    let data = await serviceGetCategories();
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Kategori Seçiniz</option>');
    $.each(data.categories, function(i, category){
        let optionRow = '<option value="'+category.id+'">'+category.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getActivityTypesAddSelectId(selectId){
    let data = await serviceGetActivityTypes();
    $('#'+selectId+' option').remove();
    $.each(data.activity_types, function(i, activity_type){
        let optionRow = '<option value="'+activity_type.id+'">'+activity_type.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getPaymentTypesAddSelectId(selectId){
    let data = await serviceGetPaymentTypes();
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Ödeme Türü Seçiniz</option>');
    $.each(data.types, function(i, type){
        let optionRow = '<option value="'+type.id+'">'+type.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}
async function getPaymentMethodsAddSelectId(selectId){
    let data = await serviceGetPaymentMethods();
    $('#'+selectId+' option').remove();
    $('#'+selectId).append('<option value="0">Ödeme Yöntemi Seçiniz</option>');
    $.each(data.methods, function(i, method){
        let optionRow = '<option value="'+method.id+'">'+method.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getPaymentTermsAddSelectId(selectId){
    let data = await serviceGetPaymentTerms();
    $('#'+selectId+' option').remove();
    $.each(data.payment_terms, function(i, payment_term){
        let optionRow = '<option value="'+payment_term.id+'">'+payment_term.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getDeliveryTermsAddSelectId(selectId){
    let data = await serviceGetDeliveryTerms();
    $('#'+selectId+' option').remove();
    $.each(data.delivery_terms, function(i, delivery_term){
        let optionRow = '<option value="'+delivery_term.name+'">'+delivery_term.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getDocumentTypesAddSelectId(selectId){
    let data = await serviceGetMobileDocumentTypes();
    $('#'+selectId+' option').remove();
    $.each(data.document_types, function(i, document_type){
        let optionRow = '<option value="'+document_type.id+'">'+document_type.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}
async function getExpenseCategoriesAddSelectId(selectId){
    let data = await serviceGetExpenseCategories();
    $('#'+selectId+' option').remove();
    $.each(data.categories, function(i, category){
        let optionRow = '<option value="'+category.id+'">'+category.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function getCountriesAddSelectId(selectId){
    let data = await serviceGetCountries();
    $('#'+selectId+' option').remove();
    $.each(data.countries, function(i, country){
        let optionRow = '<option value="'+country.id+'">'+country.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}
async function getStatesAddSelectId(selectId, countryId){
    let data = await serviceGetStatesByCountryId(countryId);
    $('#'+selectId+' option').remove();
    $.each(data.states, function(i, state){
        let optionRow = '<option value="'+state.id+'">'+state.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}async function getStatesAddSelectIdWithParent(selectId, parentSelectId){
    let countryId = document.getElementById(parentSelectId).value;
    let data = await serviceGetStatesByCountryId(countryId);
    $('#'+selectId+' option').remove();
    $.each(data.states, function(i, state){
        let optionRow = '<option value="'+state.id+'">'+state.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}
async function getCitiesAddSelectId(selectId, stateId){
	let data = await serviceGetCitiesByStateId(stateId);
    $('#'+selectId+' option').remove();
	$.each(data.cities, function(i, city){
		var optionRow = '<option value="'+city.id+'">'+city.name+'</option>';
		$('#'+selectId).append(optionRow);
	});
}
async function getCitiesAddSelectIdWithParent(selectId, parentSelectId){
    let stateId = document.getElementById(parentSelectId).value;
	let data = await serviceGetCitiesByStateId(stateId);
    $('#'+selectId+' option').remove();
	$.each(data.cities, function(i, city){
		var optionRow = '<option value="'+city.id+'">'+city.name+'</option>';
		$('#'+selectId).append(optionRow);
	});
}
async function getBrandsAddSelectId(selectId){
    let data = await serviceGetBrands();
    $('#'+selectId+' option').remove();
    var optionRow = '<option value="">Marka Seçiniz</option>';
    $('#'+selectId).append(optionRow);
    $.each(data.brands, function(i, brand){
        var optionRow = '<option value="'+brand.id+'">'+brand.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}
async function getCategoriesAddSelectId(selectId){
    let data = await serviceGetCategories();
    $('#'+selectId+' option').remove();
    var optionRow = '<option value="">Ürün Grubu Seçiniz</option>';
    $('#'+selectId).append(optionRow);
    $.each(data.categories, function(i, category){
        var optionRow = '<option value="'+category.id+'">'+category.name+'</option>';
        $('#'+selectId).append(optionRow);
        $.each(category.sub_categories, function(i, category2){
            var optionRow = '<option value="'+category2.id+'">'+category2.name+'</option>';
            $('#'+selectId).append(optionRow);

        });
    });
}

async function getMailLayoutsAddSelectId(selectId){
    let data = await serviceGetEmailLayouts();
    $('#'+selectId+' option').remove();
    $.each(data.layouts, function(i, layout){
        let optionRow = '<option value="'+layout.id+'">'+layout.name+'</option>';
        $('#'+selectId).append(optionRow);
    });
}

async function fastChangeStatus(status_id, sale_id){
    let user_id = localStorage.getItem('userId');
    let formData = JSON.stringify({
        "sale_id": sale_id,
        "status_id": status_id,
        "user_id": user_id
    });
    let data = await servicePostUpdateSaleStatus(formData);
    if(data.status == "success"){
        window.location.href = '/sales';
    }
}



/* SERVICE FUNCTIONS */

async function serviceGetCountries() {
	const data = await fetchDataGet('/admin/countries/getCountries', 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}

async function serviceGetStatesByCountryId(countryId) {
	const data = await fetchDataGet('/admin/states/getStatesByCountryId/' + countryId, 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}

async function serviceGetCitiesByStateId(stateId) {
	const data = await fetchDataGet('/admin/cities/getCitiesByStateId/' + stateId, 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}

async function serviceGetAdmins() {
	const data = await fetchDataGet('/admin/adminRole/getAdmins', 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}
async function serviceGetAdminById(id) {
	const data = await fetchDataGet('/admin/adminRole/getAdminById/' + id, 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}
async function servicePostAddAdmin(formData) {
	const data = await fetchDataPost('/admin/adminRole/addAdmin', formData, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}
async function servicePostUpdateAdmin(id, formData) {
	const data = await fetchDataPost('/admin/adminRole/updateAdmin/' + id, formData, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}
async function servicePostUpdateUserOld(id, formData) {
	const data = await fetchDataPost('/admin/adminRole/updateUser/' + id, formData, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}

async function servicePostUpdateUser(id, formData) {
    await xhrDataPost('/admin/adminRole/updateUser/' + id, formData, updateProfileCallback);
}

async function servicePostUpdateMailSignature(formData) {
    await xhrDataPost('/admin/adminRole/updateMailSignature', formData, updateMailSignatureCallback);
}

async function serviceGetDeleteAdmin(id) {
	const data = await fetchDataGet('/admin/adminRole/deleteAdmin/' + id, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}

async function serviceGetMailSignaturesByAdminId(id) {
	const data = await fetchDataGet('/admin/adminRole/getMailSignaturesByAdminId/' + id, 'application/json');
	if (data.status == "success") {
        return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}

async function serviceGetAdminRoles() {
	const data = await fetchDataGet('/admin/adminRole/getAdminRoles', 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}
async function serviceGetAdminRoleById(id) {
	const data = await fetchDataGet('/admin/adminRole/getAdminRoleById/' + id, 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}
async function servicePostAddAdminRole(formData) {
	const data = await fetchDataPost('/admin/adminRole/addAdminRole', formData, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}
async function servicePostUpdateAdminRole(id, formData) {
	const data = await fetchDataPost('/admin/adminRole/updateAdminRole/' + id, formData, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}
async function serviceGetDeleteAdminRole(id) {
	const data = await fetchDataGet('/admin/adminRole/deleteAdminRole/' + id, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}

async function serviceGetAdminRolePermissions(role_id) {
	const data = await fetchDataGet('/admin/adminRole/getAdminRolePermissions/' + role_id, 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}
async function serviceGetCheckAdminRolePermission(admin_id, permission_id) {
	const data = await fetchDataGet('/admin/adminRole/getCheckAdminRolePermission/' + admin_id + '/' + permission_id, 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}
async function serviceGetAddAdminRolePermission(role_id, permission_id) {
	const data = await fetchDataGet('/admin/adminRole/addAdminRolePermission/' + role_id + '/' + permission_id, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}
async function serviceGetDeleteAdminRolePermission(role_id, permission_id) {
	const data = await fetchDataGet('/admin/adminRole/deleteAdminRolePermission/' + role_id + '/' + permission_id, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}

async function serviceGetAdminRoleStatuses(role_id) {
    const data = await fetchDataGet('/admin/adminRole/getAdminRoleStatuses/' + role_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetAddAdminRoleStatus(role_id, status_id) {
    const data = await fetchDataGet('/admin/adminRole/addAdminRoleStatus/' + role_id + '/' + status_id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteAdminRoleStatus(role_id, status_id) {
    const data = await fetchDataGet('/admin/adminRole/deleteAdminRoleStatus/' + role_id + '/' + status_id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetAdminPermissions() {
	const data = await fetchDataGet('/admin/adminPermission/getAdminPermissions', 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}

async function serviceGetCompanies() {
	const data = await fetchDataGet('/admin/company/getCompanies', 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}
async function serviceGetPotentialCustomers() {
    const data = await fetchDataGet('/admin/company/getPotentialCustomers', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetCustomers() {
	const data = await fetchDataGet('/admin/company/getCustomers', 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}
async function serviceGetSuppliers() {
    const data = await fetchDataGet('/admin/company/getSuppliers', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetCompanyById(id) {
	const data = await fetchDataGet('/admin/company/getCompanyById/' + id, 'application/json');
	if (data.status == "success") {
		return data.object;
	} else {
		showAlert('İstek Başarısız.');
	}
}

async function servicePostAddCompany(formData) {
    await xhrDataPost('/admin/company/addCompany', formData, addCompanyCallback);
}

async function servicePostUpdateCompany(id, formData) {
    await xhrDataPost('/admin/company/updateCompany/' + id, formData, updateCompanyCallback);
}
async function serviceGetDeleteCompany(id) {
	const data = await fetchDataGet('/admin/company/deleteCompany/' + id, 'application/json');
	if (data.status == "success") {
		showAlert(data.message);
		return true;
	} else {
		showAlert('İstek Başarısız.');
		return false;
	}
}

async function serviceGetCompaniesByStaffId(id) {
    const data = await fetchDataGet('/admin/company/getCompaniesByStaffId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetSaledProductsByCompanyId(id) {
    const data = await fetchDataGet('/admin/company/getSaledProductsByCompanyId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}


async function serviceGetEmployeesByCompanyId(id) {
    const data = await fetchDataGet('/admin/employee/getEmployeesByCompanyId/'+ id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetEmployeeById(id) {
    const data = await fetchDataGet('/admin/employee/getEmployeeById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddEmployee(formData) {
    await xhrDataPost('/admin/employee/addEmployee', formData, addEmployeeCallback);
}

async function servicePostUpdateEmployee(id, formData) {
    await xhrDataPost('/admin/employee/updateEmployee/' + id, formData, updateEmployeeCallback);
}
async function serviceGetDeleteEmployee(id) {
    const data = await fetchDataGet('/admin/employee/deleteEmployee/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetNotesByCompanyId(id) {
    const data = await fetchDataGet('/admin/note/getNotesByCompanyId/'+ id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetNoteById(id) {
    const data = await fetchDataGet('/admin/note/getNoteById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddNote(formData) {
    await xhrDataPost('/admin/note/addNote', formData, addNoteCallback);
}
async function servicePostUpdateNote(id, formData) {
    await xhrDataPost('/admin/note/updateNote/' + id, formData, updateNoteCallback);
}
async function serviceGetDeleteNote(id) {
    const data = await fetchDataGet('/admin/note/deleteNote/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetActivityTypes() {
    const data = await fetchDataGet('/admin/activity/getActivityTypes', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetActivityTypeById(id) {
    const data = await fetchDataGet('/admin/activity/getActivityTypeById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddActivityType(formData) {
    const data = await fetchDataPost('/admin/activity/addActivityType', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdateActivityType(id, formData) {
    const data = await fetchDataPost('/admin/activity/updateActivityType/' + id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteActivityType(id) {
    const data = await fetchDataGet('/admin/activity/deleteActivityType/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetActivities() {
    const data = await fetchDataGet('/admin/activity/getActivities', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetPastActivities() {
    const data = await fetchDataGet('/admin/activity/getPastActivities', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetActivitiesByCompanyId(id) {
    const data = await fetchDataGet('/admin/activity/getActivitiesByCompanyId/'+ id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetActivityById(id) {
    const data = await fetchDataGet('/admin/activity/getActivityById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddActivity(formData) {
    const data = await fetchDataPost('/admin/activity/addActivity', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdateActivity(formData, id) {
    const data = await fetchDataPost('/admin/activity/updateActivity/'+ id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteActivity(id) {
    const data = await fetchDataGet('/admin/activity/deleteActivity/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteActivityTask(id) {
    const data = await fetchDataGet('/admin/activity/deleteActivityTask/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetCompleteActivityTask(id) {
    const data = await fetchDataGet('/admin/activity/completeActivityTask/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetUnCompleteActivityTask(id) {
    const data = await fetchDataGet('/admin/activity/unCompleteActivityTask/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostAddActivityTask(formData) {
    const data = await fetchDataPost('/admin/activity/addActivityTask', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetAddressesByCompanyId(id) {
    const data = await fetchDataGet('/admin/company/getAddressesByCompanyId/'+ id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetAddressById(id) {
    const data = await fetchDataGet('/admin/company/getAddressById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddCompanyAddress(formData) {
    const data = await fetchDataPost('/admin/company/addCompanyAddress', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdateCompanyAddress(formData) {
    const data = await fetchDataPost('/admin/company/updateCompanyAddress', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteCompanyAddress(id) {
    const data = await fetchDataGet('/admin/company/deleteCompanyAddress/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetCompanyPointsByCompanyId(id) {
    const data = await fetchDataGet('/admin/company/getCompanyPointsByCompanyId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddCompanyPoint(formData) {
    const data = await fetchDataPost('/admin/company/addCompanyPoint', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}


async function serviceGetOfferRequests() {
    const data = await fetchDataGet('/admin/offerRequest/getOfferRequests', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetOfferRequestById(id) {
    const data = await fetchDataGet('/admin/offerRequest/getOfferRequestById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetOfferRequestProductsById(id) {
    const data = await fetchDataGet('/admin/offerRequest/getOfferRequestProductsById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostCreateOfferRequest(formData) {
    const data = await fetchDataPost('/admin/offerRequest/createOfferRequest', formData, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostAddOfferRequest(formData) {
    const data = await fetchDataPost('/admin/offerRequest/addOfferRequest', formData, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostOfferRequestProducts(formData, id) {
    const data = await fetchDataPost('/admin/offerRequest/offerRequestProducts/' + id, formData, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdateOfferRequest(id, formData) {
    const data = await fetchDataPost('/admin/offerRequest/updateOfferRequest/' + id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostAddProductToOfferRequest(id, formData) {
    const data = await fetchDataPost('/admin/offerRequest/addProductToOfferRequest/' + id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return data;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteProductToOfferRequest(id) {
    const data = await fetchDataGet('/admin/offerRequest/deleteProductToOfferRequest/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetOfferRequestsByCompanyId(id) {
    const data = await fetchDataGet('/admin/offerRequest/getOfferRequestsByCompanyId/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}




async function serviceGetNewOffersByRequestId(id) {
    const data = await fetchDataGet('/admin/offer/getNewOffersByRequestId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetOffersByRequestId(id) {
    const data = await fetchDataGet('/admin/offer/getOffersByRequestId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetOfferById(id) {
    const data = await fetchDataGet('/admin/offer/getOfferById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddOffer(formData) {
    const data = await fetchDataPost('/admin/offer/addOffer', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetOfferProductById(offer_id, product_id) {
    const data = await fetchDataGet('/admin/offer/getOfferProductById/' + offer_id + '/' + product_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddOfferProduct(formData, offer_id) {
    const data = await fetchDataPost('/admin/offer/addOfferProduct/' + offer_id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdateOfferProduct(formData, offer_id, product_id) {
    const data = await fetchDataPost('/admin/offer/updateOfferProduct/' + offer_id + '/' + product_id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteOffer(id) {
    const data = await fetchDataGet('/admin/offer/deleteOffer/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}


async function serviceGetContacts() {
    const data = await fetchDataGet('/admin/contact/getContacts', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetContactById(id) {
    const data = await fetchDataGet('/admin/contact/getContactById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostUpdateContact(formData) {
    await xhrDataPost('/admin/contact/updateContact', formData, updateContactCallback);
}
async function serviceGetDeleteContact(id) {
    const data = await fetchDataGet('/admin/contact/deleteContact/' + id, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}



async function serviceGetSaleTypes() {
    const data = await fetchDataGet('/admin/sale/getSaleTypes', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}



async function serviceGetSales() {
    const data = await fetchDataGet('/admin/sale/getSales', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetSalesByCompanyId(id) {
    const data = await fetchDataGet('/admin/sale/getSalesByCompanyId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetActiveSales() {
    let userId = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/sale/getActiveSales/'+ userId, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetApprovedSales() {
    let userId = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/sale/getApprovedSales/'+ userId, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCancelledSales() {
    let userId = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/sale/getCancelledSales/'+ userId, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostFilterSales(formData) {
    let userId = localStorage.getItem('userId');
    const data = await fetchDataPost('/admin/sale/getFilteredSales/'+ userId, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetSaleById(id) {
    const data = await fetchDataGet('/admin/sale/getSaleById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetSaleConfirmationById(id) {
    const data = await fetchDataGet('/admin/sale/getSaleConfirmationById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetPackingListSaleById(id) {
    const data = await fetchDataGet('/admin/sale/getPackingListSaleById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetApproveOfferBySaleId(id, revize) {
    let user_id = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/sale/getApproveOfferBySaleId/' + id + '/' + user_id + '/' + revize, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        return false;
    }
}

async function serviceGetRejectOfferBySaleId(id, revize) {
    let user_id = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/sale/getRejectOfferBySaleId/' + id + '/' + user_id + '/' + revize, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        return false;
    }
}

async function serviceGetSaleOfferById(id) {
    const data = await fetchDataGet('/admin/sale/getSaleOfferById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetSaleOffersByOfferId(id) {
    const data = await fetchDataGet('/admin/sale/getSaleOffersByOfferId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostAddSale(formData) {
    const data = await fetchDataPost('/admin/sale/addSale', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return data;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteSale(id) {
    const data = await fetchDataGet('/admin/sale/deleteSale/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostAddSaleOfferPrice(formData) {
    const data = await fetchDataPost('/admin/sale/addSaleOfferPrice', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateSaleOfferPrice(formData) {
    const data = await fetchDataPost('/admin/sale/updateSaleOfferPrice', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetRfqDetailById(id) {
    const data = await fetchDataGet('/admin/sale/getRfqDetailById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostUpdateRfqDetail(formData) {
    const data = await fetchDataPost('/admin/sale/updateRfqDetail', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateSaleStatus(formData) {
    const data = await fetchDataPost('/admin/sale/updateSaleStatus', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return data;
    } else {
        if (data.status == "status-001") {
            showAlert(data.message);
        }else if (data.status == "status-002") {
            showAlert(data.message);
        }else{
            showAlert('İstek Başarısız.');
        }
        return false;
    }
}

async function servicePostUpdatePackingListStatus(formData) {
    const data = await fetchDataPost('/admin/sale/updatePackingListStatus', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return data;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostAddPackingDeliveryAddress(formData) {
    const data = await fetchDataPost('/admin/sale/addPackingDeliveryAddress', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return data;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdatePackingDeliveryAddress(formData) {
    const data = await fetchDataPost('/admin/sale/updatePackingDeliveryAddress', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return data;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostAddCancelSaleNote(formData) {
    const data = await fetchDataPost('/admin/sale/addCancelSaleNote', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetQuoteBySaleId(id) {
    const data = await fetchDataGet('/admin/sale/getQuoteBySaleId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostUpdateQuote(formData) {
    const data = await fetchDataPost('/admin/sale/updateQuote', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}




async function serviceGetExpenseCategories() {
    const data = await fetchDataGet('/admin/sale/getExpenseCategories', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetSaleExpenseById(sale_id) {
    const data = await fetchDataGet('/admin/sale/getSaleExpenseById/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetSaleExpenseByCategoryId(sale_id, category_id) {
    const data = await fetchDataGet('/admin/sale/getSaleExpenseByCategoryId/' + sale_id + '/' + category_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddSaleExpense(formData) {
    const data = await fetchDataPost('/admin/sale/addSaleExpense', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteSaleExpense(expense_id) {
    const data = await fetchDataGet('/admin/sale/deleteSaleExpense/' + expense_id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostAddCustomerPONumber(formData) {
    const data = await fetchDataPost('/admin/sale/addCustomerPONumber', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetAddSalePin(sale_id) {
    const data = await fetchDataGet('/admin/sale/addSalePin/' + sale_id, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteSalePin(sale_id) {
    const data = await fetchDataGet('/admin/sale/deleteSalePin/' + sale_id, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}



async function serviceGetSaleNotes(id) {
    const data = await fetchDataGet('/admin/sale/getSaleNotes/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddSaleNote(formData) {
    const data = await fetchDataPost('/admin/sale/addSaleNote', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}


async function serviceGetSellingProcess(sale_id) {
    const data = await fetchDataGet('/admin/sale/getSellingProcess/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        return false;
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetLiveCurrencyLog() {
    const data = await fetchDataGet('/admin/sale/getLiveCurrencyLog', 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        return false;
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetLastCurrencyLog() {
    const data = await fetchDataGet('/admin/sale/getLastCurrencyLog', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetCheckSaleCurrencyLog(request_id) {
    const data = await fetchDataGet('/admin/sale/getCheckSaleCurrencyLog/'+ request_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddSaleCurrencyLog(formData, request_id) {
    const data = await fetchDataPost('/admin/sale/addSaleCurrencyLog/'+ request_id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdateSaleCurrencyLogOnPI(formData, request_id) {
    const data = await fetchDataPost('/admin/sale/updateSaleCurrencyLogOnPI/'+ request_id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetSaleByRequestId(request_id) {
    const data = await fetchDataGet('/admin/sale/getSaleByRequestId/'+ request_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetCurrencyLogs() {
    const data = await fetchDataGet('/admin/sale/getCurrencyLogs', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddCurrencyLog(formData) {
    const data = await fetchDataPost('/admin/sale/addCurrencyLog', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteCurrencyLog(log_id) {
    const data = await fetchDataGet('/admin/sale/deleteCurrencyLog/'+ log_id, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        return false;
        showAlert('İstek Başarısız.');
    }
}




async function serviceGetStatuses() {
    const data = await fetchDataGet('/admin/status/getStatuses', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetChangeableStatuses() {
    let userId = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/status/getChangeableStatuses', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetPackingStatuses() {
    let userId = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/status/getPackingStatuses', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetMeasurements() {
    const data = await fetchDataGet('/admin/measurement/getMeasurements', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}



async function serviceGetBankInfos() {
    const data = await fetchDataGet('/admin/owner/getBankInfos', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetBankInfoById(id) {
    const data = await fetchDataGet('/admin/owner/getBankInfoById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostAddBankInfo(formData) {
    const data = await fetchDataPost('/admin/owner/addBankInfo', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateBankInfo(formData) {
    const data = await fetchDataPost('/admin/owner/updateBankInfo', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetDeleteBankInfo(id) {
    const data = await fetchDataGet('/admin/owner/deleteBankInfo/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}



async function serviceGetPurchasingOrderDetailById(id) {
    const data = await fetchDataGet('/admin/sale/getPurchasingOrderDetailById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostAddPurchasingOrderDetail(formData) {
    const data = await fetchDataPost('/admin/sale/addPurchasingOrderDetail', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdatePurchasingOrderDetail(formData) {
    const data = await fetchDataPost('/admin/sale/updatePurchasingOrderDetail', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}



async function serviceGetOrderConfirmationDetailById(id) {
    const data = await fetchDataGet('/admin/sale/getOrderConfirmationDetailById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostAddOrderConfirmationDetail(formData) {
    const data = await fetchDataPost('/admin/sale/addOrderConfirmationDetail', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateOrderConfirmationDetail(formData) {
    const data = await fetchDataPost('/admin/sale/updateOrderConfirmationDetail', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}


async function serviceGetProformaInvoiceDetailById(id) {
    const data = await fetchDataGet('/admin/sale/getProformaInvoiceDetailById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostUpdateProformaInvoiceDetail(formData) {
    const data = await fetchDataPost('/admin/sale/updateProformaInvoiceDetail', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateShippingPrice(formData) {
    const data = await fetchDataPost('/admin/sale/updateShippingPrice', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}



async function serviceGetSaleHistoryActions() {
    const data = await fetchDataGet('/admin/newsFeed/getSaleHistoryActions', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetTopSaledProducts(dash_owner) {
    const data = await fetchDataGet('/admin/newsFeed/getTopSaledProducts/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetTopRequestedProducts() {
    const data = await fetchDataGet('/admin/newsFeed/getTopRequestedProducts', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetSaleStats() {
    const data = await fetchDataGet('/admin/newsFeed/getSaleStats', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetLanguage(lang) {
    const data = await fetchDataGet('/admin/language/changeLanguage/' + lang, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}


async function serviceGetBrands() {
    const data = await fetchDataGet('/admin/brand/getBrands', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetBrandById(brandId) {
    const data = await fetchDataGet('/admin/brand/getBrandById/' + brandId, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostAddBrand(formData) {
    await xhrDataPost('/admin/brand/addBrand', formData, addBrandCallback);
}

async function servicePostUpdateBrand(brandId, formData) {
    await xhrDataPost('/admin/brand/updateBrand/' + brandId, formData, updateBrandCallback);
}

async function serviceGetDeleteBrand(brandId) {
    const data = await fetchDataGet('/admin/brand/deleteBrand/' + brandId, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}



async function serviceGetCategories() {
    const data = await fetchDataGet('/admin/category/getCategory', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCategoryById(category_id) {
    const data = await fetchDataGet('/admin/category/getCategoryById/' + category_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCategoriesByParentId(parent_id) {
    const data = await fetchDataGet('/admin/category/getCategoryByParentId/' + parent_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostAddCategory(formData) {
    const data = await fetchDataPost('/admin/category/addCategory', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateCategory(formData, category_id) {
    const data = await fetchDataPost('/admin/category/updateCategory/' + category_id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetDeleteCategory(categoryId) {
    const data = await fetchDataGet('/admin/category/deleteCategory/' + categoryId, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetProducts() {
    const data = await fetchDataGet('/admin/product/getProducts', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetProductById(id) {
    const data = await fetchDataGet('/admin/product/getProductById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostAddImportedProducts(formData) {
    const data = await fetchDataPost('/admin/product/addImportedProducts', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostAddProduct(formData) {
    const data = await fetchDataPost('/admin/product/addProduct', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateProduct(formData, id) {
    const data = await fetchDataPost('/admin/product/updateProduct/' + id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetDeleteProduct(id) {
    const data = await fetchDataGet('/admin/product/deleteProduct/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateProductName(formData, id) {
    const data = await fetchDataPost('/admin/product/updateProductName/' + id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetSaleSummary(sale_id) {
    const data = await fetchDataGet('/admin/sale/getSaleSummary/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetSaleDetailInfo(sale_id) {
    const data = await fetchDataGet('/admin/sale/getSaleDetailInfo/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetSaleStatusHistory(sale_id) {
    const data = await fetchDataGet('/admin/sale/getSaleStatusHistory/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetSaleSuppliers(sale_id) {
    const data = await fetchDataGet('/admin/sale/getSaleSuppliers/' + sale_id, 'application/json');
    console.log(data)
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}



async function servicePostAddPackingList(formData) {
    const data = await fetchDataPost('/admin/sale/addPackingList', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetPackingableProductsBySaleId(sale_id) {
    const data = await fetchDataGet('/admin/sale/getPackingableProductsBySaleId/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetPackingListsBySaleId(sale_id) {
    const data = await fetchDataGet('/admin/sale/getPackingListsBySaleId/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetPackingListProductsById(packing_list_id) {
    const data = await fetchDataGet('/admin/sale/getPackingListProductsById/' + packing_list_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetDeletePackingList(id) {
    const data = await fetchDataGet('/admin/sale/deletePackingList/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdatePackingNote(formData) {
    const data = await fetchDataPost('/admin/sale/updatePackingListNote', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}



async function serviceGetAccountingStats() {
    const data = await fetchDataGet('/admin/accounting-dashboard/getAccountingStats', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCashFlows() {
    const data = await fetchDataGet('/admin/accounting-dashboard/getCashFlows', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCashFlowPayments() {
    const data = await fetchDataGet('/admin/accounting-dashboard/getCashFlowPayments', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}




async function serviceGetPaymentTypes() {
    const data = await fetchDataGet('/admin/accounting/getPaymentTypes', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetPaymentMethods() {
    const data = await fetchDataGet('/admin/accounting/getPaymentMethods', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetAccountingPaymentType(sale_id) {
    const data = await fetchDataGet('/admin/accounting/getAccountingPaymentType/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetPaymentTerms() {
    const data = await fetchDataGet('/admin/setting/getPaymentTerms', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetPaymentTermById(id) {
    const data = await fetchDataGet('/admin/setting/getPaymentTermById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddPaymentTerm(formData) {
    const data = await fetchDataPost('/admin/setting/addPaymentTerm', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdatePaymentTerm(id, formData) {
    const data = await fetchDataPost('/admin/setting/updatePaymentTerm/' + id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetDeletePaymentTerm(id) {
    const data = await fetchDataGet('/admin/setting/deletePaymentTerm/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}


async function serviceGetDeliveryTerms() {
    const data = await fetchDataGet('/admin/setting/getDeliveryTerms', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetDeliveryTermById(id) {
    const data = await fetchDataGet('/admin/setting/getDeliveryTermById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddDeliveryTerm(formData) {
    const data = await fetchDataPost('/admin/setting/addDeliveryTerm', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdateDeliveryTerm(id, formData) {
    const data = await fetchDataPost('/admin/setting/updateDeliveryTerm/' + id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteDeliveryTerm(id) {
    const data = await fetchDataGet('/admin/setting/deleteDeliveryTerm/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}



async function serviceGetPendingAccountingSales() {
    let userId = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/accounting/getPendingAccountingSales/'+ userId, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetOngoingAccountingSales() {
    let userId = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/accounting/getOngoingAccountingSales/'+ userId, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetCompletedAccountingSales() {
    let userId = localStorage.getItem('userId');
    const data = await fetchDataGet('/admin/accounting/getCompletedAccountingSales/'+ userId, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetAccountingPayments(sale_id) {
    const data = await fetchDataGet('/admin/accounting/getAccountingPayments/'+ sale_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetAccountingPaymentById(payment_id) {
    const data = await fetchDataGet('/admin/accounting/getAccountingPaymentById/'+ payment_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostAddAccountingPayment(formData) {
    const data = await fetchDataPost('/admin/accounting/addAccountingPayment', formData, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateAccountingPayment(formData) {
    const data = await fetchDataPost('/admin/accounting/updateAccountingPayment', formData, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateAccountingPaymentStatus(formData) {
    const data = await fetchDataPost('/admin/accounting/updateAccountingPaymentStatus', formData, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateAccountingWaybill(formData) {
    const data = await fetchDataPost('/admin/accounting/updateAccountingWaybill', formData, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}



/* DASHBOARD SERVICES */

async function serviceGetTotalSales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getTotalSales/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetLastMonthSales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getLastMonthSales/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetDashboardStats(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getDashboardStats/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetLastMonthSalesByAdmin(id) {
    const data = await fetchDataGet('/admin/dashboard/getLastMonthSalesByAdmin/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetDashboardApprovedSales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getApprovedSales/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetDashboardCompletedSales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getCompletedSales/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetDashboardPotentialSales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getPotentialSales/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetDashboardCancelledSales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getCancelledSales/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetApprovedMonthlySales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyApprovedSalesLastTwelveMonths/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetApprovedMonthlySalesByAdmin(id) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyApprovedSalesLastTwelveMonthsByAdmin/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCompletedMonthlySales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyCompletedSalesLastTwelveMonths/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCompletedMonthlySalesByAdmin(id) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyCompletedSalesLastTwelveMonthsByAdmin/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetPotentialSales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyPotentialSalesLastTwelveMonths/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetPotentialSalesByAdmin(id) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyPotentialSalesLastTwelveMonthsByAdmin/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCancelledPotentialSales(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyCancelledSalesLastTwelveMonths/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCancelledPotentialSalesByAdmin(id) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyCancelledSalesLastTwelveMonthsByAdmin/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}


async function serviceGetMonthlyApprovedSalesLastTwelveMonthsByAdmins(dash_owner) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyApprovedSalesLastTwelveMonthsByAdmins/' + dash_owner, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetMonthlyApprovedSalesLastTwelveMonthsByAdminId(id) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyApprovedSalesLastTwelveMonthsByAdminId/'+ id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetDocuments(id) {
    const data = await fetchDataGet('/admin/sale/getDocuments/'+ id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetMobileDocuments(id) {
    const data = await fetchDataGet('/admin/mobile/getDocuments/'+ id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetMobileDocumentTypes() {
    const data = await fetchDataGet('/admin/mobile/getDocumentTypes', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddMobileDocument(formData, id) {
    await xhrDataPost('/admin/mobile/addDocument/'+id, formData, addMobileDocumentCallback);
}
async function serviceGetDeleteMobileDocument(id) {
    const data = await fetchDataGet('/admin/mobile/deleteDocument/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetGeneratePDF(owner_id, sale_id) {
    const data = await fetchDataGet('/admin/pdf/getGeneratePDF/' + owner_id + '/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetGenerateQuatotionPDF(lang, owner_id, sale_id) {
    const data = await fetchDataGet('/admin/pdf/getGenerateQuatotionPDF/' + lang + '/' + owner_id + '/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetGenerateOrderConfirmationPDF(lang, owner_id, sale_id, bank_id) {
    const data = await fetchDataGet('/admin/pdf/getGenerateOrderConfirmationPDF/' + lang + '/' + owner_id + '/' + sale_id + '/' + bank_id, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetGenerateProformaInvoicePDF(lang, owner_id, sale_id, bank_id, target) {
    const data = await fetchDataGet('/admin/pdf/getGenerateProformaInvoicePDF/' + lang + '/' + owner_id + '/' + sale_id + '/' + bank_id + '/' + target, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetGenerateInvoicePDF(lang, owner_id, sale_id, bank_id) {
    const data = await fetchDataGet('/admin/pdf/getGenerateInvoicePDF/' + lang + '/' + owner_id + '/' + sale_id + '/' + bank_id, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetGeneratePackingListInvoicePDF(lang, owner_id, packing_list_id, bank_id) {
    const data = await fetchDataGet('/admin/pdf/getGeneratePackingListInvoicePDF/' + lang + '/' + owner_id + '/' + packing_list_id + '/' + bank_id, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetGeneratePurchasingOrderPDF(lang, owner_id, sale_id, offer_id) {
    const data = await fetchDataGet('/admin/pdf/getGeneratePurchasingOrderPDF/' + lang + '/' + owner_id + '/' + sale_id + '/' + offer_id, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetGenerateRfqPDF(lang, owner_id, offer_id) {
    const data = await fetchDataGet('/admin/pdf/getGenerateRfqPDF/' + lang + '/' + owner_id + '/' + offer_id, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetGeneratePackingListPDF(lang, owner_id, packing_list_id) {
    const data = await fetchDataGet('/admin/pdf/getGeneratePackingListPDF/' + lang + '/' + owner_id + '/' + packing_list_id, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetGenerateSaleSummaryPDF(sale_id) {
    const data = await fetchDataGet('/admin/pdf/getGenerateSaleSummaryPDF/' + sale_id, 'application/json');
    if (data.status == "success") {
        return data;
    } else {
        showAlert('İstek Başarısız.');
    }
}


async function serviceGetStaffStatistics(id, m, y) {
    const data = await fetchDataGet('/admin/staff/getStaffStatisticsWMY/' + id + '/' + m + '/' + y, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetStaffSituation(id) {
    const data = await fetchDataGet('/admin/staff/getStaffSituationWMY/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetAllStaffStatistics() {
    const data = await fetchDataGet('/admin/staff/getAllStaffStatistics', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetAllStaffStatisticsMonthly() {
    const data = await fetchDataGet('/admin/staff/getAllStaffStatisticsMonthly', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetStaffTargetTypes() {
    const data = await fetchDataGet('/admin/staff/getStaffTargetTypes', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetStaffTargets() {
    const data = await fetchDataGet('/admin/staff/getStaffTargets', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetStaffTargetsByStaffId(id) {
    const data = await fetchDataGet('/admin/staff/getStaffTargetsByStaffId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetStaffTargetById(id) {
    const data = await fetchDataGet('/admin/staff/getStaffTargetById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddStaffTarget(formData) {
    const data = await fetchDataPost('/admin/staff/addStaffTarget', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdateStaffTarget(formData) {
    const data = await fetchDataPost('/admin/staff/updateStaffTarget', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteStaffTarget(id) {
    const data = await fetchDataGet('/admin/staff/deleteStaffTarget/' + id, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        return false;
    }
}

async function serviceGetStaffPointsByStaffId(id) {
    const data = await fetchDataGet('/admin/staff/getStaffPointsByStaffId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddStaffPoint(formData) {
    const data = await fetchDataPost('/admin/staff/addStaffPoint', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}


async function serviceGetBestCustomer() {
    const data = await fetchDataGet('/admin/company/getBestCustomer', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetBestStaff() {
    const data = await fetchDataGet('/admin/staff/getBestStaff', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetTotalProfitRate(owner_id) {
    const data = await fetchDataGet('/admin/dashboard/getTotalProfitRate/' + owner_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetMonthlyProfitRates(owner_id) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyProfitRates/' + owner_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetMonthlyTurningRates(owner_id) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyTurningRates/' + owner_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetMonthlyProfitRatesLastTwelveMonths(owner_id) {
    const data = await fetchDataGet('/admin/dashboard/getMonthlyProfitRatesLastTwelveMonths/' + owner_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCustomerByNotSaleLongTimes() {
    const data = await fetchDataGet('/admin/dashboard/getCustomerByNotSaleLongTimes', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetCustomerByNotSale() {
    const data = await fetchDataGet('/admin/dashboard/getCustomerByNotSale', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function serviceGetBestSalesLastNinetyDays(owner_id) {
    const data = await fetchDataGet('/admin/dashboard/getBestSalesLastNinetyDays/' + owner_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}


async function servicePostCompanyChatMessage(formData) {
    const data = await fetchDataPost('/admin/companyChat/sendMessage', formData, 'application/json');
    if (data.status == "success") {
        // showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetCompanyChatMessages(page) {
    const data = await fetchDataGet('/admin/companyChat/getPublicChatMessages/' + page, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}

async function servicePostAddNotificationSetting(formData) {
    const data = await fetchDataPost('/admin/notify/addNotifySetting', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function servicePostUpdateNotificationSetting(formData) {
    const data = await fetchDataPost('/admin/notify/updateNotifySetting', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteNotificationSetting(id) {
    const data = await fetchDataGet('/admin/notify/deleteNotifySetting/' + id, 'application/json');
    if (data.status == "success") {
        return true;
    } else {
        return false;
    }
}
async function serviceGetNotifySettings() {
    const data = await fetchDataGet('/admin/notify/getNotifySettings', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetNotifySettingById(id) {
    const data = await fetchDataGet('/admin/notify/getNotifySettingById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetReadNotifyById(notify_id) {
    const data = await fetchDataGet('/admin/notify/getReadNotifyById/' + notify_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetReadAllNotifyByUserId(user_id) {
    const data = await fetchDataGet('/admin/notify/getReadAllNotifyByUserId/' + user_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetNotReadNotifyCountByUserId(user_id) {
    const data = await fetchDataGet('/admin/notify/getNotReadNotifyCountByUserId/' + user_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetNotifiesByUserId(user_id) {
    const data = await fetchDataGet('/admin/notify/getNotifiesByUserId/' + user_id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}


async function serviceGetEmailLayouts() {
    const data = await fetchDataGet('/admin/mail/getLayouts', 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function serviceGetEmailLayoutById(id) {
    const data = await fetchDataGet('/admin/mail/getLayoutById/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostAddEmailLayout(formData) {
    const data = await fetchDataPost('/admin/mail/addLayout', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function servicePostUpdateEmailLayout(id, formData) {
    const data = await fetchDataPost('/admin/mail/updateLayout/' + id, formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
async function serviceGetDeleteEmailLayout(id) {
    const data = await fetchDataGet('/admin/mail/deleteLayout/' + id, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}

async function serviceGetMailableSuppliersByRequestId(id) {
    const data = await fetchDataGet('/admin/mail/getMailableSuppliersByRequestId/' + id, 'application/json');
    if (data.status == "success") {
        return data.object;
    } else {
        showAlert('İstek Başarısız.');
    }
}
async function servicePostSendMailOfferToSupplier(formData) {
    const data = await fetchDataPost('/admin/mail/sendMailOfferToSupplier', formData, 'application/json');
    if (data.status == "success") {
        showAlert(data.message);
        return true;
    } else {
        showAlert('İstek Başarısız.');
        return false;
    }
}
