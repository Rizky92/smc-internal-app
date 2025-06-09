<div>
    @if ($nextAntrian)
        <span id="nomorAntrian">{{ $nextAntrian->no_reg }}</span>
        <script>
            document.addEventListener('livewire:load', function () {
                Livewire.on('pasienCalled', function (data) {
                    callPasien(data.no_reg, data.poli);
                });
            });
        </script>
    @endif
</div>

<script>
    function callPasien(noAntrian, poli) {
        let message = `Nomor antrian ${noAntrian}, ${poli}`;

        // Panggil fungsi TTS atau lakukan aksi lainnya di sini
        // responsiveVoice.speak(message, 'Indonesian Female', { rate: 0.7 });
    }
</script>
