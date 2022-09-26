@if ($activeReceivingDomains->isNotEmpty())
    @foreach ($activeReceivingDomains as $activeReceivingDomain)
        <div class="form-floating mb-2 mr-2">
            {!! Form::label('synced_' . $activeReceivingDomain['id'], 'Синхронизировано с ' . $activeReceivingDomain['host'] . ':') !!}
            {!! Form::select('synced_' . $activeReceivingDomain['id'], ['' => 'Не выбрано', 'success' => 'Да', 'error' => 'Ошибка', 'awaiting' => 'Ожидает синхронизации', '0' => 'Нет'], null, ['class' => 'form-control form-control-sm', 'disabled' => 'disabled', 'style' => 'width: 280px']) !!}
        </div>
    @endforeach
@endif
