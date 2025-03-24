<?php

namespace Tonkra\Referral\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, string $uid)
 * @method static create(array $array)
 */
class ReferralRole extends Role
{
	const ROLE_NAME_ADMIN  = 'administrator';

	public function role(): BelongsTo
	{
		return $this->belongsTo(Role::class, 'id');
	}
}
