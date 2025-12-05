<?php
/**
 * Custom CPT class
 */

require_once AEC_PATH . 'inc/class-Metabox.php';
require_once AEC_PATH . 'inc/class-Field.php';
require_once AEC_PATH . 'inc/class-InputField.php';
require_once AEC_PATH . 'inc/class-TextareaField.php';
require_once AEC_PATH . 'inc/class-AttachmentField.php';
require_once AEC_PATH . 'inc/class-PostField.php';

use AlesAggloEmptyCustom\Metabox;
use AlesAggloEmptyCustom\Field;
use AlesAggloEmptyCustom\InputField;
use AlesAggloEmptyCustom\TextareaField;
use AlesAggloEmptyCustom\AttachmentField;
use AlesAggloEmptyCustom\PostField;

class Custom {

	public const CPT = 'alesagglo_custom';
	public const TAXO = 'alesagglo_custom_category';
	private const SORT_META = self::CPT.'_sort_meta';
	private const PATH = AEC_PATH;
	private const PREFIX = AEC_PREFIX;
	private array $boxes = array();


	/**
	 * constructor
	 */
	public function __construct() {
		$this->register_taxonomy();
		$this->register_post_type();
		$this->define_metabox();
		$this->register_hooks();
	}


	/**
	 * add metabox
	 */
	public function add_box(Metabox $box) {
		$this->boxes[$box->get_id()] = $box;
	}


	/**
	 * register custom post type
	 */
	public function register_post_type() {

		$labels = array(
			'name'					=> 'Customs',
			'singular_name'			=> 'Custom',
			'menu_name'				=> 'Customs',
			'all_items'				=> 'Tous les customs',
			'name_admin_bar'		=> 'Custom',
			'add_new'				=> 'Ajouter',
			'add_new_item'			=> 'Ajouter un custom',
			'new_item'				=> 'Nouveau custom',
			'edit_item'				=> 'Modifier le custom',
			'view_item'				=> 'Voir le custom',
			'update_item'			=> 'Modifier le custom',
			'search_items'			=> 'Rechercher un custom',
			'not_found'				=> 'Aucun custom trouvé',
			'not_found_in_trash'	=> 'Aucun custom dans la corbeille',
		);

		$args = array(
			'labels'				=> $labels,
			'label'					=> 'Customs',
			'description'			=> 'Customs, etc...',
			'public'				=> true,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> true,
			'show_in_rest'			=> true,
			'has_archive'			=> true,
			'hierarchical'			=> false,
			'exclude_from_search'	=> false,
			'rewrite'				=> array('slug' => 'customs', 'with_front' => false),
			'capability_type'		=> 'post',
			'supports'				=> array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields'),
			'taxonomies'			=> array(self::TAXO),
			'menu_icon'				=> 'dashicons-media-document',
		);

		register_post_type(self::CPT, $args);
	}


	/**
	 * register custom taxonomy
	 */
	public function register_taxonomy() {

		$labels = array(
			'name'				=> 'Catégories des customs',
			'singular_name'		=> 'Catégorie des customs',
			'menu_name'			=> 'Catégories',
			'all_items'			=> 'Tous les catégories',
			'add_new_item'		=> 'Ajouter une nouvelle catégorie',
			'new_item_name'		=> 'Nom de la nouvelle catégorie',
			'edit_item'			=> 'Modifier la catégorie',
			'view_item'			=> 'Voir la catégorie',
			'update_item'		=> 'Mettre à jour la catégorie',
			'search_items'		=> 'Rechercher une catégorie',
		);

		$args = array(
			'labels'			=> $labels,
			'label'				=> 'Catégories des customs',
			'description'		=> 'Catégories des customs',
			'public'			=> true,
			'show_ui'			=> true,
			'show_in_menu'		=> true,
			'show_admin_column'	=> true,
			'show_in_nav_menus'	=> true,
			'show_in_rest'		=> true,
			'hierarchical'		=> true,
			'rewrite'			=> array('slug' => 'customs/categorie', 'with_front' => false),
		);

		register_taxonomy(self::TAXO, self::CPT, $args);
		register_taxonomy_for_object_type(self::TAXO, self::CPT);
	}


