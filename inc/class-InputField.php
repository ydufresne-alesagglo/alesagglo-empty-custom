<?php
/**
 * Input field class
 */

namespace AlesAggloEmptyCustom;

class InputField extends Field {

	protected $input_type;
	protected $placeholder;
	protected $default_value;
	protected $required;
	protected $admin_column;
	protected $sort_column;

	public function __construct(string $meta_key, string $label, string $input_type = 'text', string $placeholder = '', string $default_value = '', bool $required = false, bool $admin_column = false, bool $sort_column = true) {

		$allowed_types = ['text', 'number', 'url', 'email', 'password', 'checkbox', 'color', 'date', 'datetime-local', 'time'];
		if (!in_array($input_type, $allowed_types, true)) {
			$this->input_type = false;
		} else {
			$this->input_type = $input_type;
		}

		$this->placeholder = $placeholder;
		$this->default_value = $default_value;
		$this->required = $required;
		$this->admin_column = $admin_column;
		$this->sort_column = $sort_column;

		parent::__construct($meta_key, $label);
	}

	public function get_input_type() {
		return $this->input_type;
	}

	public function get_placeholder() {
		return $this->placeholder;
	}

	public function get_default_value() {
		return $this->default_value;
	}

	public function is_required() {
		return $this->required;
	}

	public function is_admin_column() {
		return $this->admin_column;
	}

	public function is_sortable_column() {
		return $this->sort_column;
	}

	public function save($post_id) {
		if ($this->input_type === false) return;

		if ($this->input_type == 'checkbox') {
			if (!isset($_POST[$this->meta_key])) {
				delete_post_meta($post_id, $this->meta_key);
			} else {
				update_post_meta($post_id, $this->meta_key, 1);
			}
			return;
		} else
		if($this->input_type == 'date') {
			if (!isset($_POST[$this->meta_key])) {
				delete_post_meta($post_id, $this->meta_key);
			} else {
				$date = new \DateTime($_POST[$this->meta_key]);
				update_post_meta($post_id, $this->meta_key, $date->format('Y-m-d'));
			}
			return;
		} else
		if($this->input_type == 'datetime-local') {
			if (!isset($_POST[$this->meta_key])) {
				delete_post_meta($post_id, $this->meta_key);
			} else {
				$date = new \DateTime($_POST[$this->meta_key]);
				update_post_meta($post_id, $this->meta_key, $date->format('Y-m-d H:i:s'));
			}
			return;
		} else
		if($this->input_type == 'time') {
			if (!isset($_POST[$this->meta_key])) {
				delete_post_meta($post_id, $this->meta_key);
			} else {
				$date = new \DateTime($_POST[$this->meta_key]);
				update_post_meta($post_id, $this->meta_key, $date->format('H:i:s'));
			}
			return;
		}

		if (!isset($_POST[$this->meta_key])) {
			return;
		}

		$allowed = ['sup' => [], 'sub' => []];
		$value = wp_kses( wp_unslash($_POST[$this->meta_key]), $allowed );
		if ($value === '') {
			delete_post_meta($post_id, $this->meta_key);
		} else {
			update_post_meta($post_id, $this->meta_key, $value);
		}
	}

	public function default_render($post) {
		$input_type = $this->get_input_type();
		if ($input_type === false) return;

		$meta_key = $this->get_meta_key();
		$value = get_post_meta($post->ID, $meta_key, true);
		if ($value === '' && $this->get_default_value() !== '') {
			$value = $this->get_default_value();
		}

		$html = '<div class="'.parent::PREFIX.'input-field">';
		$html .= '<label for="' . esc_attr($meta_key) . '">' . esc_html($this->get_label()) . '</label>&nbsp;';
		$html .= '<input type="' . esc_attr($input_type) . '" name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '"';
		$html .= ($input_type == 'checkbox' ? ($value ? ' checked' : '') : ' value="' . esc_attr($value) . '" placeholder="' . esc_attr($this->get_placeholder()) . '"');
		$html .= $this->is_required() ? ' required' : '';
		$html .= '>';
		$html .= '</div>';

		return $html;
	}
}
