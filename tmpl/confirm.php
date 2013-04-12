<?php if(!defined('WPE')) die(); ?>
	<?php if(!empty($_POST['discount_code']) && !$_SESSION['FORM']['DISCOUNT']): ?>
        <p style="clear:both; overflow:hidden; padding: 12px; background: #FFFABF; border: 1px solid #FFF15F; margin:0px 0px 15px 0px; font-size:14px;
        font-weight: bold; color: red"><?php _e("You discount code is invalid or expired", "evp-event") ?></p>
    <?php endif; ?>
    <?php if(!empty($_POST['discount_code']) && $_SESSION['FORM']['DISCOUNT']): ?>
    	  <p style="clear:both; overflow:hidden; padding: 12px; background: #FFFABF; border: 1px solid #FFF15F; margin:0px 0px 15px 0px; font-size:14px;
        font-weight: bold; color: green"><?php printf(_e("Discount code <i>%s</i> successfully used", "evp-event"),$_POST['discount_code']) ?></p>
    <?php endif ?>
    <div class="ticket_price">
			<strong><?php _e("Ticket price", "evp-event") ?>:</strong> <?php echo $event_cfg['tickets'][$_SESSION['FORM']['TICKET_PRICE']]['price'] ?> 
			<?php echo $event_cfg['currency'] ?></span>
	
		<br />
        <?php
           if($discount > 0):
        ?>
            <strong><?php _e("Discount for ticket", "evp-event") ?></strong>: <?php echo discount_for_ticket(); ?> <?php echo $event_cfg['currency'] ?> <br />
        <?php endif ?>

		<strong><?php _e("Particpants count", "evp-event") ?></strong>: <?php echo $total_users ?><br />
		<strong><?php _e("Total sum", "evp-event") ?></strong>: <?php echo $total_price ?> <?php echo $event_cfg['currency'] ?><br />
	</div>
	<br />
	<div id="form_company" <?php echo $_SESSION['FORM']['COMPANY'] ? 'style="display:block"' : '' ?>>
		<h2><?php _e("Company info", "evp-event") ?></h2>
			<table style="width:100%">
				<tr>
					<td><?php _e("Company name", "evp-event") ?>:</td>
					<td><?php echo @$_SESSION['FORM']['COMPANY']['name'] ?></td>
				</tr>
                <tr>
                    <td><?php _e("Company address", "evp-event") ?>:</td>
                    <td><?php echo @$_SESSION['FORM']['COMPANY']['address'] ?></td>
                </tr>
				<tr>
					<td><?php _e("Company code", "evp-event") ?>:</td>
					<td><?php echo @$_SESSION['FORM']['COMPANY']['code'] ?></td>
				</tr>
				<tr>
					<td><?php _e("Company PVM Code", "evp-event") ?>:</td>
					<td><?php echo @$_SESSION['FORM']['COMPANY']['pvm_code'] ?></td>		
				</tr>
				<tr>
					<td><?php _e("Company contact person", "evp-event") ?>:</td>
					<td><?php echo @$_SESSION['FORM']['COMPANY']['person_name'] ?></td>		
				</tr>
				<tr>
					<td><?php _e("Company contact phone", "evp-event") ?>:</td>
					<td><?php echo @$_SESSION['FORM']['COMPANY']['person_phone'] ?></td>		
				</tr>
				<tr>
					<td><?php _e("Company fax", "evp-event") ?>:</td>
					<td><?php echo @$_SESSION['FORM']['COMPANY']['fax'] ?></td>		
				</tr>
				<tr>
					<td><?php _e("Company contact email", "evp-event") ?>:</td>
					<td><?php echo @$_SESSION['FORM']['COMPANY']['person_email'] ?></td>		
				</tr>
		</table>
	</div>
	<br />
	<h2><?php _e("Participants", "evp-event") ?></h2>

	<table style="width:100%" id="table_participants">
		<tr>
			<th><?php _e("Name Surname", "evp-event") ?></th>
			<th><?php _e("Email", "evp-event") ?></th>
			<th><?php _e("Phone number", "evp-event") ?></th>
			<th></th>
		</tr>
		<?php foreach($_SESSION['FORM']['USERS'] as $user): ?>
			<tr name="entry">
				<td><?php echo $user['name'] ?></td>
				<td><?php echo $user['email'] ?></td>
				<td><?php echo $user['phone'] ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
    <br />

	<br /><br />
	<input id="button_edit" type="button" class="primary-button" value="< <?php _e("Change order", "evp-event") ?>" />
	<?php if($_SESSION['FORM']['payment_type'] == 'guaranty'): ?>
		<input id="button_confirm" type="button" class="primary-button" style="float:right" value="<?php _e("Accept", "evp-event") ?> >" />
	<?php else: ?>
		<input id="button_pay" type="button" class="primary-button" style="float:right" value="<?php _e("Pay", "evp-event") ?> >" />
	<?php endif ?>
	<br />
<style>
	#form_company {
		display:none;
	}
	
	.sub {
		font-size: 0.9em;
	}
</style>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$("#button_edit").click(function() {
			window.location = "<?php echo get_permalink();  ?>"
		});
		
		$("#button_pay").click(function() {
			window.location = "<?php echo add_query_arg( 'action', 'payment_redirect' ); ?>";
		});

		$("#button_confirm").click(function() {
			window.location = "<?php echo add_query_arg( 'action', 'payment_guaranty' ); ?>";
		});
	});
</script>

