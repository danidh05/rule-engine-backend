<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RuleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($rule) use ($request) {
                return new RuleResource($rule);
            }),
            'summary' => $this->generateSummary(),
        ];
    }

    /**
     * Generate summary statistics for the rule collection
     */
    private function generateSummary(): array
    {
        $total = $this->collection->count();
        $active = $this->collection->where('is_active', true)->count();
        $stackable = $this->collection->where('stackable', true)->count();
        
        // Group by priority levels
        $priorities = $this->collection->groupBy(function ($rule) {
            return match (true) {
                $rule->salience <= 10 => 'very_high',
                $rule->salience <= 20 => 'high',
                $rule->salience <= 30 => 'medium',
                $rule->salience <= 40 => 'low',
                default => 'very_low'
            };
        })->map->count();

        // Group by action types
        $actionTypes = $this->collection->groupBy(function ($rule) {
            return $rule->action_json['type'] ?? 'unknown';
        })->map->count();

        return [
            'total_rules' => $total,
            'active_rules' => $active,
            'inactive_rules' => $total - $active,
            'stackable_rules' => $stackable,
            'exclusive_rules' => $total - $stackable,
            'priority_distribution' => $priorities->toArray(),
            'action_type_distribution' => $actionTypes->toArray(),
            'average_salience' => $total > 0 ? round($this->collection->avg('salience'), 2) : 0,
        ];
    }

    /**
     * Get additional resource information
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
                'request_id' => $request->header('X-Request-ID', uniqid()),
            ],
            'links' => [
                'documentation' => url('/docs/api/rules'),
                'evaluate' => route('api.evaluate'),
            ],
        ];
    }
}