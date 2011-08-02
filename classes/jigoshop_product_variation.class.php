<?php
/**
 * Product Variation Class
 * @class jigoshop_product_variation
 * 
 * The JigoShop product variation class handles product variation data.
 *
 * @author 		Jigowatt
 * @category 	Classes
 * @package 	JigoShop
 */
class jigoshop_product_variation extends jigoshop_product {
	
	var $variation;
	var $variation_id;
	var $variation_has_weight;
	var $variation_has_price;
	var $variation_has_sale_price;
	var $variation_has_stock;
	var $variation_has_sku;
	
	/**
	 * Loads all product data from custom fields
	 *
	 * @param   int		$id		ID of the product to load
	 */
	function jigoshop_product_variation( $variation_id ) {
		
		$this->variation_id = $variation_id;

		$product_custom_fields = get_post_custom( $this->variation_id );

		$this->get_variation_post_data();
		
		parent::jigoshop_product( $this->variation->post_parent );

		$this->parent = &new jigoshop_product( $this->variation->post_parent );
		
		if (isset($product_custom_fields['SKU'][0]) && !empty($product_custom_fields['SKU'][0])) :
			$this->variation_has_sku = true;
			$this->sku = $product_custom_fields['SKU'][0];
		endif;
		
		if (isset($product_custom_fields['stock'][0]) && !empty($product_custom_fields['stock'][0])) :
			$this->variation_has_stock = true;
			$this->stock = $product_custom_fields['stock'][0];
		endif;
		
		if (isset($product_custom_fields['weight'][0]) && !empty($product_custom_fields['weight'][0])) :
			$this->variation_has_weight = true;
			$this->data['weight'] = $product_custom_fields['weight'][0];
		endif;
		
		if (isset($product_custom_fields['price'][0]) && !empty($product_custom_fields['price'][0])) :
			$this->variation_has_price = true;
			$this->price = $product_custom_fields['price'][0];
		endif;
		
		if (isset($product_custom_fields['sale_price'][0]) && !empty($product_custom_fields['sale_price'][0])) :
			$this->variation_has_sale_price = true;
			$this->data['sale_price'] = $product_custom_fields['sale_price'][0];
		endif;
	}

	/** Get the product's post data */
	function get_variation_post_data() {
		if (empty($this->variation)) :
			$this->variation = get_post( $this->variation_id );
		endif;
		return $this->variation;
	}
	
	/** Returns the product's price */
	function get_price() {
		
		if ($this->variation_has_price) :
			if ($this->variation_has_sale_price) :
				return $this->data['sale_price'];
			else :
				return $this->price;
			endif;
		else :
			return $this->parent->get_price();
		endif;
		
	}
	
	/** Returns the price in html format */
	function get_price_html() {
		if ($this->variation_has_price) :
			$price = '';
			
			if ($this->price) :
				if ($this->variation_has_sale_price) :
					$price .= '<del>'.jigoshop_price( $this->price ).'</del> <ins>'.jigoshop_price( $this->data['sale_price'] ).'</ins>';
				else :
					$price .= jigoshop_price( $this->price );
				endif;
			endif;
	
			return $price;
		else :
			return jigoshop_price($this->parent->get_price());
		endif;
	}
	
	/**
	 * Reduce stock level of the product
	 *
	 * @param   int		$by		Amount to reduce by
	 */
	function reduce_stock( $by = 1 ) {
		if ($this->variation_has_stock) :
			if ($this->managing_stock()) :
				$reduce_to = $this->stock - $by;
				update_post_meta($this->variation_id, 'stock', $reduce_to);
				return $reduce_to;
			endif;
		else :
			$this->parent->reduce_stock( $by );
		endif;
	}
	
	/**
	 * Increase stock level of the product
	 *
	 * @param   int		$by		Amount to increase by
	 */
	function increase_stock( $by = 1 ) {
		if ($this->variation_has_stock) :
			if ($this->managing_stock()) :
				$increase_to = $this->stock + $by;
				update_post_meta($this->variation_id, 'stock', $increase_to);
				return $increase_to;
			endif;
		else :
			$this->parent->increase_stock( $by );
		endif;
	}

}