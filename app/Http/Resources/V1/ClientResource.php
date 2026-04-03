<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_url,
            'primary_colour' => $this->primary_colour,
            'secondary_colour' => $this->secondary_colour,
            'accent_colour' => $this->accent_colour,
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'website' => $this->website,
            'report_footer_text' => $this->report_footer_text,
            'active' => $this->active,
            'projects_count' => $this->when($this->projects_count !== null, $this->projects_count),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
