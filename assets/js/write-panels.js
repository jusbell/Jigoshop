			jQuery('#attributes_list tr').each(function(index, el){ jQuery('.attribute_position', el).val( parseInt( jQuery(el).index('#attributes_list tr') ) ); });
		};

			var attribute = $('select.attribute_taxonomy').val();
			var type = $('select.attribute_taxonomy').find(':selected').data('type');

			if (!attribute) {
				var size = jQuery('table.jigoshop_attributes tbody tr').size();
				// Add custom attribute row
				jQuery('#attributes_list').append('<tr><td class="center"><button type="button" class="button move_up">&uarr;</button><button type="button" class="move_down button">&darr;</button><input type="hidden" name="attribute_position[' + size + ']" class="attribute_position" value="' + size + '" /></td><td><input type="text" name="attribute_names[' + size + ']" /><input type="hidden" name="attribute_is_taxonomy[' + size + ']" value="0" /></td><td><input type="text" name="attribute_values[' + size + ']" /></td><td class="center"><input type="checkbox" checked="checked" name="attribute_visibility[' + size + ']" value="1" /></td><td class="center"><input type="checkbox" name="attribute_variation[' + size + ']" value="1" /></td><td class="center"><button type="button" class="remove_row button">&times;</button></td></tr>');
			} else {

				var size = jQuery('table.jigoshop_attributes tbody tr').size();
				// Reveal taxonomy row
				var thisrow = jQuery('#attributes_list tr.' + attribute);

				jQuery('table.jigoshop_attributes tbody').append( thisrow );
				jQuery(thisrow).show();
				row_indexes();

			}

		jQuery('button.hide_row').live('click', function(){
			var answer = confirm("Remove this attribute?")
			if (answer){
				jQuery(this).parent().parent().find('select, input[type=text], input[type=checkbox]').val('');
				jQuery(this).parent().parent().hide();

