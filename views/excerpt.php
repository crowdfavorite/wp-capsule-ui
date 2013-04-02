<article id="post-content-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>" <?php post_class('content clearfix excerpt'); ?>>
	<nav class="post-menu">
	 	<?php edit_post_link(__('Edit', 'capsule'), '', ''); ?>
		<a href="#" class="post-stick-link"><span><?php _e('Sticky', 'capsule'); ?></span></a>
		<a href="#" class="post-delete-link"><span><?php _e('Delete', 'capsule'); ?></span></a>
	</nav>
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
?>
	</div>
	<div class="post-content">
		<?php the_content(); ?>
		<div class="post-toggle"></div>
	</div>
</article>