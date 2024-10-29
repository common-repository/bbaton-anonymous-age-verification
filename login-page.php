<?php 

// require(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php'); 
require($_SERVER['DOCUMENT_ROOT'] .'/wp-load.php');

?>

<link href="https://fonts.googleapis.com/css2?family=Nanum+Gothic&display=swap" rel="stylesheet">
<link href="<?php echo plugins_url('assets/css/bbaa.css?ver=2.5', __FILE__); ?>" rel="stylesheet">

<style>
	.bbaa {
		background:#fff
	}
	<?php
	$left_img = get_option('background_image');
	if( $left_img == "" ){
	?>
	.left-bg{
		background-image: url('<?php echo plugins_url('assets/bbaton-bg.jpg', __FILE__); ?>') ; 
	}
	<?php
	}else{
	?>
	.left-bg{
		background-image: url("<?php echo $left_img  ?>");
	}
	<?php
	}

	?>
	.left-bg{
		height: 100vh;
		background-size: cover;
		background-repeat: no-repeat;
	}

	@media only screen and (max-width: 1024px){
		.left-bg{
			height: 50vh;
		}
	}


</style>
<div class="bbaa">
	<div class="cs-container">
		<div class="col-left">
			<div class="spacer">
				<div class="left-bg">
				</div>
			</div>
		</div>
		<div class="col-right text-center">
			<span class="number">19</span>
			<p>
				본 내용은 청소년 유해매체물로서 정보통신망 이용촉진법 및 정보보호 등에 관한 법률 및 청소년 보호법 규정에 의하여 19세 미만의 청소년은 사용할 수 없습니다.
			</p>
			<div  class="btn-container">
				<div style="padding: 4px;">
					<a style='display: inline-block;' id='bbaton_auth' href='#'>
						<img src='<?php echo plugins_url('assets/BBaton_Logo_Login_KR_v2.png?ver=2.12', __FILE__); ?>'/></a>
				</div>
			</div>
<!-- 			<div  class="btn-container">
				<div style="padding: 4px;">
					<a style='display: inline-block;' id='iamport_auth' href='#'>
						<img src='<?php echo plugins_url('assets/iamport.png?ver=2.12', __FILE__); ?>'/></a>
				</div>
			</div> -->
			<div class="btn-container">
				<div style="padding: 4px;">
					<a style='display: inline-block;color:#000; text-decoration:underline;margin-top: 20px;margin-bottom: 50px;' href='<?php echo get_option('bbaa_exit_url'); ?>'>
						19세미만 나가기
						<!-- 						<img src='<?php //echo plugins_url('assets/exit_button.png?ver=2.12', __FILE__); ?>'/> -->
					</a>
				</div>
			</div>
			<div  class="btn-container">
				<div style="padding:1px;">
					<a style='display: inline-block;' id='' href='<?php  if(!empty(get_option('bbaa_site_url')) && get_option('bbaa_site_url') == true){echo get_option('bbaa_site_url');} ?>'><?php  if(!empty(get_option('bbaa_site_url')) && get_option('bbaa_site_url') == true){echo get_option('bbaa_site_url');} ?></a>
				</div>
			</div> 
		</div>
	</div>
</div>
<script>
	jQuery("header").remove();
	jQuery("#header-top-bar").remove();
</script>