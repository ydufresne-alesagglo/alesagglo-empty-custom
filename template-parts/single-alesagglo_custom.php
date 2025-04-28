<?php get_header(); ?>
	<main id="publi-<?php the_ID(); ?>" <?php post_class(); ?> role="main">
		<header class="publi-header">
			<?php if (has_post_thumbnail()) {
				the_post_thumbnail('', array('aria-hidden' => 'true'));
			} ?>
			<h1 class="publi-title" aria-label="<?php echo esc_attr( get_the_title() );?>">
				<?php the_title(); ?>
			</h1>
		</header>
		<div class="publi-content">
			<?php the_excerpt(); ?>
		</div>
		<?php // add custom fields
		?>
	</main>
<?php get_footer();