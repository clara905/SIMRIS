<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\RiskAssessment;
use App\Models\RiskLevel;
use App\Models\RiskTreatment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /** Status laporan yang sedang menunggu keputusan (alur approval dibangun di Step 5). */
    private const REVIEW_STATUSES = ['diajukan', 'review_kasubbid', 'review_kabid', 'review_kasatker'];

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $perLevel = RiskAssessment::visibleTo($user)
            ->selectRaw('risk_level_id, COUNT(*) AS jumlah')
            ->groupBy('risk_level_id')
            ->pluck('jumlah', 'risk_level_id');

        return response()->json(['data' => [
            'totals' => [
                'aset'              => Asset::visibleTo($user)->count(),
                'risiko'            => RiskAssessment::visibleTo($user)->count(),
                'menunggu_approval' => RiskAssessment::visibleTo($user)->whereIn('status', self::REVIEW_STATUSES)->count(),
                'mitigasi_berjalan' => RiskTreatment::where('status', 'berjalan')
                    ->whereHas('assessment', fn ($q) => $q->visibleTo($user))
                    ->count(),
            ],
            'distribusi_risiko' => RiskLevel::orderBy('skor_min')->get()->map(fn ($l) => [
                'level'  => $l->nama_level,
                'warna'  => $l->warna,
                'jumlah' => (int) ($perLevel[$l->risk_level_id] ?? 0),
            ])->values(),
            'perlu_tindakan' => $this->pending($user),
        ]]);
    }

    public function staf(Request $request): JsonResponse
    {
        $user = $request->user();
        $own = fn () => RiskAssessment::where('users_id', $user->users_id);

        $tasks = RiskTreatment::where('penanggung_jawab', $user->users_id)
            ->whereIn('status', ['direncanakan', 'berjalan'])
            ->orderBy('target_date')
            ->limit(5)
            ->get();

        return response()->json(['data' => [
            'totals' => [
                'aset'           => Asset::visibleTo($user)->count(),
                'draft'          => $own()->where('status', 'draft')->count(),
                'diajukan'       => $own()->whereIn('status', self::REVIEW_STATUSES)->count(),
                'perlu_revisi'   => $own()->where('status', 'revisi')->count(),
                'mitigasi_aktif' => RiskTreatment::where('penanggung_jawab', $user->users_id)
                    ->where('status', 'berjalan')->count(),
            ],
            'perlu_revisi' => $own()->with(['riskLevel', 'pembuat'])
                ->where('status', 'revisi')
                ->latest('risk_assessment_id')->limit(10)->get()
                ->map(fn ($r) => $this->row($r))->values(),
            'tugas_mitigasi' => $tasks->map(fn ($t) => [
                'risk_treatments_id' => $t->risk_treatments_id,
                'rencana_mitigasi'   => $t->rencana_mitigasi,
                'target_date'        => $t->target_date?->format('Y-m-d'),
                'status'             => $t->status,
            ])->values(),
        ]]);
    }

    /** Laporan yang menunggu keputusan sesuai jenjang role. */
    private function pending(User $user): Collection
    {
        $statuses = match ($user->primaryRole()) {
            'kasubbid' => ['review_kasubbid'],
            'kabid'    => ['review_kabid'],
            'kasatker' => ['review_kasatker'],
            default    => ['review_kasubbid', 'review_kabid', 'review_kasatker'],
        };

        return RiskAssessment::visibleTo($user)
            ->with(['riskLevel', 'pembuat'])
            ->whereIn('status', $statuses)
            ->latest('risk_assessment_id')
            ->limit(20)
            ->get()
            ->map(fn ($r) => $this->row($r))
            ->values();
    }

    private function row(RiskAssessment $r): array
    {
        return [
            'risk_assessment_id' => $r->risk_assessment_id,
            'kode_risiko'        => $r->kode_risiko,
            'deskripsi_risiko'   => $r->deskripsi_risiko,
            'level'              => $r->riskLevel?->nama_level ?? '-',
            'warna'              => $r->riskLevel?->warna ?? '#94A3B8',
            'status'             => $r->status,
            'oleh'               => $r->pembuat?->name ?? '-',
        ];
    }
}