<?php if(!defined('WPE')) die(); ?>
<form name="form" id="my_form" method="POST" action="<?php echo add_query_arg( 'action', 'confirm' ); ?>">
	<div class="error-notification">
		<ul class="error-notification">
		<?php if(isset($_SESSION['FORM']['ERROR']) && !empty($_SESSION['FORM']['ERROR'])): ?>
			<?php foreach($_SESSION['FORM']['ERROR'] as $error): ?>
				<li><?php echo $error ?></li>
			<?php endforeach ?>
		<? endif ?>
		</ul>
	</div>
	<span style="float:right; font-size:11px"><?php  _e('Required fields', 'evp-event')?><img src="<?php echo plugins_url() ?>/evp-event/tmpl/img/required_star.png" /></span>
	<h2 class="section"><?php _e("Choose ticket price", "evp-event") ?></h2>
	<table>
		<?php foreach($event_cfg['tickets'] as $i => $t): 
			  if($used[$i] >= $event_cfg['tickets'][$i]['amount'] ) 
				$u = true;
			  else
			  	$u = false;
		?>
		<tr>
			<td><input <?php echo $u ? 'disabled="disabled"' : '' ?> type="radio" name="ticket_price" <?php echo @$_SESSION['FORM']['TICKET_PRICE'] == $i && !$u ?  'checked="checked"' : "" ?> value="<?php echo $i ?>"/><?php echo $t['price'] ?> <?php echo $event_cfg['currency'] ?></td>
			<td><strong><?php echo $t['name'] ?></strong><span style='float:right'><?php echo $u ? __('*no tickets left', 'evp-event') : '' ?></span></td>
		</tr>	
		<?php endforeach?>
	</table>
	<br />
	<br />
	<h2 class="section"><?php _e("Enter participants", "evp-event") ?></h2>
	<table id="table_participants" style="width:100%">
		<tr>
			<th><?php _e("Name Surname", "evp-event") ?></th>
			<th><?php _e("Email", "evp-event") ?></th>
			<th><?php _e("Phone number", "evp-event") ?></th>
			<th></th>
		</tr>
		<?php		
		if(isset($_SESSION['FORM']['USERS']) && !empty($_SESSION['FORM']['USERS'])):
			$i = 1;
			foreach($_SESSION['FORM']['USERS'] as $user): 
				$id = $i++;
		?>
				<tr name="entry">
					<td valign="top"><input type="text" name="users[<?php echo $id ?>][name]" class="required" value="<?php echo esc_attr($user['name']) ?>"/></td>
					<td valign="top"><input type="text" name="users[<?php echo $id ?>][email]"  class="required email" value="<?php echo esc_attr($user['email']) ?>"/></td>
					<td valign="top"><input type="text" name="users[<?php echo $id ?>][phone]"  class="" value="<?php echo esc_attr($user['phone']) ?>"/></td>
					<?php if($id > 1): ?>
					<td><a href="javascript:void(0)" id="remove_row_<?php echo $id ?>">X</a></td>
					<?php else: ?>
					<td>&nbsp;</td>
					<?php endif ?>
				</tr>
			<?php endforeach ?>
		<?php else: ?>
			<tr name="entry">
				<td valign="top" style="width:32%"><input type="text" name="users[1][name]" class="required" /></td>
				<td valign="top" style="width:32%"><input type="text" name="users[1][email]" class="required email"/></td>
				<td valign="top" style="width:32%"><input type="text" name="users[1][phone]" class="" /></td>
				<td style="width:4%">&nbsp;</td>
			</tr>
		<?php endif ?>
		</table>
		<input id="button_add" style="float:right; margin-right: 47px; margin-top: 10px" type="button" value="<?php _e("Add more", "evp-event") ?>"  />
		
		<br /><br />
		<input type="checkbox" name="is_company" id="is_company" <?php echo !empty($_SESSION['FORM']['COMPANY']) ? 'checked="checked"' : '' ?>/><?php _e("Need invoice", "evp-event") ?>
		
		<input type="checkbox" name="use_discount_code" id="use_discount_code" style="margin-left: 20px" /><?php _e("Use discount code", "evp-event") ?>
				<br /><br />
	
		<div id="form_company" <?php echo !empty($_SESSION['FORM']['COMPANY']) ? 'style="display:block"' : '' ?>>
			<h2 class="section"><?php _e("Enter company information", "evp-event") ?></h2>
			<table cellpadding="3">
				<tr>
					<td align="right"><?php _e("Company name", "evp-event") ?></td>
					<td><input type="text" name="company[name]" class="required" size="30" value="<?php echo esc_attr(@$_SESSION['FORM']['COMPANY']['name']) ?>" /></td>
				</tr>
                <tr>
                    <td align="right"><?php _e("Company address", "evp-event") ?></td>
                    <td><input type="text" name="company[address]" class="required" size="30" value="<?php echo esc_attr(@$_SESSION['FORM']['COMPANY']['address']) ?>" /></td>
                </tr>
				<tr>
					<td align="right"><?php _e("Company code", "evp-event") ?></td>
					<td><input type="text" name="company[code]" class="required" size="30" value="<?php echo esc_attr(@$_SESSION['FORM']['COMPANY']['code']) ?>"  /></td>
				</tr>
				<tr>
					<td align="right"><?php _e("Company VAT code", "evp-event") ?></td>
					<td><input type="text" name="company[pvm_code]" class="required"  size="30" value="<?php echo esc_attr(@$_SESSION['FORM']['COMPANY']['pvm_code']) ?>"  /></td>		
				</tr>
				<tr>
					<td align="right"><?php _e("Company contact person", "evp-event") ?></td>
					<td><input type="text" name="company[person_name]" class="required" size="30" value="<?php echo esc_attr(@$_SESSION['FORM']['COMPANY']['person_name']) ?>"  /></td>		
				</tr>
				<tr>
					<td align="right"><?php _e("Company contact phone", "evp-event") ?></td>
					<td><input type="text" name="company[person_phone]" class="required" size="30" value="<?php echo esc_attr(@$_SESSION['FORM']['COMPANY']['person_phone']) ?>"  /></td>		
				</tr>
				<tr>
					<td align="right"><?php _e("Company fax", "evp-event") ?></td>
					<td><input type="text" name="company[fax]" class="required" size="30" value="<?php echo esc_attr(@$_SESSION['FORM']['COMPANY']['fax']) ?>"  /></td>		
				</tr>
				<tr>
					<td align="right"><?php _e("Company contact email", "evp-event") ?></td>
					<td><input type="text" name="company[person_email]" class="required email" size="30" value="<?php echo esc_attr(@$_SESSION['FORM']['COMPANY']['person_email']) ?>"  /></td>		
				</tr>
				<tr>
			</table>
		<br />
		</div>
		
		<div style="display: none" id="div-discount">
	        
	        <h2 class="section"><?php _e("Discount code", "evp-event") ?></h2>
	        
	        <?php _e("Enter discount code", "evp-event") ?>:
	
	        <input type="text" name="discount_code" value="<?php echo @$_POST['discount_code'] ?>"  />
	        <br /><br />
	    </div>
	
	    	<h2 class="section"><?php _e("Choose payment type", "evp-event") ?></h2>  
			<table>
			
				<tr id="pay_webtopay">
					<td><input type="radio" name="payment_type" <?php echo @$_SESSION['FORM']['payment_type'] == 'webtopay' ? "checked='checked'" : '' ?> value="webtopay" /></td>
					<td><?php _e("Webtopay", 'evp-event') ?></td>
					<td><?php  _e('Webtopay description', 'evp-event') ?></td>
				</tr>
				<tr id="pay_prebill">
					
					<td><input type="radio" name="payment_type" <?php echo @$_SESSION['FORM']['payment_type'] == 'prebill' ? "checked='checked'" : '' ?> value="prebill" /></td>
					<td><?php _e("Prebill", 'evp-event') ?></td>
					<td><?php  _e('Prebill description', 'evp-event') ?></td>
				</tr>
			
			
				<tr id="pay_guaranty">
					<td><input type="radio" name="payment_type" value="guaranty" <?php echo @$_SESSION['FORM']['payment_type'] == 'guaranty' ? "checked='checked'" : '' ?> /></td>
					<td><?php _e("Payment guaranty", 'evp-event') ?></td>
					<td><?php  _e('Payment guaranty description', 'evp-event') ?></td>
				</tr>

			</table>
			<span style="font-style:italic; font-size:12px" id='pay-info'>*<?php _e('If you would to use prebill or payment guaranty option, you must enter company information by click need invoice checkbox', 'evp-event')?></span>
			<br />
		<input id="button_submit" type="submit" value="<?php _e("Confirm order", "evp-event") ?>" />