	/**
	 * define metabox
	 */
	public function define_metabox() {
		$samplebox = new Metabox('custom_metabox_sample', 'Sample Box', self::CPT);
		$samplebox->add_field(new InputField('custom_field_sample_text', 'Sample Text', 'text', '', '', false, true, false));
		$samplebox->add_field(new InputField('custom_field_sample_number', 'Sample Number', 'number', '', '', false, true));
		$emptybox = new Metabox('custom_metabox_empty', 'Empty Box', self::CPT);
		$this->add_box($samplebox);
		$this->add_box($emptybox);
	}


	/**
	 * register hooks
	 */
	public function register_hooks() {

		if (is_admin()) {
			// filter
			add_action('restrict_manage_posts', array($this, 'filter_by_taxonomy'));
			add_filter('parse_query', array($this, 'apply_filter_by_taxonomy_query'));

			// manage columns
			add_filter('manage_'.self::CPT.'_posts_columns', array($this, 'register_admin_columns'));
			add_action('manage_'.self::CPT.'_posts_custom_column', array($this, 'display_admin_columns'), 10, 2);
			add_filter('manage_edit-'.self::CPT.'_sortable_columns', array($this, 'register_sortable_columns'));
			add_action('pre_get_posts', array($this, 'prepare_sorting_columns'));
			add_filter('posts_join', array($this, 'add_leftjoin_sortmeta'), 10, 2);
			add_filter('posts_orderby', array($this, 'set_orderby_sortmeta'), 10, 2);

			// home
			add_filter('get_pages', array($this, 'allow_define_as_home'), 10, 2);
		}

		// home
		add_action('pre_get_posts', array($this, 'display_as_home'));
		add_filter('post_type_link', array($this, 'remove_slug_for_home'), 10, 2);
		add_action('template_redirect', array($this, 'redirect_home_to_root'));

		// template
		add_filter('single_template', array($this, 'load_template'));
		add_filter('archive_template', array($this, 'load_template'));
		add_filter('taxonomy_template', array($this, 'load_template'));
		add_filter('template_include', array($this, 'load_template'));
	}


	/**
	 * filter admin list by taxonomy
	 */
	public function filter_by_taxonomy() {
		global $typenow;
		if ($typenow !== self::CPT) return;

		$taxonomy = self::TAXO;
		$tax_obj = get_taxonomy($taxonomy);
		if (!$tax_obj) return;

		$selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$terms = get_terms(array(
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
		));

		if (!empty($terms) && !is_wp_error($terms)) {
			echo '<select name="' . esc_attr($taxonomy) . '" class="postform">';
			echo '<option value="">' . esc_html($tax_obj->labels->all_items) . '</option>';
			foreach ($terms as $term) {
				printf(
					'<option value="%s"%s>%s</option>',
					$term->slug,
					($term->slug === $selected ? ' selected="selected"' : ''),
					$term->name
				);
			}
			echo '</select>';
		}
	}

	/**
	 * apply filter by taxonomy in admin list
	 */
	public function apply_filter_by_taxonomy_query($query) {
		global $pagenow;

		if (
			$pagenow === 'edit.php' &&
			isset($query->query_vars['post_type']) &&
			$query->query_vars['post_type'] === self::CPT &&
			isset($_GET[self::TAXO]) &&
			!empty($_GET[self::TAXO])
		) {
			$query->query_vars[self::TAXO] = sanitize_text_field($_GET[self::TAXO]);
		}
	}


	/**
	 * register fields as new columns in admin list
	 */
	public function register_admin_columns($columns) {

		foreach ($this->boxes as $bid => $box) {
			$fields = $box->get_fields();
			foreach ($fields as $fid => $field) {
				if($field instanceof InputField && $field->is_admin_column()) {
					$columns[$field->get_meta_key()] = $field->get_label();
				}
			}
		}
		return $columns;
	}


