<?php date_default_timezone_set('Europe/Vilnius'); ?>
<script type="text/javascript">
jQuery(document).ready(function($) {

	/*
    $('#valid_from').datepicker({
        dateFormat : 'yy-mm-dd'
    });
    */
	/*
    jQuery.validator.addMethod(
            "trioDate",
            function(value, element) {
            	//return Date.parse(value);
                return value.match('(\d{4})-(\d{2})-(\d{2})[ tT](.*)');
            },
            "Please enter a date in the format yyyy-mm-dd h:m"
     );
    
    $('#ui-datepicker-div').css('clip', 'auto');
    */
    
    $("#event_form").validate();
   	disable_smtp();

	$('a[id^="remove_row_"]').click(function() {
		$(this).parent().parent().remove();
	});

	$("#add-row").click(function() {
		
		var id  = $('#table-tickets tr').length-1;
		
		var insert_point = $("#table-tickets tr:last");

		$('<tr>'	                          
			+'<td><input type="text" name="tickets['+id+'][name]" value="" /></td>'
			+'<td><input type="text" size="6" class="number required" name="tickets['+id+'][price]" /> <span class="price">LTL</span></td>'
			+'<td><input type="text" name="tickets['+id+'][amount]" value="" size="5" /></td>'
			+ '<td><input type="checkbox" name="tickets['+id+'][payment_types][webtopay]" /><?php _e('WebToPay', 'evp-event') ?><br />'
				+ '<input type="checkbox" name="tickets['+id+'][payment_types][prebill]" /><?php _e('PreBill', 'evp-event') ?><br />'
				+ '<input type="checkbox" name="tickets['+id+'][payment_types][guaranty]" /><?php _e('Payment guaranty', 'evp-event') ?><br />'
			+ '</td>'
			+ '<td><input type="text" size="2" class="digits" name="tickets['+id+'][discount_persons]"  /> <?php _e('persons', 'evp-event')?>'
		        	+ '<input type="text" size="2" class="number" name="tickets['+id+'][discount_percent]" /> %'
		    +	'</td>'	
		    + '<td><a href="javascript:void(0)" id="remove_row_'+id+'">X</a></td>'
		+'</td>').insertAfter(insert_point);

		$('a[id=remove_row_'+id+']').click(function() {
			$(this).parent().parent().remove();
		});
		
	});
    
});


function disable_smtp() {
	if(jQuery("input[name='smtp[enabled]']").is(':checked')) {
		jQuery("#smtp-table").show();
	}
	else {
		jQuery("#smtp-table").hide();
	}
}

function remove_confirm(location) {
	if(window.confirm("<?php _e('Do you realy want remove event?', 'evp-event') ?>")) {
		window.location = location;
	}
	else {
		return false;
	}
}


</script>
<style>
.stuffbox {
	width: 950px;
}

div.error {
	color: red;
	font-weight: bold;
	margin-right: 10px;
	font-size: 12px;
	width: 500px;
	margin-bottom: 20px;
	padding: 5px;
}

input[disabled='disabled'] {
	background-color: #DFDFDF;
}

label.error {
	margin-left: 4px;
	color: red;
	font-weight: bold;
}
</style>
<div id="icon-users" class="icon32">
	<br>
</div>
<?php if(is_null($id)): ?>
<h2>
	<?php _e('Create new event', 'evp-event') ?>
</h2>
<br />
<?php else: ?>
<h2>
	<?php _e('Event settings', 'evp-event') ?>
</h2>
<br />
<?php  endif ?>

<?php if(isset($edit_success)): ?>
<div id="message" style="width: 800px; margin-bottom: 10px"
	class="updated below-h2">
	<p>
		<?= _e("Event settings successfully saved", "evp-event"); ?>
	</p>
</div>
<? endif ?>

<?php if(!empty($errors)): ?>
<div id="message" class="error">
	<?php foreach($errors as $error): ?>
	<?php echo $error ?>
	<br />
	<?php endforeach ?>
