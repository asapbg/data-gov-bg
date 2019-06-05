<!DOCTYPE html>
<?php
    $lang = App::getLocale();
    $altLang = $lang == 'bg' ? 'en' : 'bg';
?>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (!empty($description))
        <meta name="description" content="{{ $description }}">
    @endif
    @if (!empty($keywords))
        <meta name="keywords" content="{{ $keywords }}">
    @endif
    <title>{{ !empty($title) ? $title : config('app.name') }}</title>
    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css?family=Roboto&amp;subset=cyrillic,cyrillic-ext" rel="stylesheet">
    <link rel="stylesheet" href="/css/custom.css">
    <link href="{{ asset('fonts/vendor/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/nanoscroller.css') }}" rel="stylesheet">
    <link href="{{ asset('css/summernote/summernote.css') }}" rel="stylesheet">
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <link href="{{ asset('css/summernote/summernote.css') }}" rel="stylesheet">
    <link href="{{ asset('css/colorpicker.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-clockpicker.min.css') }}" rel="stylesheet">

    @if (isset($cssPaths))
        @foreach ($cssPaths as $path)
            <link href="{{ asset($path) }}" rel="stylesheet">
        @endforeach
    @endif

    @if (isset($link))
        <link rel="alternate" type="application/rss+xml" title="{{ $organisation->name }}" href="{{ url('/datasets/'. $organisation->uri .'/rss') }}"/>
    @endif
    @if (isset($datasetLink))
        <link rel="alternate" type="application/rss+xml" title="Datasets" href="{{ url('/datasets/rss') }}"/>
    @endif
    @if (isset($newsLink))
        <link rel="alternate" type="application/rss+xml" title="News" href="{{ url('/news/rss') }}"/>
    @endif
    @yield('css')
    <!-- Global site tag (gtag.js) - Google Analytics -->
    @if (!empty(config('app.GA_TRACKING_ID')))
        <script async src="{{ 'https://www.googletagmanager.com/gtag/js?id='. config('app.GA_TRACKING_ID') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            gtag('js', new Date());

            gtag('config', '{{ config('app.GA_TRACKING_ID') }}');
        </script>
    @endif
