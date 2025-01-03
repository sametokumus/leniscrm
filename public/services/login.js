(function($) {
    "use strict";

	 $(document).ready(function() {

		 $(":input").inputmask();
		 $("#login_email").inputmask("email");


		 $( "#login_form" ).submit(function( event ) {
		 	event.preventDefault();
			let userEmail = document.getElementById('login_email').value;
			let userPass = document.getElementById('login_password').value;

			let formData = JSON.stringify({
				"email": userEmail,
				"password": userPass
			});

			fetchDataPost('/admin/login', formData, 'application/json').then(data=>{
				if(data.status == "success"){
					let __userInfo = data.object.admin;

						localStorage.setItem('userRole',__userInfo.admin_role_id);
						localStorage.setItem('userId',__userInfo.id);
						localStorage.setItem('userEmail',__userInfo.email);
						localStorage.setItem('userName',__userInfo.name + ' ' + __userInfo.surname);
						localStorage.setItem('userPhoto',__userInfo.profile_photo);
						localStorage.setItem('appToken',__userInfo.token);

						try{
							var hash = __userInfo.admin_role_id.toString()+(__userInfo.id).toString()+__userInfo.email;
							var salt = gensalt(5);
							function result(newhash){
								localStorage.setItem('userLogin',newhash);

								var rel = getURLParam('rel');
								if(rel != null && rel=="xxx"){
									window.location.href = "xxx?id=";
								}else{
									window.location.href = "my-dashboard";
								}
							}
							hashpw(hash, salt, result, function() {});


						}catch(err){
							showAlert('err');
							return;
						}

				}else{
					showAlert('data.message');
				}
			});

		 });

	});

})(window.jQuery);
