<?php
/*
 * Cart shortcode
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
function get_jigoshop_cart( $atts=array() ) {
	return jigoshop::shortcode_wrapper('jigoshop_cart', $atts);
}

function get_jigoshop_review_cart($atts=array()){
	$defaults = array(
			'hide_thumbnail'=>true,
			'hide_price'=>true,
			'cart_mode'=>'review',
			'footer_mode'=>'total',
			'shipping_control'=>true);
	$attribs = array_merge($defaults, $atts);
	return jigoshop::shortcode_wrapper('jigoshop_cart',$attribs);
}

function jigoshop_cart( $atts ) {
	$atts = shortcode_atts(
		array(
			// Style options
			'css_class'=>'shop_table cart',
			
			// Options to hide certain parts of the cart body
			'hide_remove_button' => false,
			'hide_thumbnail' => false,
			'hide_name' => false,
			'hide_price' => false,
			'hide_quantity' => false,
			'hide_subtotal' => false,
			'hide_cart_collaterals'=>false,

			// Overall cart layout/mode options
			'cart_mode'=>'update',
			'footer_mode'=>'checkout',

			// Allow chaning of shipping methods when footer mode is total
			'shipping_control'=>false
		), $atts);

	$hide_remove_button = boolval($atts['hide_remove_button']) || $atts['cart_mode'] == 'review';
	$hide_thumbnail = boolval($atts['hide_thumbnail']);
	$hide_name = boolval($atts['hide_name']);
	$hide_price = boolval($atts['hide_price']);
	$hide_subtotal = boolval($atts['hide_subtotal']);
	$hide_quantity = boolval($atts['hide_quantity']);
	$hide_cart_collaterals = boolval($atts['hide_cart_collaterals']) || $atts['cart_mode'] == 'review';


	// Process Discount Codes
	if (isset($_POST['apply_coupon']) && $_POST['apply_coupon'] && jigoshop::verify_nonce('cart')) :

		$coupon_code = stripslashes(trim($_POST['coupon_code']));
		jigoshop_cart::add_discount($coupon_code);

	// Update Shipping
	elseif (isset($_POST['calc_shipping']) && $_POST['calc_shipping'] && jigoshop::verify_nonce('cart')) :

		unset($_SESSION['chosen_shipping_method_id']);
		$country 	= $_POST['calc_shipping_country'];
		$state 		= $_POST['calc_shipping_state'];

		$postcode 	= $_POST['calc_shipping_postcode'];

		if ($postcode && !jigoshop_validation::is_postcode( $postcode, $country )) :
			jigoshop::add_error( __('Please enter a valid postcode/ZIP.','jigoshop') );
			$postcode = '';
		elseif ($postcode) :
			$postcode = jigoshop_validation::format_postcode( $postcode, $country );
		endif;

		if ($country) :

			// Update customer location
			jigoshop_customer::set_location( $country, $state, $postcode );
			jigoshop_customer::set_shipping_location( $country, $state, $postcode );

			// Re-calc price
			jigoshop_cart::calculate_totals();

			jigoshop::add_message(  __('Shipping costs updated.', 'jigoshop') );

		else :

			jigoshop_customer::set_shipping_location( '', '', '' );

			jigoshop::add_message(  __('Shipping costs updated.', 'jigoshop') );

		endif;

	endif;

	$result = jigoshop_cart::check_cart_item_stock();
	if (is_wp_error($result)) :
		jigoshop::add_error( $result->get_error_message() );
	endif;

	jigoshop::show_messages();

	if (sizeof(jigoshop_cart::$cart_contents)==0) :
		echo '<p>'.__('Your cart is empty.', 'jigoshop').'</p>';
		echo '<p><a class="button" href="'.get_permalink(get_option('jigoshop_shop_page_id')).'">'.__('&larr; Return To Shop', 'jigoshop').'</a></p>';
		return;
	endif;
	?>
	<?php if ($atts['cart_mode'] == 'update'): ?>
	<form action="<?php echo jigoshop_cart::get_cart_url(); ?>" method="post">
	<?php endif; ?>

	<table class="<?php echo $atts['css_class'] ?>" cellspacing="0">

		<thead>
		<tr>
			<?php $numcols=0 ?>
			<?php if (!$hide_remove_button): ?><th class="product-remove"></th><?php $numcols++; endif; ?>
			<?php if (!$hide_thumbnail): ?><th class="product-thumbnail"></th><?php $numcols++; endif; ?>
			<?php if (!$hide_name): ?><th class="product-name"><span class="nobr"><?php _e('Product Name', 'jigoshop'); ?></span></th><?php $numcols++; endif; ?>
			<?php if (!$hide_price): ?><th class="product-price"><span class="nobr"><?php _e('Unit Price', 'jigoshop'); ?></span></th><?php $numcols++; endif; ?>
			<?php if (!$hide_quantity): ?><th class="product-quantity"><?php _e('Quantity', 'jigoshop'); ?></th><?php $numcols++; endif; ?>
			<?php if (!$hide_subtotal): ?><th class="product-subtotal"><?php _e('Price', 'jigoshop'); ?></th><?php $numcols++; endif; ?>
		</tr>
			<?php do_action( 'jigoshop_shop_table_cart_head' ); ?>
		</thead>

		<tbody>
			<?php
			if (sizeof(jigoshop_cart::$cart_contents) > 0) {
				foreach (jigoshop_cart::$cart_contents as $cart_item_key => $values) {
					$_product = $values['data'];
					if (!$_product->exists() || $values['quantity'] <= 0) continue;

					$additional_description = '';
					if ($_product instanceof jigoshop_product_variation && is_array($values['variation'])) {
						$additional_description = jigoshop_get_formatted_variation($values['variation']);
					}

					echo "<tr>";

					if (!$hide_remove_button) {
						printf('<td class="product-remove"><a href="%s" class="remove" title="%s">&times;</a></td>'
							, jigoshop_cart::get_remove_url($cart_item_key)
							, __('Remove this item.', 'jigoshop'));
					}

					if (!$hide_thumbnail) {
						$template_params = array();

						if ($atts['cart_mode'] != 'review') {
							$template = '<a href="%s">%s</a>';
							array_push($template_params, get_permalink($values['product_id']));
						} else {
							$template = '%s';
						}

						if ($values['variation_id'] && has_post_thumbnail($values['variation_id'])) {
							array_push($template_params, get_the_post_thumbnail($values['variation_id'], 'shop_tiny'));
						} else if (has_post_thumbnail($values['product_id'])) {
							array_push($template_params, get_the_post_thumbnail($values['product_id'], 'shop_tiny'));
						} else {
							$img = '<img src="' . jigoshop::plugin_url() . '/assets/images/placeholder.png" alt="Placeholder" width="' . jigoshop::get_var('shop_tiny_w') . '" height="' . jigoshop::get_var('shop_tiny_h') . '" />';
							array_push($template_params, $img);
						}
						
						echo '<td class="product-thumbnail">' . vsprintf($template, $template_params) . '</td>';
					}

					if (!$hide_name) {
						echo '<td class="product-name">';
						if ($atts['cart_mode'] == 'review') {
							printf('%s%s'
								, apply_filters('jigoshop_cart_product_title', $_product->get_title(), $_product)
								, $additional_description);
						} else {
							printf('<a href="%s">%s</a>%s'
								, get_permalink($values['product_id'])
								, apply_filters('jigoshop_cart_product_title', $_product->get_title(), $_product)
								, $additional_description);
						}
						echo '</td>';
					}

					if (!$hide_price) {
						printf('<td class="product-price">%s</td>', jigoshop_price($_product->get_price()));
					}

					if (!$hide_quantity) {
						if ($atts['cart_mode'] == 'review') {
							printf('<td class="product-quantity">%s</td>', $values['quantity']);
						} else {
							printf('<td class="product-quantity"><div class="quantity"><input name="cart[%s][qty]" value="%s" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div></td>'
								, $cart_item_key
								, $values['quantity']);
						}
					}
					
					if (!$hide_subtotal) {
						printf('<td class="product-subtotal">%s</td>', jigoshop_price($_product->get_price() * $values['quantity']));
					}
					echo "</tr>";
				}
			}
			do_action( 'jigoshop_shop_table_cart_body' );
			?>
		</tbody>

		<tfoot>
			<?php if ($atts['footer_mode'] == 'checkout'): ?>
				<tr>
					<td colspan="<?php echo $numcols; ?>" class="actions">
						<div class="coupon">
							<label for="coupon_code"><?php _e('Coupon', 'jigoshop'); ?>:</label> <input name="coupon_code" class="input-text" id="coupon_code" value="" />
							<input type="submit" class="button" name="apply_coupon" value="<?php _e('Apply Coupon', 'jigoshop'); ?>" />
						</div>
						<?php jigoshop::nonce_field('cart') ?>
						<input type="submit" class="button" name="update_cart" value="<?php _e('Update Shopping Cart', 'jigoshop'); ?>" /> <a href="<?php echo jigoshop_cart::get_checkout_url(); ?>" class="checkout-button button-alt"><?php _e('Proceed to Checkout &rarr;', 'jigoshop'); ?></a>
					</td>
				</tr>
				<?php if ( count( jigoshop_cart::$applied_coupons ) ) : ?>
					<tr>
						<td colspan="<?php echo $numcols; ?>" class="applied-coupons">
							<div>
								<span class="applied-coupons-label"><?php _e('Applied Discount Coupons: ','jigoshop'); ?></span>
								<span class="applied-coupons-values"><?php echo implode( ',', jigoshop_cart::$applied_coupons ); ?></span>
							</div>
						</td>
					</tr>
				<?php endif; ?>

			<?php elseif ($atts['footer_mode'] == 'total' && $numcols > 2):

			foreach(jigoshop_cart::get_itemized_totals() as $total): ?>
			<tr>
				<td colspan="<?php echo $numcols-1; ?>"><?php echo $total->get_title_display(false) ?></td>
				<td><?php
					if (boolval($atts['shipping_control']) && $total->title == __('Shipping', 'jigoshop') && count(jigoshop_shipping::get_available_shipping_methods()) > 0) {
						jigoshop_shipping_selector();
					} else {
						echo $total->get_amount_display(false);
					}
				?>
				</td>
			</tr>
			<?php endforeach; endif; ?>
			<?php do_action( 'jigoshop_shop_cart_foot' ); ?>
		</tfoot>
		<?php do_action( 'jigoshop_shop_table_cart' ); ?>
	</table>

	<?php if ($atts['cart_mode'] == 'update'): ?>
	</form>
	<?php endif; ?>

	<?php if (!$hide_cart_collaterals): ?>
	<div class="cart-collaterals">
		<?php do_action('cart-collaterals'); ?>
		<div class="cart_totals">
		<?php
		// Hide totals if customer has set location and there are no methods going there
		$available_methods = jigoshop_shipping::get_available_shipping_methods();
		if ($available_methods || !jigoshop_customer::get_shipping_country() || !jigoshop_shipping::is_enabled() ) :
			?>
			<h2><?php _e('Cart Totals', 'jigoshop'); ?></h2>
			<table cellspacing="0" cellpadding="0">
				<tbody>
					<?php
						$totals = jigoshop_cart::get_itemized_totals();
						/* @var $total jigoshop_total */
						foreach($totals as $total){
							printf('<tr><th>%s</th><td>%s</td></tr>', $total->get_title_display(), $total->get_amount_display());
						}
					?>
				</tbody>
			</table>
			<?php
			else :
				echo '<p>'.__('Sorry, it seems that there are no available shipping methods to your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'jigoshop').'</p>';
			endif;
		?>
		</div>
		<?php jigoshop_shipping_calculator(); ?>
	</div>
	<?php endif;
}