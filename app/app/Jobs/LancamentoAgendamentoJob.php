<?php

namespace App\Jobs;

use App\Helpers\LancamentoAgendamentoHelper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LancamentoAgendamentoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Início do processamento de todos os tenants às " . Carbon::parse(now())->format('d/m/Y H:i:s'));
        LancamentoAgendamentoHelper::processarTodosTenants();
        Log::info("Fim do processamento de todos os tenants às " . Carbon::parse(now())->format('d/m/Y H:i:s'));
    }
}
