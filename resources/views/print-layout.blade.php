<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
    }
</style>

<div id="printHeader">
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
    <hr style="border-top: 2px solid #333; margin-top: 10px; margin-bottom: 1px;">
    <hr style="border-top: 2px solid #333; margin-top: 1px; margin-bottom: 10px; padding-top: 2px">
</div>

<h2 style="display: flex; justify-content: center">POSTING JURNAL</h2>

@php
    $totalDebet = 0;
    $totalKredit = 0;
@endphp

<table border width="100%">
    <thead style="background: rgb(166, 179, 152)" >
        <th>No. Jurnal</th>
        <th>No. Bukti</th>
        <th>Tgl. Jurnal</th>
        <th>Jenis</th>
        <th>Keterangan</th>
        <th>Rekening</th>
        <th>Debet</th>
        <th>Kredit</th>
    </thead>
    <tbody>
        @foreach ($postingJurnal->detail as $detail)
            <tr>
                <td>{{ $postingJurnal->no_jurnal }}</td>
                <td>{{ $postingJurnal->jurnal->no_bukti }}</td>
                <td>{{ $postingJurnal->jurnal->tgl_jurnal }}</td>
                <td>{{ $postingJurnal->jurnal->jenis === 'U' ? 'Umum' : 'Penyesuaian' }}</td>
                <td>{{ $postingJurnal->jurnal->keterangan }}</td>
                <td>{{ $detail->kd_rek }} - {{ optional($detail->rekening)->nm_rek }}</td>
                <td>{{ $detail->debet }}</td>
                <td>{{ $detail->kredit }}</td>
                @php
                    $totalDebet += $detail->debet;
                    $totalKredit += $detail->kredit;
                @endphp
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align: center;"><b>Total:</b></td>
            <td>{{ $totalDebet }}</td>
            <td>{{ $totalKredit }}</td>
        </tr>
    </tfoot>
</table>

<div style="display: flex; justify-content: space-between">
    <div style="text-align: center">
        <p> <b>Samarinda,{{ now()->formatLocalized('%d %B %Y') }}</b></p>
        <p><b>Menyetujui</b></p>
        <br>
        <br>
        <br>
        <p><b>dr. Daisy Wijaya</b></p>
        <p><b>Manager Keuangan</b></p>
    </div>
    <div style="text-align: center"> 
        <p> <b>Samarinda,{{ now()->formatLocalized('%d %B %Y') }}</b></p>
        <p><b>Mengetahui</b></p>
        <br>
        <br>
        <br>
        <p><b>dr. Teguh Nurwanto, MARS</b></p>
        <p><b>Direktur</b></p>
    </div>
</div>

<script>

    window.onload = function() {
        window.print();
    };
</script>