@foreach ([
    'bootstrap',
    'styles',
    'menu',
    'asset-thumb',
    'assets',
    'filter'
] as $name)
    <link rel="stylesheet" href="{{ asset_skip_cache('css/' . $name . '.css') }}">
@endforeach
