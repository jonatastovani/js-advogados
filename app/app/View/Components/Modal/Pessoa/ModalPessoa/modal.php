<?php

namespace App\View\Components\Modal\Pessoa\ModalPessoa;

use App\Helpers\ModalSessionHelper;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Fluent;
use Illuminate\View\Component;

class Modal extends Component
{
    public Fluent $dados;
    /**
     * Create a new component instance.
     */
    public function __construct(?Fluent $dados = null)
    {
        $this->dados = $dados ?? new Fluent();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if (ModalSessionHelper::registerModal('modalPessoa', request('request_uuid'))) {
            return view('components.modal.pessoa.modal-pessoa.modal');
        }
        return '';
    }
}
