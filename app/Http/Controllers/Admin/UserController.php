<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\RoleController as ApiRole;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\LocaleController as ApiLocale;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class UserController extends AdminController {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Show the role list.
     *
     * @return view with list and actions
     */
    public function list(Request $request)
    {
        $class = 'user';
        $search = '';
        $userId = $request->offsetGet('user_id');
        $roleId = $request->offsetGet('role_id');
        $adminFilter = $request->offsetGet('is_admin', false);
        $organisations = $this->getOrgDropdown();
        $perPage = 6;

        $rq = Request::create('/api/listRoles', 'POST');
        $api = new ApiRole($rq);
        $result = $api->listRoles($rq)->getData();
        $roles = isset($result->roles) ? $result->roles : [];

        $params = [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        if ($adminFilter) {
            $params['criteria']['is_admin'] = $adminFilter;
        }

        if (isset($request->org)) {
            $params['criteria']['org_id'] = $request->org;
        }

        if (isset($request->role)) {
            $params['criteria']['role_id'] = $request->role;
        }

        if (isset($request->active)) {
            $params['criteria']['active'] = (bool) $request->active;
        }

        if (isset($request->approved)) {
            $params['criteria']['approved'] = (bool) $request->approved;
        }

        $rq = Request::create('/api/listUsers', 'POST', $params);
        $api = new ApiUser($rq);
        $result = $api->listUsers($rq)->getData();

        $paginationData = $this->getPaginationData(
            $result->users,
            $result->total_records,
            array_except(app('request')->input(), ['q', 'page']),
            $perPage
        );

        if ($request->has('q')) {
            $search = $request->q;

            if (empty(trim($search))) {
                return redirect(url('admin/users'));
            }

            $params = [
                'api_key'           => Auth::user()->api_key,
                'records_per_page'  => $perPage,
                'page_number'       => !empty($request->page) ? $request->page : 1,
                'criteria'          => [
                    'keywords'          => $search,
                ],
            ];

            $searchReq = Request::create('/api/searchUsers', 'POST', $params);
            $api = new ApiUser($searchReq);
            $result = $api->searchUsers($searchReq)->getData();

            $users = !empty($result->users) ? $result->users : [];
            $count = !empty($result->total_records) ? $result->total_records : 0;

            $paginationData = $this->getPaginationData(
                $users,
                $count,
                array_except(app('request')->input(), ['q', 'page']),
                $perPage
            );
        }

        if ($request->has('invite')) {
            $email = $request->offsetGet('email');

            $rq = Request::create('/inviteUser', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'data'      => [
                    'email'     => $email,
                    'generate'  => true,
                ],
            ]);
            $api = new ApiUser($rq);
            $result = $api->inviteUser($rq)->getData();

            if (!empty($result->success)) {
                $request->session()->flash('alert-success', __('custom.confirm_mail_sent'));
            } else {
                $request->session()->flash('alert-danger', __('custom.add_error'));
            }

            return back();
        }

        return view('admin/userList', [
            'class'         => $class,
            'users'         => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'roles'         => $roles,
            'isAdmin'       => Role::isAdmin(),
            'organisations' => $organisations,
            'search'        => $search,
            'adminFilter'   => $adminFilter,
        ]);
    }

    /**
     * Show the role creation.
     *
     * @return view with inpits
     */
    public function create(Request $request)
    {
        $class = 'user';
        $errors = [];
        $digestFreq = UserSetting::getDigestFreq();
        $organisations = $this->getOrgDropdown();
        $rq = Request::create('/api/listRoles', 'POST');
        $api = new ApiRole($rq);
        $result = $api->listRoles($rq)->getData();
        $roles = isset($result->roles) ? $result->roles : [];

        if ($request->isMethod('post')) {
            $params = $request->all();

            $rq = Request::create('/addUser', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'data'      => $params,
            ]);
            $api = new ApiUser($rq);
            $result = $api->addUser($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.add_success'));

                return back();
            } else {
                $request->session()->flash('alert-danger', __('custom.add_error'));

                return redirect()->back()->withInput()->withErrors($result->errors);
            }
        }

        return view('admin/userCreate', compact('class', 'digestFreq', 'organisations', 'roles'));
    }

    /**
     * Show edit role.
     *
     * @return view with inpits
     */
    public function edit(Request $request, $id)
    {
        $class = 'user';
        $digestFreq = UserSetting::getDigestFreq();

        $rq = Request::create('/api/listRoles', 'POST');
        $api = new ApiRole($rq);
        $result = $api->listRoles($rq)->getData();
        $roles = isset($result->roles) ? $result->roles : [];

        $localePost = Request::create('/api/listLocale', 'POST', ['criteria' => ['active' => true]]);
        $locale = new ApiLocale($localePost);
        $localeList = $locale->listLocale($localePost)->getData()->locale_list;

        $rq = Request::create('/api/listUsers', 'POST', [
            'api_key'   => Auth::user()->api_key,
            'criteria'  => [
                'id'        => $id,
            ],
        ]);
        $api = new ApiUser($rq);
        $result = $api->listUsers($rq)->getData();

        if ($result->success) {
            $user = $result->users[0];
            $orgRoles = [];
            $roleIds = [];

            foreach ($user->user_to_org_role as $orgRole) {
                if (!$orgRole->org_id) {
                    $orgRoles[0][] = $orgRole->role_id;
                } else {
                    $orgRoles[$orgRole->org_id][] = $orgRole->role_id;
                }
            }

            $rq = Request::create('/api/getUserSettings', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'id'        => $id,
            ]);
            $result = $api->getUserSettings($rq)->getData();
            $userSett = isset($result->user) ? $result->user->settings : [];
            $organisations = $this->getOrgDropdown($id);

            if ($request->has('remove_role')) {
                $params = [
                    'org_id'    => $request->offsetGet('org_id'),
                    'user_id'   => $id,
                ];

                $rq = Request::create('api/delMember', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $result = $api->delMember($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.delete_success'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.delete_error'));
                }
            }

            if ($request->has('save')) {
                $rolesPost = $request->offsetGet('role_id');

                $saveData = [
                    'api_key'   => $user->api_key,
                    'id'        => $user->id,
                    'data'      => [
                        'firstname'     => $request->offsetGet('firstname'),
                        'lastname'      => $request->offsetGet('lastname'),
                        'username'      => $request->offsetGet('username'),
                        'email'         => $request->offsetGet('email'),
                        'add_info'      => $request->offsetGet('add_info'),
                        'role_id'       => $rolesPost,
                        'org_id'        => $request->offsetGet('org_id'),
                        'user_settings' => [
                            'newsletter_digest' => $request->offsetGet('newsletter'),
                            'locale'            => $request->offsetGet('locale'),
                        ],
                    ],
                ];

                if ($request->offsetGet('email') && $request->offsetGet('email') !== $user->email) {
                    $request->session()->flash('alert-warning', __('custom.email_change_upon_confirm'));
                }
            }

            if ($request->has('change_pass')) {
                $oldPass = $request->offsetGet('old_password');

                if (Hash::check($oldPass, $user->password)) {
                    $saveData = [
                        'api_key'   => $user->api_key,
                        'id'        => $user->id,
                        'data'      => [
                            'password'          => $request->offsetGet('password'),
                            'password_confirm'  => $request->offsetGet('password_confirm'),
                        ],
                    ];
                } else {
                    $request->session()->flash('alert-danger', __('custom.wrong_password'));
                }
            }

            if ($request->has('generate_key')) {
                $data = [
                    'api_key'   => $user->api_key,
                    'id'        => $user->id,
                ];

                $newKey = Request::create('api/generateAPIKey', 'POST', $data);
                $api = new ApiUser($newKey);
                $result = $api->generateAPIKey($newKey)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.api_key_success'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.api_key_failure'));
                }
            }

            if ($request->has('delete')) {
                $data = [
                    'api_key'   => $user->api_key,
                    'id'        => $user->id,
                ];

                $delUser = Request::create('api/deleteUser', 'POST', $data);
                $api = new ApiUser($delUser);
                $result = $api->deleteUser($delUser)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.user_success_delete'));

                    return redirect('/');
                } else {
                    $request->session()->flash('alert-danger', __('custom.user_failure_delete'));
                }
            }

            if (!empty($saveData)) {
                $editPost = Request::create('api/editUser', 'POST', $saveData);
                $api = new ApiUser($editPost);
                $result = $api->editUser($editPost)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.changes_success_save'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.changes_success_fail'));

                    $error = $result->errors;
                }
            }

            return view('admin/userEdit', compact(
                'class',
                'user',
                'digestFreq',
                'userSett',
                'localeList',
                'organisations',
                'roles',
                'orgRoles'
            ));
        }

        return redirect(url('/admin/users'));
    }

    public function search(Request $request)
    {
        $perPage = 6;
        $search = $request->q;

        if (empty(trim($search))) {
            return redirect(url('admin/users'));
        }

        $params = [
            'api_key'           => Auth::user()->api_key,
            'records_per_page'  => $perPage,
            'page_number'       => !empty($request->page) ? $request->page : 1,
            'criteria'          => [
                'keywords'          => $search,
            ],
        ];

        $searchReq = Request::create('/api/searchUsers', 'POST', $params);
        $api = new ApiUser($searchReq);
        $result = $api->searchUsers($searchReq)->getData();

        $users = !empty($result->users) ? $result->users : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $getParams = [
            'search' => $search
        ];

        $paginationData = $this->getPaginationData($users, $count, $getParams, $perPage);

        return redirect(
            url('admin/users'),
            [
                'class'         => 'user',
                'users'         => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'q'             => $search
            ]
        );
    }
}
