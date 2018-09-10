<?php

namespace App\Http\Controllers\Api;

use App\Role;
use App\User;
use App\Locale;
use App\Module;
use PDOException;
use App\RoleRight;
use App\UserSetting;
use App\Organisation;
use App\UserToOrgRole;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
Use Uuid;

class UserController extends ApiController
{
    /**
     * List user records by given criteria
     *
     * @param string api_key - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     * @param array criteria - optional
     * @param string criteria[keywords] - optional
     * @param integer criteria[active] - optional
     * @param integer criteria[is_admin] - optional
     * @param integer criteria[org_id] - optional
     * @param integer criteria[role_id] - optional
     * @param integer criteria[id] - optional
     * @param array criteria[user_ids] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     *
     * @return json response with list of users or error
     */
    public function listUsers(Request $request)
    {
        $result = [];
        $data = $request->all();

        $validator = \Validator::make($data, [
            'records_per_page'      => 'nullable|int|digits_between:1,10',
            'page_number'           => 'nullable|int|digits_between:1,10',
            'criteria'              => 'nullable|array',
        ]);

        $criteria = isset($data['criteria']) ? $data['criteria'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($criteria, [
                'active'       => 'nullable|boolean',
                'approved'     => 'nullable|boolean',
                'is_admin'     => 'nullable|int|digits_between:1,10',
                'role_id'      => 'nullable',
                'org_id'       => 'nullable',
                'id'           => 'nullable|int|digits_between:1,10',
                'user_ids'     => 'nullable|array',
                'order'        => 'nullable|array',
                'keywords'     => 'nullable|string|max:191'
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $query = User::select()->with('userToOrgRole');

            if (isset($criteria['keywords'])) {
                $ids = User::search($criteria['keywords'])->get()->pluck('id');
                $query = User::whereIn('id', $ids)->with('userToOrgRole');
            }

            if (isset($criteria['active'])) {
                $query->where('active', $criteria['active']);
            }

            if (isset($criteria['approved'])) {
                $query->where('approved', $criteria['approved']);
            }

            if (!empty($criteria['is_admin'])) {
                $query->where('is_admin', $criteria['is_admin']);
            }

            if (!empty($criteria['role_ids'])) {
                $query->whereHas('userToOrgRole', function($q) use($criteria) {
                    if (is_array($criteria['role_ids'])) {
                        $q->whereIn('role_id', $criteria['role_ids']);
                    } else {
                        $q->where('role_id', $criteria['role_ids']);
                    }
                });
            }

            if (!empty($criteria['org_ids'])) {
                $query->whereHas('userToOrgRole', function($q) use($criteria) {
                    if (is_array($criteria['org_ids'])) {
                        $q->whereIn('org_id', $criteria['org_ids']);
                    } else {
                        $q->where('org_id', $criteria['org_ids']);
                    }
                });
            }

            if (!empty($criteria['id'])) {
                $query->where('id', $criteria['id']);
            } elseif (isset($criteria['user_ids'])) {
                $query->whereIn('id', $criteria['user_ids']);
            }

            $count = $query->count();

            if (isset($criteria['order']['field']) && isset($criteria['order']['type'])) {
                $query->orderBy($criteria['order']['field'], $criteria['order']['type']);
            }

            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            try {
                $users = $query->get();

                if (Auth::user() !== null) {
                    $logData = [
                        'module_name'      => Module::getModuleName(Module::USERS),
                        'action'           => ActionsHistory::TYPE_SEE,
                        'action_msg'       => 'Listed users',
                    ];

                    Module::add($logData);
                }

                return $this->successResponse(['users'=> $users, 'total_records' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_users_fail'), $validator->errors()->messages());
    }

    /**
     * Search in user records by given keywords
     *
     * @param string api_key - required
     * @param array criteria - required
     * @param string criteria[keywords] - required
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with found users or error
     */
    public function searchUsers(Request $request)
    {
        $search = $request->all();

        $validator = \Validator::make($search, [
            'records_per_page'      => 'nullable|int|digits_between:1,10',
            'page_number'           => 'nullable|int|digits_between:1,10',
            'criteria'              => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($search['criteria'], [
                'keywords'     => 'required|string|max:191',
                'order'        => 'nullable|array',
            ]);
        }

        $order = isset($search['criteria']['order']) ? $search['criteria']['order'] :[];

        if (!$validator->fails()) {
            $validator = \Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $ids = User::search($search['criteria']['keywords'])->get()->pluck('id');
            $query = User::whereIn('id', $ids)->with('userToOrgRole');

            $count = $query->count();

            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            try {
                $data = $query->get();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::USERS),
                    'action'           => ActionsHistory::TYPE_SEE,
                    'action_msg'       => 'Searched users',
                ];

                Module::add($logData);

                return $this->successResponse(['users' => $data, 'total_records' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.search_users_fail'), $validator->errors()->messages());
    }

    /**
     * Get user roles and organisations by given user id
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json response with roles or error
     */
    public function getUserRoles(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['id' => 'required|int|digits_between:1,10']);

        if (!$validator->fails()) {
            $user = User::find($post['id']);

            if ($user) {
                $result = [];

                foreach($user->userToOrgRole as $role) {

                    $roleRights = RoleRight::where('role_id', $role->role_id)->get();

                    $rightResult = [];

                    foreach($roleRights as $singleRoleRight) {
                        $rightResult[] = [
                            'module_name'          => $singleRoleRight->module_name,
                            'right'                => $singleRoleRight->right,
                            'limit_to_own_data'    => $singleRoleRight->limit_to_own_data,
                            'api'                  => $singleRoleRight->api
                        ];
                    }

                    $result[] = [
                        'org_id'    => $role->org_id,
                        'role_id'   => $role->role_id,
                        'rights'    => $rightResult

                    ];

                    unset($roleRights);
                    unset($rightResult);
                }

                $logData = [
                    'module_name'      => Module::getModuleName(Module::USERS),
                    'action'           => ActionsHistory::TYPE_SEE,
                    'action_object'    => $post['id'],
                    'action_msg'       => 'Got user roles',
                ];

                Module::add($logData);

                return $this->successResponse(['roles' => $result]);
            }
        }

        return $this->errorResponse(__('custom.get_user_role_fail'), $validator->errors()->messages());
    }

    /**
     * Get user settings by given user id
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json response with settings or error
     */
    public function getUserSettings(Request $request)
    {
        $result = [];

        $validator = \Validator::make($request->all(), ['id' => 'required|int|digits_between:1,10']);

        if (!$validator->fails()) {
            $user = User::with('userSetting', 'follow')->find($request->id);

            if (!empty($user)) {
                $result['settings'] = [
                    'locale'            => !empty($user['userSetting']) ? $user['userSetting']['locale'] : null,
                    'newsletter_digest' => !empty($user['userSetting']) ? $user['userSetting']['newsletter_digest'] : null,
                    'created_at'        => $user['created_at'],
                    'updated_at'        => $user['updated_at'],
                    'created_by'        => $user['created_by'],
                    'updated_by'        => $user['updated_by'],
                ];

                $result['follows'] = [];

                if (!empty($user['follow'])) {
                    foreach ($user['follow'] as $follow) {
                        $result['follows'][] = [
                            'news'        => $follow['news'],
                            'org_id'      => $follow['org_id'],
                            'group_id'      => $follow['group_id'],
                            'dataset_id'  => $follow['data_set_id'],
                            'category_id' => $follow['category_id'],
                            'tag_id' => $follow['tag_id'],
                            'follow_user_id' => $follow['follow_user_id'],
                        ];
                    }
                }
            }

            $logData = [
                'module_name'      => Module::getModuleName(Module::USERS),
                'action'           => ActionsHistory::TYPE_SEE,
                'action_object'    => $request->id,
                'action_msg'       => 'Got user settings',
            ];

            Module::add($logData);

            return $this->successResponse(['user' => $result], true);
        }

        return $this->errorResponse(__('custom.get_user_settings_fail'), $validator->errors()->messages());
    }

    /**
     * Add new user record
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[firstname] - required
     * @param string data[lastname] - required
     * @param string data[email] - required
     * @param string data[add_info] - optional
     * @param string data[username] - optional
     * @param string data[password] - required
     * @param string data[password_confirm] - required
     * @param integer data[role_id] - optional
     * @param array data[user_settings] - optional
     * @param string data[user_settings][locale] - optional
     * @param integer data[user_settings][newsletter_digest] - optional
     *
     * @return json $response - response with status and api key if successful
     */
    public function addUser(Request $request)
    {
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'firstname'         => 'required|string',
            'lastname'          => 'required|string',
            'username'          => 'nullable|string|unique:users,username,NULL,id,deleted_at,NULL',
            'email'             => 'required|email',
            'is_admin'          => 'nullable|bool',
            'active'            => 'nullable|bool',
            'approved'          => 'nullable|bool',
            'password'          => 'required|string|min:6',
            'password_confirm'  => 'required|string|same:password',
            'role_id'           => 'nullable',
            'org_id'            => 'nullable',
        ]);

        if (!$validator->fails()) {
            try {
                DB::beginTransaction();

                $apiKey = Uuid::generate(4)->string;
                $user = new User;

                $user->username = !empty($request->data['username'])
                    ? $request->data['username']
                    : $this->generateUsername($request->data['email']);
                $user->password = bcrypt($request->data['password']);
                $user->email = $request->data['email'];
                $user->firstname = $request->data['firstname'];
                $user->lastname = $request->data['lastname'];
                $user->add_info = !empty($request->data['add_info'])
                    ? $request->data['add_info']
                    : null;
                $user->is_admin = !empty($request->data['is_admin']) && Role::isAdmin()
                    ? $request->data['is_admin']
                    : false;
                $user->active = !empty($request->data['active']) ? $request->data['active'] : false;
                $user->approved = !empty($request->data['invite']) ? 1 : false;
                $user->api_key = $apiKey;
                $user->hash_id = str_replace('-', '', Uuid::generate(4)->string);
                $user->remember_token = null;

                $registered = $user->save();

                if (!$user->active) {
                    $mailData = [
                        'user'  => $user->firstname,
                        'hash'  => $user->hash_id,
                        'id'    => $user->id,
                    ];

                    Mail::send('mail/confirmationMail', $mailData, function ($m) use ($user) {
                        $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                        $m->to($user->email, $user->firstname);
                        $m->subject(__('custom.register_subject'));
                    });
                }

                if (isset($data['role_id']) || isset($data['org_id'])) {
                    foreach ($data['role_id'] as $role) {
                        $userToOrgRole = new UserToOrgRole;
                        $userToOrgRole->user_id = $user->id;
                        $userToOrgRole->org_id = !empty($data['org_id']) ? $data['org_id'] : null;
                        $userToOrgRole->role_id = $role;

                        $userToOrgRole->save();
                    }
                }

                $userSettings = new UserSetting;

                $userLocale = !empty($data['user_settings']['locale'])
                    && !empty(Locale::where('locale', $data['user_settings']['locale'])->value('locale'))
                    ? $data['user_settings']['locale']
                    : config('app.locale');

                $userSettings->user_id = $user->id;
                $userSettings->locale = $userLocale;

                if (
                    isset($data['user_settings']['newsletter_digest'])
                    && is_numeric($data['user_settings']['newsletter_digest'])
                ) {
                    $userSettings->newsletter_digest = $data['user_settings']['newsletter_digest'];
                }

                $userSettings->save();

                DB::commit();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::USERS),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $user->id,
                    'action_msg'       => 'Added user',
                ];

                Module::add($logData);

                return $this->successResponse(['api_key' => $apiKey], true);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            } catch (\Swift_TransportException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());

                $validator->errors()->add('email', __('custom.send_mail_failed'));
            }
        }

        return $this->errorResponse(__('custom.add_user_fail'), $validator->errors()->messages());
    }

    /**
     * Edit existing user record
     *
     * @param string api_key - required
     * @param integer id - required
     * @param array data - required
     * @param string data[firstname] - optional
     * @param string data[lastname] - optional
     * @param string data[email] - optional
     * @param string data[add_info] - optional
     * @param string data[username] - optional
     * @param string data[password] - optional
     * @param string data[password_confirm] - optional
     * @param integer data[role_id] - optional
     * @param integer data[is_admin] - optional
     * @param array data[user_settings] - optional
     * @param string data[user_settings][locale] - optional
     * @param integer data[user_settings][newsletter_digest] - optional
     *
     * @return json $response - response with status and api key if successful
     */
    public function editUser(Request $request)
    {
        $data = $request->data;
        $id = $request->id;
        $post = $request->all();

        $validator = \Validator::make($post, [
            'id'                    => 'required|int|digits_between:1,10',
            'data'                  => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($post['data'], [
                'firstname'         => 'nullable|string|max:100',
                'lastname'          => 'nullable|string|max:100',
                'email'             => 'nullable|email|max:191',
                'add_info'          => 'nullable|string|max:8000',
                'password'          => 'nullable|string',
                'is_admin'          => 'nullable|bool',
                'active'            => 'nullable|bool',
                'aproved'           => 'nullable|bool',
                'password_confirm'  => 'nullable|string|same:password',
            ]);
        }

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.edit_user_fail'), $validator->errors()->messages());
        }

        if (empty($user = User::find($request->id))) {
            return $this->errorResponse(__('custom.edit_user_fail'));
        }

        $newUserData = [];

        if (!empty($data['firstname'])) {
            $newUserData['firstname'] = $data['firstname'];
        }

        if (!empty($data['lastname'])) {
            $newUserData['lastname'] = $data['lastname'];
        }

        if (!empty($data['email']) && $data['email'] !== $user->email) {
            $newUserData['hash_id'] = str_replace('-', '', Uuid::generate(4)->string);

            $mailData = [
                'user'  => $user->firstname,
                'hash'  => $newUserData['hash_id'],
                'mail'  => $data['email'],
                'id'    => $id,
            ];

            Mail::send('mail/emailChangeMail', $mailData, function ($m) use ($data) {
                $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                $m->to($data['email'], $data['firstname']);
                $m->subject(__('custom.email_change_header'));
            });

            if (count(Mail::failures()) > 0) {
                return $this->errorResponse(__('custom.failed_send_mail'));
            }
        }

        if (!empty($data['add_info'])) {
            $newUserData['add_info'] = $data['add_info'];
        }

        if (!empty($data['username'])) {
            $newUserData['username'] = $data['username'];
        }

        if (!empty($data['password'])) {
            $validator = \Validator::make($request->all(), [
                'data.password_confirm'  => 'required|same:data.password',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(__('custom.edit_user_fail'), $validator->errors()->messages());
            }

            $newUserData['password'] = bcrypt($data['password']);
        }

        if (isset($data['is_admin'])) {
            $newUserData['is_admin'] = (int) $data['is_admin'];
        }

        if (isset($data['active'])) {
            $newUserData['active'] = (int) $data['active'];
        }

        if (isset($data['approved'])) {
            $newUserData['approved'] = (int) $data['approved'];
        }

        $orgAndRoles = [];
        $newSettings = [];

        if (isset($data['user_settings']['locale'])) {
            $newSettings['locale'] = $data['user_settings']['locale'];
        }

        if (isset($data['user_settings']['newsletter_digest'])) {
            $newSettings['newsletter_digest'] = (int) $data['user_settings']['newsletter_digest'];
        }

        if (empty($newUserData) && empty($newSettings) && empty($orgAndRoles)) {
            return $this->errorResponse(__('custom.edit_user_fail'));
        }

        if (!empty($newUserData)) {
            $newUserData['updated_by'] = Auth::id();

            try {
                User::where('id', $request->id)->update($newUserData);
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse(__('custom.edit_user_fail'));
            }
        }

        if (isset($data['role_id']) || isset($data['org_id'])) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'data.role_id' => 'required',
                    'data.org_id'  => 'nullable',
                ]
            );

            if ($validator->fails()) {

                return $this->errorResponse(__('custom.edit_user_fail'));
            }

            try {
                UserToOrgRole::where('user_id', $request->id)->delete();

                foreach ($data['role_id'] as $org => $role) {
                    if (!empty($role)) {
                        foreach ($role as $id) {
                            $orgAndRoles['user_id'] = $request->id;
                            $orgAndRoles['org_id'] = $org != 0 ? $org : null;
                            $orgAndRoles['role_id'] = $id;

                            UserToOrgRole::create($orgAndRoles);
                        }
                    }
                }
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse(__('custom.edit_user_fail'));
            }
        }

        if (!empty($newSettings)) {
            try {
                UserSetting::updateOrCreate(['user_id' => $request->id], $newSettings);
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse(__('custom.edit_user_fail'));
            }
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::USERS),
            'action'           => ActionsHistory::TYPE_MOD,
            'action_object'    => $user->id,
            'action_msg'       => 'Edited user',
        ];

        Module::add($logData);

        return $this->successResponse(['api_key' => $user['api_key']], true);
    }

