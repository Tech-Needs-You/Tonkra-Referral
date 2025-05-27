<?php

namespace Tonkra\Referral\Models;

use App\Models\Invoices;
use App\Models\SubscriptionTransaction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReferralInvoice extends Invoices
{
	protected $table = 'invoices';


	public function invoice(): BelongsTo
	{
		return $this->belongsTo(Invoices::class, 'id');
	}
	
	public function subscriptionTransaction(): BelongsTo
    {
        return $this->belongsTo(SubscriptionTransaction::class,'transaction_id', 'uid');
    }

}
