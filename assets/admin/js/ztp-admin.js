/**
 * Zouetech Portfolio — Admin gallery, image picker, and repeaters.
 *
 * @package Zouetech_Portfolio
 */
(function ($) {
	'use strict';

	function syncGalleryInput($wrap) {
		var ids = [];
		$wrap.find('[data-ztp-gallery-preview] .ztp-gallery-item').each(function () {
			var id = $(this).data('id');
			if (id) {
				ids.push(id);
			}
		});
		$wrap.find('.ztp-gallery-ids').val(ids.join(','));
	}

	function initGallery() {
		var $wrap = $('.ztp-admin-wrap');
		if (!$wrap.length) {
			return;
		}

		$wrap.on('click', '.ztp-gallery-add', function (e) {
			e.preventDefault();
			var frame = wp.media({
				title: 'Select Gallery Images',
				button: { text: 'Add to Gallery' },
				multiple: true,
				library: { type: 'image' }
			});

			frame.on('select', function () {
				var selection = frame.state().get('selection');
				var $preview = $wrap.find('[data-ztp-gallery-preview]');
				selection.each(function (attachment) {
					attachment = attachment.toJSON();
					if ($preview.find('[data-id="' + attachment.id + '"]').length) {
						return;
					}
					var url = (attachment.sizes && attachment.sizes.thumbnail)
						? attachment.sizes.thumbnail.url
						: attachment.url;
					$preview.append(
						'<div class="ztp-gallery-item" data-id="' + attachment.id + '">' +
							'<img src="' + url + '" alt="" />' +
							'<button type="button" class="ztp-gallery-item__remove" aria-label="Remove image">&times;</button>' +
						'</div>'
					);
				});
				syncGalleryInput($wrap);
			});

			frame.open();
		});

		$wrap.on('click', '.ztp-gallery-item__remove', function (e) {
			e.preventDefault();
			$(this).closest('.ztp-gallery-item').remove();
			syncGalleryInput($wrap);
		});

		$wrap.on('click', '.ztp-gallery-clear', function (e) {
			e.preventDefault();
			$wrap.find('[data-ztp-gallery-preview]').empty();
			syncGalleryInput($wrap);
		});
	}

	function initImageFields() {
		$(document).on('click', '.ztp-image-field__pick', function (e) {
			e.preventDefault();
			var $field = $(this).closest('[data-ztp-image-field]');
			var frame = wp.media({
				title: 'Select Image',
				button: { text: 'Use Image' },
				multiple: false,
				library: { type: 'image' }
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				var url = (attachment.sizes && attachment.sizes.thumbnail)
					? attachment.sizes.thumbnail.url
					: attachment.url;
				$field.find('.ztp-image-field__id').val(attachment.id);
				$field.find('.ztp-image-field__preview').html('<img src="' + url + '" alt="" />');
			});

			frame.open();
		});

		$(document).on('click', '.ztp-image-field__clear', function (e) {
			e.preventDefault();
			var $field = $(this).closest('[data-ztp-image-field]');
			$field.find('.ztp-image-field__id').val('0');
			$field.find('.ztp-image-field__preview').html('<span class="dashicons dashicons-admin-users"></span>');
		});
	}

	function reindexRows($repeater) {
		$repeater.find('[data-ztp-row]').each(function (index) {
			$(this).find('input, select, textarea').each(function () {
				var name = $(this).attr('name');
				if (!name) {
					return;
				}
				$(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']').replace('[__INDEX__]', '[' + index + ']'));
			});
		});
	}

	function initRepeaters() {
		$(document).on('click', '[data-ztp-repeater-add]', function (e) {
			e.preventDefault();
			var $repeater = $(this).closest('[data-ztp-repeater]');
			var template = $repeater.find('[data-ztp-repeater-template]').html();
			if (!template) {
				return;
			}
			var index = $repeater.find('[data-ztp-row]').length;
			template = template.replace(/__INDEX__/g, String(index));
			$repeater.find('[data-ztp-repeater-rows]').append(template);
			reindexRows($repeater);
		});

		$(document).on('click', '.ztp-repeater__remove', function (e) {
			e.preventDefault();
			var $repeater = $(this).closest('[data-ztp-repeater]');
			var $rows = $repeater.find('[data-ztp-row]');
			if ($rows.length <= 1) {
				$rows.find('input[type="text"]').val('');
				$rows.find('.ztp-tech-icon__id').val('0');
				$rows.find('.ztp-tech-icon__preview').html('<span class="dashicons dashicons-format-image"></span>');
				return;
			}
			$(this).closest('[data-ztp-row]').remove();
			reindexRows($repeater);
		});

		$(document).on('click', '.ztp-tech-icon__pick', function (e) {
			e.preventDefault();
			var $icon = $(this).closest('[data-ztp-tech-icon]');
			var frame = wp.media({
				title: 'Select Technology Icon',
				button: { text: 'Use Icon' },
				multiple: false,
				library: { type: 'image' }
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				var url = (attachment.sizes && attachment.sizes.thumbnail)
					? attachment.sizes.thumbnail.url
					: attachment.url;
				$icon.find('.ztp-tech-icon__id').val(attachment.id);
				$icon.find('.ztp-tech-icon__preview').html('<img src="' + url + '" alt="" />');
			});

			frame.open();
		});

		$(document).on('click', '.ztp-tech-icon__clear', function (e) {
			e.preventDefault();
			var $icon = $(this).closest('[data-ztp-tech-icon]');
			$icon.find('.ztp-tech-icon__id').val('0');
			$icon.find('.ztp-tech-icon__preview').html('<span class="dashicons dashicons-format-image"></span>');
		});
	}

	$(function () {
		initGallery();
		initImageFields();
		initRepeaters();
	});
}(jQuery));
