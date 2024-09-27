<?php

namespace App\View\Components\Consulta\SectionPaginacao;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Fluent;
use Illuminate\View\Component;

class componente extends Component
{
    public Fluent $dados;
    public bool $display;

    /**
     * Create a new component instance.
     */
    public function __construct(public string $sufixo, $display = null, ?Fluent $dados = null)
    {
        $this->display = $display ?? true;
        $this->dados = $dados ?? new Fluent();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.consulta.section-paginacao.componente');
    }
}
