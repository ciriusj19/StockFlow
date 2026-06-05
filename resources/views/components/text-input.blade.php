@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-full border-slate-200 bg-white/95 px-4 shadow-sm shadow-slate-900/5 transition focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-50 disabled:text-slate-500']) }}>