</form>
<script type="text/javascript">

	var event_data = <?php echo json_encode($event_cfg['tickets'], JSON_FORCE_OBJECT) ?>;

	jQuery(document).ready(function($) {
		
		<?php if(empty($_SESSION['FORM']['USERS'])): ?>
			$.rows_count = 2;
		<?php else: ?>
			$.rows_count = <?php echo count($_SESSION['FORM']['USERS']) + 1 ?>;
		<?php endif ?>

		
		jQuery.extend(jQuery.validator.messages, {
    			required: "* <?php _e("Required", "evp-event") ?>",
				email: "* <?php _e("Invalid email", "evp-event") ?>"
		});

		$('#my_form').validate();
		
		$("#button_add").click(function() {
			id  = $.rows_count++
			var insert_point = $("#table_participants tr:last");
			$('<tr>'
				+ '<td valign="top"><input type="text" name="users['+id+'][name]" class="required" /></td>'
				+ '<td valign="top"><input type="text" name="users['+id+'][email]" class="required email"/></td>'
				+ '<td valign="top"><input type="text" name="users['+id+'][phone]" class=""/></td>'
				+ '<td valign="middle"><a href="javascript:void(0)" id="remove_row_'+id+'">X</a></td>'
			  +'</tr>').insertAfter(insert_point);

			$('a[id=remove_row_'+id+']').click(function() {
				$(this).parent().parent().remove();
			});
		});

		$("#use_discount_code").change(function() {
			if($(this).is(':checked')) {
				$("#div-discount").show();
			}
			else {
				$("#div-discount").hide();
			}
		});

		$("#is_company").change(function() {
			if($(this).is(':checked')) {
				$("#form_company").show();
			}
			else {
				$("#form_company").hide();
			}
		});
		
		$("input[name=ticket_price]").add('#is_company').change(function() {
			
			ticket_price = $('input:checked[name=ticket_price]').val();
			
			types = event_data[ticket_price].payment_types;

			if(types == undefined) {
				types = {
						webtopay : false,
						guaranty : false,
						prebill : false
				}
			}

			if(types.guaranty) {
				$('#pay_guaranty').show();
			}
			else {
				$('#pay_guaranty').hide();	
			}

			
			if(types.webtopay) {
				$('#pay_webtopay').show();
			}
			else {
				$('#pay_webtopay').hide();
			}

			if(types.prebill) {
				$('#pay_prebill').show();
			}
			else {
				$('#pay_prebill').hide();
			}

			if(!types.guaranty && !types.prebill) {
				$('#pay-info').hide();
			}
			else {
				$('#pay-info').show();
			}
			
						
			if($('#is_company').is(':checked')) {
				unblur($('#pay_guaranty'));
				unblur($('#pay_prebill'));
			}
			else {
				blur($('#pay_prebill'))
				blur($('#pay_guaranty'));
			}
			
			if(!$("input:visible[name=payment_type]").is(':checked')) {
				$("input:visible[name=payment_type]").first().attr('checked', 'checked');
			}
			select_first_checked_and_visible();

			if(!$("input[name=ticket_price][disabled!=disabled]").is(':checked')) {
				$("input[name=ticket_price][disabled!=disabled]").first().attr('checked', 'checked');
			}

			
		});
		
		$("#is_company").change();
				
		function select_first_checked_and_visible() {
			if(!$("input[name=payment_type][disabled!=disabled]").is(':checked')) {
				$("input[name=payment_type][disabled!=disabled]").first().attr('checked', 'checked');
			}
		}
		
		select_first_checked_and_visible()
		
		$('a[id^="remove_row_"]').click(function() {
			$(this).parent().parent().remove();
		});

		function blur(el) {
			el.find('input').attr('disabled', 'disabled');
			el.find('input').attr('disabled', 'disabled');
			el.css('color', 'transparent').css('color', 'grey');
		}

		function unblur(el) {
			el.find('input').removeAttr('disable');
			el.find('input').removeAttr('disabled');
			el.css('color', '').css('text-shadow', '');
		}

	});
</script>
<style>
	#form_company {
		display:none;	
	}
	#button_submit {
		float: right;
	}
	label.error {
		font-size: 0.8em; 
		float: none; 
		color: red; 
		padding-left: .5em; 
		margin-right:80px;
		display:none;
	}
	.sub {
		font-size: 1em;
	}
	p { 
		clear: both; 
	}
	.error-notification {
		color: red;
	}
	
	input {
		margin-right: 2px;
	}

    BODY {
        counter-reset: section;
    }
    H2.section:before  {
        content: counter(section) ". ";
        counter-increment: section;
    }
    H2.section {
        sectioncounter-reset: section;
    }

</style>

