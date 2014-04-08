<?php
function create_fwddc_artist_post_type() {
	$labels = array(
		'name' => __( 'Artists' ),
		'singular_name' => __( 'Artist' ),
	);
	$support = array(
		'title',
		'editor',
		'thumbnail',
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'menu_name' => 'Artists',
		'menu_position' => 6,
		'menu_icon' => 'dashicons-format-audio',
		'has_archive' => true,
		'add_new_item' => __("Add New Artist", 'roots'),
		'edit_item' => __('Edit Artist', 'roots'),
		'new_item' => __('New Artist', 'roots'),
		'view_item' => __('View Artist', 'roots'),	
		'rewrite' => array('slug' => 'artists'),
		'supports' => $support,
		'capability_type' => 'page',
	);
	register_post_type( 'fwddc_artist', $args);
}
add_action( 'init', 'create_fwddc_artist_post_type' );


/**
 * Custom Taxonomies for Artist post type
 */
 
 /**
  * Genres Taxonomy
  */
function register_fwddc_artist_genre_taxonomy() {
	$labels = array (
        'name' 			=> _x( "Genres", 'taxonomy general name', 'roots' ),
        'singular_name' => _x( 'Genre', 'taxonomy singular name', 'roots' ),
        'search_items' 	=> __( 'Search Genres', 'roots' ),
        'all_items' 	=> __( 'All Genres', 'roots' ),
        'parent_item' 	=> __( 'Parent Genre', 'roots'),
        'parent_item_colon' => __( 'Parent Genre:', 'roots'),
        'edit_item' 	=> __( 'Edit Genre', 'roots'),
        'update_item' 	=> __( 'Update Genre', 'roots'),
        'add_new_item' 	=> __( 'Add New Genre', 'roots'),
		'new_item_name' => __( 'New Genre Name', 'roots'),
		'menu_name'		=> __( 'Genre', 'roots' ),
    );
	$args = array (
     	'labels' => $labels,
        'hierarchical' =>true,
        'show_ui' => true,
        'show_tagcloud' => true,
        'show_admin_column' => true,
        'rewrite' => array( 'slug' => 'genre' ),
        'public'=>true,
    );
	register_taxonomy('fwddc_artist_genre', 'fwddc_artist', $args );
}
add_action('init', 'register_fwddc_artist_genre_taxonomy');


/**
 * Events Taxonomy
 */
function register_fwddc_events_taxonomy(){
	$labels = array (
        'name' 			=> _x( 'Events', 'taxonomy general name', 'roots' ),
        'singular_name' => _x( 'Event', 'taxonomy singular name', 'roots' ),
        'search_items' 	=> __( 'Search Events', 'roots' ),
        'all_items' 	=> __( 'All Events', 'roots' ),
        'edit_item' 	=> __( 'Edit Event', 'roots'),
        'update_item' 	=> __( 'Update Event', 'roots'),
        'add_new_item' 	=> __( 'Add New Event', 'roots'),
		'new_item_name' => __( 'New Event', 'roots'),
		'menu_name'		=> __( 'Events', 'roots' ),
    );
	$args = array (
     	'labels' => $labels,
        'hierarchical' => false,
        'separate_items_with_commas' => __('Separate events with commas'),
        'show_ui' => true,
        'show_tagcloud' => false, 
        'show_admin_column' => true,
        'show_in_nav_menus' => false,
        'query_var' => 'event',
        'rewrite' => array( 'slug' => 'events' ),
        'public'=>true,
    );
    $post_types = array(
    	'fwddc_artist',
    	'fwddc_venue',
    );
	register_taxonomy( 'fwddc_events', $post_types, $args );
}
add_action('init', 'register_fwddc_events_taxonomy');

function populate_fwddc_events_taxonomy(){
	/**
	 * The WordPress Query class.
	 * @link http://codex.wordpress.org/Function_Reference/WP_Query
	 *
	 */
	$q_args = array(
		//Type & Status Parameters
		'post_status' => 'any',
		'post_type' => array(
			'fwddc_event',
			),
		//Order & Orderby Parameters
		'order'               => 'ASC',
		'orderby'             => 'name',
		'ignore_sticky_posts' => false,

		//Pagination Parameters
		'posts_per_page'         => -1,
		'nopaging'               => true,

		//Permission Parameters -
		'perm' => 'readable',
		
		//Parameters relating to caching
		'cache_results'          => true,
		'update_post_term_cache' => true,
		'update_post_meta_cache' => true,
	);
	
	$query = new WP_Query( $q_args );
	if ($query->have_posts()){
		while ($query->have_posts()){
			$query->the_post();
			addTaxTerm(get_the_title(), 'fwddc_events', array( 'description' => get_the_excerpt()));
		}
		wp_reset_postdata();
	}
}
add_action('admin_init','populate_fwddc_events_taxonomy');


function create_term_on_publish($post_id) {
    if( ( $_POST['post_status'] == 'publish' ) && ( $_POST['original_post_status'] != 'publish' ) ) {
    	$post = get_post($post_id);
    	$taxo = false;
    	$ptype = get_post_type($post);
    	switch($ptype) {
    		case "fwddc_event":
    			$taxo = 'fwddc_artists';
    			break;
    		case "fwddc_artist":
    			$taxo = 'fwddc_events';
    			break;
    		default:
    			break;
    	}
    	if ($taxo){
    		$tit = get_the_title($post);
    		$desc = get_the_excerpt( $post );
			addTaxTerm($tit, $taxo );
    	}
    	wp_reset_postdata();
    }
}
add_action('publish_post','create_term_on_publish');

function addTaxTerm($trm, $taxo, $args) {
	if (!($rtn=term_exists( $trm, $taxo ))) {
		$rtn = wp_insert_term( $trm, $taxo);
	}
	return $rtn;
}

/**
 * Admin Post Columns Config/Setup
 */
function set_custom_fwddc_artist_columns($columns) {
    unset( $columns['date'] );
    $columns['thumbnail'] = __( 'Photo', 'roots' );
    $columns['title'] = __( 'Artist Name', 'roots' );
    $columns['taxonomy-fwddc_events'] = __( 'Events', 'roots' );
    $columns['taxonomy-fwddc_artist_genre'] = __( 'Genres', 'roots' );

    return $columns;
}
add_filter('manage_fwddc_artist_posts_columns', 'set_custom_fwddc_artist_columns');

function fwddc_artist_custom_columns( $column, $post_id ) {
    switch ( $column ) {
		case 'thumbnail' :
			if (has_post_thumbnail( $post_id )) {
				echo get_the_post_thumbnail( $post_id, array(75,75), array() );
			} else {
				echo "<img src='http://fillmurray.com/75/75'>";
			}
			break;
		default:
			echo "<script>console.log($column);</script>";
			break;
    }
}
add_action( 'manage_fwddc_artist_posts_custom_column' , 'fwddc_artist_custom_columns', 10, 2 );

function posts_column_register_sortable( $columns ) {
    $columns['title'] = 'title';
    $columns['taxonomy-fwddc_event_year'] = 'taxonomy-fwddc_event_year';
    return $columns;
}
add_filter( 'manage_edit-fwddc_artist_posts_sortable_columns', 'posts_column_register_sortable' );