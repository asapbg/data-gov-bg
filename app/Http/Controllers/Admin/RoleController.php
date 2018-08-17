<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\RoleController as ApiRole;

class RoleController extends Controller {

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
    public function list(Request $request) {
        $class = 'user';

        $rq = Request::create('/api/listRoles', 'POST');
        $api = new ApiRole($rq);
        $result = $api->listRoles($rq)->getData();
        $roles = isset($result->roles) ? $result->roles : [];

        if ($request->has('delete')) {
            if ($this->deleteRole($request->offsetGet('id'))) {
                $request->session()->flash('alert-success', __('custom.delete_success'));
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));
            }

            return back();
        }

        return view('admin/roleList', compact('class', 'roles'));
    }

    /**
     * Show the role creation.
     *
     * @return view with inpits
     */
    public function addRole(Request $request) {
        $class = 'user';
        $errors = [];

        if ($request->has('save')) {
            $rq = Request::create('/api/addRole', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'data'      => [
                    'name'      => $request->offsetGet('name'),
                    'active'    => $request->offsetGet('active') ? $request->offsetGet('active') : false,
                ],
            ]);
            $api = new ApiRole($rq);
            $result = $api->addRole($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return redirect(url('admin/roles'));
            } else {
                $errors = $result->errors;
                $request->session()->flash('alert-danger', __('custom.edit_error'));
            }

            return back()->withInput()->withErrors($errors);
        }

        return view('admin/roleAdd', compact('class', 'roles'));
    }

    /**
     * Show edit role.
     *
     * @return view with inpits
     */
    public function editRole(Request $request, $id)
    {
        $class = 'user';
        $errors = [];

        $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['role_id' => $id]]);
        $api = new ApiRole($rq);
        $result = $api->listRoles($rq)->getData();
        $role = isset($result->roles) ? $result->roles : [];


        if ($request->has('edit')) {
            $rq = Request::create('/api/editRole', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'id'        => $id,
                'data'      => [
                    'name'      => $request->offsetGet('name'),
                    'active'    => $request->offsetGet('active'),
                ],
            ]);
            $api = new ApiRole($rq);
            $result = $api->editRole($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return redirect(url('admin/roles'));
            } else {
                $errors = $result->errors;
                $request->session()->flash('alert-danger', __('custom.edit_error'));
            }

            return back()->withInput()->withErrors($errors);
        }

        return view('admin/roleEdit', compact('class', 'role'));
    }

    public function deleteRole($id)
    {
        $rq = Request::create('/api/deleteRole', 'POST', [
            'api_key'   => Auth::user()->api_key,
            'id'        => $id,
        ]);
        $api = new ApiRole($rq);
        $result = $api->deleteRole($rq)->getData();

        return $result->success;
    }

}
