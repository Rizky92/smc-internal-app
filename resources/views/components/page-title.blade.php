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
<div class="print">
    <div class="container">
        <div class="row">
            <div class="col-1">
                <img src="{{ asset('img/logo.png') }}" margin="0" width="80px" />
            </div>
            <div class="col-11">
                <h2 style="font-size: 12px; margin: 0">RS SAMARINDA MEDIKA CITRA</h2>
                <p style="font-size: 9px; margin: 1px">
                    Jl. Kadrie Oening no.85, RT.35, Kel. Air Putih, Kec. Samarinda Ulu, Samarinda, Kalimantan Timur
                    <br />
                    TEL:0541-7273000
                    <br />
                    E-mail:info@rssmc.co.id
                </p>
            </div>
        </div>
    </div>
    <hr style="border-top: 1px solid #000; margin-top: 10px; margin-bottom: 0px" />
    <hr style="border-top: 1px solid #000; margin-top: 4px; margin-bottom: 10px" />
</div>
<div class="p-3" id="title">
    <h2 class="m-0">{{ $title }}</h2>
</div>
