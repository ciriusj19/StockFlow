<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full border border-transparent bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_12px_30px_rgba(37,99,235,0.22)] transition ease-in-out duration-150 hover:-translate-y-0.5 hover:bg-blue-800 focus:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-900']) }}>
    {{ $slot }}
</button>
