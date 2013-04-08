<? if(!defined('WPE')) die(); ?>
<center><strong><? _e("Payment canceled", "evp-event") ?></strong></center>
<br />
<input type="button" id="go_back" style="float: left" value="<<? _e("Back", "evp-event") ?>" />

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$("#go_back").click(function() {
			window.location = "<?php echo add_query_arg( 'action', 'confirm' ); ?>";
		});
	});
</script>
