import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('toastNotifications', (initialToasts = [], options = {}) => ({
	toasts: [],
	nextId: 0,
	defaultDuration: Number(options.duration) > 0 ? Number(options.duration) : 3500,

	init() {
		initialToasts.forEach((toast) => this.pushToast(toast));

		window.addEventListener('toast', (event) => {
			const detail = event?.detail ?? {};

			if (!detail.message) {
				return;
			}

			this.pushToast({
				type: detail.type ?? 'info',
				message: detail.message,
				duration: detail.duration,
			});
		});
	},

	pushToast(payload = {}) {
		const message = typeof payload.message === 'string' ? payload.message.trim() : '';

		if (!message) {
			return;
		}

		const toast = {
			id: ++this.nextId,
			type: this.normalizeType(payload.type),
			message,
			timeoutId: null,
			isVisible: true,
			isClosing: false,
		};

		this.toasts = [...this.toasts, toast];

		const duration = Number(payload.duration) > 0 ? Number(payload.duration) : this.defaultDuration;
		toast.timeoutId = setTimeout(() => {
			this.remove(toast.id);
		}, duration);
	},

	remove(id) {
		const toast = this.toasts.find((entry) => entry.id === id);

		if (!toast || toast.isClosing) {
			return;
		}

		if (toast.timeoutId) {
			clearTimeout(toast.timeoutId);
		}

		toast.isClosing = true;
		toast.isVisible = false;

		setTimeout(() => {
			this.toasts = this.toasts.filter((entry) => entry.id !== id);
		}, 220);
	},

	normalizeType(type) {
		const accepted = ['success', 'error', 'info', 'warning'];
		const normalized = typeof type === 'string' ? type.toLowerCase() : 'info';

		return accepted.includes(normalized) ? normalized : 'info';
	},

	toastClass(type) {
		const palette = {
			success: 'bg-green-50/95 border-green-200 text-green-800',
			error: 'bg-red-50/95 border-red-200 text-red-800',
			info: 'bg-blue-50/95 border-blue-200 text-blue-800',
			warning: 'bg-yellow-50/95 border-yellow-200 text-yellow-800',
		};

		return palette[type] ?? palette.info;
	},

	indicatorClass(type) {
		const indicators = {
			success: 'bg-green-500',
			error: 'bg-red-500',
			info: 'bg-blue-500',
			warning: 'bg-yellow-500',
		};

		return indicators[type] ?? indicators.info;
	},
}));

// Shared add-to-cart behaviour for product cards.
// Usage: x-data="addToCart({ url, productId, addedMessage, errorMessage })"
Alpine.data('addToCart', (config) => ({
	loading: false,
	added: false,

	toast(type, message) {
		window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } }));
	},

	async add() {
		if (this.loading) return;
		this.loading = true;

		try {
			const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
			const response = await fetch(config.url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, Accept: 'application/json' },
				body: JSON.stringify({ product_id: config.productId, quantity: 1 }),
			});
			const data = await response.json().catch(() => null);

			if (data?.ok) {
				if (Alpine.store('cart')) Alpine.store('cart').count = data.count;
				this.toast('success', data.message || config.addedMessage);
				this.added = true;
				setTimeout(() => (this.added = false), 1500);
			} else {
				this.toast('error', data?.message || config.errorMessage);
			}
		} catch {
			this.toast('error', config.errorMessage);
		} finally {
			this.loading = false;
		}
	},
}));

