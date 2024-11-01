<?php

namespace App\View\Components\Consulta\FormularioPadraoCriterio;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Fluent;
use Illuminate\View\Component;

class Componente extends Component
{
    public Fluent $dados;

    /**
     * Create a new component instance.
     */
    public function __construct(public string $sufixo, ?Fluent $dados = null)
    {
        $this->dados = $dados ?? new Fluent();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.consulta.formulario-padrao-criterio.componente');
    }
}
