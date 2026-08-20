<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#1F3864] border border-transparent rounded-lg text-sm font-medium text-white hover:bg-[#16294a] focus:bg-[#16294a] active:bg-[#16294a] focus:outline-none focus:ring-2 focus:ring-[#1F3864]/30 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
