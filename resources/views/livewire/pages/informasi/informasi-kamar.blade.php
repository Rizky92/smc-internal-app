<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        #scrollingContent {
        max-height: 100vh;
        overflow-y: hidden;
    }
    #scrollingContent table {
        animation: marqueeAnimation 60s linear infinite;
        transform-origin: 0% 0%;
        display: table;
        width: 100%;
    }
    #scrollingContent table tbody {
        display: table-row-group;
    }
    
    #scrollingContent table tbody tr:nth-child(even) {
    background-color: #28a745;
    color: white;
    }
    
    @keyframes marqueeAnimation {
      0% {
          transform: translateY(0);
      }
      100% {
          transform: translateY(calc(-300% + 300px));
      }
    }
    </style>
</head>
<body>
    @if ($informasiKamar->count() > 0)
        <table class="table table-bordered table-striped text-white">
            <thead class="bg-green">
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
                            <td>Jumlah Terisi : {{ $this->countOccupiedRooms($bangsal->kd_bangsal) }} | Jumlah Kosong : {{ $this->countEmptyRooms($bangsal->kd_bangsal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
@else
    <p>No data available</p>
@endif

<script>
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
    }, 60000);
</script>
</body>
</html>


