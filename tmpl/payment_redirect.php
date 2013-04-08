<?php if(!defined('WPE')) die(); ?>
<span id="loading" style="display:block;width:280px;height: 100%; margin: 0 auto; font-weight:bold"><?php _e("Redirecting to mokejimai.lt payment system", "evp-event") ?></span>
<form id="payform" action="<?php echo WebToPay::PAY_URL; ?>" method="post">
    <?php foreach ($request as $key => $val): ?>
        <input type="hidden"
               name="<?php echo $key ?>"
               value="<?php echo htmlspecialchars($val); ?>" />
    <?php endforeach; ?>
</form>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		window.setInterval(function() {
			$("#loading").text( $("#loading").text() + ".");	
		}, 300);
		$("#payform").submit();
	});
</script>
