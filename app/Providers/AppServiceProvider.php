<?php

namespace App\Providers;

use App\Models\LaporanHarian;
use App\Models\Tugas;
use App\Policies\LaporanHarianPolicy;
use App\Policies\TugasPolicy;
use App\Support\NotifikasiAnggota;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

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
        View::composer('layouts.topbar', function ($view) {
            $view->with('notifAnggota', auth()->check() ? NotifikasiAnggota::untuk(auth()->user()) : null);
        });

        Gate::policy(LaporanHarian::class, LaporanHarianPolicy::class);
        Gate::policy(Tugas::class, TugasPolicy::class);
        Paginator::defaultView('vendor.pagination.tailwind');
    }
}
