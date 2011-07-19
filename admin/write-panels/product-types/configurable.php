<?php

/**
 * Product Options
 * 
 * Add an options panel for this product type
 **/
function configurable_product_type_options() {
	global $post;
	?>
	<div id="configurable_product_options" class="panel">
		
		<div class="jigoshop_configurations">
			<div class="jigoshop_configuration">
				<p>
					<button type="button" class="remove_config button"><?php _e('Remove', 'jigoshop'); ?></button>
					<strong><?php _e('Variation:', 'jigoshop'); ?></strong>
					<?php
						$attributes = maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) );
						if (isset($attributes) && sizeof($attributes)>0) foreach ($attributes as $attribute) :
							
							if ( $attribute['variation']!=='yes' ) continue;
							
							$options = $attribute['value'];
							
							if (!is_array($options)) $options = explode(',', $options);
							
							echo '<select name="'.sanitize_title($attribute['name']).'"><option value="">'.$attribute['name'].'&hellip;</option><option>'.implode('</option><option>', $options).'</option></select>';

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
		<p class="description"><?php _e('Add pricing/inventory for product variations. All fields are optional; leave blank to use attributes from the main product data. <strong>Note:</strong> Please save your product attributes in the "Product Data" panel first.', 'jigoshop'); ?></p>

		<button type="button" class="button button-primary add_configuration"><?php _e('Add Configuration', 'jigoshop'); ?></button>
		
		<div class="clear"></div>
	</div>
	<?php
}
add_action('jigoshop_product_type_options_box', 'configurable_product_type_options');

/**
 * Product Type selector
 * 
 * Adds type to the selector on the edit product page
 **/
function configurable_product_type_selector( $product_type ) {
	echo '<option value="configurable" '; if ($product_type=='configurable') echo 'selected="selected"'; echo '>'.__('Configurable','jigoshop').'</option>';
}
add_action('product_type_selector', 'configurable_product_type_selector');

/**
 * Product Type JavaScript
 * 
 * Adds JavaScript for the panel
 **/
function configurable_product_write_panel_js( $product_type ) {
	
	global $post;
	?>
	jQuery(function(){
		
		// CONFIGURABLE PRODUCT PANEL
		jQuery('button.add_configuration').live('click', function(){
		
			jQuery('.jigoshop_configurations').append('<div class="jigoshop_configuration">\
				<p>\
					<button type="button" class="remove_config button"><?php _e('Remove', 'jigoshop'); ?></button>\
					<strong><?php _e('Variation:', 'jigoshop'); ?></strong><?php
						
						/*$attributes = maybe_unserialize( get_post_meta($post->ID, 'product_attributes', true) );
						if (isset($attributes) && sizeof($attributes)>0) foreach ($attributes as $attribute) :
							
							var_dump($attribute);
							
							$options = explode("\n", $attribute[1]);
							if (sizeof($options)>0) :
								echo '<select name="'.sanitize_title($attribute[0]).'"><option value="">'.$attribute[0].'&hellip;</option><option>'.implode('</option><option>', $options).'</option></select>\\';
							endif;
						endforeach;*/
						
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
		
		jQuery('.upload_image_button').click(function(){
			parent = jQuery(this).parent();
			
			current_field_wrapper = parent;
			
			formfield = jQuery('.upload_image_src', parent).attr('name');
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			return false;
		});

		window.send_to_editor = function(html) {
			imgurl = jQuery('img', html).attr('src');
			jQuery('.upload_image_src', current_field_wrapper).val(imgurl);
			jQuery('img', current_field_wrapper).attr('src', imgurl);
			tb_remove();
		}

	});
	<?php
	
}
add_action('product_write_panel_js', 'configurable_product_write_panel_js');

