let otp_modal = ( show = true ) => {
	if(show) {
		jQuery('#otp-shield-modal').show();
	}
	else {
		jQuery('#otp-shield-modal').hide();
	}
}

jQuery(function($){
	
	$('#otp-shield_report-copy').click(function(e) {
		e.preventDefault();
		$('#otp-shield_tools-report').select();

		try {
			if( document.execCommand('copy') ){
				$(this).html('<span class="dashicons dashicons-saved"></span>');
			}
		} catch (err) {
			console.log('Oops, unable to copy!');
		}
	});
})