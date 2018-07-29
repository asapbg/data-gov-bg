@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-xs-12 p-l-r-none">
        <div class="row">
            @include('partials.sidebar')
            <div class="col-sm-9 col-xs-11 page-content p-sm">
                <div class="filter-content">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-xs-12 p-l-none">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        <li><a class="active p-l-none" href="{{ url('/data') }}">данни</a></li>
                                        <li><a href="{{ url('/data/relatedData') }}">свързани данни</a></li>
                                        <li><a href="{{ url('/data/reportedList') }}">сигнализирани данни</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="articles">
                    <div class="article m-b-md">
                        <div>
                            <div class="col-sm-7 col-xs-12 p-l-r-none">
                                <div class="pull-left">
                                    <h2>Автор: Иван Иванов</h2>
                                </div>
                            </div>
                            <div class="col-sm-5 col-xs-12">
                                <div class="follow m-t-md m-b-sm p-w-sm pull-right">
                                    <span class="badge badge-pill"><a href="#">Последване</a></span>
                                    <!-- if it is aleready followed -->
                                    <!--<span class="badge badge-pill"><a href="#">Прекрати следването</a></span>-->
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="col-sm-7 col-xs-12 p-l-none">
                                <ul class="p-l-none">
                                    <li>Отговорник подръжка:</li>
                                    <li>Версия:</li>
                                    <li>Последен ъпдейт:</li>
                                    <li>Създаден:</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-12 p-l-none art-heading-bar">
                            <div class="socialPadding">
                                <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                                <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                                <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                            </div>
                            <div class="sendMail p-w-sm">
                                <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                            </div>
                            <div class="status p-w-sm">
                                <span>одобрен</span>
                            </div>
                        </div>

                        <div class="col-sm-12 p-l-none">
                            <h2>Lorem ipsum dolor sit amet</h2>
                            <p>
                                Pellentesque risus nisl, hendrerit eget tellus sit amet,
                                ornare blandit nisi. Morbi consectetur, felis in semper euismod,
                                mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.
                                Ut euismod nibh at ante dapibus, sit amet pharetra lectus blandit.
                                Aliquam eget orci tellus. Aliquam quis dignissim lectus, non dictum purus.
                                Pellentesque scelerisque quis enim at varius. Duis a ex faucibus urna volutpat
                                varius ac quis mauris. Sed porttitor cursus metus, molestie ullamcorper dolor
                                auctor sed. Praesent dictum posuere tellus, vitae eleifend dui ornare et.
                                Donec eu ornare eros. Cras eget velit et ex viverra facilisis eget nec lacus.
                            </p>
                            <div class="col-sm-12 p-l-none">
                                <div class="tags pull-left">
                                    <span class="badge badge-pill">ТАГ</span>
                                    <span class="badge badge-pill">ДЪЛЪГ ТАГ</span>
                                    <span class="badge badge-pill">ТАГ</span>
                                </div>
                            </div>

                            <!-- chart goes here -->
                            <div class="col-sm-12 pull-left m-t-md p-l-r-none">
                                <img class="img-responsive" src="{{ asset('img/test-img/bar-chart.jpg') }}">
                            </div>
                            <div class="col-xs-12 pull-left m-t-md p-l-r-none">
                                <div class="col-md-6 col-xs-12 text-left p-l-r-none m-b-md">
                                    <div class="badge-info m-r-md pull-left">
                                        <span class="badge badge-pill js-toggle-info-box m-b-sm">информация</span>
                                        <div class="info-box">
                                            <p>
                                                Ред<br>
                                                от ... &nbsp; до ...
                                            </p>
                                            <p>
                                                Колона<br>
                                                от ... &nbsp; до ...
                                            </p>
                                        </div>
                                    </div>
                                    <div class="badge-info m-r-md">
                                        <span class="badge badge-pill js-toggle-info-box m-b-sm">покажи като</span>
                                        <div class="info-box">
                                            <p>lorem ipsum</p>
                                            <p>lorem ipsum</p>
                                            <p>lorem ipsum</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xs-12 text-right p-l-r-none m-b-md group-three">
                                    <span class="badge badge-pill m-b-sm"><a href="#">изтегляне</a></span>
                                    <span class="badge badge-pill m-b-sm"><a href="#">сигнал</a></span>
                                    <span class="badge badge-pill m-b-sm"><a href="#">коментар</a></span>
                                </div>
                            </div>
                            <!-- IF there are old versions of this article -->
                            <div class="col-sm-12 pull-left m-t-md p-l-none">
                                <div class="pull-left history">
                                    <div>
                                        <a href="#">
                                            <span class="version-heading">Заглавие</span>
                                            <span class="version">&nbsp;&#8211;&nbsp;версия 3</span>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="#">
                                            <span class="version-heading">Заглавие</span>
                                            <span class="version">&nbsp;&#8211;&nbsp;версия 2</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- IF there are commnets -->
                            <div class="col-sm-12 pull-left m-t-md p-l-none">
                                <div class="comments p-lg">
                                    @for ($i=0; $i<3; $i++)
                                        <div class="comment-box p-lg m-b-lg">
                                            <img class="img-rounded coment-avatar" src="{{ asset('img/test-img/avatar.png') }}"/>
                                            <p class="comment-author p-b-xs">Име на профила</p>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                                                sed do eiusmod tempor incididunt ut labore et dolore magna
                                                aliqua. Ut enim ad minim veniam, quis nostrud exercitation
                                                ullamco laboris nisi ut aliquip ex ea commodo consequat.
                                            </p>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
