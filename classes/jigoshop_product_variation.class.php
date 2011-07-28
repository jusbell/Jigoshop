<?php
/**
 * Product Variation Class
 * @class jigoshop_product_variation
 * 
 * The JigoShop product variation class handles product variations.
 *
 * @author 		Jigowatt
 * @category 	Classes
 * @package 	JigoShop
 */
class jigoshop_product_variation {
	
	var $id;
	var $exists;
	var $sku;
	var $price;
	var $weight;
	var $enabled;
	var $stock;
	var $parent;
	
	/**
	 * Loads all variation data
	 *
	 * @param   int		$id		ID of the product to load
	 */
	function jigoshop_product_variation( $id ) {
		
		$product_custom_fields = get_post_custom( $id );
		
		$this->id = $id;
		
		if (isset($product_custom_fields['SKU'][0]) && !empty($product_custom_fields['SKU'][0])) $this->sku = $product_custom_fields['SKU'][0]; else $this->sku = $this->id;
		
		if (isset($product_custom_fields['product_data'][0])) $this->data = maybe_unserialize( $product_custom_fields['product_data'][0] ); else $this->data = '';
		
		if (isset($product_custom_fields['product_attributes'][0])) $this->attributes = maybe_unserialize( $product_custom_fields['product_attributes'][0] ); else $this->attributes = array();		
		
		if (isset($product_custom_fields['price'][0])) $this->price = $product_custom_fields['price'][0]; else $this->price = 0;

		if (isset($product_custom_fields['visibility'][0])) $this->visibility = $product_custom_fields['visibility'][0]; else $this->visibility = 'hidden';
		
		if (isset($product_custom_fields['stock'][0])) $this->stock = $product_custom_fields['stock'][0]; else $this->stock = 0;
		
		// Again just in case, to fix WP bug
		$this->data = maybe_unserialize( $this->data );
		$this->attributes = maybe_unserialize( $this->attributes );
		
		$terms = wp_get_object_terms( $id, 'product_type' );
		if (!is_wp_error($terms) && $terms) :
			$term = current($terms);
			$this->product_type = $term->slug; 
		else :
			$this->product_type = 'simple';
		endif;
		
		$this->children = array();
		
		if ( $children_products =& get_children( 'post_parent='.$id.'&post_type=product&orderby=menu_order&order=ASC' ) ) :
			if ($children_products) foreach ($children_products as $child) :
				$child->product = &new jigoshop_product( $child->ID );
			endforeach;
			$this->children = (array) $children_products;
		endif;
		
		if ($this->data) :
			$this->exists = true;		
		else :
			$this->exists = false;	
		endif;
	}

}