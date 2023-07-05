<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Models\Keuangan\RKAT\PemakaianAnggaranDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RKATSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        Bidang::truncate();
        Anggaran::truncate();
        AnggaranBidang::truncate();
        PemakaianAnggaran::truncate();
        PemakaianAnggaranDetail::truncate();

        Bidang::insert([
            ['nama' => 'Keuangan'],
            ['nama' => 'Marketing'],
            ['nama' => 'Pelayanan Medis'],
            ['nama' => 'SDM'],
            ['nama' => 'Umum'],
        ]);

        Anggaran::insert([
            ['nama' => 'Kebutuhan Program Kerja', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kebutuhan Ketenagakerjaan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kebutuhan Barang Umum', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kebutuhan Barang Alkes', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kebutuhan Barang Obat', 'created_at' => now(), 'updated_at' => now()],
        ]);

        AnggaranBidang::insert([
            ['bidang_id' => 1, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 58532122865, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 1, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 1818732347, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 1, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 169253345, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 1, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 4962623, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 1, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],

            ['bidang_id' => 2, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 374035000, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 2, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 1068214956, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 2, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 45289690, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 2, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 1561800, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 2, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],

            ['bidang_id' => 3, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 2641290000, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 3, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 1866038114, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 3, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 35453084, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 3, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 26227062, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 3, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],

            ['bidang_id' => 4, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 6477214955, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 4, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 890426877, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 4, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 28989220, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 4, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 6127189, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 4, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],

            ['bidang_id' => 5, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 9975789451, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 5, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 1598399150, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 5, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 623717072, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 5, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 6054475, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 5, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        PemakaianAnggaran::insert($dataPemakaianAnggaran = [
            ['anggaran_bidang_id' => 1, 'judul' => 'Lorem Ipsum Dolor Sit, Amet Consectetur Adipisicing', 'deskripsi' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Debitis ipsum eum, cum hic eveniet necessitatibus blanditiis nam cupiditate fuga. Odio.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 1, 'judul' => 'Aperiam Minus Obcaecati Consequuntur Unde, Recusandae Dolores', 'deskripsi' => 'Earum explicabo ducimus, labore dicta itaque facilis deleniti fugiat at corrupti odio incidunt rem dolores beatae animi neque architecto numquam.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 1, 'judul' => 'Repellat Quisquam Expedita Eaque, Animi Earum Quos', 'deskripsi' => 'Odio unde nobis assumenda odit, facere deserunt eaque alias, facilis, provident minus possimus aliquid autem architecto veritatis. Fugiat, sed? Libero.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 1, 'judul' => 'Omnis Similique Hic Quos? Placeat, Illum Nesciunt', 'deskripsi' => 'Optio, at fugiat? Culpa, magni. Natus harum amet praesentium, eos incidunt enim magnam nemo aspernatur libero, quibusdam ea iste autem.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 1, 'judul' => 'Quas Sed Dolorem Fugit Officiis Quaerat Vel', 'deskripsi' => 'Quia numquam ex obcaecati, saepe molestias totam sapiente quas veritatis! Blanditiis cupiditate in debitis nam ducimus animi. Nisi, optio ab.', 'tgl_dipakai' => carbon('2023-03-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 1, 'judul' => 'Rerum, Vero Nesciunt! Facere In Ratione Expedita', 'deskripsi' => 'Deleniti, eum, aperiam commodi autem impedit officia exercitationem velit sint aut fugit iste mollitia temporibus eius omnis a aliquam. Numquam.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 1, 'judul' => 'Eos Eveniet Atque Laudantium Assumenda Minus Sunt', 'deskripsi' => 'Necessitatibus architecto corporis itaque, fugit impedit saepe id veritatis ad. Nihil magni distinctio enim nobis suscipit odio eveniet mollitia voluptates.', 'tgl_dipakai' => carbon('2023-05-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 2, 'judul' => 'Placeat Necessitatibus Sit, Non Corrupti Reprehenderit Eum', 'deskripsi' => 'Numquam quos rerum alias, vero aliquid voluptates eligendi a! Quaerat voluptate debitis assumenda magnam excepturi optio, perferendis a sunt molestias.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 2, 'judul' => 'Tempora, Totam Magni Ut Commodi Neque Cum', 'deskripsi' => 'Quaerat, reiciendis. Ad deserunt, minus doloremque sequi hic iure sapiente provident quis quas ullam? Similique dolorum deleniti sint laudantium sunt.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 2, 'judul' => 'Laudantium Blanditiis, Hic Quisquam Nostrum Illum Fugiat', 'deskripsi' => 'Odio pariatur alias molestiae, unde quo ut reiciendis optio itaque. Aut, quae quos tempora ipsam aspernatur similique a laboriosam accusantium.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 2, 'judul' => 'Recusandae Error Voluptates Neque Beatae Quaerat. Voluptate', 'deskripsi' => 'Recusandae fuga ex iusto aspernatur atque, soluta fugiat, qui impedit in, esse exercitationem! Quo dicta voluptatibus modi deleniti ab rem.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 2, 'judul' => 'Omnis Neque Eius Id Assumenda Officia Eos', 'deskripsi' => 'Neque, itaque modi alias delectus laborum perspiciatis quas aliquid quod, adipisci quidem ullam molestias a velit eius voluptatum nam voluptatem.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 2, 'judul' => 'Vel Aliquam Nostrum Quidem Illo Tempore Rerum', 'deskripsi' => 'Laudantium quas ratione accusantium eos impedit explicabo minus dolorum, maxime esse amet excepturi cum provident alias officia. Adipisci, eius eveniet.', 'tgl_dipakai' => carbon('2023-05-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 3, 'judul' => 'Ad Neque Odio Temporibus, Atque Recusandae Blanditiis', 'deskripsi' => 'Molestias iste voluptatum nulla veritatis sunt voluptas animi distinctio, sequi rerum est quis, eligendi magni possimus neque earum ea repudiandae.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 3, 'judul' => 'Ipsa Ducimus Sint Nisi Facere Dolor Quidem', 'deskripsi' => 'Accusantium beatae aperiam, dolores sequi corrupti velit ratione nam adipisci asperiores repellendus laborum maiores quis aut, dignissimos distinctio vel omnis.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 3, 'judul' => 'Aperiam Quae Eligendi Enim Dignissimos Porro Inventore', 'deskripsi' => 'Dolorem deleniti, facilis ratione ab veniam voluptatem rerum possimus saepe, molestias atque consectetur. Adipisci quisquam voluptatibus necessitatibus libero praesentium ipsum.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 3, 'judul' => 'Consequuntur Ex Dignissimos Debitis Modi Obcaecati Rerum', 'deskripsi' => 'Non nisi rem minus ducimus vero maiores eum quos, totam nesciunt sapiente quidem facilis est nobis, aspernatur qui impedit! Debitis.', 'tgl_dipakai' => carbon('2023-05-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 3, 'judul' => 'Dolorum Totam Alias Doloribus Culpa Earum! Molestias', 'deskripsi' => 'Quas perspiciatis maxime possimus iure, eveniet vel! Debitis commodi, ullam amet esse repellendus provident quos, perspiciatis consequuntur totam, sapiente in.', 'tgl_dipakai' => carbon('2023-05-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 4, 'judul' => 'Perspiciatis Iure, Deleniti Ut Eius Qui Quidem', 'deskripsi' => 'Exercitationem aliquid, atque quam neque non voluptatem magni tenetur ea dignissimos, fuga, recusandae doloremque facilis laboriosam sed accusamus! Minus, delectus.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 4, 'judul' => 'Tempora Ipsum Debitis Ducimus, Itaque Non Officia', 'deskripsi' => 'Unde non repellendus officiis ipsa esse explicabo, doloribus accusantium quam necessitatibus rem debitis hic nulla at qui deleniti. Atque, assumenda.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 4, 'judul' => 'Veritatis Deleniti Minus Beatae Error, Quas Quibusdam', 'deskripsi' => 'Incidunt corporis recusandae et possimus sapiente, quaerat commodi modi quidem earum cum, neque at iusto. Deserunt fuga doloribus voluptatem quia.', 'tgl_dipakai' => carbon('2023-03-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 6, 'judul' => 'Porro Est Aperiam Quidem Eos Ut Consectetur', 'deskripsi' => 'Obcaecati nesciunt sed, non, tempore sapiente, facere nulla quo reiciendis voluptate ut minus ad illum? Nam adipisci aliquid consequuntur facilis.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 6, 'judul' => 'Maxime Reprehenderit Quidem Quas Nam Adipisci Incidunt', 'deskripsi' => 'Deserunt quo necessitatibus, sit, amet at in repellendus, quis consequuntur quisquam dolores consequatur. Omnis quas, repellendus repudiandae ea quo voluptatem.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 6, 'judul' => 'Accusantium, Quis Maxime Eaque Corrupti Ab Facere', 'deskripsi' => 'Praesentium harum tenetur eos ipsum vero nostrum rem, perspiciatis nihil consequuntur molestias, accusantium saepe itaque quam ab suscipit corrupti earum.', 'tgl_dipakai' => carbon('2023-03-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 6, 'judul' => 'Dolorem, Omnis Itaque Consequatur Architecto Est Explicabo', 'deskripsi' => 'Odio, quia aut alias pariatur dolor sed! Culpa praesentium repellendus dicta tempore adipisci suscipit nesciunt doloribus molestiae. Nam, recusandae ab.', 'tgl_dipakai' => carbon('2023-03-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 6, 'judul' => 'Assumenda Debitis Quidem Vel Recusandae, Earum Commodi', 'deskripsi' => 'Vitae corporis sint molestiae beatae ab alias voluptas suscipit, sed minima, assumenda quod illo officiis excepturi adipisci atque dolore quam.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 6, 'judul' => 'Suscipit Nobis Modi Totam Quaerat, Animi Ducimus', 'deskripsi' => 'Est, ipsum provident similique dolorum quaerat at veritatis nobis nam quo illum facilis mollitia natus minima ut fugit repellendus enim.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 6, 'judul' => 'Cum Aliquam Saepe Architecto Magnam Quis Illo', 'deskripsi' => 'Ad nostrum error perferendis culpa quas modi iure in est. Adipisci harum consequatur quisquam expedita tenetur, eos porro exercitationem quia.', 'tgl_dipakai' => carbon('2023-05-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 7, 'judul' => 'Aspernatur Voluptatum Vero Distinctio Omnis Eaque Voluptas', 'deskripsi' => 'Libero fugiat, dicta officiis quos pariatur expedita praesentium excepturi nobis quam labore. Ad modi velit officiis enim voluptatem ea non.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 7, 'judul' => 'Libero Molestiae Adipisci Ad Corporis, Tempora Cum', 'deskripsi' => 'Accusamus vero ea eligendi, laboriosam, cum saepe pariatur cupiditate, soluta asperiores quis porro! Aspernatur reprehenderit fuga, aut dolor dolorem corporis.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 7, 'judul' => 'Est Iure Corrupti, Tenetur Recusandae Ipsam Beatae', 'deskripsi' => 'Tenetur perferendis itaque harum ullam neque dolorem repudiandae possimus aliquid magnam veniam iste fugit aliquam, tempore placeat sed sit laudantium.', 'tgl_dipakai' => carbon('2023-03-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 7, 'judul' => 'Placeat Eius Minima, Cumque Quaerat Ipsam Consectetur', 'deskripsi' => 'Quas facere, dolor officia dolorem mollitia labore fugiat totam ipsa atque ratione, nisi aliquid a nesciunt eaque, odit ut excepturi.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 7, 'judul' => 'Ducimus, Est Illum Quo Quos Maiores Voluptatibus', 'deskripsi' => 'Pariatur reiciendis, quia minus ex, debitis suscipit ipsa, doloribus tempora inventore beatae quo dolores harum. Quos nemo ea similique cum.', 'tgl_dipakai' => carbon('2023-05-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 8, 'judul' => 'Totam Aut Repellendus Ipsa Ex, Nam Consectetur', 'deskripsi' => 'Sequi velit ut asperiores quia alias neque ullam, provident earum voluptate illum. Aliquam aliquid eos dolor eligendi numquam eius deleniti.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 8, 'judul' => 'Quis Fugit Consequuntur Quibusdam Accusamus Sit Facilis', 'deskripsi' => 'Reprehenderit impedit doloremque aliquam architecto, quam molestiae consectetur sint quis similique corrupti corporis, assumenda ullam tenetur non illo, id vero.', 'tgl_dipakai' => carbon('2023-03-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 8, 'judul' => 'Corrupti Quidem Tenetur Quia Accusamus Deserunt. Pariatur', 'deskripsi' => 'Tenetur, rem officiis sint qui soluta facilis laudantium itaque ea perspiciatis nemo culpa voluptatem dolorem hic ratione eligendi aliquam veniam.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 8, 'judul' => 'Aspernatur, Culpa Temporibus! Magnam Soluta Necessitatibus Optio', 'deskripsi' => 'Eius perferendis accusantium eaque laborum. Commodi libero dolores, blanditiis aliquid mollitia quia maxime nulla ipsam illum asperiores delectus voluptatum. Consequatur.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 9, 'judul' => 'Ex Id Architecto Optio. Quam, Sapiente Illum', 'deskripsi' => 'Aliquam numquam voluptate dignissimos sapiente consequatur delectus fugit, omnis recusandae debitis perspiciatis a distinctio unde accusantium est ducimus doloremque nam.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 9, 'judul' => 'Magni, Illo Incidunt. Nemo Est Veritatis Neque', 'deskripsi' => 'Tenetur, mollitia assumenda sit, eum dolore totam dicta maxime molestias repellat dignissimos nesciunt atque explicabo sunt minus vero porro adipisci.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 9, 'judul' => 'Praesentium Necessitatibus Dolore, Repellendus Autem Voluptatum Magnam', 'deskripsi' => 'Amet explicabo sunt nesciunt eos ut blanditiis et consequuntur facilis. Tempore, ratione velit ad suscipit necessitatibus sed repellat dolorem voluptatibus.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 11, 'judul' => 'Porro, Id Magnam. Odio Blanditiis Quis In', 'deskripsi' => 'Eos minima atque reprehenderit earum tempora ea quas non voluptatum assumenda? Incidunt quidem ab tempore in blanditiis! Quasi, numquam ex.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 11, 'judul' => 'Illum Odit Recusandae Quia Quasi Asperiores Voluptate', 'deskripsi' => 'Delectus, iste explicabo vitae possimus maiores dolores eaque rerum ea laudantium cumque dolor autem distinctio quas odio unde commodi fugiat.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 11, 'judul' => 'Voluptas Odit Vitae Esse Odio Quidem Unde', 'deskripsi' => 'Eius fugit dignissimos laboriosam temporibus perferendis a repellat saepe, ipsum quod at, officia eum. Doloribus amet expedita veritatis cum alias.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 11, 'judul' => 'Officiis Natus Et Corporis Similique Rerum Quam', 'deskripsi' => 'Deleniti possimus tempore magnam doloribus? Quidem possimus nesciunt dicta ratione, odit sapiente reiciendis amet quam eaque fugiat laudantium et inventore.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 11, 'judul' => 'Dignissimos Commodi, Sit At Cum Quaerat Sequi', 'deskripsi' => 'Distinctio consectetur reprehenderit porro repellendus, facere sequi iste fugit id esse harum laboriosam quia inventore, asperiores recusandae maiores et eos.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 11, 'judul' => 'Animi, Exercitationem Odit! Dicta Voluptatem Laudantium Earum', 'deskripsi' => 'Doloremque, optio veniam cumque exercitationem et nesciunt laboriosam odio libero provident, itaque quia ipsam voluptate nobis. Repudiandae obcaecati quo maxime.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 12, 'judul' => 'Ipsum Quia Distinctio Laudantium, Quaerat Iure Ullam', 'deskripsi' => 'Deleniti placeat commodi quisquam earum illo nulla, sapiente doloremque assumenda recusandae ex reiciendis, enim, odio ipsa quia porro nostrum rem.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 12, 'judul' => 'Amet, Voluptatibus Officia? Eveniet Quia Ut Delectus', 'deskripsi' => 'Molestiae optio odio vel tempora! Incidunt, autem obcaecati facilis quo, a laboriosam nemo distinctio minus repellat expedita labore, rerum culpa.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 12, 'judul' => 'Iste Sunt Tenetur Dolorum Esse? Culpa, Placeat', 'deskripsi' => 'Minus tempora soluta error cupiditate at, sapiente fugit suscipit culpa nostrum asperiores rerum sint quidem earum necessitatibus! Mollitia, labore minima.', 'tgl_dipakai' => carbon('2023-03-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 12, 'judul' => 'Ex, Debitis! Architecto Quae Quas At Nemo', 'deskripsi' => 'Iure fugit odio facere eos eaque similique eveniet aut impedit, harum eligendi placeat at, quidem tenetur. Odit dolorum error ex.', 'tgl_dipakai' => carbon('2023-04-01')->addDays(rand(1, 29)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 12, 'judul' => 'Dolorem Natus, Quasi Optio Aut Voluptas Quo', 'deskripsi' => 'Doloribus reiciendis itaque voluptas autem labore error laboriosam a quam ex nihil. Aut cumque alias reprehenderit repellat maxime sunt omnis.', 'tgl_dipakai' => carbon('2023-05-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 13, 'judul' => 'Dolor Qui Iusto Eum Illum Debitis Quisquam', 'deskripsi' => 'Repellendus itaque quos, quo sunt debitis accusantium ut voluptatibus ipsa fugiat nulla optio excepturi quibusdam officiis maiores rem sit culpa.', 'tgl_dipakai' => carbon('2023-01-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 13, 'judul' => 'Sint, Eaque Architecto Eius Accusantium Adipisci Quo', 'deskripsi' => 'Officia minima ullam ipsam ipsum quos similique necessitatibus, voluptas dolorem animi doloremque ea dignissimos iste, debitis iusto architecto soluta nostrum.', 'tgl_dipakai' => carbon('2023-03-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 13, 'judul' => 'Expedita Necessitatibus Aperiam, Nihil Enim Voluptatibus Quam', 'deskripsi' => 'Perspiciatis, minus delectus nihil aperiam possimus cupiditate vel esse commodi porro, deleniti rerum minima reprehenderit fugiat corporis fugit numquam quas.', 'tgl_dipakai' => carbon('2023-05-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 14, 'judul' => 'Maiores Velit Voluptatem Aspernatur Provident Temporibus Quia', 'deskripsi' => 'Perspiciatis, voluptate! Molestiae dolorum aut cumque quibusdam dolorem quisquam, nesciunt omnis eaque ea error labore blanditiis odio quaerat doloremque accusamus.', 'tgl_dipakai' => carbon('2023-02-01')->addDays(rand(1, 27)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
            ['anggaran_bidang_id' => 14, 'judul' => 'Est Eligendi Dicta, Iure Hic Veritatis Architecto', 'deskripsi' => 'Tempore labore, unde laborum distinctio eum vitae possimus laudantium quam odit commodi enim quis illum ratione quibusdam veritatis sapiente voluptas.', 'tgl_dipakai' => carbon('2023-05-01')->addDays(rand(1, 30)), 'user_id' => '88888888', 'created_at' => now(), 'updated_at' => now()],
        ]);

        collect($dataPemakaianAnggaran)->each(function (array $value, int $key) {
            PemakaianAnggaranDetail::insert([
                ['pemakaian_anggaran_id' => $key + 1, 'keterangan' => 'Lorem ipsum dolor sit', 'nominal' => rand(1, 1_000_000), 'created_at' => now(), 'updated_at' => now()],
                ['pemakaian_anggaran_id' => $key + 1, 'keterangan' => 'Incidunt accusantium ullam qui', 'nominal' => rand(1, 1_000_000), 'created_at' => now(), 'updated_at' => now()],
                ['pemakaian_anggaran_id' => $key + 1, 'keterangan' => 'Quisquam eius temporibus modi', 'nominal' => rand(1, 1_000_000), 'created_at' => now(), 'updated_at' => now()],
                ['pemakaian_anggaran_id' => $key + 1, 'keterangan' => 'Repellendus similique sunt quibusdam', 'nominal' => rand(1, 1_000_000), 'created_at' => now(), 'updated_at' => now()],
                ['pemakaian_anggaran_id' => $key + 1, 'keterangan' => 'Iste atque aspernatur necessitatibus', 'nominal' => rand(1, 1_000_000), 'created_at' => now(), 'updated_at' => now()],
                ['pemakaian_anggaran_id' => $key + 1, 'keterangan' => 'Dolore alias eius eum', 'nominal' => rand(1, 1_000_000), 'created_at' => now(), 'updated_at' => now()],
                ['pemakaian_anggaran_id' => $key + 1, 'keterangan' => 'Modi culpa quibusdam porro', 'nominal' => rand(1, 1_000_000), 'created_at' => now(), 'updated_at' => now()],
            ]);
        });

        Schema::enableForeignKeyConstraints();
    }
}
