{{-- « Installer l'application » : visible seulement quand le navigateur propose l'installation (voir resources/js/app.js) --}}
<button type="button"
        x-data="{ installable: !! window.educoeurInstall?.prompt }"
        x-on:educoeur:installable.window="installable = true"
        x-on:educoeur:installed.window="installable = false"
        x-show="installable" x-cloak
        x-on:click="window.educoeurInstallApp()"
        class="menu-item text-terra-700">
    <x-icon name="download" class="size-5" /> {{ __('app.install') }}
</button>
