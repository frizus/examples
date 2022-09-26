<div class="container mb-3 mt-2">
    <div class="card filter">
        <div class="card-header">
            <a href="javascript:void(0)" class="text-body" data-toggle="collapse" data-target="#filter-contents" aria-expanded="false" aria-controls="filter-contents">
                {!! $filter->countForFilter->addCount('Фильтр по производным ассетам') !!}
            </a>
        </div>
        <div class="collapse show" id="filter-contents">
            <div class="card-body">
                {!! Form::model($filter, ['route' => ['derivative.index'], 'method' => 'get']) !!}

                <div class="mb-2">
                    <div class="form-floating">
                        {!! Form::label('name', 'Название') !!}
                        {!! Form::text('name', null, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>
                <div class="form-inline">
                    <div class="form-floating mb-2 mr-2">
                        {!! Form::label('domain', 'Товары из:') !!}
                        {!! Form::select('domain', $filter->domainsList(), null, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                    <div class="form-floating mb-2">
                        {!! Form::label('source_category', 'Категория:') !!}
                        {!! Form::select('source_category', ['' => 'Все'], null, ['class' => 'form-control form-control-sm', 'disabled' => 'disabled', 'style' => 'width: 220px']) !!}
                    </div>
                </div>
                @include('parts.filter_bitrix_category')
                <div class="form-inline">
                    <div class="form-floating mb-2 mr-2">
                        {!! Form::label('remoderate', 'Нужно перемодерировать:') !!}
                        {!! Form::select('remoderate', ['' => 'Не выбрано', '1' => 'Да', '0' => 'Нет'], null, ['class' => 'form-control form-control-sm', 'disabled' => 'disabled', 'style' => 'width: 170px']) !!}
                    </div>
                    @include('parts.filter_sync')
                </div>
                <div class="form-inline">
                    <div class="form-floating mb-2">
                        {!! Form::label('active', 'Активность:') !!}
                        {!! Form::select('active', $filter->activeList(), null, ['class' => 'form-control form-control-sm']) !!}
                    </div>
                </div>

                <div class="mt-2">
                    {!! Form::submit('Применить', ['class' => 'btn btn-primary btn-sm']) !!}
                    <a href="{{ route('derivative.index') }}" class="btn btn-default btn-sm">Отмена</a>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
