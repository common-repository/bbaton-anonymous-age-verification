<?php
/*
* Plugin Name: 비바톤 익명 성인인증 | BBaton Anonymous Age Verification
* Description: Anonymous Age Verification Service for Korean Users
* Version: 2.10
* Author: BBaton
* Author URI: https://www.bbaton.com
* License: GPL2 or later
* Text Domain: bbaton-anonymous-age-verification
* Domain Path: /languages
*/
defined("ABSPATH") or die("No direct access!");

if (!class_exists("BBatonAnonymousAgeVerification")) {
	class BBatonAnonymousAgeVerification
	{
		function __construct()
		{
			add_action('init', array($this, 'bbaa_start_from_here'));
			if (in_array('oxygen/functions.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				add_action('oxygen_enqueue_scripts', array($this,	'bbaa_enqueue_script_front'), 999);
				add_action('wp_footer', array($this,	"bbaa_lock_page_content"));
			} else {
				add_action('wp_enqueue_scripts', array($this,	'bbaa_enqueue_script_front'), 999);
				add_filter("the_content", array($this,	"bbaa_lock_page_content"), 10, 2);
			}
			//add_action('init', array($this, 'bbaa_analyze_child'));
			add_action('init', array($this, 'bbaa_redirect_back'));
		}

		function bbaa_start_from_here()
		{
			require_once plugin_dir_path(__FILE__) . 'bbaa_back/bbaa_api_settings.php';
			require_once plugin_dir_path(__FILE__) . 'bbaa_front/bbaa_login_process.php';
		}
		// Enqueue Style and Scripts
		function bbaa_enqueue_script_front()
		{
			//Style & Script
			wp_enqueue_script('bbaa-script', plugins_url('assets/js/bbaa.js', __FILE__), array('jquery'), '2.5', false);
			wp_enqueue_script('iamport-script', "https://cdn.iamport.kr/js/iamport.payment-1.1.8.js", array('jquery'), '1.2', false);
			wp_localize_script('bbaa-script', 'bbaton_anonymous_age_verification', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'iamport_merchant_id' => 'imp34765018',
				'site_url' =>  get_site_url(),
				'template' => plugin_dir_url(__FILE__),
				'domain' => parse_url(get_option('siteurl'), PHP_URL_HOST),
				'bbaa_client_id' => (sanitize_text_field(get_option('bbaa_client_id'))),
				'redirect_uri' => sanitize_text_field(get_option('bbaa_redirect_url'))
			));
		}
		// Lock Page
		function bbaa_lock_page_content($content)
		{

			global $_COOKIE, $wp_query;
			if (isset($_COOKIE['bbaton_anonymous_age_verification_confirmed']) && $_COOKIE['bbaton_anonymous_age_verification_confirmed']) return $content;
			$heading = sanitize_text_field(get_option('bbaa_headings'));
			$bbaa_lock_pages = get_option('bbaa_lock_pages', array(
				'pages' => []
			));

			if (in_array($wp_query->post->ID, $bbaa_lock_pages['pages'])) {
				if (is_page($wp_query->post->ID)) {
					if (!empty(get_option('bbaa_scroll_to_show')) && get_option('bbaa_scroll_to_show') == true) { ?>
						<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
						<script>
							jQuery(document).ready(function() {
								jQuery('body').click(function() {
									alter_security();
								});
								jQuery('body a').click(function(e) {
									e.preventDefault();
									alter_security();
								});
							});
							// 	var lastScrollTop = 0;
							// 	jQuery(window).scroll(function(event){
							// 		var st = jQuery(this).scrollTop();
							// 		if (st > 20){
							// 			alter_security();
							// 		} 
							// 		lastScrollTop = st;
							// 	});

							var alter_security = (function() {
								var executed = false;
								return function() {
									if (!executed) {
										executed = true;
										jQuery.get(bbaton_anonymous_age_verification.template + 'login-page.php', function(data, status) {

											if (jQuery('#content').length == 0) {
												jQuery('body').append('<section id="content"> </section>');
											}
											jQuery(data).insertBefore("#content");
											// re include js
											jQuery(document).ready(function() {
												var mer_uid = '';
												var imp_uid = '';
												var IMP = window.IMP;
												IMP.init(bbaton_anonymous_age_verification.iamport_merchant_id);
												jQuery(".bbaa #iamport_auth").on("click", function(e) {
													e.preventDefault();
													IMP.certification({
														popup: true
													}, function(rsp) { // callback
														if (rsp.success) {
															mer_uid = rsp.merchant_uid;
															imp_uid = rsp.imp_uid;
															var postData = {
																imp_uid: imp_uid,
																action: 'request_token'
															};
															jQuery.ajax({
																type: "POST",
																url: bbaton_anonymous_age_verification.ajax_url,
																dataType: "json",
																data: postData,
																success: function(response) {
																	if (response.success) {
																		var user_info = response.data;
																		console.log(response);
																		console.log(checkIsAdult(user_info.birthday));
																		if (checkIsAdult(user_info.birthday) > 18 && user_info.name) {
																			var user_name = user_info.name;
																			var gender = user_info.gender;
																			var birthday = user_info.birthday.replace(/–/gi, "");
																			var phone = user_info.phone;
																			var carrier = user_info.carrier;
																			setCookie('bbaton_anonymous_age_verification_confirmed', mer_uid, 1);
																			window.open("https://bapi.bbaton.com/v1/user/auto-register?name=" + user_name + "&gender=" + gender + "&birthday=" + birthday + "&phone=" + phone + "&carrier=" + carrier + "&impUid=" + imp_uid + "&url=" + bbaton_anonymous_age_verification.site_url + "", "bbaton", "width=400, height=500");

																		} else {
																			alert('Age Verification Failed!.');
																			location.reload();
																		}
																	} else {
																		alert(response.msg);
																		setTimeout(function() {
																			location.reload();
																		}, 2000);
																	}
																},
																error: function() {}
															});
														} else {
															alert(rsp.error_msg);
														}
													});

												});

												jQuery(".bbaa #bbaton_auth").click(function(e) {
													e.preventDefault();
													var current_url = jQuery(location).attr("href");
													if (typeof(Storage) !== "undefined") {
														localStorage.setItem("bbaton_anonymous_age_verification_last_url", current_url);
													}
													var bbaa_client_id = bbaton_anonymous_age_verification.bbaa_client_id;
													var redirect_uri = bbaton_anonymous_age_verification.redirect_uri;
													window.open("https://bauth.bbaton.com/oauth/authorize?client_id=" + bbaa_client_id + "&redirect_uri=" + redirect_uri + "&response_type=code&scope=read_profile", "bbaton", "width=400, height=500");

												});
												if (jQuery("div").hasClass("bbaa")) {
													jQuery("html").css("overflow-y", "hidden !important");
												}
											});

											function setCookie(cname, cvalue, exdays) {
												var d = new Date();
												d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
												var expires = "expires=" + d.toUTCString();
												document.cookie = cname + "=" + cvalue + ";" + expires + "; domain=" + bbaton_anonymous_age_verification.domain + ";path=/ ";
											}

											function checkIsAdult(enteredDate) {
												var years = new Date(new Date() - new Date(enteredDate)).getFullYear() - 1970;
												years = parseInt(years) + parseInt(1);
												return years;
											}


											jQuery(document).ready(function() {

												var all_urls = jQuery.parseJSON(localStorage.getItem("site_url"));
												jQuery.each(all_urls, function(index, value) {
													//console.log(value);
													if (jQuery(location).attr("href") == value) {
														jQuery(".bbaa").css("display", "none");
													}
												});

												var site_url = JSON.parse(localStorage.getItem('site_url'));
												if (site_url !== null) {
													var last_element = site_url[site_url.length - 1];
													jQuery(".confirm_done a").attr("href", last_element);
												}


											});
										});
									}
								};
							})();
						</script>

<?php
						return $content;
					} else {
						return include_once __DIR__ . "/login-page.php";
					}
				} else {
					return $content;
				}
			} else {
				return $content;
			}
		}
		function bbaa_analyze_child()
		{
			$bbaa_lock_pages = get_option('bbaa_lock_pages', array(
				'pages' => []
			));
			$array = array();
			foreach ($bbaa_lock_pages['pages'] as $page) {
				array_push($array, $page);
				$mypages = get_pages(array(
					'child_of' => $page
				));
				$i = 0;
				foreach ($mypages as $page) {
					array_push($array, $page->ID);
					$i++;
				}
			}
			$bbaa_lock_pages['pages'] = $array;
			update_option("bbaa_lock_pages", $bbaa_lock_pages);
		}
		/**
		 * Display message on page to user to confirm age and Connect to bbaton.com to make auth operation
		 *
		 */
		function bbaa_redirect_back()
		{
			if (isset($_GET['code'])) {
				$code = sanitize_text_field($_GET['code']);
				$url = 'https://bauth.bbaton.com/oauth/token';
				$client_id = sanitize_text_field(get_option('bbaa_client_id'));
				$client_secret = sanitize_text_field(get_option('bbaa_client_secret'));
				$redirect_uri = sanitize_text_field(get_option('bbaa_redirect_url'));
				$basicauth = 'Basic ' . base64_encode($client_id . ':' . $client_secret);
				$headers = array(
					'Authorization' => $basicauth,
					'Content-type' => 'application/x-www-form-urlencoded'
				);
				$response = wp_remote_post($url, array(
					'method' => 'POST',
					'timeout' => 60,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => $headers,
					'body' => array(
						'grant_type' => 'authorization_code',
						'redirect_uri' => $redirect_uri,
						'code' => $code
					),
					'cookies' => array()
				));
				$body = wp_remote_retrieve_body($response);
				$data = json_decode($body);
				if (!empty($data)) {
					$url1 = 'https://bapi.bbaton.com/v1/user/me';
					$token = $data->access_token;
					$response = wp_remote_get($url1, array(
						'headers' => array(
							'Authorization' => "Bearer " . $token
						),
					));
					$flagJson = wp_remote_retrieve_body($response);
					$result = json_decode($flagJson);
					if ($result->adult_flag == '"Y"') {
						$path = '/';
						$domain = parse_url(get_option('siteurl'), PHP_URL_HOST);
						setcookie('bbaton_anonymous_age_verification_confirmed', $code, time() + 86400, $path, $domain);
					}
					echo '<script>
						var redirectURL = "' . home_url() . '";
						if (localStorage.getItem("bbaton_anonymous_age_verification_last_url")) {
							redirectURL = localStorage.getItem("bbaton_anonymous_age_verification_last_url");
						} 
						opener.location.href = redirectURL;
						self.close();
						</script>';
					die();
				}
			}
		}
	}
	$obj = new BBatonAnonymousAgeVerification();
}
