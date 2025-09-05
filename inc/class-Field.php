<?php
/**
 * Top class for all form fields
 */

namespace AlesAggloEmptyCustom;

abstract class Field {

	protected const PREFIX = AEC_PREFIX;

	protected $meta_key;
	protected $label;

	public function __construct(string $meta_key, string $label) {
		$this->meta_key = $meta_key;
		$this->label = $label;
	}

	public function save($post_id) {
		if (!isset($_POST[$this->meta_key])) {
			return;
		}

		$value = sanitize_text_field( $_POST[$this->meta_key] );
		if ($value == '') {
			delete_post_meta($post_id, $this->meta_key);
		} else {
			update_post_meta($post_id, $this->meta_key, $value);
		}
	}

	public function render_html($post) {
		$value = get_post_meta($post->ID, $this->meta_key, true);

		return '<div class="'.self::PREFIX.'field"><span>' . esc_attr($this->label) . ' (' . esc_attr($this->meta_key) . ') : ' . esc_attr($value) . '</span></div>';
	}
}
