<?php
/*
Plugin Name: WP Places Manager
Plugin URI: https://github.com/jlethuau/wp-places-manager
Description: A wordpress plugin to easily manage places in Wordpress using cutom post types.
Version: 0.1-sample
Author: mbamultimedia
Author URI: http://www.mba-multimedia.com/
Author Email: contact@mba-multimedia.com
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

require_once('widget-list.php');
// require_once('widget-find.php'); // TODO: Ajouter d'autres widgets (ex. Recherche, affichage d'un lieu...)

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

		// Use Wordpress included jQuery library
		wp_enqueue_script('jquery');

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

		// Ajout custom post type : Place (= lieux)
	    add_action( 'init', array( $this, 'register_place_custom_post_type' ) );

	    // Ajout d'attributs particuliers sur ce nouveau type
	    // puis traitement des données attributs saisies
	    add_action( 'add_meta_boxes',  array( $this, 'places_geoloc_add_custom_box' ) ); /* add_action( 'admin_init', array( $this, 'places_geoloc_add_custom_box' ) ); */ // Before WP 3.0
		add_action( 'save_post', array( $this, 'places_geoloc_save_postdata' ) );
	    add_action( 'add_meta_boxes',  array( $this, 'places_address_add_custom_box' ) );
		add_action( 'save_post', array( $this, 'places_address_save_postdata' ) );
	    add_action( 'add_meta_boxes',  array( $this, 'places_media_add_custom_box' ) );
		add_action( 'save_post', array( $this, 'places_media_save_postdata' ) );

		// Exemple filtre
	    // add_filter( 'TODO', array( $this, 'filter_method_name' ) );

	    // Utilisation d'un template custom pour les lieux à la place du template par défaut de WP
		// add_filter( 'template_include', array( $this, 'get_places_template' ) );
		// TODO: A activer au besoin si comportement basique du thème inacceptable

	    // Personalisation des colonnes affichées dans le back
	    add_filter( 'manage_edit-place_columns', array( $this, 'admin_places_columns' ) );
		// Remplissage des nouvelles colonnes
		add_action( 'manage_posts_custom_column', array( $this, 'populate_places_columns' ) );
		// Permettre le tri des colonnes ajoutées
		add_filter( 'manage_edit-place_sortable_columns', array( $this, 'sort_places_columns' ) );
		/*
		Pour un tri différent du tri alphabétique :
		http://wp.tutsplus.com/tutorials/plugins/a-guide-to-wordpress-custom-post-types-taxonomies-admin-columns-filters-and-archives/
		*/

		// Filtrage selon la catégorie de lieu dans le panel d'admin
		add_action( 'restrict_manage_posts', array( $this, 'places_filter_list' ) );
		// Affichage suite au filtrage
		add_filter( 'parse_query',array( $this, 'perform_places_category_filtering' ) );

		// Mettre a jour les messages du back pour être cohérent avec le custom post type
		add_filter( 'post_updated_messages', array( $this, 'get_places_custom_messages' ) );

		// Affiche une aide contextuelle pour le custom post type
		add_action( 'contextual_help', array( $this, 'add_places_help_text' ), 10, 3 );

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

		wp_enqueue_style( 'mba-places-manager-admin-styles', plugins_url( dirname( plugin_basename( __FILE__ ) ) . '/css/admin.css' ) );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		//wp_enqueue_script( 'mba-places-manager-admin-script', plugins_url( dirname( plugin_basename( __FILE__ ) ) . '/js/admin.js' ) );

		wp_register_script( 'mba-places-manager-admin-script', plugins_url( dirname( plugin_basename( __FILE__ ) ) . '/js/admin.js' ) );
		wp_enqueue_script( 'mba-places-manager-admin-script' );

		// Gestion de l'upload des meta de type fichiers
		//wp_register_script( 'custom_admin_script', get_template_directory_uri() . '/js/admin.js' );
		//wp_enqueue_script( 'custom_admin_script' );

	} // end register_admin_scripts

	/**
	 * Registers and enqueues plugin-specific styles.
	 */
	public function register_plugin_styles() {

		wp_enqueue_style( 'mba-places-manager-plugin-styles', plugins_url( dirname( plugin_basename( __FILE__ ) ) . '/css/display.css' ) );

	} // end register_plugin_styles

	/**
	 * Registers and enqueues plugin-specific scripts.
	 */
	public function register_plugin_scripts() {

		wp_enqueue_script( 'mba-places-manager-plugin-script', plugins_url( dirname( plugin_basename( __FILE__ ) ) . '/js/display.js' ) );

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
	function register_place_custom_post_type() {

		// Ajout custom type "place"

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
				'description' => __('Décrit un lieu et les caractéristiques de ce lieu', 'mba-places-manager-locale'),
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'supports' => array( 'title', 'editor', 'thumbnail' ),
	            // 'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
				// 'menu_position' => 5,
	            'taxonomies' => array( 'post_tag' ), // array( 'category', 'post_tag' ), // Attention : Ces taxonomies seront partagés avec les articles !
				'has_archive' => true,
			);

		register_post_type('place',$args);

		// Ajout custom taxonomy (Exemple : categories pour classer les lieux)
		// Note : les catégories pourraients être gérées en natif via : 'taxonomies' => array( 'category', 'post_tag' )
		// A réserver donc à un nouveau type de taxonomie (3 dans WP en natif : categories, tags et link categories)
		// ou si l'on souhaite avoir des catégories et tags différents entre les post_types

		$labels = array(
				'name' => _x( 'Catégories de lieu', 'taxonomy general name', 'mba-places-manager-locale' ),
				'singular_name' => _x( 'Catégorie de lieu', 'taxonomy singular name', 'mba-places-manager-locale' ),
				'search_items' =>  __( 'Rechercher catégorie', 'mba-places-manager-locale' ),
				'all_items' => __( 'Toutes les catégories', 'mba-places-manager-locale' ),
				'parent_item' => __( 'Parent', 'mba-places-manager-locale' ),
				'parent_item_colon' => __( 'Parent :', 'mba-places-manager-locale' ),
				'edit_item' => __( 'Editer catégorie', 'mba-places-manager-locale' ),
				'update_item' => __( 'Mettre à jour la catégorie', 'mba-places-manager-locale' ),
				'add_new_item' => __( 'Ajouter nouvelle catégorie', 'mba-places-manager-locale' ),
				'new_item_name' => __( 'Nouvelle catégorie', 'mba-places-manager-locale' ),
				'menu_name' => __( 'Catégories de lieux', 'mba-places-manager-locale' )
			);

		register_taxonomy(
				'places_categories',
				array('place'),
				array(
					'hierarchical' => true,
					'labels' => $labels,
					'query_var' => true,
					'show_ui' => true
				)
			);
	}

	/*--------------------------------------------*
	 * Attributs des lieux
	 *---------------------------------------------*/

	// Cf. http://wp.smashingmagazine.com/2012/11/08/complete-guide-custom-post-types/

	// Box coordonnées adresse postale -----------------------------

	function places_address_add_custom_box() {
		add_meta_box(
				"address-meta",
				__( "Coordonnées postales", 'mba-places-manager-locale' ),
				array( $this , "address_custom_box" ),
				"place",
				"normal",
				"low"
			);
	}

	// Création custom panel adresse

	function address_custom_box( $post ) {

		// Use nonce for verification
  		wp_nonce_field( plugin_basename( __FILE__ ), 'places_manager_noncename' );

		$custom_fields = get_post_custom( $post->ID );

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

	function places_address_save_postdata( $post_id ) {

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// TODO: Ajouter vérification nonce_field

		// TODO: Vérifier droit user a modifier cet objet

		update_post_meta( $post_id, "street", $_POST["street"] );
		update_post_meta( $post_id, "city", $_POST["city"] );
		update_post_meta( $post_id, "country", $_POST["country"] );
	}

	// Fin coordonnées adresse postale ----------------------------

	// Box coordonnées geoloc -------------------------------------

	function places_geoloc_add_custom_box() {
		add_meta_box(
				"geoloc-meta",
				__( "Coordonnées géographiques", 'mba-places-manager-locale' ),
				array( $this , "geoloc_custom_box" ),
				"place",
				"side", // Sidebar
				"low"
			);
	}

	// Création custom panel geoloc

	function geoloc_custom_box( $post ) {

		// Use nonce for verification
  		wp_nonce_field( plugin_basename( __FILE__ ), 'places_manager_noncename' );

		$custom_fields = get_post_custom($post->ID);

		$coord_lat = floatval( $custom_fields["coord-lat"][0] );
		$coord_lng = floatval( $custom_fields["coord-lng"][0] );

		// TODO Carte et scripts à coder
		echo '<div id="map-input"><!-- Maps Google maps pour aide à la localisation --></div>';

		echo '<label for="coord-lat">' . __('Latitude :', 'mba-places-manager-locale') . '</label>';
		echo '<input id="coord-lat" name="coord-lat" type="text" value="' . $coord_lat .'" />' . '<br />';

		echo '<label for="coord-lng">' . __('Longitude :', 'mba-places-manager-locale') . '</label>';
		echo '<input id="coord-lng" name="coord-lng" type="text" value="' . $coord_lng . '" />';
	}

	// Sauvegarde des données geoloc

	function places_geoloc_save_postdata( $post_id ) {

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// TODO: Ajouter vérification nonce_field

		// TODO: Vérifier droit user a modifier cet objet

		// FIXME: Ajouter un test de valeur (float)
		update_post_meta( $post_id, "coord-lat", $_POST["coord-lat"] );
		update_post_meta( $post_id, "coord-lng", $_POST["coord-lng"] );
	}

	// Fin coordonnées geoloc -------------------------------------

	// Box fichiers liés (fichiers, images, videos...) ------------

	function places_media_add_custom_box() {
		add_meta_box(
				"media-meta",
				__( "Fichiers liés", 'mba-places-manager-locale' ),
				array( $this , "media_custom_box" ),
				"place",
				"normal",
				"low"
			);
	}

	// Création custom panel media

	function media_custom_box( $post ) {
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'places_manager_noncename' );
		$custom_fields = get_post_custom( $post->ID );

		$actual_file  = $custom_fields["media-meta"][0];

		echo '<input id="media-meta" type="file" name="media-meta" value="" size="25" />';

		// FIXME: Voir comment utiliser json_decode() pour des raisons de sécurité à la place du unserialize()
		if ( !empty( $actual_file ) ) {
			$array_file_meta = unserialize( $actual_file );
			echo '<br/>' . '<label>Type fichier lié :</label>' . $array_file_meta[ 'type' ];
			echo '<br/>' . '<label>URL fichier lié :</label>'  . $array_file_meta[ 'url' ];
		}

		// TODO: Permettre également la suppression du fichier (cf. http://codex.wordpress.org/Function_Reference/delete_post_meta)
		// TODO: Améliorer en permettant l'upload multiple ?
	}

	// Sauvegarde des données media

	function places_media_save_postdata( $post_id ) {

		// Verifications sécurité
		if( !wp_verify_nonce( $_POST['places_manager_noncename'], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if( 'place' == $_POST['post_type']) {
			if( !current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		if( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		// Fin verifications sécurité

		$this->update_custom_meta_data( $post_id, 'media-meta', true );
	}

	// Gestion particulière pour les meta de type fichiers

	function update_custom_meta_data( $post_id, $data_key, $is_file = false ) {
		// FIXME: Le test fichier vide ne parait pas fonctionner correctement (Ici ou dans admin.js ?)
		if( $is_file && !empty( $_FILES ) ) {

			$upload = wp_handle_upload( $_FILES[$data_key], array( 'test_form' => false ) );

			if( isset( $upload['error'] ) && '0' != $upload['error'] ) {
				wp_die( __("Une erreur est survenue lors de l'upload de votre fichier.",'mba-places-manager-locale') . var_dump($_FILES) );
			} else {
				update_post_meta( $post_id, $data_key, $upload );
			}
		}
	}

	// Fin fichiers liés ------------------------------------------

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *		  WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *		  Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 */

	/**
	 * Surcharge des messages par défaut dans le back
	 */
	function get_places_custom_messages ( $messages ) {
		global $post, $post_ID;
		$messages['place'] = array(
				0 => '',
				1 => sprintf( __('Lieu mis à jour. <a href="%s">Voir le lieu</a>'), esc_url( get_permalink($post_ID) ) ),
				2 => __('Champ mis à jour.'),
				3 => __('Champ supprimé.'),
				4 => __('Lieu mis à jour.'),
				5 => isset($_GET['revision']) ? sprintf( __('Lieu restauré à partir de sa revision %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __('Lieu publié. <a href="%s">Voir lieu</a>'), esc_url( get_permalink($post_ID) ) ),
				7 => __('Lieu sauvegardé.'),
				8 => sprintf( __('Lieu soumis. <a target="_blank" href="%s">Prévisualiser lieu</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				9 => sprintf( __('Lieu programmé pour : <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview product</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
				10 => sprintf( __('Brouillon du lieu mis à jour. <a target="_blank" href="%s">Prévisualiser lieu</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}

	function get_places_template( $places_template ) {
		// Exemple de code possible
		/*if ( get_post_type() == 'place' ) {
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
		if ($post->post_type == 'place') {
		    if ( is_single() ) {
		    	// Template pour la fiche "lieu"
				$places_template = dirname( __FILE__ ) . '/single-place.php';
			}
			else {
		    	// Template pour le listing "lieu"
				$places_template = dirname( __FILE__ ) . '/places.php';
			}
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
		if ( $screen->post_type == 'place' ) {
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

	/**
	 * Définit le contenu HTML des zones d'aide contextuelle (En ahut des écrans d'admin)
	 */
	function add_places_help_text ( $contextual_help, $screen_id, $screen ) {
		//$contextual_help .= var_dump( $screen ); // use this to help determine $screen->id
		if ( 'place' == $screen->id ) {
			$contextual_help =
			'<p>' . __("Ce qu'il faut savoir concernant l'édition et la manipulation des lieux :", 'mba-places-manager-locale') . '</p>' .
			'<ul>' .
			'<li>' . __('Entrez le nom du lieu.', 'mba-places-manager-locale') . '</li>' .
			'<li>' . __('Saisissez les informations géographiques (Géolocalisation, adresse).', 'mba-places-manager-locale') . '</li>' .
			'<li>' . __('A compléter...', 'mba-places-manager-locale') . '</li>' .
			'</ul>' .
			'<p>' . __('Si vous souhaitez plannifier la publication du lieu à une date future :', 'mba-places-manager-locale') . '</p>' .
			'<ul>' .
			'<li>' . __('Dans le bloc de publication, cliquez sur le lien Modifier sur la ligne dédiée à la date de publication.', 'mba-places-manager-locale') . '</li>' .
			'<li>' . __('Modifiez la date en renseignant la date à laquelle vous souhaitez que le lieu soit publié automatiquement, puis cliquez sur Ok.', 'mba-places-manager-locale') . '</li>' .
			'</ul>' .
			'<p><strong>' . __("Pour plus d'information :", 'mba-places-manager-locale') . '</strong></p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Posts_Edit_SubPanel" target="_blank">Documentation Wordpress (en)</a>', 'mba-places-manager-locale') . '</p>' .
			'' ;
		}
		elseif ( 'edit-place' == $screen->id )
		{
			$contextual_help =
			'<p>' . __('This is the help screen displaying the table of books blah blah blah.', 'mba-places-manager-locale') . '</p>' ;
		}
		return $contextual_help;
	}

} // end class

// Instantiation call of the plugin
$wp_places_manager = new PlacesManager();
