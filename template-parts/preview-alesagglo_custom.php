		<?php if (has_post_thumbnail()) {
			the_post_thumbnail('thumbnail', array('aria-hidden' => 'true'));
		} ?>
		<h2 class="preview-title" aria-label="<?php echo esc_attr( get_the_title() );?>">
			<?php the_title( '<a href="' . esc_url(get_permalink()) . '" aria-label="lire l&rsquo;information compl&egrave;te">', '</a>' ); ?>
		</h2>
		<?php // add custom fields
		?>
