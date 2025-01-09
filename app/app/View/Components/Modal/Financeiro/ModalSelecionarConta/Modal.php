<?php

namespace App\View\Components\Modal\Servico\ModalSelecionarConta;

use App\Helpers\ModalSessionHelper;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if (ModalSessionHelper::registerModal('modalSelecionarConta', request('request_uuid'))) {
            return view('components.modal.financeiro.modal-selecionar-conta.modal');
        }
        return '';
    }
}
