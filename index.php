<?php

/**
 * @package capsule
 *
 * This file is part of the Capsule Theme for WordPress
 * http://crowdfavorite.com/capsule/
 *
 * Copyright (c) 2012 Crowd Favorite, Ltd. All rights reserved.
 * http://crowdfavorite.com
 *
 * **********************************************************************
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * **********************************************************************
 */

if (!isset($body_classes)) {
	$body_classes = array();
}

$blog_desc = get_bloginfo('description');
$title_description = (is_home() && !empty($blog_desc) ? ' - '.$blog_desc : '');

if (get_option('permalink_structure') != '') {
	$search_onsubmit = "location.href=this.action+'search/'+encodeURIComponent(this.s.value).replace(/%20/g, '+'); return false;";
}
else {
	$search_onsubmit = '';
}

if (function_exists('cftf_is_filter') && cftf_is_filter()) {
	$body_classes[] = 'filters-on';
}

?>
<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width" />
	
	<title><?php wp_title( '|', true, 'right' ); echo esc_html( get_bloginfo('name'), 1 ).$title_description; ?></title>
	
<?php wp_head(); ?>
</head>
<body <?php body_class(implode(' ', $body_classes)); ?>>
<div class="container">
	<nav class="main-nav">
		<ul>
			<li><a href="<?php echo esc_url(site_url('/')); ?>" class="icon">&#59392;</a></li>
			<li><a href="<?php echo esc_url(admin_url('post-new.php')); ?>" class="post-new-link icon">&#59396;</a></li>
			<li><a href="#sidr" class="projects"><?php _e('@', 'capsule'); ?></a></li>
			<li><a href="#" class="tags icon"><?php _e('#', 'capsule'); ?></a></li>
			<li><a href="<?php echo esc_url(admin_url('admin.php?page=capsule')); ?>" class="icon">&#59400;</a></li>
			<li><span class="spacer"></span></li>
		</ul>
	</nav>
	
	<div id="wrap">
		<header id="header">
			<div class="inner">
<?php

$title = '';

if (is_home() || is_front_page()) {
	$title = __('Home', 'capsule');
}
else if (function_exists('cftf_is_filter') && cftf_is_filter()) {
	$title = __('Filter', 'capsule');
}
else if (is_search()) {
	$title = sprintf(__('Search: %s', 'capsule'), esc_html(get_query_var('s')));
}
else if (is_tag()) {
	$term = get_queried_object();
	$title = sprintf(__('#%s', 'capsule'), esc_html($term->name));
}
else if (is_tax('projects')) {
	$term = get_queried_object();
	$title = sprintf(__('@%s', 'capsule'), esc_html($term->name));
}
else if (is_tax('code')) {
	$term = get_queried_object();
	$title = sprintf(__('`%s', 'capsule'), esc_html($term->name));
}

?>
				<h1><?php echo $title; ?></h1>
				<form class="search clearfix" action="<?php echo esc_url(home_url('/')); ?>" method="get" onsubmit="<?php echo $search_onsubmit; ?>">
					<a href="#" class="filter-toggle"><?php _e('Filters', 'capsule'); ?></a>
					<input type="text" class="js-search" name="s" value="" placeholder="<?php _e('Search @projects, #tags, `code, etc&hellip;', 'capsule'); ?>" />
					<input type="submit" value="<?php _e('Search', 'capsule'); ?>" />
				</form>
			</div>
			<div class="filter clearfix">
			<?php capsule_taxonomy_filter(); ?>
			</div>
		</header>
		<div class="body">
<?php

if (have_posts()) {
	while (have_posts()) {
		the_post();
		
		if (is_singular()) {
			include('views/content.php');
		}
		else {
			include('views/excerpt.php');
		}
	}
	if ( $wp_query->max_num_pages > 1 ) {
?>
			<nav class="pagination clearfix">
				<div class="nav-previous"><?php next_posts_link( __( 'Older posts <span class="meta-nav">&rarr;</span>', 'capsule' ) ); ?></div>
				<div class="nav-next"><?php previous_posts_link( __( '<span class="meta-nav">&larr;</span> Newer posts', 'capsule' ) ); ?></div>
			</nav>
<?php
	}
}
else if (is_search()) {
?>
			<p class="search-no-results-msg"><?php _e('Nothing to see here&hellip; move along.', 'capsule'); ?></p>
<?php
}

?>
		</div>
	</div>
	
</div>

<footer>
	<p><a href="http://crowdfavorite.com/capsule/">Capsule</a> by <a href="http://crowdfavorite.com">Crowd Favorite</a></p>
</footer>

<div id="projects">
	<h2><?php _e('Projects', 'capsule'); ?></h2>
	<ul>
<?php
wp_list_categories(array(
	'show_option_none' => __('(none)', 'capsule'),
	'taxonomy' => 'projects',
	'title_li' => ''
));
?>
	</ul>
</div>
<div id="tags">
	<h2><?php _e('Tags', 'capsule'); ?></h2>
	<ul>
<?php
wp_list_categories(array(
	'show_option_none' => __('(none)', 'capsule'),
	'taxonomy' => 'post_tag',
	'title_li' => ''
));
?>
	</ul>
</div>
<div class="connection-error"><?php _e('Lost connection to server.', 'capsule'); ?></div>

<?php wp_footer(); ?>

</body>
</html>