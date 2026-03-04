@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-slate-600 bg-slate-800 text-slate-100 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm']) !!}>
