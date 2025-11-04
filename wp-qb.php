<?php
/**
 * Plugin Name: WP Query Builder
 * Plugin URI: https://github.com/yourusername/wp-qb
 * Description: Eloquent-like Query Builder for WordPress custom tables
 * Version: 1.0.0
 * Author: arisciwek
 * Author URI: https://yourwebsite.com
 * License: MIT
 * Text Domain: wp-qb
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * @package     WP_Query_Builder
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-qb/wp-qb.php
 *
 * Description: Main plugin file untuk WP Query Builder.
 *              Menyediakan fluent interface Eloquent-like untuk WordPress database queries.
 *              Includes autoloading dan plugin initialization.
 *
 * Changelog:
 * 1.0.0 - 2025-11-05
 * - Restructured to follow wp-customer plugin architecture
 * - Added includes/ directory with proper class structure
 * - Implemented singleton pattern
 * - Added hook management system
 * - Added activation/deactivation hooks
 * - Fluent query builder dengan method chaining
 * - Support SELECT, WHERE, JOIN, GROUP BY, HAVING, ORDER BY
 * - Support INSERT, UPDATE, DELETE operations
 * - Collection helper class
 * - SQL Grammar compiler
 * - Prepared statements untuk security
 */

defined('ABSPATH') || exit;

// Define plugin constants first, before anything else
define('WPQB_VERSION', '1.0.0');
define('WPQB_FILE', __FILE__);
define('WPQB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPQB_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WPQB {
    /**
     * Single instance of the class
     */
    private static $instance = null;

    private $loader;
    private $plugin_name;
    private $version;

    /**
     * Get single instance of WPQB
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->plugin_name = 'wp-qb';
        $this->version = WPQB_VERSION;

        // Register autoloader first
        require_once WPQB_PLUGIN_DIR . 'includes/class-autoloader.php';
        $autoloader = new WPQBAutoloader('WPQB\\', WPQB_PLUGIN_DIR);
        $autoloader->register();

        // Load Composer autoloader if exists
        if (file_exists(WPQB_PLUGIN_DIR . 'vendor/autoload.php')) {
            require_once WPQB_PLUGIN_DIR . 'vendor/autoload.php';
        }

        $this->includeDependencies();
        $this->initHooks();
    }

    /**
     * Include required dependencies
     */
    private function includeDependencies() {
        require_once WPQB_PLUGIN_DIR . 'includes/class-loader.php';
        require_once WPQB_PLUGIN_DIR . 'includes/class-activator.php';
        require_once WPQB_PLUGIN_DIR . 'includes/class-deactivator.php';
        require_once WPQB_PLUGIN_DIR . 'includes/class-dependencies.php';
        require_once WPQB_PLUGIN_DIR . 'includes/class-init-hooks.php';

        $this->loader = new WP_QB_Loader();
    }

    /**
     * Initialize hooks and controllers
     */
    private function initHooks() {
        // Register activation/deactivation hooks
        register_activation_hook(WPQB_FILE, array('WP_QB_Activator', 'activate'));
        register_deactivation_hook(WPQB_FILE, array('WP_QB_Deactivator', 'deactivate'));

        // Initialize dependencies
        $dependencies = new WP_QB_Dependencies($this->plugin_name, $this->version);

        // Register asset hooks (if needed in the future)
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_scripts');

        // Initialize other hooks
        $init_hooks = new WP_QB_Init_Hooks();
        $init_hooks->init();
    }

    /**
     * Run the plugin
     */
    public function run() {
        $this->loader->run();

        /**
         * Action: wpqb_init
         *
         * Fires after wp-qb core is initialized.
         * Used by integration framework for bootstrapping.
         *
         * @since 1.0.0
         */
        do_action('wpqb_init');
    }
}

/**
 * Returns the main instance of WPQB
 */
function wpqb() {
    return WPQB::getInstance();
}

// Initialize the plugin
wpqb()->run();
