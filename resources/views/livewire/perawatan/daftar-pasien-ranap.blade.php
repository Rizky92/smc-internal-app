<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            @can('perawatan.daftar-pasien-ranap.update-harga-kamar')
                @once
                    @push('js')
                        <script>
                            let inputNoRawat
                            let inputKamar
                            let inputPasien
                            let inputHargaKamar
                            let inputLamaInap
                            let inputTotalHarga
                            
                            let inputReadClipboard

                            let hiddenKdKamar
                            let hiddenTglMasuk
                            let hiddenJamMasuk
                            let hiddenTarifKamar

                            let buttonSimpan
                            let buttonBatalSimpan
                            let buttonResetFilters

                            $(document).ready(() => {
                                inputNoRawat = $('#no_rawat')
                                inputKamar = $('#kamar')
                                inputPasien = $('#pasien')
                                inputHargaKamar = $('#harga_kamar')
                                inputLamaInap = $('#lama_inap')
                                inputTotalHarga = $('#total_harga')

                                inputReadClipboard = $('textarea#copy-to-clipboard')

                                hiddenKdKamar = $('#kd_kamar')
                                hiddenTglMasuk = $('#tgl_masuk')
                                hiddenJamMasuk = $('#jam_masuk')
                                hiddenTarifKamar = $('#trf_kamar')

                                buttonSimpan = $('#simpan-data')
                                buttonBatalSimpan = $('#batal-simpan')
                                buttonCopyData = $('button#copy-data')
                                buttonResetFilters = $('#reset-filters')

                                buttonSimpan.prop('disabled', true)
                                buttonBatalSimpan.prop('disabled', true)
                                buttonCopyData.prop('disabled', true)

                                buttonSimpan.click(e => {
                                    let noRawat = inputNoRawat.val()
                                    let kdKamar = hiddenKdKamar.val()
                                    let tglMasuk = hiddenTglMasuk.val()
                                    let jamMasuk = hiddenJamMasuk.val()
                                    let hargaKamarBaru = inputHargaKamar.val()
                                    let lamaInap = inputLamaInap.val()

                                    @this.updateHargaKamar(noRawat, kdKamar, tglMasuk, jamMasuk, hargaKamarBaru, lamaInap)
                                })

                                buttonResetFilters.click(clearData)

                                inputHargaKamar.keyup(updateTotalHarga)
                                inputHargaKamar.change(updateTotalHarga)

                                inputLamaInap.keyup(updateTotalHarga)
                                inputLamaInap.change(updateTotalHarga)

                                document.addEventListener('data-tersimpan', clearData)

                                buttonBatalSimpan.click(clearData)
                            })

                            function updateTotalHarga(e) {
                                let val = inputHargaKamar.val()

                                inputTotalHarga.val(val * inputLamaInap.val())

                                inputTotalHarga.trigger('change')
                            }

                            function loadData({
                                noRawat,
                                kamar,
                                pasien,
                                hargaKamar,
                                lamaInap,
                                totalHarga,
                                kdKamar,
                                tglMasuk,
                                jamMasuk,
                                clipboard
                            }) {
                                inputNoRawat.val(noRawat)
                                inputKamar.val(kamar)
                                inputPasien.val(pasien)
                                inputHargaKamar.val(hargaKamar)
                                inputLamaInap.val(lamaInap)
                                inputTotalHarga.val(totalHarga)

                                inputReadClipboard.val(clipboard)

                                hiddenKdKamar.val(kdKamar)
                                hiddenTglMasuk.val(tglMasuk)
                                hiddenJamMasuk.val(jamMasuk)
                                hiddenTarifKamar.val(hargaKamar)

                                inputKamar.trigger('change')
                                inputNoRawat.trigger('change')
                                inputPasien.trigger('change')
                                inputHargaKamar.trigger('change')
                                inputLamaInap.trigger('change')
                                inputTotalHarga.trigger('change')

                                inputReadClipboard.trigger('change')

                                hiddenKdKamar.trigger('change')
                                hiddenTglMasuk.trigger('change')
                                hiddenJamMasuk.trigger('change')
                                hiddenTarifKamar.trigger('change')

                                buttonSimpan.prop('disabled', false)
                                buttonBatalSimpan.prop('disabled', false)
                            }

                            function clearData() {
                                inputNoRawat.val('')
                                inputKamar.val('')
                                inputPasien.val('')
                                inputHargaKamar.val('')
                                inputLamaInap.val('')
                                inputTotalHarga.val('')

                                inputReadClipboard.val('')

                                hiddenKdKamar.val('')
                                hiddenTglMasuk.val('')
                                hiddenJamMasuk.val('')
                                hiddenTarifKamar.val('')

                                inputKamar.trigger('change')
                                inputNoRawat.trigger('change')
                                inputPasien.trigger('change')
                                inputHargaKamar.trigger('change')
                                inputLamaInap.trigger('change')
                                inputTotalHarga.trigger('change')

                                inputReadClipboard.trigger('change')

                                hiddenKdKamar.trigger('change')
                                hiddenTglMasuk.trigger('change')
                                hiddenJamMasuk.trigger('change')
                                hiddenTarifKamar.trigger('change')

                                buttonSimpan.prop('disabled', true)
                                buttonBatalSimpan.prop('disabled', true)
                            }
                        </script>
                    @endpush
                @endonce

                <x-card.row>
                    <div class="col-4">
                        <div class="form-group">
                            <label class="text-sm" for="no_rawat">No. Rawat</label>
                            <input type="text" class="form-control form-control-sm" id="no_rawat" readonly autocomplete="off">
                            <input type="hidden" id="kd_kamar">
                            <input type="hidden" id="trf_kamar">
                            <input type="hidden" id="tgl_masuk">
                            <input type="hidden" id="jam_masuk">
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label class="text-sm" for="kamar">Kamar</label>
                            <input type="text" class="form-control form-control-sm" id="kamar" readonly autocomplete="off">
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            <label class="text-sm" for="pasien">Pasien</label>
                            <input type="text" class="form-control form-control-sm" id="pasien" readonly autocomplete="off">
                        </div>
                    </div>
                </x-card.row>

                <x-card.row>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="text-sm" for="harga_kamar">Harga Kamar</label>
                            <div class="d-flex align-items-center">
                                <input type="number" class="form-control form-control-sm" id="harga_kamar" autocomplete="off" min="0">
                                <span class="font-weight-medium pl-3">x</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-1">
                        <div class="form-group">
                            <label class="text-sm" for="lama_inap">Lama Inap</label>
                            <input type="number" class="form-control form-control-sm" id="lama_inap" autocomplete="off" min="0">
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            <label class="text-sm" for="total_harga">Total Harga</label>
                            <input type="text" class="form-control form-control-sm" id="total_harga" readonly autocomplete="off">
                        </div>
                    </div>
                </x-card.row>

                <x-card.row-col class="pb-3 border-bottom">
                    <x-button disabled class="btn-primary" id="simpan-data" title="Simpan" icon="fas fa-save" />
                    <x-button disabled class="btn-default ml-2" id="batal-simpan" title="Batal" />
                    <textarea id="copy-to-clipboard" readonly rows="2" class="text-xs border-0 ml-2" style="font-family: monospace; resize: none; display: block; flex-grow: 1; white-space: pre-wrap"></textarea>
                </x-card.row-col>
            @endcan

            <x-card.row-col :class="Arr::toCssClasses(['mt-3' => auth()->user()->can('perawatan.daftar-pasien-ranap.update-harga-kamar')])">
                <x-filter.range-date />
                <x-filter.label class="ml-auto pr-3">Berdasarkan:</x-filter.label>
                <div class="input-group input-group-sm" style="width: max-content">
                    <x-filter.select model="statusPerawatan" :options="[
                        '-' => 'Sedang Dirawat',
                        'tanggal_masuk' => 'Tgl. Masuk',
                        'tanggal_keluar' => 'Tgl. Keluar',
                    ]" />
                </div>
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search />
                <x-filter.button-export-excel class="ml-2" />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body" class="table-responsive">
            <x-table style="width: 250rem" sortable :sortColumns="$sortColumns">
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" style="width: 20ch" />
                    <x-table.th name="no_rkm_medis" title="No. RM" style="width: 10ch" />
                    <x-table.th name="ruangan" title="Kamar" style="width: 35ch" />
                    <x-table.th name="kelas" title="Kelas" style="width: 10ch" />
                    <x-table.th name="data_pasien" title="Pasien" style="width: 50ch" />
                    <x-table.th name="alamat_pasien" title="Alamat" />
                    <x-table.th name="agama" title="Agama" style="width: 10ch" />
                    <x-table.th name="pj" title="P.J." style="width: 30ch" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" style="width: 25ch" />
                    <x-table.th name="nm_poli" title="Asal Poli" style="width: 20ch" />
                    <x-table.th name="dokter_poli" title="Dokter Poli" style="width: 40ch" />
                    <x-table.th name="stts_pulang" title="Status" style="width: 15ch" />
                    <x-table.th name="tgl_masuk" title="Tgl. Masuk" style="width: 12ch" />
                    <x-table.th name="jam_masuk" title="Jam Masuk" style="width: 12ch" />
                    <x-table.th name="tgl_keluar" title="Tgl. Keluar" style="width: 12ch" />
                    <x-table.th name="jam_keluar" title="Jam Keluar" style="width: 12ch" />
                    <x-table.th name="trf_kamar" title="Tarif Kamar" style="width: 15ch" />
                    <x-table.th name="lama" title="Lama" style="width: 10ch" />
                    <x-table.th name="ttl_biaya" title="Total" style="width: 20ch" />
                    <x-table.th name="dokter_ranap" title="DPJP" style="width: 35ch" />
                    <x-table.th name="no_tlp" title="No. HP" style="width: 15ch" />
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->daftarPasienRanap as $pasien)
                        <x-table.tr>
                            <x-table.td>
                                {{ $pasien->no_rawat }}
                                <x-slot name="clickable" data-no-rawat="{{ $pasien->no_rawat }}" data-kamar="{{ $pasien->ruangan }}" data-pasien="{{ $pasien->data_pasien }}" data-harga-kamar="{{ $pasien->trf_kamar }}" data-lama-inap="{{ $pasien->lama }}" data-total-harga="{{ $pasien->ttl_biaya }}" data-kd-kamar="{{ $pasien->kd_kamar }}" data-tgl-masuk="{{ $pasien->tgl_masuk }}" data-jam-masuk="{{ $pasien->jam_masuk }}" data-clipboard="{{ collect($pasien->getAttributes())->except('kd_kamar')->join('   ') }}"></x-slot>
                            </x-table.td>
                            <x-table.td>{{ $pasien->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $pasien->ruangan }}</x-table.td>
                            <x-table.td>{{ $pasien->kelas }}</x-table.td>
                            <x-table.td>{{ $pasien->data_pasien }}</x-table.td>
                            <x-table.td>{{ $pasien->alamat_pasien }}</x-table.td>
                            <x-table.td>{{ $pasien->agama }}</x-table.td>
                            <x-table.td>{{ $pasien->pj }}</x-table.td>
                            <x-table.td>{{ $pasien->png_jawab }}</x-table.td>
                            <x-table.td>{{ $pasien->nm_poli }}</x-table.td>
                            <x-table.td>{{ $pasien->dokter_poli }}</x-table.td>
                            <x-table.td>{{ $pasien->stts_pulang }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_masuk }}</x-table.td>
                            <x-table.td>{{ $pasien->jam_masuk }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_keluar }}</x-table.td>
                            <x-table.td>{{ $pasien->jam_keluar }}</x-table.td>
                            <x-table.td>{{ rp($pasien->trf_kamar) }}</x-table.td>
                            <x-table.td>{{ $pasien->lama }}</x-table.td>
                            <x-table.td>{{ rp($pasien->ttl_biaya) }}</x-table.td>
                            <x-table.td>{{ $pasien->dokter_ranap }}</x-table.td>
                            <x-table.td>{{ $pasien->no_tlp }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <x-paginator :data="$this->daftarPasienRanap" />
        </x-slot>
    </x-card>
</div>
