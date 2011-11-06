<?php
class jigoshop_total {

	public $amount;
	public $amount_meta;

	public $title;
	public $title_meta;

	public $priority;

	function __construct($title = null, $amount = 0, $priority = 10) {
		$this->title = $title;
		$this->amount = jigoshop_total::clean_amount($amount);
		$this->priority = $priority;
	}

	public function get_amount() {
		return jigoshop_total::clean_amount($this->amount);
	}

	public function get_title_display($show_meta = true) {
		$display = $this->title;
		if ($this->title_meta && $show_meta) {
			$display .= sprintf('<small>%s</small>', $this->title_meta);
		}
		return $display;
	}

	public function get_amount_display($show_meta = true) {
		$display = jigoshop_price($this->amount);
		if ($this->amount_meta && $show_meta) {
			$display .= sprintf('<small>%s</small>', $this->amount_meta);
		}
		return $display;
	}


	public static function clean_amount($amount) {
		$amount = html_entity_decode($amount);
		return doubleval(preg_replace("/^[^0-9\.]/", "", $amount));
	}

	static function sort_by_title(&$totals) {
		usort($totals, create_function('$a,$b', 'return $a->title > $b->title ? 1 : -1;'));
	}

	static function sort_by_amount(&$totals) {
		usort($totals, create_function('$a,$b', 'return $a->amount > $b->amount ? 1 : -1;'));
	}

	static function sort(&$totals) {
		usort($totals, create_function('$a,$b', 'return $a->priority > $b->priority ? 1 : -1;'));
	}
}