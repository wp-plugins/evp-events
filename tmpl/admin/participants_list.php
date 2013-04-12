<h2><?php _e("Orders", "evp-event") ?></h2>
<!--<?php if(isset($_GET['success'])): ?>
	<div id="message" class="updated below-h2"><p><?php _e("Order successfully saved", "evp-event") ?></p></div>
<? endif ?>-->
<?php echo $table->display(); ?>
<?php //echo $event_id;?>
<!--<a href="admin.php?page=event-<?php echo $event_id ?>-participants&action=create"><?php _e("Add new", "evp-event") ?></a>-->