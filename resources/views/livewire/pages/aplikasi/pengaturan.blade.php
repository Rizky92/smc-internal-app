<div>
    <x-flash />

    @canany(\App\Livewire\Pages\Aplikasi\Pengaturan::getPengaturanRKATPermissions())
        @include('livewire.pages.aplikasi._inc.pengaturan-rkat')
    @endcanany
</div>
