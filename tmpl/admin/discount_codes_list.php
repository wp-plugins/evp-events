<h2><?php _e("Discount codes", "evp-event") ?></h2>
<?php if(isset($_GET['success'])): ?>
	<div id="message" class="updated below-h2"><p><?php _e("Discount code successfully saved", "evp-event") ?></p></div>
<? endif ?>
<?php echo $table->display(); ?>
<a href="admin.php?page=event-<?php echo $event_id ?>-discount-codes&action=create"><?php _e("Add new", "evp-event") ?></a>