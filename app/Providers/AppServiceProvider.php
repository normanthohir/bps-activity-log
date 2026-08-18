<?php

namespace App\Providers;

use App\Models\LaporanHarian;
use App\Models\Tugas;
use App\Policies\LaporanHarianPolicy;
use App\Policies\TugasPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Menghubungkan Model ke Policy-nya masing-masing.
        // Setelah ini, $this->authorize('approve', $laporan) di Controller
        // otomatis memanggil method approve() di LaporanHarianPolicy.
        Gate::policy(LaporanHarian::class, LaporanHarianPolicy::class);
        Gate::policy(Tugas::class, TugasPolicy::class);
    }
}
