<?php
/**
 * Metabox class handling fields
 */

namespace AlesAggloEmptyCustom;

class Metabox {

	private const PREFIX = AEC_PREFIX;

	private $id;
	private $title;
	private $post_type;
	private array $fields = array();
	private $render_callback;

	public function __construct(string $id, string $title, string $post_type) {
		$this->id = $id;
		$this->title = $title;
		$this->post_type = $post_type;
		$this->render_callback = [$this, 'default_render'];

		add_action('add_meta_boxes', [$this, 'add_meta_box']);
		add_action('save_post_' . $this->post_type, [$this, 'save_meta_box']);
	}

	public function set_render_callback(callable $render_callback) {
		if (is_callable($render_callback)) {
			$this->render_callback = $render_callback;
		}
	}

	public function get_id(): string {
		return $this->id;
	}

	public function add_field(Field $field) {
		$this->fields[$field->get_meta_key()] = $field;
	}

	public function get_fields(): array {
		return $this->fields;
	}

	public function add_meta_box() {
		add_meta_box(
			$this->id,
			$this->title,
			[$this, 'render_meta_box'],
			$this->post_type,
			'normal',
			'default'
		);
	}

	public function render_meta_box($post) {
		echo call_user_func($this->render_callback, $post);
	}

	public function default_render($post) {
		$id = $this->get_id();
		$html = wp_nonce_field(self::PREFIX . $id . '_action', self::PREFIX . $id . '_nonce', true, false);

		$html .= '<div class="'.self::PREFIX.'custom-metabox">';
		foreach ($this->get_fields() as $field) {
			$html .= $field->render_html($post);
		}
		$html .= '</div>';

		return $html;
	}

	public function save_meta_box($post_id) {
		if (!isset($_POST[self::PREFIX . $this->id . '_nonce']) || !wp_verify_nonce($_POST[self::PREFIX . $this->id . '_nonce'], self::PREFIX . $this->id . '_action')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		foreach ($this->fields as $field) {
			$field->save($post_id);
		}
	}
}
