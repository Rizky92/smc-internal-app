<div>
    <x-flash />

    @canany(\App\Livewire\Pages\Aplikasi\Pengaturan::getPengaturanRKATPermissions())
        @include('livewire.pages.aplikasi._inc.pengaturan-rkat')
    @endcanany

    @canany(\App\Livewire\Pages\Aplikasi\Pengaturan::getSetNPWPPenjualPermissions())
        @include('livewire.pages.aplikasi._inc.set-npwp-penjual')
    @endcanany
</div>
