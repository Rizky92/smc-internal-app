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
                font-size: 0.65em;
            }

            td {
                vertical-align: top;
                white-space: pre-wrap;
                padding: 0 0.125rem 0 0.125rem;
                margin: 0;
            }

            thead {
                color-adjust: exact !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #f0f0dc;
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
                font-size: 0.7em;
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
                    size: landscape;
                }
            }
        </style>
    @endpush

    <div id="printHeader">
        <div class="container">
            <img
                src="data:image/jpeg;base64,{{ base64_encode($this->SIMRSSettings->logo) }}"
                style="display: block; width: 80px; position: absolute; left: 1%; top: 1%; transform: translate(-1% -1%)" />
            <div class="row">
                <div class="col-12">
                    <h2 style="font-size: 12pt; margin: 0">
                        {{ $this->SIMRSSettings->nama_instansi }}
                    </h2>
                    <p style="font-size: 9pt; margin: 1px">
                        {{ $this->SIMRSSettings->alamat_instansi }}
                        <br />
                        {{ $this->SIMRSSettings->kontak }}
                        <br />
                        E-mail: {{ $this->SIMRSSettings->email }}
                    </p>
                </div>
            </div>
        </div>
        <hr style="border-top: 1px solid #000; margin-top: 10px; margin-bottom: 0px" />
        <hr style="border-top: 1px solid #000; margin-top: 4px; margin-bottom: 10px" />
    </div>

    <div class="row">
        <div class="col-12">
            <h2 style="font-size: 12pt">POSTING JURNAL</h2>
        </div>
    </div>

    <table style="min-width: 100%">
        <thead>
            <tr>
                <td style="width: 9.5%">No. Jurnal</td>
                <td style="width: 10%">No. Bukti</td>
                <td style="width: 10%">Tgl. Jurnal</td>
                <td style="width: 4%">Jenis</td>
                <td>Keterangan</td>
                <td style="text-align: right; width: 10%">Debet</td>
                <td style="text-align: right; width: 10%">Kredit</td>
            </tr>
        </thead>
        <tbody class="no-border-table">
            @php
                $totalDebet = 0;
                $totalKredit = 0;
            @endphp

            @foreach ($printJurnal as $jurnal)
                @php
                    $detailPertama = $jurnal->detail->first();
                    $totalDebet += $jurnal->detail->sum('debet');
                    $totalKredit += $jurnal->detail->sum('kredit');
                    $index = 1;
                @endphp

                <tr>
                    <td>{{ $jurnal->no_jurnal }}</td>
                    <td>{{ $jurnal->no_bukti }}</td>
                    <td>{{ $jurnal->tgl_jurnal }} {{ $jurnal->jam_jurnal }}</td>
                    <td>
                        {{ $jurnal->jenis === 'U' ? 'UMUM' : 'PENYESUAIAN' }}
                    </td>
                    <td>{{ $jurnal->keterangan }}</td>
                </tr>
                <tr>
                    <td colspan="4">&nbsp;</td>
                    <td>
                        {{ $index }}. {{ $detailPertama->kd_rek }},
                        {{ $detailPertama->rekening->nm_rek }}
                    </td>
                    <td style="text-align: right">
                        {{ rp($detailPertama->debet) }}
                    </td>
                    <td style="text-align: right">
                        {{ rp($detailPertama->kredit) }}
                    </td>
                </tr>
                @foreach ($jurnal->detail->skip(1) as $detail)
                    @php
                        $index++;
                    @endphp

                    <tr>
                        <td colspan="4">&nbsp;</td>
                        <td>
                            {{ $index }}. {{ $detail->kd_rek }},
                            {{ $detail->rekening->nm_rek }}
                        </td>
                        <td style="text-align: right">
                            {{ rp($detail->debet) }}
                        </td>
                        <td style="text-align: right">
                            {{ rp($detail->kredit) }}
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="4">&nbsp;</td>
                    <td>Total:</td>
                    <td style="text-align: right">
                        {{ rp($jurnal->detail->sum('debet')) }}
                    </td>
                    <td style="text-align: right">
                        {{ rp($jurnal->detail->sum('kredit')) }}
                    </td>
                </tr>
            @endforeach

            <tr style="border-top: 1px solid #202020; border-bottom: 1px solid #202020">
                <td colspan="5">Total:</td>
                <td style="vertical-align: top; text-align: right; font-weight: bold">
                    {{ $totalDebet != 0 ? 'Rp. ' . number_format($totalDebet, 0, '.', '.') : '' }}
                </td>
                <td style="vertical-align: top; text-align: right; font-weight: bold">
                    {{ $totalKredit != 0 ? 'Rp. ' . number_format($totalKredit, 0, '.', '.') : '' }}
                </td>
            </tr>
        </tbody>
    </table>
    <div style="display: grid; grid-template-columns: max-content 1fr max-content; font-size: 0.65em; font-weight: bold; page-break-inside: avoid">
        <div style="text-align: center">
            <div></div>
            <div>Menyetujui</div>
            <br />
            <br />
            <br />
            <div>Ns. YULIANA PABIBAK, S.Kep</div>
            <div>MANAGER KEUANGAN</div>
        </div>
        <div></div>
        <div style="text-align: center">
            <div>Samarinda, {{ now()->translatedFormat('d F Y') }}</div>
            <div>Mengetahui</div>
            <br />
            <br />
            <br />
            <div>dr. TEGUH NURWANTO, MARS</div>
            <div>DIREKTUR</div>
        </div>
    </div>
    @push('js')
        <script>
            window.onload = () => window.print();
        </script>
    @endpush
</div>
