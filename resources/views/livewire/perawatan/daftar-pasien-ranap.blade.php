<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            @can('perawatan.daftar-pasien-ranap.update-biaya-ranap')
                @once
                    @push('js')
                        <script>
                            let inputNoRawat
                            let inputKamar
                            let inputPasien
                            let inputHargaKamar
                            let inputLamaInap
                            let inputTotalHarga

                            let hiddenKdKamar
                            let hiddenTglMasuk
                            let hiddenJamMasuk
                            let hiddenTarifKamar

                            let buttonSimpan
                            let buttonBatalSimpan

                            $(document).ready(() => {
                                inputNoRawat = $('#no_rawat')
                                inputKamar = $('#kamar')
                                inputPasien = $('#pasien')
                                inputHargaKamar = $('#harga_kamar')
                                inputLamaInap = $('#lama_inap')
                                inputTotalHarga = $('#total_harga')

                                hiddenKdKamar = $('#kd_kamar')
                                hiddenTglMasuk = $('#tgl_masuk')
                                hiddenJamMasuk = $('#jam_masuk')
                                hiddenTarifKamar = $('#trf_kamar')

                                buttonSimpan = $('#simpan-data')
                                buttonBatalSimpan = $('#batal-simpan')

                                buttonSimpan.prop('disabled', true)
                                buttonBatalSimpan.prop('disabled', true)

                                buttonSimpan.click(e => {
                                    let noRawat = inputNoRawat.val()
                                    let kdKamar = hiddenKdKamar.val()
                                    let tglMasuk = hiddenTglMasuk.val()
                                    let jamMasuk = hiddenJamMasuk.val()
                                    let hargaKamarBaru = inputHargaKamar.val()

                                    @this.emit('updateHargaKamar', noRawat, kdKamar, tglMasuk, jamMasuk, hargaKamarBaru)
                                })

                                Livewire.on('updateHargaKamar', clearData)

                                buttonBatalSimpan.click(clearData)
                            })

                            function loadData({
                                noRawat,
                                kamar,
                                pasien,
                                hargaKamar,
                                lamaInap,
                                totalHarga,
                                kdKamar,
                                tglMasuk,
                                jamMasuk
                            }) {
                                inputNoRawat.val(noRawat)
                                inputKamar.val(kamar)
                                inputPasien.val(pasien)
                                inputHargaKamar.val(hargaKamar)
                                inputLamaInap.val(lamaInap)
                                inputTotalHarga.val(totalHarga)

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
                            <input type="text" class="form-control form-control-sm" id="harga_kamar" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-1">
                        <div class="form-group">
                            <label class="text-sm text-white" for="lama_inap">Lama Inap</label>
                            <div class="d-flex align-items-center">
                                <span class="font-weight-medium pr-3">x</span>
                                <input type="text" class="form-control form-control-sm" id="lama_inap" readonly autocomplete="off">
                            </div>
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
                </x-card.row-col>
            @endcan

            <x-card.row-col class="mt-3">
                <x-filter.range-datetime />
                <x-filter.label class="ml-auto pr-3">Status:</x-filter.label>
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
            <x-table style="width: 180rem">
                <x-slot name="columns">
                    <x-table.th style="width: 20ch">No. Rawat</x-table.th>
                    <x-table.th style="width: 10ch">No. RM</x-table.th>
                    <x-table.th>Kamar</x-table.th>
                    <x-table.th style="width: 25ch">Pasien</x-table.th>
                    <x-table.th>Alamat</x-table.th>
                    <x-table.th style="width: 8ch">Agama</x-table.th>
                    <x-table.th style="width: 25ch">P.J.</x-table.th>
                    <x-table.th style="width: 20ch">Jenis Bayar</x-table.th>
                    <x-table.th style="width: 10ch">Asal Poli</x-table.th>
                    <x-table.th style="width: 25ch">Dokter Poli</x-table.th>
                    <x-table.th style="width: 15ch">Status</x-table.th>
                    <x-table.th style="width: 12ch">Tgl. Masuk</x-table.th>
                    <x-table.th style="width: 12ch">Jam Masuk</x-table.th>
                    <x-table.th style="width: 12ch">Tgl. Keluar</x-table.th>
                    <x-table.th style="width: 12ch">Jam Keluar</x-table.th>
                    <x-table.th style="width: 15ch">Tarif</x-table.th>
                    <x-table.th>Dokter P.J.</x-table.th>
                    <x-table.th>No. HP</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->daftarPasienRanap as $pasien)
                        <x-table.tr>
                            <x-table.td>
                                {{ $pasien->no_rawat }}
                                <x-slot name="clickable" data-no-rawat="{{ $pasien->no_rawat }}" data-kamar="{{ $pasien->ruangan }}" data-pasien="{{ $pasien->data_pasien }}" data-harga-kamar="{{ $pasien->trf_kamar }}" data-lama-inap="{{ $pasien->lama }}" data-total-harga="{{ $pasien->ttl_biaya }}" data-kd-kamar="{{ $pasien->kd_kamar }}" data-tgl-masuk="{{ $pasien->tgl_masuk }}" data-jam-masuk="{{ $pasien->jam_masuk }}"></x-slot>
                            </x-table.td>
                            <x-table.td>{{ $pasien->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $pasien->ruangan }}</x-table.td>
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
                            <x-table.td>{{ $pasien->nama_dokter }}</x-table.td>
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
