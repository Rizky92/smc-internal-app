<div>
    @push('styles')
        <style>
            body {
                font-family: Tahoma;
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
                margin: 0;
                display: inline-block;
                vertical-align: middle;
            }
        
            #printHeader p {
                margin: 1px;
            }
        
            h2 {
                display: flex;
                justify-content: center;
            }
        
            table {
                width: 100%;
                padding: 0;
                border-collapse: collapse;
                margin-bottom: 20px;
                font-size: 0.70em;
            }

            td {
                vertical-align: top;
                padding: 0;
                margin: 0;
            }
            
            thead {
                background: #F0F0DC;
                border-top: 1px solid #333;
                border-bottom: 1px solid #333;
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

            .conclusion {
                display: grid;
                page-break-inside: avoid;
                font-size: 0.70em;
            }
        
            .time {
                display: flex;
                justify-content: end;
            }
        
            .signature {
                display: flex; 
                justify-content: space-between;
            }
        
            .no-border-table, .no-border-table th, .no-border-table td {
                border: none !important;
            }

            @media print {

                @page {
                    size: landscape;
                }
            }
        </style>
    @endpush
    <div id="printHeader">
        <div class="container">
            <div class="row">
                <div class="col-1">
                    <img src="{{ asset('img/logo.png') }}" margin="0" width="80px">
                </div>
                <div class="col-11">
                    <h2 style="font-size: 12pt; margin: 0;">{{ $this->SIMRSSettings->nama_instansi }}</h2>
                    <p style="font-size: 9pt; margin: 1px;">{{ $this->SIMRSSettings->alamat_instansi }}
                        <br>{{ $this->SIMRSSettings->kontak }}
                        <br>E-mail: {{ $this->SIMRSSettings->email }}
                    </p>
                </div>
            </div>
        </div>
        <hr style="border-top: 1px solid #000; margin-top: 10px; margin-bottom: 0px;">
        <hr style="border-top: 1px solid #000; margin-top: 4px; margin-bottom: 10px;">
    </div>

    <div class="row">
        <div class="col-1">
            
        </div>
        <div class="col-11">
            <h2 style="font-size: 12pt">POSTING JURNAL</h2>
        </div>
    </div>

    <table style="min-width: 100%;">
        <thead>
            <tr>
                <td style="width: 10%;">No. Jurnal</td>
                <td style="width: 10%;">No. Bukti</td>
                <td style="width: 6%;">Tgl. Jurnal</td>
                <td style="width: 4%;">Jenis</td>
                <td style="width: 22%;">Keterangan</td>
                <td style="width: 4%;">Kode</td>
                <td style="width: 22%;">Rekening</td>
                <td style="text-align: right; width: 11%;">Debet</td>
                <td style="text-align: right; width: 11%;">Kredit</td>
            </tr>
        </thead>
        <tbody class="no-border-table">
            @php
                $totalDebet = 0;
                $totalKredit = 0;
            @endphp

            @foreach ($dataJurnal as $jurnal)
                @php
                    $detailPertama = $jurnal->detail->first();
                    $totalDebet += $jurnal->detail->sum('debet');
                    $totalKredit += $jurnal->detail->sum('kredit');
                @endphp
                <tr>
                    <td>{{ $jurnal->no_jurnal }}</td>
                    <td>{{ $jurnal->no_bukti }}</td>
                    <td>{{ $jurnal->tgl_jurnal }} {{ $jurnal->jam_jurnal }}</td>
                    <td>{{ $jurnal->jenis === 'U' ? 'UMUM' : 'PENYESUAIAN' }}</td>
                    <td>{{ $jurnal->keterangan }}</td>
                    <td>{{ $detailPertama->kd_rek }}</td>
                    <td>{{ $detailPertama->rekening->nm_rek }}</td>
                    <td style="text-align: right">{{ rp($detailPertama->debet) }}</td>
                    <td style="text-align: right">{{ rp($detailPertama->kredit) }}</td>
                </tr>
                @foreach ($jurnal->detail as $detail)
                    <td colspan="5">&nbsp;</td>
                    <td>{{ $detail->kd_rek }}</td>
                    <td>{{ $detail->rekening->nm_rek }}</td>
                    <td style="text-align: right">{{ rp($detail->debet) }}</td>
                    <td style="text-align: right">{{ rp($detail->kredit) }}</td>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">Total:</td>
                <td style="vertical-align: top; text-align: right">{{ $totalDebet != 0 ? 'Rp. ' . number_format($totalDebet, 0, '.', '.') : '' }}</td>
                <td style="vertical-align: top; text-align: right">{{ $totalKredit != 0 ? 'Rp. ' . number_format($totalKredit, 0, '.', '.') : '' }}</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="conclusion">
        <div class="time">
            <p><b>Samarinda, {{ now()->formatLocalized('%d %B %Y') }}</b></p>
        </div>
        <div class="signature">
            <div style="text-align: center;">
                <p><b>Menyetujui</b></p>
                <br>
                <br>
                <br>
                <p><b>dr. Daisy Wijaya</b></p>
                <p><b>Manager Keuangan</b></p>
            </div>
            <div style="text-align: center;"> 
                <p><b>Mengetahui</b></p>
                <br>
                <br>
                <br>
                <p><b>dr. Teguh Nurwanto, MARS</b></p>
                <p><b>Direktur</b></p>
            </div>
        </div>
    </div>
    @push('js')
        <script>
            window.onload = () => window.print()
        </script>
    @endpush
</div>
