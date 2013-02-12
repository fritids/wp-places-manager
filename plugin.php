<?php
/*
Plugin Name: wp-places-manager
Plugin URI: https://github.com/jlethuau/wp-places-manager
Description: A wordpress plugin to easily manage places in Wordpress using cutom post types.
Version: 0.1-sample
Author: mbamultimedia
Author URI: http://www.mba-multimedia.com/
Author Email: TODO
License:

  Copyright 2013 MBA Multimedia (mbamultimedia@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

require_once('widget.php');

class PlacesManager {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'plugin_textdomain' ) );

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

		// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );

	    /*
	     * TODO:
	     * Define the custom functionality for your plugin. The first parameter of the
	     * add_action/add_filter calls are the hooks into which your code should fire.
	     *
	     * The second parameter is the function name located within this class. See the stubs
	     * later in the file.
	     *
	     * For more information:
	     * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
	     */
	    // Adding Post Thumbnail Support to the theme (Featured image)
		add_theme_support( 'post-thumbnails' );

		// Exemple action
	    //add_action( 'TODO', array( $this, 'action_method_name' ) );

		// Ajout custom post type : Places (= lieux)
	    add_action( 'init', array( $this, 'register_places' ) );

	    // Ajout d'attributs particuliers sur ce nouveau type
	    // puis traitement des données attributs saisies
	    add_action( 'add_meta_boxes',  array( $this, 'places_geoloc_add_custom_box' ) ); /* add_action( 'admin_init', array( $this, 'places_geoloc_add_custom_box' ) ); */ // Before WP 3.0
		add_action( 'save_post', array( $this, 'places_geoloc_save_postdata' ) );
	    add_action( 'add_meta_boxes',  array( $this, 'places_address_add_custom_box' ) );
		add_action( 'save_post', array( $this, 'places_address_save_postdata' ) );

		// Exemple filtre
	    // add_filter( 'TODO', array( $this, 'filter_method_name' ) );

	    // Utilisation d'un template custom pour les lieux à la place du template par défaut de WP
	    /*add_filter( 'places_template', array( $this, 'get_places_template' ) );*/
		add_filter( 'template_include', array( $this, 'get_places_template' ) );

	    // Personalisation des colonnes affichées dans le back
	    add_filter( 'manage_edit-places_columns', array( $this, 'admin_places_columns' ) );
		// Remplissage des nouvelles colonnes
		add_action( 'manage_posts_custom_column', array( $this, 'populate_places_columns' ) );
		// Permettre le tri des colonnes ajoutées
		add_filter( 'manage_edit-places_sortable_columns', array( $this, 'sort_places_columns' ) );
		/*
		Pour un tri différent du tri alphabétique :
		http://wp.tutsplus.com/tutorials/plugins/a-guide-to-wordpress-custom-post-types-taxonomies-admin-columns-filters-and-archives/
		*/

		// Filtrage selon la catégorie de lieu dans le panel d'admin
		add_action( 'restrict_manage_posts', array( $this, 'places_filter_list' ) );
		// Affichage suite au filtrage
		add_filter( 'parse_query',array( $this, 'perform_places_category_filtering' ) );

		// Ajout widget
		add_action( 'widgets_init', create_function( '', 'register_widget( "PlacesWidget" );' ) );

	} // end constructor

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function activate( $network_wide ) {
		// TODO:	Define activation functionality here
	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {
		// TODO:	Define deactivation functionality here
	} // end deactivate

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function uninstall( $network_wide ) {
		// TODO:	Define uninstall functionality here
	} // end uninstall

	/**
	 * Loads the plugin text domain for translation
	 */
	public function plugin_textdomain() {

		$domain = 'mba-places-manager-locale';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	} // end plugin_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		wp_enqueue_style( 'mba-places-manager-admin-styles', plugins_url( 'mba-places-manager/css/admin.css' ) );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		wp_enqueue_script( 'mba-places-manager-admin-script', plugins_url( 'mba-places-manager/js/admin.js' ) );

	} // end register_admin_scripts

	/**
	 * Registers and enqueues plugin-specific styles.
	 */
	public function register_plugin_styles() {

		wp_enqueue_style( 'mba-places-manager-plugin-styles', plugins_url( 'mba-places-manager/css/display.css' ) );

	} // end register_plugin_styles

	/**
	 * Registers and enqueues plugin-specific scripts.
	 */
	public function register_plugin_scripts() {

		wp_enqueue_script( 'mba-places-manager-plugin-script', plugins_url( 'mba-places-manager/js/display.js' ) );

	} // end register_plugin_scripts

	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/

	/**
 	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *		  WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *		  Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 */

	/**
	 * Ajout du custom post type 'places' et des ses attributs pour la gestion des lieux dans Wordpress
	 */
	function register_places() {

		// Ajout custom type "places"

		$labels = array(
			'name' => _x('Lieux', 'post type general name', 'mba-places-manager-locale'),
			'singular_name' => _x('Lieu', 'post type singular name', 'mba-places-manager-locale'),
			'add_new' => _x('Ajouter', 'Lieu', 'mba-places-manager-locale'),
			'add_new_item' => __('Ajouter nouveau lieu', 'mba-places-manager-locale'),
			'edit_item' => __('Editer lieu'),
			'new_item' => __('Nouveau lieu'),
			'all_items' => __('Tous les lieux'),
			'view_item' => __('Voir lieu', 'mba-places-manager-locale'),
			'search_items' => __('Rechercher lieu', 'mba-places-manager-locale'),
			'not_found' =>  __('Aucun lieu trouvé', 'mba-places-manager-locale'),
			'not_found_in_trash' => __('Aucun lieu dans la corbeille', 'mba-places-manager-locale'),
			'parent_item_colon' => '',
			'menu_name' => _x('Lieux', 'wordpress admin menu name', 'mba-places-manager-locale')
			);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array( 'title', 'editor', 'thumbnail' ),
            //'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
			);

		register_post_type('places',$args);

		// Ajout categories pour classer les lieux

		$labels = array(
			'name' => _x( 'Catégories de lieu', 'taxonomy general name', 'mba-places-manager-locale' ),
			'singular_name' => _x( 'Catégorie de lieu', 'taxonomy singular name', 'mba-places-manager-locale' ),
			'search_items' =>  __( 'Rechercher catégorie', 'mba-places-manager-locale' ),
			'all_items' => __( 'Toutes les catégories', 'mba-places-manager-locale' ),
			'parent_item' => __( 'Parent', 'mba-places-manager-locale' ),
			'parent_item_colon' => __( 'Parent:', 'mba-places-manager-locale' ),
			'edit_item' => __( 'Editer catégorie', 'mba-places-manager-locale' ),
			'update_item' => __( 'Update Product Category', 'mba-places-manager-locale' ),
			'add_new_item' => __( 'Ajouter nouvelle catégorie', 'mba-places-manager-locale' ),
			'new_item_name' => __( 'Nouvelle catégorie', 'mba-places-manager-locale' ),
			'menu_name' => __( 'Catégories de lieux', 'mba-places-manager-locale' )
			);

		register_taxonomy('places_categories',array('places'), array(
			'hierarchical' => true,
			'labels' => $labels,
			'query_var' => true,
			'show_ui' => true
			));
	}

	/*--------------------------------------------*
	 * Attributes des lieux
	 *---------------------------------------------*/

	// Box coordonnées adresse postale

	function places_address_add_custom_box() {
		add_meta_box("address-meta", __( "Coordonnées postales", 'mba-places-manager-locale' ), array( $this , "address_custom_box" ), "places", "normal", "low");
	}

	// Création custom panel adresse

	function address_custom_box( $place ) {

		// Use nonce for verification
  		//wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

		// global $post;
		$custom_fields = get_post_custom( $place->ID );

		$street = esc_html( $custom_fields["street"][0] );
		$city = esc_html( $custom_fields["city"][0] );
		$country = esc_html( $custom_fields["country"][0] );

		echo '<label for="street">' . __('Rue :', 'mba-places-manager-locale') . '</label>';
		echo '<input id="street" name="street" type="text" value="' . $street .'" /><br />';

		echo '<label for="city">' . __('Ville :', 'mba-places-manager-locale') . '</label>';
		echo '<input id="city" name="city" type="text" value="' . $city .'" /><br />';

		echo '<label for="country">' . __('Pays :', 'mba-places-manager-locale') . '</label>';
		echo '<input id="country" name="country" type="text" value="' . $country .'" />';
	}

	// Sauvegarde des données adresse

	function places_address_save_postdata() {
		/*if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return $post_id;*/
		global $post;
		update_post_meta($post->ID, "street", $_POST["street"]);
		update_post_meta($post->ID, "city", $_POST["city"]);
		update_post_meta($post->ID, "country", $_POST["country"]);
	}

	// Fin coordonnées adresse postale

	// Box coordonnées geoloc

	function places_geoloc_add_custom_box() {
		add_meta_box("geoloc-meta", __( "Coordonnées géographiques", 'mba-places-manager-locale' ), array( $this , "geoloc_custom_box" ), "places", "normal", "low");
	}

	// Création custom panel geoloc

	function geoloc_custom_box( $place ) {

		// Use nonce for verification
  		//wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

		//global $post;
		$custom_fields = get_post_custom($place->ID);

		$coord_x = floatval( $custom_fields["coord-x"][0] );
		$coord_y = floatval( $custom_fields["coord-y"][0] );

		// TODO Carte et scripts à coder
		echo '<div id="map-input"><!-- Maps Google maps pour aide à la localisation --></div>';

		echo '<label for="coord-x">' . __('Coordonnées géographiques X :', 'mba-places-manager-locale') . '</label>';
		echo '<input id="coord-x" name="coord-x" type="text" value="' . $coord_x .'" />';

		echo '<label for="coord-y">' . __('Coordonnées géographiques Y :', 'mba-places-manager-locale') . '</label>';
		echo '<input id="coord-y" name="coord-y" type="text" value="' . $coord_y . '" />';
	}

	// Sauvegarde des données geoloc

	function places_geoloc_save_postdata() {
		/*if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return $post_id;*/
		global $post;
		update_post_meta($post->ID, "coord-x", $_POST["coord-x"]);
		update_post_meta($post->ID, "coord-y", $_POST["coord-y"]);
	}

	// Fin coordonnées geoloc

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *		  WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *		  Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 */

	function get_places_template( $places_template ) {
		// Exemple de code possible
		/*if ( get_post_type() == 'places' ) {
		    if ( is_single() ) {
		    	// Template pour la fiche "lieu"

		        // checks if the file exists in the theme first,
		        // otherwise serve the file from the plugin
		        if ( $theme_file = locate_template( array ( 'single-places.php' ) ) ) {
		            $places_template = $theme_file;
		        } else {
		            $places_template = plugin_dir_path( __FILE__ ) . '/single-places.php';
		        }
		    }
		    else
		    {
		    	// TODO Ici autre template possible pour le listing
		    }
		}*/
		global $post;
		if ($post->post_type == 'places') {
			$places_template = dirname( __FILE__ ) . '/single-places.php';
		}

    	return $places_template;
	}

	function admin_places_columns( $columns ) {
    	$columns['city'] = __('Ville', 'mba-places-manager-locale' );
    	$columns['country'] = __('Pays', 'mba-places-manager-locale' );
    	// unset( $columns['comments'] ); // Masque la colonne comments
    	return $columns;
	}

	function populate_places_columns( $column ) {
		if ( 'city' == $column ) {
			$city = esc_html( get_post_meta( get_the_ID(), 'city', true ) );
			echo $city;
		}
		elseif ( 'country' == $column ) {
			$country = get_post_meta( get_the_ID(), 'country', true );
			echo $country;
		}
	}

	function sort_places_columns( $columns ) {
		$columns['city'] = 'city';
		$columns['country'] = 'country';

		return $columns;
	}

	function places_filter_list() {
		$screen = get_current_screen();
		global $wp_query;
		if ( $screen->post_type == 'places' ) {
			wp_dropdown_categories( array(
				'show_option_all' => __('Afficher toutes les catégories', 'mba-places-manager-locale' ),
				'taxonomy' => 'places_categories',
				'name' => 'places_categories',
				'orderby' => 'name',
				'selected' => ( isset( $wp_query->query['places_categories'] ) ? $wp_query->query['places_categories'] : '' ),
				'hierarchical' => false,
				'depth' => 3,
				'show_count' => false,
				'hide_empty' => false,
			) );
			/* http://codex.wordpress.org/Function_Reference/wp_dropdown_categories */
		}
	}

	function perform_places_category_filtering( $query ) {
		$qv = &$query->query_vars;
		if ( ( $qv['places_categories'] ) && is_numeric( $qv['places_categories'] ) ) {
			$term = get_term_by( 'id', $qv['places_categories'], 'places_categories' );
			$qv['places_categories'] = $term->slug;
		}
	}

} // end class

// Instantiation call of the plugin
$plugin_name = new PlacesManager();
