<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}"> 
                    Nekilnojamo turto skelbimų surinkimo informacinė sistema
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">Prisijungti</a>
                                </li>
                            @endif
                            
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">Registruotis</a>
                                </li>
                            @endif
                        @else
                            @if(count(session()->get('messages')) > 0)
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        Pranešimai
                                        @if (session()->get('unreadMsgCnt') > 9)
                                            <span class="badge badge-pill badge-danger">9+</span>
                                        @elseif (session()->get('unreadMsgCnt') == 0)
                                            <span> </span>
                                        @else
                                            <span class="badge badge-pill badge-danger">{{session()->get('unreadMsgCnt')}}</span>
                                        @endif
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown" style="height: auto;max-height: 200px; overflow-x: hidden;">
                                        <div class="text-right">
                                            <a href="/markAllMessagesRead">Pažymėti visus kaip perskaitytus. &ensp;</a>
                                        </div>
                                        <div class="dropdown-divider">
                                        </div>

                                        @foreach (session()->get('messages') as $message)
                                            <a href="/markMessageRead/{{$message['id']}}">
                                                <div class="dropdown-item">
                                                    @if ($message['read_msg'] != 1)
                                                        <p><strong>{{$message['message']}}</strong></p>
                                                    @else
                                                        <p>{{$message['message']}}</p>
                                                    @endif
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </li>
                            @endif

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    Profilis
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <div class="dropdown-item">
                                        <a href="/profileEditPage"> Profilio redagavimas </a>
                                    </div>

                                    <div class="dropdown-item">
                                        <a href="/likedListings"> Patinkantys skelbimai </a>
                                    </div>

                                    <div class="dropdown-item">
                                        <a href="/notifications"> Pranešimų nustatymai </a>
                                    </div>

                                    @if (auth()->user()->isAdmin())
                                        <div class="dropdown-item">
                                            <a href="/userList"> Naudotoju sarasas </a>
                                        </div>

                                        <div class="dropdown-item">
                                            <a href="" data-toggle="modal" data-target="#ModalCenter">
                                                Paleisti interneti vora
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                                        Atsijungti
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Modal -->
        <div class="modal fade" id="ModalCenter" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalCenterTitle">Paleisti internetini vora</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="modal-body" class="modal-body" style="display:none;">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Kraunama...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Uždaryti</button>
                        <a href="/scrapper" class="btn btn-primary" onclick="document.getElementById('modal-body').style.display = 'block';">Paleisti</a>
                    </div>
                </div>
            </div>
        </div>

        <main class="py-4">
            <div class="row justify-content-center">
                @include('layouts/flash')
            </div>

            @yield('content')
        </main>
    </div>
    @yield('chart')
    @yield('script')
</body>
</html>