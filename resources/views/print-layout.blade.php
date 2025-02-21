<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
    }

    #printHeader {
        text-align: center;
        margin-bottom: 20px;
    }

    #printHeader img {
        display: inline-block;
        vertical-align: middle;
        width: 80px;
    }

    #printHeader h2 {
        font-size: 20px;
        margin: 0;
        display: inline-block;
        vertical-align: middle;
    }

    #printHeader p {
        font-size: 14px;
        margin: 1px;
    }

    hr {
        border-top: 2px solid #333;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    h2 {
        display: flex;
        justify-content: center;
        font-weight: 400;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    thead {
        background: #f0f0dc;
        border-top: 1px solid #333;
        border-bottom: 1px solid #333;
    }

    th,
    td {
        padding: 8px;
        text-align: left;
    }

    ul.list-group {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .list-group-item {
        border: 1px solid #333;
        margin-bottom: 10px;
        padding: 10px;
    }

    .list-group-item strong {
        display: inline-block;
        width: 150px; /* Adjust as needed */
        font-weight: bold;
    }

    .time {
        display: flex;
        justify-content: end;
    }

    .signature {
        display: flex;
        justify-content: space-between;
    }

    .no-border-table,
    .no-border-table th,
    .no-border-table td {
        border: none !important;
    }

    @media print {
        @page {
            size: portrait;
        }
    }
</style>

<div id="printHeader">
    <div style="text-align: center">
        <img src="{{ asset('img/logo.png') }}" margin="0" style="display: inline-block; vertical-align: middle" width="80px" />
        <div style="display: inline-block; vertical-align: middle">
            <h2 style="font-family: 'Arial', serif; font-size: 20px; margin: 0">RS SAMARINDA MEDIKA CITRA</h2>
            <p style="font-size: 14px; margin: 1px">
                Jl. Kadrie Oening no.85, RT.35, Kel. Air Putih, Kec. Samarinda Ulu, Samarinda, Kalimantan Timur
                <br />
                TEL:0541-7273000
                <br />
                E-mail:info@rssmc.co.id
            </p>
        </div>
    </div>
    <hr style="border-top: 2px solid #333; margin-top: 10px; margin-bottom: 1px" />
    <hr style="border-top: 2px solid #333; margin-top: 1px; margin-bottom: 10px; padding-top: 2px" />
</div>

<h2>POSTING JURNAL</h2>

@if (! empty($jurnalSementara) && is_array($jurnalSementara))
    <table class="table">
        <thead>
            <tr>
                <td>No. Jurnal</td>
                <td>No. Bukti</td>
                <td>Tgl. Jurnal</td>
                <td>Jam Jurnal</td>
                <td>Jenis</td>
                <td>Keterangan</td>
                <td>Kode Akun</td>
                <td>Nama Akun</td>
                <td>Debet</td>
                <td>Kredit</td>
            </tr>
        </thead>
        <tbody class="no-border-table">
            @php
                $totalDebet = 0;
                $totalKredit = 0;
            @endphp

            @foreach ($jurnalSementara as $jurnal)
                @foreach ($jurnal['details'] as $detail)
                    @php
                        $totalDebet += $detail['debet'];
                        $totalKredit += $detail['kredit'];
                    @endphp

                    <tr>
                        <td>
                            {{ $loop->first ? $jurnal['jurnal']['no_jurnal'] : '' }}
                        </td>
                        <td>
                            {{ $loop->first ? $jurnal['jurnal']['no_bukti'] : '' }}
                        </td>
                        <td>
                            {{ $loop->first ? $jurnal['jurnal']['tgl_jurnal'] : '' }}
                        </td>
                        <td>
                            {{ $loop->first ? $jurnal['jurnal']['jam_jurnal'] : '' }}
                        </td>
                        <td>
                            {{ $loop->first ? ($jurnal['jurnal']['jenis'] === 'U' ? 'Umum' : 'Penyesuaian') : '' }}
                        </td>
                        <td>
                            {{ $loop->first ? $jurnal['jurnal']['keterangan'] : '' }}
                        </td>
                        <td>{{ $detail['kd_rek'] }}</td>
                        <td>{{ $rekeningData[$detail['kd_rek']] ?? '' }}</td>
                        <td>
                            {{ $detail['debet'] != 0 ? 'Rp ' . number_format($detail['debet'], 0, ',', '.') : '' }}
                        </td>
                        <td>
                            {{ $detail['kredit'] != 0 ? 'Rp ' . number_format($detail['kredit'], 0, ',', '.') : '' }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot class="no-border-table">
            <tr>
                <td colspan="8">Jumlah Total:</td>
                <td>
                    {{ $totalDebet != 0 ? 'Rp ' . number_format($totalDebet, 0, ',', '.') : '' }}
                </td>
                <td>
                    {{ $totalKredit != 0 ? 'Rp ' . number_format($totalKredit, 0, ',', '.') : '' }}
                </td>
            </tr>
        </tfoot>
    </table>
@else
    <p>Data jurnal sementara kosong.</p>
@endif

<div class="time">
    <p><b>Samarinda,{{ now()->formatLocalized('%d %B %Y') }}</b></p>
</div>
<div class="signature">
    <div style="text-align: center">
        <p><b>Menyetujui</b></p>
        <br />
        <br />
        <br />
        <p><b>dr. Daisy Wijaya</b></p>
        <p><b>Manager Keuangan</b></p>
    </div>
    <div style="text-align: center">
        <p><b>Mengetahui</b></p>
        <br />
        <br />
        <br />
        <p><b>dr. Teguh Nurwanto, MARS</b></p>
        <p><b>Direktur</b></p>
    </div>
</div>

<script>
    window.onload = function () {
        window.print();
    };
</script>
