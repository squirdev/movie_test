<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static where(string $string, string $uid)
 * @method static count()
 * @method static offset(mixed $start)
 * @method static whereLike(string[] $array, mixed $search)
 * @method static insert(array[] $subscriptions)
 * @method static whereNull(string $string)
 * @method static whereDate(string $string, string $toDateString)
 * @property mixed options
 * @property mixed status
 * @property mixed end_at
 * @property int|mixed end_by
 * @property Carbon|mixed start_at
 * @property bool|mixed|null current_period_ends_at
 * @property mixed end_period_last_days
 * @property mixed plan
 * @property mixed payment_claimed
 */
class Subscription extends Model
{
    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_ENDED = 'ended';
    const STATUS_RENEW = 'renew';

    /**
     * The attributes that should be mutated to date.
     *
     * @var array
     */
    protected $dates = [
        'current_period_ends_at',
        'start_at',
        'end_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'options',
        'status',
        'paid',
        'current_period_ends_at',
        'payment_claimed',
        'start_at',
        'end_at',
        'end_period_last_days',
    ];

    /**
     * Indicates if the plan change should be prorated.
     *
     * @var bool
     */
    protected $prorate = true;


    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($subscription) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $subscription->uid     = $uid;
            $subscription->options = json_encode([
                'credit_warning'       => true,
                'credit'               => '100',
                'credit_notify'        => 'both',
                'subscription_warning' => true,
                'subscription_notify'  => 'both',
                'send_warning'         => false,
            ]);
        });
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
     * Get options.
     *
     * @return mixed
     */
    public function getOptions()
    {
        if ( ! $this->options) {
            return json_decode('{}', true);
        }

        return json_decode($this->options, true);
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
     * update options
     *
     * @param $data
     *
     */
    public function updateOptions($data)
    {
        $options       = (object) array_merge((array) $this->getOptions(), $data);
        $this->options = json_encode($options);

        $this->save();
    }


    /**
     * plan associations
     *
     * @return BelongsTo
     *
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    /**
     * Get the user that owns the subscription.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * subscription transactions associations
     *
     * @return HasMany
     */
    public function subscriptionTransactions(): HasMany
    {

        return $this->hasMany(SubscriptionTransaction::class);
    }

    /**
     * subscriptions logs associations
     *
     * @return HasMany
     */
    public function subscriptionLogs(): HasMany
    {
        return $this->hasMany(SubscriptionLog::class);
    }


    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->status == self::STATUS_NEW;
    }

    /**
     * Determine if the subscription is renewing.
     *
     * @return bool
     */
    public function isRenew(): bool
    {
        return $this->status == self::STATUS_RENEW;
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status == self::STATUS_PENDING;
    }


    /**
     * Determine if the subscription is no longer active.
     *
     * @return bool
     */
    public function cancelled(): bool
    {
        return ! is_null($this->end_at);
    }

    /**
     * Determine if the subscription is ended.
     *
     * @return bool
     */
    public function isEnded(): bool
    {
        return $this->status == self::STATUS_ENDED;
    }

    /**
     * Cancel subscription. Set ends at to the end of period.
     *
     * @return void
     */
    public function cancelNow()
    {
        $this->setEnded();
    }

    /**
     * Determine if the subscription is ended.
     *
     * @param  int  $ended_by
     *
     * @return void
     */
    public function setEnded(int $ended_by = 1)
    {

        $invoice = Invoices::whereLike(['transaction_id'], $this->uid)->first();
        if ($invoice !== null) {
            $invoice->delete();
        }

        $this->status                 = self::STATUS_ENDED;
        $this->end_by                 = $ended_by;
        $this->current_period_ends_at = Carbon::now();
        $this->end_at                 = Carbon::now();
        $this->save();
    }

    /**
     * Determine if the subscription is pending.
     *
     * @return void
     */
    public function setPending()
    {
        $this->status = self::STATUS_PENDING;
        $this->save();
    }

    /**
     * Determine if the subscription is pending.
     *
     * @return void
     */
    public function setActive()
    {
        $this->status                 = self::STATUS_ACTIVE;
        $this->start_at               = Carbon::now();
        $this->current_period_ends_at = $this->getPeriodEndsAt(Carbon::now());
        $this->save();
    }

    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->active() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active(): bool
    {
        return (is_null($this->end_at) || $this->onGracePeriod()) && ! $this->isPending() && ! $this->isNew();
    }

    /**
     * Determine if the subscription is recurring and not on trial.
     *
     * @return bool
     */
    public function isRecurring(): bool
    {
        return ! $this->cancelled();
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod(): bool
    {
        return $this->current_period_ends_at && $this->current_period_ends_at->isFuture();
    }

    /**
     * Check if subscription is going to expire.
     *
     * @return Boolean
     */
    public function goingToExpire(): bool
    {
        if ( ! $this->end_at) {
            return false;
        }

        $days = $this->end_period_last_days;

        return $this->end_at->subDay($days)->lessThanOrEqualTo(Carbon::now());
    }


    /**
     *  Next one period to subscription.
     *
     * @return null
     */
    public function nextPeriod()
    {
        $endsAt = $this->current_period_ends_at;

        $interval      = $this->plan->getBillableInterval();
        $intervalCount = $this->plan->getBillableIntervalCount();

        switch ($interval) {
            case 'month':
                $endsAt->add(new DateInterval('P' . $intervalCount . 'M'));
                break;
            case 'day':
                $endsAt->add(new DateInterval('P' . $intervalCount . 'D'));
                break;
            case 'week':
                $endsAt->add(new DateInterval('P' . $intervalCount . 'W'));
                break;
            case 'year':
                $endsAt->add(new DateInterval('P' . $intervalCount . 'Y'));
                break;
            default:
                $endsAt = null;
                break;
        }

        return $endsAt;
    }

    /**
     * Next one period to subscription.
     *
     * @return null
     */
    public function periodStartAt()
    {
        $startAt       = $this->current_period_ends_at;
        $interval      = $this->plan->getBillableInterval();
        $intervalCount = $this->plan->getBillableIntervalCount();

        switch ($interval) {
            case 'month':
                $startAt->sub(new DateInterval('P' . $intervalCount . 'M'));
                break;
            case 'day':
                $startAt->sub(new DateInterval('P' . $intervalCount . 'D'));
                break;
            case 'week':
                $startAt->sub(new DateInterval('P' . $intervalCount . 'W'));
                break;
            case 'year':
                $startAt->sub(new DateInterval('P' . $intervalCount . 'Y'));
                break;
            default:
                $startAt = null;
                break;
        }

        return $startAt;
    }

    /**
     * Add one period to subscription.
     *
     * @return void
     */
    public function addPeriod()
    {
        $this->end_at = $this->nextPeriod();
        $this->save();
    }

    /**
     *  Check if payment is claimed.
     *
     * @return mixed
     */
    public function isPaymentClaimed()
    {
        return $this->payment_claimed;
    }

    /**
     * Claim payment.
     *
     * @return void
     */
    public function claimPayment()
    {
        $this->payment_claimed = true;
        $this->save();
    }


    /**
     *
     * @param $startDate
     *
     * @return null
     */
    public function getPeriodEndsAt($startDate)
    {
        // does not support recurring, update ends at column
        $interval      = $this->plan->getBillableInterval();
        $intervalCount = $this->plan->getBillableIntervalCount();

        switch ($interval) {
            case 'month':
                $startDate->addMonthsNoOverflow($intervalCount);
                break;
            case 'day':
                $startDate->addDay($intervalCount);
                break;
            case 'week':
                $startDate->addWeek($intervalCount);
                break;
            case 'year':
                $startDate->addYearsNoOverflow($intervalCount);
                break;
            default:
                break;
        }

        return $startDate;
    }

    /**
     * Start subscription.
     *
     * @return void
     */
    public function start()
    {
        $this->end_at                 = null;
        $this->current_period_ends_at = $this->getPeriodEndsAt(Carbon::now());
        $this->status                 = self::STATUS_ACTIVE;
        $this->start_at               = Carbon::now();
        $this->save();
    }

    /**
     * Subscription transactions.
     *
     * @return Collection
     */
    public function getTransactions(): Collection
    {
        return $this->subscriptionTransactions()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Subscription transactions.
     *
     * @return Collection
     */
    public function getLogs(): Collection
    {
        return $this->subscriptionLogs()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Subscription transactions.
     *
     * @param $type
     * @param $data
     *
     * @return SubscriptionTransaction
     */
    public function addTransaction($type, $data): SubscriptionTransaction
    {
        $transaction                  = new SubscriptionTransaction();
        $transaction->subscription_id = $this->id;
        $transaction->type            = $type;
        $transaction->fill($data);

        if (isset($data['options'])) {
            $transaction->options = json_encode($data['options']);
        }

        $transaction->save();

        return $transaction;
    }

    /**
     * Subscription transactions.
     *
     * @param $type
     * @param $data
     * @param  null  $transaction_id
     *
     * @return SubscriptionLog
     */
    public function addLog($type, $data, $transaction_id = null): SubscriptionLog
    {
        $log                  = new SubscriptionLog();
        $log->subscription_id = $this->id;
        $log->type            = $type;
        $log->transaction_id  = $transaction_id;
        $log->save();

        if (isset($data)) {
            $log->updateData($data);
        }

        return $log;
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

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
