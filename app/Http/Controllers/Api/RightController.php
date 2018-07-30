<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\RoleRight;

class RightController extends ApiController
{
    /**
     * API function for listing all types of rights
     *
     * @param string api_key required
     *
     * @return json wit list of rights or error
     */
    public function listRights(Request $request)
    {
        $rights = RoleRight::getRights();

        if (!empty($rights)) {
            foreach ($rights as $id => $right) {
                $result[] = [
                    'id' => $id,
                    'name' => $right,
                ];
            }

            return $this->successResponse(['rights' => $result], true);
        }

        return $this->errorResponse('Get rights data failure');
    }
}
