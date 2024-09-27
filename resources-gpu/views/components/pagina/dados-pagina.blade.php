<figure>
    <blockquote class="blockquote">
        <p>{{ $paginaDados->nome }}</p>
    </blockquote>
    @foreach ($paginaDados->descricao as $descricao)
        @php
            $class_add = $descricao['class_add'] ?? '';
        @endphp
        <figcaption class="blockquote-footer {{ $class_add }}">
            {{ $descricao['texto'] }}
        </figcaption>
    @endforeach
</figure>