</div>
<? endif ?>

<form method="post" id="event_form" enctype="multipart/form-data"
	action="<?php echo $_SERVER["REQUEST_URI"]  ?>&noheader=true">

	<div class="stuffbox">
		<h3>
			<label for="webtopay">1. <?php _e('Create new page with placeholder', 'evp-event') ?>
			</label>
		</h3>
		<div class="inside">
			<table class="form-table">
				<tr>
					<td><?php _e('Event ID', 'evp-event') ?></td> <!--Renginio ID-->
					<td><input type="text" value="<?php echo $data['id'] ?>" name="id"
						size="2" readonly="readonly" /></td>
					<?php if($id): ?>
					<td><input style="float: right; color: red" type="button"
						class="button"
						onclick="remove_confirm('admin.php?page=event-<?php echo $id ?>-participants&action=remove_event')"
						value="<?php _e("Remove event", "evp-event") ?>" /></td>
					<?php endif ?>
				</tr>
				<tr>
					<td><?php _e('Placeholder', 'evp-event') ?></td>
					<td><input type="text" size="15"
						value="<?php echo $data['placeholder'] ?>" readonly="readonly" /><br />
						<span class="small description"><?php _e('Copy this placeholder in the page body', 'evp-event') ?>
					</span>
					</td>
				</tr>
			</table>
		</div>
		<br />
	</div>

	<div class="stuffbox">
		<h3>
			<label for="webtopay">2. <?php _e('Enter event details', 'evp-event'); ?>
			</label>
		</h3>
		<div class="inside">
			<table class="form-table">
				<tr>
					<td><?php _e('Valid from', 'evp-event') ?></td>
					<td><input type="text" id="valid_from" name="valid_from"
						class="required" value="<?php echo $data['valid_from'] ?>" /></td>
				</tr>
				<tr>
					<td><?php _e('Valid to', 'evp-event') ?></td>
					<td><input type="text" id="valid_to" name="valid_to"
						value="<?php echo $data['valid_to'] ?> ">
					</td>
				</tr>
				<tr>
					<td><?php _e('Currency', 'evp-event'); ?></td>
					<td><select name="currency"
						onchange="jQuery(document).find('.price').text(this.value);">

							<option
							<?php echo $data['currency'] == 'LTL' ? "selected='selected'" : "" ?>
								value="LTL">LTL</option>
							<option
							<?php echo $data['currency'] == 'USD' ? "selected='selected'" : "" ?>
								value="USD">USD</option>
							<option
							<?php echo $data['currency'] == 'EUR' ? "selected='selected'" : "" ?>
								value="EUR">EUR</option>
					</select>
					</td>
				</tr>
				<tr>
					<td colspan="2"><strong><?php _e('Ticket groups', 'evp-event')?> </strong>
						<br />
						<table id="table-tickets" class="wp-list-table widefat">
							<tr>
								<th><strong><?php _e('Name', 'evp-event') ?> </strong></th>
								<th><strong><?php _e('Ticket price', 'evp-event') ?> </strong></th>
								<th><strong><?php _e('Ticket amount', 'evp-event') ?> </strong>
								<th><strong><?php _e('Payment types', 'evp-event') ?> </strong>
								</th>
								<th><strong><?php _e('Group discount, when register more than', 'evp-event') ?>
									</th> </strong></th>

								<th style="width: 2%"></th>
							</tr>
							<?php $i = 0;
							if(empty($data['tickets'])) {
								$data['tickets'][] = array(
										'price' => null, 'payment_types' => array('prebill' => null, 'webtopay' => null, 'guaranty' => null),
										'discount_persons' => null, 'discount_percent' => null, 'name'=> null
								);
							}

							foreach($data['tickets'] as $t):
							?>
							<tr>
								<td><input type="text" name="tickets[<?php echo $i?>][name]"
									value="<?php echo $t['name'] ?>" /></td>
								<td><input type="text" size="6" class="number required"
									name="tickets[<?php echo $i?>][price]"
									value="<?php echo $t['price'] ?>" /> <span class="price">LTL</span>
								</td>
								<td><input type="text" name="tickets[<?php echo $i?>][amount]"
									size="5" value="<?php echo @$t['amount'] ?>"
									class="number valid" /> 
								</td>	
								<td><input type="checkbox"
									name="tickets[<?php echo $i?>][payment_types][webtopay]"
									<?php echo @$t['payment_types']['webtopay'] ? "checked='checked'" : "" ?> />
									<?php _e('WebToPay', 'evp-event') ?><br /> <input
									type="checkbox"
									name="tickets[<?php echo $i?>][payment_types][prebill]"
									<?php echo @$t['payment_types']['prebill'] ? "checked='checked'" : "" ?> />
									<?php _e('PreBill', 'evp-event') ?><br /> <input
									type="checkbox"
									name="tickets[<?php echo $i?>][payment_types][guaranty]"
									<?php echo @$t['payment_types']['guaranty'] ? "checked='checked'" : "" ?> />
									<?php _e('Payment guaranty', 'evp-event') ?><br />
								</td>
								<td><input type="text" size="2" class="digits"
									name="tickets[<?php echo $i?>][discount_persons]"
									value="<?php echo $t['discount_persons'] ?>" /> <?php _e('persons', 'evp-event')?>,
									<input type="text" size="2" class="number"
									name="tickets[<?php echo $i?>][discount_percent]"
									value="<?php echo $t['discount_percent'] ?>" /> %</td>
									<?php if($i > 0): ?>
								
								<td><a href="javascript:void(0)"
									id="remove_row_<?php echo $i ?>">X</a></td>
								<?php else: ?>
								<td></td>
							</tr>
							<?php endif?>
							<?php
							$i++;
							endforeach
							?>
						</table> <input type="button" id="add-row"
						value="<?php _e('New ticket', 'evp-event') ?>"
						style="float: right; margin-top: 5px" />
					</td>
				</tr>
			</table>
		</div>
		<br />
	</div>
	<div class="stuffbox">
		<h3>
			<label for="webtopay">3. <?php _e('WebTopay.com account', 'evp-event')?>
			</label>
		</h3>
		<div class="inside">
			<table class="form-table">
				<tr>
					<td nowrap><?php _e('Project ID:', 'evp-event') ?></td>
					<td><input type="text" size="10" class="required"
						value="<?php echo $data['webtopay_project_id'] ?>"
						name="webtopay_project_id" /><br> <span class="small description"><?php _e('Your webtopay.com project ID:', 'evp-event') ?>
					</span>
					</td
				
				</tr>
				<tr>
					<td nowrap><?php _e('Project sign:', 'evp-event') ?></td>
					<td><input type="text" size="30" class="required"
						value="<?php echo $data['webtopay_project_sign'] ?>"
						name="webtopay_project_sign" /><br> <span
						class="small description"><?php  _e('Your webtopay.com project sign password:', 'evp-event') ?>
					</span>
					</td
				
				</tr>
				<tr>
					<td nowrap><?php _e('Test mode:', 'evp-event') ?></td>
					<td><input type="checkbox" size="30"
					<?php echo $data['webtopay_project_test'] == 1 ? "checked='checked'" : '' ?>
						value="1" name="webtopay_project_test" /><br>
				
				</tr>
				</tr>
			</table>
		</div>
		<br />
	</div>
	<!--
	<div class="stuffbox">
		<h3>
			4. <label for="webtopay"><?php _e('Upload ticket template', 'evp-event')?>
			</label>
		</h3>
		<div class="inside">
			<table class="form-table">
				<tr>
					<td><?php _e('Template', 'evp-event')?>:</td>
					<td><input type="file" name="ticket" /><br /> <span
						class="small description"><?php _e('You can download sample from ', 'evp-event')?><a
							target="_blank"
							href="<?php echo plugins_url('evp-event' .DS .'tmpl' . DS . 'ticket.png') ?>"><?php _e('here', 'evp-event')?>
						</a> </span> <br />
					</td>
				</tr>
				<?php if(!is_null($id)): ?>
				<tr>
					<td colspan="2"><img style="width: 600px"
						src="<?php echo plugins_url('evp-event' . DS .'tmpl' . DS . 'ticket_templates' . DS . $id . '.png') ?>" />
					</td>
				</tr>
				<?php endif ?>
			</table>
		</div>
		<br />
	</div>
	- -->

	<div class="stuffbox">
		<h3>
			<label for="webtopay">4. <?php _e('Mail settings', 'evp-event')?>
			</label>
		</h3>
		<div class="inside">
			<table class="form-table">
				<tr>
					<td><?php _e('Email From', 'evp-event')?></td>
					<td><input type="text" name="email_from"
						value="<?php echo $data['email_from'] ?>" class="required_email"
						size="30" />
					</td>
				</tr>
				<tr>
					<td><?php _e('Email From Name', 'evp-event') ?>
					
					<td><input type="text" name="email_from_name"
						value="<?php echo $data['email_from_name'] ?>" class="required"
						size="30" />
				
				</tr>
				<tr>
					<td><?php _e('Mail subjects', 'evp-event') ?></td>
					<td>
						<table class="wp-list-table widefat">
							<tr>
								<th><?php _e("Email: send ticket to customer", "evp-event")?></th>
								<td><input type="text" name="email_to_cust_sb"
									value="<?php echo $data['email_to_cust_sb'] ?>"
									class="required" size="30" /></td>
							</tr>
							<tr>
								<th><?php _e("Email: send tickets to representative person", "evp-event") ?>
								</th>
								<td><input type="text" name="email_to_rep1_sb"
									value="<?php echo $data['email_to_rep1_sb'] ?>"
									class="required" size="30" /></td>
							</tr>
							<tr>
								<th><?php _e("Email: send payment guaranty to represantive person", "evp-event") ?>
								</th>
								<td><input type="text" name="email_to_rep2_sb"
									value="<?php echo $data['email_to_rep2_sb'] ?>"
									class="required" size="30" /></td>
							</tr>
							<tr>
								<th><?php _e("Email: send bill to representative person", "evp-event")?>
								</th>
								<td><input type="text" name="email_to_rep3_sb"
									value="<?php echo $data['email_to_rep3_sb'] ?>"
									class="required" size="30" /></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td><?php _e('Use SMTP', 'evp-event') ?></td>
					<td><input type="checkbox"
					<?php echo $data['smtp']['enabled'] ? "checked='checked'" : "" ?>
						name="smtp[enabled]" onclick="disable_smtp()" /></td>
				</tr>
				<tr>
					<td></td>
					<td>
						<table id="smtp-table"
						<?php echo $data['smtp']['enabled'] ? '' : 'style="display:none"'?>
							class="wp-list-table widefat">

							<tr>
								<td><?php _e('SMTP Hostname', 'evp-event') ?></td>
								<td><input type="text" name="smtp[host]" class="required"
									value="<?php echo $data['smtp']['host'] ?>" /></td>
							</tr>
							<tr>
								<td><?php _e('SMTP Port', 'evp-event') ?></td>
								<td><input type="text" name="smtp[port]" class="digits required"
									value="<?php echo $data['smtp']['port'] ?>" /></td>
							</tr>
							<tr>
								<td><?php _e('SMTP Username', 'evp-event') ?></td>
								<td><input type="text" name="smtp[username]" class="required"
									value="<?php echo $data['smtp']['username'] ?>" /></td>
							</tr>
							<tr>
								<td><?php _e('SMTP Password', 'evp-event') ?></td>
								<td><input type="text" name="smtp[password]" class="required"
									value="<?php echo $data['smtp']['password'] ?>" /></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<br />
	</div>

	<input type="submit" class="button-primary"
		value="<?php _e("Save event", "evp-event") ?>" />
</form>
