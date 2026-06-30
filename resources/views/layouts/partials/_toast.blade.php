{{-- Toast Notification Stack --}}
<div class="fixed bottom-20 md:bottom-6 right-4 z-[60] flex flex-col-reverse gap-2 pointer-events-none" aria-live="polite">
    <template x-for="toast in $store.toast.messages" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-full"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 translate-x-full"
            class="pointer-events-auto flex items-start gap-3 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-xl border border-gray-100 p-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
                :class="{ 'bg-green-100': toast.type==='success', 'bg-red-100': toast.type==='error', 'bg-yellow-100': toast.type==='warning', 'bg-blue-100': toast.type==='info' }">
                <x-heroicon-s-check-circle x-show="toast.type==='success'" class="w-4 h-4 text-green-600" />
                <x-heroicon-s-x-circle x-show="toast.type==='error'" class="w-4 h-4 text-red-600" />
                <x-heroicon-s-information-circle x-show="toast.type==='info'" class="w-4 h-4 text-blue-600" />
            </div>
            <p class="flex-1 text-sm text-gray-800 font-medium" x-text="toast.message"></p>
            <button @click="$store.toast.dismiss(toast.id)" class="p-1 rounded-md hover:bg-gray-100 text-gray-400">
                <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
            </button>
        </div>
    </template>
</div>
