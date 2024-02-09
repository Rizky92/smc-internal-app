<style>
    #title.print-hidden {
        display: none !important;
    }

    @media print {
        #title {
            display: none !important;
        }
    }
</style>
<div class="p-3" id="title" class="print-hidden">
    <h2 class="m-0">{{ $title }}</h2>
</div>
