<?php
require_once AEC_PATH . 'inc/class-Metabox.php';
require_once AEC_PATH . 'inc/class-InputField.php';
require_once AEC_PATH . 'inc/class-TextareaField.php';
require_once AEC_PATH . 'inc/class-AttachmentField.php';

class Custom {

	public const CPT = 'alesagglo_custom';
	public const TAXO = 'alesagglo_custom_category';
	private const AEC_PATH = AEC_PATH;

	/*
	 * register custom post type
	 */
	public function register_custom() {

		$labels = array(
			'name'					=> 'Customs',
			'singular_name'			=> 'Custom',
			'menu_name'				=> 'Customs',
			'all_items'				=> 'Toutes les customs',
			'name_admin_bar'		=> 'Custom',
			'add_new'				=> 'Ajouter',
			'add_new_item'			=> 'Ajouter un custom',
			'new_item'				=> 'Nouvelle custom',
			'edit_item'				=> 'Modifier la custom',
			'view_item'				=> 'Voir la custom',
			'update_item'			=> 'Modifier la custom',
			'search_items'			=> 'Rechercher un custom',
			'not_found'				=> 'Aucune custom trouvée',
			'not_found_in_trash'	=> 'Aucune custom dans la corbeille',
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
			'supports'				=> array('title', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields'),
			'taxonomies'			=> array(self::TAXO),
			'menu_icon'				=> 'dashicons-media-document',
		);
	
		register_post_type(self::CPT, $args);
	}

	/*
	 * register custom taxonomy
	 */
	public function register_custom_category() {

		$labels = array(
			'name'				=> 'Catégories',
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


	/*
	 * define metabox
	 */
	public function define_metabox() {
		$metabox = new Metabox('custom_metabox_sample', 'Sample', self::CPT);
		$metabox->add_field(new InputField('custom_field_sample', 'Sample', 'number'));
	}


	/*
	 * register hooks
	 */
	public function register_hooks() {

		if (is_admin()) {
			// filter
			add_action('restrict_manage_posts', array($this, 'filter_by_taxonomy'));
			add_filter('parse_query', array($this, 'apply_filter_by_taxonomy_query'));

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


	/*
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


	/*
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

	/*
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

	/*
	* remove slug for home
	*/
	public function remove_slug_for_home($post_link, $post) {
		if ($post->post_type == self::CPT && intval($post->ID) == intval(get_option('page_on_front'))) {
			return home_url('/');
		}
		return $post_link;
	}

	/*
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


	/*
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
	
		$plugin_template = self::AEC_PATH . $custom_template;
		if (file_exists($plugin_template)) {
			return $plugin_template;
		}
	
		return $template;
	}

	/*
	 * get preview template
	 */
	public static function get_preview_template_part() {
		$custom_template = 'template-parts/preview-' . self::CPT . '.php';
		$theme_template = locate_template($custom_template);
		if ($theme_template) {
			get_template_part('template-parts/preview', self::CPT);
		} else {
			include self::AEC_PATH . 'template-parts/preview-' . self::CPT . '.php';
		}
	}
}
