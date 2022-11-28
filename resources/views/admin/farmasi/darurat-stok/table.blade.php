<table id="table_index">
    <thead>
        <tr>
            <th>Kode barang</th>
            <th>Nama</th>
            <th>Satuan kecil</th>
            <th>Kategori</th>
            <th>Supplier</th>
            <th>Stok minimal</th>
            <th>Stok saat ini</th>
            <th>Saran order</th>
            <th>Harga Per Unit (Rp)</th>
            <th>Total Harga (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($daruratStok as $barang)
            @php($saranOrder = $barang->saran_order < 0 ? '0' : $barang->saran_order)
            <tr>
                <td>{{ $barang->kode_brng }}</td>
                <td>{{ $barang->nama_brng }}</td>
                <td>{{ $barang->satuan_kecil }}</td>
                <td>{{ $barang->kategori }}</td>
                <td>{{ $barang->nama_industri }}</td>
                <td>{{ $barang->stokminimal }}</td>
                <td>{{ $barang->stok_saat_ini }}</td>
                <td>{{ $saranOrder }}</td>
                <td>{{ ceil($barang->h_beli) }}</td>
                <td>{{ ceil($barang->h_beli * $saranOrder) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
