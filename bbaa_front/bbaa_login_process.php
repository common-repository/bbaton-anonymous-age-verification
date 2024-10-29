<?php
/**
 * Get bbaton.com auth link as return link after user accept or decline 
**/
add_action('wp_ajax_nopriv_bbaa_login_process', 'bbaa_login_process');
add_action('wp_ajax_bbaa_login_process', 'bbaa_login_process');
if (!function_exists("bbaa_login_process"))
{
    function bbaa_login_process()
    {
        $client_id = sanitize_text_field(get_option('bbaa_client_id'));
        $client_secret = sanitize_text_field(get_option('bbaa_client_secret'));
        $redirect_uri = sanitize_text_field(get_option('bbaa_redirect_url'));
        $authorization_code = sanitize_text_field($_POST['code']);
        $url = "https://bauth.bbaton.com/oauth/authorize?client_id=$client_id&redirect_uri=$redirect_uri&response_type=code&scope=read_profile";
        echo $url;
        wp_die();
    }
}

add_action('wp_ajax_nopriv_request_token', 'request_token');
add_action('wp_ajax_request_token', 'request_token');
if (!function_exists("request_token"))
{
    function request_token()
    {       
          $imp_uid =  sanitize_text_field($_POST['imp_uid']);
            $apiData = array(
          "imp_key" => "3019915759100860", // REST API키
          "imp_secret" => "XIFkKK24L9ZIkwtUvFZBtECP8lvGoCymleG5YIaJR9scGV6cA6N4KqTTMoAb08xlndrlWB5NLxZrs6E2"
            );
            $headers = array(
                'Content-type'  => 'application/json'
            );
                $response = wp_remote_post(
                    'https://api.iamport.kr/users/getToken', array(
                    'method' => 'POST',
                    'timeout' => 60,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => $headers,
                    'body'          => json_encode($apiData),
                    'cookies' => array()
                ));
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body);
                if (!is_wp_error( $response) &&  !empty($data->response->access_token ) )
                { 
                $access_token = $data->response->access_token;
                $headers = array(
               "Authorization" => $access_token
                );
                $apiData = array();
                  $response = wp_remote_post(
                    'https://api.iamport.kr/certifications/'.trim($imp_uid), array(
                        'method' => 'GET',
                        'timeout' => 60,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking' => true,
                        'headers' => $headers,
                        'body'          => $apiData,
                        'cookies' => array()
                    ));  
        if (is_wp_error($response)) {
            $res = array('success'=>false, 'msg'=> 'Authentication Error!');
            } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);
            $res = array('success'=>true, 'data'=> $data->response);
            }
        } else {
            $res = array('success'=>false, 'msg'=> 'Authentication Error!');   
        }  
        wp_send_json($res);
    }
}

