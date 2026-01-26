<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'is_group' => $this->is_group,
            'users_count' => $this->users()->count(),
            'unread_count' => $this->messages()
                ->whereDoesntHave('deletedByUsers', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->where('user_id', '!=', auth()->id())
                ->where('is_read', false)
                ->count(),
            'last_message' => new MessageResource($this->messages()->latest()->first()),
            'last_message_at' => $this->last_message_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
