@php
    $selected = null;
    $paths = [
        'moderation' => 'moderation.index',
        'exchange' => [
            'exchange_errors' => 'exchange.errors.*',
            'finished' => 'exchange.finished.*',
            'settings' => 'exchange.settings.*',
        ],
        'assets' => [
            'original' => 'original.*',
            'derivative' => 'derivative.*'
        ]
    ];
    $request = request();
    foreach ($paths as $name => $path) {
        if (is_array($path)) {
            foreach ($path as $subName => $subPath) {
                if ($request->routeIs($subPath)) {
                    $selected = [$name, $subName];
                    break 2;
                }
            }
        } else {
            if ($request->routeIs($path)) {
                $selected = [$name];
                break;
            }
        }
    }
    if (!isset($selected)) {
        $selected = [];
    }
@endphp
<div class="bg-light">
    <nav class="navbar top-menu container navbar-expand-lg navbar-light bg-light">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarItems" aria-controls="navbarItems" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarItems">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link{!! in_array('moderation', $selected, true) ? ' active' : '' !!}" href="{{ route('moderation.index') }}">Модерация <span class="badge badge-primary">250</span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle{!! in_array('exchange', $selected, true) ? ' active' : '' !!}" data-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">Обмен</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item{!! in_array('exchange_errors', $selected, true) ? ' active' : '' !!}" href="{{ route('exchange.errors.index') }}">Ошибки <span class="badge badge-danger">5</span></a>
                        <a class="dropdown-item{!! in_array('finished', $selected, true) ? ' active' : '' !!}" href="{{ route('exchange.finished.index') }}">Выполненные обмены <span class="badge badge-primary">20</span></a>
                        <div class="dropdown-item menu-item-sync-waiting">
                            <a href="#" class="link">Синхронизация</a>
                            <span class="badge badge-warning" title="Ожидает синхронизации">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-hourglass" viewBox="0 0 16 16">
                              <path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1-.5-.5zm2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256 1.011.791 1.011 1.491v.702c0 .7-.478 1.235-1.011 1.491A3.5 3.5 0 0 0 4.5 13v1h7v-1a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351v-.702c0-.7.478-1.235 1.011-1.491A3.5 3.5 0 0 0 11.5 3V2h-7z"/>
                            </svg>2
                        </span>
                            <a href="#" class="badge badge-success" title="Успешная синхронизация">1</a>
                            <a href="#" class="badge badge-danger" title="Неудавшаяся синхронизация">1</a>
                        </div>
                        <a class="dropdown-item{!! in_array('settings', $selected, true) ? ' active' : '' !!}" href="{{ route('exchange.settings.index') }}">Настройки</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle{!! in_array('assets', $selected, true) ? ' active' : '' !!}" data-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false">Ассеты</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item{!! in_array('original', $selected, true) ? ' active' : '' !!}" href="{{ route('original.index') }}">Оригинальные</a>
                        <a class="dropdown-item{!! in_array('derivative', $selected, true) ? ' active' : '' !!}" href="{{ route('derivative.index') }}">Производные</a>
                    </div>
                </li>
            </ul>
            @if (Auth::check())
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0)" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Выйти
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            @endif
        </div>
    </nav>
</div>
