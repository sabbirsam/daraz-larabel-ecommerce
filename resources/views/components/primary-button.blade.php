<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-daraz hover:bg-daraz-hover active:bg-daraz-dark border border-transparent rounded font-semibold text-xs text-white uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-daraz focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
