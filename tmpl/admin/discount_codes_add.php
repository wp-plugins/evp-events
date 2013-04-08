<?php if(isset($_GET['success'])): ?>
<div id="message" class="updated below-h2"><?= _e("Discount code successfully saved", "evp-event"); ?></div>
<? endif ?>
<form method="POST" action="<?php echo add_query_arg( 'noheader', true,  $_SERVER["REQUEST_URI"])?>">
    <h3><?php _e("Add new discount code", "evp-event") ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e("Code", "evp-event") ?></th>
            <td><input type="text" name="code" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Discount %", "evp-event")?></th>
            <td><input type="text" name="discount" /></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Use amount", "evp-event")?></th>
            <td><input type="text" name="amount" /></td>
        </tr>
    </table>
    <input type="submit" class="button-primary" value="<?php _e("Save","evp-event") ?>" />
</form>