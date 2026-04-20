/* global elementor, jQuery, mmbEditorData */
(function($) {
	'use strict';

	// Wait for Elementor editor to be ready
	$(window).on('elementor:init', function() {
		console.log('[MMB] Elementor editor initialized');

		// Listen for template apply button click
		elementor.channels.editor.on('mmb:applyTemplate', function(panel) {
			console.log('[MMB] Apply template button clicked');
			console.log('[MMB] Panel:', panel);
			
			// Get current editing element
			var currentElement = elementor.getPanelView().getCurrentPageView();
			console.log('[MMB] Current Element:', currentElement);
			
			if (!currentElement || !currentElement.model) {
				console.error('[MMB] No element model found');
				alert('Please try again. Could not find widget.');
				return;
			}
			
			var settings = currentElement.model.get('settings');
			console.log('[MMB] Settings:', settings);
			
			var templateId = settings.get('load_template');
			console.log('[MMB] Template ID:', templateId);
			
			if (!templateId) {
				alert('Please select a template first.');
				return;
			}

			// Show loading
			var $panel = $('.elementor-panel');
			$panel.append('<div class="mmb-loading-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:99999;display:flex;align-items:center;justify-content:center;"><div style="background:#fff;padding:30px;border-radius:8px;text-align:center;"><div class="elementor-loading" style="margin:0 auto 15px;"></div><p style="margin:0;font-size:16px;color:#333;">Loading template...</p></div></div>');

			console.log('[MMB] Sending AJAX request...');

			// Load template data via AJAX
			$.ajax({
				url: mmbEditorData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'mmb_load_template_data',
					template_id: templateId,
					mmb_nonce: mmbEditorData.nonce
				},
				success: function(response) {
					$('.mmb-loading-overlay').remove();
					
					console.log('[MMB] AJAX Response:', response);
					
					if (response.success && response.data) {
						var templateData = response.data;
						
						console.log('[MMB] Template Data:', templateData);
						
						// Apply settings
						if (templateData.settings) {
							$.each(templateData.settings, function(key, value) {
								console.log('[MMB] Setting:', key, value);
								settings.set(key, value);
							});
						}
						
						// Apply menu items
						if (templateData.menu_items) {
							console.log('[MMB] Menu Items:', templateData.menu_items);
							settings.set('menu_items', templateData.menu_items);
						}
						
						// Trigger change to update preview
						currentElement.model.trigger('change');
						
						// Show success message
						elementor.notifications.showToast({
							message: 'Template loaded successfully! Scroll down to see Menu Items.',
							buttons: []
						});
						
						console.log('[MMB] Template applied successfully');
					} else {
						console.error('[MMB] Failed response:', response);
						alert('Failed to load template: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
					}
				},
				error: function(xhr, status, error) {
					$('.mmb-loading-overlay').remove();
					alert('Network error: ' + error);
					console.error('[MMB] AJAX error:', error);
				}
			});
		});
	});

})(jQuery);
