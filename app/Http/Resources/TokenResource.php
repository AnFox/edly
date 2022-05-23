<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TokenResource
 * @property mixed refresh_token
 * @property mixed access_token
 * @property mixed expires_in
 * @property mixed token_type
 * @package App\Http\Resources
 */
class TokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'token_type' => $this->token_type,
            'expires_in' => $this->expires_in,
            'access_token' => $this->access_token,
            'refresh_token' => $this->refresh_token ?: '',
        ];
    }
}
