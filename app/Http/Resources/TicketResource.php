<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            // 'user' => $this->whenLoaded('user', function () {
            //     return [
            //         'id' => $this->user->id,
            //         'name' => $this->user->first_name . ' ' . $this->user->last_name,
            //         'email' => $this->user->email,
            //         'type' => $this->user->type,
            //     ];
            // }),
            'assigned_admin' => $this->when(
                $this->relationLoaded('assignedAdmin') && $this->assignedAdmin,
                function () {
                    return [
                        'id' => $this->assignedAdmin->id,
                        'name' => $this->assignedAdmin->name ?? 'Admin',
                        'email' => $this->assignedAdmin->email,
                    ];
                }
            ),
            'messages_count' => (int) ($this->messages_count ?? 0),
            'messages' => $this->whenLoaded('messages', function () {
                return $this->messages ? TicketMessageResource::collection($this->messages) : [];
            }),
            'latest_message' => $this->when(
                $this->relationLoaded('latestMessage') && $this->latestMessage,
                function () {
                    return [
                        'id' => $this->latestMessage->id,
                        'message' => $this->latestMessage->message,
                        'created_at' => $this->latestMessage->created_at->format('Y-m-d H:i:s'),
                        'created_at_human' => $this->latestMessage->created_at->diffForHumans(),
                    ];
                }
            ),
            'closed_at' => $this->closed_at ? $this->closed_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'created_at_human' => $this->created_at ? $this->created_at->diffForHumans() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
