<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncIngresosRc extends Command
{
    protected $signature = 'ingresosrc:sync {--fecha= : Fecha específica (YYYY-MM-DD) para sincronizar}';
    protected $description = 'Sincroniza la tabla ingresosrc con salidas (con factor) e insumos (carbonatoSodio)';

    public function handle(): int
    {
        $fechaFiltro = $this->option('fecha');

        $grupos = DB::table('salidas')
            ->where('is_deleted', 0)
            ->when($fechaFiltro, fn($q) => $q->where('fechasalida', $fechaFiltro))
            ->selectRaw('fechasalida, gruposalida, turnosalida')
            ->distinct()
            ->get();

        if ($grupos->isEmpty()) {
            $this->info('No se encontraron registros en salidas para sincronizar.');
            return self::SUCCESS;
        }

        $this->info("Sincronizando {$grupos->count()} grupo(s) fecha+grupo+turno...");

        $bar = $this->output->createProgressBar($grupos->count());
        $bar->start();

        foreach ($grupos as $g) {
            $r = DB::table('salidas')
                ->where('is_deleted', 0)
                ->where('fechasalida', $g->fechasalida)
                ->where('gruposalida', $g->gruposalida)
                ->where('turnosalida', $g->turnosalida)
                ->selectRaw('
                    SUM(metalico * COALESCE(calculablemeta, 0.97)) as salidas_metalico,
                    SUM(rejilla * COALESCE(calculablereji, 0.97)) as salidas_rejilla,
                    SUM(metalicofino * COALESCE(calculablemetafino, 0.97)) as salidas_metalicofino,
                    SUM(pastadesulfurada * COALESCE(calculablepasta, 0.97)) as salidas_pastadesulfurada,
                    SUM(pastasin * COALESCE(calculablepastasin, 0.97)) as salidas_pastasin
                ')->first();

            DB::table('ingresosrc')->updateOrInsert(
                [
                    'fecha' => $g->fechasalida,
                    'grupo' => $g->gruposalida,
                    'turno' => $g->turnosalida,
                ],
                [
                    'salidas_metalico'         => round($r->salidas_metalico ?? 0),
                    'salidas_rejilla'          => round($r->salidas_rejilla ?? 0),
                    'salidas_metalicofino'     => round($r->salidas_metalicofino ?? 0),
                    'salidas_pastadesulfurada' => round($r->salidas_pastadesulfurada ?? 0),
                    'salidas_pastasin'         => round($r->salidas_pastasin ?? 0),
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Sync insumos → carbonatoSodio
        $gruposInsumos = DB::table('insumos')
            ->where('is_deleted', 0)
            ->when($fechaFiltro, fn($q) => $q->where('fecha', $fechaFiltro))
            ->selectRaw('fecha, grupoinsumo, turnoinsumo')
            ->distinct()
            ->get();

        if ($gruposInsumos->isNotEmpty()) {
            $this->info("Sincronizando {$gruposInsumos->count()} grupo(s) de insumos...");
            $bar2 = $this->output->createProgressBar($gruposInsumos->count());
            $bar2->start();

            foreach ($gruposInsumos as $g) {
                $r = DB::table('insumos')
                    ->where('is_deleted', 0)
                    ->where('fecha', $g->fecha)
                    ->where('grupoinsumo', $g->grupoinsumo)
                    ->where('turnoinsumo', $g->turnoinsumo)
                    ->selectRaw('SUM(COALESCE(carbonatoSodio, 0)) as carbonatoSodio')
                    ->first();

                DB::table('ingresosrc')->updateOrInsert(
                    ['fecha' => $g->fecha, 'grupo' => $g->grupoinsumo, 'turno' => $g->turnoinsumo],
                    ['carbonatoSodio' => round($r->carbonatoSodio ?? 0)]
                );

                $bar2->advance();
            }

            $bar2->finish();
            $this->newLine();
        }

        $this->info('Sincronización completada correctamente.');

        return self::SUCCESS;
    }
}
