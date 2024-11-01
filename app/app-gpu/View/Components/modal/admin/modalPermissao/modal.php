<?php

namespace App\View\Components\Modal\Admin\ModalPermissao;

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
        if (ModalSessionHelper::registerModal('modalPermissao', request('request_uuid'))) {
            return view('components.modal.admin.modal-permissao.modal');
        }
        return '';
    }
}
