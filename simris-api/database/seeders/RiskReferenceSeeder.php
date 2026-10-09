<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data awal matriks risiko 5x5. NILAI INI PLACEHOLDER:
 * sesuaikan dengan pedoman manajemen risiko instansi.
 */
class RiskReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $likelihood = [
            1 => ['Sangat Jarang', '< 5%', 'Kurang dari 1x per 5 tahun'],
            2 => ['Jarang', '5% - 20%', '1x per 2-5 tahun'],
            3 => ['Mungkin', '20% - 50%', '1x per tahun'],
            4 => ['Sering', '50% - 80%', 'Beberapa kali per tahun'],
            5 => ['Sangat Sering', '> 80%', 'Hampir setiap bulan'],
        ];
        foreach ($likelihood as $level => [$nama, $persen, $frekuensi]) {
            DB::table('likelihood_levels')->updateOrInsert(
                ['level' => $level],
                [
                    'nama_level' => $nama,
                    'persentase_kemungkinan' => $persen,
                    'frekuensi_kemungkinan' => $frekuensi,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $impact = [
            1 => 'Tidak Signifikan',
            2 => 'Minor',
            3 => 'Moderat',
            4 => 'Signifikan',
            5 => 'Katastropik',
        ];
        foreach ($impact as $level => $nama) {
            DB::table('impact_levels')->updateOrInsert(
                ['level' => $level],
                ['nama_level' => $nama, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // [nama, skor_min, skor_max, warna, rekomendasi default]
        $riskLevels = [
            ['Rendah', 1, 4, '#22C55E', 'diterima'],
            ['Sedang', 5, 9, '#EAB308', 'dimitigasi'],
            ['Tinggi', 10, 16, '#F97316', 'dimitigasi'],
            ['Sangat Tinggi', 17, 25, '#EF4444', 'dihindari'],
        ];
        $levelMap = []; // nama => [id, min, max, rekomendasi]
        foreach ($riskLevels as [$nama, $min, $max, $warna, $rekomendasi]) {
            DB::table('risk_levels')->updateOrInsert(
                ['nama_level' => $nama],
                [
                    'skor_min' => $min,
                    'skor_max' => $max,
                    'warna' => $warna,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $id = DB::table('risk_levels')->where('nama_level', $nama)->value('risk_level_id');
            $levelMap[] = [$id, $min, $max, $rekomendasi];
        }

        // Matriks penerimaan risiko 5x5 (L x D -> level -> rekomendasi)
        for ($l = 1; $l <= 5; $l++) {
            for ($d = 1; $d <= 5; $d++) {
                $score = $l * $d;
                foreach ($levelMap as [$id, $min, $max, $rekomendasi]) {
                    if ($score >= $min && $score <= $max) {
                        DB::table('acceptance_criteria')->updateOrInsert(
                            ['likelihood_level' => $l, 'impact_level' => $d],
                            [
                                'risk_level_id' => $id,
                                'rekomendasi_keputusan' => $rekomendasi,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]
                        );
                        break;
                    }
                }
            }
        }
    }
}
