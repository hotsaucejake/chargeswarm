<?php

namespace Rennokki\Chargeswarm\Models;

use Illuminate\Database\Eloquent\Model;
use Chargebee_Invoice as ChargebeeInvoice;
use ChargeBee_Environment as ChargebeeEnvironment;

class Invoice extends Model
{
    protected $table = 'chargebee_invoices';
    protected $guarded = [];
    public $incrementing = false;

    public function model()
    {
        return $this->morphTo();
    }

    public function subscription()
    {
        return $this->belongsTo(config('chargeswarm.models.subscription'), 'subscription_id');
    }

    /**
     * Get the the invoice.
     *
     * @return Chargebee_Invoice
     */
    public function retrieve()
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        $invoice = ChargebeeInvoice::retrieve($this->id);

        return $invoice->invoice();
    }

    /**
     * Get the download link of the invoice.
     *
     * @return Chargebee_Download
     */
    public function downloadLink()
    {
        ChargebeeEnvironment::configure((getenv('CHARGEBEE_SITE')) ?: env('CHARGEBEE_SITE', ''), (getenv('CHARGEBEE_KEY')) ?: env('CHARGEBEE_KEY', ''));

        $invoice = ChargebeeInvoice::pdf($this->id);

        return $invoice->download();
    }
}
