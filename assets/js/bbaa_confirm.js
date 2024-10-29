jQuery(document).ready(function(){

	var all_urls = jQuery.parseJSON(localStorage.getItem("site_url"));
	jQuery.each(all_urls, function(index, value) {
    	//console.log(value);
    	if(jQuery(location).attr("href") == value){
    		jQuery(".bbaa").css("display","none");
    	}
	});

	var site_url = JSON.parse(localStorage.getItem('site_url'));
	if(site_url !== null){
		var last_element = site_url[site_url.length-1];
		jQuery(".confirm_done a").attr("href",last_element);
	}


});