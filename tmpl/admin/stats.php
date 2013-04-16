<h2><?php _e("Statistics", "evp-event")?></h2>
<table  class="widefat" style="width: 400px">
	<tr>
		<td><?php _e("Orders total", "evp-event") ?>: </td>
		<td><input type="text" readonly="readonly" value="<?= $orders_count ?>" /></td>
	</tr>
	<tr>
		<td><?php _e("Unconfirmed payment guaranties", "evp-event") ?> </td>
		<td><input type="text" readonly="readonly" value="<?= $unconfirmed_payment_guaranties ?>" /></td>
	</tr>
		<tr>
		<td><?php _e("Unpaid pre bills", "evp-event") ?></td>
		<td><input type="text" readonly="readonly" value="<?= $unpaid_prebill ?>" /></td>
	</tr>
	<tr>
		<td><?php _e("Participants registered", "evp-event") ?></td>
		<td><input type="text" readonly="readonly" value="<?= $tickets_count ?>" /></td>
	</tr>
	<tr>
		<td><?php _e("Amount gained from prebills", "evp-event") ?></td>
		<td><input type="text" readonly="readonly" value="<?= money_format('%i', $prebill) ?>" /></td>
	</tr>
	<tr>
		<td><?php _e("Amount gained from webtopay.com", "evp-event") ?></td>
		<td><input type="text" readonly="readonly" value="<?= money_format('%i', $mokejimai_gateway ) ?>" /></td>
	</tr>
	<tr>
		<td><?php _e("Amount gained from payment guaranty", "evp-event") ?></td>
		<td><input type="text" readonly="readonly" value="<?= money_format('%i', $payment_guaranty)  ?>" /></td>
	</tr>
	<tr>
		<td><?php _e("Total amount", "evp-event") ?></td>
		<td><input type="text" readonly="readonly" value="<?= money_format('%i', $total_amount ) ?>" /> </td>
	</tr>
</table>