<div>
    <x-flash />

    <x-card :table="false">
        <x-slot name="header" class="pb-1">
            <span class="m-0 w-25 font-weight-normal">Total Job: {{ $this->jobs }}</span>
        </x-slot>
        <x-slot name="body" class="pt-1"></x-slot>
        <x-slot name="footer">
            <x-button size="sm" icon="fas fa-sync" variant="link" wire:click="cleanJobs" title="Clean Jobs" />
        </x-slot>
    </x-card>
</div>