	/**
	 * display field values in new columns in admin list
	 */
	public function display_admin_columns($column_name, $post_id) {
		if (!is_admin()) {
			return;
		}

		foreach ($this->boxes as $bid => $box) {
			$fields = $box->get_fields();
			foreach ($fields as $fid => $field) {
				if($field instanceof InputField && $field->is_admin_column() && $column_name === $field->get_meta_key()) {

					$input_type = $field->get_input_type();
					$value = get_post_meta($post_id, $field->get_meta_key(), true);
					if ($value) {
						switch ($input_type) {
							case 'text':
							case 'number':
								echo '<span class="'.self::PREFIX.'text-input-field">'.esc_html($value).'</span>';
								break;
							case 'url':
								echo '<span class="'.self::PREFIX.'url-input-field"><a href="'.esc_url($value).'" target="_blank">'.esc_html($value).'</a></span>';
								break;
							case 'email':
								echo '<span class="'.self::PREFIX.'email-input-field"><a href="mailto:'.esc_attr($value).'">'.esc_html($value).'</a></span>';
								break;
							case 'password':
								echo '<span class="'.self::PREFIX.'password-input-field">'.str_repeat('*', strlen($value)).'</span>';
								break;
							case 'checkbox':
								echo '<span class="'.self::PREFIX.'checkbox-input-field">Ok</span>';
								break;
							case 'color':
								echo '<span class="'.self::PREFIX.'color-input-field" style="color:'.esc_attr($value).';">'.esc_attr($value).'</span>';
								break;
							case 'date':
								echo '<span class="'.self::PREFIX.'date-input-field">'.esc_html(date_format(date_create($value), 'd/m/Y')).'</span>';
								break;
							case 'datetime-local':
								echo '<span class="'.self::PREFIX.'datetime-input-field">'.esc_html(date_format(date_create($value), 'd/m/Y H\hi')).'</span>';
								break;
							case 'time':
								echo '<span class="'.self::PREFIX.'time-input-field">'.esc_html(date_format(date_create($value), 'H\hi')).'</span>';
								break;
						}
					} else {
						echo '&mdash;';
					}
				}
			}
		}
	}


	/**
	 * register fields as sortable columns in admin list
	 */
	public function register_sortable_columns($columns) {

		foreach ($this->boxes as $bid => $box) {
			$fields = $box->get_fields();
			foreach ($fields as $fid => $field) {
				if($field instanceof InputField && $field->is_admin_column() && $field->is_sortable_column()) {
					$columns[$field->get_meta_key()] = $field->get_meta_key();
				}
			}
		}
		return $columns;
	}


	/**
	 * prepare sorting on fields columns in admin list
	 */
	public function prepare_sorting_columns($query) {
		if (!is_admin() || !$query->is_main_query()) {
			return;
		}

		if ($query->get('post_type') !== self::CPT) {
			return;
		}

		$orderby = $query->get('orderby');
		if (!$orderby) {
			return;
		}

		foreach ($this->boxes as $bid => $box) {
			$fields = $box->get_fields();
			foreach ($fields as $fid => $field) {

				if ($field instanceof InputField && $field->is_admin_column() && $field->is_sortable_column() && $orderby === $field->get_meta_key()) {

					$input_type = $field->get_input_type();

					switch ($input_type) {
						case 'text':
						case 'url':
						case 'email':
						case 'password':
						case 'color':
						case 'date':
						case 'datetime-local':
						case 'time':
							$query->set(self::SORT_META, $orderby);
							break;
						case 'number':
						case 'checkbox':
							$query->set(self::SORT_META, $orderby);
							$query->set(self::SORT_META.'_numeric', true);
							break;
					}

					return;
				}
			}
		}
	}


