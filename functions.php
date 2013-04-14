<?php

define('CAPSULE_URL_VERSION', '2.3');
define('CAPSULE_TAX_PREFIX_PROJECT', '@');
define('CAPSULE_TAX_PREFIX_TAG', '#');
define('CAPSULE_TAX_PREFIX_CODE', '`');

if (!defined('CAPSULE_SERVER') || !CAPSULE_SERVER) {
	include('controller.php');
}
include_once('lib/wp-taxonomy-filter/taxonomy-filter.php');

show_admin_bar(false);

function capsule_gatekeeper() {
	if (!current_user_can('publish_posts')) {
		$login_page = wp_login_url();
		is_ssl() ? $proto = 'https://' : $proto = 'http://';
		$requested = $proto.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if (substr($requested, 0, strlen($login_page)) != $login_page) {
			auth_redirect();
		}
	}
}
add_action('init', 'capsule_gatekeeper', 9999);

function capsule_login_duration() {
    return 2592000; // 30 * 24 * 60 * 60 = 30 days
}
add_filter('auth_cookie_expiration', 'capsule_login_duration');

function capsule_unauthorized_json() {
	header('Content-type: application/json');
	echo json_encode(array(
		'result' => 'unauthorized',
		'msg' => __('Please log in.', 'capsule'),
		'login_url' => wp_login_url(),
	));
	die();
}

function capsule_resources() {
	$template_url = trailingslashit(get_template_directory_uri()).'ui/';
	$assets_url = trailingslashit($template_url . 'assets');

	// Styles
	wp_enqueue_style(
		'capsule_styles',
		$assets_url.'css/style.css',
		array(),
		CAPSULE_URL_VERSION
	);

	// Scripts
	wp_enqueue_script('jquery');
	wp_enqueue_script('suggest');

	// require.js enforces JS module dependencies, heavily used in 
	// loading Ace and related code
	wp_enqueue_script(
		'requirejslib',
		$template_url.'lib/require.js',
		array(),
		CAPSULE_URL_VERSION,
		true
	);
	wp_enqueue_script(
		'requirejs',
		$assets_url.'js/requirejs_config.js',
		array('requirejslib'),
		CAPSULE_URL_VERSION,
		true
	);
	wp_localize_script('requirejs', 'requirejsL10n', array(
		'capsule' => $assets_url,
		'ace' => $template_url.'lib/ace/lib/ace',
		'lib' => $template_url.'lib',
		'cachebust' => urlencode(CAPSULE_URL_VERSION),
	));
	// Load the partially pre-built ace to speed up loading times,
	// even though we pull things in with require.js from the
	// unbuilt code
	wp_enqueue_script(
		'ace',
		$template_url.'lib/ace/build/src/ace.js',
		array('requirejs'),
		CAPSULE_URL_VERSION,
		true
	);
	wp_enqueue_script(
		'capsule',
		$assets_url.'js/capsule.js',
		array('jquery', 'twitter-text', 'suggest', 'ace'),
		CAPSULE_URL_VERSION,
		true
	);
	wp_localize_script('capsule', 'capsuleL10n', array(
		'endpointAjax' => home_url('index.php'),
		'loading' => __('Loading...', 'capsule'),
	));

	wp_enqueue_script(
		'php-date',
		$template_url.'lib/phpjs/functions/datetime/date.js',
		array(),
		CAPSULE_URL_VERSION,
		true
	);
	wp_enqueue_script(
		'twitter-text',
		$template_url.'lib/twitter-text-js/twitter-text.js',
		array('jquery'),
		CAPSULE_URL_VERSION,
		true
	);

	wp_enqueue_script(
		'json',
		$template_url.'lib/json-js/json2.js',
		array(),
		CAPSULE_URL_VERSION,
		true
	);
	wp_enqueue_script(
		'jquery-scrollto',
		$assets_url.'js/jquery.scrollTo-1.4.2-min.js',
		array('jquery'),
		CAPSULE_URL_VERSION,
		true
	);
	wp_enqueue_script(
		'jquery-scrollintoview',
		$template_url.'lib/jquery-scrollintoview/jquery.scrollintoview.min.js',
		array('jquery'),
		CAPSULE_URL_VERSION,
		true
	);
	wp_enqueue_script(
		'sidr',
		$template_url.'lib/sidr/dist/jquery.sidr.js',
		array('jquery'),
		CAPSULE_URL_VERSION,
		true
	);
	wp_enqueue_script(
		'sidr',
		$template_url.'lib/sidr/dist/jquery.sidr.js',
		array('jquery'),
		CAPSULE_URL_VERSION,
		true
	);
	if (!defined('CAPSULE_SERVER') || !CAPSULE_SERVER) {
		wp_enqueue_script('heartbeat');
	}
}
add_action('wp_enqueue_scripts', 'capsule_resources');

