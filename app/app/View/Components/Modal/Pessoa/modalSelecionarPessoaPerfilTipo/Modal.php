<?php

namespace App\View\Components\Modal\Servico\ModalSelecionarPessoaPerfilTipo;

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
        if (ModalSessionHelper::registerModal('modalSelecionarPessoaPerfilTipo', request('request_uuid'))) {
            return view('components.modal.pessoa.modal-selecionar-pessoa-perfil-tipo.modal');
        }
        return '';
    }
}
