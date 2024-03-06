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
                border-collapse: collapse;
                margin-bottom: 20px;
                font-size: 9px;
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
                page-break-after: auto; 
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
                    size: portrait;
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
                    <h2 style="font-size: 12px; margin: 0;">RS SAMARINDA MEDIKA CITRA</h2>
                    <p style="font-size: 9px; margin: 1px;">Jl. Kadrie Oening no.85, RT.35, Kel. Air Putih, Kec. Samarinda Ulu, Samarinda, Kalimantan Timur
                        <br>TEL:0541-7273000
                        <br>E-mail:info@rssmc.co.id
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
            <h2 style="font-size: 11px">POSTING JURNAL</h2>
        </div>
    </div>

    @if(!empty($savedData) && is_array($savedData))
        <table style="width: 100%">
            <thead>
                <tr>
                    <td style="width: 10%">No. Jurnal</td>
                    <td style="width: 10%">No. Bukti</td>
                    <td style="width: 10%">Tgl. Jurnal</td>
                    <td style="width: 5%">Jenis</td>
                    <td style="width: 10%">Keterangan</td>
                    <td>Kode Akun</td>
                    <td style="width: 35%">Nama Akun</td>
                    <td style="text-align: right; width: 10%">Debet</td>
                    <td style="text-align: right; width: 10%">Kredit</td>
                </tr>
            </thead>
            <tbody class="no-border-table">
                @php
                    $totalDebet = 0;
                    $totalKredit = 0;
                @endphp

                @foreach ($savedData as $jurnal)
                    @foreach ($jurnal['details'] as $detail)
                        @php
                            $totalDebet += $detail['debet'];
                            $totalKredit += $detail['kredit'];
                        @endphp
                        <tr>
                            <td>{{ $loop->first ? $jurnal['jurnal']['no_jurnal'] : '' }}</td>
                            <td>{{ $loop->first ? $jurnal['jurnal']['no_bukti'] : '' }}</td>
                            <td>{{ $loop->first ? $jurnal['jurnal']['tgl_jurnal'] : '' }} {{ $loop->first ? $jurnal['jurnal']['jam_jurnal'] : '' }}</td>
                            <td>{{ $loop->first ? $jurnal['jurnal']['jenis'] === 'U' ? 'Umum' : 'Penyesuaian' : '' }}</td>
                            <td>{{ $loop->first ? $jurnal['jurnal']['keterangan'] : '' }}</td>
                            <td>{{ $detail['kd_rek'] }}</td>
                            <td>{{ $rekeningData[$detail['kd_rek']] ?? '' }}</td>
                            <td style="text-align: right">{{ $detail['debet'] != 0 ? 'Rp. ' . number_format($detail['debet'], 0, '.', '.') : '' }}</td>
                            <td style="text-align: right">{{ $detail['kredit'] != 0 ? 'Rp. ' . number_format($detail['kredit'], 0, '.', '.') : '' }}</td>                       
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot class="no-border-table">
                <tr>
                    <td colspan="7">Jumlah Total:</td>
                    <td style="text-align: right">{{ $totalDebet != 0 ? 'Rp. ' . number_format($totalDebet, 0, '.', '.') : '' }}</td>
                    <td style="text-align: right">{{ $totalKredit != 0 ? 'Rp. ' . number_format($totalKredit, 0, '.', '.') : '' }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <p>Data jurnal sementara kosong.</p>
    @endif
    
    <div class="conclusion">
        <div class="time">
            <p style="font-size: 10px"><b>Samarinda,{{ now()->formatLocalized('%d %B %Y') }}</b></p>
        </div>
        <div class="signature">
            <div style="text-align: center; font-size: 10px;">
                <p><b>Menyetujui</b></p>
                <br>
                <br>
                <br>
                <p><b>dr. Daisy Wijaya</b></p>
                <p><b>Manager Keuangan</b></p>
            </div>
            <div style="text-align: center; font-size: 10px;"> 
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
            window.onload = function () {
                window.print();
            }
        </script>
    @endpush

</div>
