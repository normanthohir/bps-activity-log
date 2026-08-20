<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-[#1F3864]/10 border border-[#1F3864]/30 rounded-lg text-sm font-medium text-[#1F3864] hover:bg-[#1F3864]/20 focus:outline-none focus:ring-2 focus:ring-[#1F3864]/30 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
