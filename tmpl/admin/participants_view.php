<h2><?php _e("Order view", "evp-event")?></h2>

<?php if(isset($_GET['success'])): ?>
	<div id="message" class="updated below-h2"><p><?php _e("Payment guaranty successfully confirmed", "evp-event") ?></p></div>
<? endif ?>

<div style="width: 900px">
<input style="float: right; color: red" type="button" class="button" onclick="remove_confirm('admin.php?page=event-<?php echo admin_get_event_id()?>-participants&action=remove_order&id=<?= $invoice->id?>&noheader=true')" value="<?php _e("Remove order", "evp-event") ?>" />

<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php  _e("Amount", "evp-event")?></th>
		<td><?= $invoice->amount ?> <?php echo $event_cfg['currency']?> </td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e("Creation date", "evp-event"); ?></th>
		<td><?= $invoice->created ?></td>
	</tr>
	
	<? if($invoice->payment_type == 0 || $invoice->payment_type == 2): ?>
		<tr valign="top">
			<th scope="row"><?php _e("Paid in", "evp-event")?></th>
			<td><?= $invoice->paid_in ? "<span style='color: green'>"._e("Yes", "evp-event")."</span>" : "<span style='color: red'>"._e("No", "evp-event")."</span>" ?></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e("Paid in date", "evp-event")?></th>
			<td><?= $invoice->paid_in_date ?></td>
		</tr>
        <? if($invoice->payment_type == 2): ?>
            <tr valign="top">
                <th scope="row"><?php _e("Bank transfer", "evp-event") ?></th>
                <td><?php _e("Yes", "evp-event") ?> <input style="margin-top: 20px; margin-right: 100px" onclick="window.location='<?= WP_PLUGIN_URL ?>/evp-event/pdf/<?= $invoice->id ?>.pdf'" class="button" type="submit" value='Sąskaita' /></td>
            </tr>
        <? else: ?>
            <tr valign="top">
                <th scope="row"><?php _e("Bank transfer", "evp-event") ?></th>
                <td><?php _e("No", "evp-event") ?></td>
            </tr>
        <? endif ?>
	<? else: ?>
		<tr valign="top">
			<th scope="row"><?php _e("Payment guaranty", "evp-event") ?></th>
			<td><?php _e("Yes", "evp-event") ?> <input onclick="window.location='<?php echo get_site_url() ?>?guaranty_template&id=<?php echo $company->id ?>'" class='button-primary' type='submit' value='<?php _e("Download", "evp-event") ?>' /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e("Payment guaranty confirmed", "evp-event") ?></th>
			<td><?= $invoice->paid_in == 1 ? 'Taip' : "Ne&nbsp;&nbsp;<input onclick='window.location=\"admin.php?page=event-". admin_get_event_id()."-participants&action=guarantee_confirm&id={$invoice->id}&noheader=true\"' class='button-primary' type='submit' value='".__("Confirm", "evp-event")."' />" ?></td>
		</tr
    <? endif ?>
	<tr valign="top">
		<th scope="row"><?php _e("Participants count", "evp-event") ?></th>
		<td><?= count($users); ?> </td>
	</tr>
	</table>
<?php if(isset($company)): ?>
<h3><?php _e("Company information", "evp-event") ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e("Company name", "evp-event")?></th>
		<td><?= $company->name ?></td>
	</tr>
    <tr valign="top">
        <th scope="row"><? _e("Company address", "evp-event") ?></th>
        <td><?= $company->address ?></td>
    </tr>
	<tr valign="top">
		<th scope="row"><?php _e("Company code", "evp-event") ?></th>
		<td><?= $company->code ?></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e("Company VAT code", "evp-event")?></th>
		<td><?= $company->pvm_code ?></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e("Company Fax", "evp-event") ?></th>
		<td><?= $company->fax ?></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e("Responsable person (Name Surname)", "evp-event")?></th>
		<td><?= $company->person_name ?></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e("Responsable phone", "evp-event")?></th>
		<td><?= $company->person_phone ?></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e("Responsable email", "evp-event") ?></th>
		<td><a href="mailto:<?= $company->person_email ?>"><?= $company->person_email ?></a></td>
	</tr>	
</table>
<?php endif ?>


<h3><?php _e("Participant list", "evp-event") ?></h3>
<table class="wp-list-table widefat fixed dalyviai"  cellspacing="0">
	<thead>
		<tr>
			<th id="col_id"><?php _e("Name Surname", "evp-event") ?></th>
			<th id="col_id"><?php _e("Phone", "evp-event") ?></th>
			<th id="col_id"><?php _e("Mail", 'evp-event') ?></th>
			<th id="col_id"><?php _e('Ticket code', 'evp-event')?></th>
			<th id="col_id"><?php _e('Ticket used', 'evp-event') ?></th>
		</tr>
	</thead>
<? foreach($users as $user): ?>
	<tr>
		<td><?= $user->name ?></td>
		<td><?= $user->phone ?></td>
		<td><a href="mailto:<?=  $user->email ?>"><?=  $user->email ?></a></td>
		<td><a href="<?= site_url() ?>?event_ticket&id=<?= $user->ticket_nr ?>"><?= $user->ticket_nr ?></a></td>
		<td><?= $user->used ?></td>
	</tr>
<? endforeach ?>
</table>

</div>


<script>
function remove_confirm(location) {
	if(window.confirm("<?php _e('Do you realy want remove order?', 'evp-event') ?>")) {
		window.location = location;
	}
	else {
		return false;
	}
}
</script>
