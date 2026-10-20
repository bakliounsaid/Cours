{{-- Logo Éducœur (voir App\View\Components\Brand\Logo) --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }} dir="ltr">
    <x-brand.mark :mono="$mono" @class([$classes['mark']]) />
    <span class="flex flex-col leading-none">
        <span @class([$classes['name'], 'font-[\'Baloo_2\'] font-extrabold tracking-tight', 'text-olive-700' => ! $mono])>Édu<span @class(['text-terra-600' => ! $mono])>cœur</span></span>
        @if ($showArabic)
            <span @class([$classes['ar'], 'font-[\'Baloo_Bhaijaan_2\'] font-bold opacity-75', 'text-olive-700' => ! $mono]) dir="rtl" lang="ar">إديكور@if ($tagline) · نُعلّم بقلب@endif</span>
        @endif
    </span>
</span>
