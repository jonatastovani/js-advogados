<?php

namespace App\View\Components\modal\comum\modalBuscaPessoas;

use App\Helpers\ModalSessionHelper;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Fluent;
use Illuminate\View\Component;

class modal extends Component
{
    public Fluent $dados;
    /**
     * Create a new component instance.
     */
    public function __construct(?Fluent $dados)
    {
        $this->dados = $dados ?? new Fluent();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if (ModalSessionHelper::registerModal('modalBuscaPessoas', request('request_uuid'))) {
            return view('components.modal.comum.modal-busca-pessoas.modal');
        }
        return '';
    }
}
