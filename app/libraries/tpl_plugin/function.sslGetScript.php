<?php

/* SSL Form action script 반환 */
function sslGetScript()
{
	$CI =& get_instance();
	$CI->load->model('ssl');
	$CI->load->helper('javascript');

	if(!$CI->ssl->ssl_use) return;
	
	$action_url = $CI->ssl->get_action_url();
	
	$script = "
		$(\"input.sslCheckBox\")
		.each(function(){
			$(this.form).attr('original_action',this.form.action);
		})
		.live('change',function(){
			if($(this).is(':checked')){
				this.form.action = '{$action_url}' + encodeURIComponent(this.form.action);
			}else{
				this.form.action = $(this.form).attr('original_action');
			}
			this.form.method = 'post';
		});
	";
	
	return $script; 
	
}
?>