<?php

namespace App\Http\Controllers;

use App\Role;
use App\ActionsHistory;
use App\Module;
use App\Organisation;
use App\DataSet;
use App\Resource;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\TagController as ApiTag;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\SignalController as ApiSignal;
use Illuminate\Http\Request;

class OrganisationController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Loads a view for browsing organisations
     *
     * @param Request $request
     *
     * @return view for browsing organisations
     */
    public function list(Request $request)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        // list organisation types
        $rq = Request::create('/api/listOrganisationTypes', 'GET', ['locale' => $locale]);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisationTypes($rq)->getData();
        $orgTypes = $result->success ? $result->types : [];

        $getParams = [];

        // apply type filter
        if ($request->filled('type')) {
            $getParams['type'] = $request->type;
        } else {
            $getParams['type'] = !empty($orgTypes) ? array_first($orgTypes)->id : '';
        }

        $perPage = 6;
        $params = [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
            'criteria'         => [
                'active'   => Organisation::ACTIVE_TRUE,
                'approved' => Organisation::APPROVED_TRUE,
                'type'     => $getParams['type'],
                'locale'   => $locale
            ]
        ];

        // apply search
        if ($request->filled('q') && !empty(trim($request->q))) {
            $getParams['q'] = trim($request->q);
            $params['criteria']['keywords'] = $getParams['q'];
        }

        // apply sort parameters
        if ($request->has('sort')) {
            $params['criteria']['order']['field'] = $request->sort;
            $getParams['sort'] = $request->sort;
            if ($request->has('order')) {
                $params['criteria']['order']['type'] = $request->order;
                $getParams['order'] = $request->order;
            }
        }

        // list organisations
        $rq = Request::create('/api/listOrganisations', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisations($rq)->getData();

        $organisations = !empty($result->organisations) ? $result->organisations : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $paginationData = $this->getPaginationData($organisations, $count, $getParams, $perPage);

        return view(
            'organisation.list',
            [
                'class'         => 'organisation',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'orgTypes'      => $orgTypes,
                'getParams'     => $getParams,
            ]
        );
    }

    public function view(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'org_uri' => $uri,
            'locale'  => $locale
        ];
        $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->getOrganisationDetails($rq)->getData();

        if (isset($result->success) && $result->success && !empty($result->data) &&
            $result->data->active == Organisation::ACTIVE_TRUE &&
            $result->data->approved == Organisation::APPROVED_TRUE) {

            $params = [
                'criteria'     => [
                    'org_id'   => $result->data->id,
                    'active'   => Organisation::ACTIVE_TRUE,
                    'approved' => Organisation::APPROVED_TRUE,
                    'locale'   => $locale
                ]
            ];
            $rq = Request::create('/api/listOrganisations', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->listOrganisations($rq)->getData();
            $childOrgs = $res->success ? $res->organisations : [];

            $parentOrg = null;
            if (isset($result->data->parent_org_id)) {
                $params = [
                    'org_id' => $result->data->parent_org_id,
                    'locale' => $locale
                ];
                $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->getOrganisationDetails($rq)->getData();
                if ($res->success) {
                    $parentOrg = $res->data;
                }
            }

            $followed = false;
            if ($user = \Auth::user()) {
                $params = [
                    'api_key' => $user->api_key,
                    'id'      => $user->id
                ];
                $rq = Request::create('/api/getUserSettings', 'POST', $params);
                $api = new ApiUser($rq);
                $res = $api->getUserSettings($rq)->getData();
                if (!empty($res->user) && !empty($res->user->follows)) {
                    $followedOgrs = array_where(array_pluck($res->user->follows, 'org_id'), function ($value, $key) {
                        return !is_null($value);
                    });
                    if (in_array($result->data->id, $followedOgrs)) {
                        $followed = true;
                    }
                }

                if (!$followed) {
                    if ($request->has('follow')) {
                        $followRq = Request::create('api/addFollow', 'POST', [
                            'api_key' => $user->api_key,
                            'user_id' => $user->id,
                            'org_id'  => $result->data->id,
                        ]);
                        $apiFollow = new ApiFollow($followRq);
                        $followResult = $apiFollow->addFollow($followRq)->getData();
                        if ($followResult->success) {
                            return back();
                        }
                    }
                } else {
                    if ($request->has('unfollow')) {
                        $followRq = Request::create('api/unFollow', 'POST', [
                            'api_key' => $user->api_key,
                            'user_id' => $user->id,
                            'org_id'  => $result->data->id,
                        ]);
                        $apiFollow = new ApiFollow($followRq);
                        $followResult = $apiFollow->unFollow($followRq)->getData();
                        if ($followResult->success) {
                            return back();
                        }
                    }
                }
            }

            return view(
                'organisation/profile',
                [
                    'class'        => 'organisation',
                    'organisation' => $result->data,
                    'childOrgs'    => $childOrgs,
                    'parentOrg'    => $parentOrg,
                    'followed'     => $followed
                ]
            );
        }

        return redirect()->back();
    }

    public function datasets(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        // get organisation details
        $params = [
            'org_uri' => $uri,
            'locale'  => $locale
        ];
        $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->getOrganisationDetails($rq)->getData();

        if (isset($result->success) && $result->success && !empty($result->data) &&
            $result->data->active == Organisation::ACTIVE_TRUE &&
            $result->data->approved == Organisation::APPROVED_TRUE) {

            $criteria = [
                'org_ids' => [$result->data->id]
            ];

            // filters
            $categories = [];
            $tags = [];
            $formats = [];
            $termsOfUse = [];
            $getParams = [];
            $display = [];

            // main categories filter
            if ($request->filled('category') && is_array($request->category)) {
                $criteria['category_ids'] = $request->category;
                $getParams['category'] = $request->category;
            } else {
                $getParams['category'] = [];
            }

            // tags filter
            if ($request->filled('tag') && is_array($request->tag)) {
                $criteria['tag_ids'] = $request->tag;
                $getParams['tag'] = $request->tag;
            } else {
                $getParams['tag'] = [];
            }

            // data formats filter
            if ($request->filled('format') && is_array($request->format)) {
                $criteria['formats'] = array_map('strtoupper', $request->format);
                $getParams['format'] = $request->format;
            } else {
                $getParams['format'] = [];
            }

            // terms of use filter
            if ($request->filled('license') && is_array($request->license)) {
                $criteria['terms_of_use_ids'] = $request->license;
                $getParams['license'] = $request->license;
            } else {
                $getParams['license'] = [];
            }

            // prepare datasets parameters
            $perPage = 6;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'criteria'         => $criteria,
            ];
            $params['criteria']['locale'] = $locale;
            $params['criteria']['status'] = DataSet::STATUS_PUBLISHED;
            $params['criteria']['visibility'] = DataSet::VISIBILITY_PUBLIC;

            // apply search
            if ($request->filled('q') && !empty(trim($request->q))) {
                $getParams['q'] = trim($request->q);
                $params['criteria']['keywords'] = $getParams['q'];
            }

            // apply sort parameters
            if ($request->has('sort')) {
                $getParams['sort'] = $request->sort;
                if ($request->sort != 'relevance') {
                    $params['criteria']['order']['field'] = $request->sort;
                    if ($request->has('order')) {
                        $params['criteria']['order']['type'] = $request->order;
                    }
                    $getParams['order'] = $request->order;
                }
            }

            // list datasets
            $rq = Request::create('/api/listDatasets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDatasets($rq)->getData();

            $datasets = !empty($res->datasets) ? $res->datasets : [];
            $count = !empty($res->total_records) ? $res->total_records : 0;

            $paginationData = $this->getPaginationData($datasets, $count, $getParams, $perPage);

            $followed = [];

            if (!empty($paginationData['items'])) {
                $recordsLimit = 10;

                // check for category records limit
                $hasLimit = !($request->filled('category_limit') && $request->category_limit == 0);

                // list data categories
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria,
                        'locale' => $locale
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataCategories', 'POST', $params);
                $api = new ApiCategory($rq);
                $res = $api->listDataCategories($rq)->getData();

                $categories = !empty($res->categories) ? $res->categories : [];
                $getParams['category'] = array_intersect($getParams['category'], array_pluck($categories, 'id'));

                $this->prepareDisplayParams(count($categories), $hasLimit, $recordsLimit, 'category', $display);

                // check for tag records limit
                $hasLimit = !($request->filled('tag_limit') && $request->tag_limit == 0);

                // list data tags
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataTags', 'POST', $params);
                $api = new ApiTag($rq);
                $res = $api->listDataTags($rq)->getData();

                $tags = !empty($res->tags) ? $res->tags : [];
                $getParams['tag'] = array_intersect($getParams['tag'], array_pluck($tags, 'id'));

                $this->prepareDisplayParams(count($tags), $hasLimit, $recordsLimit, 'tag', $display);

                // check for format records limit
                $hasLimit = !($request->filled('format_limit') && $request->format_limit == 0);

                // list data formats
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria,
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataFormats', 'POST', $params);
                $api = new ApiResource($rq);
                $res = $api->listDataFormats($rq)->getData();

                $formats = !empty($res->data_formats) ? $res->data_formats : [];
                $getParams['format'] = array_intersect($getParams['format'], array_map('strtolower', array_pluck($formats, 'format')));

                $this->prepareDisplayParams(count($formats), $hasLimit, $recordsLimit, 'format', $display);

                // check for terms of use records limit
                $hasLimit = !($request->filled('license_limit') && $request->license_limit == 0);

                // list data terms of use
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria,
                        'locale' => $locale
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataTermsOfUse', 'POST', $params);
                $api = new ApiTermsOfUse($rq);
                $res = $api->listDataTermsOfUse($rq)->getData();

                $termsOfUse = !empty($res->terms_of_use) ? $res->terms_of_use : [];
                $getParams['license'] = array_intersect($getParams['license'], array_pluck($termsOfUse, 'id'));

                $this->prepareDisplayParams(count($termsOfUse), $hasLimit, $recordsLimit, 'license', $display);

                // follow / unfollow dataset
                if ($user = \Auth::user()) {
                    // get user follows
                    $params = [
                        'api_key' => $user->api_key,
                        'id'      => $user->id
                    ];
                    $rq = Request::create('/api/getUserSettings', 'POST', $params);
                    $api = new ApiUser($rq);
                    $res = $api->getUserSettings($rq)->getData();
                    if (!empty($res->user) && !empty($res->user->follows)) {
                        $followed = array_where(array_pluck($res->user->follows, 'dataset_id'), function ($value, $key) {
                            return !is_null($value);
                        });
                    }

                    $datasetIds = array_pluck($paginationData['items'], 'id');

                    if ($request->has('follow')) {
                        // follow dataset
                        if (in_array($request->follow, $datasetIds) && !in_array($request->follow, $followed)) {
                            $followRq = Request::create('api/addFollow', 'POST', [
                                'api_key' => $user->api_key,
                                'user_id' => $user->id,
                                'data_set_id' => $request->follow,
                            ]);
                            $apiFollow = new ApiFollow($followRq);
                            $followResult = $apiFollow->addFollow($followRq)->getData();
                            if ($followResult->success) {
                                return back();
                            }
                        }
                    } elseif ($request->has('unfollow')) {
                        // unfollow dataset
                        if (in_array($request->unfollow, $datasetIds) && in_array($request->unfollow, $followed)) {
                            $followRq = Request::create('api/unFollow', 'POST', [
                                'api_key' => $user->api_key,
                                'user_id' => $user->id,
                                'data_set_id' => $request->unfollow,
                            ]);
                            $apiFollow = new ApiFollow($followRq);
                            $followResult = $apiFollow->unFollow($followRq)->getData();
                            if ($followResult->success) {
                                return back();
                            }
                        }
                    }
                }
            }

            return view(
                'organisation/datasets',
                [
                    'class'              => 'organisation',
                    'organisation'       => $result->data,
                    'approved'           => ($result->data->type == Organisation::TYPE_COUNTRY),
                    'datasets'           => $paginationData['items'],
                    'resultsCount'       => $count,
                    'pagination'         => $paginationData['paginate'],
                    'categories'         => $categories,
                    'tags'               => $tags,
                    'formats'            => $formats,
                    'termsOfUse'         => $termsOfUse,
                    'getParams'          => $getParams,
                    'display'            => $display,
                    'followed'           => $followed
                ]
            );
        }

        return redirect()->back();
    }

    private function prepareDisplayParams($count, $hasLimit, $recordsLimit, $type, &$display)
    {
        if ($hasLimit && $count >= $recordsLimit) {
            $display['show_all'][$type] = true;
            $display['only_popular'][$type] = false;
        } elseif ($count > $recordsLimit) {
            $display['show_all'][$type] = false;
            $display['only_popular'][$type] = true;
        } else {
            $display['show_all'][$type] = false;
            $display['only_popular'][$type] = false;
        }
    }

    public function viewDataset(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        // get dataset details
        $params = [
            'dataset_uri' => $uri,
            'locale'  => $locale
        ];
        $rq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSet($rq);
        $res = $api->getDataSetDetails($rq)->getData();
        $dataset = !empty($res->data) ? $this->getModelUsernames($res->data) : [];

        if (!empty($dataset) && isset($dataset->org_id) &&
            $dataset->status == DataSet::STATUS_PUBLISHED &&
            $dataset->visibility == DataSet::VISIBILITY_PUBLIC) {

            // get organisation details
            $params = [
                'org_id' => $dataset->org_id,
                'locale'  => $locale
            ];
            $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->getOrganisationDetails($rq)->getData();
            $organisation = !empty($res->data) ? $res->data : [];

            if (!empty($organisation) &&
                $organisation->active == Organisation::ACTIVE_TRUE &&
                $organisation->approved == Organisation::APPROVED_TRUE) {

                $params = [
                    'criteria' => [
                        'dataset_uri' => $uri
                    ]
                ];
                $rq = Request::create('/api/listResources', 'POST', $params);
                $apiResources = new ApiResource($rq);
                $res = $apiResources->listResources($rq)->getData();
                $resources = !empty($res->resources) ? $res->resources : [];

                // get category details
                if (!empty($dataset->category_id)) {
                    $params = [
                        'category_id' => $dataset->category_id,
                        'locale'  => $locale
                    ];
                    $rq = Request::create('/api/getMainCategoryDetails', 'POST', $params);
                    $api = new ApiCategory($rq);
                    $res = $api->getMainCategoryDetails($rq)->getData();

                    $dataset->category_name = isset($res->category) && !empty($res->category) ? $res->category->name : '';
                }

                // get terms of use details
                if (!empty($dataset->terms_of_use_id)) {
                    $params = [
                        'terms_id' => $dataset->terms_of_use_id,
                        'locale'  => $locale
                    ];
                    $rq = Request::create('/api/getTermsOfUseDetails', 'POST', $params);
                    $api = new ApiTermsOfUse($rq);
                    $res = $api->getTermsOfUseDetails($rq)->getData();

                    $dataset->terms_of_use_name = isset($res->data) && !empty($res->data) ? $res->data->name : '';
                }

                return view(
                    'organisation/viewDataset',
                    [
                        'class'          => 'organisation',
                        'organisation'   => $organisation,
                        'approved'       => ($organisation->type == Organisation::TYPE_COUNTRY),
                        'dataset'        => $dataset,
                        'resources'      => $resources
                    ]
                );
            }
        }

        return redirect()->back();
    }

    public function resourceView(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'resource_uri' => $uri,
            'locale'  => $locale
        ];
        $rq = Request::create('/api/getResourceMetadata', 'POST', $params);
        $api = new ApiResource($rq);
        $res = $api->getResourceMetadata($rq)->getData();
        $resource = !empty($res->resource) ? $this->getModelUsernames($res->resource) : [];

        if (!empty($resource) && isset($resource->dataset_uri)) {
            // get dataset details
            $params = [
                'dataset_uri' => $resource->dataset_uri,
                'locale'  => $locale
            ];
            $rq = Request::create('/api/getDataSetDetails', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->getDataSetDetails($rq)->getData();
            $dataset = !empty($res->data) ? $res->data : [];

            if (!empty($dataset) && isset($dataset->org_id) &&
                $dataset->status == DataSet::STATUS_PUBLISHED &&
                $dataset->visibility == DataSet::VISIBILITY_PUBLIC) {

                // get organisation details
                $params = [
                    'org_id' => $dataset->org_id,
                    'locale'  => $locale
                ];
                $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->getOrganisationDetails($rq)->getData();
                $organisation = !empty($res->data) ? $res->data : [];

                if (!empty($organisation) &&
                    $organisation->active == Organisation::ACTIVE_TRUE &&
                    $organisation->approved == Organisation::APPROVED_TRUE) {

                    // set resource format code
                    $resource->format_code = Resource::getFormatsCode($resource->file_format);

                    // get resource data
                    $rq = Request::create('/api/getResourceData', 'POST', ['resource_uri' => $resource->uri]);
                    $api = new ApiResource($rq);
                    $res = $api->getResourceData($rq)->getData();
                    $data = !empty($res->data) ? $res->data : [];

                    $userData = [];
                    if (\Auth::check()) {
                        $userData['firstname'] =  \Auth::user()->firstname;
                        $userData['lastname'] =  \Auth::user()->lastname;
                        $userData['email'] =  \Auth::user()->email;
                    }

                    return view(
                        'organisation/resourceView',
                        [
                            'class'          => 'organisation',
                            'organisation'   => $organisation,
                            'approved'       => ($organisation->type == Organisation::TYPE_COUNTRY),
                            'dataset'        => $dataset,
                            'resource'       => $resource,
                            'data'           => $data,
                            'userData'       => $userData
                        ]
                    );
                }
            }
        }

        return redirect()->back();
    }

    /**
     * Send signal for resource
     *
     * @param Request $request
     *
     * @return json response with result
     */
    public function sendSignal(Request $request)
    {
        $params = [
            'data' => $request->only(['resource_id', 'firstname', 'lastname', 'email', 'description'])
        ];
        $sendRequest = Request::create('api/sendSignal', 'POST', $params);
        $api = new ApiSignal($sendRequest);
        $result = $api->sendSignal($sendRequest)->getData();

        return json_encode($result);
    }

    public function chronology(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'org_uri' => $uri,
            'locale'  => $locale
        ];

        $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->getOrganisationDetails($rq)->getData();

        if (isset($result->success) && $result->success && !empty($result->data) &&
            $result->data->active == Organisation::ACTIVE_TRUE &&
            $result->data->approved == Organisation::APPROVED_TRUE) {

            $params = [
                'criteria' => [
                    'org_ids' => [$result->data->id],
                    'locale' => $locale
                ]
            ];
            $rq = Request::create('/api/listDatasets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDatasets($rq)->getData();

            $criteria = [
                'org_ids' => [$result->data->id]
            ];

            $objType = Module::getModuleName(Module::ORGANISATIONS);
            $actObjData[$objType] = [];
            $actObjData[$objType][$result->data->id] = [
                'obj_id'        => $result->data->uri,
                'obj_name'      => $result->data->name,
                'obj_module'    => ultrans('custom.organisations'),
                'obj_type'      => 'org',
                'obj_view'      => '/organisation/profile/'. $result->data->uri,
                'parent_obj_id' => ''
            ];

            if (isset($res->success) && $res->success && !empty($res->datasets)) {
                $objType = Module::getModuleName(Module::DATA_SETS);
                $objTypeRes = Module::getModuleName(Module::RESOURCES);
                $actObjData[$objType] = [];

                foreach ($res->datasets as $dataset) {
                    $criteria['dataset_ids'][] = $dataset->id;
                    $actObjData[$objType][$dataset->id] = [
                        'obj_id'        => $dataset->uri,
                        'obj_name'      => $dataset->name,
                        'obj_module'    => ultrans('custom.dataset'),
                        'obj_type'      => 'dataset',
                        'obj_view'      => '/data/view/'. $dataset->uri,
                        'parent_obj_id' => ''
                    ];

                    if (!empty($dataset->resource)) {
                        foreach ($dataset->resource as $resource) {
                            $criteria['resource_uris'][] = $resource->uri;
                            $actObjData[$objTypeRes][$resource->uri] = [
                                'obj_id'            => $resource->uri,
                                'obj_name'          => $resource->name,
                                'obj_module'        => ultrans('custom.resource'),
                                'obj_type'          => 'resource',
                                'obj_view'          => '/data/resourceView/'. $resource->uri,
                                'parent_obj_id'     => $dataset->uri,
                                'parent_obj_name'   => $dataset->name,
                                'parent_obj_module' => ultrans('custom.dataset'),
                                'parent_obj_type'   => 'dataset',
                                'parent_obj_view'   => '/data/view/'. $dataset->uri
                            ];
                        }
                    }
                }
            }

            $paginationData = [];
            $actTypes = [];

            if (!empty($criteria)) {
                $rq = Request::create('/api/listActionTypes', 'GET', ['locale' => $locale, 'publicOnly' => true]);
                $api = new ApiActionsHistory($rq);
                $res = $api->listActionTypes($rq)->getData();

                if ($res->success && !empty($res->types)) {
                    $linkWords = ActionsHistory::getTypesLinkWords();
                    foreach ($res->types as $type) {
                        $actTypes[$type->id] = [
                            'name'     => $type->name,
                            'linkWord' => $linkWords[$type->id]
                        ];
                    }

                    $criteria['actions'] = array_keys($actTypes);
                    $perPage = 10;
                    $params = [
                        'criteria'         => $criteria,
                        'records_per_page' => $perPage,
                        'page_number'      => !empty($request->page) ? $request->page : 1,
                    ];

                    $rq = Request::create('/api/listActionHistory', 'POST', $params);
                    $api = new ApiActionsHistory($rq);
                    $res = $api->listActionHistory($rq)->getData();
                    $res->actions_history = isset($res->actions_history) ? $res->actions_history : [];
                    $paginationData = $this->getPaginationData($res->actions_history, $res->total_records, [], $perPage);
                }
            }

            return view(
                'organisation/chronology',
                [
                    'class'          => 'organisation',
                    'organisation'   => $result->data,
                    'chronology'     => !empty($paginationData) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes,
                ]
            );
        }

        return redirect()->back();
    }
}
