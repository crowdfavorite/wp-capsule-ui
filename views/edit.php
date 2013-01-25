<article id="post-edit-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>" <?php post_class('edit clearfix' . (is_sticky() ? ' sticky' : '')); ?>>
	<header>
		<a href="#" class="post-close-link"><span><?php _e('Sticky', 'capsule'); ?></span></a>
	</header>
	<div class="leftCol">
		<ul>
			<li><h2 class="day"><?php the_time('j'); ?></h2></li>
			<li><h3 class="month"><?php the_time('M'); ?></h3></li>
			<li><h4 class="year"><?php the_time('Y'); ?></h4></li>
		</ul>
	</div>	
	<div class="meta">
		<h3><?php _e('Projects', 'capsule'); ?></h3>
		<?php echo capsule_term_list(get_the_ID(), 'projects'); ?>
		<br>
		<h3><?php _e('Tags', 'capsule'); ?></h3>
		<?php echo capsule_term_list(get_the_ID(), 'post_tag'); ?>
		<br>
		<h3><?php _e('Code', 'capsule'); ?></h3>
		<?php echo capsule_term_list(get_the_ID(), 'code'); ?>
	</div>
	<div class="content">
		<div id="ace-editor-<?php echo $post->ID; ?>" class="ace-editor"></div>
	</div>
</article>
