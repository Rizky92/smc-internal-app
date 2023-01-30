@once
    @push('css')
        <style>
            .warning-banner {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                height: 8rem !important;
                width: 100% !important;
                font-size: 12pt !important;
                font-weight: 700 !important;
                color: white !important;
                background: repeating-linear-gradient(45deg,
                        #101010,
                        #101010 10px,
                        #feea69 10px,
                        #feea69 20px) !important;
            }
        </style>
    @endpush
@endonce
<div class="warning-banner">
    <span class="text-center">{{ $slot }}</span>
</div>
