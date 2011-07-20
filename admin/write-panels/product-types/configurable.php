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
			<div class="jigoshop_configuration">
				<p>
					<button type="button" class="remove_config button"><?php _e('Remove', 'jigoshop'); ?></button>
					<strong><?php _e('Variation:', 'jigoshop'); ?></strong>
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
							<td class="upload_image">
								<img src="<?php echo jigoshop::plugin_url().'/assets/images/placeholder.png'; ?>" width="60px" height="60px" />
								<input type="hidden" name="upload_image" class="upload_image_src" value="" /><input type="button" class="upload_image_button button" value="<?php _e('Product Image', 'jigoshop'); ?>" />
							</td>
							<td><label><?php _e('SKU:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_sku[]" /></td>
							<td><label><?php _e('Weight', 'jigoshop').' ('.get_option('jigoshop_weight_unit').'):'; ?></label><input type="text" size="5" name="configurable_weight[]" /></td>
							<td><label><?php _e('Stock Qty:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_stock[]" /></td>
							<td><label><?php _e('Price variation:', 'jigoshop'); ?></label><input type="text" size="5" name="configurable_price[]" placeholder="<?php _e('Price (e.g. 5.99, -2.99)', 'jigoshop'); ?>" /></td>
						</tr>		
					</tbody>
				</table>
			</div>
		</div>
		<p class="description"><?php _e('Add (optional) pricing/inventory for product variations. You must save your product attributes in the "Product Data" panel first for them to be available.', 'jigoshop'); ?></p>

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
							
							echo '<select name="'.sanitize_title($attribute['name']).'"><option value="">'.__('Any ', 'jigoshop').$attribute['name'].'&hellip;</option><option>'.implode('</option><option>', $options).'</option></select>\\';

						endforeach;
						
				?></p>\
				<table cellpadding="0" cellspacing="0" class="jigoshop_configurable_attributes">\
					<tbody>	\
						<tr>\
							<td class="upload_image">\
								<img src="<?php echo jigoshop::plugin_url().'/assets/images/placeholder.png'; ?>" width="60px" height="60px" />\
								<input type="hidden" name="upload_image" class="upload_image_src" value="" /><input type="button" class="upload_image_button button" value="<?php _e('Product Image', 'jigoshop'); ?>" />\
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
			
			formfield = jQuery('.upload_image_src', parent).attr('name');
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			return false;
		});

		window.send_to_cproduct = function(html) {
			imgurl = jQuery('img', html).attr('src');
			jQuery('.upload_image_src', current_field_wrapper).val(imgurl);
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
	
	//if (isset($_POST['file_path']) && $_POST['file_path']) update_post_meta( $post_id, 'file_path', $_POST['file_path'] );
	//if (isset($_POST['download_limit'])) update_post_meta( $post_id, 'download_limit', $_POST['download_limit'] );
	
	return $data;

}
add_filter('process_product_meta_configurable', 'process_product_meta_configurable', 1, 2);