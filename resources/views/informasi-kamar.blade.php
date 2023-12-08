<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- AdminLTE CSS --}}
    <link rel="stylesheet" href="css/adminlte.min.css">
    <!-- Bed CSS -->
    <link rel="stylesheet" href="css/bed.css">

    <title>Bed | Anjungan</title>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container-fluid d-flex justify-content-center">
        <img src="img/logo.png" alt="" width="100" height="80" class="d-inline-block align-text-top">
          <span>
              BED MANAGEMENT RS SMC
            </span>
      </div>
    </nav>
    @if ($informasiKamar->count() > 0)
    <table class="table table-bordered table-striped text-white">
        <thead>
            <tr>
                <th width="30%">Bangsal</th>
                <th width="20%">Kelas</th>
                <th>Status</th>
            </tr>
        </thead>
    </table>

    <div id="scrollingContent">
        <table class="table table-bordered table-striped">
            <tbody>
                <p style="height: 30px;"></p>
                <p style="height: 30px;"></p>
                <p style="height: 30px;"></p>
                <p style="height: 30px;"></p>
                <p style="height: 30px;"></p>
                <p style="height: 30px;"></p>
                <p style="height: 30px;"></p>
                <p style="height: 30px;"></p>
                <p style="height: 30px;"></p>
                <p style="height: 30px;"></p>
                @foreach ($informasiKamar as $bangsal)
                    <tr>
                        <td width="30%">{{ $bangsal->nm_bangsal }}</td>
                        <td width="20%">{{ $bangsal->kelas }}</td>
                        <td>Jumlah Terisi : {{ app(App\Http\Controllers\KamarController::class)->countOccupiedRooms($bangsal->kd_bangsal) }} | Jumlah Kosong : {{ app(App\Http\Controllers\KamarController::class)->countEmptyRooms($bangsal->kd_bangsal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
<p>No data available</p>
@endif

<script>
    function refreshPage() {
    setTimeout(function () {
        location.reload(true); // Reload halaman dengan membersihkan cache
    }, 40000); // Waktu dalam milidetik (60 detik)
    }
    
    document.addEventListener('DOMContentLoaded', function () {
    refreshPage();
    });

    setInterval(function () {
        updateDateTime();
        refreshPage();
    }, 40000); // Set interval untuk memastikan halaman direfresh setiap 60 detik
    function startScrollAnimation() {
        const scrollingContent = document.getElementById('scrollingContent');
        scrollingContent.style.animation = 'none';
        void scrollingContent.offsetWidth;
        scrollingContent.style.animation = null; 
        scrollingContent.style.animationPlayState = 'running';
        setTimeout(() => {
            scrollingContent.classList.add('hidden');
        }, 15000);
    };
    document.addEventListener('DOMContentLoaded', function () {
        startScrollAnimation();
    });
    setInterval(function () {
        updateDateTime();
        const scrollingContent = document.getElementById('scrollingContent');
        scrollingContent.classList.remove('hidden');
        startScrollAnimation();
    }, 40000);
</script>
</body>
</html>
