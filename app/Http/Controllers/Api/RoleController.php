<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Role;
use App\RoleRight;

class RoleController extends ApiController
{
    public function addRole(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'name'      => 'required|max:255',
            'active'    => 'required|boolean',
        ]);

        if (!$validator->fails()) {
            try {
                $newRole = Role::create($post);

                if ($newRole) {
                    return $this->successResponse(['id' => $newRole->id], true);
                }
            } catch (\Illuminate\Database\QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Add role failure');
    }

    public function editRole(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'id'        => 'required|max:255',
            'name'      => 'required|max:255',
            'active'    => 'required|boolean',
        ]);

        if (
            !$validator->fails()
            && Role::where('id', $post['id'])->get()->count()
        ) {
            try {
                if (Role::where('id', $post['id'])->update($post)) {
                    return $this->successResponse();
                }
            } catch (\Illuminate\Database\QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Edit role failure');
    }

    public function deleteRole(Request $request)
    {
        $id = $request->get('id');

        if (
            empty($id)
            || !Role::where('id', $id)->get()->count()
            || !Role::find($id)->delete()
        ) {
            return $this->errorResponse('Delete role failure');
        }

        return $this->successResponse();
    }

    public function listRoles(Request $request)
    {
        $validator = \Validator::make($request->all(), ['active' => 'boolean']);

        if ($validator->fails()) {
            return $this->errorResponse('Get role data failure');
        }

        try {
            if ($request->has('active')) {
                $roles = Role::where('active', $request->active)->get();
            } else {
                $roles = Role::all();
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->errorResponse($ex->getMessage());
        }

        return $this->successResponse($roles);
    }

    public function getRoleRights(Request $request)
    {
        $id = $request->get('id');

        if (
            !empty($id)
            && Role::find($id)->get()->count()
        ) {
            try {
                $role = Role::where('id', $id)->first();

                return $this->successResponse($role->rights);
            } catch (\Illuminate\Database\QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Get role rights failure');
    }

    public function modifyRoleRights(Request $request)
    {
        $post = $request->all();
        $id = $request->get('id');

        $validator = \Validator::make($post, [
            'data.*.module_name'       => 'required|max:255',
            'data.*.right'             => 'required',
            'data.*.limit_to_own_data' => 'required|boolean',
            'data.*.api'               => 'required|boolean',
        ]);

        if (
            empty($id)
            || !Role::where('id', $id)->get()->count()
            || $validator->fails()
        ) {
            $response = $this->errorResponse('No role found');
        } else {
            $role = Role::where('id', $id)->first();
            $rights = $role->rights;
            $names = $request->input('data.*.module_name');

            try {
                foreach ($rights as $right) {
                    if (!in_array($right->module_name, $names)) {
                        RoleRight::find($right->id)->delete();
                    }
                }
            } catch (\Illuminate\Database\QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }

            try {
                foreach ($post['data'] as $module) {
                    $newRight = RoleRight::updateOrCreate(
                        ['role_id' => $id, 'module_name' => $module['module_name']],
                        $module
                    );

                    $response = $newRight ? $this->successResponse() : $this->errorResponse('No role found');

                    if (empty($newRight)) {
                        break;
                    }
                }
            } catch (\Illuminate\Database\QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $response;
    }
}
