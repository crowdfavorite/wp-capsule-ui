<article id="post-content-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>" <?php post_class('content clearfix excerpt' . (is_sticky() ? ' sticky' : '')); ?>>
	<header>
		<!-- <a href="<?php the_permalink(); ?>" class="post-link"><?php the_time(); ?></a> -->
	 	<?php edit_post_link(__('Edit', 'capsule'), '', ''); ?>
		<a href="#" class="post-stick-link"><span><?php _e('Sticky', 'capsule'); ?></span></a>
		<a href="#" class="post-delete-link"><span><?php _e('Delete', 'capsule'); ?></span></a>
	</header>
	<div class="post-date">
		<ul>
			<li class="day"><?php the_time('j'); ?></li>
			<li class="month"><?php the_time('M'); ?></li>
			<li class="year"><?php the_time('Y'); ?></li>
		</ul>
	</div>	
	<div class="post-meta">
		<h3><?php _e('Projects', 'capsule'); ?></h3>
		<?php echo capsule_term_list(get_the_ID(), 'projects'); ?>
		<h3><?php _e('Tags', 'capsule'); ?></h3>
		<?php echo capsule_term_list(get_the_ID(), 'post_tag'); ?>
		<h3><?php _e('Code', 'capsule'); ?></h3>
		<?php echo capsule_term_list(get_the_ID(), 'code'); ?>
	</div>
	<div class="post-content">
		<?php the_content(); ?>
		<div class="post-excerpt"></div>
	</div>
</article>