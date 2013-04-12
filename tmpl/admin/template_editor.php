<h2><?php _e("Event template editor", "evp-event")?></h2>
<form method="POST" id="wpe_template_form" class="form-table">
	<table>
		<tr>
			<th class="row-title">
				<?php _e("Choose template", "evp-event") ?>:
			</th>
			<td>		
				<select name="template" onchange="change()">
					<option value="mail_to_customer.html" <?php echo @$_POST['template'] == "mail_to_customer.html" ? "selected='selected'" : "" ?> ><?php _e("Email: send ticket to customer", "evp-event")?></option>
					<option value="mail_to_representative1.html" <?php echo @$_POST['template'] == "mail_to_representative1.html" ? "selected='selected'" : "" ?>><?php _e("Email: send tickets to representative person", "evp-event") ?></option>
					<option value="mail_to_representative2.html" <?php echo @$_POST['template'] == "mail_to_representative2.html" ? "selected='selected'" : "" ?>><?php _e("Email: send payment guaranty to represantive person", "evp-event") ?></option>
					<option value="mail_to_representative3.html" <?php echo @$_POST['template'] == "mail_to_representative3.html" ? "selected='selected'" : "" ?>><?php _e("Email: send bill to representative person", "evp-event")?></option>
					<option value="invoice_advanced.html" <?php echo @$_POST['template'] == "invoice_advanced.html" ? "selected='selected'" : "" ?>><?php _e("Invoice: advanced", "evp-event")?></option>
					<option value="payment_guaranty.html" <?php echo @$_POST['template'] == "payment_guaranty.html" ? "selected='selected'" : "" ?>><?php _e("Other: payment guaranty", "evp-event")?></option>
					<option value="ticket.html" <?php echo @$_POST['template'] == "ticket.html" ? "selected='selected'" : "" ?>><?php _e("Other: ticket template", "evp-event")?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th class="row-title"><?php _e('Template body', 'evp-event') ?>:</th>
			<td>
				<?php wp_editor($html, 'content', array(
					
					'textarea_rows' => 30,
					'tinymce' => array(
		    				"wpautop" => false,
							"theme" => "advanced",
		        			"mode" => "textareas",
		        			"plugins" => "fullpage",
		        			"theme_advanced_buttons3_add" => "fullpage"
					))) ?>
				<input type="submit" name="submit_button" class="button-primary" style="float:right; margin: 5px" value="<?php _e("Save changes", "evp-event") ?>" />
			</td>
		</tr>
	</table>
</form>


<script>
	function change() {
		document.getElementById('wpe_template_form').submit();
	}
</script>

<style>
	#wp-content-wrap {
		width: 800px;
	}
</style>