</head>
<body class="{{ isset($class) ? 'theme-'. $class : 'theme-user' }}">
    <div id="app" class="nano" data-lang="{{ $lang }}">
        <div class="nano-content js-nano-content">
            <nav class="navbar navbar-default navbar-static-top js-head">
                <div class="container">
                    <div class="navbar-header">
                        <div class="nav-logos">
                            <a
                                href="{{ url('/') }}"
                            ><img alt="Лого на портала" src="{{ asset('img/opendata-logo-color.svg') }}"></a>
                        </div>
                        @if (!config('app.IS_TOOL'))
                            <div class="access-terms-icons">
                                <a href="{{ url('/help') }}">
                                    <img class="help-section" title="{{ __('custom.help') }}" src="{{ asset('/img/help_section.svg') }}">
                                </a>
                            </div>
                            <div class="hamburger-trigger hidden-lg hidden-md hidden-sm pull-right">
                                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#my-navbar">
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                            </div>
                        @endif
                        <div class="nav-controls text-right {{ config('app.IS_TOOL') ? null : 'hidden-xs' }} js-show-on-load">
                            @if (!config('app.IS_TOOL'))
                                @if (\Auth::check())
                                    <span class="login-link username">
                                        <a href="{{ url('/user') }}">{{ \Auth::user()->username }}  </a>
                                    </span>
                                    <span class="user-icon {{ in_array(Request::segment(1), ['user', 'admin']) ? 'active' : '' }}">
                                        <a
                                            href="{{ url('/user') }}"
                                        >
                                            @if (\Auth::user()->is_admin)
                                                <img class="admin" src="{{ asset('img/admin.svg') }}">
                                            @else
                                                <img src="{{ asset('img/user.svg') }}">
                                            @endif
                                        </a>
                                    </span>
                                    <span class="login-link">
                                        <a
                                            href="{{ url('/logout') }}"
                                            class="js-ga-event"
                                            data-ga-action="logout"
                                            data-ga-label="logout attempt"
                                            data-ga-category="users"
                                        > {{ __('custom.logout') }}</a>
                                    </span>
                                @else
                                    <span class="login-link">>
                                        <a href="{{ url('/login') }}">{{ __('custom.login') }}</a>
                                    </span>
                                @endif
                                <span class="search-input">
                                    <form action="{{ action('DataController@list') }}" class="inline-block js-ga-event">
                                        <input
                                            type="text"
                                            name="q"
                                            placeholder="{{ __('custom.search') }}"
                                            data-ga-action="search"
                                            data-ga-label="data search"
                                            data-ga-category="data"
                                        >
                                    </form>
                                </span>
                            @endif

                            <span class="trans-link">
                                <a
                                    href="{{ route('lang.switch', $altLang) }}"
                                >{{ strtoupper($altLang) }}</a>
                            </span>

                            @if (!config('app.IS_TOOL'))
                                <span class="social-icons">
                                    <a
                                        target="_blank"
                                        href="http://www.facebook.com/sharer.php?u={{ url('/') }}"
                                        class="fb"
                                    ><span class="fa fa-facebook"></span></a>
                                    <a
                                        target="_blank"
                                        href="http://twitter.com/home?status={{ url('/') }}"
                                        class="tw"
                                    ><span class="fa fa-twitter"></span></a>
                                    <a
                                        target="_blank"
                                        href="https://plus.google.com/share?url={{ url('/') }}"
                                        class="gp"
                                    ><span class="fa fa-google-plus"></span></a>
                                    <a
                                        target="_blank"
                                        href="https://www.linkedin.com/shareArticle?mini=true&url={{ url('/') }}" class="in"
                                    ><span class="fa fa-linkedin"></span></a>
                                    @if (isset($newsLink))
                                        <a
                                            target="_blank"
                                            href="{{ url('/news/rss') }}" class="in"
                                        ><span class="fa fa-rss"></span></a>
                                    @endif
                                    @if (isset($datasetLink))
                                        <a
                                            target="_blank"
                                            href="{{ url('/datasets/rss') }}" class="in"
                                        ><span class="fa fa-rss"></span></a>
                                    @endif
                                    @if (isset($link))
                                        <a
                                            target="_blank"
                                            href="{{ url('/datasets/'. $organisation->uri .'/rss') }}" class="in"
                                        ><span class="fa fa-rss"></span></a>
                                    @endif
                                </span>
                            @endif

                        </div>
                    </div>
                    <div class="collapse navbar-collapse" id="my-navbar">
                        <div class="hidden-lg hidden-md hidden-sm close-btn text-right">
                            <span><img class="js-close-navbar" src="{{ asset('img/close-btn.png') }}"></span>
                        </div>
                        <ul class="nav navbar-nav sections">
                            @if (config('app.IS_TOOL'))
                                <li class="index {{ empty(Request::segment(2)) || Request::segment(2) == 'configDbms' ? 'active' : '' }}">
                                    <a href="{{ url('/tool/configDbms') }}">{{ sprintf(__('custom.config'), __('custom.dbms')) }}</a>
                                </li>
                                <li class="index {{ Request::segment(2) == 'configFile' ? 'active' : '' }}">
                                    <a href="{{ url('/tool/configFile') }}">{{ sprintf(__('custom.config'), utrans('custom.file')) }}</a>
                                </li>
                                <li class="index {{ Request::segment(2) == 'chronology' ? 'active' : '' }}">
                                    <a href="{{ url('/tool/chronology') }}">{{ uctrans('custom.chronology') }}</a>
                                </li>
                            @else
                                <li class="index {{ Request::is('/') ? 'active' : '' }}">
                                    <a href="{{ url('/') }}">{{ uctrans('custom.home') }}</a>
                                </li>
                                <li class="data {{ Request::segment(1) == 'data' ? 'active' : '' }}">
                                    <a href="{{ url('/data') }}">{{ uctrans('custom.data') }}</a>
                                </li>
                                <li class="organisation {{ Request::segment(1) == 'organisation'  ? 'active' : '' }}">
                                    <a href="{{ url('/organisation') }}">{{ uctrans('custom.organisations', 2) }}</a>
                                </li>
                                <li class="request {{ Request::segment(1) == 'request' ? 'active' : '' }}">
                                    <a href="{{ url('/request') }}">{{ __('custom.data_requests') }}</a>
                                </li>
                                <li class="news {{ Request::segment(1) == 'news' ? 'active' : '' }}">
                                    <a href="{{ url('/news') }}">{{ __('custom.news_events') }}</a>
                                </li>
                                <li class="document {{ Request::segment(1) == 'document' ? 'active' : '' }}">
                                    <a href="{{ url('/document') }}">{{ __('custom.documents') }}</a>
                                </li>
                                @if (isset($activeSections))
                                    @foreach ($activeSections as $section)
                                        <li
                                            class="
                                                {{
                                                    isset(app('request')->input()['section'])
                                                    && app('request')->input()['section'] == $section->id
                                                        ? 'active'
                                                        : ''
                                                }}
                                                {{ isset($section->class) ? $section->class : '' }}
                                            "
                                        >
                                            <a
                                                href="{{
                                                    url(str_slug($section->name)) .
                                                    '?'.
                                                    http_build_query(['section' => $section->id])
                                                }}"
                                            >{{ $section->name }}</a>
                                        </li>
                                    @endforeach
                                @endif
                                <li
                                    class="hidden-lg hidden-md hidden-sm js-check-url {{ in_array(
                                        Request::segment(1),
                                        ['user', 'login', 'registration']
                                    ) ? 'active' : null }}"
                                >
                                    @if (!\Auth::check())
                                        <a href="{{ url('/login') }}">{{ uctrans('custom.login') }}</a>
                                    @else
                                        <a href="{{ url('/user') }}">{{ uctrans('custom.profile') }}</a>
                                    </li>
                                    <li class="hidden-lg hidden-md hidden-sm index">
                                        <a
                                            href="{{ url('/logout') }}"
                                            class="js-ga-event"
                                            data-ga-action="logout"
                                            data-ga-label="logout attempt"
                                            data-ga-category="users"
                                        >{{ uctrans('custom.logout') }}&nbsp;<i class="fa fa-sign-out"></i></a>
                                    @endif
                                </li>
                                <li class="hidden-lg hidden-md hidden-sm">
                                    <input
                                        type="text"
                                        placeholder="{{ __('custom.search') }}"
                                        class="form-control rounded-input input-long js-ga-event"
                                        data-ga-action="search"
                                        data-ga-label="data search"
                                        data-ga-category="data"
                                    >
                                </li>
                                <li class="hidden-lg hidden-md hidden-sm icons">
                                    <a
                                        href="{{ route('lang.switch', $altLang) }}"
                                    >{{ strtoupper($altLang) }}</a>
                                    <a href="#" class="fb"><i class="fa fa-facebook"></i></a>
                                    <a href="#" class="tw"><i class="fa fa-twitter"></i></a>
                                    <a href="#" class="gp"><i class="fa fa-google-plus"></i></a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="underline">
                    @if (config('app.IS_TOOL'))
                       <div class="container">
                           <a
                                class="tool-version"
                                href="https://github.com/governmentbg/data-gov-bg/releases/tag/{{ exec('git describe') }}"
                            >{{ exec('git describe') }}</a>
                       </div>
                    @else
                        <div class="help-btn js-help">
                            @if (\Auth::check() && App\Role::isAdmin() && empty($help))
                                <img class="js-open-help help-icon" src="{{ asset('/img/help-icon.svg') }}">
                                <div class="js-help-bar help-container hidden">
                                    <div class="help-content">
                                    <img class="close-help close-btn" src="{{ asset('/img/X.svg') }}">
                                        <h3>{{ __('custom.no_help') }}</h3>
                                        <a
                                            class="btn-primary btn"
                                            href="{{
                                                route('addHelpPage', ['page' => config('app.APP_URL') == \Request::url()
                                                    ? 'home'
                                                    : \Request::getPathInfo()
                                                ])
                                            }}"
                                        >{{ __('custom.add') }}</a>
                                    </div>
                                </div>
                            @else
                                <img class="js-open-help help-icon" src="{{ asset('/img/help-icon.svg') }}">
                                @include('components.help', ['help' => !empty($help) ? $help : []])
                            @endif
                        </div>
                    @endif
                </div>
            </nav>

            <div class="js-content m-b-xl">
                @yield('content')
            </div>

            <footer>
                <div class="text-center col-xs-12 m-t-xl">
                    <div class="row">
                        <div class="container">
                            <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 text-left align-top m-t-md m-l-none">
                                <a href="https://europa.eu/european-union/index_bg" target="_blank">
                                    <img
                                        alt="Официална страница на Европейския съюз"
                                        src="{{ asset('img/euro-union.svg') }}"
                                        width="150"
                                        height="100"
                                    >
                                </a>
                                <a class="m-l-r-md">
                                    <img
                                        alt="Добро управление"
                                        src="{{ asset('img/upravlenie-logo.svg') }}"
                                        width="150"
                                        height="100"
                                    >
                                </a>
                            </div>
                            <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12">
                                <h6 class="text-justify m-t-xl">
                                    Платформата за отворени данни на Република България е разработена в рамките на обществена поръчка с предмет: „Изработване, тестване
                                    и внедряване на Портал за отворени данни, разработване на инструмент за автоматизирано въвеждане на данни на портала и провеждане на свързано
                                    обучение“ в изпълнение на Проект: BG05SFOP001-2.001-0001 „Подобряване на процесите, свързани с предоставянето, достъпа и повторното използване
                                    на информацията от обществения сектор“, финансиран по Оперативна програма „Добро управление“
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @include('partials.js-translations')
    <!-- Scripts -->
    @if (isset($jsPaths))
        @foreach ($jsPaths as $path)
            <script src="{{ asset($path) }}"></script>
        @endforeach
    @endif

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/jquery.smartmenus.min.js') }}"></script>
    <script src="{{ asset('js/jquery.smartmenus.bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/jquery.nanoscroller.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-colorpicker.js') }}"></script>
    <script src="{{ asset('js/bootstrap-clockpicker.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.js"></script>

    @if (isset($script))
        {!! $script !!}
    @endif

    @yield('js')
</body>
</html>
