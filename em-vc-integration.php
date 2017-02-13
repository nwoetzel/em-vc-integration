<?php
// based on https://github.com/easydigitaldownloads/EDD-Extension-Boilerplate
/**
 * Plugin Name: Events Manager Visual Composer Integration
 * Plugin URI:  https://github.com/nwoetzel/em-vc-integration
 * Description: This plugin maps events-manager shortcodes to WPBakery Visual Composer elements.
 * Version:     1.0.0
 * Author:      Nils Woetzel
 * Author URI:  https://github.com/nwoetzel
 * Text Domain: em-vc-integration
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EM_VC_Integration' ) ) {

/**
 * Main EM_VC_Integration class
 *
 * @since 1.0.0
 */
class EM_VC_Integration {

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
//            self::$instance->load_textdomain();
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
        define( 'EM_VC_INTERGATION_VER', '1.0.0' );
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
                'group' => __('General','events-manager'),
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
                'group' => __('General','events-manager'),
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
            'heading' => __( 'List Limits', 'events-manager'),
            'description' => sprintf(__( "This will control how many %s are shown on one list by default.", 'events-manager'),__($postType,'events-manager')),
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __('General','events-manager'),
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
            'heading' => 'Near',
            'description' => 'Accepts a comma-separated coordinates (e.g. 1,1) value, which searches for events or locations located near this coordinate.',
            'type' => 'textfield',
            'dependency' => array('element' => 'near_unit', 'value' => array('km','mi')),
            'admin_label' => true,
            'group' => __('Near...','events-manager'),
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
            'heading' => 'Near distance',
            'description' => 'The radius distance when searching with the near attribute.',
            'type' => 'textfield',
            'dependency' => array('element' => 'near_unit', 'value' => array('km','mi')),
            'admin_label' => true,
            'group' => __('Near...','events-manager'),
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
            'heading' => 'Near unit',
            'description' => 'Select a unit to define a coordinate and distance within.',
            'type' => 'dropdown',
            'value' => array('' => '', 'km' => 'km', 'mi' => 'mi',),
            'admin_label' => true,
            'group' => __('Near...','events-manager'),
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
            'heading' => __('Event Categories','events-manager'),
            'description' => 'Show events of a particular '.EM_TAXONOMY_CATEGORY.'.',
            'type' => 'autocomplete',
            'settings' => array(
                'multiple' => 'true',
                'sortable' => true,
                'min_length' => 1,
                'no_hide' => true,
                'unique_values' => true,
                'display_inline' => true,
                'values' => self::taxonomyTerms(EM_TAXONOMY_CATEGORY),
            ),
            'admin_label' => true,
            'group' => __('Event','events-manager'),
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
            'heading' => __( 'Event Tags', 'events-manager'),
            'description' => 'Show events with a particular '.EM_TAXONOMY_TAG.'.',
            'type' => 'autocomplete',
            'settings' => array(
                'multiple' => 'true',
                'sortable' => true,
                'min_length' => 1,
                'no_hide' => true,
                'unique_values' => true,
                'display_inline' => true,
                'values' => self::taxonomyTerms(EM_TAXONOMY_TAG),
            ),
            'admin_label' => true,
            'group' => __('Event','events-manager'),
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
            'heading' => 'Search',
            'description' => 'Do a search for this string within event name, details and location address.',
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __('Event','events-manager'),
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
            'description' => __('Only show events starting within a certain time limit on the events page. Default is future events with no end time limit.','events-manager') . ' Choose the time frame of events to show. Additionally you can supply dates (in format of YYYY-MM-DD), either single for events on a specific date or two dates separated by a comma (e.g. 2010-12-25,2010-12-31) for events ocurring between these dates. Default Value: future Accepted Arguments : future, past, today, tomorrow, month, next-month, 1-months, 2-months, 3-months, 6-months, 12-months, all',
            'admin_label' => true,
            'group' => __('General','events-manager'),
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
            'heading' => __('Country','events-manager'),
            'description' => 'Search for locations in this Country (no partial matches, case sensitive). Use two-character country codes as defined in countrycode.org, can be comma-separated e.g. DE,US',
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __('Location','events-manager'),
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
            'heading' => 'Postcode',
            'description' => 'Search for locations in this Postcode (no partial matches, case sensitive).',
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __('Location','events-manager'),
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
            'heading' => __('Region','events-manager'),
            'description' => 'Search for locations in this Region (no partial matches, case sensitive).',
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __('Location','events-manager'),
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
            'heading' => __('State','events-manager'),
            'description' => 'Search for locations in this State (no partial matches, case sensitive).',
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __('Location','events-manager'),
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
            'heading' => __('Town','events-manager'),
            'description' => 'Search for locations in this Town (no partial matches, case sensitive).',
            'type' => 'textfield',
            'admin_label' => true,
            'group' => __('Location','events-manager'),
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
            'heading' => 'Map width',
            'description' => 'The width in pixels of the map. Default: 450.',
            'type' => 'textfield',
            'admin_label' => true,
            'group' => 'Layout',
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
            'heading' => 'Map height',
            'description' => 'The height in pixels of the map. Default: 300.',
            'type' => 'textfield',
            'admin_label' => true,
            'group' => 'Layout',
        );
    }

    /******************
     * map shortcodes *
     ******************/

    protected function vcMapEventsList() {
        vc_map( array(
            'name' => sprintf(__('%s List/Archives','events-manager'),__('Event','events-manager')),
            'base' => 'events_list',
            'category' => __('Events Manager','events-manager'),
            'icon' => 'dashicons dashicons-calendar-alt',
            'params' => array_merge(
                self::generalAttributes('events'),
                self::eventAttributes(),
                self::nearAttributes()
            ),
        ) );
    }

    protected function vcMapLocationsList() {
        vc_map( array(
            'name' => 'Locations List',
            'base' => 'locations_list',
            'category' => __('Events Manager','events-manager'),
            'icon' => 'dashicons dashicons-admin-site',
            'params' => array_merge(
                self::generalAttributes('locations'),
                self::eventAttributes(),
                self::locationAttributes(),
                self::nearAttributes()
            ),
        ) );
    }

    protected function vcMapLocationsMap() {
        vc_map( array(
            'name' => 'Locations Map',
            'base' => 'locations_map',
            'category' => __('Events Manager','events-manager'),
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

    protected function vcMapEvent() {
        vc_map( array(
            'name' => __('Event','events-manager'),
            'base' => 'event',
            'category' => __('Events Manager','events-manager'),
            'icon' => 'dashicons dashicons-calendar',
            'params' => array(
                array(
                    'param_name' => 'post_id',
                    'heading' => __('Event','events-manager'),
                    'type' => 'dropdown',
                    'value' => self::posts('event'),
                    'admin_label' => true,
                    'group' => __('Event','events-manager'),
                ),
            ),
        ) );
    }

    protected function vcMapLocation() {
        vc_map( array(
            'name' => __('Location','events-manager'),
            'base' => 'location',
            'category' => __('Events Manager','events-manager'),
            'icon' => 'dashicons dashicons-admin-home',
            'params' => array(
                array(
                    'param_name' => 'post_id',
                    'heading' => __('Location','events-manager'),
                    'type' => 'dropdown',
                    'value' => self::posts('location'),
                    'admin_label' => true,
                    'group' => __('Location','events-manager'),
                ),
            ),
        ) );
    }

    protected function vcMapEventsCalendar() {
        vc_map( array(
            'name' => __('Events Calendar','events-manager'),
            'base' => 'events_calendar',
            'category' => __('Events Manager','events-manager'),
            'icon' => 'dashicons dashicons-calendar-alt',
            'params' => array_merge(
                array(
                    array(
                        'param_name' => 'full',
                        'heading' => 'Full calendar',
                        'description' => 'Show a full sized calendar.',
                        'type' => 'checkbox',
                        'value' => array( __( 'Yes', 'js_composer' ) => 1 ),
                        'admin_label' => true,
                        'group' => 'Layout',
                    ),
                ),
                self::generalAttributes('events'),
                self::eventAttributes(),
                self::locationAttributes(),
                self::nearAttributes()
            ),
        ) );
    }

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
