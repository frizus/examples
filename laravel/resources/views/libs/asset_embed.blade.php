@once
    @prepend('head')
        <link rel="stylesheet" href="{{ asset_skip_cache('css/asset-embed.css') }}">
    @endprepend
@endonce
