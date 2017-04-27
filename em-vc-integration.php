<?php
// based on https://github.com/easydigitaldownloads/EDD-Extension-Boilerplate
/**
 * Plugin Name: Events Manager Visual Composer Integration
 * Plugin URI:  https://github.com/nwoetzel/em-vc-integration
 * Description: This plugin maps events-manager shortcodes to WPBakery Visual Composer elements.
 * Version:     1.2.0
 * Author:      Nils Woetzel
 * Author URI:  https://github.com/nwoetzel
 * Text Domain: em-vc-integration
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
        require __DIR__ . '/vendor/autoload.php';
}

if( !class_exists( 'EM_VC_Integration' ) ) {

/**
 * Main EM_VC_Integration class
 *
 * @since 1.0.0
 */
class EM_VC_Integration {

    /**
     * @since 1.1.0
     * @var   string Text domain used for translations
     */
    CONST TEXT_DOMAIN = 'em-vc-integration';

    /**
     * @var EM_VC_Integration $instance The one true EM_VC_Integration
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      object self::$instance The one true EM_VC_Integration
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new EM_VC_Integration();
            self::$instance->setup_constants();
            self::$instance->load_textdomain();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function setup_constants() {
        // Plugin version
        define( 'EM_VC_INTERGATION_VER', '1.2.0' );
        // Plugin path
        define( 'EM_VC_INTERGATION_DIR', plugin_dir_path( __FILE__ ) );
        // Plugin URL
        define( 'EM_VC_INTERGATION_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Run action and filter hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {
        // map shortcodes
        if( function_exists( 'vc_map' ) && class_exists('EM_Location') && class_exists('EM_Event') ) {
            add_action( 'vc_before_init', array( $this, 'vcMap' ) );
        }
    }

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.1.0
     * @return      void
     */
    public function load_textdomain() {
        // Set filter for language directory
        $lang_dir = EM_VC_INTEGRATION_DIR . '/languages/';
        $lang_dir = apply_filters( 'em_vc_integration_languages_directory', $lang_dir );
        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), self::TEXT_DOMAIN );
        $mofile = sprintf( '%1$s-%2$s.mo', self::TEXT_DOMAIN, $locale );
        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/' . self::TEXT_DOMAIN . '/' . $mofile;
        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/em-vc-integration/ folder
            load_textdomain( self::TEXT_DOMAIN, $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/em-vc-integration/languages/ folder
            load_textdomain( self::TEXT_DOMAIN, $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( self::TEXT_DOMAIN, false, 'em-vc-integration/languages' );
        }
    }

    /**
     * This is an array of event column names.
     * They can be used as a sort parameter.
     *
     * @var          string[]
     * @access       protected
     * @since        1.0.0
     */
    protected static $eventTableColumnNames = array(
        'event_id',
        'post_id',
        'event_slug',
        'event_owner',
        'event_status',
        'event_name',
        'event_start_time',
        'event_end_time',
        'event_all_day',
        'event_start_date',
        'event_end_date',
        'post_content',
        'event_rsvp',
        'event_rsvp_date',
        'event_rsvp_time',
        'event_rsvp_spaces',
        'event_spaces',
        'event_private',
        'location_id',
        'recurrence_id',
        'event_category_id',
        'event_attributes',
        'event_date_created',
        'event_date_modified',
        'recurrence',
        'recurrence_interval',
        'recurrence_freq',
        'recurrence_byday',
        'recurrence_byweekno',
        'recurrence_days',
        'recurrence_rsvp_days',
        'blog_id',
        'group_id',
    );

    /**
     * This is an array of location column names.
     * They can be used as a sort parameter.
     *
     * @var          string[]
     * @access       protected
     * @since        1.0.0
     */
    protected static $locationTableColumnNames = array(
        'location_id',
        'post_id',
        'blog_id',
        'location_slug',
        'location_name',
        'location_owner',
        'location_address',
        'location_town',
        'location_state',
        'location_postcode',
        'location_region',
        'location_country',
        'location_latitude',
        'location_longitude',
        'post_content',
        'location_status',
        'location_private',
    );



    /**
     * This is an array of shortcode parameters with general output parameters.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array of arrays describing shortcode parameters
     */
    protected static function generalAttributes($postType) {
        return array(
            self::limitParam(),
/*            array(
                'param_name' => 'order',
                'heading' => 'Order',
                'description' => 'Indicates the alphabetical/numerical order of the lists. Default value: ASC',
                'value' => array('ASC', 'DESC'),
                'type' => 'dropdown',
                'admin_label' => true,
                'group' => __( 'General', self::TEXT_DOMAIN),
            ),
            array(
                'param_name' => 'orderby',
                'heading' => 'Order by',
                'description' => 'Choose what fields to order your results by.',
                'type' => 'autocomplete',
                'settings' => array(
                    'multiple' => 'true',
                    'sortable' => true,
                    'min_length' => 1,
                    'no_hide' => true,
                    'unique_values' => true,
                    'display_inline' => true,
                    'values' => $this->getEventTableColumnNames(),
                ),
                'save_always' => true,
                'admin_label' => true,
                'group' => __( 'General', self::TEXT_DOMAIN),
            ),*/
        );
    }

    /**
     * This is a shortcode parameter to limit the number of events shown.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function limitParam() {
        return array(
            'param_name' => 'limit',
            'heading' => __( 'List Limits', self::TEXT_DOMAIN),
            'description' => __( "This will control how many events or locations are shown on one list by default.", self::TEXT_DOMAIN),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __( 'General', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is an array of shortcode parameters to select events within a radius of a coordinate.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array of arrays describing shortcode parameters
     */
    protected static function nearAttributes() {
        return array(
            self::nearParam(),
            self::nearDistanceParam(),
            self::nearUnitParam(),
        );
    }

    /**
     * This is a shortcode parameter to define a coordinate near which events are shown.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function nearParam() {
        return array(
            'param_name' => 'near',
            'heading' => __( 'Near', self::TEXT_DOMAIN),
            'description' => __( 'Accepts a comma-separated coordinates (e.g. 1,1) value, which searches for events or locations located near this coordinate.', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'dependency' => array('element' => 'near_unit', 'value' => array('km','mi')),
            'admin_label' => true,
            'group' => __( 'Near...', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to define the near radius to a nearParam coordinate.
     *
     * @see          nearParam()
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function nearDistanceParam() {
        return array(
            'param_name' => 'near_distance',
            'heading' => __( 'Near distance', self::TEXT_DOMAIN),
            'description' => __( 'The radius distance when searching with the near attribute.', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'dependency' => array('element' => 'near_unit', 'value' => array('km','mi')),
            'admin_label' => true,
            'group' => __( 'Near...', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to define the near radius unit.
     *
     * @see          nearParam()
     * @see          nearDistanceParam()
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function nearUnitParam() {
        return array(
            'param_name' => 'near_unit',
            'heading' => __( 'Near unit', self::TEXT_DOMAIN),
            'description' => __( 'Select a unit to define a coordinate and distance within.', self::TEXT_DOMAIN),
            'type' => 'dropdown',
            'value' => array('' => '', 'km' => 'km', 'mi' => 'mi',),
            'admin_label' => true,
            'group' => __( 'Near...', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is an array of shortcode parameters to select events.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array of arrays describing shortcode parameters
     */
    protected static function eventAttributes() {
        return array(
            self::categoryParam(),
            self::tagParam(),
            self::searchParam(),
            self::scopeParam(),
        );
    }

    /**
     * This is a shortcode parameter to select events by categories.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function categoryParam() {
        return array(
            'param_name' => 'category',
            'heading' => __( 'Event Categories', self::TEXT_DOMAIN),
            'description' => __( 'Show events of a particular event category.', self::TEXT_DOMAIN),
            'type' => 'autocomplete',
            'settings' => array(
                'multiple' => 'true',
                'sortable' => true,
                'min_length' => 1,
                'no_hide' => true,
                'unique_values' => true,
                'display_inline' => true,
                'values' => self::taxonomyTerms( EM_TAXONOMY_CATEGORY),
            ),
            'admin_label' => true,
            'group' => __( 'Event', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to select events by tags.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function tagParam() {
        return array(
            'param_name' => 'tag',
            'heading' => __( 'Event Tags', self::TEXT_DOMAIN),
            'description' => __( 'Show events with a particular event tag.', self::TEXT_DOMAIN),
            'type' => 'autocomplete',
            'settings' => array(
                'multiple' => 'true',
                'sortable' => true,
                'min_length' => 1,
                'no_hide' => true,
                'unique_values' => true,
                'display_inline' => true,
                'values' => self::taxonomyTerms( EM_TAXONOMY_TAG),
            ),
            'admin_label' => true,
            'group' => __( 'Event', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to select events by searching name, details and locations for a string.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function searchParam() {
        return array(
            'param_name' => 'search',
            'heading' => __( 'Search', self::TEXT_DOMAIN),
            'description' => __( 'Do a search for this string within event name, details and location address.', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __( 'Event', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to define the time scope of which events are shown.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function scopeParam() {
        return array(
            'param_name' => 'scope',
            'heading' => 'Scope',
            'type' => 'textfield',
            'description' => __( 'Only show events starting within a certain time limit on the events page. Default is future events with no end time limit.', self::TEXT_DOMAIN) . ' ' . __( 'Choose the time frame of events to show. Additionally you can supply dates (in format of YYYY-MM-DD), either single for events on a specific date or two dates separated by a comma (e.g. 2010-12-25,2010-12-31) for events ocurring between these dates. Default Value: future Accepted Arguments : future, past, today, tomorrow, month, next-month, 1-months, 2-months, 3-months, 6-months, 12-months, all', self::TEXT_DOMAIN),
            'admin_label' => true,
            'group' => __( 'General', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is an array of shortcode parameters to select locations or events by locations.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array of arrays describing shortcode parameters
     */
    protected static function locationAttributes() {
        return array(
            self::countryParam(),
            self::postcodeParam(),
            self::regionParam(),
            self::stateParam(),
            self::townParam(),
        );
    }

    /**
     * This is a shortcode parameter to select an event or location by the country.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function countryParam() {
        return array(
            'param_name' => 'country',
            'heading' => __( 'Country', self::TEXT_DOMAIN),
            'description' => __( 'Search for locations in this Country (no partial matches, case sensitive). Use two-character country codes as defined in countrycode.org, can be comma-separated e.g. DE,US', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __( 'Location', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to select an event or location by the postal code.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function postcodeParam() {
        return array(
            'param_name' => 'postcode',
            'heading' => __( 'Postcode', self::TEXT_DOMAIN),
            'description' => __( 'Search for locations in this Postcode (no partial matches, case sensitive).', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __( 'Location', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to select an event or location by the region.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function regionParam() {
        return array(
            'param_name' => 'region',
            'heading' => __( 'Region', self::TEXT_DOMAIN),
            'description' => __( 'Search for locations in this Region (no partial matches, case sensitive).', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __( 'Location', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to select an event or location by the state.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function stateParam() {
        return array(
            'param_name' => 'state',
            'heading' => __( 'State', self::TEXT_DOMAIN),
            'description' => __( 'Search for locations in this State (no partial matches, case sensitive).', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __( 'Location', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to select an event or location by the town.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function townParam() {
        return array(
            'param_name' => 'town',
            'heading' => __( 'Town', self::TEXT_DOMAIN),
            'description' => __( 'Search for locations in this Town (no partial matches, case sensitive).', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __( 'Location', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to define the width of a map.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function widthParam() {
        return array(
            'param_name' => 'width',
            'heading' => __( 'Map width', self::TEXT_DOMAIN),
            'description' => __( 'The width in pixels of the map. Default: 450.', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __( 'Layout', self::TEXT_DOMAIN),
        );
    }

    /**
     * This is a shortcode parameter to define the height of a map.
     *
     * @access       protected
     * @since        1.0.0
     * @return       array describing a shortcode parameter
     */
    protected static function heightParam() {
        return array(
            'param_name' => 'height',
            'heading' => __( 'Map height', self::TEXT_DOMAIN),
            'description' => __( 'The height in pixels of the map. Default: 300.', self::TEXT_DOMAIN),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __( 'Layout', self::TEXT_DOMAIN),
        );
    }

    /******************
     * map shortcodes *
     ******************/

    /**
     * Map the events_list shortcode.
     *
     * @access       protected
     * @since        1.0.0
     * @return       void
     */
    protected function vcMapEventsList() {
        vc_map( array(
            'name' => sprintf(__( '%s List/Archives', self::TEXT_DOMAIN), __( 'Event',  self::TEXT_DOMAIN)),
            'base' => 'events_list',
            'category' => __( 'Events Manager', self::TEXT_DOMAIN),
            'icon' => 'dashicons dashicons-calendar-alt',
            'params' => array_merge(
                self::generalAttributes('events'),
                self::eventAttributes(),
                self::nearAttributes()
            ),
        ) );
    }

    /**
     * Map the locations_list shortcode.
     *
     * @access       protected
     * @since        1.0.0
     * @return       void
     */
    protected function vcMapLocationsList() {
        vc_map( array(
            'name' => __( 'Locations List', self::TEXT_DOMAIN),
            'base' => 'locations_list',
            'category' => __( 'Events Manager', self::TEXT_DOMAIN),
            'icon' => 'dashicons dashicons-admin-site',
            'params' => array_merge(
                self::generalAttributes('locations'),
                self::eventAttributes(),
                self::locationAttributes(),
                self::nearAttributes()
            ),
        ) );
    }

    /**
     * Map the locations_map shortcode.
     *
     * @access       protected
     * @since        1.0.0
     * @return       void
     */
    protected function vcMapLocationsMap() {
        vc_map( array(
            'name' => __( 'Locations Map', self::TEXT_DOMAIN),
            'base' => 'locations_map',
            'category' => __( 'Events Manager', self::TEXT_DOMAIN),
            'icon' => 'dashicons dashicons-admin-site',
            'params' => array_merge(
                array(
                    self::widthParam(),
                    self::heightParam(),
                ),
                self::generalAttributes('locations'),
                self::eventAttributes(),
                self::locationAttributes(),
                self::nearAttributes()
            ),
        ) );
    }

    /**
     * Map the event shortcode.
     *
     * @access       protected
     * @since        1.0.0
     * @return       void
     */
    protected function vcMapEvent() {
        vc_map( array(
            'name' => __( 'Event', self::TEXT_DOMAIN),
            'base' => 'event',
            'category' => __( 'Events Manager', self::TEXT_DOMAIN),
            'icon' => 'dashicons dashicons-calendar',
            'params' => array(
                array(
                    'param_name' => 'post_id',
                    'heading' => __( 'Event', self::TEXT_DOMAIN),
                    'type' => 'dropdown',
                    'value' => self::posts('event'),
                    'admin_label' => true,
                    'group' => __( 'Event', self::TEXT_DOMAIN),
                ),
            ),
        ) );
    }

    /**
     * Map the location shortcode.
     *
     * @access       protected
     * @since        1.0.0
     * @return       void
     */
    protected function vcMapLocation() {
        vc_map( array(
            'name' => __( 'Location', self::TEXT_DOMAIN),
            'base' => 'location',
            'category' => __( 'Events Manager', self::TEXT_DOMAIN),
            'icon' => 'dashicons dashicons-admin-home',
            'params' => array(
                array(
                    'param_name' => 'post_id',
                    'heading' => __( 'Location', self::TEXT_DOMAIN),
                    'type' => 'dropdown',
                    'value' => self::posts('location'),
                    'admin_label' => true,
                    'group' => __( 'Location', self::TEXT_DOMAIN),
                ),
            ),
        ) );
    }

    /**
     * Map the events_calendar shortcode.
     *
     * @access       protected
     * @since        1.0.0
     * @return       void
     */
    protected function vcMapEventsCalendar() {
        vc_map( array(
            'name' => __( 'Events Calendar', self::TEXT_DOMAIN),
            'base' => 'events_calendar',
            'category' => __( 'Events Manager', self::TEXT_DOMAIN),
            'icon' => 'dashicons dashicons-calendar-alt',
            'params' => array_merge(
                array(
                    array(
                        'param_name' => 'full',
                        'heading' => __( 'Full calendar', self::TEXT_DOMAIN),
                        'description' => __( 'Show a full sized calendar.', self::TEXT_DOMAIN),
                        'type' => 'checkbox',
                        'value' => array( __( 'Yes', self::TEXT_DOMAIN) => 1 ),
                        'admin_label' => true,
                        'group' => __( 'Layout', self::TEXT_DOMAIN),
                    ),
                ),
                self::generalAttributes('events'),
                self::eventAttributes(),
                self::locationAttributes(),
                self::nearAttributes()
            ),
        ) );
    }

    /**
     * Map all events manager shortcodes.
     * @see https://wpbakery.atlassian.net/wiki/pages/viewpage.action?pageId=524332#vc_map()-Addexistingshortcode
     *
     * @access       protected
     * @since        1.0.0
     * @return       void
     */
    public function vcMap() {
        $this->vcMapEventsList();
        $this->vcMapLocationsList();
        $this->vcMapEventsCalendar();
        $this->vcMapLocationsMap();
        $this->vcMapEvent();
        $this->vcMapLocation();
    }

   /********************
    * helper functions *
    ********************/

    /**
     * Get the names of all terms for a given taxonomy.
     *
     * @access       protected
     * @since        1.0.0
     * @param        $sTaxonomy string name of taxonomy
     * @return       string[] the names of all terms for the $sTaxonomy
     */
    protected static function taxonomyTerms($sTaxonomy) {
        $term_names = get_terms( array(
            'taxonomy' => $sTaxonomy,
            'fields' => 'names',
        ) );

        $values = array();
        foreach( $term_names as $term) {
            $values[] = array( 'label' => $term, 'value' => $term);
        }

        return $values;
    }

    /**
     * Get the posts for a post type.
     *
     * @access       protected
     * @since        1.0.0
     * @param        $sPostType string name of post type
     * @return       array post_title => id
     */
    protected static function posts($sPostType) {
        $posts_array = get_posts(array(
            'post_type' => $sPostType,
            'numberposts' => -1,
            'orderby' => 'post_title',
            'order' => 'ASC',
            'fields' => array('ID','post_title')
        ) );

        $posts = array();
        foreach($posts_array as $post) {
            $posts[$post->post_title] = $post->ID;
        }

        return $posts;
    }

}

} // End if class_exists check

/**
 * The main function responsible for returning the one true EM_VC_Integration
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EM_VC_Integration The one true EM_VC_Integration
 */
function em_vc_integration_load() {
    return EM_VC_Integration::instance();
}
add_action( 'plugins_loaded', 'em_vc_integration_load' );

/**
 * A nice function name to retrieve the instance that's created on plugins loaded
 *
 * @since 1.0.0
 * @return object EM_VC_Integration
 */
function em_vc_integration() {
	return em_vc_integration_load();
}
