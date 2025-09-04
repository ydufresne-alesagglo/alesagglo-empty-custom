<?php get_header(); ?>
	<main id="site-main" <?php post_class( 'site-main' ); ?> role="main">
		<header class="archive-header">
			<h1 class="archive-title" aria-label="<?php echo esc_attr( get_the_archive_title() );?>">
				<?php the_archive_title(); ?>
			</h1>
			<div class="archive-description" aria-label="<?php echo esc_attr( get_the_archive_description() );?>">
				<?php the_archive_description(); ?>
			</div>
		</header>
		<div class="archive-content">
		<?php if ( have_posts() ) : ?>
			<div class="archive-list" role="feed" aria-label="Liste des archives">
			<?php while ( have_posts() ) :
				the_post();
				aec_get_custom_preview_template_part();
			endwhile; ?>
			</div>
			<?php the_posts_navigation();
		else : ?>
			<div class="empty-list" role="status" aria-live="polite">
				<p>Il semble que rien n'ait été trouvé à cet endroit.</p>
			</div>
		<?php endif; ?>
		</div>
	</main>
<?php get_footer();