function capsule_wp_head() {
?>
<style>
.spinner {
	background: url(<?php echo admin_url('images/loading.gif'); ?>) no-repeat center center;
	background-size: 12px;
}
</style>
<?php
}
add_action('wp_head', 'capsule_wp_head');

function capsule_register_taxonomies() {
	register_taxonomy(
		'projects',
		'post',
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => __('Projects', 'capsule'),
				'singular_name' => __('Project', 'capsule'),
				'search_items' => __('Search Projects', 'capsule'),
				'popular_items' => __('Popular Projects', 'capsule'),
				'all_items' => __('All Projects', 'capsule'),
				'parent_item' => __('Parent Project', 'capsule'),
				'parent_item_colon' => __('Parent Project:', 'capsule'),
				'edit_item' => __('Edit Project', 'capsule'),
				'update_item' => __('Update Project', 'capsule'),
				'add_new_item' => __('Add New Project', 'capsule'),
				'new_item_name' => __('New Project Name', 'capsule'),
			),
			'sort' => true,
			'args' => array('orderby' => 'term_order'),
			'rewrite' => array(
				'slug' => 'projects',
				'with_front' => false,
			),
		)
	);
	register_taxonomy(
		'code',
		'post',
		array(
			'hierarchical' => false,
			'labels' => array(
				'name' => __('Code Languages', 'capsule'),
				'singular_name' => __('Code Language', 'capsule'),
				'search_items' => __('Search Code Languages', 'capsule'),
				'popular_items' => __('Popular Code Languages', 'capsule'),
				'all_items' => __('All Code Languages', 'capsule'),
				'parent_item' => __('Parent Code Language', 'capsule'),
				'parent_item_colon' => __('Parent Code Language:', 'capsule'),
				'edit_item' => __('Edit Code Language', 'capsule'),
				'update_item' => __('Update Code Language', 'capsule'),
				'add_new_item' => __('Add New Code Language', 'capsule'),
				'new_item_name' => __('New Code Language Name', 'capsule'),
			),
			'sort' => true,
			'args' => array('orderby' => 'term_order'),
			'rewrite' => array(
				'slug' => 'code',
				'with_front' => false,
			),
		)
	);
	register_taxonomy(
		'evergreen',
		'post',
		array(
			'hierarchical' => true,
			'labels' => array(
				'name' => __('Evergreen', 'capsule'),
				'singular_name' => __('Evergreen Status', 'capsule'),
				'search_items' => __('Search Evergreen Status', 'capsule'),
				'popular_items' => __('Popular Evergreen Status', 'capsule'),
				'all_items' => __('All Evergreen Status', 'capsule'),
				'parent_item' => __('Parent Evergreen Status', 'capsule'),
				'parent_item_colon' => __('Parent Evergreen Status:', 'capsule'),
				'edit_item' => __('Edit Evergreen Status', 'capsule'),
				'update_item' => __('Update Evergreen Status', 'capsule'),
				'add_new_item' => __('Add New Evergreen Status', 'capsule'),
				'new_item_name' => __('New Evergreen Status Name', 'capsule'),
			),
			'sort' => true,
			'args' => array('orderby' => 'term_order'),
			'rewrite' => array(
				'slug' => 'evergreen',
				'with_front' => false,
			),
			'show_ui' => false,
		)
	);
}
add_action('init', 'capsule_register_taxonomies');

function capsule_get_the_terms($terms, $id, $taxonomy) {
	if (is_array($terms) && count($terms)) {
		$prefix = null;
		switch ($taxonomy) {
			case 'projects':
				$prefix = CAPSULE_TAX_PREFIX_PROJECT;
				break;
			case 'post_tag':
				$prefix = CAPSULE_TAX_PREFIX_TAG;
				break;
			case 'code':
				$prefix = CAPSULE_TAX_PREFIX_CODE;
				break;
		}
		$_terms = array();
		foreach ($terms as $term_id => $term) {
			if (!empty($prefix)) {
				if (substr($term->name, 0, strlen($prefix)) != $prefix) {
					$term->name = $prefix.$term->name;
				}
			}
			$_terms[$term_id] = $term;
		}
		$terms = $_terms;
	}
	return $terms;
}
add_filter('get_the_terms', 'capsule_get_the_terms', 10, 3);

function capsule_term_list($post_id, $taxonomy) {
	if (($tax_terms = get_the_terms($post_id, $taxonomy)) != false) {
		if ($taxonomy == 'post_tag') {
			return get_the_term_list($post_id, $taxonomy, '<ul class="post-meta-tags"><li>', '</li><li>', '</li></ul>'); 
		} else {
			return get_the_term_list($post_id, $taxonomy, '<ul><li>', '</li><li>', '</li></ul>'); 
		}
	}
	else {
		return '';
	}
}

