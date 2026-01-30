<?php
	get_header();
	the_post();
	$post_id = get_the_ID();
?>
	<main id="custom-<?php echo $post_id; ?>" <?php post_class(); ?> role="main">
		<header class="custom-header">
			<?php if (has_post_thumbnail()) {
				the_post_thumbnail('', array('aria-hidden' => 'true'));
			} ?>
			<h1 class="custom-title" aria-label="<?php echo esc_attr( get_the_title() );?>">
				<?php the_title(); ?>
			</h1>
		</header>
		<div class="custom-content">
			<?php the_content(); ?>
		</div>
		<?php // add custom fields here
		?>
	</main>
<?php get_footer();