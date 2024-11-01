@isset($display)
    @if ($display)
        @php
            $display = 'block';
        @endphp
    @else
        @php
            $display = 'none';
        @endphp
    @endif
@else
    @php
        $display = 'block';
    @endphp
@endisset

<section id="footerPagination{{ $sufixo }}" class="footerPagination" style="display: {{ $display }}">
    <div class="row">
        <nav aria-label="Navegação por páginas" class="mt-2">
            <ul class="pagination justify-content-center">
                <li class="page-item">
                    <button type="button" class="page-link disabled" aria-label="Anterior">
                        <span aria-hidden="true">&laquo; Anterior</span>
                    </button>
                </li>
                <li class="page-item">
                    <button type="button" class="page-link disabled" aria-label="Próxima">
                        <span aria-hidden="true">Próxima &raquo;</span>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
    <div class="row justify-content-around">
        <div class="col mt-2">
            <p class="mb-0">Status: <span class="queryStatus">Aguardando comando do usuário...</span></p>
        </div>
        <div class="col mt-2">
            <p class="text-end mb-0 ">Total de Registros: <span class="totalRegisters">0</span></p>
        </div>
    </div>
</section>
