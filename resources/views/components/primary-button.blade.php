<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex  items-center px-4 py-2 bg-white border border-orange-600 rounded-md font-semibold text-xs text-orange-700 uppercase tracking-widest hover:bg-orange-500 hover:text-white focus:bg-orange-500 active:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
