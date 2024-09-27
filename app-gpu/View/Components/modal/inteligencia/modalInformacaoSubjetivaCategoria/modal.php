<?php

namespace App\View\Components\modal\inteligencia\modalInformacaoSubjetivaCategoria;

use App\Helpers\ModalSessionHelper;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class modal extends Component
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
        if (ModalSessionHelper::registerModal('modalInformacaoSubjetivaCategoria', request('request_uuid'))) {
            return view('components.modal.inteligencia.modal-informacao-subjetiva-categoria.modal');
        }
        return '';
    }
}
