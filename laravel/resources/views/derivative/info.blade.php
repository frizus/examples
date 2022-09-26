<div class="card-text info">
    @include('parts.assets_source')
    @if ($row->assetOriginal->moderate)
        Нужна повторная модерация<br>
    @endif
    Изменение {{ display_datetime($row->derivative_updated_at) }}
</div>
