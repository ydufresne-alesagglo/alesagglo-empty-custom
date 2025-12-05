<?php
/**
 * Top class for all form fields
 */

namespace AlesAggloEmptyCustom;

abstract class Field {

	protected const PREFIX = AEC_PREFIX;

	protected $meta_key;
	protected $label;
	private $render_callback;

	public function __construct(string $meta_key, string $label) {
		$this->meta_key = $meta_key;
		$this->label = $label;
		$this->render_callback = [$this, 'default_render'];
	}

	public function set_render_callback(callable $render_callback) {
		if (is_callable($render_callback)) {
			$this->render_callback = $render_callback;
		}
	}

	function get_meta_key() {
		return $this->meta_key;
	}

	function get_label() {
		return $this->label;
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
		return call_user_func($this->render_callback, $post, $this);
	}

	public function default_render($post) {
		$meta_key = $this->get_meta_key();
		$value = get_post_meta($post->ID, $meta_key, true);

		return '<div class="'.self::PREFIX.'field"><span>' . esc_attr($this->get_label()) . ' (' . esc_attr($meta_key) . ') : ' . esc_attr($value) . '</span></div>';
	}
}
