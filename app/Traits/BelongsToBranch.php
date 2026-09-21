<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Exception;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToBranch
{
    protected static function bootBelongsToBranch(): void
    {
        // 1. OTOMATIS FILTER DATA SAAT READ (SELECT)
        static::addGlobalScope('branch', function (Builder $builder) {
            // Gunakan auth()->guard()->check() langsung agar tidak memicu event session berulang
            if (auth()->check()) {
                $user = auth()->user();

                // Cek apakah user punya role hak akses semua cabang
                $isGlobalRole = in_array($user?->role, ['Admin', 'Owner']);

                // Jika role global, default-nya 'ALL'. Kalau role biasa (kasir/operator), default-nya branch_id user.
                $defaultBranch = $isGlobalRole ? 'ALL' : $user?->branch_id;
                
                // Ambil active_branch_id dari session via request helper
                $activeBranch = request()->hasSession() 
                    ? request()->session()->get('active_branch_id', $defaultBranch) 
                    : $defaultBranch;

                // Jika Owner pilih 'ALL', lewatkan filter
                if ($activeBranch === 'ALL') {
                    return;
                }

                if ($activeBranch) {
                    $builder->where($builder->getQuery()->from . '.branch_id', $activeBranch);
                }
            }
        });

        // 2. OTOMATIS ISI & VALIDASI KETAT SAAT CREATE (INSERT)
        static::creating(function ($model) {
            if (!$model->branch_id) {
                $user = auth()->user();
                $activeBranch = request()->hasSession() 
                    ? request()->session()->get('active_branch_id') 
                    : null;

                if ($activeBranch === 'ALL') {
                    $model->branch_id = $user?->branch_id;
                } else {
                    $model->branch_id = $activeBranch ?? $user?->branch_id;
                }
            }

            // GUARD: Jika tetap NULL / Kosong, batalkan transaksi!
            if (empty($model->branch_id)) {
                throw new Exception("Gagal menyimpan data: 'branch_id' tidak terdeteksi. Silakan tentukan cabang atau re-login.");
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }
}