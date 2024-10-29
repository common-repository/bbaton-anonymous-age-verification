<?php
// create custom plugin settings menu
add_action('admin_menu', 'bbaa_plugin_create_menu');
if (!function_exists('bbaa_plugin_create_menu'))
{
	function bbaa_plugin_create_menu()
	{

		//create new top-level menu
		add_menu_page('BBaton Settings', 'BBaton Settings', 'manage_options', 'bbaa_settings', 'bbaa_plugin_settings_page', 'dashicons-yes-alt', 25);

		//call register settings function
		add_action('admin_init', 'register_my_bbaa_plugin_settings');
		add_option('bbaa_scroll_to_show','true');
	}
}
function media_uploader_enqueue() {
	wp_enqueue_media();
	wp_register_script('media-uploader', plugins_url('media-uploader.js' , __FILE__ ), array('jquery'));
	wp_enqueue_script('media-uploader');
}
add_action('admin_enqueue_scripts', 'media_uploader_enqueue');

if (!function_exists('register_my_bbaa_plugin_settings'))
{
	function register_my_bbaa_plugin_settings()
	{
		//register our settings
		register_setting('bbaa-plugin-settings-group', 'bbaa_client_id');
		register_setting('bbaa-plugin-settings-group', 'background_image');
		register_setting('bbaa-plugin-settings-group', 'bbaa_client_secret');
		register_setting('bbaa-plugin-settings-group', 'bbaa_redirect_url');
		register_setting('bbaa-plugin-settings-group', 'bbaa_lock_pages');       
		register_setting('bbaa-plugin-settings-group', 'bbaa_exit_url');
		register_setting('bbaa-plugin-settings-group', 'bbaa_scroll_to_show');
		register_setting('bbaa-plugin-settings-group', 'bbaa_site_url');
	}
}

if (!function_exists('bbaa_plugin_settings_page'))
{
	function bbaa_plugin_settings_page()
	{
?>
<script type="text/javascript">

	jQuery(document).ready(function(){

		jQuery("#all_pages_parent option").each(function(){
			if(jQuery(this).attr('status') == 0){
				jQuery(this).show();
			}else{
				jQuery(this).hide();
			}
		});

		//console.log(status);

	}); 

</script>
<div class="wrap" style="background: #fff;padding: 10px 15px;box-shadow: 1px 1px 3px #ddd, -1px -1px 3px #ddd;">
	<h1><?php echo __('BBaton Anonymous Age Verification'); ?> </h1><hr>
	<p style="font-size: 18px; font-weight: bold;">비바톤을 웹사이트에 적용하기 위해서는 클라이언트 가입을 통한 웹사이트 연결이 필요합니다. www.bbaton.com 에서 연결을 신청해주세요.</p>
	<?php settings_errors(); ?>

	<form method="post" action="options.php">
		<?php

	 settings_fields('bbaa-plugin-settings-group'); ?>
		<?php do_settings_sections('bbaa-plugin-settings-group'); ?>

		<?php
	 $bbaa_lock_pages = get_option('bbaa_lock_pages', array('pages' => []));
	 $bbaa_headings = sanitize_text_field(get_option('bbaa_headings'));
		?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php echo __('비바톤 Client ID'); ?></th>
				<td><input type="text" name="bbaa_client_id" value="<?php echo sanitize_text_field(get_option('bbaa_client_id')); ?>" style="width:70%;"/></td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php echo __('비바톤 Secret key'); ?></th>
				<td><input type="text" name="bbaa_client_secret" value="<?php echo sanitize_text_field(get_option('bbaa_client_secret')); ?>" style="width:70%;"/></td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php echo __('비바톤 redirect url'); ?></th>
				<td><input type="text" name="bbaa_redirect_url" value="<?php echo sanitize_text_field(get_option('bbaa_redirect_url')); ?>" style="width:70%;"/></td>
			</tr>       


			<tr valign="top">
				<th scope="row"><?php echo __('19세 미만 랜딩페이지'); ?></th>
				<td>
					<?php 
	 $pages = get_pages(); 
					?>
					<select name="bbaa_exit_url" id="" >
						<?php 
	 foreach ($pages as $page_data) {
		 $link = get_page_link($page_data->ID);
		 $is_selected = sanitize_text_field(get_option('bbaa_exit_url')) == $link ? "selected" : null;
						?> 

						<option value="<?php echo $link; ?>" <?php echo $is_selected; ?>><?php echo $page_data->post_title;?></option>

						<?php } ?>
					</select>

			</tr>

			<tr valign="top">
				<th scope="row"><?php echo __('성인인증을 적용할 페이지 (다중 선택 가능)'); ?></th>
				<td>
					<select name="bbaa_lock_pages[pages][]" multiple="multiple" style="width:100%;    max-width: 70%;" id="all_pages_parent"> 
						<option value="9999999999999999999" style="text-transform:capitalize;" status="0"

								<?php $none = $bbaa_lock_pages['pages'][0];

	 if ($none == "9999999999999999999")
	 {
		 echo "selected='selected' ";
	 } ?> >None</option>


						<?php

	 $pages = get_pages();

	 $i = 0;

	 foreach ($pages as $page)
	 {
		 if (in_array($page->ID, $bbaa_lock_pages['pages']))
		 {
			 $option = '<option value="' . $page->ID . '" style="text-transform:capitalize;" selected="selected" status="' . $page->post_parent . '">';
			 $option .= $page->post_title;
			 $option .= '</option>';
		 }
		 else
		 {
			 $option = '<option value="' . $page->ID . '" style="text-transform:capitalize;" status="' . $page->post_parent . '">';
			 $option .= $page->post_title;
			 $option .= '</option>';

		 }
		 echo $option;
		 $i++;
	 }
						?>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php echo __('로그인 페이지 좌측에 표시될 이미지 (8:9 사이즈 권장)'); ?></th>
				<td><input id="background_image" type="text" name="background_image" value="<?php echo get_option('background_image'); ?>" />
					<input id="upload_image_button" type="button" class="button-primary" value="Insert Logo" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php echo __('성인인증을 적용할 페이지가 미리보이도록 하시겠습니까?', 'Client Logo'); ?></th>
				<td><input id="bbaa_scroll_to_show" type="checkbox" name="bbaa_scroll_to_show" <?php if(get_option('bbaa_scroll_to_show') == true){ echo "checked='checked'";}?> value="<?php 
	 if(!empty(get_option('bbaa_scroll_to_show')) && get_option('bbaa_scroll_to_show') == true){echo get_option('bbaa_scroll_to_show');} ?>" />
				</td>
			</tr>
			<tr valign="top">
<th scope="row"><?php echo __('로그인 페이지 하단에 표시될 url 주소를 입력해주세요. (http:// 혹은 https:// 포함)', 'Client Logo'); ?></th>
				<td><input id="bbaa_site_url" type="text" name="bbaa_site_url"  value="<?php  if(!empty(get_option('bbaa_site_url')) && get_option('bbaa_site_url') == true){echo get_option('bbaa_site_url');} ?>" style="width:70%;" />
				</td>
			</tr>

		</table>

		<?php submit_button(); ?>
		<script>

			jQuery(document).ready(function(){
				jQuery('#bbaa_scroll_to_show').click(function(){
					if (jQuery(this).prop("checked") === true) {
						jQuery(this).val("true");
					}else{
						jQuery(this).val('false');
					}
				})
			});

		</script>
	</form>
</div>
<?php
	}
} ?>