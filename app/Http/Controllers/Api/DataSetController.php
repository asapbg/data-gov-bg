<?php
namespace App\Http\Controllers\Api;

use Uuid;
use Error;
use App\Tags;
use App\User;
use Throwable;
use App\Module;
use App\Signal;
use App\DataSet;
use App\Category;
use App\Resource;
use App\RoleRight;
use App\DataSetTags;
use App\DataSetGroup;
use \App\Organisation;
use App\CustomSetting;
use App\UserToOrgRole;
use App\ActionsHistory;
use Illuminate\Http\Request;
use App\Translator\Translation;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class DataSetController extends ApiController
{
    /**
     * API function for adding Data Set
     *
     * @param string api_key - required
     * @param integer org_id - optional
     * @param array data - required
     * @param string data[locale] - required
     * @param mixed data[name] - required
     * @param string data[uri] - optional
     * @param mixed data[description] - optional
     * @param array data[tags] - optional
     * @param integer data[category_id] - required
     * @param integer data[terms_of_use_id] - optional
     * @param integer data[visibility] - optional
     * @param string data[source] - optional
     * @param string data[author_name] - optional
     * @param string data[author_email] - optional
     * @param string data[support_name] - optional
     * @param string data[support_email] - optional
     * @param mixed data[sla] - optional
     *
     * @return json response with id of Data Set or error
     */
    public function addDataset(Request $request)
    {
        $errors = [];
        $post = $request->all();
        $visibilityTypes = DataSet::getVisibility();

        if (isset($post['data']['org_id'])) {
            $post['org_id'] = $post['data']['org_id'];
        }

        $validator = \Validator::make($post, [
            'org_id'    => 'nullable|int|digits_between:1,10|exists:organisations,id,deleted_at,NULL',
            'data'      => 'required|array',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
        } else {
            $orgRule = UserToOrgRole::where('user_id', Auth::user()->id)->whereNotNull('org_id')->count() ? 'required' : 'nullable';
            $validator = \Validator::make($post['data'], [
                'locale'                => 'nullable|string|max:5',
                'name'                  => 'required_with:locale|max:8000',
                'name.bg'               => 'required_without:locale|string|max:8000',
                'description'           => 'nullable|max:8000',
                'tags.*.name'           => 'nullable|string|max:191',
                'category_id'           => 'required|int|digits_between:1,10|exists:categories,id',
                'org_id'                => $orgRule .'|int|digits_between:1,10|exists:organisations,id,deleted_at,NULL',
                'terms_of_use_id'       => 'nullable|int|digits_between:1,10|exists:terms_of_use,id',
                'visibility'            => 'nullable|int|in:'. implode(',', array_flip($visibilityTypes)),
                'source'                => 'nullable|string|max:191',
                'author_name'           => 'nullable|string|max:191',
                'author_email'          => 'nullable|email|max:191',
                'support_name'          => 'nullable|string|max:191',
                'support_email'         => 'nullable|email|max:191',
                'sla'                   => 'nullable|max:8000',
                'forum_link'            => 'nullable|string|max:191',
                'trusted'               => 'nullable|digits_between:0,1',
                'custom_fields.*.label' => 'nullable|max:191',
                'custom_fields.*.value' => 'nullable|max:8000',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->messages();
            }
        }

        if (isset($post['org_id'])) {
            $organisation = Organisation::where('id', $post['org_id'])->first();
            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_EDIT,
                [
                    'org_id' => $organisation->id
                ],
                [
                    'org_id' => $organisation->id
                ]
            );
        } else {
            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_EDIT
            );
        }

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

        if (empty($errors)) {
            $data = $post['data'];
            $locale = isset($data['locale']) ? $data['locale'] : null;

            $dbData = [
                'uri'               => Uuid::generate(4)->string,
                'name'              => $this->trans($locale, $data['name']),
                'descript'          => empty($data['description']) ? null : $this->trans($locale, $data['description']),
                'sla'               => empty($data['sla']) ? null : $this->trans($locale, $data['sla']),
                'org_id'            => empty($data['org_id']) ? null : $data['org_id'],
                'visibility'        => empty($data['visibility']) ? DataSet::VISIBILITY_PRIVATE : $data['visibility'],
                'version'           => 1,
                'status'            => DataSet::STATUS_DRAFT,
                'category_id'       => $data['category_id'],
                'terms_of_use_id'   => empty($data['terms_of_use_id']) ? null : $data['terms_of_use_id'],
                'source'            => empty($data['source']) ? null : $data['source'],
                'author_name'       => empty($data['author_name']) ? null : $data['author_name'],
                'author_email'      => empty($data['author_email']) ? null : $data['author_email'],
                'support_name'      => empty($data['support_name']) ? null : $data['support_name'],
                'support_email'     => empty($data['support_email']) ? null : $data['support_email'],
                'forum_link'        => empty($data['forum_link']) ? null : $data['forum_link'],
                'trusted'           => Auth::user()->is_admin && !empty($data['trusted']) ? 1 : 0,
            ];

            if (
                isset($data['migrated_data'])
                && Auth::user()->username == 'migrate_data'
            ){
                if (!empty($data['created_by'])) {
                    $dbData['created_by'] = $data['created_by'];
                }

                if (!empty($data['updated_by'])) {
                    $dbData['updated_by'] = $data['updated_by'];
                }

                if (!empty($data['created_at'])) {
                    $dbData['created_at'] = date('Y-m-d H:i:s', strtotime($data['created_at']));
                }

                if (!empty($data['status'])) {
                    $dbData['status'] = $data['status'];
                }

                if (!empty($data['visibility'])) {
                    $dbData['visibility'] = $data['visibility'];
                }

                $dbData['is_migrated'] = true;
            }

            $customFields = null;

            if (!empty($data['custom_fields'])) {
                foreach ($data['custom_fields'] as $fieldSet) {
                    if (is_array($fieldSet['value']) && is_array($fieldSet['label'])) {
                        if (!empty(array_filter($fieldSet['value']) || !empty(array_filter($fieldSet['label'])))) {
                            $customFields[] = [
                                'value' => $fieldSet['value'],
                                'label' => $fieldSet['label'],
                            ];
                        }
                    } elseif (!empty($fieldSet['label'])) {
                        $customFields[] = [
                            'value' => [
                               $locale => $fieldSet['value']
                            ],
                            'label' =>[
                                $locale => $fieldSet['label']
                             ]
                        ];
                    }
                }
            }

            try {
                $result = DB::transaction(function () use ($dbData, $data, $customFields) {
                    $newDataSet = DataSet::create($dbData);
                    $newDataSet->searchable();

                    if (!empty($data['tags'])) {
                        if (!$this->checkAndCreateTags($newDataSet, $data['tags'])) {
                            throw new Error;
                        }
                    }

                    if (!empty($customFields)) {
                        if (!$this->checkAndCreateCustomSettings($customFields, $newDataSet->id)) {
                            throw new Error;
                        }
                    }

                    $logData = [
                        'module_name'      => Module::getModuleName(Module::DATA_SETS),
                        'action'           => ActionsHistory::TYPE_ADD,
                        'action_object'    => $newDataSet->id,
                        'action_msg'       => 'Added dataset',
                    ];

                    Module::add($logData);

                    return $this->successResponse(['uri' => $newDataSet->uri], true);
                }, config('app.TRANSACTION_ATTEMPTS'));

                return $result;
            } catch (Throwable $e) {
                Log::error($ex->getMessage());
                return $this->errorResponse(__('custom.add_dataset_fail'));
            }
        }

        return $this->errorResponse(__('custom.add_dataset_fail'), $errors);
    }

    /**
     * API function for editing an existing Data Set
     *
     * @param string api_key - required
     * @param string dataset_uri - required
     * @param array data - required
     * @param string data[locale] - required
     * @param string data[name] - required
     * @param string data[uri] - optional
     * @param string data[description] - optional
     * @param array data[tags] - optional
     * @param integer data[category_id] - required
     * @param integer data[org_id] - optional
     * @param integer data[terms_of_use_id] - optional
     * @param integer data[visibility] - optional
     * @param string data[source] - optional
     * @param string data[author_name] - optional
     * @param string data[author_email] - optional
     * @param string data[support_name] - optional
     * @param string data[support_email] - optional
     * @param string data[sla] - optional
     * @param integer data[status] - optional
     *
     * @return json response with success or error
     */
    public function editDataset(Request $request)
    {
        ini_set('max_execution_time', 600);
        $post = $request->all();
        $tags = [];
        $customFields = [];
        $errors = [];
        $visibilityTypes = DataSet::getVisibility();
        $statusTypes = DataSet::getStatus();

        $validator = \Validator::make($post, [
            'dataset_uri'   => 'required|string|max:191|exists:data_sets,uri,deleted_at,NULL',
            'data'          => 'required|array',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
        } else {
            $orgRule = UserToOrgRole::where('user_id', Auth::user()->id)->whereNotNull('org_id')->count() ? 'required' : 'nullable';
            $validator = \Validator::make($post['data'], [
                'locale'                => 'nullable|string|max:5',
                'name'                  => 'required_with:locale|max:8000',
                'name.bg'               => 'required_without:locale|string|max:8000',
                'description'           => 'nullable|max:8000',
                'category_id'           => 'required|int|digits_between:1,10',
                'org_id'                => $orgRule .'|int|digits_between:1,10|exists:organisations,id,deleted_at,NULL',
                'tags.*.name'           => 'nullable|string|max:191',
                'terms_of_use_id'       => 'nullable|int|digits_between:1,10',
                'visibility'            => 'nullable|int|in:'. implode(',', array_flip($visibilityTypes)),
                'source'                => 'nullable|string|max:255',
                'author_name'           => 'nullable|string|max:191',
                'author_email'          => 'nullable|email|max:191',
                'support_name'          => 'nullable|string|max:191',
                'support_email'         => 'nullable|email|max:191',
                'sla'                   => 'nullable|max:8000',
                'forum_link'            => 'nullable|string|max:191',
                'status'                => 'nullable|int|in:'. implode(',', array_flip($statusTypes)),
                'trusted'               => 'nullable|digits_between:0,1',
                'custom_fields.*.label' => 'nullable|max:191',
                'custom_fields.*.value' => 'nullable|max:8000',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->messages();
            } else {
                $validator->after(function ($validator) use ($post) {
                    if (
                        !empty($post['data']['status'])
                        && $post['data']['status'] == DataSet::STATUS_PUBLISHED
                    ) {
                        $resourcesWithNoData = get_dataset_resources_with_no_data($post['dataset_uri']);

                        if (!empty($resourcesWithNoData)) {
                            $validator->errors()->add('status',
                                __('custom.publish_dataset_with_no_resource_data')
                                .' ('. implode(', ', $resourcesWithNoData) .')'
                            );
                        }
                    }
                });

                if ($validator->fails()) {
                    $errors = $validator->errors()->messages();
                }
            }
        }

        if (empty($errors)) {
            $dataset = DataSet::where('uri', $post['dataset_uri'])->first();
            $locale = isset($post['data']['locale']) ? $post['data']['locale'] : null;

            if (isset($post['data']['org_id'])) {
                $organisation = Organisation::where('id', $post['data']['org_id'])->first();
                $rightCheck = RoleRight::checkUserRight(
                    Module::DATA_SETS,
                    RoleRight::RIGHT_EDIT,
                    [
                        'org_id' => $organisation->id
                    ],
                    [
                        'created_by' => $dataset->created_by,
                        'org_id'     => $organisation->id
                    ]
                );
            } else {
                $rightCheck = RoleRight::checkUserRight(
                    Module::DATA_SETS,
                    RoleRight::RIGHT_EDIT
                );
            }

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            $customFields = null;

            if (!empty($post['data']['custom_fields'])) {
                foreach ($post['data']['custom_fields'] as $i => $fieldSet) {
                    if (
                        !empty(array_filter($fieldSet['value'])
                        && isset($post['data']['sett_id'][$i]))
                    ) {
                        $customFields[] = [
                            'value'     => $fieldSet['value'],
                            'sett_id'   => $post['data']['sett_id'][$i],
                            'label'     => isset($fieldSet['label']) ? $fieldSet['label'] : null,
                        ];
                    } elseif (
                        !empty(
                            array_filter($fieldSet['value'])
                            && isset($fieldSet['label'])
                            && !empty(array_filter($fieldSet['label']))
                        )
                    ) {
                        $customFields[] = [
                            'value' => $fieldSet['value'],
                            'label' => $fieldSet['label'],
                        ];
                    }
                }
            }

            try {
                $result = DB::transaction(function () use ($post, $dataset, $locale, $customFields) {
                    if (!empty($post['data']['name'])) {
                        $dataset->name = $this->trans($locale, $post['data']['name']);
                    }

                    if (!empty($post['data']['sla'])) {
                        $dataset->sla = $this->trans($locale, $post['data']['sla']);
                    }

                    if (!empty($post['data']['description'])) {
                        $dataset->descript = $this->trans($locale, $post['data']['description']);
                    }

                    if (!empty($post['data']['category_id'])) {
                        $dataset->category_id = $post['data']['category_id'];
                    }

                    if (
                        isset($post['data']['migrated_data'])
                        && Auth::user()->username == 'migrate_data'
                    ){
                        $dataset->is_migrated = true;
                    }

                    // Increase dataset version withot goint to new full version
                    $versionParts = explode('.', $dataset->version);

                    if (isset($versionParts[1])) {
                        $dataset->version = $versionParts[0] .'.'. strval(intval($versionParts[1]) + 1);
                    } else {
                        $dataset->version = $versionParts[0] .'.1';
                    }

                    // If NULL passed - dataset not connected to organisation
                    if (!empty($post['data']['org_id'])) {
                        $dataset->org_id = $post['data']['org_id'];
                    }

                    if (!empty($post['data']['terms_of_use_id'])) {
                        $dataset->terms_of_use_id = $post['data']['terms_of_use_id'];
                    }

                    if (!empty($post['data']['visibility'])) {
                        $dataset->visibility = $post['data']['visibility'];
                    }

                    if (!empty($post['data']['source'])) {
                        $dataset->source = $post['data']['source'];
                    }

                    if (!empty($post['data']['author_name'])) {
                        $dataset->author_name = $post['data']['author_name'];
                    }

                    if (!empty($post['data']['author_email'])) {
                        $dataset->author_email = $post['data']['author_email'];
                    }

                    if (!empty($post['data']['support_name'])) {
                        $dataset->support_name = $post['data']['support_name'];
                    }

                    if (!empty($post['data']['support_email'])) {
                        $dataset->support_email = $post['data']['support_email'];
                    }

                    $dataset->forum_link = !empty($post['data']['forum_link'])
                        ? $post['data']['forum_link']
                        : null;

                    $isAdmin = Auth::user()->is_admin;
                    $dataset->trusted = $isAdmin && !empty($post['data']['trusted']) ? 1 : 0;

                    if (!empty($post['data']['status'])) {
                        $dataset->status = $post['data']['status'];
                    }

                    if (!$isAdmin && !$dataset->trusted) {
                        $dataset->status = DataSet::STATUS_DRAFT;
                    }

                    $dataset->save();

                    if (!empty($customFields)) {
                        if (!$this->checkAndCreateCustomSettings($customFields, $dataset->id)) {
                            throw new Error;
                        }
                    }

                    if (!empty($post['data']['tags'])) {
                        if (!$this->checkAndCreateTags($dataset, $post['data']['tags'])) {
                            throw new Error;
                        }
                    }

                    $logData = [
                        'module_name'      => Module::getModuleName(Module::DATA_SETS),
                        'action'           => ActionsHistory::TYPE_MOD,
                        'action_object'    => $dataset->id,
                        'action_msg'       => 'Edited dataset',
                    ];

                    Module::add($logData);

                    return $this->successResponse();
                }, config('app.TRANSACTION_ATTEMPTS'));

                return $result;
            } catch (Throwable $e) {
                Log::error($e->getMessage());
                return $this->errorResponse(__('custom.edit_dataset_fail'));
            }
        }

        return $this->errorResponse(__('custom.edit_dataset_fail'), $errors);
    }

    /**
     * API function for deleting an existing Data Set
     *
     * @param string api_key - required
     * @param integer dataset_uri - required
     *
     * @return json response with success or error
     */
    public function deleteDataset(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['dataset_uri' => 'required|string|max:191']);

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.delete_dataset_fail'), $validator->errors()->messages());
        }

        if (empty($dataset = DataSet::where('uri', $post['dataset_uri'])->first())) {
            return $this->errorResponse(__('custom.delete_dataset_fail'));
        }

        if (isset($dataset->org_id)) {
            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_ALL,
                [
                    'org_id' => $dataset->org_id
                ],
                [
                    'created_by' => $dataset->created_by,
                    'org_id'     => $dataset->org_id
                ]
            );
        } else {
            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_ALL,
                [],
                [
                    'created_by' => $dataset->created_by,
                ]
            );
        }

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

        try {
            $logData = [
                'module_name'      => Module::getModuleName(Module::DATA_SETS),
                'action'           => ActionsHistory::TYPE_DEL,
                'action_object'    => $dataset->id,
                'action_msg'       => 'Deleted dataset',
            ];

            $dataset->delete();

            if ($dataset->deleted_by) {
                $resources = Resource::where('data_set_id', $dataset->id)->withTrashed()->get();

                foreach ($resources as $singleResource) {
                    $removeSignals[] = $singleResource->id;
                }

                if (!empty($removeSignals)) {
                    Signal::whereIn('resource_id', $removeSignals)->delete();
                }
            }

            Module::add($logData);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse(__('custom.delete_dataset_fail'));
        }

        try {
            $dataset->deleted_by = \Auth::id();
            $dataset->save();

        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse(__('custom.delete_dataset_fail'));
        }

        return $this->successResponse();
    }

    /**
     * API function for listing Data Sets
     *
     * @param array criteria - optional
     * @param array criteria[dataset_ids] - optional
     * @param string criteria[locale] - optional
     * @param array criteria[org_ids] - optional
     * @param array criteria[group_ids] - optional
     * @param array criteria[tag_ids] - optional
     * @param array criteria[category_ids] - optional
     * @param array criteria[terms_of_use_ids] - optional
     * @param array criteria[formats] - optional
     * @param integer criteria[reported] - optional
     * @param integer criteria[created_by] - optional
     * @param array criteria[user_ids] - optional
     * @param boolean criteria[user_datasets_only] - optional
     * @param boolean criteria[keywords] - optional
     * @param array criteria[public] - optional
     * @param string criteria[date_from] - optional
     * @param string criteria[date_to] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json list with records or error
     */
    public function listDatasets(Request $request)
    {
        $post = $request->all();

        $criteria = !empty($post['criteria']) ? $post['criteria'] : [];
        $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'desc';
        $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'created_at';
        $locale = !empty($post['criteria']['locale'])
            ? $post['criteria']['locale']
            : \LaravelLocalization::getCurrentLocale();

        $validator = \Validator::make($post, [
            'api_key'                    => 'nullable|string|exists:users,api_key',
            'criteria'                   => 'nullable|array',
            'records_per_page'           => 'nullable|int|digits_between:1,10',
            'page_number'                => 'nullable|int|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($criteria, [
                'dataset_ids'           => 'nullable|array',
                'dataset_ids.*'         => 'int|digits_between:1,10',
                'locale'                => 'nullable|string|max:5',
                'org_ids'               => 'nullable|array',
                'org_ids.*'             => 'int|digits_between:1,10',
                'group_ids'             => 'nullable|array',
                'group_ids.*'           => 'int|digits_between:1,10',
                'category_ids'          => 'nullable|array',
                'category_ids.*'        => 'int|digits_between:1,10',
                'tag_ids'               => 'nullable|array',
                'tag_ids.*'             => 'int|digits_between:1,10',
                'formats'               => 'nullable|array|min:1',
                'formats.*'             => 'string|in:'. implode(',', Resource::getFormats()),
                'terms_of_use_ids'      => 'nullable|array',
                'terms_of_use_ids.*'    => 'int|digits_between:1,10',
                'keywords'              => 'nullable|string|max:191',
                'status'                => 'nullable|int|in:'. implode(',', array_keys(DataSet::getStatus())),
                'visibility'            => 'nullable|int|in:'. implode(',', array_keys(DataSet::getVisibility())),
                'reported'              => 'nullable|int|digits_between:1,10',
                'created_by'            => 'nullable|int|digits_between:1,10',
                'user_ids'              => 'nullable|array',
                'user_ids.*'            => 'int|digits_between:1,10',
                'user_datasets_only'    => 'nullable|bool',
                'public'                => 'nullable|bool',
                'date_from'             => 'nullable|date',
                'date_to'               => 'nullable|date',
                'order'                 => 'nullable|array',
            ]);
        }

        if (!$validator->fails()) {
            $validator = \Validator::make($order, [
                'type'  => 'nullable|string|max:191',
                'field' => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $query = DataSet::select()->with('resource');

                if (isset($post['api_key'])) {
                    $user = \App\User::where('api_key', $post['api_key'])->first();
                    $rightCheck = RoleRight::checkUserRight(
                        Module::DATA_SETS,
                        RoleRight::RIGHT_VIEW,
                        [
                            'user' => $user
                        ]
                    );

                    if (!$rightCheck) {
                        return $this->errorResponse(__('custom.access_denied'));
                    }

                    if (!empty($criteria['status'])) {
                        $query->where('status', $criteria['status']);
                    }

                    if (!empty($criteria['visibility'])) {
                        $query->where('visibility', $criteria['visibility']);
                    }
                } else {
                    $query->where('status', DataSet::STATUS_PUBLISHED);
                    $query->where('visibility', DataSet::VISIBILITY_PUBLIC);
                }

                if (!empty($criteria['public'])) {
                    $query->where(function($q) {
                        $q->whereIn(
                            'data_sets.org_id',
                            Organisation::select('id')
                                ->where('organisations.active', 1)
                                ->where('organisations.approved', 1)
                                ->get()
                                ->pluck('id')
                        )
                            ->orWhereNull('data_sets.org_id');
                    });
                }
                if (!empty($criteria['dataset_ids'])) {
                    $query->whereIn('data_sets.id', $criteria['dataset_ids']);
                }

                if (!empty($criteria['keywords'])) {
                    $tntIds = DataSet::search($criteria['keywords'])->get()->pluck('id');

                    $fullMatchIds = DataSet::select('data_sets.id')
                        ->leftJoin('translations', 'translations.group_id', '=', 'data_sets.name')
                        ->where('translations.locale', $locale)
                        ->where('translations.text', 'like', '%'. $criteria['keywords'] .'%')
                        ->pluck('id');

                    $ids = $fullMatchIds->merge($tntIds)->unique();

                    $query->whereIn('data_sets.id', $ids);

                    if (count($ids)) {
                        $strIds = $ids->implode(',');
                        $query->raw(DB::raw('FIELD(data_sets.id, '. $strIds .')'));
                    }
                }

                if (isset($criteria['user_datasets_only']) && $criteria['user_datasets_only']) {
                    $query->whereNull('org_id');
                } elseif (!empty($criteria['org_ids'])) {
                    $query->whereIn('org_id', $criteria['org_ids']);
                }

                if (!empty($criteria['group_ids'])) {
                    $query->whereHas('dataSetGroup', function($q) use($criteria) {
                        $q->whereIn('group_id', $criteria['group_ids']);
                    });
                }

                if (!empty($criteria['category_ids'])) {
                    $query->whereIn('category_id', $criteria['category_ids']);
                }

                if (!empty($criteria['tag_ids'])) {
                    $query->whereHas('dataSetTags', function($q) use ($criteria) {
                        $q->whereIn('tag_id', $criteria['tag_ids']);
                    });
                }

                if (!empty($criteria['formats'])) {
                    $formatCodes = array_flip(Resource::getFormats());
                    $formats = [];

                    foreach ($criteria['formats'] as $format) {
                        if (isset($formatCodes[$format])) {
                            array_push($formats, $formatCodes[$format]);
                        }
                    }

                    $query->whereHas('resource', function($q) use ($formats) {
                        $q->whereIn('file_format', $formats);
                    });
                }

                if (!empty($criteria['terms_of_use_ids'])) {
                    $query->whereIn('terms_of_use_id', $criteria['terms_of_use_ids']);
                }

                if (!empty($criteria['date_from'])) {
                    $query->where('data_sets.created_at', '>=', $criteria['date_from']);
                }

                if (!empty($criteria['date_to'])) {
                    $query->where('data_sets.created_at', '<=', $criteria['date_to']);
                }

                if (!empty($criteria['reported'])) {
                    $query->whereHas('resource', function($q) use ($criteria) {
                        $q->where('is_reported', $criteria['reported']);
                    });
                }

                if (!empty($criteria['user_ids'])) {
                    $query->whereIn('data_sets.created_by', $criteria['user_ids']);
                } elseif (!empty($criteria['created_by'])) {
                    $query->where('data_sets.created_by', $criteria['created_by']);
                }

                $orderColumns = [
                    'org_id',
                    'name',
                    'descript',
                    'visibility',
                    'source',
                    'version',
                    'author_name',
                    'author_email',
                    'support_name',
                    'sla',
                    'status',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by'
                ];

                if (isset($criteria['order']['field'])) {
                    if (!in_array($criteria['order']['field'], $orderColumns)) {
                        return $this->errorResponse(__('custom.invalid_sort_field'));
                    }
                }

                $count = $query->count();

                $transFields = ['name', 'sla', 'descript'];

                $transCols = DataSet::getTransFields();

                if (in_array($order['field'], $transFields)) {
                    $col = $transCols[$order['field']];
                    $query->select('translations.label', 'translations.group_id', 'translations.text', 'data_sets.*')
                        ->leftJoin('translations', 'translations.group_id', '=', 'data_sets.' . $order['field'])->where('translations.locale', $locale)
                        ->orderBy('translations.' . $col, $order['type']);
                } else {
                    $query->orderBy($order['field'], $order['type']);
                }

                $query->forPage(
                    $request->offsetGet('page_number'),
                    $this->getRecordsPerPage($request->offsetGet('records_per_page'))
                );

                $results = [];

                foreach ($query->get() as $set) {
                    $result['id'] = $set->id;
                    $result['uri'] = $set->uri;
                    $result['org_id'] = $set->org_id;
                    $result['name'] = $set->name;
                    $result['descript'] = $set->descript;
                    $result['locale'] = $locale;
                    $result['category_id'] = $set->category_id;
                    $result['terms_of_use_id'] = $set->terms_of_use_id;
                    $result['visibility'] = $set->visibility;
                    $result['source'] = $set->source;
                    $result['version'] = $set->version;
                    $result['author_name'] = $set->author_name;
                    $result['author_email'] = $set->author_email;
                    $result['support_name'] = $set->support_name;
                    $result['support_email'] = $set->support_email;
                    $result['sla'] = $set->sla;
                    $result['status'] = $set->status;
                    $result['forum_link'] = $set->forum_link;
                    $result['followers_count'] = $set->userFollow()->count();
                    $result['reported'] = 0;
                    $result['created_at'] = isset($set->created_at) ? $set->created_at->toDateTimeString() : null;
                    $result['updated_at'] = isset($set->updated_at) ? $set->updated_at->toDateTimeString() : null;
                    $result['created_by'] = $set->created_by;
                    $result['updated_by'] = $set->updated_by;

                    $hasRes = $set->resource()->count();
                    $formats = [];
                    $formatList = Resource::getFormats();

                    if ($hasRes) {
                        foreach ($set->resource as $resourse) {
                            $result['resource'][$resourse->uri] = $resourse;
                            $result['resource'][$resourse->uri]['name'] = $resourse->name;

                            if (!empty($resourse->file_format)) {
                                $formats[$formatList[$resourse->file_format]] = $formatList[$resourse->file_format];
                            }
                            if ($resourse->is_reported) {
                                $result['reported'] = 1;
                            }
                        }
                    }

                    $result['formats'] = array_keys($formats);
                    $tags = [];

                    foreach ($set->tags as $tag) {
                        $tags[] = [
                            'id'    => $tag->id,
                            'name'  => $tag->name,
                        ];
                    }

                    $result['tags'] = $tags;

                    $results[] = $result;
                    unset($result);
                }

                return $this->successResponse([
                    'datasets'      => $results,
                    'total_records' => $count
                ], true);
            } catch (Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.criteria_error'), $validator->errors()->messages());
    }

    /**
     * API function for viewing information about an existing Data Set
     *
     * @param string api_key - optional
     * @param string dataset_uri - required
     * @param string locale - optional
     *
     * @return json response with data or error
     */
    public function getDatasetDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'dataset_uri'   => 'required|string|max:191|exists:data_sets,uri,deleted_at,NULL',
            'locale'        => 'nullable|string|max:5',
        ]);

        if (!$validator->fails()) {
            try {
                $data = DataSet::where('uri', $post['dataset_uri'])
                    ->withCount('userFollow as followers_count')
                    ->with(['tags' => function($query) {
                        $query->select('id', 'name');
                    }])
                    ->with('organisation')
                    ->first()
                    ->loadTranslations();

                if ($data) {
                    $customFields = [];
                    $settings = $data->customSetting()->get();

                    if (count($settings)) {
                        foreach ($settings as $setting) {
                            $customFields[] = [
                                'key'    => $setting->key,
                                'value'  => $setting->value
                            ];
                        }
                    }

                    $groupLinks = $data->dataSetGroup()->get();
                    $ids = $groups = [];

                    if (count($groupLinks)) {
                        foreach ($groupLinks as $link) {
                            $ids[] = $link->group_id;
                        }
                    }

                    $groupsCollection = Organisation::whereIn('id', $ids)->get()->loadTranslations();

                    foreach ($groupsCollection as $value) {
                        $groups[] = [
                            'id'    => $value->id,
                            'uri'   => $value->uri,
                            'name'  => $value->name,
                        ];
                    }

                    if (isset($data->organisation)) {
                        $data['org'] = [
                            'uri'   => $data->organisation->uri,
                            'name'  => $data->organisation->name,
                        ];
                        unset($data->organisation);
                    }

                    $data['groups'] = $groups;
                    $data['custom_settings'] = $customFields;
                    $data['name'] = $data->name;
                    $data['sla'] = $data->sla;
                    $data['description'] = $data->description;
                    $data['reported'] = 0;
                    $data['forum_link'] = $data->forum_link;

                    $hasRes = $data->resource()->count();

                    $allSignals = [];

                    if ($hasRes) {
                        foreach ($data->resource as $resource) {
                            if ($resource->is_reported) {
                                $data['reported'] = 1;
                                $signals = Signal::where('resource_id', $resource->id)
                                    ->where('status', Signal::STATUS_NEW)->get();

                                if ($signals) {
                                    foreach ($signals as $signal) {
                                        $allSignals[] = [
                                            'id'            => $signal->id,
                                            'resource_name' => $resource->name,
                                            'resource_uri'  => $resource->uri,
                                            'description'   => $signal->descript,
                                            'firstname'     => $signal->firstname,
                                            'lastname'      => $signal->lastname,
                                            'email'         => $signal->email,
                                            'status'        => $signal->status,
                                            'created_at'    => date($signal->created_at),
                                            'updated_at'    => date($signal->updated_at),
                                            'created_by'    => $signal->created_by,
                                            'updated_by'    => $signal->updated_by,
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    $data['signals'] = $allSignals;
                    unset($data['resource']);

                    $logData = [
                        'module_name'      => Module::getModuleName(Module::DATA_SETS),
                        'action'           => ActionsHistory::TYPE_SEE,
                        'action_object'    => $data->id,
                        'action_msg'       => 'Got dataset details',
                    ];

                    Module::add($logData);

                    return $this->successResponse($data);
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.get_dataset_fail'), $validator->errors()->messages());
    }

    /**
     * API function for adding Data Set to group
     *
     * @param string api_key - required
     * @param integer dataset_uri - required
     * @param integer group_id - required
     *
     * @return json success or error
     */
    public function addDatasetToGroup(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'data_set_uri'  => 'required|string|exists:data_sets,uri,deleted_at,NULL|max:191',
            'group_id'      => 'required|array',
            'group_id.*'    => [
                'required',
                'int',
                Rule::exists('organisations', 'id')->where(function ($query) {
                    $query->where('type', Organisation::TYPE_GROUP);
                }),
            ],
        ]);

        if (!$validator->fails()) {
            try {
                $result = DB::transaction(function () use ($post) {
                    $dataSetId = DataSet::where('uri', $post['data_set_uri'])->first()->id;
                    DataSetGroup::destroy($dataSetId);

                    foreach ($post['group_id'] as $id) {
                        $rightCheck = RoleRight::checkUserRight(
                            Module::GROUPS,
                            RoleRight::RIGHT_EDIT,
                            [
                                'group_id' => $id
                            ],
                            [
                                'group_ids' => $post['group_id']
                            ]
                        );

                        if (!$rightCheck) {
                            return $this->errorResponse(__('custom.access_denied'));
                        }

                        $setGroup = new DataSetGroup;
                        $setGroup->data_set_id = $dataSetId;
                        $setGroup->group_id = $id;

                        $setGroup->save();
                    }

                    $logData = [
                        'module_name'      => Module::getModuleName(Module::DATA_SETS),
                        'action'           => ActionsHistory::TYPE_MOD,
                        'action_object'    => $dataSetId,
                        'action_msg'       => 'Added dataset to group',
                    ];

                    Module::add($logData);
                }, config('app.TRANSACTION_ATTEMPTS'));

                return $this->successResponse();
            } catch (Throwable $e) {
                return $this->errorResponse(__('custom.add_datasetgroup_fail'));
            }
        }

        return $this->errorResponse(__('custom.add_datasetgroup_fail'), $validator->errors()->messages());
    }


    /**
     * API function for removing Data Set from group
     *
     * @param string api_key - required
     * @param integer data_set_uri - required
     * @param integer group_id - required
     *
     * @return json success or error
     */
    public function removeDatasetFromGroup(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'data_set_uri'  => 'required|string|max:191',
            'group_id'      => 'required|array',
            'group_id.*'    => [
                'required',
                'int',
                Rule::exists('organisations', 'id')->where(function ($query) {
                    $query->where('type', Organisation::TYPE_GROUP);
                }),
            ],
        ]);

        if (!$validator->fails()) {
            $dataSet = DataSet::where('uri', $post['data_set_uri'])->first();

            if ($dataSet) {
                try {
                    foreach ($post['group_id'] as $id) {
                        $rightCheck = RoleRight::checkUserRight(
                            Module::GROUPS,
                            RoleRight::RIGHT_EDIT,
                            [
                                'group_id' => $id
                            ],
                            [
                                'group_ids' => $post['group_id']
                            ]
                        );

                        if (!$rightCheck) {
                            return $this->errorResponse(__('custom.access_denied'));
                        }
                    }

                    if (
                        DataSetGroup::where('data_set_id', $dataSet->id)
                            ->whereIn('group_id', $post['group_id'])
                            ->delete()
                    ) {
                        $logData = [
                            'module_name'      => Module::getModuleName(Module::DATA_SETS),
                            'action'           => ActionsHistory::TYPE_MOD,
                            'action_object'    => $dataSet->id,
                            'action_msg'       => 'Removed dataset from group',
                        ];

                        Module::add($logData);

                        return $this->successResponse();
                    }
                } catch (Exception $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.remove_datasetgroup_fail'), $validator->errors()->messages());
    }

    private function checkTag($tagName, $setId)
    {
        $tag = Tags::select('id');
        $tag = Tags::checkName($tag, $tagName);
        $tag = $tag->first();

        if (empty($tag)) {
            return false;
        }

        return $tag->id;
    }

    /**
     * Function for adding tags to Data Set
     *
     * @param array $allTags - required
     * @param integer $parent - required
     * @param string $locale - required
     * @return result true or false
     */
    private function checkAndCreateTags($dataSet, $tags)
    {
        try {
            $tagIds = [];

            foreach ($tags as $tag) {
                if ($old = $this->checkTag($tag, $dataSet->id)) {
                    $tagIds[] = $old;
                } else {
                    $newTag = new Tags;
                    $newTag->name = $tag;
                    $newTag->save();

                    $tagIds[] = $newTag->id;
                }
            }

            $dataSet->tags()->sync($tagIds);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Check and create custom settings for datasets
     *
     * @param array $customFields
     * @param int   $datasetId
     *
     * @return true if successful, false otherwise
     */
    public function checkAndCreateCustomSettings($customFields, $datasetId)
    {
        if ($datasetId) {
            CustomSetting::where('data_set_id', $datasetId)->delete();

            foreach ($customFields as $field) {
                if (!empty($field['label']) && !empty($field['value'])) {
                    foreach ($field['label'] as $locale => $label) {
                        if (
                            (empty($field['label'][$locale]) && !empty($field['value'][$locale]))
                            || (!empty($field['label'][$locale]) && empty($field['value'][$locale]))

                        ) {
                            return false;
                        }
                    }

                    $saveField = new CustomSetting;
                    $saveField->data_set_id = $datasetId;
                    $saveField->created_by = \Auth::user()->id;
                    $saveField->key = $this->trans($empty, $field['label']);
                    $saveField->value = $this->trans($empty, $field['value']);

                    $saveField->save();
                } else {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Function for getting the number of DataSets a given user has
     *
     * @param string api_key - required
     * @param array criteria - required
     * @param integer id - required
     *
     * @return json result with DataSet count or error
     */
    public function getUsersDataSetCount(Request $request)
    {
        $data = $request->criteria;

        $validator = \Validator::make($data, ['id' => 'required|int|digits_between:1,10']);

        if (!$validator->fails()) {
            $sets = DataSet::where('created_by', $data['id'])
                ->where('visibility', DataSet::VISIBILITY_PUBLIC)
                ->where('status', DataSet::STATUS_PUBLISHED)
                ->where('org_id', null);

            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_VIEW
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                $count = $sets->count();

                return $this->successResponse(['count' => $count], true);
            } catch (Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.get_user_count_fail'), $validator->errors()->messages());
    }
}
