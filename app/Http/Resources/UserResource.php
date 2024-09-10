<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'role' => $this->role,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            'client' => new ClientResource($this->whenLoaded('client')),
            "photo" => $this->photo,
        ];
    }
}
