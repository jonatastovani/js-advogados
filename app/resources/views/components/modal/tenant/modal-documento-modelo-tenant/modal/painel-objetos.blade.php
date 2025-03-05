<div class="d-flex flex-column h-100">
    {{-- <div class="row flex-row-reverse g-2 mt-2">
        <div class="col-6 col-sm-4 col-md-3 col-xl-2 text-end mt-2">
            <label for="dataDocumento{{ $sufixo }}" class="form-label">Data</label>
            <input type="date" class="form-control text-end" id="dataDocumento{{ $sufixo }}">
        </div>
    </div> --}}
    <div id="rowObjetosSistema{{ $sufixo }}" class="row flex-row-reverse g-2 mt-2">
    </div>
    <h5 class="mt-2">Objetos disponíveis</h5>

    <div id="divObjetos{{ $sufixo }}" class="row row-cols-1 row-cols-md-2 row-cols-xxl-3 g-2"></div>
</div>
