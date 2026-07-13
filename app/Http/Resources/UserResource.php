<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $parts = explode(' ', trim($this->name), 2);

        return [

            'id' => $this->id,

            'username' => $this->username,

            'email' => $this->email,

            'phone' => $this->phone,

            'avatar' => $this->avatar,

            'role_id' => $this->role_id,

            'name' => [

                'firstname' => $parts[0] ?? '',

                'lastname' => $parts[1] ?? '',

            ],

        ];
    }
}