<?php
/**
 * Plugin Name:       Mega Menu Builder for Elementor
 * Plugin URI:        https://wordpress.org/plugins/mega-menu-builder-for-elementor/
 * Description:       Advanced mega menu builder widget for Elementor with horizontal/vertical layouts, dropdown animations, WooCommerce products, posts integration, and mobile-responsive design.
 * Version:           1.0.0
 * Author:            Webhook Academy
 * Author URI:        https://webhookacademy.com/
 * Text Domain:       mega-menu-builder-for-elementor
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 * Elementor tested up to: 4.0
 * Elementor Pro tested up to: 4.0
 */

namespace MMBElementor;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
if ( ! defined( 'MMB_VERSION' ) ) {
	define( 'MMB_VERSION', '1.0.0' );
}

define( 'MMB_FILE', __FILE__ );
define( 'MMB_PLUGIN_BASE', plugin_basename( MMB_FILE ) );
define( 'MMB_PATH', plugin_dir_path( MMB_FILE ) );
define( 'MMB_URL', plugin_dir_url( MMB_FILE ) );

/**
 * Main Plugin Class
 */
final class Mega_Menu_Builder {

	const MINIMUM_ELEMENTOR_VERSION = '3.0.0';
	const MINIMUM_PHP_VERSION = '7.4';

	private static $_instance = null;

	/**
	 * Singleton instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
		// Textdomain loaded automatically by WordPress.org
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
		add_action( 'wp_ajax_mmb_import_template', [ $this, 'ajax_import_template' ] );
		add_action( 'wp_ajax_mmb_delete_template', [ $this, 'ajax_delete_template' ] );
		add_action( 'wp_ajax_mmb_clear_all_templates', [ $this, 'ajax_clear_all_templates' ] );
		add_action( 'wp_ajax_mmb_load_template_data', [ $this, 'ajax_load_template_data' ] );
		add_shortcode( 'mmb_menu', [ $this, 'render_menu_shortcode' ] );
	}

	/**
	 * On plugins loaded
	 */
	public function on_plugins_loaded() {
		// Check if Elementor is installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		// Register widget
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		
		// Register widget category
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_category' ] );

		// Enqueue frontend scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
	}

	/**
	 * Register widget category
	 */
	public function register_widget_category( $elements_manager ) {
		$elements_manager->add_category(
			'mega-menu-builder',
			[
				'title' => esc_html__( 'Mega Menu Builder', 'mega-menu-builder-for-elementor' ),
				'icon'  => 'fa fa-plug',
			]
		);
	}

	/**
	 * Register widgets
	 */
	public function register_widgets( $widgets_manager ) {
		require_once MMB_PATH . 'includes/widgets/mega-menu-widget.php';
		$widgets_manager->register( new \MMBElementor\Widgets\Mega_Menu_Widget() );
	}

