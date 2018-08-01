<?php

namespace App\Http\Controllers\Api;

use App\DataRequest;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use \Validator;

class DataRequestController extends ApiController
{
    /**
     * Send a request based on input
     *
     * @param array data - required
     * @param integer data[org_id] - required
     * @param string data[description] - required
     * @param string data[published_url] - optional
     * @param string data[contact_name] - optional
     * @param string data[email] - required
     * @param string data[notes] - optional
     * @param integer data[status] - optional
     *
     * @return response with request_id or error message
     */
    public function sendDataRequest(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'data'                  => 'required|array',
            'data.org_id'           => 'required|integer',
            'data.description'      => 'required|string',
            'data.published_url'    => 'nullable|string',
            'data.contact_name'     => 'nullable|string',
            'data.email'            => 'required|email',
            'data.notes'            => 'nullable|string',
            'data.status'           => 'nullable|integer',
        ]);

        if (!$validator->fails()) {

            $dataRequest = new DataRequest;
            $dataRequest->org_id = $requestData['data']['org_id'];
            $dataRequest->descript = $requestData['data']['description'];
            $dataRequest->email = $requestData['data']['email'];

            if (isset($requestData['data']['published_url'])) {
                $dataRequest->published_url = $requestData['data']['published_url'];
            }

            if (isset($requestData['data']['contact_name'])) {
                $dataRequest->contact_name = $requestData['data']['contact_name'];
            }

            if (isset($requestData['data']['notes'])) {
                $dataRequest->notes = $requestData['data']['notes'];
            }

            if (isset($requestData['data']['status'])) {
                $dataRequest->status = $requestData['data']['status'];
            } else {
                $dataRequest->status = DataRequest::NEW_DATA_REQUEST;
            }

            try {
                $dataRequest->save();
                return $this->successResponse(['request_id' => $dataRequest->id], true);
            } catch (QueryException $e) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Send request failure', $validator->errors()->messages());
    }

     /**
     * edit a request based on input
     * @param integer request_id - required
     * @param array data - required
     * @param integer data[org_id] - optional
     * @param string data[description] - optional
     * @param string data[published_url] - optional
     * @param string data[contact_name] - optional
     * @param string data[email] - optional
     * @param string data[notes] - optional
     * @param integer data[status] - optional
     *
     * @return response with request_id or error message
     */
    public function editDataRequest(Request $request)
    {
        $editRequestData = $request->all();
        $validator = Validator::make($editRequestData, [
            'request_id'            => 'required|integer|exists:data_requests,id',
            'data'                  => 'required|array',
            'data.org_id'           => 'nullable|integer',
            'data.description'      => 'nullable|string',
            'data.published_url'    => 'nullable|string',
            'data.contact_name'     => 'nullable|string',
            'data.email'            => 'nullable|email',
            'data.notes'            => 'nullable|string',
            'data.status'           => 'nullable|integer',
        ]);

        if (!$validator->fails()) {

            $requestToEdit = DataRequest::find($editRequestData['request_id']);

            if (isset($editRequestData['data']['org_id'])) {
                $requestToEdit->org_id = $editRequestData['data']['org_id'];
            }

            if (isset($editRequestData['data']['description'])) {
                $requestToEdit->descript = $editRequestData['data']['description'];
            }

            if (isset($editRequestData['data']['published_url'])) {
                $requestToEdit->published_url = $editRequestData['data']['published_url'];
            }

            if (isset($editRequestData['data']['contact_name'])) {
                $requestToEdit->contact_name = $editRequestData['data']['contact_name'];
            }

            if (isset($editRequestData['data']['email'])) {
                $requestToEdit->email = $editRequestData['data']['email'];
            }

            if (isset($editRequestData['data']['notes'])) {
                $requestToEdit->notes = $editRequestData['data']['notes'];
            }

            if (isset($editRequestData['data']['status'])) {
                $requestToEdit->status = $editRequestData['data']['status'];
            }

            try {
                $requestToEdit->save();
                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($ex->getMessage());
            }
        }

    return $this->errorResponse('Edit data request failure', $validator->errors()->messages());
    }

    /**
     * Delete a data reuqest based on id
     *
     * @param integer request_id - required
     *
     * @return response with success or error message
     */
    public function deleteDataRequest(Request $request)
    {
        $deleteRequestData = $request->all();
        $validator = Validator::make($deleteRequestData, [
            'request_id' => 'required|integer|exists:data_requests,id',
        ]);

        if (!$validator->fails()) {

            $requestToDelete = DataRequest::find($deleteRequestData['request_id']);

            try {
                $requestToDelete->delete();
                return $this->successResponse();
            } catch (QueryException $e) {

            }
        }
        return $this->errorResponse('Delete data request failure', $validator->errors()->messages());
    }

    /**
     * List data requests based on criteria
     *
     * @param integer request_id - optional
     * @param array criteria - optional
     * @param integer criteria[org_id] - optional
     * @param integer criteria[status] - optional
     * @param date criteria[date_from] - optional
     * @param date criteria[date_to] - optional
     * @param string criteria[search] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return response with success or error message
     */
    public function listDataRequests(Request $request)
    {
        $listRequestData = $request->all();
        $validator = Validator::make($listRequestData, [
            'criteria'                => 'nullable|array',
            'criteria.request_id'     => 'nullable|integer',
            'criteria.org_id'         => 'nullable|integer',
            'criteria.status'         => 'nullable|integer',
            'criteria.date_from'      => 'nullable|date',
            'criteria.date_to'        => 'nullable|date',
            'criteria.search'         => 'nullable|string',
            'criteria.order'          => 'nullable|array',
            'criteria.order.type'     => 'nullable|string',
            'criteria.order.field'    => 'nullable|string',
            'records_per_page'        => 'nullable|integer',
            'page_number'             => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('List data request failure');
        }

        $result = [];
        $criteria = $request->json('criteria');

        $dataRequestList = DataRequest::select('*');

        $orderColumns = [
            'id',
            'org_id',
            'descript',
            'published_url',
            'contact_name',
            'email',
            'notes',
            'status',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];

        if (isset($criteria['order']['field'])) {
            if (!in_array($criteria['order']['field'], $orderColumns)) {
                unset($criteria['order']['field']);
            }
        }

        if (isset($criteria['request_id'])) {
            $dataRequestList = $dataRequestList->where('request_id', $criteria['request_id']);
        }

        if (isset($criteria['org_id'])) {
            $dataRequestList = $dataRequestList->where('org_id', $criteria['org_id']);
        }

        if (isset($criteria['status'])) {
            $dataRequestList = $dataRequestList->where('status', $criteria['status']);
        }

        $total_records = $dataRequestList->count();

        if (isset($request['records_per_page']) && isset($request['page_number'])) {
            $dataRequestList = $dataRequestList->forPage($request->input('page_number'), $request->input('records_per_page'));
        }

        if (isset($criteria['date_from'])) {
            $dataRequestList = $dataRequestList->where('created_at', '>=', $criteria['date_from']);
        }

        if (isset($criteria['date_to'])) {
            $dataRequestList = $dataRequestList->where('created_at', '<=', $criteria['date_to']);
        }

        if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
            if ($criteria['order']['type'] == 'desc') {

                $dataRequestList = $dataRequestList->orderBy($criteria['order']['field'], 'desc');
            }
            if ($criteria['order']['type'] == 'asc') {
                $dataRequestList = $dataRequestList->orderBy($criteria['order']['field'], 'asc');
            }
        }

        if (isset($criteria['search'])) {
            $search = $criteria['search'];
            $dataRequestList = $dataRequestList->where('descript', 'like', '%' . $search . '%')
                ->orWhere('published_url', 'like', '%' . $search . '%')
                ->orWhere('contact_name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('notes', 'like', '%' . $search . '%');
        }

        $dataRequestList = $dataRequestList->get();

        if (!empty($dataRequestList)) {
            foreach ($dataRequestList as $singleDataRequest) {
                $result[] = [
                    'id'               => $singleDataRequest->id,
                    'org_id'           => $singleDataRequest->org_id,
                    'description'      => $singleDataRequest->descript,
                    'published_url'    => $singleDataRequest->published_url,
                    'contact_name'     => $singleDataRequest->contact_name,
                    'email'            => $singleDataRequest->email,
                    'notes'            => $singleDataRequest->notes,
                    'status'           => $singleDataRequest->status,
                    'created_at'       => date($singleDataRequest->created_at),
                    'updated_at'       => date($singleDataRequest->updated_at),
                    'created_by'       => $singleDataRequest->created_by,
                    'updated_by'       => $singleDataRequest->updated_by,
                ];
            }
        }
        return $this->successResponse([
            'total_records' => $total_records,
            'dataRequests'  => $result,
        ], true);

    }
}
