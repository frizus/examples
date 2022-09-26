@if (isset($row->price))
    <div class="price">
        {!! format_price($row->price) !!}
        @if (isset($row->old_price))
            <s>{!! format_price($row->old_price) !!}</s>
        @endif
    </div>
@endif