	/**
	 * Enqueue frontend scripts
	 */
	public function enqueue_frontend_scripts() {
		// Enqueue CSS
		$css_file = MMB_PATH . 'assets/css/mega-menu.css';
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'mmb-mega-menu',
				MMB_URL . 'assets/css/mega-menu.css',
				[],
				filemtime( $css_file )
			);
		}

		// Enqueue JS
		$js_file = MMB_PATH . 'assets/js/mega-menu.js';
		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'mmb-mega-menu',
				MMB_URL . 'assets/js/mega-menu.js',
				[ 'jquery' ],
				filemtime( $js_file ),
				true
			);
		}
	}

	/**
	 * Admin notice - Missing Elementor
	 */
	public function admin_notice_missing_elementor() {
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'mega-menu-builder-for-elementor' ),
			'<strong>' . esc_html__( 'Mega Menu Builder for Elementor', 'mega-menu-builder-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'mega-menu-builder-for-elementor' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Admin notice - Minimum Elementor version
	 */
	public function admin_notice_minimum_elementor_version() {
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'mega-menu-builder-for-elementor' ),
			'<strong>' . esc_html__( 'Mega Menu Builder for Elementor', 'mega-menu-builder-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'mega-menu-builder-for-elementor' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Admin notice - Minimum PHP version
	 */
	public function admin_notice_minimum_php_version() {
		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'mega-menu-builder-for-elementor' ),
			'<strong>' . esc_html__( 'Mega Menu Builder for Elementor', 'mega-menu-builder-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'mega-menu-builder-for-elementor' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Register admin menu
	 */
	public function register_admin_menu() {
		add_menu_page(
			esc_html__( 'Mega Menu Builder', 'mega-menu-builder-for-elementor' ),
			esc_html__( 'Mega Menu', 'mega-menu-builder-for-elementor' ),
			'manage_options',
			'mega-menu-builder',
			[ $this, 'render_dashboard_page' ],
			'dashicons-menu-alt',
			58
		);
	}

	/**
	 * Render dashboard page
	 */
	public function render_dashboard_page() {
		include MMB_PATH . 'admin/dashboard.php';
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on our dashboard page
		if ( 'toplevel_page_mega-menu-builder' !== $hook ) {
			return;
		}

		// Enqueue SweetAlert2 (local files)
		wp_enqueue_style(
			'mmb-sweetalert2',
			MMB_URL . 'admin/assets/vendor/sweetalert2/sweetalert2.min.css',
			[],
			'11.14.5'
		);

		wp_enqueue_script(
			'mmb-sweetalert2',
			MMB_URL . 'admin/assets/vendor/sweetalert2/sweetalert2.min.js',
			[],
			'11.14.5',
			true
		);

		// Enqueue CSS
		wp_enqueue_style(
			'mmb-dashboard',
			MMB_URL . 'admin/assets/css/dashboard.css',
			[ 'mmb-sweetalert2' ],
			MMB_VERSION
		);

		// Enqueue JS
		wp_enqueue_script(
			'mmb-dashboard',
			MMB_URL . 'admin/assets/js/dashboard.js',
			[ 'jquery', 'mmb-sweetalert2' ],
			MMB_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'mmb-dashboard',
			'mmbDashboard',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mmb_import_template' ),
			]
		);
	}

	/**
	 * Enqueue Elementor editor scripts
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script(
			'mmb-editor',
			MMB_URL . 'assets/js/mega-menu-editor.js',
			[ 'jquery', 'elementor-editor' ],
			MMB_VERSION,
			true
		);

		wp_localize_script(
			'mmb-editor',
			'mmbEditorData',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mmb_load_template' ),
			]
		);
	}

	/**
	 * AJAX handler for template import - Save to database
	 */
	public function ajax_import_template() {
		// Verify nonce
		check_ajax_referer( 'mmb_import_template', 'mmb_nonce' );

		// Check user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'mega-menu-builder-for-elementor' ) ] );
		}

		// Get template ID
		$template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';

		if ( empty( $template_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid template ID.', 'mega-menu-builder-for-elementor' ) ] );
		}

		// Check if already imported
		$saved_templates = get_option( 'mmb_saved_templates', [] );
		foreach ( $saved_templates as $saved ) {
			if ( isset( $saved['original_id'] ) && $saved['original_id'] === $template_id ) {
				wp_send_json_error( [ 'message' => esc_html__( 'This template is already imported!', 'mega-menu-builder-for-elementor' ) ] );
			}
		}

		// Load templates index
		$templates_file = MMB_PATH . 'includes/menu-templates/index.json';
		
		if ( ! file_exists( $templates_file ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Templates file not found.', 'mega-menu-builder-for-elementor' ) ] );
		}

		$json_content = file_get_contents( $templates_file );
		$templates_data = json_decode( $json_content, true );
		$templates = isset( $templates_data['templates'] ) ? $templates_data['templates'] : [];

		// Find template
		foreach ( $templates as $template ) {
			if ( $template['id'] === $template_id ) {
				$template_file = MMB_PATH . 'includes/menu-templates/' . $template['file'];
				
				if ( file_exists( $template_file ) ) {
					$template_json = file_get_contents( $template_file );
					$template_data = json_decode( $template_json, true );
					
					// Generate unique ID for saved template
					$saved_id = 'saved_' . $template_id . '_' . time();
					
					$saved_templates[ $saved_id ] = [
						'id' => $saved_id,
						'title' => isset( $template['title'] ) ? $template['title'] : 'Imported Template',
						'original_id' => $template_id,
						'data' => $template_data,
						'imported_at' => current_time( 'mysql' ),
					];
					
					update_option( 'mmb_saved_templates', $saved_templates );
					
					wp_send_json_success( [
						'message' => esc_html__( 'Template imported successfully! You can now use it in Mega Menu widget.', 'mega-menu-builder-for-elementor' ),
						'saved_id' => $saved_id,
						'template_title' => isset( $template['title'] ) ? $template['title'] : ''
					] );
					return;
				}
			}
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Template not found.', 'mega-menu-builder-for-elementor' ) ] );
	}

	/**
	 * AJAX handler for deleting imported template
	 */
	public function ajax_delete_template() {
		// Verify nonce
		check_ajax_referer( 'mmb_import_template', 'mmb_nonce' );

		// Check user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'mega-menu-builder-for-elementor' ) ] );
		}

		// Get template ID
		$template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';

		if ( empty( $template_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid template ID.', 'mega-menu-builder-for-elementor' ) ] );
		}

		// Load saved templates
		$saved_templates = get_option( 'mmb_saved_templates', [] );

		// Check if template exists
		if ( ! isset( $saved_templates[ $template_id ] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Template not found.', 'mega-menu-builder-for-elementor' ) ] );
		}

		// Delete template
		unset( $saved_templates[ $template_id ] );
		update_option( 'mmb_saved_templates', $saved_templates );

		wp_send_json_success( [
			'message' => esc_html__( 'Template deleted successfully!', 'mega-menu-builder-for-elementor' ),
		] );
	}

	/**
	 * AJAX handler for clearing all imported templates
	 */
	public function ajax_clear_all_templates() {
		// Verify nonce
		check_ajax_referer( 'mmb_import_template', 'mmb_nonce' );

		// Check user capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'mega-menu-builder-for-elementor' ) ] );
		}

		// Clear all saved templates
		delete_option( 'mmb_saved_templates' );

		wp_send_json_success( [
			'message' => esc_html__( 'All templates cleared successfully!', 'mega-menu-builder-for-elementor' ),
		] );
	}

	/**
	 * AJAX handler for loading template data in Elementor editor
	 */
	public function ajax_load_template_data() {
		// Verify nonce
		check_ajax_referer( 'mmb_load_template', 'mmb_nonce' );

		// Check user capability
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'mega-menu-builder-for-elementor' ) ] );
		}

		// Get template ID
		$template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';

		if ( empty( $template_id ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid template ID.', 'mega-menu-builder-for-elementor' ) ] );
		}

		// Load saved templates from database
		$saved_templates = get_option( 'mmb_saved_templates', [] );

		if ( isset( $saved_templates[ $template_id ] ) && isset( $saved_templates[ $template_id ]['data'] ) ) {
			$template_data = $saved_templates[ $template_id ]['data'];
			
			// Check if it's Elementor export format
			if ( isset( $template_data['content'] ) && is_array( $template_data['content'] ) ) {
				// Extract widget settings from Elementor export
				$extracted_data = $this->extract_widget_data_from_elementor_export( $template_data );
				if ( $extracted_data ) {
					wp_send_json_success( $extracted_data );
					return;
				}
			}
			
			// Return as is if it's simple format
			wp_send_json_success( $template_data );
			return;
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Template not found. Please import it from dashboard first.', 'mega-menu-builder-for-elementor' ) ] );
	}

	/**
	 * Extract widget data from Elementor export format
	 */
	private function extract_widget_data_from_elementor_export( $export_data ) {
		if ( ! isset( $export_data['content'] ) || ! is_array( $export_data['content'] ) ) {
			return false;
		}

		// Recursively search for mmb-mega-menu widget
		$widget_data = $this->find_widget_in_content( $export_data['content'], 'mmb-mega-menu' );
		
		if ( ! $widget_data || ! isset( $widget_data['settings'] ) ) {
			return false;
		}

		$settings = $widget_data['settings'];
		
		// Extract menu_items separately
		$menu_items = isset( $settings['menu_items'] ) ? $settings['menu_items'] : [];
		
		// Clean menu items - remove Elementor internal properties
		$menu_items = $this->clean_elementor_data( $menu_items );
		
		// Remove menu_items from settings to avoid duplication
		unset( $settings['menu_items'] );
		
		// Clean settings too
		$settings = $this->clean_elementor_data( $settings );
		
		// Return ALL settings (colors, typography, spacing, etc.)
		return [
			'settings' => $settings,
			'menu_items' => $menu_items
		];
	}

	/**
	 * Clean Elementor internal properties from data
	 */
	private function clean_elementor_data( $data ) {
		if ( is_array( $data ) ) {
			// Remove ONLY problematic Elementor internal keys
			// Keep _id as it's required for repeater items
			$internal_keys = [ 'activeItemIndex', '__dynamic__', 'isRepeaterItem', 'elType', 'isInner', 'widgetType' ];
			
			foreach ( $internal_keys as $key ) {
				unset( $data[ $key ] );
			}
			
			// Recursively clean nested arrays
			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					$data[ $key ] = $this->clean_elementor_data( $value );
				}
			}
		}
		
		return $data;
	}

	/**
	 * Recursively find widget in Elementor content
	 */
	private function find_widget_in_content( $content, $widget_type ) {
		foreach ( $content as $element ) {
			// Check if this is the widget we're looking for
			if ( isset( $element['widgetType'] ) && $element['widgetType'] === $widget_type ) {
				return $element;
			}

			// Check nested elements
			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$found = $this->find_widget_in_content( $element['elements'], $widget_type );
				if ( $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	/**
	 * Render menu shortcode
	 */
	public function render_menu_shortcode( $atts ) {
		$atts = shortcode_atts( [
			'id' => '',
		], $atts, 'mmb_menu' );

		if ( empty( $atts['id'] ) ) {
			return '<p>' . esc_html__( 'Please provide a menu template ID.', 'mega-menu-builder-for-elementor' ) . '</p>';
		}

		// Load template data
		$template_id = sanitize_text_field( $atts['id'] );
		$templates_file = MMB_PATH . 'includes/menu-templates/index.json';
		
		if ( ! file_exists( $templates_file ) ) {
			return '<p>' . esc_html__( 'Templates file not found.', 'mega-menu-builder-for-elementor' ) . '</p>';
		}

		$json_content = file_get_contents( $templates_file );
		$templates_data = json_decode( $json_content, true );
		$templates = isset( $templates_data['templates'] ) ? $templates_data['templates'] : [];

		// Find template
		foreach ( $templates as $template ) {
			if ( $template['id'] === $template_id ) {
				$template_file = MMB_PATH . 'includes/menu-templates/' . $template['file'];
				
				if ( file_exists( $template_file ) ) {
					$template_json = file_get_contents( $template_file );
					$template_data = json_decode( $template_json, true );
					
					// Render menu using template data
					return $this->render_menu_from_template( $template_data, $template_id );
				}
			}
		}

		return '<p>' . esc_html__( 'Template not found.', 'mega-menu-builder-for-elementor' ) . '</p>';
	}

	/**
	 * Render menu from template data
	 */
	private function render_menu_from_template( $data, $template_id ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '<p>' . esc_html__( 'Elementor is required.', 'mega-menu-builder-for-elementor' ) . '</p>';
		}

		// Enqueue widget assets
		wp_enqueue_style( 'mmb-mega-menu' );
		wp_enqueue_script( 'mmb-mega-menu' );

		$settings = isset( $data['settings'] ) ? $data['settings'] : [];
		$menu_items = isset( $data['menu_items'] ) ? $data['menu_items'] : [];
		
		$layout = isset( $settings['menu_layout'] ) ? $settings['menu_layout'] : 'horizontal';
		$trigger = isset( $settings['dropdown_trigger'] ) ? $settings['dropdown_trigger'] : 'hover';
		$animation = isset( $settings['dropdown_animation'] ) ? $settings['dropdown_animation'] : 'slide';
		$bp = isset( $settings['mobile_breakpoint'] ) ? (int) $settings['mobile_breakpoint'] : 1024;
		$show_ham = isset( $settings['show_hamburger'] ) && 'yes' === $settings['show_hamburger'];
		$is_vert = 'vertical' === $layout;
		$cat_label = isset( $settings['cat_header_label'] ) ? $settings['cat_header_label'] : esc_html__( 'All Categories', 'mega-menu-builder-for-elementor' );
		$cat_open = isset( $settings['cat_header_open'] ) && 'yes' === $settings['cat_header_open'];

		ob_start();
		?>
		<style>
		@media (min-width: <?php echo esc_attr( $bp + 1 ); ?>px) {
			.mmb-shortcode-<?php echo esc_attr( $template_id ); ?> .mmb-mm-hamburger { display: none !important; }
			.mmb-shortcode-<?php echo esc_attr( $template_id ); ?> .mmb-mm-horizontal .mmb-mm-nav { display: flex !important; flex-wrap: wrap !important; }
		}
		@media (max-width: <?php echo esc_attr( $bp ); ?>px) {
			.mmb-shortcode-<?php echo esc_attr( $template_id ); ?> .mmb-mm-hamburger { display: flex !important; }
			.mmb-shortcode-<?php echo esc_attr( $template_id ); ?> .mmb-mm-horizontal .mmb-mm-nav { display: none !important; }
			.mmb-shortcode-<?php echo esc_attr( $template_id ); ?> .mmb-mm-horizontal .mmb-mm-nav.mmb-mm-nav-open {
				display: flex !important; flex-direction: column !important; position: absolute !important;
				top: 100% !important; left: 0 !important; right: 0 !important; z-index: 99999 !important;
				background: #fff !important; border: 1px solid #e8e8e8 !important;
			}
		}
		</style>
		<div class="mmb-shortcode-<?php echo esc_attr( $template_id ); ?>">
			<div class="mmb-mm-wrap mmb-mm-<?php echo esc_attr( $layout ); ?> mmb-mm-anim-<?php echo esc_attr( $animation ); ?>"
				data-mmb-trigger="<?php echo esc_attr( $trigger ); ?>"
				data-mmb-bp="<?php echo esc_attr( $bp ); ?>"
				data-indicator-rotate="yes"
				role="navigation">

			<?php if ( $is_vert ) : ?>
			<button class="mmb-mm-cat-header<?php echo $cat_open ? ' mmb-mm-cat-open' : ''; ?>"
				aria-expanded="<?php echo $cat_open ? 'true' : 'false'; ?>">
				<span class="mmb-mm-cat-icon">&#9776;</span>
				<span class="mmb-mm-cat-label"><?php echo esc_html( $cat_label ); ?></span>
			</button>
			<?php endif; ?>

			<?php if ( ! $is_vert && $show_ham ) : ?>
			<button class="mmb-mm-hamburger" aria-label="<?php esc_attr_e( 'Toggle Menu', 'mega-menu-builder-for-elementor' ); ?>" aria-expanded="false">
				<span class="mmb-mm-hamburger-open">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</span>
				<span class="mmb-mm-hamburger-close">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</span>
			</button>
			<?php endif; ?>

			<ul class="mmb-mm-nav<?php echo ( $is_vert && $cat_open ) ? ' mmb-mm-nav-open' : ''; ?>" role="menubar">
			<?php foreach ( $menu_items as $item ) :
				$type = ! empty( $item['dropdown_type'] ) ? sanitize_key( $item['dropdown_type'] ) : 'none';
				$has_drop = 'none' !== $type;
				$label = ! empty( $item['item_label'] ) ? $item['item_label'] : '';
				$url = ! empty( $item['item_link']['url'] ) ? $item['item_link']['url'] : '#';
				$badge = ! empty( $item['item_badge'] ) ? $item['item_badge'] : '';
			?>
			<li class="mmb-mm-item<?php echo $has_drop ? ' mmb-mm-has-drop' : ''; ?><?php echo ( $has_drop && 'mega' === $type ) ? ' mmb-mm-mega-item' : ''; ?>" role="none">
				<a href="<?php echo esc_url( $url ); ?>" class="mmb-mm-link" role="menuitem">
					<span class="mmb-mm-label"><?php echo esc_html( $label ); ?></span>
					<?php if ( $badge ) : ?>
					<span class="mmb-mm-badge"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
					<?php if ( $has_drop ) : ?>
					<span class="mmb-mm-indicator">&#8964;</span>
					<?php endif; ?>
				</a>
				<?php if ( $has_drop ) : ?>
				<div class="mmb-mm-dropdown<?php echo 'mega' === $type ? ' mmb-mm-mega' : ''; ?>" role="region">
					<?php echo wp_kses_post( $this->render_dropdown_content( $item, $type ) ); ?>
				</div>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
			</ul>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render dropdown content
	 */
	private function render_dropdown_content( $item, $type ) {
		ob_start();
		
		switch ( $type ) {
			case 'simple':
				$subs = ! empty( $item['sub_items'] ) ? $item['sub_items'] : [];
				echo '<ul class="mmb-mm-simple-list">';
				foreach ( $subs as $sub ) {
					$label = ! empty( $sub['sub_label'] ) ? $sub['sub_label'] : '';
					$url = ! empty( $sub['sub_link']['url'] ) ? $sub['sub_link']['url'] : '#';
					echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
				}
				echo '</ul>';
				break;

			case 'mega':
				$cols = ! empty( $item['mega_columns'] ) ? $item['mega_columns'] : [];
				$col_count = max( 1, count( $cols ) );
				echo '<div class="mmb-mm-mega-inner">';
				echo '<div class="mmb-mm-mega-cols" style="--mm-cols:' . esc_attr( $col_count ) . ';">';
				foreach ( $cols as $col ) {
					$heading = ! empty( $col['col_heading'] ) ? $col['col_heading'] : '';
					$links = ! empty( $col['col_links'] ) ? $col['col_links'] : [];
					echo '<div class="mmb-mm-mega-col">';
					if ( $heading ) {
						echo '<div class="mmb-mm-col-title">' . esc_html( $heading ) . '</div>';
					}
					echo '<div class="mmb-mm-col-links">';
					foreach ( $links as $link ) {
						$label = ! empty( $link['link_label'] ) ? $link['link_label'] : '';
						$url = ! empty( $link['link_url']['url'] ) ? $link['link_url']['url'] : '#';
						echo '<a href="' . esc_url( $url ) . '" class="mmb-mm-col-link-item"><span class="mmb-mm-col-link-label">' . esc_html( $label ) . '</span></a>';
					}
					echo '</div></div>';
				}
				echo '</div></div>';
				break;

			case 'posts':
				$post_type = ! empty( $item['posts_post_type'] ) ? $item['posts_post_type'] : 'post';
				$count = ! empty( $item['posts_count'] ) ? (int) $item['posts_count'] : 3;
				$columns = ! empty( $item['posts_columns'] ) ? (int) $item['posts_columns'] : 3;
				$q = new \WP_Query( [ 'post_type' => $post_type, 'posts_per_page' => $count, 'post_status' => 'publish' ] );
				if ( $q->have_posts() ) {
					echo '<div class="mmb-mm-posts-panel"><div class="mmb-mm-posts-grid" style="--mm-post-cols:' . esc_attr( $columns ) . ';">';
					while ( $q->have_posts() ) {
						$q->the_post();
						$thumb = get_the_post_thumbnail_url( null, 'medium' );
						echo '<div class="mmb-mm-post-card"><a href="' . esc_url( get_permalink() ) . '">';
						if ( $thumb ) {
							echo '<img class="mmb-mm-post-thumb" src="' . esc_url( $thumb ) . '" alt="' . esc_attr( get_the_title() ) . '">';
						}
						echo '<span class="mmb-mm-post-title">' . esc_html( get_the_title() ) . '</span>';
						echo '</a></div>';
					}
					wp_reset_postdata();
					echo '</div></div>';
				}
				break;
		}
		
		return ob_get_clean();
	}
}

// Initialize the plugin
Mega_Menu_Builder::instance();
