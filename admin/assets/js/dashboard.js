(function($) {
	'use strict';

	$(document).ready(function() {
		
		// Tab switching
		$('.mmb-tab-btn').on('click', function() {
			var tabId = $(this).data('tab');
			
			// Update active button
			$('.mmb-tab-btn').removeClass('active');
			$(this).addClass('active');
			
			// Update active content
			$('.mmb-tab-content').removeClass('active');
			$('#mmb-tab-' + tabId).addClass('active');
			
			// Smooth scroll to tabs
			$('html, body').animate({
				scrollTop: $('.mmb-main-tabs').offset().top - 100
			}, 400);
		});
		
		// Filter templates by category
		$('.mmb-filter-tab').on('click', function() {
			var category = $(this).data('category');
			
			// Update active tab
			$('.mmb-filter-tab').removeClass('active');
			$(this).addClass('active');
			
			// Filter templates
			if (category === 'all') {
				$('.mmb-template-card').removeClass('hidden').fadeIn(300);
			} else {
				$('.mmb-template-card').each(function() {
					if ($(this).data('category') === category) {
						$(this).removeClass('hidden').fadeIn(300);
					} else {
						$(this).addClass('hidden').fadeOut(300);
					}
				});
			}
		});

		// Import template
		$('.mmb-btn-import').on('click', function(e) {
			e.preventDefault();
			
			var $btn = $(this);
			var templateId = $btn.data('template-id');
			var $modal = $('#mmb-import-modal');
			
			// Show modal
			$modal.fadeIn(300);
			
			// Reset modal state
			$('.mmb-import-progress').show();
			$('.mmb-import-success').hide();
			$('.mmb-import-error').hide();
			
			// AJAX import
			$.ajax({
				url: mmbDashboard.ajaxUrl,
				type: 'POST',
				data: {
					action: 'mmb_import_template',
					template_id: templateId,
					mmb_nonce: mmbDashboard.nonce
				},
				success: function(response) {
					$('.mmb-import-progress').hide();
					
					if (response.success) {
						$('.mmb-import-success').fadeIn(300);
						
						// Reload page after 2 seconds to show updated list
						setTimeout(function() {
							location.reload();
						}, 2000);
					} else {
						$('.mmb-import-error').fadeIn(300);
						$('.mmb-error-message').text(response.data.message || 'Import failed. Please try again.');
					}
				},
				error: function(xhr, status, error) {
					$('.mmb-import-progress').hide();
					$('.mmb-import-error').fadeIn(300);
					$('.mmb-error-message').text('Network error. Please check your connection and try again.');
					console.error('Import error:', error);
				}
			});
		});

		// Delete template
		$('.mmb-btn-delete-template').on('click', function(e) {
			e.preventDefault();
			
			var $btn = $(this);
			var templateId = $btn.data('template-id');
			var $item = $btn.closest('.mmb-imported-item');
			var templateName = $item.find('h4').text();
			
			// Show SweetAlert confirmation
			Swal.fire({
				title: 'Delete Template?',
				html: 'Are you sure you want to delete <strong>"' + templateName + '"</strong>?<br><small style="color:#999;">This action cannot be undone.</small>',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#e74c3c',
				cancelButtonColor: '#95a5a6',
				confirmButtonText: '<i class="dashicons dashicons-trash" style="font-size:16px;width:16px;height:16px;"></i> Yes, Delete It',
				cancelButtonText: 'Cancel',
				reverseButtons: true,
				customClass: {
					confirmButton: 'mmb-swal-confirm',
					cancelButton: 'mmb-swal-cancel'
				}
			}).then((result) => {
				if (result.isConfirmed) {
					// Show loading
					Swal.fire({
						title: 'Deleting...',
						html: 'Please wait while we delete the template.',
						allowOutsideClick: false,
						allowEscapeKey: false,
						didOpen: () => {
							Swal.showLoading();
						}
					});
					
					// AJAX delete
					$.ajax({
						url: mmbDashboard.ajaxUrl,
						type: 'POST',
						data: {
							action: 'mmb_delete_template',
							template_id: templateId,
							mmb_nonce: mmbDashboard.nonce
						},
						success: function(response) {
							if (response.success) {
								// Show success message
								Swal.fire({
									title: 'Deleted!',
									text: 'Template has been deleted successfully.',
									icon: 'success',
									timer: 1500,
									showConfirmButton: false
								}).then(() => {
									// Fade out and remove item
									$item.fadeOut(400, function() {
										$(this).remove();
										
										// Check if list is empty
										if ($('.mmb-imported-item').length === 0) {
											location.reload();
										}
									});
								});
							} else {
								Swal.fire({
									title: 'Error!',
									text: response.data.message || 'Failed to delete template.',
									icon: 'error',
									confirmButtonColor: '#667eea'
								});
							}
						},
						error: function(xhr, status, error) {
							Swal.fire({
								title: 'Network Error!',
								text: 'Please check your connection and try again.',
								icon: 'error',
								confirmButtonColor: '#667eea'
							});
							console.error('Delete error:', error);
						}
					});
				}
			});
		});

		// Clear all templates
		$('.mmb-clear-all-templates').on('click', function(e) {
			e.preventDefault();
			
			// Show SweetAlert confirmation
			Swal.fire({
				title: 'Clear All Templates?',
				html: 'Are you sure you want to delete <strong>ALL</strong> imported templates?<br><small style="color:#e74c3c;">This will remove all templates from database. You can re-import them later.</small>',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#e74c3c',
				cancelButtonColor: '#95a5a6',
				confirmButtonText: '<i class="dashicons dashicons-trash" style="font-size:16px;width:16px;height:16px;"></i> Yes, Clear All',
				cancelButtonText: 'Cancel',
				reverseButtons: true,
				customClass: {
					confirmButton: 'mmb-swal-confirm',
					cancelButton: 'mmb-swal-cancel'
				}
			}).then((result) => {
				if (result.isConfirmed) {
					// Show loading
					Swal.fire({
						title: 'Clearing...',
						html: 'Please wait while we clear all templates.',
						allowOutsideClick: false,
						allowEscapeKey: false,
						didOpen: () => {
							Swal.showLoading();
						}
					});
					
					// AJAX clear all
					$.ajax({
						url: mmbDashboard.ajaxUrl,
						type: 'POST',
						data: {
							action: 'mmb_clear_all_templates',
							mmb_nonce: mmbDashboard.nonce
						},
						success: function(response) {
							if (response.success) {
								Swal.fire({
									title: 'Cleared!',
									text: 'All templates have been cleared. Reloading page...',
									icon: 'success',
									timer: 1500,
									showConfirmButton: false
								}).then(() => {
									location.reload();
								});
							} else {
								Swal.fire({
									title: 'Error!',
									text: response.data.message || 'Failed to clear templates.',
									icon: 'error',
									confirmButtonColor: '#667eea'
								});
							}
						},
						error: function(xhr, status, error) {
							Swal.fire({
								title: 'Network Error!',
								text: 'Please check your connection and try again.',
								icon: 'error',
								confirmButtonColor: '#667eea'
							});
							console.error('Clear error:', error);
						}
					});
				}
			});
		});

		// Close modal
		$('.mmb-modal-close').on('click', function() {
			$('#mmb-import-modal').fadeOut(300);
		});

		// Close modal on outside click
		$(window).on('click', function(e) {
			if ($(e.target).is('#mmb-import-modal')) {
				$('#mmb-import-modal').fadeOut(300);
			}
		});

		// Animate stats on scroll
		var statsAnimated = false;
		$(window).on('scroll', function() {
			if (!statsAnimated && isElementInViewport($('.mmb-stats'))) {
				animateStats();
				statsAnimated = true;
			}
		});

		function isElementInViewport(el) {
			if (el.length === 0) return false;
			var rect = el[0].getBoundingClientRect();
			return (
				rect.top >= 0 &&
				rect.left >= 0 &&
				rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
				rect.right <= (window.innerWidth || document.documentElement.clientWidth)
			);
		}

		function animateStats() {
			$('.mmb-stat-card').each(function(index) {
				$(this).delay(index * 100).queue(function(next) {
					$(this).addClass('animated');
					next();
				});
			});
		}

		// Add animation class
		$('<style>')
			.prop('type', 'text/css')
			.html('.mmb-stat-card.animated { animation: mmb-fade-in-up 0.5s ease forwards; } @keyframes mmb-fade-in-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }')
			.appendTo('head');

		// Smooth scroll for anchor links
		$('a[href^="#"]').on('click', function(e) {
			var target = $(this.getAttribute('href'));
			if (target.length) {
				e.preventDefault();
				$('html, body').stop().animate({
					scrollTop: target.offset().top - 100
				}, 800);
			}
		});

		// Add hover effect to template cards
		$('.mmb-template-card').hover(
			function() {
				$(this).find('.mmb-template-thumbnail img').css('transform', 'scale(1.05)');
			},
			function() {
				$(this).find('.mmb-template-thumbnail img').css('transform', 'scale(1)');
			}
		);

		// Keyboard navigation for modal
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' && $('#mmb-import-modal').is(':visible')) {
				$('#mmb-import-modal').fadeOut(300);
			}
		});

		// Add loading state to buttons
		$(document).on('click', '.mmb-btn-import', function() {
			var $btn = $(this);
			var originalText = $btn.html();
			
			$btn.prop('disabled', true)
				.html('<span class="dashicons dashicons-update-alt" style="animation: mmb-spin 1s linear infinite;"></span> Importing...');
			
			// Reset button after modal interaction
			setTimeout(function() {
				$btn.prop('disabled', false).html(originalText);
			}, 5000);
		});

		// Console welcome message
		console.log('%c🚀 Mega Menu Builder for Elementor', 'color: #667eea; font-size: 20px; font-weight: bold;');
		console.log('%cVersion: 1.0.0', 'color: #764ba2; font-size: 14px;');
		console.log('%cThank you for using our plugin!', 'color: #555; font-size: 12px;');
	});

})(jQuery);
