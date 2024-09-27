@props(['routes'])

<script>
    window.frontRoutes = window.frontRoutes || {}; 
    Object.assign(window.frontRoutes, {
        @foreach ($routes as $name => $route)
            {{ $name }}: "{{ $route }}",
        @endforeach
    });
</script>
