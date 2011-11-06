<?php
/**
 * Review order form template
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
	if (!defined('JIGOSHOP_CHECKOUT')) define('JIGOSHOP_CHECKOUT', true);
	
	if (!defined('ABSPATH')) :
		define('DOING_AJAX', true);
		$root = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
		require_once( $root.'/wp-load.php' );
	endif;
	
	if (sizeof(jigoshop_cart::$cart_contents)==0) :
		echo '<p class="error">'.__('Sorry, your session has expired.', 'jigoshop').' <a href="'.home_url().'">'.__('Return to homepage &rarr;', 'jigoshop').'</a></p>';
		exit;
	endif;
	
	if (isset($_POST['shipping_method'])) $_SESSION['chosen_shipping_method_id'] = $_POST['shipping_method'];
	
	if (isset($_POST['country'])) jigoshop_customer::set_country( $_POST['country'] );
	if (isset($_POST['state'])) jigoshop_customer::set_state( $_POST['state'] );
	if (isset($_POST['postcode'])) jigoshop_customer::set_postcode( $_POST['postcode'] );
	
	if (isset($_POST['s_country'])) jigoshop_customer::set_shipping_country( $_POST['s_country'] );
	if (isset($_POST['s_state'])) jigoshop_customer::set_shipping_state( $_POST['s_state'] );
	if (isset($_POST['s_postcode'])) jigoshop_customer::set_shipping_postcode( $_POST['s_postcode'] );
	
	jigoshop_cart::calculate_totals();
?>
<div id="order_review">

	<?php echo get_jigoshop_review_cart(); ?>
	
	<div id="payment">
		<?php if (jigoshop_cart::needs_payment()) : ?>
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
						<label for="payment_method_<?php echo $gateway->id; ?>"><?php echo $gateway->title; ?> <?php echo apply_filters('gateway_icon', $gateway->icon(), $gateway->id); ?></label> 
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
				
					if ( !jigoshop_customer::get_country() ) :
						echo '<p>'.__('Please fill in your details above to see available payment methods.', 'jigoshop').'</p>';
					else :
						echo '<p>'.__('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'jigoshop').'</p>';
					endif;
					
				endif;
			?>
		</ul>
		<?php endif; ?>

		<div class="form-row">
		
			<noscript><?php _e('Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'jigoshop'); ?><br/><input type="submit" class="button-alt" name="update_totals" value="<?php _e('Update totals', 'jigoshop'); ?>" /></noscript>
		
			<?php jigoshop::nonce_field('process_checkout')?>
			<input type="submit" class="button-alt" name="place_order" id="place_order" value="<?php _e('Place order', 'jigoshop'); ?>" />
			
			<?php do_action( 'jigoshop_review_order_before_submit' ); ?>
			
			<?php if (get_option('jigoshop_terms_page_id')>0) : ?>
			<p class="form-row terms">
				<label for="terms" class="checkbox"><?php _e('I accept the', 'jigoshop'); ?> <a href="<?php echo get_permalink(get_option('jigoshop_terms_page_id')); ?>" target="_blank"><?php _e('terms &amp; conditions', 'jigoshop'); ?></a></label>
				<input type="checkbox" class="input-checkbox" name="terms" <?php if (isset($_POST['terms'])) echo 'checked="checked"'; ?> id="terms" />
			</p>
			<?php endif; ?>
			
			<?php do_action( 'jigoshop_review_order_after_submit' ); ?>
			
		</div>

	</div>
	
</div>