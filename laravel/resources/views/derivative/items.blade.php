@if ($rows->isEmpty())
    @if ($filter->haveFilters)
        <p>Производные ассеты не найдены.</p>
    @else
        <p>Производных ассетов нет.</p>
    @endif
@else
    <div class="assets derivative d-flex flex-wrap justify-content-center">
        @foreach ($rows as $row)
            <div class="card">
                <a href="{{ route('derivative.show', [$row->id]) }}">
                    @include('parts.asset_sync')
                    @include('parts.asset_thumb')
                </a>
                <div class="card-body">
                    <a href="{{ route('derivative.show', [$row->id]) }}" class="card-title">
                        {{ $row->name1 }}
                    </a>
                    @include('derivative.price')
                    @include('derivative.info')
                </div>
            </div>
        @endforeach
        {!! str_repeat('<div class="card card-blank"></div>', 35) !!}
    </div>
@endif