    /**
     * Delete existing user record
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json $response - response with status
     */
    public function deleteUser(Request $request)
    {
        $id = $request->id;

        $validator = \Validator::make(
            $request->all(),
            [
                'id' => 'required|digits_between:1,10',
            ]
        );

        if ($validator->fails()) {

            return $this->errorResponse(__('custom.delete_user_fail'), $validator->errors()->messages());
        }

        if (empty($user = User::find($request->id))) {
            return $this->errorResponse(__('custom.delete_user_fail'));
        }

        try {
            $user->delete();
        } catch (QueryException $e) {

            return $this->errorResponse(__('custom.delete_user_fail'));
        }

        try {
            $user->deleted_by = Auth::id();
            $user->save();
        } catch (QueryException $e) {
            Log::error($e->getMessage());

            return $this->errorResponse(__('custom.delete_user_fail'));
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::USERS),
            'action'           => ActionsHistory::TYPE_DEL,
            'action_object'    => $id,
            'action_msg'       => 'Deleted user',
        ];

        Module::add($logData);

        return $this->successResponse();
    }

    /**
     * Generate new api key for existing user
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json $response - response with status
     */
    public function generateAPIKey(Request $request)
    {
        $validator = \Validator::make($request->all(), ['id' => 'required|int|digits_between:1,10']);

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.generate_api_key_fail'), $validator->errors()->messages());
        }

        if (empty($user = User::find($request->id))) {
            return $this->errorResponse(__('custom.generate_api_key_fail'));
        }

        try {
            $user->api_key = Uuid::generate(4)->string;
            $user->updated_by = Auth::id();
            $user->save();
        } catch (QueryException $e) {
            Log::error($e->getMessage());

            return $this->errorResponse(__('custom.generate_api_key_fail'));
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::USERS),
            'action'           => ActionsHistory::TYPE_MOD,
            'action_object'    => $request->id,
            'action_msg'       => 'Generated API key',
        ];

        Module::add($logData);

        return $this->successResponse();
    }

    /**
     * Invite user to register
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[email] - required
     * @param integer data[is_admin] - optional
     * @param integer data[approved] - optional
     * @param integer data[role_id] - optional
     * @param integer data[org_id] - optional
     *
     * @return json $response - response with status
     */
    public function inviteUser(Request $request)
    {
        $errors = [];
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required|array']);

        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
        } else {
            $validator = \Validator::make($post['data'], [
                'email'    => 'required|email|max:191',
                'is_admin' => 'nullable|int|digits_between:1,10',
                'approved' => 'nullable|int|digits_between:1,10',
                'role_id'  => 'nullable|required_with:org_id',
                'org_id'   => 'nullable|int|required_with:role_id|digits_between:1,10|exists:organisations,id',
                'generate' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->messages();
            }
        }

        $rightCheck = false;

        if (isset($post['data']['org_id'])) {
            if ($orgData = Organisation::where('id', $post['data']['org_id'])->first()) {
                if( $orgData->type == Organisation::TYPE_GROUP) {
                    $rightCheck = RoleRight::checkUserRight(
                        Module::GROUP,
                        RoleRight::RIGHT_EDIT,
                        [
                            'group_id'       => $orgData->id
                        ],
                        [
                            'created_by'    => $orgData->created_by,
                            'group_ids'     => [$orgData->id]
                        ]
                    );
                } else {
                    $rightCheck = RoleRight::checkUserRight(
                        Module::ORGANISATIONS,
                        RoleRight::RIGHT_ALL,
                        [
                            'org_id'       => $organisation->id
                        ],
                        [
                            'created_by' => $organisation->created_by,
                            'org_id'     => $organisation->id
                        ]
                    );
                }
            }
        } else {
            $rightCheck = RoleRight::checkUserRight(
                Module::USERS,
                RoleRight::RIGHT_EDIT
            );
        }

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

        if (!empty($errors)) {
            return $this->errorResponse(__('custom.invite_user_fail'), $errors);
        }

        if (!empty($post['data']['generate'])) {
            DB::beginTransaction();

            $password = Uuid::generate(4)->string;
            $reqOrgId = isset($request->data['org_id']) ? $request->data['org_id']: null;

            $loggedUser = User::with('userToOrgRole')->find(Auth::id());
            $loggedOrgId = isset($loggedUser['userToOrgRole'][0]['org_id']) ? $loggedUser['userToOrgRole'][0]['org_id'] : null;
            $loggedRoleRight = isset($loggedUser['userToOrgRole'][0]['role_id'])
                ? RoleRight::where('role_id', $loggedUser['userToOrgRole'][0]['role_id'])->value('right')
                : null;

            $user = new User;

            $user->username = $this->generateUsername($request->data['email']);
            $user->password = bcrypt($password);
            $user->email = $request->data['email'];
            $user->firstname = '';
            $user->lastname = '';
            $user->add_info = '';
            $user->is_admin = isset($request->data['is_admin']) && $loggedUser->is_admin
                ? (int) $request->data['is_admin']
                : 0;
            $user->active = 0;
            $user->approved = !empty($post['data']['approved']) ? $post['data']['approved'] : 0;
            $user->api_key = Uuid::generate(4)->string;
            $user->hash_id = str_replace('-', '', Uuid::generate(4)->string);
            $user->remember_token = null;

            try {
                $user->save();

                if (isset($request->data['approved'])) {
                    $user->approved = $request->data['approved'];
                }

                $template = 'mail/generateMail';
                $mailData = [
                    'user'      => Auth::user()->firstname .' '. Auth::user()->lastname,
                    'username'  => $user->username,
                    'pass'      => $password,
                ];

                if (isset($post['data']['role_id']) && isset($post['data']['org_id'])) {
                    foreach ($post['data']['role_id'] as $role) {
                        $userToOrgRole = new UserToOrgRole;
                        $userToOrgRole->user_id = $user->id;
                        $userToOrgRole->org_id = !empty($post['data']['org_id']) ? $post['data']['org_id'] : null;
                        $userToOrgRole->role_id = $role;

                        $userToOrgRole->save();
                    }
                }

                DB::commit();
            } catch (QueryException $e) {
                $mailData = null;

                DB::rollback();

                Log::error($e->getMessage());
            }
        } else {
            $template = 'mail/inviteMail';
            $mailData = [
                'user'  => Auth::user()->firstname .' '. Auth::user()->lastname,
                'mail'  => $post['data']['email'],
            ];
        }

        if (!empty($mailData)) {
            if ($mailData['user'] == ' ') {
                $mailData['user'] = Auth::user()->username;
            }

            try {
                Mail::send($template, $mailData, function ($m) use ($post) {
                    $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                    $m->to($post['data']['email']);
                    $m->subject(__('custom.invite_subject'));
                });
            } catch (\Swift_TransportException $ex) {
                Log::error($ex->getMessage());

                $validator->errors()->add('email', __('custom.send_mail_failed'));

                return $this->errorResponse(__('custom.invite_user_fail'), $validator->errors()->messages());
            }
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::USERS),
            'action'           => ActionsHistory::TYPE_ADD,
            'action_object'    => $user->id,
            'action_msg'       => 'Invited user',
        ];

        Module::add($logData);

        return $this->successResponse();
    }

    /**
     * Register new user
     *
     * @param array data - required
     * @param string data[firstname] - required
     * @param string data[lastname] - required
     * @param string data[email] - required
     * @param string data[add_info] - optional
     * @param string data[username] - optional
     * @param string data[password] - required
     * @param string data[password_confirm] - required
     * @param integer data[role_id] - optional
     * @param array data[user_settings] - optional
     * @param string data[user_settings][locale] - optional
     * @param integer data[user_settings][newsletter_digest] - optional
     * @param array data[org_data] - optional
     * @param integer data[org_data][parent_org_id] - optional
     * @param string data[org_data][locale] - required
     * @param string data[org_data][name] - required
     * @param integer data[org_data][type] - required
     * @param string data[org_data][description] - required
     * @param string data[org_data][logo_file_name] - optional
     * @param string data[org_data][logo_mime_type] - optional
     * @param string data[org_data][logo_data] - optional
     * @param string data[org_data][activity_info] - optional
     * @param string data[org_data][contacts] - optional
     *
     * @return json response with api key or error
     */
    public function register(Request $request)
    {
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'firstname'         => 'required|string|max:100',
            'lastname'          => 'required|string|max:100',
            'username'          => 'required|string|unique:users,username,NULL,id,deleted_at,NULL|max:100',
            'email'             => 'required|email|max:191',
            'password'          => 'required|string|min:6',
            'password_confirm'  => 'required|string|same:password',
            'role_id'           => 'nullable',
            'org_id'            => 'nullable',
        ]);

        if (!$validator->fails()) {
            try {
                DB::beginTransaction();

                $apiKey = Uuid::generate(4)->string;
                $user = new User;

                $user->username = !empty($request->data['username'])
                    ? $request->data['username']
                    : $this->generateUsername($request->data['email']);
                $user->password = bcrypt($request->data['password']);
                $user->email = $request->data['email'];
                $user->firstname = $request->data['firstname'];
                $user->lastname = $request->data['lastname'];
                $user->add_info = !empty($request->data['add_info'])
                    ? $request->data['add_info']
                    : null;
                $user->is_admin = 0;
                $user->active = 0;
                $user->approved = !empty($request->offsetGet('invite')) ? 1 : 0;
                $user->api_key = $apiKey;
                $user->hash_id = str_replace('-', '', Uuid::generate(4)->string);
                $user->remember_token = null;

                $registered = $user->save();

                $mailData = [
                    'user'  => $user->firstname,
                    'hash'  => $user->hash_id,
                    'id'    => $user->id,
                    'mail'  => $user->email
                ];

                Mail::send('mail/confirmationMail', $mailData, function ($m) use ($user) {
                    $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                    $m->to($user->email, $user->firstname);
                    $m->subject(__('custom.register_subject'));
                });

                if (isset($data['role_id']) || isset($data['org_id'])) {
                    foreach ($data['role_id'] as $role) {
                        $userToOrgRole = new UserToOrgRole;
                        $userToOrgRole->user_id = $user->id;
                        $userToOrgRole->org_id = !empty($data['org_id']) ? $data['org_id'] : null;
                        $userToOrgRole->role_id = $role;

                        $userToOrgRole->save();
                    }
                }

                $userSettings = new UserSetting;

                $userLocale = !empty($data['user_settings']['locale'])
                    && !empty(Locale::where('locale', $data['user_settings']['locale'])->value('locale'))
                    ? $data['user_settings']['locale']
                    : config('app.locale');

                $userSettings->user_id = $user->id;
                $userSettings->locale = $userLocale;

                if (
                    isset($data['user_settings']['newsletter_digest'])
                    && is_numeric($data['user_settings']['newsletter_digest'])
                ) {
                    $userSettings->newsletter_digest = $data['user_settings']['newsletter_digest'];
                }

                $userSettings->save();

                if (!empty($data['org_data']) && $registered) {
                    $organisation = new Organisation;

                    $organisation->uri = !empty($data['org_data']['uri']) ? $data['org_data']['uri'] : \Uuid::generate(4)->string;
                    $organisation->type = $data['org_data']['type'];
                    $organisation->parent_org_id = !empty($data['org_data']['parent_org_id'])
                        ? $data['org_data']['parent_org_id']
                        : null;
                    $organisation->logo_file_name = !empty($data['org_data']['logo_file_name'])
                        ? $data['org_data']['logo_file_name']
                        : null;
                    $organisation->logo_mime_type = !empty($data['org_data']['logo_mime_type'])
                        ? $data['org_data']['logo_mime_type']
                        : null;
                    $organisation->logo_data = !empty($data['org_data']['logo_data'])
                        ? $data['org_data']['logo_data']
                        : null;
                    $organisation->active = 0;
                    $organisation->approved = 0;
                    $organisation->created_by = $registered->id;
                    $organisation->name = $this->trans($locale, $data['org_data']['name']);
                    $organisation->descript = $this->trans($locale, $data['org_data']['description']);
                    $organisation->activity_info = $data['org_data']['activity_info'];
                    $organisation->contacts = $data['org_data']['contacts'];

                    $organisation->save();
                }

                DB::commit();

                return $this->successResponse(['api_key' => $apiKey], true);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            } catch (\Swift_TransportException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());

                $validator->errors()->add('email', __('custom.send_mail_failed'));
            }
        } else if(!empty($data['org_data']) && !empty($data['username'])) {
            $user = User::where('username', $data['username'])->first();
            $id = $user->id;

            $validator = \Validator::make($data['org_data'], [
                'locale'                => 'nullable|string|max:5',
                'name'                  => 'required_with:locale|max:191',
                'name.bg'               => 'required_without:locale|string|max:191',
                'type'                  => 'required|int|max:191|in:'. implode(',', array_keys(Organisation::getPublicTypes())),
                'description'           => 'nullable|max:8000',
                'uri'                   => 'nullable|string|unique:organisations,uri|max:191',
                'logo'                  => 'nullable|string|max:191',
                'logo_filename'         => 'nullable|string|max:191',
                'logo_mimetype'         => 'nullable|string|max:191',
                'logo_data'             => 'nullable|max:16777215',
                'activity_info'         => 'nullable|max:8000',
                'contacts'              => 'nullable|max:8000',
                'parent_org_id'         => 'nullable|int|digits_between:1,10',
                'active'                => 'nullable|bool',
                'approved'              => 'nullable|bool',
                'custom_fields.*.label' => 'nullable|max:191',
                'custom_fields.*.value' => 'nullable|max:8000',
            ]);

            if ($user && !$validator->fails()) {
                DB::beginTransaction();

                try {
                    $organisation = new Organisation;

                    $organisation->uri = !empty($data['org_data']['uri']) ? $data['org_data']['uri'] : \Uuid::generate(4)->string;
                    $organisation->type = $data['org_data']['type'];
                    $organisation->parent_org_id = !empty($data['org_data']['parent_org_id'])
                        ? $data['org_data']['parent_org_id']
                        : null;
                    $organisation->active = 0;
                    $organisation->approved = 0;
                    $organisation->created_by = $id;
                    $organisation->name = $this->trans($locale, $data['org_data']['name']);
                    $organisation->descript = $this->trans($locale, $data['org_data']['descript']);
                    $organisation->activity_info = $data['org_data']['activity_info'];
                    $organisation->contacts = $data['org_data']['contacts'];

                    if (!empty($data['org_data']['logo'])) {
                        try {
                            $img = \Image::make($data['org_data']['logo']);

                            $organisation->logo_file_name = empty($data['org_data']['logo_filename'])
                                ? basename($data['org_data']['logo'])
                                : $data['org_data']['logo_filename'];
                            $organisation->logo_mime_type = $img->mime();

                            $temp = tmpfile();
                            $path = stream_get_meta_data($temp)['uri'];
                            $img->save($path);
                            $organisation->logo_data = file_get_contents($path);

                            fclose($temp);
                        } catch (\Exception $ex) {
                            $imageError = true;

                            $validator->errors()->add('logo', $this->getImageTypeError());
                        }

                        if (isset($data['org_data']['logo_filename']) && isset($data['org_data']['logo_mimetype']) && isset($data['org_data']['logo_data'])) {
                            $organisation->logo_file_name = $data['org_data']['logo_filename'];
                            $organisation->logo_mime_type = $data['org_data']['logo_mimetype'];
                            $organisation->logo_data = $data['org_data']['logo_data'];
                        }

                        if (isset($organisation->logo_data) && !$this->checkImageSize($organisation->logo_data)) {
                            $imageError = true;

                            $validator->errors()->add('logo', $this->getImageSizeError());
                        }
                    }

                    $organisation->save();

                    if ($organisation) {
                        $userToOrgRole = new UserToOrgRole;
                        $userToOrgRole->org_id = $organisation->id;
                        $userToOrgRole->user_id = $id;
                        $userToOrgRole->role_id = Role::ROLE_ADMIN;

                        $userToOrgRole->save();

                        DB::commit();
                    }

                    return $this->successResponse(['api_key' => $user->api_key], true);
                } catch (QueryException $ex) {
                    DB::rollback();

                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.user_registration_fail'), $validator->errors()->messages());
    }

    /**
     * Get available username using the first part of an email
     *
     * @param string $email
     * @return string $availableUsername
     */
    public function generateUsername($email)
    {
        $parts = explode('@', $email);
        $username = $parts[0];
        $count = 0;

        while (User::where('username', $username)->count()) {
            $count++;
            $username = $username . $count;
        }

        return $username;
    }

    /**
     * Forgotten password
     *
     * @param string username - required
     *
     * @return json response with status (true - if user is found and email is sent false - otherwise)
     */
    public function forgottenPassword(Request $request)
    {
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'username' => 'required|string|exists:users,username,deleted_at,NULL|max:255'
        ]);

        if (!$validator->fails()) {
            $user = User::where('username', '=', $data['username'])->first();

            if (!empty($user['email'])) {
                $mailData = [
                    'user'      => $user['firstname'] .' '. $user['lastname'],
                    'hash'      => str_replace('-', '', Uuid::generate(4)->string),
                    'username'  => $user['username']
                ];

                Mail::send('mail/passReset', $mailData, function ($m) use ($user) {
                    $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                    $m->to($user['email'], $user['firstname']);
                    $m->subject(__('custom.pass_change').'!');
                });

                $newUserData['hash_id'] = $mailData['hash'];
                $newUserData['updated_by'] = $user['id'];

                if (count(Mail::failures()) <= 0) {
                    try {
                        User::where('username', $user['username'])->update($newUserData);
                    } catch (QueryException $e) {
                        Log::error($e->getMessage());
                    }
                } else {
                    return $this->errorResponse(__('custоm.email_send_err'));
                }

                return $this->successResponse();
            }
        }

        return $this->errorResponse(__('custom.reset_pass_fail'), $validator->errors()->messages());
    }

    /**
     * Password reset
     *
     * @param string hash - required
     * @param string password - required
     * @param string password_confirm - required
     *
     * @return json response with status (true - if password is changed false - otherwise)
     */
    public function passwordReset(Request $request)
    {
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'hash'              => 'required|string',
            'password'          => 'required|string|min:6',
            'password_confirm'  => 'required|string|same:password',
        ]);

        if (!$validator->fails()) {
            $user = User::where('hash_id', '=', $data['hash'])->first();

            if ($user) {
                $newUserData['password'] = bcrypt($data['password']);

                if (!empty($newUserData)) {
                    $newUserData['updated_by'] = $user->id;
                    try {
                        User::where('id', $user->id)->update($newUserData);
                    } catch (QueryException $e) {
                        Log::error($e->getMessage());
                    }

                    return $this->successResponse();
                }
            } else {
                return $this->errorResponse(__('custom.wrong_reset_link'));
            }
        }

        return $this->errorResponse(__('custom.pass_change_err'), $validator->errors()->messages());
    }

    /**
     * Get active users count
     *
     * @return json response with user count
     */
    public function userCount(Request $request)
    {
        $users = User::where('active', 1)->count();

        return $this->successResponse(['count' => $users], true);
    }
}
