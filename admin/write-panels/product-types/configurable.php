<?php
/**
 * Configurable Product Type
 * 
 * Functions specific to configurable products (for the write panels)
 *
 * @author 		Jigowatt
 * @category 	Admin Write Panel Product Types
 * @package 	JigoShop
 */
 
/**
 * Product Options
 * 
 * Product Options for the configurable product type
 *
 * @since 		1.0
 */
function configurable_product_type_options() {
	global $post;
	
	$attributes = maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) );
	if (!isset($attributes)) $attributes = array();
	?>
	<div id="configurable_product_options" class="panel">
		
		<div class="jigoshop_configurations">
			<?php
			$args = array(
				'post_type'	=> 'product_variation',
				'post_status' => array('private', 'publish'),
				'numberposts' => -1,
				'orderby' => 'title',
				'order' => 'asc',
				'post_parent' => $post->ID
			);
			$variations = get_posts($args);
			if ($variations) foreach ($variations as $variation) :
			
				$variation_data = get_post_custom( $variation->ID );
				if (isset($variation_data['_thumbnail_id'][0])) :
					$image = wp_get_attachment_url( $variation_data['_thumbnail_id'][0] );
				else :
					$image = jigoshop::plugin_url().'/assets/images/placeholder.png';
				endif;
				?>
				<div class="jigoshop_configuration">
					<p>
						<button type="button" class="remove_config button"><?php _e('Remove', 'jigoshop'); ?></button>
						<strong>#<?php echo $variation->ID; ?> &mdash; <?php _e('Variation:', 'jigoshop'); ?></strong>
						<?php
							foreach ($attributes as $attribute) :
								
								if ( $attribute['variation']!=='yes' ) continue;
								
								$options = $attribute['value'];
								
								if (!is_array($options)) $options = explode(',', $options);
								
								echo '<select name="'.sanitize_title($attribute['name']).'"><option value="">'.__('Any ', 'jigoshop').$attribute['name'].'&hellip;</option><option>'.implode('</option><option>', $options).'</option></select>';
	
							endforeach;
						?>
					</p>
					<table cellpadding="0" cellspacing="0" class="jigoshop_configurable_attributes">
						<tbody>	
							<tr>
								<td class="upload_image"><img src="<?php echo $image ?>" width="60px" height="60px" /><input type="hidden" name="upload_image_id[]" class="upload_image_id" value="<?php if (isset($variation_data['_thumbnail_id'][0])) echo $variation_data['_thumbnail_id'][0]; ?>" /><input type="button" class="upload_image_button button" value="<?php _e('Product Image', 'jigoshop'); ?>" /></td>
								<td><label><?php _e('SKU:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_sku[]" value="<?php if (isset($variation_data['SKU'][0])) echo $variation_data['SKU'][0]; ?>" /></td>
								<td><label><?php _e('Weight', 'jigoshop').' ('.get_option('jigoshop_weight_unit').'):'; ?></label><input type="text" size="5" name="configurable_weight[]" value="<?php if (isset($variation_data['weight'][0])) echo $variation_data['weight'][0]; ?>" /></td>
								<td><label><?php _e('Stock Qty:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_stock[]" value="<?php if (isset($variation_data['stock'][0])) echo $variation_data['stock'][0]; ?>" /></td>
								<td><label><?php _e('Price variation:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_price[]" placeholder="<?php _e('e.g. 5.99, -2.99', 'jigoshop'); ?>" value="<?php if (isset($variation_data['price'][0])) echo $variation_data['price'][0]; ?>" /></td>
								<td><label><?php _e('Enabled', 'jigoshop'); ?></label><input type="checkbox" class="checkbox" name="configurable_enabled[]" <?php checked($variation->post_status, 'publish'); ?> /></td>
							</tr>		
						</tbody>
					</table>
				</div>
			<?php endforeach; ?>
		</div>
		<p class="description"><?php _e('Add (optional) pricing/inventory for product variations. You must save your product attributes in the "Product Data" panel to make them available for selection.', 'jigoshop'); ?></p>

		<button type="button" class="button button-primary add_configuration"><?php _e('Add Configuration', 'jigoshop'); ?></button>
		
		<div class="clear"></div>
	</div>
	<?php
}
add_action('jigoshop_product_type_options_box', 'configurable_product_type_options');

 
/**
 * Product Type Javascript
 * 
 * Javascript for the configurable product type
 *
 * @since 		1.0
 */
function configurable_product_write_panel_js() {
	global $post;
	
	$attributes = maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) );
	if (!isset($attributes)) $attributes = array();
	?>
	jQuery(function(){
		
		jQuery('button.add_configuration').live('click', function(){
		
			jQuery('.jigoshop_configurations').append('<div class="jigoshop_configuration">\
				<p>\
					<button type="button" class="remove_config button"><?php _e('Remove', 'jigoshop'); ?></button>\
					<strong><?php _e('Variation:', 'jigoshop'); ?></strong><?php
						foreach ($attributes as $attribute) :
							
							if ( $attribute['variation']!=='yes' ) continue;
							
							$options = $attribute['value'];
							
							if (!is_array($options)) $options = explode(',', $options);
							
							echo '<select name="'.sanitize_title($attribute['name']).'[]"><option value="">'.__('Any ', 'jigoshop').$attribute['name'].'&hellip;</option><option>'.implode('</option><option>', $options).'</option></select>\\';

						endforeach;
						
				?></p>\
				<table cellpadding="0" cellspacing="0" class="jigoshop_configurable_attributes">\
					<tbody>	\
						<tr>\
							<td class="upload_image">\
								<img src="<?php echo jigoshop::plugin_url().'/assets/images/placeholder.png'; ?>" width="60px" height="60px" />\
								<input type="hidden" name="upload_image" class="upload_image_id" value="" /><input type="button" class="upload_image_button button" value="<?php _e('Product Image', 'jigoshop'); ?>" />\
							</td>\
							<td><label><?php _e('SKU:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_sku[]" /></td>\
							<td><label><?php _e('Weight', 'jigoshop').' ('.get_option('jigoshop_weight_unit').'):'; ?></label><input type="text" size="5" name="configurable_weight[]" /></td>\
							<td><label><?php _e('Stock Qty:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_stock[]" /></td>\
							<td><label><?php _e('Price variation:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_price[]" placeholder="<?php _e('Price (e.g. 5.99, -2.99)', 'jigoshop'); ?>" /></td>\
						</tr>\
					</tbody>\
				</table>\
			</div>');
			
			return false;
		
		});
		
		jQuery('button.remove_config').live('click', function(){
			var answer = confirm('<?php _e('Are you sure you want to remove this variation?', 'jigoshop'); ?>');
			if (answer){
				jQuery(this).parent().parent().remove();
			}
			return false;
		});
		
		var current_field_wrapper;
		
		window.send_to_editor_default = window.send_to_editor;

		jQuery('.upload_image_button').live('click', function(){
			parent = jQuery(this).parent();
			
			current_field_wrapper = parent;
			
			window.send_to_editor = window.send_to_cproduct;
			
			formfield = jQuery('.upload_image_id', parent).attr('name');
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			return false;
		});

		window.send_to_cproduct = function(html) {
			
			imgurl = jQuery('img', html).attr('src');
			imgclass = jQuery('img', html).attr('class');
			imgid = parseInt(imgclass.replace(/\D/g, ''), 10);
			
			jQuery('.upload_image_id', current_field_wrapper).val(imgid);

			jQuery('img', current_field_wrapper).attr('src', imgurl);
			tb_remove();
			window.send_to_editor = window.send_to_editor_default;
		}

	});
	<?php
	
}
add_action('product_write_panel_js', 'configurable_product_write_panel_js');

/**
 * Product Type selector
 * 
 * Adds this product type to the product type selector in the product options meta box
 *
 * @since 		1.0
 *
 * @param 		string $product_type Passed the current product type so that if it keeps its selected state
 */
function configurable_product_type_selector( $product_type ) {
	
	echo '<option value="configurable" '; if ($product_type=='configurable') echo 'selected="selected"'; echo '>'.__('Configurable','jigoshop').'</option>';

}
add_action('product_type_selector', 'configurable_product_type_selector');

/**
 * Process meta
 * 
 * Processes this product types options when a post is saved
 *
 * @since 		1.0
 *
 * @param 		array $data The $data being saved
 * @param 		int $post_id The post id of the post being saved
 */
function process_product_meta_configurable( $data, $post_id ) {
	
	if (isset($_POST['configurable_sku'])) :
		
		$configurable_post_id 	= $_POST['configurable_post_id'];
		$configurable_sku 		= $_POST['configurable_sku'];
		$configurable_weight	= $_POST['configurable_weight'];
		$configurable_stock 	= $_POST['configurable_stock'];
		$configurable_price 	= $_POST['configurable_price'];
		$upload_image_id		= $_POST['upload_image_id'];
		if (isset($_POST['configurable_enabled'])) $configurable_enabled = $_POST['configurable_enabled'];
		
		$attributes = maybe_unserialize( get_post_meta($post_id, 'product_attributes', true) );
		if (!isset($attributes)) $attributes = array();

		for ($i=0; $i<sizeof($configurable_sku); $i++) :
			
			$variation_id = $configurable_post_id[$i];

			// Enabled or disabled
			if (isset($configurable_enabled[$i])) $post_status = 'publish'; else $post_status = 'private';
			
			// Update or Add post
			if (!$variation_id) :
				
				$variation = array(
					'post_title' => 'Product #' . $post_id . ' Variation',
					'post_content' => '',
					'post_status' => $post_status,
					'post_author' => get_current_user_id(),
					'post_parent' => $post_id,
					'post_type' => 'product_variation'
				);
				$variation_id = wp_insert_post( $variation );
			
			else :
			
				$variation = array();
				$variation['ID'] = $variation_id;
				$variation['post_status'] = $post_status;
				wp_update_post( $variation );
			
			endif;
			
			// Update post meta
			update_post_meta( $variation_id, 'SKU', $configurable_sku[$i] );
			update_post_meta( $variation_id, 'price', $configurable_price[$i] );
			update_post_meta( $variation_id, 'weight', $configurable_weight[$i] );
			update_post_meta( $variation_id, 'stock', $configurable_stock[$i] );
			
			// Update taxonomies
			foreach ($attributes as $attribute) :
							
				if ( $attribute['variation']!=='yes' ) continue;
				
				$value = $_POST[ sanitize_title($attribute['name']) ][$i];
				
				if ($value) update_post_meta( $variation_id, 'tax_' . sanitize_title($attribute['name']), $value );

			endforeach;
			
			// Update featured post image
			update_post_meta( $variation_id, "_thumbnail_id", $upload_image_id[$i] );
		 	
		 endfor; 
	endif;
	
	return $data;

}
add_filter('process_product_meta_configurable', 'process_product_meta_configurable', 1, 2);
