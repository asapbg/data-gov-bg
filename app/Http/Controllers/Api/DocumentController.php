<?php

namespace App\Http\Controllers\Api;

use \Validator;
use App\Document;
use App\Module;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class DocumentController extends ApiController
{
    /**
     * Add a document with provided data
     *
     * @param array data - required
     * @param string|array data[name] - required
     * @param string|array data[description] - required
     * @param string data[locale] - optional
     * @param string data[filename] - required
     * @param string data[mimetype] - required
     * @param string data[data] - required
     *
     * @return json response with doc id or error message
     */
    public function addDocument(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'data'              => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($post['data'], [
                'name'           => 'required_with:locale|max:191',
                'name.bg'        => 'required_without:locale|string|max:191',
                'name.*'         => 'max:191',
                'description'    => 'required_with:locale|max:8000',
                'description.bg' => 'required_without:locale|string|max:8000',
                'locale'         => 'nullable|string|max:5',
                'filename'       => 'required|string|max:191',
                'mimetype'       => 'required|string|max:191',
                'data'           => 'required|string|max:4294967295',
            ]);
        }

        if (!$validator->fails()) {
            try {
                DB::beginTransaction();

                $newDocument = new Document;
                $newDocument->name = $this->trans($post['data']['locale'], $post['data']['name']);
                $newDocument->descript = $this->trans($post['data']['locale'], $post['data']['description']);
                $newDocument->file_name = $post['data']['filename'];
                $newDocument->mime_type = $post['data']['mimetype'];
                $newDocument->data = $post['data']['data'];
                $newDocument->save();

                DB::commit();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::DOCUMENTS),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $newDocument->id,
                    'action_msg'       => 'Added new document',
                ];

                Module::add($logData);

                return $this->successResponse(['doc_id' => $newDocument->id]);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_document_fail'), $validator->errors()->messages());
    }

    /**
     * Edit document with provided data
     *
     * @param int doc_id - required
     * @param array data - required
     * @param string|array data[name] - optional
     * @param string|array data[description] - optional
     * @param string data[locale] - optional
     * @param string data[filename] - optional
     * @param string data[mimetype] - optional
     * @param string data[data] - optional
     *
     * @return json response with success or error message
     */
    public function editDocument(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'doc_id'            => 'required|integer|exists:documents,id|digits_between:1,10',
            'data'              => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($post['data'], [
                'name'         => 'nullable|max:191',
                'description'  => 'nullable|max:8000',
                'locale'       => 'nullable|string|max:5',
                'filename'     => 'nullable|string|max:191',
                'mimetype'     => 'nullable|string|max:191',
                'data'         => 'nullable|string|max:4294967295',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $editDocument = Document::find($post['doc_id']);

                DB::beginTransaction();

                if (isset($post['data']['name'])) {
                    $editDocument->name = $this->trans($post['data']['locale'], $post['data']['name'], true);
                }

                if (isset($post['data']['description'])) {
                    $editDocument->descript = $this->trans($post['data']['locale'], $post['data']['description'], true);
                }

                if (isset($post['data']['filename'])) {
                    $editDocument->file_name = $post['data']['filename'];
                }

                if (isset($post['data']['mimetype'])) {
                    $editDocument->mime_type = $post['data']['mimetype'];
                }

                if (isset($post['data']['data'])) {
                    $editDocument->data = $post['data']['data'];
                }

                $editDocument->save();

                DB::commit();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::DOCUMENTS),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $editDocument->id,
                    'action_msg'       => 'Edited a document',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $e) {
                DB::rollback();

                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.edit_document_fail'), $validator->errors()->messages());
    }

    /**
     * Delete a document based on ID
     *
     * @param int doc_id - required
     *
     * @return json response with success or error message
     */
    public function deleteDocument(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'doc_id' => 'required|integer|exists:documents,id|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $deleteDocument = Document::find($post['doc_id']);

            try {
                $deleteDocument->delete();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::DOCUMENTS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $post['doc_id'],
                    'action_msg'       => 'Deleted document',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
            return $this->errorResponse(__('custom.delete_document_fail'));
        }

        return $this->errorResponse(__('custom.delete_document_fail'), $validator->errors()->messages());
    }

    /**
     * List documents based on search criteria
     *
     * @param array criteria - optional
     * @param integer criteria[doc_id] - optional
     * @param date criteria[date_from] - optional
     * @param date criteria[date_to] - optional
     * @param string criteria[locale] - optional
     * @param string criteria[date_type] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with doc list or error message
     */
    public function listDocuments(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'criteria'              => 'nullable|array',
            'records_per_page'      => 'nullable|integer|digits_between:1,10',
            'page_number'           => 'nullable|integer|digits_between:1,10',
        ]);

        $criteria = isset($post['criteria']) ? $post['criteria'] : [];
        if (!$validator->fails()) {
            $validator = Validator::make($criteria, [
                'doc_id'       => 'nullable|integer|digits_between:1,10',
                'date_from'    => 'nullable|date',
                'date_to'      => 'nullable|date',
                'locale'       => 'nullable|string|max:5',
                'date_type'    => 'nullable|string|max:191',
                'order'        => 'nullable|array',
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.list_document_fail'), $validator->errors()->messages());
        }

        $result = [];

        $columns = [
            'id',
            'name',
            'descript',
            'file_name',
            'mime_type',
            'data',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];

        $query = Document::select($columns);

        if (isset($criteria['order'])) {
            if (is_array($criteria['order'])) {
                if (!in_array($criteria['order']['field'], $columns)) {
                    unset($criteria['order']['field']);
                }
            }
        }

        if (isset($criteria['doc_id'])) {
            $query->where('id', $criteria['doc_id']);
        }

        $filterColumn = 'created_at';

        if (isset($criteria['date_type'])) {
            if (strtolower($criteria['date_type']) == Document::DATE_TYPE_UPDATED) {
                $filterColumn = 'updated_at';
            }
        }

        if (isset($criteria['date_from'])) {
            $query->where($filterColumn, '>=', $criteria['date_from']);
        }

        if (isset($criteria['date_to'])) {
            $query->where($filterColumn, '<=', $criteria['date_to']);
        }

        if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
            $query->orderBy(
                $criteria['order']['field'],
                $criteria['order']['type'] == 'asc' ? 'asc' : 'desc'
            );
        }

        $count = $query->count();
        $query->forPage(
            $request->offsetGet('page_number'),
            $this->getRecordsPerPage($request->offsetGet('records_per_page'))
        );

        $locale = \LaravelLocalization::getCurrentLocale();
        $results = [];

        foreach ($query->get() as $result) {
            $results[] = [
                'id'            => $result->id,
                'locale'        => $locale,
                'name'          => $result->name,
                'description'   => $result->descript,
                'filename'      => $result->file_name,
                'mimetype'      => $result->mime_type,
                'data'          => $result->data,
                'created_at'    => isset($result->created_at) ? $result->created_at->toDateTimeString() : null,
                'updated_at'    => isset($result->updated_at) ? $result->updated_at->toDateTimeString() : null,
                'created_by'    => $result->created_by,
                'updated_by'    => $result->updated_by,
            ];
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::DOCUMENTS),
            'action'           => ActionsHistory::TYPE_SEE,
            'action_msg'       => 'Listed documents',
        ];

        Module::add($logData);

        $transFields = ['description', 'name'];

        if ($criteria['order'] && in_array($criteria['order']['field'], $transFields)) {
            usort($results, function($a, $b) use ($criteria) {
                return strtolower($criteria['order']['type']) == 'asc'
                    ? strcmp($a[$criteria['order']['field']], $b[$criteria['order']['field']])
                    : strcmp($b[$criteria['order']['field']], $a[$criteria['order']['field']]);
            });
        }

        return $this->successResponse(
            [
                'total_records' => $count,
                'documents'     => $results
            ],
            true
        );
    }

    /**
     * Search for a list of documents based on criteria
     *
     * @param array criteria - required
     * @param integer criteria[search] - required
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with doc list or error message
     */
    public function searchDocuments(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'criteria'              => 'required|array',
            'records_per_page'      => 'nullable|integer|digits_between:1,10',
            'page_number'           => 'nullable|integer|digits_between:1,10',
        ]);

        $criteria = $post['criteria'];

        if (!$validator->fails()) {
            $validator = Validator::make($criteria, [
                'search'       => 'required|string|max:191',
                'order'        => 'nullable|array',
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $data = [];
            $criteria = $post['criteria'];
            $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
            $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';

            $orderColumns = [
                'id',
                'name',
                'descript',
                'file_name',
                'mime_type',
                'data',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
            ];

            if (isset($order['field'])) {
                if (!in_array($order['field'], $orderColumns)) {
                    unset($order['field']);
                }
            }

            $ids = Document::search($criteria['search'])->get()->pluck('id');
            $query = Document::whereIn('id', $ids);

            $count = $query->count();
            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            $query->orderBy($order['field'], $order['type']);

            $locale = \LaravelLocalization::getCurrentLocale();
            $results = [];

            foreach ($query->get() as $result) {
                $results[] = [
                    'id'            => $result->id,
                    'locale'        => $locale,
                    'name'          => $result->name,
                    'description'   => $result->descript,
                    'filename'      => $result->file_name,
                    'mimetype'      => $result->mime_type,
                    'data'          => $result->data,
                    'created_at'    => isset($result->created_at) ? $result->created_at->toDateTimeString() : null,
                    'updated_at'    => isset($result->updated_at) ? $result->updated_at->toDateTimeString() : null,
                    'created_by'    => $result->created_by,
                    'updated_by'    => $result->updated_by,
                ];
            }

            $logData = [
                'module_name'      => Module::getModuleName(Module::DOCUMENTS),
                'action'           => ActionsHistory::TYPE_SEE,
                'action_msg'       => 'Searched documents',
            ];

            Module::add($logData);

            $transFields = ['description', 'name'];

            if ($order && in_array($order['field'], $transFields)) {
                usort($results, function($a, $b) use ($criteria) {
                    return strtolower($order['type']) == 'asc'
                    ? strcmp($a[$order['field']], $b[$order['field']])
                    : strcmp($b[$order['field']], $a[$order['field']]);
                });
            }

            return $this->successResponse([
                'documents'     => $results,
                'total_records' => $count,
            ], true);
        }

        return $this->errorResponse(__('custom.search_document_fail'), $validator->errors()->messages());
    }
}
