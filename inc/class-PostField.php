<?php
/**
 * Post field class
 */

namespace AlesAggloEmptyCustom;

class PostField extends Field {

	protected $post_type;
	protected $placeholder;

	public function __construct(string $meta_key, string $label, string $post_type = 'post', string $placeholder = '') {

		$this->post_type = $post_type;
		$this->placeholder = $placeholder;
		
		parent::__construct($meta_key, $label);
	}

	public function get_post_type() {
		return $this->post_type;
	}

	public function get_placeholder() {
		return $this->placeholder;
	}

	public function save($post_id): void {
		if (!isset($_POST[$this->meta_key])) {
			return;
		}

		$value = intval($_POST[$this->meta_key]);
		if ($value <= 0) {
			delete_post_meta($post_id, $this->meta_key);
		} else {
			update_post_meta($post_id, $this->meta_key, $value);
		}
	}

	public function default_render($post) {
		$meta_key = $this->get_meta_key();
		$value = get_post_meta($post->ID, $meta_key, true);

		$link = ($value ? '<a href="' . esc_url(get_permalink($value)) . '" target="_blank">' . esc_html(get_the_title($value)) . '</a>' : '&nbsp;');

		$html = '<div class="'.parent::PREFIX.'post-field" data-meta-key="' . esc_attr($meta_key) . '" data-post-type="' . esc_attr($this->get_post_type()) . '">';
		$html .= '<label>' . esc_html($this->get_label()) . '</label>';
		$html .= '<input type="hidden" name="' . esc_attr($meta_key) . '" id="'.parent::PREFIX.esc_attr($meta_key) . '" value="' . esc_attr($value) . '">';
		$html .= '<span class="'.parent::PREFIX.'post-field-link">'.$link.'</span>';
		$html .= '<button type="button" class="'.parent::PREFIX.'post-field-select">Select</button>';
		$html .= '<button type="button" class="'.parent::PREFIX.'post-field-remove">Remove</button>';

		$html .= '<div class="'.parent::PREFIX.'post-search-container" style="display:none;">';
		$html .= '<div class="'.parent::PREFIX.'post-search-header">';
		$html .= '<input type="text" class="'.parent::PREFIX.'post-search-input" placeholder="' . esc_attr($this->get_placeholder()) . '" autocomplete="off">';
		$html .= '<button type="button" class="'.parent::PREFIX.'post-search-close"><strong>X</strong></button>';
		$html .= '</div>';
		$html .= '<ul class="'.parent::PREFIX.'post-search-results"></ul>';
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}
}
