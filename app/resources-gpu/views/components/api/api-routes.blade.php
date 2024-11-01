@props(['routes'])

<script>
    window.apiRoutes = window.apiRoutes || {}; 
    Object.assign(window.apiRoutes, {
        @foreach ($routes as $name => $route)
            {{ $name }}: "{{ $route }}",
        @endforeach
    });
</script>
