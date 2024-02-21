<style>
    .print {
        display: none;
    }

    @media print {
        .print {
            display: block;
            margin: 0;
            padding: 0 !important;
        }

        #title {
            display: none !important;
        }
    }
</style>
<div class="print" >
    <div style="text-align: center;">
        <img src="{{ asset('img/logo.png') }}" style="display: inline-block; vertical-align: middle;" width="80px">
        <div style="display: inline-block; vertical-align: middle;">
            <h2 style="font-size: 20px; margin: 0;">RS SAMARINDA MEDIKA CITRA</h2>
            <p style="font-size: 14px; margin: 1px;">Jl. Kadrie Oening no.85, RT.35, Kel. Air Putih, Kec. Samarinda Ulu, Samarinda, Kalimantan Timur
                <br>TEL:0541-7273000
                <br>E-mail: info@rssmc.co.id
            </p>
        </div>
    </div>
    <hr style="border-top: 1px solid #333; margin-top: 10px; margin-bottom: 1px;">
    <hr style="border-top: 1px solid #333; margin-top: 2px; margin-bottom: 10px;">
</div>
<div class="p-3" id="title">
    <h2 class="m-0">{{ $title }}</h2>
</div>
