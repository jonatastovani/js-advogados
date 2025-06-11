<?php

namespace App\Jobs;

use App\Helpers\ServicoPagamentoLancamentoStatusHelper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AtualizarStatusLancamentosServicoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Cria uma nova instância do job.
     */
    public function __construct()
    {
        //
    }

    /**
     * Executa o job.
     */
    public function handle(): void
    {
        Log::info("Início da atualização de status dos lançamentos de serviço às " . Carbon::now()->format('d/m/Y H:i:s'));
        ServicoPagamentoLancamentoStatusHelper::processarTodosTenants();
        Log::info("Fim da atualização de status dos lançamentos de serviço às " . Carbon::now()->format('d/m/Y H:i:s'));
    }
}
