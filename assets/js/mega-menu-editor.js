/* global elementor, jQuery, mmbEditorData */
(function($) {
	'use strict';

	// Wait for Elementor editor to be ready
	$(window).on('elementor:init', function() {
		console.log('[MMB] Elementor editor initialized');

		// Listen for template dropdown change
		elementor.channels.editor.on('change', function(controlView) {
			// Check if it's the load_template control
			if (controlView && controlView.model && controlView.model.get('name') === 'load_template') {
				var selectedValue = controlView.getControlValue();
				console.log('[MMB] Template dropdown changed:', selectedValue);
				
				// If "Select Imported Template" is selected (empty value)
				if (!selectedValue || selectedValue === '') {
					console.log('[MMB] Default option selected, resetting to widget defaults');
					
					// Get current element
					var currentElement = elementor.getPanelView().getCurrentPageView();
					if (currentElement && currentElement.model) {
						// Reset menu_items to default
						var defaultMenuItems = [
							{ 'item_label': 'Home', 'item_link': { 'url': '#' }, 'dropdown_type': 'none' },
							{ 'item_label': 'Shop', 'item_link': { 'url': '#' }, 'dropdown_type': 'mega', 'item_badge': 'NEW' },
							{ 'item_label': 'Blog', 'item_link': { 'url': '#' }, 'dropdown_type': 'posts' },
							{ 'item_label': 'Contact', 'item_link': { 'url': '#' }, 'dropdown_type': 'none' }
						];
						
						currentElement.model.setSetting('menu_items', defaultMenuItems);
						currentElement.model.trigger('change');
						currentElement.model.renderRemoteServer();
						
						elementor.notifications.showToast({
							message: 'Reset to default menu items.',
							buttons: []
						});
					}
				}
			}
		});

		// Listen for template apply button click
		elementor.channels.editor.on('mmb:applyTemplate', function(panel) {
			console.log('[MMB] Apply template button clicked');
			
			// Get current editing element
			var currentElement = elementor.getPanelView().getCurrentPageView();
			
			if (!currentElement || !currentElement.model) {
				console.error('[MMB] No element model found');
				elementor.notifications.showToast({
					message: 'Please try again.',
					buttons: []
				});
				return;
			}
			
			var settings = currentElement.model.get('settings');
			var templateId = settings.get('load_template');
			
			console.log('[MMB] Template ID:', templateId);
			
			if (!templateId) {
				elementor.notifications.showToast({
					message: 'Please select a template first.',
					buttons: []
				});
				return;
			}

			// Show loading toast
			elementor.notifications.showToast({
				message: 'Applying template...',
				buttons: []
			});

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
					console.log('[MMB] AJAX Response:', response);
					
					if (response.success && response.data) {
						var templateData = response.data;
						console.log('[MMB] Template Data:', templateData);
						
						var elementModel = currentElement.model;
						
						// Apply all settings
						if (templateData.settings) {
							$.each(templateData.settings, function(key, value) {
								elementModel.setSetting(key, value);
							});
						}
						
						if (templateData.menu_items) {
							elementModel.setSetting('menu_items', templateData.menu_items);
						}
						
						// Trigger change
						elementModel.trigger('change');
						
						// Force auto-save then reload (no "Leave site?" dialog)
						elementor.notifications.showToast({
							message: 'Saving and refreshing...',
							buttons: []
						});
						
						// Use Elementor's save command
						$e.run('document/save/auto', {
							force: true,
							onSuccess: function() {
								console.log('[MMB] Saved successfully, reloading...');
								// Reload after successful save
								window.location.reload();
							}
						});
						
						console.log('[MMB] Template applied, saving...');
					} else {
						console.error('[MMB] Failed response:', response);
						elementor.notifications.showToast({
							message: response.data && response.data.message ? response.data.message : 'Failed to load template.',
							buttons: []
						});
					}
				},
				error: function(xhr, status, error) {
					elementor.notifications.showToast({
						message: 'Network error. Please try again.',
						buttons: []
					});
					console.error('[MMB] AJAX error:', error);
				}
			});
		});
	});

})(jQuery);