function capsule_the_content_markdown($content) {
	include_once(STYLESHEETPATH.'/ui/lib/php-markdown/markdown_extended.php');
	return MarkdownExtended($content);
}
add_filter('the_content', 'capsule_the_content_markdown');
remove_filter('the_content', 'wpautop');
remove_filter('the_content', 'wptexturize');

function capsule_trim_excerpt($excerpt) {
	$max = 500;
	if (strlen($excerpt) > $max) {
		$excerpt = substr($excerpt, 0, $max);
	}
	return $excerpt;
}
add_filter('get_the_excerpt', 'capsule_trim_excerpt');

// Generate the searchurl for the frontend
function capsule_header_js() {
?>
<script type="text/javascript">
var capsuleSearchURL = '<?php echo home_url(); ?>';
</script>
<?php 
}
if (!is_admin()) {
	add_action('wp_head', 'capsule_header_js');
}

function capsule_taxonomy_filter() {
	if (!class_exists('CF_Taxonomy_Filter')) {
		return;
	}
	$args = array(
		'taxonomies' => array(
			'projects' => array(
				'prefix' => '@',
			),
			'post_tag' => array(
				'prefix' => '#',
			),
			'code' => array(
				'prefix' => '`',
			),
		),
	);

	CF_Taxonomy_Filter::start_form();

	echo '<div class="cftf-options"><span class="label">'.__('Options', 'capsule').'</span>';

	foreach ($args['taxonomies'] as $taxonomy => $tax_args) {
		if (is_array($args)) {
			CF_Taxonomy_Filter::tax_filter($taxonomy, $tax_args);
		}
		// Just passed in taxonomy name with no options
		else {
			CF_Taxonomy_Filter::tax_filter($args);
		}
	}

	CF_Taxonomy_Filter::author_select();

	echo '</div>';
	echo '<div class="cftf-dates"><span class="label">'.__('Date Range', 'capsule').'</span>';

	CF_Taxonomy_Filter::date_filter();

	echo '</div>';
	echo '<div class="cftf-submit">';

	CF_Taxonomy_Filter::submit_button();

	echo '</div>';

	CF_Taxonomy_Filter::end_form();
}

function capsule_tax_filter_url() {
	return get_template_directory_uri().'/ui/lib/wp-taxonomy-filter/';
}
add_filter('cftf_url', 'capsule_tax_filter_url');

function capsule_credits() {
?>
		<ul>
			<li><a href="http://ajaxorg.github.io/ace/">Ace Code Editor</a> (<a href="https://github.com/ajaxorg/ace">GitHub</a>)</li>
			<li><a href="http://harvesthq.github.io/chosen/">Chosen</a> (<a href="https://github.com/harvesthq/chosen">GitHub</a>)</li>
			<li><a href="http://michelf.ca/projects/php-markdown/extra/">PHP Markdown Extra</a> (<a href="https://github.com/michelf/php-markdown">GitHub</a>)</li>
			<li>Twitter Text JS (<a href="https://github.com/twitter/twitter-text-js">GitHub</a>)</li>
			<li>jQuery .scrollintoview() (<a href="https://github.com/litera/jquery-scrollintoview">GitHub</a>)</li>
			<li>JSON in JavaScript (<a href="https://github.com/douglascrockford/JSON-js">GitHub</a>)</li>
			<li><a href="http://requirejs.org/">RequireJS</a> (<a href="https://github.com/jrburke/requirejs">GitHub</a>)</li>
			<li><a href="http://www.berriart.com/sidr/">Sidr</a> (<a href="https://github.com/artberri/sidr">GitHub</a>)</li>
			<li><a href="http://sass-lang.com/">Sass</a> (<a href="https://github.com/nex3/sass">GitHub</a>)</li>
			<li><a href="http://www.google.com/fonts/specimen/Source+Sans+Pro">Source Sans Pro</a> (<a href="https://github.com/adobe/source-sans-pro">GitHub</a>)</li>
			<li><a href="http://www.google.com/fonts/specimen/Source+Code+Pro">Source Code Pro</a> (<a href="https://github.com/adobe/source-code-pro">GitHub</a>)</li>
			<li><a href="http://fontello.com">Fontello</a> (<a href="https://github.com/fontello/fontello">GitHub</a>) &amp; Fontelico (<a href="https://github.com/fontello/fontelico.font">GitHub</a>)</li>
			<li><a href="http://aristeides.com/">Elusive</a> (<a href="https://github.com/aristath/elusive-iconfont">GitHub</a>)</li>
			<li><a href="http://entypo.com/">Entypo</a> (<a href="https://github.com/danielbruce/entypo">GitHub</a>)</li>
			<li><a href="http://somerandomdude.com/work/iconic/">Iconic</a> (<a href="https://github.com/somerandomdude/Iconic">GitHub</a>)</li>
			<li><a href="http://www.justbenicestudio.com/studio/websymbols/">Web Symbols</a></li>
		</ul>
<?php
}