<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    private function resolveImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        return Storage::disk('public')->url($normalized);
    }

    public function toArray($request): array
    {
        return [

            'id' => $this->id,

            'username' => $this->username,

            'email' => $this->email,

            'phone' => $this->phone,

            'avatar' => $this->resolveImageUrl($this->avatar),

            'role_id' => $this->role_id,

            'is_admin' => $this->role?->name === 'ADMIN',

            'name' => $this->name,

        ];
    }
}
