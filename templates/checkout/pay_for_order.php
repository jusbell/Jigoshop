<?php
/**
 * Pay for order form template
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Checkout
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
?>

<?php
/* @var $order jigoshop_order */
global $order; 
?>
<form id="order_review" method="post">

	<?php echo get_jigoshop_cart(); ?>

	<table class="shop_table">
		<thead>
			<tr>
				<th><?php _e('Product', 'jigoshop'); ?></th>
				<th><?php _e('Qty', 'jigoshop'); ?></th>
				<th><?php _e('Totals', 'jigoshop'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<?php foreach($order->get_itemized_totals() as $total): ?>
			<tr>
				<td colspan="2"><?php echo $total->title ?></td>
				<td><?php echo $total->get_display(); ?> ?></td>
			</tr>
			<?php endforeach; ?>
		</tfoot>
		<tbody>
			<?php
			if (sizeof($order->items)>0) : 
				foreach ($order->items as $item) :
					echo '
						<tr>
							<td>'.$item['name'].'</td>
							<td>'.$item['qty'].'</td>
							<td>'.jigoshop_price( $item['cost']*$item['qty'] ).'</td>
						</tr>';
				endforeach; 
			endif;
			?>
		</tbody>
	</table>
	
	<div id="payment">
		<?php if ($order->order_total > 0) : ?>
		<ul class="payment_methods methods">
			<?php 
				$available_gateways = jigoshop_payment_gateways::get_available_payment_gateways();
				if ($available_gateways) : 
					// Chosen Method
					if (sizeof($available_gateways)) current($available_gateways)->set_current();
					foreach ($available_gateways as $gateway ) :
						?>
						<li>
							<input type="radio" id="payment_method_<?php echo $gateway->id; ?>" class="input-radio" name="payment_method" value="<?php echo $gateway->id; ?>" <?php if ($gateway->chosen) echo 'checked="checked"'; ?> />
							<label for="payment_method_<?php echo $gateway->id; ?>"><?php echo $gateway->title; ?> <?php echo $gateway->icon(); ?></label> 
							<?php
								if ($gateway->has_fields || $gateway->description) : 
									echo '<div class="payment_box payment_method_'.$gateway->id.'" style="display:none;">';
									$gateway->payment_fields();
									echo '</div>';
								endif;
							?>
						</li>
						<?php
					endforeach;
				else :
				
					echo '<p>'.__('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'jigoshop').'</p>';
					
				endif;
			?>
		</ul>
		<?php endif; ?>

		<div class="form-row">
			<?php jigoshop::nonce_field('pay')?>
			<input type="submit" class="button-alt" name="pay" id="place_order" value="<?php _e('Pay for order', 'jigoshop'); ?>" />

		</div>

	</div>
	
</form>