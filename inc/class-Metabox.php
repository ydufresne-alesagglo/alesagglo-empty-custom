<?php
require_once AEC_PATH . 'inc/class-Field.php';
/**
 * Metabox class handling fields
 */
class Metabox {

	private const PREFIX = AEC_PREFIX;

	private $id;
	private $title;
	private $post_type;
	private array $fields = array();
	private $render_callback;

	public function __construct(string $id, string $title, string $post_type, $render_callback = null) {
		$this->id = $id;
		$this->title = $title;
		$this->post_type = $post_type;

		if (is_callable($render_callback)) {
			$this->render_callback = $render_callback;
		} else {
			$this->render_callback = [$this, 'default_render'];
		}

		add_action('add_meta_boxes', [$this, 'add_meta_box']);
		add_action('save_post_' . $this->post_type, [$this, 'save_meta_box']);
	}


	public function add_field(Field $field): void {
		$this->fields[] = $field;
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
		call_user_func($this->render_callback, $post);
	}

	private function default_render($post) {
		wp_nonce_field(self::PREFIX . $this->id . '_action', self::PREFIX . $this->id . '_nonce');

		$html = '<div class="'.self::PREFIX . 'custom-metabox">';
		foreach ($this->fields as $field) {
			$html .= $field->render_html($post);
		}
		$html .= '</div>';

		echo $html;
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
