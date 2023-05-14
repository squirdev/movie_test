<?php

namespace App\Models;

use App\Library\Tool;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static where(string $string, bool $true)
 * @method static select(string $string)
 * @method static count()
 * @method static offset(mixed $start)
 * @method static whereLike(string[] $array, mixed $search)
 * @method static cursor()
 * @method static find(mixed $plan_id)
 * @method static create(array $plan)
 * @method static whereIn(string $string, array $ids)
 * @property mixed frequency_unit
 * @property mixed frequency_amount
 * @property bool|mixed status
 * @property mixed id
 * @property mixed name
 * @property mixed currency
 * @property mixed price
 */

class Plan extends Model
{
    // Plan status
    const STATUS_INACTIVE = false;
    const STATUS_ACTIVE   = true;
    const TYPE_BASIC ='Basic';
    const TYPE_PREMIUM = 'Premium';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'billing_cycle',
        'frequency_amount',
        'frequency_unit',
        'price',
        'currency_id',
        'options',
        'status',
        'is_popular',
        'tax_billing_required',
        'show_in_customer',
    ];

    protected $casts = [
        'status'               => 'boolean',
        'show_in_customer'     => 'boolean',
        'is_popular'           => 'boolean',
        'tax_billing_required' => 'boolean',
    ];

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // Update custom order
            self::getAll()->increment('custom_order');
            $item->custom_order = 0;
        });
    }

    /**
     * get Basic Plan
     *
     * @return object
     *
     * */
    public static function basicPlan(){
        return self::where('name',self::TYPE_BASIC)->first();
    }

    /**
     * get Premium Plan
     *
     * @return object
     *
     * */
    public static function preminumPlan(){
        return self::where('name',self::TYPE_PREMIUM)->first();
    }


    /**
     * Active status scope
     *
     * @param        $query
     * @param  bool  $status
     *
     * @return mixed
     */
    public function scopeStatus($query, bool $status)
    {
        return $query->where('status', $status);
    }

    /**
     * get user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Currency
     *
     * @return BelongsTo
     *
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * get all plans
     *
     * @return Plan
     */

    public static function getAll()
    {
        return self::select('*');
    }

    /**
     * Find item by uid.
     *
     * @param $uid
     *
     * @return object
     */
    public static function findByUid($uid): object
    {
        return self::where('uid', $uid)->first();
    }

    /**
     * Frequency time unit options.
     *
     * @return array
     */
    public static function timeUnitOptions(): array
    {
        return [
            ['value' => 'day', 'text' => 'day'],
            ['value' => 'week', 'text' => 'week'],
            ['value' => 'month', 'text' => 'month'],
            ['value' => 'year', 'text' => 'year'],
        ];
    }

    /**
     * Get sending limit types.
     *
     * @return array
     */
    public static function sendingLimitValues(): array
    {
        return [
            'unlimited'      => [
                'quota_value' => -1,
                'quota_base'  => -1,
                'quota_unit'  => 'day',
            ],
            '100_per_minute' => [
                'quota_value' => 100,
                'quota_base'  => 1,
                'quota_unit'  => 'minute',
            ],
            '1000_per_hour'  => [
                'quota_value' => 1000,
                'quota_base'  => 1,
                'quota_unit'  => 'hour',
            ],
            '10000_per_hour' => [
                'quota_value' => 10000,
                'quota_base'  => 1,
                'quota_unit'  => 'hour',
            ],
            '50000_per_hour' => [
                'quota_value' => 50000,
                'quota_base'  => 1,
                'quota_unit'  => 'hour',
            ],
            '10000_per_day'  => [
                'quota_value' => 10000,
                'quota_base'  => 1,
                'quota_unit'  => 'day',
            ],
            '100000_per_day' => [
                'quota_value' => 100000,
                'quota_base'  => 1,
                'quota_unit'  => 'day',
            ],
        ];
    }

    /**
     * Get billing recurs available values.
     *
     * @return array
     */
    public static function billingCycleValues(): array
    {
        return [
            'daily'   => [
                'frequency_amount' => 1,
                'frequency_unit'   => 'day',
            ],
            'monthly' => [
                'frequency_amount' => 1,
                'frequency_unit'   => 'month',
            ],
            'yearly'  => [
                'frequency_amount' => 1,
                'frequency_unit'   => 'year',
            ],
        ];
    }

    /**
     * Check if plan time is unlimited.
     *
     * @return bool
     */
    public function isTimeUnlimited(): bool
    {
        return $this->frequency_unit == 'unlimited';
    }

    /**
     * Display frequency time
     *
     * @return array|Application|Translator|string|null
     */
    public function displayFrequencyTime()
    {
        // unlimited
        if ($this->isTimeUnlimited()) {
            return __('locale.labels.unlimited');
        }
        if ($this->frequency_amount == 1) {
            return Tool::getPluralParse($this->frequency_unit, $this->frequency_amount);
        }

        return $this->frequency_amount.' '.Tool::getPluralParse($this->frequency_unit, $this->frequency_amount);
    }

    /**
     * Display total quota
     *
     * @return Application|array|string|Translator|null
     */

    public function displayTotalQuota()
    {
        if ($this->getOption('sms_max') == -1) {
            return __('locale.labels.unlimited');
        } else {
            return Tool::format_number($this->getOption('sms_max'));
        }
    }


    /**
     * get single option
     *
     * @param $name
     *
     * @return string
     */
    public function getOption($name): string
    {
        return $this->getOptions()[$name];
    }

    /**
     * Get sending limit select options.
     *
     * @return array
     */
    public function getSendingLimitSelectOptions(): array
    {
        $options = [];

        foreach (self::sendingLimitValues() as $key => $data) {
            $wording   = __('locale.plans.'.$key);
            $options[] = ['text' => $wording, 'value' => $key];
        }

        // exist
        if ($this->getOption('sending_limit') == 'custom') {
            $wording = __('locale.plans.custom_sending_limit_phrase', [
                'quota_value' => Tool::format_number($this->getOption('sending_quota')),
                'quota_base'  => Tool::format_number($this->getOption('sending_quota_time')),
                'quota_unit'  => $this->getOption('sending_quota_time_unit'),
            ]);

            $options[] = ['text' => $wording, 'value' => 'other'];
        }

        // Custom
        $options[] = ['text' => 'Custom', 'value' => 'custom'];

        return $options;
    }

    /**
     * Disable plan.
     *
     * @return bool
     */
    public function disable(): bool
    {
        $this->status = self::STATUS_INACTIVE;

        return $this->save();
    }

    /**
     * Enable plan.
     *
     * @return bool
     */
    public function enable(): bool
    {
        $this->status = self::STATUS_ACTIVE;

        return $this->save();
    }

    /**
     * get plan id
     *
     * @return string
     */
    public function getBillableId(): string
    {
        return $this->id;
    }

    /**
     * get plan name
     *
     * @return string
     */
    public function getBillableName(): string
    {
        return $this->name;
    }


    /**
     * get plan interval.
     *
     * @return string
     */
    public function getBillableInterval(): string
    {
        return $this->frequency_unit;
    }

    /**
     * get plan interval count.
     *
     * @return string
     */
    public function getBillableIntervalCount(): string
    {
        return $this->frequency_amount;
    }

    /**
     *  get currency.
     *
     * @return string
     */
    public function getBillableCurrency(): string
    {
        return $this->currency->code;
    }

    /**
     * get plan interval count.
     *
     * @return string
     */
    public function getBillableAmount(): string
    {
        return $this->price;
    }

    /**
     * get plan interval count.
     *
     * @return string
     */
    public function getBillableFormattedPrice(): string
    {
        return Tool::format_price($this->price, $this->currency->format);
    }

    /**
     * get subscriptions
     *
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id', 'id')
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', Carbon::now());
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * get route key by uid
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'uid';
    }
}
