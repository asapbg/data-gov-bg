<?php
namespace App\Http\Controllers\Api;

use Uuid;
use App\Module;
use App\Signal;
use App\DataSet;
use App\Resource;
use App\RoleRight;
use App\Organisation;
use App\CustomSetting;
use App\ActionsHistory;
use App\ElasticDataSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class ResourceController extends ApiController
{
    /**
     * Add resource record
     *
     * @param string api_key - required
     * @param string dataset_uri - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[description] - optional
     * @param string data[locale] - required
     * @param string data[version] - optional
     * @param string data[schema_description] - required if no schema_url|optional
     * @param string data[schema_url] - required if no schema_description|optional
     * @param int data[type] - required (1 -> File, 2 -> Hiperlink, 3 -> API)
     * @param string data[resource_url] - required if type is Hyperlink or API|optional
     * @param string data[http_rq_type] - required if type is API|optional (post, get)
     * @param string data[authentication] - required if type is API|optional
     * @param string data[http_headers] - required if type is API|optional
     * @param array data[custom_fields] - optional
     * @param string data[custom_fields][label] - optional
     * @param string data[custom_fields][value] - optional
     *
     * @return json with success or error
     */
    public function addResourceMetadata(Request $request)
    {
        $errors = [];
        $post = $request->all();
        $requestTypes = Resource::getRequestTypes();

        if (isset($post['data']['http_rq_type'])) {
            $post['data']['http_rq_type'] = strtoupper($post['data']['http_rq_type']);
        }

        $validator = \Validator::make($post, [
            'dataset_uri'   => 'required|string|exists:data_sets,uri,deleted_at,NULL',
            'data'          => 'required|array',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
        } else {
            $validator = \Validator::make($post['data'], [
                'description'          => 'nullable|max:8000',
                'locale'               => 'nullable|max:5',
                'name'                 => 'required_with:locale|max:191',
                'name.bg'              => 'required_without:locale|string|max:191',
                'version'              => 'nullable|max:15',
                'file_format'          => 'nullable|string',
                'schema_description'   => 'nullable|string|max:8000',
                'schema_url'           => 'nullable|url|max:191',
                'type'                 => 'required|int|digits_between:1,10|in:'. implode(',', array_keys(Resource::getTypes())),
                'resource_url'         => 'nullable|url|max:191|required_if:type,'. Resource::TYPE_HYPERLINK .','. Resource::TYPE_API,
                'http_rq_type'         => 'nullable|string|required_if:type,'. Resource::TYPE_API .'|in:'. implode(',', $requestTypes),
                'authentication'       => 'nullable|string|max:191',
                'http_headers'         => 'nullable|string|max:8000',
                'post_data'            => 'nullable|string|max:8000',
                'custom_fields'        => 'nullable|array',
                'custom_fields.label'  => 'nullable|string|max:191',
                'custom_fields.value'  => 'nullable|string|max:8000',
            ]);
        }

        $validator->sometimes('post_data', 'required', function($post) use ($requestTypes) {
            if (
                isset($post['data']['type'])
                && $post['data']['type'] == Resource::TYPE_API
                && isset($post['data']['http_rq_type'])
                && $post['data']['http_rq_type'] == $requestTypes[Resource::HTTP_POST]
            ) {
                return true;
            }

            return false;
        });

        if (!$validator->fails()) {
            $dataset = DataSet::where('uri', $post['dataset_uri'])->first();
            if (isset($dataset->org_id)) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::RESOURCES,
                    RoleRight::RIGHT_EDIT,
                    [
                        'org_id' => $dataset->org_id
                    ],
                    [
                        'org_id' => $dataset->org_id
                    ]
                );
            } else {
                $rightCheck = RoleRight::checkUserRight(
                    Module::RESOURCES,
                    RoleRight::RIGHT_EDIT
                );
            }

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            DB::beginTransaction();

            try {
                $dbData = [
                    'data_set_id'       => DataSet::where('uri', $post['dataset_uri'])->first()->id,
                    'name'              => $this->trans($post['data']['locale'], $post['data']['name']),
                    'descript'          => isset($post['data']['description'])
                        ? $this->trans($post['data']['locale'], $post['data']['description'])
                        : null,
                    'uri'               => Uuid::generate(4)->string,
                    'version'           => isset($post['data']['version']) ? $post['data']['version'] : 1,
                    'resource_type'     => isset($post['data']['type']) ? $post['data']['type'] : null,
                    'resource_url'      => isset($post['data']['resource_url']) ? $post['data']['resource_url'] : null,
                    'http_rq_type'      => isset($post['data']['http_rq_type']) ? array_flip($requestTypes)[$post['data']['http_rq_type']] : null,
                    'authentication'    => isset($post['data']['authentication']) ? $post['data']['authentication'] : null,
                    'post_data'         => isset($post['data']['post_data']) ? $post['data']['post_data'] : null,
                    'http_headers'      => isset($post['data']['http_headers']) ? $post['data']['http_headers'] : null,
                    'file_format'       => isset($post['data']['file_format']) ? Resource::getFormatsCode($post['data']['file_format']) : null,
                    'schema_descript'   => isset($post['data']['schema_description']) ? $post['data']['schema_description'] : null,
                    'schema_url'        => isset($post['data']['schema_url']) ? $post['data']['schema_url'] : null,
                    'is_reported'       => 0,
                ];

                if (
                    isset($data['migrated_data'])
                    && Auth::user()->username == 'migrate_data'
                ) {
                    if (!empty($data['created_by'])) {
                        $dbData['created_by'] = $data['created_by'];
                    }
                }

                $resource = Resource::create($dbData);
                $resource->searchable();

                if (!empty($post['data']['custom_fields'])) {
                    foreach ($post['data']['custom_fields'] as $fieldSet) {
                        if (!empty(array_filter($fieldSet['value']) || !empty(array_filter($fieldSet['label'])))) {
                            $customFields[] = [
                                'value' => $fieldSet['value'],
                                'label' => $fieldSet['label'],
                            ];
                        }
                    }

                    if (!empty($customFields)) {
                        if (!$this->checkAndCreateCustomSettings($customFields, $resource->id)) {
                            DB::rollback();

                            return $this->errorResponse(__('custom.add_resource_meta_fail'));
                        }
                    }
                }

                $logData = [
                    'module_name'      => Module::getModuleName(Module::RESOURCES),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $resource->uri,
                    'action_msg'       => 'Added resource metadata',
                ];

                Module::add($logData);

                DB::commit();

                return $this->successResponse(['uri' => $resource->uri]);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        } else {
            $errors = $validator->errors()->messages();
        }

        return $this->errorResponse(__('custom.add_resource_meta_fail'), $errors);
    }

    /**
     * Add data to elastic search
     *
     * @param string api_key - required
     * @param int resource_uri - required
     * @param array data - required
     *
     * @return json with success or error
     */
    public function addResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string|exists:resources,uri,deleted_at,NULL|max:191',
            'data'          => 'required|array',
        ]);

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();
            $dataset = DataSet::where('id', $resource->data_set_id);

            if (isset($dataset->org_id)) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::RESOURCES,
                    RoleRight::RIGHT_EDIT,
                    [
                        'org_id' => $dataset->org_id
                    ],
                    [
                        'org_id' => $dataset->org_id
                    ]
                );
            } else {
                $rightCheck = RoleRight::checkUserRight(
                    Module::RESOURCES,
                    RoleRight::RIGHT_EDIT
                );
            }

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            DB::beginTransaction();

            try {
                $id = $resource->id;
                $index = $resource->data_set_id;

                $elasticDataSet = ElasticDataSet::create([
                    'index'         => $index,
                    'index_type'    => ElasticDataSet::ELASTIC_TYPE,
                    'doc'           => $id .'_1',
                    'version'       => 1,
                    'resource_id'   => $id
                ]);

                \Elasticsearch::index([
                    'body'  => ['rows' => $post['data']],
                    'index' => $index,
                    'type'  => ElasticDataSet::ELASTIC_TYPE,
                    'id'    => $id .'_1',
                ]);

                DB::commit();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::RESOURCES),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $resource->uri,
                    'action_msg'       => 'Added resource data',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (\Exception $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_resource_data_fail'), $validator->errors()->messages());
    }

    /**
     * Edit resource record
     *
     * @param string api_key - required
     * @param string resource_uri - required
     * @param array data - required
     * @param string data[resource_uri] - optional
     * @param string data[name] - optional
     * @param string data[description] - optional
     * @param string data[locale] - optional
     * @param string data[version] - optional
     * @param string data[schema_description] - optional
     * @param string data[schema_url] - optional
     * @param int data[type] - optional (1 -> File, 2 -> Hiperlink, 3 -> API)
     * @param string data[resource_url] - optional if type is Hyperlink or API
     * @param string data[http_rq_type] - optional if type is API (post, get)
     * @param string data[authentication] - optional if type is API
     * @param string data[http_headers] - optional if type is API
     * @param array data[custom_fields] - optional
     * @param string data[custom_fields][label] - optional
     * @param string data[custom_fields][value] - optional
     *
     * @return json with success or error
     */
    public function editResourceMetadata(Request $request)
    {
        $post = $request->all();
        $requestTypes = Resource::getRequestTypes();

        if (isset($post['data']['http_rq_type'])) {
            $post['data']['http_rq_type'] = strtoupper($post['data']['http_rq_type']);
        }

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string|exists:resources,uri,deleted_at,NULL',
            'data'          => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($post['data'], [
                'name'                 => 'sometimes|required_with:locale|max:191',
                'name.bg'              => 'sometimes|required_without:locale|string|max:191',
                'description'          => 'nullable|max:8000',
                'file_format'          => 'sometimes|string|max:191',
                'locale'               => 'sometimes|string|required_with:data.name,data.description|max:5',
                'version'              => 'nullable|string|max:15',
                'schema_description'   => 'nullable|string|max:8000',
                'schema_url'           => 'nullable|url|max:191',
                'type'                 => 'sometimes|int|digits_between:1,10|in:'. implode(',', array_keys(Resource::getTypes())),
                'resource_url'         => 'sometimes|nullable|url|max:191|required_if:data.type,'. Resource::TYPE_HYPERLINK .','. Resource::TYPE_API,
                'http_rq_type'         => 'sometimes|nullable|string|required_if:data.type,'. Resource::TYPE_API .'|in:'. implode(',', $requestTypes),
                'authentication'       => 'sometimes|nullable|string|max:191|required_if:data.type,'. Resource::TYPE_API,
                'http_headers'         => 'sometimes|nullable|string|max:8000|required_if:data.type,'. Resource::TYPE_API,
                'post_data'            => 'sometimes|nullable|string|max:8000',
                'is_reported'          => 'sometimes|boolean',
                'custom_fields'        => 'sometimes|array',
            ]);
        }

        $custom = isset($post['data']['custom_fields']) ? $post['data']['custom_fields'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($custom, [
                'label'  => 'nullable|string',
                'value'  => 'nullable|string',
            ]);
        }

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();
            $dataset = DataSet::where('id', $resource->data_set_id);

            if (isset($dataset->org_id)) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::RESOURCES,
                    RoleRight::RIGHT_EDIT,
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
                    Module::RESOURCES,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $resource->created_by
                    ]
                );
            }

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            DB::beginTransaction();

            if (isset($post['data']['version'])) {
                $resource->version = $post['data']['version'];
            }

            if (isset($post['data']['type'])) {
                $resource->resource_type = $post['data']['type'];
            }

            if (isset($post['data']['resource_url'])) {
                $resource->resource_url = $post['data']['resource_url'];
            }

            if (isset($post['data']['http_rq_type'])) {
                $resource->http_rq_type = array_flip($requestTypes)[$post['data']['http_rq_type']];
            }

            if (isset($post['data']['authentication'])) {
                $resource->authentication = $post['data']['authentication'];
            }

            if (isset($post['data']['post_data'])) {
                $resource->post_data = $post['data']['post_data'];
            }

            if (isset($post['data']['http_headers'])) {
                $resource->http_headers = $post['data']['http_headers'];
            }

            if (isset($post['data']['file_format'])) {
                $resource->file_format = Resource::getFormatsCode($post['data']['file_format']);
            }

            if (isset($post['data']['schema_description'])) {
                $resource->schema_descript = $post['data']['schema_description'];
            }

            if (isset($post['data']['schema_url'])) {
                $resource->schema_url = $post['data']['schema_url'];
            }

            if (isset($post['data']['is_reported'])) {
                $resource->is_reported = $post['data']['is_reported'];

                if ($resource->is_reported == Resource::REPORTED_FALSE) {
                    Signal::where('resource_id', '=', $resource->id)->update(['status' => Signal::STATUS_PROCESSED]);
                }
            }

            try {
                if (isset($post['data']['name'])) {
                    $resource->name = $this->trans($post['data']['locale'], $post['data']['name']);
                }

                if (isset($post['data']['description'])) {
                    $resource->descript = $this->trans($post['data']['locale'], $post['data']['description']);
                }

                $resource->save();

                if (!empty($post['data']['custom_fields'])) {
                    foreach ($post['data']['custom_fields'] as $fieldSet) {
                        if (!empty(array_filter($fieldSet['value']) || !empty(array_filter($fieldSet['label'])))) {
                            $customFields[] = [
                                'value' => $fieldSet['value'],
                                'label' => $fieldSet['label'],
                            ];
                        }
                    }

                    if (!empty($customFields)) {
                        if (!$this->checkAndCreateCustomSettings($customFields, $resource->id)) {
                            DB::rollback();

                            return $this->errorResponse(__('custom.add_resource_meta_fail'));
                        }
                    }
                }

                $logData = [
                    'module_name'      => Module::getModuleName(Module::RESOURCES),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $resource->uri,
                    'action_msg'       => 'Edit resource metadata',
                ];

                Module::add($logData);

                DB::commit();

                return $this->successResponse();
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.edit_resource_metadata_fail'), $validator->errors()->messages());
    }

    /**
     * Update elastic search data
     *
     * @param string api_key - required
     * @param int resource_uri - required
     * @param array data - required
     *
     * @return json with success or error
     */
    public function updateResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string|exists:resources,uri,deleted_at,NULL|max:191',
            'data'          => 'required|array',
        ]);

        if (!$validator->fails()) {
            try {
                $resource = Resource::where('uri', $post['resource_uri'])->first();
                $dataset = DataSet::where('id', $resource->data_set_id);

                if (isset($dataset->org_id)) {
                    $rightCheck = RoleRight::checkUserRight(
                        Module::RESOURCES,
                        RoleRight::RIGHT_EDIT,
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
                        Module::RESOURCES,
                        RoleRight::RIGHT_EDIT,
                        [],
                        [
                            'created_by' => $resource->created_by
                        ]
                    );
                }

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                $id = $resource->id;
                $index = $resource->dataSet->id;

                $elasticDataSet = ElasticDataSet::create([
                    'index'         => $index,
                    'index_type'    => ElasticDataSet::ELASTIC_TYPE,
                    'doc'           => $id .'_'. $resource->version,
                    'version'       => $resource->version,
                    'resource_id'   => $id
                ]);

                $update = \Elasticsearch::index([
                    'body'  => ['rows' => $post['data']],
                    'index' => $index,
                    'type'  => ElasticDataSet::ELASTIC_TYPE,
                    'id'    => $id .'_'. $resource->version,
                ]);

                // update signals status after resource version update and mark resource as not reported
                Signal::where('resource_id', '=', $resource->id)->update(['status' => Signal::STATUS_PROCESSED]);
                $resource->is_reported = Resource::REPORTED_FALSE;
                $resource->save();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::RESOURCES),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $resource->uri,
                    'action_msg'       => 'Update resource data',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.update_resource_fail'), $validator->errors()->messages());
    }

    /**
     * Delete resource metadata record
     *
     * @param string api_key - required
     * @param int resource_uri - required
     * @param array data - required
     *
     * @return json with success or error
     */
    public function deleteResource(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['resource_uri' => 'required|string|exists:resources,uri,deleted_at,NULL|max:191']);

        if (!$validator->fails()) {
            try {
                $resource = Resource::where('uri', $post['resource_uri'])->first();
                $dataset = DataSet::where('id', $resource->data_set_id);

                if (isset($dataset->org_id)) {
                    $rightCheck = RoleRight::checkUserRight(
                        Module::RESOURCES,
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
                        Module::RESOURCES,
                        RoleRight::RIGHT_ALL,
                        [],
                        [
                            'created_by' => $resource->created_by
                        ]
                    );
                }

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                $resource->deleted_by = \Auth::id();
                $resource->save();
                $resource->delete();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::RESOURCES),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $post['resource_uri'],
                    'action_msg'       => 'Deleted resource',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_resource_fail'), $validator->errors()->messages());
    }

    /**
     * List resource records
     *
     * @param string api_key - optional
     * @param array criteria - required
     * @param string criteria[locale] - optional
     * @param string criteria[dataset_uri] - optional
     * @param string criteria[reported] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param int criteria[records_per_page] - optional
     * @param int criteria[page_number] - optional
     *
     * @return json with success or error
     */
    public function listResources(Request $request)
    {
        $count = 0;
        $results = [];
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria'              => 'required|array',
            'records_per_page'      => 'nullable|int|digits_between:1,10',
            'page_number'           => 'nullable|int|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($post['criteria'], [
                'locale'       => 'nullable|string|max:5',
                'resource_uri' => 'nullable|string|exists:resources,uri,deleted_at,NULL|max:191',
                'dataset_uri'  => 'nullable|string|exists:data_sets,uri,deleted_at,NULL|max:191',
                'reported'     => 'nullable|boolean',
                'order'        => 'nullable|array',
            ]);
        }

        $order = isset($post['criteria']['order']) ? $post['criteria']['order'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $query = Resource::with('DataSet');

            if (!empty($post['criteria']['dataset_uri'])) {
                $query->whereHas('DataSet', function($q) use ($post) {
                    $q->where('uri', $post['criteria']['dataset_uri']);
                });
            }

            if (!empty($post['criteria']['resource_uri'])) {
                $query->where('uri', $post['criteria']['resource_uri']);
            }

            if (!empty($post['criteria']['reported'])) {
                $query->where('is_reported', $post['criteria']['reported']);
            }

            $count = $query->count();
            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            $field = empty($request->criteria['order']['field']) ? 'created_at' : $request->criteria['order']['field'];
            $type = empty($request->criteria['order']['type']) ? 'desc' : $request->criteria['order']['type'];

            $columns = [
                'id',
                'name',
                'descript',
                'version',
                'schema_description',
                'resource_url',
                'type',
                'file_format',
                'http_rq_type',
                'schema_url',
                'reported',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
            ];

            if (isset($request->criteria['order']['field'])) {
                if (!in_array($request->criteria['order']['field'], $columns)) {
                    return $this->errorResponse(__('custom.invalid_sort_field'));
                }
            }

            $query->orderBy($field, $type);

            $locale = \LaravelLocalization::getCurrentLocale();
            $fileFormats = Resource::getFormats();
            $rqTypes = Resource::getRequestTypes();
            $types = Resource::getTypes();

            foreach ($query->get() as $result) {
                $results[] = [
                    'id'                    => $result->id,
                    'uri'                   => $result->uri,
                    'dataset_uri'           => isset($result->dataSet->uri) ? $result->dataSet->uri : null,
                    'name'                  => $result->name,
                    'description'           => $result->descript,
                    'locale'                => $locale,
                    'version'               => $result->version,
                    'schema_description'    => $result->schema_descript,
                    'schema_url'            => $result->schema_url,
                    'type'                  => $types[$result->resource_type],
                    'resource_url'          => $result->resource_url,
                    'http_rq_type'          => isset($result->http_rq_type) ? $rqTypes[$result->http_rq_type] : null,
                    'authentication'        => $result->authentication,
                    'custom_fields'         => [], // TODO
                    'file_format'           => isset($result->file_format) ? $fileFormats[$result->file_format] : null,
                    'es_id'                 => isset($resource->es_id) ? $resource->es_id : null,
                    'reported'              => $result->is_reported,
                    'created_at'            => isset($result->created_at) ? $result->created_at->toDateTimeString() : null,
                    'updated_at'            => isset($result->updated_at) ? $result->updated_at->toDateTimeString() : null,
                    'created_by'            => $result->created_by,
                    'updated_by'            => $result->updated_by,
                ];
            }

            $transFields = ['name', 'description'];

            if (in_array($field, $transFields)) {
                usort($results, function($a, $b) use ($type, $field) {
                    return strtolower($type) == 'asc'
                        ? strcmp($a[$field], $b[$field])
                        : strcmp($b[$field], $a[$field]);
                });
            }

            return $this->successResponse(['resources' => $results, 'total_records' => $count], true);
        }

        return $this->errorResponse(__('custom.list_resources_fail'), $validator->errors()->messages());
    }

    /**
     * Get resource metadata
     *
     * @param string api_key - optional
     * @param string resource_uri - required
     * @param string locale - optional
     *
     * @return json with success or error
     */
    public function getResourceMetadata(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string|exists:resources,uri,deleted_at,NULL|max:191',
            'locale'        => 'nullable|string|max:5',
        ]);

        if (!$validator->fails()) {
            $resource = Resource::with('DataSet')->with('customFields')->where('uri', $post['resource_uri'])->first();
            $fileFormats = Resource::getFormats();
            $rqTypes = Resource::getRequestTypes();
            $types = Resource::getTypes();

            if ($resource) {
                $data = [
                    'id'                    => $resource->id,
                    'uri'                   => $resource->uri,
                    'dataset_uri'           => $resource->dataSet->uri,
                    'name'                  => $resource->name,
                    'description'           => $resource->descript,
                    'locale'                => \LaravelLocalization::getCurrentLocale(),
                    'version'               => $resource->version,
                    'schema_description'    => $resource->schema_descript,
                    'schema_url'            => $resource->schema_url,
                    'type'                  => $types[$resource->resource_type],
                    'resource_type'         => $resource->resource_type,
                    'resource_url'          => $resource->resource_url,
                    'http_rq_type'          => isset($resource->http_rq_type) ? $rqTypes[$resource->http_rq_type] : null,
                    'authentication'        => $resource->authentication,
                    'http_headers'          => $resource->http_headers,
                    'post_data'             => $resource->post_data,
                    'file_format'           => isset($resource->file_format) ? $fileFormats[$resource->file_format] : null,
                    'es_id'                 => isset($resource->es_id) ? $resource->es_id : null,
                    'reported'              => $resource->is_reported,
                    'created_at'            => isset($resource->created_at) ? $resource->created_at->toDateTimeString() : null,
                    'created_by'            => $resource->created_by,
                    'updated_at'            => isset($resource->updated_at) ? $resource->updated_at->toDateTimeString() : null,
                    'updated_by'            => $resource->updated_by,
                ];

                $customSett = $resource->customFields()->get()->loadTranslations();
                if (!empty($customSett)) {
                    foreach ($customSett as $sett) {
                        $data['custom_settings'][] = [
                            'key'   => $sett->key,
                            'value' => $sett->value,
                        ];
                    }
                }

                $allSignals = [];
                if ($resource->is_reported) {
                    $signals = $resource->signal()->where('status', Signal::STATUS_NEW)->get();

                    if ($signals) {
                        foreach ($signals as $signal) {
                            $allSignals[] =
                                [
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

                $data['signals'] = $allSignals;

                // get resource versions
                $versionsList = [];
                $versions = $resource->elasticDataSet()->get();
                if ($versions) {
                    foreach ($versions as $row) {
                        $versionsList[] = $row->version;
                    }
                }

                $data['versions_list'] = $versionsList;

                return $this->successResponse(['resource' => $data], true);
            }
        }

        return $this->errorResponse(__('custom.get_resource_metadata_fail'), $validator->errors()->messages());
    }

    /**
     * Get description schema of a given resource
     *
     * @param string api_key - optional
     * @param string resource_uri - required
     *
     * @return json with success or error
     */
    public function getResourceSchema(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['resource_uri' => 'required|string|exists:resources,uri,deleted_at,NULL|max:191']);

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();

            if ($resource) {
                $definition = isset($resource->schema_descript) ? $resource->schema_descript : $resource->schema_url;

                return $this->successResponse(['schema_definition' => $definition], true);
            }
        }

        return $this->errorResponse(__('custom.get_resource_schema_fail'), $validator->errors()->messages());
    }

    /**
     * Get a view of a given resource
     *
     * @param string api_key - optional
     * @param string resource_uri - required
     *
     * @return json with success or error
     */
    public function getResourceView(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['resource_uri' => 'required|string|exists:resources,uri,deleted_at,NULL|max:191']);

        if (!$validator->fails()) {
            // TODO tool
            return $this->successResponse(['view' => 'html'], true);
        }

        return $this->errorResponse(__('custom.get_resource_view_fail'), $validator->errors()->messages());
    }

    /**
     * Get elastic search data of a given resource
     *
     * @param string api_key - optional
     * @param string resource_uri - required
     * @param int version - optional
     *
     * @return json with success or error
     */
    public function getResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string|exists:resources,uri,deleted_at,NULL|max:191',
            'version'       => 'sometimes|int',
        ]);

        if (!$validator->fails()) {
            try {
                $resource = Resource::where('uri', $post['resource_uri'])->first();
                $version = !is_null($request->offsetGet('version')) ? $request->offsetGet('version') : $resource->version;

                return $this->successResponse(
                    ($resource->resource_type == Resource::TYPE_HYPERLINK)
                    ? []
                    : ElasticDataSet::getElasticData($resource->id, $version)
                );
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.get_resource_data_fail'), $validator->errors()->messages());
    }

    /**
     * Search elastic search data
     *
     * @param string api_key - optional
     * @param string keywords - required
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param int records_per_page - optional
     * @param int page_number - optional
     *
     * @return json with results or error
     */
    public function searchResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria'              => 'required|array',
            'records_per_page'      => 'nullable|int|digits_between:1,10',
            'page_number'           => 'nullable|int|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($post['criteria'], [
                'keywords'     => 'required|string|max:191',
                'order'        => 'nullable|array',
            ]);
        }

        $order = isset($post['criteria']['order']) ? $post['criteria']['order'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $pageNumber = !empty($post['page_number']) ? $post['page_number'] : 1;
            $recordsPerPage = $this->getRecordsPerPage($request->offsetGet('records_per_page'));
            $orderType = isset($post['criteria']['order']['type']) ? $post['criteria']['order']['type'] : null;
            $orderField = isset($post['criteria']['order']['field']) ? $post['criteria']['order']['field'] : null;
            $keywords = array_map(function($element) { return '*'. $element .'*'; }, explode(' ', $post['criteria']['keywords']));
            $orderJson = isset($orderType) && isset($orderField)
                ? '"sort": [
                        {
                            "'. $orderField .'": {
                                "order": "'. $orderType .'"
                            }
                        }
                    ],
                '
                : '';

            try {
                $data = \Elasticsearch::search([
                    'body'  => '{
                        "size": '. $recordsPerPage .',
                        "from": '. ($pageNumber * $recordsPerPage - $recordsPerPage + 1) .',
                        '. $orderJson .'
                        "query": {
                            "query_string": {
                                "query": "'. implode(' ', $keywords) .'"
                            }
                        }
                    }',
                ]);

                if (!empty($data['hits'])) {
                    $data = array_merge(['page_number' => $pageNumber], $data['hits']);
                }

                return $this->successResponse(['data' => isset($data['hits']) ? $data['hits'] : []], true);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.search_resource_data_fail'), $validator->errors()->messages());
    }

    /**
     * Gets linked data
     *
     * @param Request $request
     * @param string namespaces - optional
     * @param json query - required
     * @param string order[type] - optional
     * @param string order[field] - optional
     * @param string format - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with data or error response
     */
    public function getLinkedData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'namespaces'        => 'nullable|string|max:191',
            'query'             => 'required|json|max:8000',
            'order.type'        => 'nullable|string|max:191',
            'order.field'       => 'nullable|string|max:191',
            'format'            => 'nullable|string|max:191',
            'records_per_page'  => 'nullable|int|digits_between:1,10',
            'page_number'       => 'nullable|int|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $namespaces = [];
            if (isset($post['namespaces'])) {
                preg_match_all('!\d+!', $post['namespaces'], $namespaces);
            }
            $orderType = isset($post['order']['type']) ? $post['order']['type'] : null;
            $orderField = isset($post['order']['field']) ? $post['order']['field'] : null;
            $pageNumber = !empty($post['page_number']) ? $post['page_number'] : 1;
            $recordsPerPage = $this->getRecordsPerPage($request->offsetGet('records_per_page'));
            $orderJson = isset($orderType) && isset($orderField)
                ? '"sort": [
                        {
                            "'. $orderField .'": {
                                "order": "'. $orderType .'"
                            }
                        }
                    ],
                '
                : '';

            try {
                $data = \Elasticsearch::search([
                    'index' => isset($namespaces[0]) ? $namespaces[0] : null,
                    'body'  => '{
                        "size": '. $recordsPerPage .',
                        "from": '. ($pageNumber * $recordsPerPage - $recordsPerPage) .',
                        '. $orderJson .'
                        "query": '. $post['query'] .'
                    }',
                ]);

                if (!empty($data['hits'])) {
                    $data = array_merge(['page_number' => $pageNumber], $data['hits']);
                }

                return $this->successResponse(['data' => $data], true);
            } catch (\Elasticsearch\Common\Exceptions\BadRequest400Exception $ex) {
                Log::error($ex->getMessage());
                return $this->errorResponse(__('custom.link_data_fail'), ['query' => $ex->getMessage()]);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                return $this->errorResponse(__('custom.link_data_fail'));
            }
        }

        return $this->errorResponse(__('custom.link_data_fail'), $validator->errors()->messages());
    }

    /**
     * Lists the count of the datasets per format
     *
     * @param array criteria - optional
     * @param array criteria[dataset_criteria] - optional
     * @param array criteria[dataset_criteria][user_ids] - optional
     * @param array criteria[dataset_criteria][org_ids] - optional
     * @param array criteria[dataset_criteria][group_ids] - optional
     * @param array criteria[dataset_criteria][category_ids] - optional
     * @param array criteria[dataset_criteria][tag_ids] - optional
     * @param array criteria[dataset_criteria][formats] - optional
     * @param array criteria[dataset_criteria][terms_of_use_ids] - optional
     * @param boolean criteria[dataset_criteria][reported] - optional
     * @param array criteria[dataset_ids] - optional
     * @param int criteria[records_limit] - optional
     *
     * @return json response
     */
    public function listDataFormats(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria' => 'nullable|array',
        ]);

        if (!$validator->fails()) {
            $criteria = isset($post['criteria']) ? $post['criteria'] : [];
            $validator = \Validator::make($criteria, [
                'dataset_criteria'  => 'nullable|array',
                'dataset_ids'       => 'nullable|array',
                'dataset_ids.*'     => 'int|exists:data_sets,id|digits_between:1,10',
                'records_limit'     => 'nullable|int|digits_between:1,10|min:1',
            ]);
        }

        $formats = Resource::getFormats();

        if (!$validator->fails()) {
            $dsCriteria = isset($criteria['dataset_criteria']) ? $criteria['dataset_criteria'] : [];
            $validator = \Validator::make($dsCriteria, [
                'user_ids'            => 'nullable|array',
                'user_ids.*'          => 'int|digits_between:1,10|exists:users,id',
                'org_ids'             => 'nullable|array',
                'org_ids.*'           => 'int|digits_between:1,10|exists:organisations,id',
                'group_ids'           => 'nullable|array',
                'group_ids.*'         => 'int|digits_between:1,10|exists:organisations,id,type,'. Organisation::TYPE_GROUP,
                'category_ids'        => 'nullable|array',
                'category_ids.*'      => 'int|digits_between:1,10|exists:categories,id,parent_id,NULL',
                'tag_ids'             => 'nullable|array',
                'tag_ids.*'           => 'int|digits_between:1,10|exists:tags,id',
                'terms_of_use_ids'    => 'nullable|array',
                'terms_of_use_ids.*'  => 'int|digits_between:1,10|exists:terms_of_use,id',
                'formats'             => 'nullable|array|min:1',
                'formats.*'           => 'string|in:'. implode(',', $formats),
                'reported'            => 'nullable|boolean',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $data = Resource::select('file_format', DB::raw('count(distinct data_set_id, file_format) as total'));

                $data->whereHas('DataSet', function($q) use ($dsCriteria) {
                    if (!empty($dsCriteria['user_ids'])) {
                        $q->whereIn('created_by', $dsCriteria['user_ids']);
                    }
                    if (!empty($dsCriteria['org_ids'])) {
                        $q->whereIn('org_id', $dsCriteria['org_ids']);
                    }
                    if (!empty($dsCriteria['group_ids'])) {
                        $q->whereHas('DataSetGroup', function($qr) use ($dsCriteria) {
                            $qr->whereIn('group_id', $dsCriteria['group_ids']);
                        });
                    }
                    if (!empty($dsCriteria['category_ids'])) {
                        $q->whereIn('category_id', $dsCriteria['category_ids']);
                    }
                    if (!empty($dsCriteria['tag_ids'])) {
                        $q->whereHas('DataSetTags', function($qr) use ($dsCriteria) {
                            $qr->whereIn('tag_id', $dsCriteria['tag_ids']);
                        });
                    }
                    if (!empty($dsCriteria['terms_of_use_ids'])) {
                        $q->whereIn('terms_of_use_id', $dsCriteria['terms_of_use_ids']);
                    }
                    $q->where('status', DataSet::STATUS_PUBLISHED);
                    $q->where('visibility', DataSet::VISIBILITY_PUBLIC);
                });

                $fileFormats = [];
                if (!empty($dsCriteria['formats'])) {
                    foreach ($dsCriteria['formats'] as $format) {
                        $fileFormats[] = Resource::getFormatsCode($format);
                    }
                } else {
                    $fileFormats = array_flip($formats);
                }
                $data->whereIn(
                    'data_set_id',
                    DB::table('resources')->select('data_set_id')->distinct()->whereIn('file_format', $fileFormats)
                );

                if (isset($dsCriteria['reported']) && $dsCriteria['reported']) {
                    $data->whereIn(
                        'data_set_id',
                        DB::table('resources')->select('data_set_id')->distinct()->where('is_reported', Resource::REPORTED_TRUE)
                    );
                }

                if (!empty($criteria['dataset_ids'])) {
                    $data->whereIn('data_set_id', $criteria['dataset_ids']);
                }

                $data->groupBy('file_format')->orderBy('total', 'desc');

                if (!empty($criteria['records_limit'])) {
                    $data->take($criteria['records_limit']);
                }

                $data = $data->pluck('total', 'file_format')->all();

                $results = [];
                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if (isset($formats[$key])) {
                            $results[] = [
                                'format'         => $formats[$key],
                                'datasets_count' => $value,
                            ];
                        }
                    }
                }

                return $this->successResponse(['data_formats' => $results], true);

            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_data_formats_fail'), $validator->errors()->messages());
    }

    /**
     * Check if user has reported resources
     *
     * @param int user_id - required
     * @return json with results or error
     */
    public function hasReportedResource(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'user_id'   => 'required|int|exists:users,id|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            try {
                $hasReported = Resource::where('created_by', $post['user_id'])
                        ->where('is_reported', 1)->count();

                return $this->successResponse(['flag' => ($hasReported) ? true : false], true);
            } catch (Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.search_reported_fail'), $validator->errors()->messages());
    }

    public function checkAndCreateCustomSettings($customFields, $resourceId)
    {
        if (!empty($resourceId)) {
            try {
                DB::beginTransaction();

                CustomSetting::where('resource_id', $resourceId)->delete();

                foreach ($customFields as $field) {
                    if (!empty($field['label']) && !empty($field['value'])) {
                        foreach ($field['label'] as $locale => $label) {
                            if (
                                (empty($field['label'][$locale]) && !empty($field['value'][$locale]))
                                || (!empty($field['label'][$locale]) && empty($field['value'][$locale]))

                            ) {
                                DB::rollback();

                                return false;
                            }
                        }

                        $saveField = new CustomSetting;
                        $saveField->resource_id = $resourceId;
                        $saveField->created_by = \Auth::user()->id;
                        $saveField->key = $this->trans($empty, $field['label']);
                        $saveField->value = $this->trans($empty, $field['value']);

                        $saveField->save();
                    } else {
                        DB::rollback();

                        return false;
                    }
                }

                DB::commit();

                return true;
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return false;
    }
}
