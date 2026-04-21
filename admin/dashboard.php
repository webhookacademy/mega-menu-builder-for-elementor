<?php
/**
 * Admin Dashboard Page
 *
 * @package Mega_Menu_Builder_For_Elementor
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template file with local scope variables

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get templates
$mmb_templates_file = MMB_PATH . 'includes/menu-templates/index.json';
$mmb_templates_data = [];

if ( file_exists( $mmb_templates_file ) ) {
	$json_content = file_get_contents( $mmb_templates_file );
	$mmb_templates_data = json_decode( $json_content, true );
}

$mmb_templates = isset( $mmb_templates_data['templates'] ) ? $mmb_templates_data['templates'] : [];
$mmb_categories = isset( $mmb_templates_data['categories'] ) ? $mmb_templates_data['categories'] : [];

// Get already imported templates
$mmb_saved_templates = get_option( 'mmb_saved_templates', [] );
$mmb_imported_template_ids = [];
foreach ( $mmb_saved_templates as $saved ) {
	if ( isset( $saved['original_id'] ) ) {
		$mmb_imported_template_ids[] = $saved['original_id'];
	}
}

// Handle AJAX import
if ( isset( $_POST['mmb_import_template'] ) && check_admin_referer( 'mmb_import_template', 'mmb_nonce' ) ) {
	$template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
	
	foreach ( $mmb_templates as $template ) {
		if ( $template['id'] === $template_id ) {
			$template_file = MMB_PATH . 'includes/menu-templates/' . $template['file'];
			
			if ( file_exists( $template_file ) ) {
				$template_json = file_get_contents( $template_file );
				
				// Store in transient for Elementor to use
				set_transient( 'mmb_imported_template_' . get_current_user_id(), $template_json, 300 );
				
				wp_send_json_success( [
					'message' => 'Template imported successfully!',
					'data' => json_decode( $template_json, true )
				] );
			}
		}
	}
	
	wp_send_json_error( [ 'message' => 'Template not found!' ] );
}
?>

<div class="wrap mmb-dashboard">
	<div class="mmb-header">
		<div class="mmb-header-content">
			<h1>
				<span class="mmb-logo">
					<svg width="32" height="32" viewBox="0 0 32 32" fill="none">
						<rect width="32" height="32" rx="6" fill="#7b2ff7"/>
						<path d="M8 12h16M8 16h16M8 20h16" stroke="white" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</span>
				<?php esc_html_e( 'Mega Menu Builder', 'mega-menu-builder-for-elementor' ); ?>
			</h1>
			<p class="mmb-subtitle"><?php esc_html_e( 'Create stunning navigation menus with pre-designed templates', 'mega-menu-builder-for-elementor' ); ?></p>
		</div>
		<div class="mmb-header-actions">
			<a href="#" class="button" target="_blank">
				<span class="dashicons dashicons-book"></span>
				<?php esc_html_e( 'Documentation', 'mega-menu-builder-for-elementor' ); ?>
			</a>
			<a href="#" class="button" target="_blank">
				<span class="dashicons dashicons-sos"></span>
				<?php esc_html_e( 'Support', 'mega-menu-builder-for-elementor' ); ?>
			</a>
		</div>
	</div>

	<div class="mmb-stats">
		<div class="mmb-stat-card">
			<div class="mmb-stat-icon">
				<span class="dashicons dashicons-layout"></span>
			</div>
			<div class="mmb-stat-content">
				<h3><?php echo count( $mmb_templates ); ?></h3>
				<p><?php esc_html_e( 'Templates Available', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
		</div>
		<div class="mmb-stat-card">
			<div class="mmb-stat-icon">
				<span class="dashicons dashicons-saved"></span>
			</div>
			<div class="mmb-stat-content">
				<h3><?php echo count( $mmb_saved_templates ); ?></h3>
				<p><?php esc_html_e( 'Templates Imported', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
		</div>
		<div class="mmb-stat-card">
			<div class="mmb-stat-icon">
				<span class="dashicons dashicons-category"></span>
			</div>
			<div class="mmb-stat-content">
				<h3><?php echo count( $mmb_categories ); ?></h3>
				<p><?php esc_html_e( 'Categories', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
		</div>
		<div class="mmb-stat-card">
			<div class="mmb-stat-icon">
				<span class="dashicons dashicons-smartphone"></span>
			</div>
			<div class="mmb-stat-content">
				<h3><?php esc_html_e( '100%', 'mega-menu-builder-for-elementor' ); ?></h3>
				<p><?php esc_html_e( 'Mobile Responsive', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Imported Templates Management -->
	<?php if ( ! empty( $mmb_saved_templates ) ) : ?>
	<div class="mmb-imported-templates-section">
		<div class="mmb-section-header">
			<h2>
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Your Imported Templates', 'mega-menu-builder-for-elementor' ); ?>
			</h2>
			<div style="display:flex;align-items:center;gap:15px;">
				<p class="mmb-section-desc" style="margin:0;"><?php esc_html_e( 'Manage your imported templates. You can delete templates you no longer need.', 'mega-menu-builder-for-elementor' ); ?></p>
				<button class="button button-secondary mmb-clear-all-templates" style="white-space:nowrap;">
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Clear All', 'mega-menu-builder-for-elementor' ); ?>
				</button>
			</div>
		</div>
		<div class="mmb-imported-templates-list">
			<?php foreach ( $mmb_saved_templates as $saved_id => $saved_template ) : ?>
				<div class="mmb-imported-item" data-template-id="<?php echo esc_attr( $saved_id ); ?>">
					<div class="mmb-imported-item-icon">
						<span class="dashicons dashicons-menu-alt"></span>
					</div>
					<div class="mmb-imported-item-content">
						<h4><?php echo esc_html( $saved_template['title'] ); ?></h4>
						<p class="mmb-imported-meta">
							<span class="dashicons dashicons-calendar-alt"></span>
							<?php
							printf( 
								/* translators: %s: date and time when template was imported */
								esc_html__( 'Imported on %s', 'mega-menu-builder-for-elementor' ),
								esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $saved_template['imported_at'] ) ) )
							); 
							?>
						</p>
					</div>
					<div class="mmb-imported-item-actions">
						<button class="button mmb-btn-delete-template" data-template-id="<?php echo esc_attr( $saved_id ); ?>" title="<?php esc_attr_e( 'Delete Template', 'mega-menu-builder-for-elementor' ); ?>">
							<span class="dashicons dashicons-trash"></span>
							<?php esc_html_e( 'Delete', 'mega-menu-builder-for-elementor' ); ?>
						</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- Main Content Tabs -->
	<div class="mmb-main-tabs">
		<div class="mmb-tab-buttons">
			<button class="mmb-tab-btn active" data-tab="templates">
				<span class="dashicons dashicons-layout"></span>
				<?php esc_html_e( 'Pre-Designed Templates', 'mega-menu-builder-for-elementor' ); ?>
			</button>
			<button class="mmb-tab-btn" data-tab="features">
				<span class="dashicons dashicons-star-filled"></span>
				<?php esc_html_e( 'Key Features', 'mega-menu-builder-for-elementor' ); ?>
			</button>
		</div>

		<!-- Templates Tab Content -->
		<div class="mmb-tab-content active" id="mmb-tab-templates">
			<div class="mmb-templates-section">
		<div class="mmb-section-header">
			<h2><?php esc_html_e( 'Pre-Designed Templates', 'mega-menu-builder-for-elementor' ); ?></h2>
			<div class="mmb-filter-tabs">
				<button class="mmb-filter-tab active" data-category="all">
					<?php esc_html_e( 'All Templates', 'mega-menu-builder-for-elementor' ); ?>
				</button>
				<?php foreach ( $mmb_categories as $category ) : ?>
					<button class="mmb-filter-tab" data-category="<?php echo esc_attr( $category['slug'] ); ?>">
						<?php echo esc_html( $category['name'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="mmb-templates-grid">
			<?php foreach ( $mmb_templates as $template ) : 
				$is_imported = in_array( $template['id'], $mmb_imported_template_ids );
			?>
				<div class="mmb-template-card <?php echo $is_imported ? 'mmb-template-imported' : ''; ?>" data-category="<?php echo esc_attr( $template['category'] ); ?>">
					<div class="mmb-template-thumbnail">
						<?php if ( $is_imported ) : ?>
							<div class="mmb-imported-badge">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'Imported', 'mega-menu-builder-for-elementor' ); ?>
							</div>
						<?php endif; ?>
						<img src="<?php echo esc_url( $template['thumbnail'] ); ?>" alt="<?php echo esc_attr( $template['title'] ); ?>">
						<div class="mmb-template-overlay">
							<?php if ( $is_imported ) : ?>
								<div class="mmb-already-imported">
									<span class="dashicons dashicons-saved"></span>
									<p><?php esc_html_e( 'Already Imported', 'mega-menu-builder-for-elementor' ); ?></p>
									<div class="mmb-how-to-use">
										<h4><?php esc_html_e( 'How to use:', 'mega-menu-builder-for-elementor' ); ?></h4>
										<ol>
											<li><?php esc_html_e( 'Open Elementor editor', 'mega-menu-builder-for-elementor' ); ?></li>
											<li><?php esc_html_e( 'Add "Mega Menu" widget', 'mega-menu-builder-for-elementor' ); ?></li>
											<li><?php esc_html_e( 'Select this template', 'mega-menu-builder-for-elementor' ); ?></li>
											<li><?php esc_html_e( 'Click "Apply Template"', 'mega-menu-builder-for-elementor' ); ?></li>
											<li><?php esc_html_e( 'Customize & Publish!', 'mega-menu-builder-for-elementor' ); ?></li>
										</ol>
									</div>
								</div>
							<?php else : ?>
								<button class="mmb-btn-import" data-template-id="<?php echo esc_attr( $template['id'] ); ?>">
									<span class="dashicons dashicons-download"></span>
									<?php esc_html_e( 'Import Now', 'mega-menu-builder-for-elementor' ); ?>
								</button>
							<?php endif; ?>
							<?php if ( ! empty( $template['preview_url'] ) ) : ?>
								<a href="<?php echo esc_url( $template['preview_url'] ); ?>" class="mmb-btn-preview" target="_blank">
									<span class="dashicons dashicons-visibility"></span>
									<?php esc_html_e( 'Preview', 'mega-menu-builder-for-elementor' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
					<div class="mmb-template-content">
						<h3><?php echo esc_html( $template['title'] ); ?></h3>
						<p><?php echo esc_html( $template['description'] ); ?></p>
						<?php if ( ! empty( $template['features'] ) ) : ?>
							<div class="mmb-template-features">
								<?php foreach ( $template['features'] as $feature ) : ?>
									<span class="mmb-feature-tag">
										<span class="dashicons dashicons-yes"></span>
										<?php echo esc_html( $feature ); ?>
									</span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $template['tags'] ) ) : ?>
							<div class="mmb-template-tags">
								<?php foreach ( $template['tags'] as $tag ) : ?>
									<span class="mmb-tag"><?php echo esc_html( $tag ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
	</div>

		<!-- Features Tab Content -->
		<div class="mmb-tab-content" id="mmb-tab-features">
			<div class="mmb-features">
				<div class="mmb-features-grid">
			<div class="mmb-feature-card">
				<span class="dashicons dashicons-admin-customizer"></span>
				<h3><?php esc_html_e( 'Drag & Drop Builder', 'mega-menu-builder-for-elementor' ); ?></h3>
				<p><?php esc_html_e( 'Build menus visually with Elementor\'s intuitive interface', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
			<div class="mmb-feature-card">
				<span class="dashicons dashicons-grid-view"></span>
				<h3><?php esc_html_e( 'Mega Menu Panels', 'mega-menu-builder-for-elementor' ); ?></h3>
				<p><?php esc_html_e( 'Create multi-column dropdowns with images and icons', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
			<div class="mmb-feature-card">
				<span class="dashicons dashicons-cart"></span>
				<h3><?php esc_html_e( 'WooCommerce Ready', 'mega-menu-builder-for-elementor' ); ?></h3>
				<p><?php esc_html_e( 'Display products directly in your menu dropdowns', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
			<div class="mmb-feature-card">
				<span class="dashicons dashicons-admin-post"></span>
				<h3><?php esc_html_e( 'Posts Integration', 'mega-menu-builder-for-elementor' ); ?></h3>
				<p><?php esc_html_e( 'Show recent posts with thumbnails in dropdowns', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
			<div class="mmb-feature-card">
				<span class="dashicons dashicons-smartphone"></span>
				<h3><?php esc_html_e( 'Mobile Responsive', 'mega-menu-builder-for-elementor' ); ?></h3>
				<p><?php esc_html_e( 'Hamburger menu with smooth animations on mobile', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
			<div class="mmb-feature-card">
				<span class="dashicons dashicons-art"></span>
				<h3><?php esc_html_e( 'Style Customization', 'mega-menu-builder-for-elementor' ); ?></h3>
				<p><?php esc_html_e( 'Complete control over colors, fonts, and spacing', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
				</div>
			</div>
		</div>
	</div>

	<div class="mmb-cta-section">
		<div class="mmb-cta-content">
			<h2><?php esc_html_e( 'Ready to Create Your Menu?', 'mega-menu-builder-for-elementor' ); ?></h2>
			<p><?php esc_html_e( 'Start building beautiful navigation menus with Elementor', 'mega-menu-builder-for-elementor' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" class="button button-primary button-hero">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Create New Page', 'mega-menu-builder-for-elementor' ); ?>
			</a>
		</div>
	</div>
</div>

<!-- Import Modal -->
<div id="mmb-import-modal" class="mmb-modal" style="display: none;">
	<div class="mmb-modal-content">
		<span class="mmb-modal-close">&times;</span>
		<div class="mmb-modal-header">
			<h2><?php esc_html_e( 'Import Template', 'mega-menu-builder-for-elementor' ); ?></h2>
		</div>
		<div class="mmb-modal-body">
			<div class="mmb-import-progress">
				<div class="mmb-spinner"></div>
				<p><?php esc_html_e( 'Importing template...', 'mega-menu-builder-for-elementor' ); ?></p>
			</div>
			<div class="mmb-import-success" style="display: none;">
				<span class="dashicons dashicons-yes-alt"></span>
				<h3><?php esc_html_e( 'Template Imported Successfully!', 'mega-menu-builder-for-elementor' ); ?></h3>
				<p><?php esc_html_e( 'The template has been saved. You can now use it in Mega Menu widget.', 'mega-menu-builder-for-elementor' ); ?></p>
				<div class="mmb-usage-instructions">
					<h4><?php esc_html_e( 'How to use:', 'mega-menu-builder-for-elementor' ); ?></h4>
					<ol>
						<li><?php esc_html_e( 'Open Elementor editor on any page', 'mega-menu-builder-for-elementor' ); ?></li>
						<li><?php esc_html_e( 'Add "Mega Menu" widget', 'mega-menu-builder-for-elementor' ); ?></li>
						<li><?php esc_html_e( 'Go to "Template" section', 'mega-menu-builder-for-elementor' ); ?></li>
						<li><?php esc_html_e( 'Select your imported template from dropdown', 'mega-menu-builder-for-elementor' ); ?></li>
						<li><?php esc_html_e( 'Click "Apply Template" button', 'mega-menu-builder-for-elementor' ); ?></li>
						<li><?php esc_html_e( 'Customize as needed and publish!', 'mega-menu-builder-for-elementor' ); ?></li>
					</ol>
				</div>
			</div>
			<div class="mmb-import-error" style="display: none;">
				<span class="dashicons dashicons-warning"></span>
				<h3><?php esc_html_e( 'Import Failed', 'mega-menu-builder-for-elementor' ); ?></h3>
				<p class="mmb-error-message"></p>
				<button class="button mmb-modal-close"><?php esc_html_e( 'Close', 'mega-menu-builder-for-elementor' ); ?></button>
			</div>
		</div>
	</div>
</div>
