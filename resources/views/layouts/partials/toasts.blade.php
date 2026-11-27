@php
	$initialToasts = [];

	foreach (['success', 'error', 'info', 'warning'] as $type) {
		if (session()->has($type)) {
			$initialToasts[] = [
				'type' => $type,
				'message' => (string) session($type),
			];
		}
	}

	if ($errors->any()) {
		$initialToasts[] = [
			'type' => 'error',
			'message' => (string) $errors->first(),
		];
	}
@endphp

<div
	x-data="toastNotifications(@js($initialToasts))"
	class="fixed top-4 sm:top-6 end-4 sm:end-6 z-[100] w-[min(92vw,24rem)] pointer-events-none"
>
	<template x-for="toast in toasts" :key="toast.id">
		<div
			x-show="toast.isVisible"
			x-transition:enter="transform transition ease-out duration-300"
			x-transition:enter-start="opacity-0 translate-y-2"
			x-transition:enter-end="opacity-100 translate-y-0"
			x-transition:leave="transform transition ease-in duration-200"
			x-transition:leave-start="opacity-100 translate-y-0"
			x-transition:leave-end="opacity-0 -translate-y-1"
			class="pointer-events-auto mb-3 rounded-xl border shadow-lg backdrop-blur-sm"
			:class="toastClass(toast.type)"
			role="alert"
		>
			<div class="flex items-start gap-3 px-4 py-3">
				<span class="mt-1 h-2.5 w-2.5 rounded-full shrink-0" :class="indicatorClass(toast.type)"></span>

				<p class="text-sm font-medium leading-5 flex-1 text-start" x-text="toast.message"></p>

				<button
					type="button"
					class="shrink-0 rounded-md p-1 text-current/70 hover:text-current hover:bg-black/5 transition"
					@click="remove(toast.id)"
					aria-label="Close notification"
				>
					<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
						<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
					</svg>
				</button>
			</div>
		</div>
	</template>
</div>
