/**
 * Featured Portfolio Showcase — queue rotation + interactive gallery.
 *
 * @package Zouetech_Portfolio
 */
(function () {
	'use strict';

	var IMG_FADE_MS = 400;

	function parseJsonAttr(el, name) {
		var raw = el.getAttribute(name);
		if (!raw) {
			return [];
		}
		try {
			return JSON.parse(raw);
		} catch (e) {
			return [];
		}
	}

	function escapeAttr(str) {
		return String(str || '')
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
	}

	function escapeHtml(str) {
		return String(str || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
	}

	function preloadUrl(url) {
		if (!url) {
			return;
		}
		var img = new Image();
		img.decoding = 'async';
		img.src = url;
	}

	function ZtpFeaturedShowcase(root) {
		this.root = root;
		this.projects = parseJsonAttr(root, 'data-ztp-projects');
		this.duration = parseInt(root.getAttribute('data-ztp-duration') || '500', 10) || 500;
		this.cardsCount = parseInt(root.getAttribute('data-ztp-cards') || '3', 10) || 3;
		this.head = 0;
		this.busy = false;
		this.activeGalleryIndex = -1;
		this.imageBusy = false;
		this.preloadCache = {};

		this.featuredImg = root.querySelector('[data-ztp-featured-img]');
		this.categoryEl = root.querySelector('[data-ztp-category]');
		this.titleEl = root.querySelector('[data-ztp-title]');
		this.excerptEl = root.querySelector('[data-ztp-excerpt]');
		this.copyEl = root.querySelector('[data-ztp-copy]');
		this.gallery = root.querySelector('[data-ztp-gallery]');
		this.galleryTrack = root.querySelector('[data-ztp-gallery-track]');
		this.galleryPrev = root.querySelector('[data-ztp-gallery-prev]');
		this.galleryNext = root.querySelector('[data-ztp-gallery-next]');
		this.cardsWrap = root.querySelector('[data-ztp-cards]');
		this.prevBtn = root.querySelector('[data-ztp-prev]');
		this.nextBtn = root.querySelector('[data-ztp-next]');

		if (!this.projects.length) {
			return;
		}

		this.bind();
		this.initGalleryInteractions();
		this.resetGalleryForProject(this.getFeatured(), false);
	}

	ZtpFeaturedShowcase.prototype.mod = function (n) {
		var len = this.projects.length;
		return ((n % len) + len) % len;
	};

	ZtpFeaturedShowcase.prototype.getFeatured = function () {
		return this.projects[this.head];
	};

	ZtpFeaturedShowcase.prototype.getCardIndexes = function () {
		var indexes = [];
		var len = this.projects.length;
		var max = Math.min(this.cardsCount, Math.max(0, len - 1));
		var i;
		for (i = 0; i < max; i++) {
			indexes.push(this.mod(this.head + 1 + i));
		}
		return indexes;
	};

	ZtpFeaturedShowcase.prototype.bind = function () {
		var self = this;

		if (this.prevBtn) {
			this.prevBtn.addEventListener('click', function () {
				self.goPrev();
			});
		}
		if (this.nextBtn) {
			this.nextBtn.addEventListener('click', function () {
				self.goNext();
			});
		}

		this.root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') {
				e.preventDefault();
				self.goNext();
			} else if (e.key === 'ArrowLeft') {
				e.preventDefault();
				self.goPrev();
			}
		});
	};

	ZtpFeaturedShowcase.prototype.initGalleryInteractions = function () {
		var self = this;
		var track = this.galleryTrack;
		if (!track) {
			return;
		}

		// Delegated thumb clicks → update large featured image.
		track.addEventListener('click', function (e) {
			var thumb = e.target.closest('[data-ztp-thumb]');
			if (!thumb || !track.contains(thumb)) {
				return;
			}
			// Ignore click only after a real drag.
			if (track._ztpDidDrag) {
				track._ztpDidDrag = false;
				e.preventDefault();
				return;
			}
			e.preventDefault();
			var idx = parseInt(thumb.getAttribute('data-index') || '-1', 10);
			var url = thumb.getAttribute('data-url') || '';
			if (idx < 0 && url) {
				self.setMainImage(url, true);
				return;
			}
			self.selectGalleryIndex(idx, true);
		});

		// Drag / swipe.
		var isDown = false;
		var startX = 0;
		var scrollLeft = 0;
		var moved = false;

		track.addEventListener('pointerdown', function (e) {
			// Don't start drag capture on thumb buttons until moved — keeps click reliable.
			isDown = true;
			moved = false;
			track._ztpDidDrag = false;
			startX = e.clientX;
			scrollLeft = track.scrollLeft;
		});

		track.addEventListener('pointermove', function (e) {
			if (!isDown) {
				return;
			}
			var dx = e.clientX - startX;
			if (Math.abs(dx) > 8) {
				if (!moved) {
					moved = true;
					track._ztpDidDrag = true;
					track.classList.add('is-dragging');
					try {
						track.setPointerCapture(e.pointerId);
					} catch (err) {
						/* ignore */
					}
				}
				track.scrollLeft = scrollLeft - dx;
				self.updateGalleryArrows();
			}
		});

		function endDrag(e) {
			if (!isDown) {
				return;
			}
			isDown = false;
			track.classList.remove('is-dragging');
			if (e && moved && e.pointerId != null) {
				try {
					track.releasePointerCapture(e.pointerId);
				} catch (err) {
					/* ignore */
				}
			}
			self.updateGalleryArrows();
			// Clear drag flag on next tick so click can still fire after tiny moves.
			if (!moved) {
				track._ztpDidDrag = false;
			} else {
				window.setTimeout(function () {
					track._ztpDidDrag = false;
				}, 50);
			}
		}

		track.addEventListener('pointerup', endDrag);
		track.addEventListener('pointercancel', endDrag);
		track.addEventListener('pointerleave', function (e) {
			if (isDown) {
				endDrag(e);
			}
		});

		// Mouse wheel → horizontal.
		track.addEventListener(
			'wheel',
			function (e) {
				if (!self.galleryCanScroll()) {
					return;
				}
				var delta = Math.abs(e.deltaY) > Math.abs(e.deltaX) ? e.deltaY : e.deltaX;
				if (delta === 0) {
					return;
				}
				e.preventDefault();
				track.scrollLeft += delta;
				self.updateGalleryArrows();
			},
			{ passive: false }
		);

		track.addEventListener('scroll', function () {
			self.updateGalleryArrows();
		}, { passive: true });

		if (this.galleryPrev) {
			this.galleryPrev.addEventListener('click', function () {
				track.scrollBy({ left: -160, behavior: 'smooth' });
			});
		}
		if (this.galleryNext) {
			this.galleryNext.addEventListener('click', function () {
				track.scrollBy({ left: 160, behavior: 'smooth' });
			});
		}

		window.addEventListener('resize', function () {
			self.updateGalleryArrows();
		});
	};

	ZtpFeaturedShowcase.prototype.galleryCanScroll = function () {
		var track = this.galleryTrack;
		if (!track) {
			return false;
		}
		return track.scrollWidth > track.clientWidth + 2;
	};

	ZtpFeaturedShowcase.prototype.updateGalleryArrows = function () {
		var track = this.galleryTrack;
		var prev = this.galleryPrev;
		var next = this.galleryNext;
		if (!track || !prev || !next) {
			return;
		}

		var can = this.galleryCanScroll();
		if (!can) {
			prev.hidden = true;
			next.hidden = true;
			if (this.gallery) {
				this.gallery.classList.remove('has-arrows');
			}
			return;
		}

		if (this.gallery) {
			this.gallery.classList.add('has-arrows');
		}
		prev.hidden = track.scrollLeft <= 2;
		next.hidden = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
	};

	ZtpFeaturedShowcase.prototype.resetGalleryForProject = function (project, animateMain) {
		var gallery = (project && project.gallery) ? project.gallery : [];
		var mainUrl = (project.image && project.image.url) ? project.image.url : '';

		if (!mainUrl && gallery.length) {
			mainUrl = gallery[0].url;
		}

		this.renderGalleryThumbs(gallery);

		if (gallery.length) {
			this.activeGalleryIndex = 0;
			this.setActiveThumb(0);
		} else {
			this.activeGalleryIndex = -1;
		}

		this.setMainImage(mainUrl, !!animateMain);
		this.preloadNeighbors(gallery, gallery.length ? 0 : -1, mainUrl);
		this.updateGalleryArrows();
	};

	ZtpFeaturedShowcase.prototype.renderGalleryThumbs = function (gallery) {
		var wrap = this.gallery;
		var track = this.galleryTrack;
		if (!wrap || !track) {
			return;
		}

		if (!gallery.length) {
			wrap.classList.add('is-empty');
			wrap.hidden = true;
			track.innerHTML = '';
			this.updateGalleryArrows();
			return;
		}

		wrap.classList.remove('is-empty');
		wrap.hidden = false;

		var html = gallery.map(function (g, i) {
			var loading = i < 2 ? 'eager' : 'lazy';
			var featuredClass = g.is_featured ? ' ztp-fs__thumb--featured' : '';
			var activeClass = 0 === i ? ' is-active' : '';
			return (
				'<button type="button" class="ztp-fs__thumb' + featuredClass + activeClass + '" data-ztp-thumb data-index="' + i +
				'" data-url="' + escapeAttr(g.url) + '" aria-label="Gallery image ' + (i + 1) + '"' +
				(0 === i ? ' aria-current="true"' : '') + '>' +
				'<img src="' + escapeAttr(g.thumb || g.url) + '" alt="" loading="' + loading + '" decoding="async" />' +
				'</button>'
			);
		}).join('');

		track.innerHTML = html;
		track.scrollLeft = 0;
	};

	ZtpFeaturedShowcase.prototype.selectGalleryIndex = function (index, animate) {
		var project = this.getFeatured();
		var gallery = (project && project.gallery) ? project.gallery : [];
		if (index < 0 || index >= gallery.length) {
			return;
		}

		var url = gallery[index].url;
		if (!url) {
			return;
		}

		this.activeGalleryIndex = index;
		this.setActiveThumb(index);
		this.setMainImage(url, animate !== false);
		this.preloadNeighbors(gallery, index, null);
	};

	ZtpFeaturedShowcase.prototype.setActiveThumb = function (index) {
		var track = this.galleryTrack;
		if (!track) {
			return;
		}
		var thumbs = track.querySelectorAll('[data-ztp-thumb]');
		Array.prototype.forEach.call(thumbs, function (t, i) {
			var on = i === index;
			t.classList.toggle('is-active', on);
			if (on) {
				t.setAttribute('aria-current', 'true');
				if (typeof t.scrollIntoView === 'function') {
					t.scrollIntoView({ inline: 'nearest', block: 'nearest', behavior: 'smooth' });
				}
			} else {
				t.removeAttribute('aria-current');
			}
		});
	};

	ZtpFeaturedShowcase.prototype.clearActiveThumbs = function () {
		var track = this.galleryTrack;
		if (!track) {
			return;
		}
		Array.prototype.forEach.call(track.querySelectorAll('[data-ztp-thumb]'), function (t) {
			t.classList.remove('is-active');
			t.removeAttribute('aria-current');
		});
	};

	ZtpFeaturedShowcase.prototype.setMainImage = function (url, animate) {
		var img = this.root.querySelector('[data-ztp-featured-img]');
		var self = this;
		if (!img || !url) {
			return;
		}
		this.featuredImg = img;

		var current = (img.getAttribute('src') || '').split('?')[0];
		var next = String(url).split('?')[0];

		if (!animate) {
			img.classList.remove('is-fading', 'is-zoom');
			img.src = url;
			return;
		}

		if (current === next) {
			img.classList.remove('is-fading', 'is-zoom');
			return;
		}

		this.imageBusy = true;
		img.classList.add('is-fading', 'is-zoom');

		window.setTimeout(function () {
			img.src = url;
			requestAnimationFrame(function () {
				img.classList.remove('is-fading', 'is-zoom');
				self.imageBusy = false;
			});
		}, 160);
	};

	ZtpFeaturedShowcase.prototype.preloadNeighbors = function (gallery, index, mainUrl) {
		var self = this;
		function cache(url) {
			if (!url || self.preloadCache[url]) {
				return;
			}
			self.preloadCache[url] = true;
			preloadUrl(url);
		}

		if (mainUrl) {
			cache(mainUrl);
		}

		if (!gallery.length) {
			return;
		}

		if (index < 0) {
			cache(gallery[0] && gallery[0].url);
			cache(gallery[1] && gallery[1].url);
			return;
		}

		cache(gallery[index] && gallery[index].url);
		cache(gallery[index - 1] && gallery[index - 1].url);
		cache(gallery[index + 1] && gallery[index + 1].url);
	};

	ZtpFeaturedShowcase.prototype.goNext = function () {
		this.rotate(1);
	};

	ZtpFeaturedShowcase.prototype.goPrev = function () {
		this.rotate(-1);
	};

	ZtpFeaturedShowcase.prototype.goToCard = function (k) {
		if (this.busy) {
			return;
		}
		var target = this.mod(this.head + 1 + k);
		if (target === this.head) {
			return;
		}
		this.head = target;
		this.applyState(true);
	};

	ZtpFeaturedShowcase.prototype.rotate = function (dir) {
		if (this.busy || this.projects.length < 2) {
			return;
		}
		this.head = this.mod(this.head + dir);
		this.applyState(true);
	};

	ZtpFeaturedShowcase.prototype.captureCardRects = function () {
		if (!this.cardsWrap) {
			return {};
		}
		var map = {};
		var cards = this.cardsWrap.querySelectorAll('[data-ztp-card]');
		Array.prototype.forEach.call(cards, function (card) {
			var id = card.getAttribute('data-id');
			if (id) {
				map[id] = card.getBoundingClientRect();
			}
		});
		return map;
	};

	ZtpFeaturedShowcase.prototype.applyState = function (animate) {
		var self = this;
		if (this.busy) {
			return;
		}
		this.busy = true;
		this.root.classList.add('is-animating');

		var firstRects = animate ? this.captureCardRects() : {};
		var project = this.getFeatured();

		this.updateCopy(project, animate);
		this.resetGalleryForProject(project, animate);
		this.updateCards(animate, firstRects);

		window.setTimeout(function () {
			self.busy = false;
			self.root.classList.remove('is-animating');
		}, this.duration + 40);
	};

	ZtpFeaturedShowcase.prototype.updateCopy = function (project, animate) {
		var self = this;
		var copy = this.copyEl;

		function write() {
			if (self.categoryEl) {
				self.categoryEl.textContent = project.category || '';
			}
			if (self.titleEl) {
				var link = self.titleEl.querySelector('a');
				if (link) {
					link.textContent = project.title || '';
					link.setAttribute('href', project.url || '#');
				} else {
					self.titleEl.textContent = project.title || '';
				}
			}
			if (self.excerptEl) {
				self.excerptEl.textContent = project.excerpt || '';
			}
		}

		if (!animate || !copy) {
			write();
			return;
		}

		copy.classList.add('is-leave');
		window.setTimeout(function () {
			write();
			copy.classList.remove('is-leave');
			copy.classList.add('is-enter');
			void copy.offsetWidth;
			requestAnimationFrame(function () {
				copy.classList.remove('is-enter');
			});
		}, Math.min(180, this.duration * 0.35));
	};

	ZtpFeaturedShowcase.prototype.updateCards = function (animate, firstRects) {
		var wrap = this.cardsWrap;
		if (!wrap) {
			return;
		}

		var indexes = this.getCardIndexes();
		var self = this;
		var html = indexes.map(function (idx) {
			var p = self.projects[idx];
			var img = (p.image && p.image.url) ? p.image.url : '';
			var url = p.url || '#';
			var catUrl = p.category_url || '';
			var catHtml = '';
			if (p.category) {
				if (catUrl) {
					catHtml = '<a class="ztp-fs__card-category" href="' + escapeAttr(catUrl) + '">' + escapeHtml(p.category) + '</a>';
				} else {
					catHtml = '<span class="ztp-fs__card-category">' + escapeHtml(p.category) + '</span>';
				}
			}
			return (
				'<article class="ztp-fs__card" data-ztp-card data-id="' + p.id + '">' +
					'<a class="ztp-fs__card-media" href="' + escapeAttr(url) + '">' +
						'<img src="' + escapeAttr(img) + '" alt="' + escapeHtml(p.title || '') + '" loading="lazy" decoding="async" data-ztp-card-img />' +
					'</a>' +
					'<div class="ztp-fs__card-body">' +
						'<a class="ztp-fs__card-title" href="' + escapeAttr(url) + '">' + escapeHtml(p.title || '') + '</a>' +
						catHtml +
					'</div>' +
				'</article>'
			);
		}).join('');

		wrap.innerHTML = html;

		if (!animate) {
			return;
		}

		var cards = wrap.querySelectorAll('[data-ztp-card]');
		Array.prototype.forEach.call(cards, function (card) {
			var id = card.getAttribute('data-id');
			var first = firstRects[id];
			var last = card.getBoundingClientRect();
			if (!first) {
				card.style.opacity = '0';
				card.style.transform = 'translate3d(0, 16px, 0)';
				requestAnimationFrame(function () {
					card.style.transition = 'transform ' + self.duration + 'ms cubic-bezier(0.22, 1, 0.36, 1), opacity ' + self.duration + 'ms cubic-bezier(0.22, 1, 0.36, 1)';
					card.style.opacity = '1';
					card.style.transform = 'translate3d(0,0,0)';
				});
				return;
			}

			var dx = first.left - last.left;
			var dy = first.top - last.top;
			var sx = first.width / Math.max(last.width, 1);
			var sy = first.height / Math.max(last.height, 1);

			card.style.transition = 'none';
			card.style.transform = 'translate3d(' + dx + 'px,' + dy + 'px,0) scale(' + sx + ',' + sy + ')';
			card.style.transformOrigin = 'top left';

			requestAnimationFrame(function () {
				requestAnimationFrame(function () {
					card.style.transition = 'transform ' + self.duration + 'ms cubic-bezier(0.22, 1, 0.36, 1)';
					card.style.transform = 'translate3d(0,0,0) scale(1,1)';
				});
			});
		});

		window.setTimeout(function () {
			Array.prototype.forEach.call(wrap.querySelectorAll('[data-ztp-card]'), function (card) {
				card.style.transition = '';
				card.style.transform = '';
				card.style.opacity = '';
				card.style.transformOrigin = '';
			});
		}, this.duration + 30);
	};

	function initAll(scope, force) {
		var root = scope || document;
		var nodes = root.querySelectorAll ? root.querySelectorAll('[data-ztp-fs]') : [];
		if ((!nodes || !nodes.length) && root.matches && root.matches('[data-ztp-fs]')) {
			nodes = [root];
		}
		Array.prototype.forEach.call(nodes, function (el) {
			if (force && el._ztpFs) {
				el._ztpFs = null;
			}
			if (el._ztpFs) {
				return;
			}
			el._ztpFs = new ZtpFeaturedShowcase(el);
		});
	}

	function bindElementor() {
		if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
			return;
		}
		window.elementorFrontend.hooks.addAction(
			'frontend/element_ready/ztp-featured-portfolio-showcase.default',
			function ($el) {
				var node = $el && $el[0] ? $el[0] : $el;
				if (node) {
					initAll(node, true);
				}
			}
		);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initAll(document, false);
		});
	} else {
		initAll(document, false);
	}

	window.addEventListener('elementor/frontend/init', function () {
		bindElementor();
		initAll(document, true);
	});

	if (window.elementorFrontend && window.elementorFrontend.hooks) {
		bindElementor();
	}
}());
