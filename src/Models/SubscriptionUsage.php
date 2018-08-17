<?php

namespace Rennokki\Chargeswarm\Models;

use Illuminate\Database\Eloquent\Model;
use Rennokki\Chargeswarm\SubscriptionBuilder;

class SubscriptionUsage extends Model {

    protected $table = 'chargebee_subscriptions_usages';
    protected $guarded = [];
    protected $casts = [
        'used' => 'float',
        'total' => 'float',
    ];

    public function subscription()
    {
        return $this->belongsTo(config('chargeswarm.models.subscription'), 'subscription_id');
    }

    public function scopeMetadata($query, $metadataId)
    {
        return $query->where('metadata_id', $metadataId);
    }

    /**
     * Get the remaining amount for this usage.
     *
     * @return float
     */
    public function remaining()
    {
        return (float) ($this->total - $this->used);
    }
}