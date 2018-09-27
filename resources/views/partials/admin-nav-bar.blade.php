<div class="row m-t-md">
    <div class="col-xs-12 sidenav m-b-lg">
        <span class="my-profile m-b-lg m-l-sm">{{ __('custom.admin_profile') }}</span>
    </div>
    <div class="col-xs-12">
        <div class="filter-content">
            <div class="col-md-12">
                <div class="row">
                    <ul class="nav filter-type right-border js-nav">
                        <li>
                            <a
                                class="{{ $view == 'newsfeed' ? 'active' : '' }}"
                                href="{{ url('/user') }}"
                            >{{ __('custom.notifications') }}</a>
                        </li>
                        <li>
                            <!-- if there is resource with signal -->
                            @if (!empty($hasReported))
                                <div class="col-xs-12 text-center exclamation-sign">
                                    <img src="{{ asset('img/reported.svg') }}">
                                </div>
                            @endif
                            <a
                                class="{{ $view == 'dataset' ? 'active' : '' }}"
                                href="{{ url('/admin/datasets') }}"
                            >{{ __('custom.my_data') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'group' ? 'active' : '' }}"
                                href="{{ url('/admin/groups') }}"
                            >{{ trans_choice(__('custom.groups'), 2) }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'organisation' ? 'active' : '' }}"
                                href="{{ url('/admin/organisations') }}"
                            >{{ trans_choice(__('custom.organisations'), 2) }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'users' ? 'active' : '' }}"
                                href="{{ url('/admin/users') }}"
                            >{{ trans_choice(__('custom.users'), 2) }}</a>
                        <li>
                            <a
                                class="{{ $view == 'statsAnalytics' ? 'active' : '' }}"
                                href="#"
                            >{{ __('custom.stats_analytics') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'setting' ? 'active' : '' }}"
                                href="{{ url('/user/settings') }}"
                            >{{ __('custom.settings') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'topicsSubtopics' ? 'active' : '' }}"
                                href="{{ url('/admin/themes/list') }}"
                            >{{ __('custom.topics_categories') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'sections' ? 'active' : '' }}"
                                href="{{ url('/admin/sections/list') }}"
                            >{{ __('custom.topics_sections') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'languages' ? 'active' : '' }}"
                                href="{{ url('/admin/languages') }}"
                            >{{ __('custom.languages') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'termsConditions' ? 'active' : '' }}"
                                href="{{ url('/admin/terms-of-use/list') }}"
                            >{{ ultrans('custom.terms_and_conditions') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'termsConditionsReq' ? 'active' : '' }}"
                                href="{{ url('/admin/terms-of-use-request/list') }}"
                            >{{ ultrans('custom.terms_and_conditions_req') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'action' ? 'active' : '' }}"
                                href="{{ url('/admin/history/action') }}"
                            >{{ __('custom.actions_history') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'login' ? 'active' : '' }}"
                                href="{{ url('/admin/history/login') }}"
                            >{{ __('custom.logins_history') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'signals' ? 'active' : '' }}"
                                href="{{ url('/admin/signals/list') }}"
                            >{{ __('custom.signals') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'manageRoles' ? 'active' : '' }}"
                                href="{{ url('/admin/roles') }}"
                            >{{ __('custom.manage_roles') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'documents' ? 'active' : '' }}"
                                href="{{ url('/admin/documents/list') }}"
                            >{{ ultrans('custom.documents') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'images' ? 'active' : '' }}"
                                href="{{ url('/admin/images/list') }}"
                            >{{ ultrans('custom.images') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'pages' ? 'active' : '' }}"
                                href="{{ url('/admin/pages/list') }}"
                            >{{ ultrans('custom.pages') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'news' ? 'active' : '' }}"
                                href="{{ url('/admin/news/list') }}"
                            >{{ ultrans('custom.news') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'dataRequests' ? 'active' : '' }}"
                                href="{{ url('/admin/data-requests/list') }}"
                            >{{ ultrans('custom.data_requests') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'forum' ? 'active' : '' }}"
                                href="{{ url('/admin/forum/discussions/list') }}"
                            >{{ ultrans('custom.forum') }}</a>
                        </li>
                        <li>
                            <a
                                class="{{ $view == 'help' ? 'active' : '' }}"
                                href="{{ url('/admin/help/sections/list') }}"
                            >{{ ultrans('custom.help_sections') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
