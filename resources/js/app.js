import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('toastNotifications', (initialToasts = [], options = {}) => ({
	toasts: [],
	nextId: 0,
	isRtl: Boolean(options.isRtl),
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

Alpine.start();