	/**
	 * add left join on field columns in admin list
	 */
	public function add_leftjoin_sortmeta($join, $query) {
		$alias = $query->get(self::SORT_META);
		if (!$alias) {
			return $join;
		}

		global $wpdb;

		$join .= " LEFT JOIN {$wpdb->postmeta} AS ".self::SORT_META." ON ({$wpdb->posts}.ID = ".self::SORT_META.".post_id AND ".self::SORT_META.".meta_key = '{$alias}')";
		return $join;
	}


	/**
	 * define order by on field columns in admin list
	 */
	public function set_orderby_sortmeta($orderby_sql, $query) {
		$alias = $query->get(self::SORT_META);
		if (!$alias) {
			return $orderby_sql;
		}

		global $wpdb;

		$order = strtoupper($query->get('order')) === 'ASC' ? 'ASC' : 'DESC';
		$meta_value = $query->get(self::SORT_META.'_numeric') ? self::SORT_META.".meta_value+0" : self::SORT_META.".meta_value";
		$orderby_sql = " (".self::SORT_META.".meta_value IS NULL) ASC, {$meta_value} {$order}, {$wpdb->posts}.ID ASC ";
		return $orderby_sql;
	}


	/**
	 * allow define custom post type as home
	 */
	public function allow_define_as_home($pages, $args) {
		if (is_admin()) {
			$screen = get_current_screen();
			if ($screen && $screen->id == 'options-reading') {
				if (isset($args['name']) && $args['name'] == 'page_on_front') {
					$cpts = get_posts(
						array(
							'post_type' => self::CPT,
							'numberposts' => -1,
						)
					);
					$pages = array_merge($pages, $cpts);
				}
			}
		}
		return $pages;
	}

	/**
	 * display as home
	 */
	public function display_as_home($query) {

		if (is_admin() || !$query->is_main_query()) return;

		$front_id = get_option('page_on_front');
		if (!$front_id) return;

		$post = get_post($front_id);
		if (!$post || $post->post_type != self::CPT) return;

		if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/') {

			$query->set('p', $front_id);
			$query->set('post_type', $post->post_type);
			$query->is_home = false;
			$query->is_page = false;
			$query->is_single = true;
			$query->is_singular = true;
		}
	}

	/**
	 * remove slug for home
	 */
	public function remove_slug_for_home($post_link, $post) {
		if ($post->post_type == self::CPT && intval($post->ID) == intval(get_option('page_on_front'))) {
			return home_url('/');
		}
		return $post_link;
	}

	/**
	 * redirect home to root
	 */
	public function redirect_home_to_root() {
		if (is_singular(self::CPT)) {
			$front_id = get_option('page_on_front');
			if (get_the_ID() == $front_id && parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/') {
				wp_redirect(home_url('/'), 301);
				exit;
			}
		}
	}


	/**
	 * load template
	 */
	public function load_template($template) {

		if (is_singular(self::CPT)) {
			$custom_template = 'template-parts/single-'.self::CPT.'.php';
		}
		if (is_post_type_archive(self::CPT)) {
			$custom_template = 'template-parts/archive-'.self::CPT.'.php';
		}
		if (is_tax(self::TAXO)) {
			$custom_template = 'template-parts/taxonomy-'.self::TAXO.'.php';
		}

		if (!isset($custom_template)) {
			return $template;
		}

		$theme_template = locate_template($custom_template);
		if ($theme_template) {
			return $theme_template;
		}

		$plugin_template = self::PATH . $custom_template;
		if (file_exists($plugin_template)) {
			return $plugin_template;
		}

		return $template;
	}

	/**
	 * get preview template
	 */
	public static function get_preview_template_part() {
		$custom_template = 'template-parts/preview-' . self::CPT . '.php';
		$theme_template = locate_template($custom_template);
		if ($theme_template) {
			get_template_part('template-parts/preview', self::CPT);
		} else {
			include self::PATH . 'template-parts/preview-' . self::CPT . '.php';
		}
	}
}
