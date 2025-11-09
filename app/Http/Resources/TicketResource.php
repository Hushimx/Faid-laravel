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
            'assigned_admin' => $this->whenLoaded('assignedAdmin', function () {
                return $this->assignedAdmin ? [
                    'id' => $this->assignedAdmin->id,
                    'name' => $this->assignedAdmin->first_name . ' ' . $this->assignedAdmin->last_name,
                    'email' => $this->assignedAdmin->email,
                ] : null;
            }),
            'messages_count' => $this->whenCounted('messages'),
            'messages' => $this->whenLoaded('messages', function () {
                return TicketMessageResource::collection($this->messages);
            }),
            'latest_message' => $this->whenLoaded('latestMessage', function () {
                return $this->latestMessage ? [
                    'id' => $this->latestMessage->id,
                    'message' => $this->latestMessage->message,
                    'created_at' => $this->latestMessage->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $this->latestMessage->created_at->diffForHumans(),
                ] : null;
            }),
            'closed_at' => $this->closed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
