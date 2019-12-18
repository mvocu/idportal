<?php
namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Services\AbstractExtSourceConnector;
use App\Models\Database\ExtSource;
use App\Http\Resources\ExtUserResource;
use Illuminate\Support\Facades\Validator;

class InternalConnector extends AbstractExtSourceConnector implements ExtSourceConnector
{

    public function findUser(ExtSource $source, $user)
    {
        return null;   
    }

    public function modifyUser(ExtUserResource $user_ext, $data)
    {
        $old = $user_ext->toArray(null);
        $new = array_replace($old, $data);
        return $this->makeResource($new, 'email');
    }

    public function listUsers(ExtSource $source)
    {
        return null;
    }

    public function getUser(ExtSource $source, $id)
    {
        return null;
    }

    public function supportsUserListing(ExtSource $source)
    {
        return false;
    }

    public function validateUpdate(ExtSource $source, $data, &$validator)
    {
        $validator = Validator::make($data, [
            'email' => 'sometimes|required|email',
            'phone_number' => 'sometimes|required|phone'
        ]);
        return $validator->passes();
    }

}