// Admin: sizes & colours editor (resources/views/admin/products/partials/variants).
Alpine.data('productVariants', (initial, lists) => ({
	lists,
	sizeSystem: initial.sizeSystem || '',
	sizes: initial.sizeSystem === 'custom' ? [] : (initial.sizes || []).map(String),
	customSizes: initial.sizeSystem === 'custom' ? (initial.sizes || []).join(', ') : '',
	colors: (initial.colors || []).map((c) => ({
		key: c.key,
		hex: c.hex || '#111111',
		names: { nl: c.names?.nl || '', en: c.names?.en || '', ar: c.names?.ar || '' },
	})),
	// Stock/price per combination, keyed "size|colourKey"; kept while toggling.
	values: {},

	init() {
		(initial.variants || []).forEach((v) => {
			this.values[this.comboId(v.size || null, v.color_key || null)] = {
				stock: v.stock ?? 0,
				price: v.price ?? '',
			};
		});
	},

	get activeSizes() {
		if (this.sizeSystem === 'custom') {
			return [...new Set(this.customSizes.split(',').map((s) => s.trim()).filter(Boolean))];
		}
		if (this.sizeSystem === 'lengths' || this.sizeSystem === 'letters') {
			// Keep the list's natural order, whatever order they were ticked in.
			return this.lists[this.sizeSystem].filter((s) => this.sizes.includes(s));
		}
		return [];
	},

	get combos() {
		const sizes = this.activeSizes;
		if (sizes.length === 0 && this.colors.length === 0) return [];

		const combos = [];
		for (const size of sizes.length ? sizes : [null]) {
			for (const color of this.colors.length ? this.colors : [null]) {
				const id = this.comboId(size, color?.key ?? null);
				if (!this.values[id]) this.values[id] = { stock: 0, price: '' };
				combos.push({ id, size, color });
			}
		}
		return combos;
	},

	comboId(size, colorKey) {
		return `${size ?? ''}|${colorKey ?? ''}`;
	},

	colorLabel(color) {
		if (!color) return '';
		const lang = document.documentElement.lang;
		return color.names[lang] || color.names.en || color.names.nl || color.names.ar || '';
	},

	addColor() {
		// Stable internal key; customers only ever see the names.
		const key = 'c' + Math.random().toString(36).slice(2, 8);
		this.colors.push({ key, hex: '#111111', names: { nl: '', en: '', ar: '' } });
	},

	removeColor(index) {
		this.colors.splice(index, 1);
	},
}));

// Storefront: size/colour picker on the product page.
// data: { sizes[], colors[{key,hex,name}], variants[{id,size,color,stock,priceText}],
//         defaultPriceText, texts{choose, outOfStock, inStock, add} }
Alpine.data('productOptions', (data) => ({
	...data,
	size: data.sizes.length === 1 ? data.sizes[0] : null,
	color: data.colors.length === 1 ? data.colors[0].key : null,

	get needsSize() {
		return this.sizes.length > 0;
	},
	get needsColor() {
		return this.colors.length > 0;
	},

	get selected() {
		if ((this.needsSize && !this.size) || (this.needsColor && !this.color)) return null;
		return this.variants.find((v) => (v.size ?? null) === (this.needsSize ? this.size : null)
			&& (v.color ?? null) === (this.needsColor ? this.color : null)) || null;
	},

	// A size (or colour) is selectable when some variant with it is in stock,
	// given the other choice if one is made.
	sizeAvailable(size) {
		return this.variants.some((v) => v.size === size && v.stock > 0 && (!this.color || v.color === this.color));
	},
	colorAvailable(key) {
		return this.variants.some((v) => v.color === key && v.stock > 0 && (!this.size || v.size === this.size));
	},

	get colorName() {
		return this.colors.find((c) => c.key === this.color)?.name ?? '';
	},

	get priceText() {
		return this.selected ? this.selected.priceText : this.defaultPriceText;
	},

	get maxQty() {
		return this.selected ? Math.max(1, this.selected.stock) : 1;
	},

	get canAdd() {
		return !!this.selected && this.selected.stock > 0;
	},

	get buttonText() {
		if (!this.selected) return this.texts.choose;
		return this.selected.stock > 0 ? this.texts.add : this.texts.outOfStock;
	},

	get stockText() {
		if (!this.selected) return '';
		return this.selected.stock > 0
			? this.texts.inStock.replace(':count', this.selected.stock)
			: this.texts.outOfStock;
	},

	get label() {
		return [this.needsSize ? this.size : null, this.needsColor ? this.colorName : null].filter(Boolean).join(' · ');
	},

	init() {
		// Keep the quantity within the chosen variant's stock.
		this.$watch('selected', () => {
			const input = document.getElementById('quantity');
			if (input && parseInt(input.value, 10) > this.maxQty) input.value = this.maxQty;
		});
	},
}));

Alpine.start();
