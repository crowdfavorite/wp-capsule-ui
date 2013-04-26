<article id="post-content-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>" <?php post_class('content clearfix' . (is_sticky() ? ' sticky' : '')); ?>>
	<div class="post-date">
		<a href="<?php the_permalink(); ?>">
			<ul>
				<li class="day"><?php the_time('j'); ?></li>
				<li class="month"><?php the_time('M'); ?></li>
				<li class="year"><?php the_time('Y'); ?></li>
			</ul>
		</a>
	</div>
	<div class="post-meta">
<?php
echo capsule_term_list(get_the_ID(), 'projects');
echo capsule_term_list(get_the_ID(), 'post_tag');
echo capsule_term_list(get_the_ID(), 'code');

if (is_capsule_server()) {
?>
		<p class="author">
<?php

echo get_avatar(get_the_author_meta('email'), 20);
printf(__('<a href="%s1">%s2</a>', 'capsule'), esc_url(get_the_author_meta('url')), get_the_author_meta('display_name'));

?>
		</p>
<?php
}
?>
	</div>
	<div class="post-content">
		<?php the_content(); ?>
		<nav class="post-menu">
			<a href="#" class="post-sticky-link" title="<?php _e('Star', 'capsule'); ?>">&#57391;</a>
			<a href="#" class="post-unsticky-link" title="<?php _e('Un-Star', 'capsule'); ?>">&#57393;</a>
			<span class="post-sticky-loading" title="<?php _e('Loading...', 'capsule'); ?>">&#59441;</span>
			<?php edit_post_link('&#57535;', '', ''); ?>
			<a href="#" class="post-delete-link" title="<?php _e('Trash', 'capsule'); ?>">&#59177;</a>
			<?php do_action( "capsule_post_menu_links" ); ?>
		</nav>
	</div>
</article>
