jQuery(document).ready(function(){
	var mer_uid= '';
	var imp_uid= '';
	var IMP = window.IMP;
	IMP.init(bbaton_anonymous_age_verification.iamport_merchant_id);
	jQuery(".bbaa #iamport_auth").on("click", function (e){
		e.preventDefault();
		IMP.certification({popup: true}, function (rsp) { // callback
		if (rsp.success) {
		mer_uid = rsp.merchant_uid; 
        imp_uid = rsp.imp_uid;
        var postData = {
            imp_uid: imp_uid,
            action : 'request_token'
          };
              jQuery.ajax({
                type: "POST",
                url: bbaton_anonymous_age_verification.ajax_url,
                dataType: "json",
                data: postData,
                success: function (response) {
                  if( response.success ) {
                    var user_info = response.data;
                    console.log(response);
                    console.log(checkIsAdult(user_info.birthday));
                    if(checkIsAdult(user_info.birthday) > 18 && user_info.name){
                    var user_name = user_info.name;
                    var gender = user_info.gender;
                    var birthday = user_info.birthday.replace(/–/gi, "");
                    var phone = user_info.phone;
                    var carrier = user_info.carrier;
                  setCookie('bbaton_anonymous_age_verification_confirmed',mer_uid,1);
                  window.open("https://bapi.bbaton.com/v1/user/auto-register?name="+user_name+"&gender="+gender+"&birthday="+birthday+"&phone="+phone+"&carrier="+carrier+"&impUid="+imp_uid+"&url="+bbaton_anonymous_age_verification.site_url+"", "bbaton", "width=400, height=500");
              //    url_redirect = "https://bapi.bbaton.com/v1/user/auto-register?name="+user_name+"&gender="+gender+"&birthday="+birthday+"&phone="+phone+"&carrier="+carrier+"&impUid="+imp_uid+"&url="+window.opener.location.href+"";
                //  window.location.href = url_redirect;
                    } else {
                      alert('Age Verification Failed!.');
                      location.reload();
                    }
                      } else {
                      alert(response.msg);
                      setTimeout(function(){ location.reload(); }, 2000);
                      }
                  },
                  error: function () {}
              });
			 } else {
					alert(rsp.error_msg);
			 }
		});
		  
	});

	jQuery(".bbaa #bbaton_auth").click(function(e){
		e.preventDefault();
		var current_url = jQuery(location).attr("href");
		if (typeof(Storage) !== "undefined") {
			localStorage.setItem("bbaton_anonymous_age_verification_last_url", current_url);
		}
		var bbaa_client_id = bbaton_anonymous_age_verification.bbaa_client_id;
		var redirect_uri = bbaton_anonymous_age_verification.redirect_uri;
		window.open( "https://bauth.bbaton.com/oauth/authorize?client_id="+bbaa_client_id+"&redirect_uri="+redirect_uri+"&response_type=code&scope=read_profile","bbaton","width=400, height=500");
		// var data = {
		// 	'action': 'bbaa_login_process',
		// 	'backUrl':  current_url
		// };
		// // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		// jQuery.post(bbaton_anonymous_age_verification.ajax_url, data, function(response) {
		// 	window.location.href = response;
		// });
	});
	if(jQuery("div").hasClass("bbaa")){
		jQuery("html").css("overflow-y","hidden !important");
	}
});

function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  var expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + "; domain="+bbaton_anonymous_age_verification.domain+";path=/ ";
}
function checkIsAdult(enteredDate){
  var years = new Date(new Date() - new Date(enteredDate)).getFullYear() - 1970;
  years = parseInt(years)+parseInt(1);
  return years;
}