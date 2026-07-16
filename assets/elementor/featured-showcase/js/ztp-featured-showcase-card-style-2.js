/**
 * Featured Portfolio Showcase — Card Style 2 JS
 * Pagination: numbers, load more, infinite scroll.
 *
 * @package Zouetech_Portfolio
 */
(function () {
	'use strict';

	function getConfig() {
		return window.ztpFsS2 || {
			ajaxUrl: '',
			nonce: ''
		};
	}

	function ZtpStyle2(root) {
		this.root = root;
		this.grid = root.querySelector('[data-ztp-s2-grid]');
		this.pagType = root.getAttribute('data-ztp-pag-type') || 'none';
		this.page = parseInt(root.getAttribute('data-ztp-page') || '1', 10) || 1;
		this.max = parseInt(root.getAttribute('data-ztp-max') || '1', 10) || 1;
		this.busy = false;
		this.settings = {};

		try {
			this.settings = JSON.parse(root.getAttribute('data-ztp-settings') || '{}');
		} catch (e) {
			this.settings = {};
		}

		if (!this.grid || this.pagType === 'none' || this.max <= 1) {
			return;
		}

		this.bind();
	}

	ZtpStyle2.prototype.bind = function () {
		var self = this;

		if (this.pagType === 'numbers') {
			this.root.addEventListener('click', function (e) {
				var btn = e.target.closest('[data-ztp-s2-page]');
				if (!btn || !self.root.contains(btn)) {
					return;
				}
				e.preventDefault();
				var page = parseInt(btn.getAttribute('data-ztp-s2-page') || '1', 10);
				if (page && page !== self.page) {
					self.loadPage(page, false);
				}
			});
		}

		if (this.pagType === 'load_more') {
			var moreBtn = this.root.querySelector('[data-ztp-s2-load-more]');
			if (moreBtn) {
				moreBtn.addEventListener('click', function () {
					self.loadPage(self.page + 1, true);
				});
			}
		}

		if (this.pagType === 'infinite') {
			this.setupInfinite();
		}
	};

	ZtpStyle2.prototype.setupInfinite = function () {
		var self = this;
		var sentinel = this.root.querySelector('[data-ztp-s2-sentinel]');
		if (!sentinel || typeof IntersectionObserver === 'undefined') {
			return;
		}

		this.observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting && !self.busy && self.page < self.max) {
						self.loadPage(self.page + 1, true);
					}
				});
			},
			{ rootMargin: '200px 0px' }
		);

		this.observer.observe(sentinel);
	};

	ZtpStyle2.prototype.loadPage = function (page, append) {
		var self = this;
		var cfg = getConfig();

		if (this.busy || page < 1 || page > this.max) {
			return;
		}

		this.busy = true;
		this.root.classList.add('is-loading');

		var loading = this.root.querySelector('[data-ztp-s2-loading]');
		if (loading) {
			loading.hidden = false;
		}

		var body = new FormData();
		body.append('action', 'ztp_fs_s2_load');
		body.append('nonce', cfg.nonce || '');
		body.append('page', String(page));
		body.append('settings', JSON.stringify(this.settings));

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (res) {
				return res.json();
			})
			.then(function (json) {
				if (!json || !json.success || !json.data) {
					throw new Error('Request failed');
				}

				var html = json.data.html || '';
				self.max = parseInt(json.data.max_num_pages || self.max, 10) || self.max;
				self.page = parseInt(json.data.page || page, 10) || page;
				self.root.setAttribute('data-ztp-page', String(self.page));
				self.root.setAttribute('data-ztp-max', String(self.max));

				if (append) {
					self.grid.insertAdjacentHTML('beforeend', html);
				} else {
					self.grid.innerHTML = html;
					self.updateNumberButtons();
					try {
						self.root.scrollIntoView({ behavior: 'smooth', block: 'start' });
					} catch (err) {
						/* ignore */
					}
				}

				self.updateMoreUI();
			})
			.catch(function () {
				/* keep UI usable */
			})
			.finally(function () {
				self.busy = false;
				self.root.classList.remove('is-loading');
				if (loading) {
					loading.hidden = true;
				}
			});
	};

	ZtpStyle2.prototype.updateNumberButtons = function () {
		var self = this;
		var buttons = this.root.querySelectorAll('[data-ztp-s2-page]');
		Array.prototype.forEach.call(buttons, function (btn) {
			var p = parseInt(btn.getAttribute('data-ztp-s2-page') || '0', 10);
			var on = p === self.page;
			btn.classList.toggle('is-active', on);
			if (on) {
				btn.setAttribute('aria-current', 'page');
			} else {
				btn.removeAttribute('aria-current');
			}
		});
	};

	ZtpStyle2.prototype.updateMoreUI = function () {
		if (this.page >= this.max) {
			var wrap = this.root.querySelector('[data-ztp-s2-pagination]');
			if (wrap) {
				wrap.hidden = true;
			}
			if (this.observer) {
				this.observer.disconnect();
			}
		}
	};

	function initAll(scope, force) {
		var root = scope || document;
		var nodes = root.querySelectorAll ? root.querySelectorAll('[data-ztp-s2]') : [];
		Array.prototype.forEach.call(nodes, function (el) {
			if (force && el._ztpS2) {
				el._ztpS2 = null;
			}
			if (el._ztpS2) {
				return;
			}
			el._ztpS2 = new ZtpStyle2(el);
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
