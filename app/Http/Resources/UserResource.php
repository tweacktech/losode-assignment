<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'is_active' => $this->is_active,
            'total_orders' => $this->orders()->count(),
            'total_spent' => (float) $this->orders()->completed()->sum('total_price'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
