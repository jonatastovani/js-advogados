<?php

namespace App\View\Components\modal\comum\modalLoading;

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
        if (ModalSessionHelper::registerModal('modalLoading', request('request_uuid'))) {
            return view('components.modal.comum.modal-loading.modal');
        }
        return '';
    }
}
