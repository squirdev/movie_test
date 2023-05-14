<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethods extends Model
{
    // PaymentMethod type
    const TYPE_CASH            = 'offline_payment';
    const TYPE_PAYPAL          = 'paypal';
    const TYPE_STRIPE          = 'stripe';
    const TYPE_BRAINTREE       = 'braintree';
    const TYPE_AUTHORIZE_NET   = 'authorize_net';
    const TYPE_2CHECKOUT       = '2checkout';
    const TYPE_PAYSTACK        = 'paystack';
    const TYPE_PAYU            = 'payu';
    const TYPE_SLYDEPAY        = 'slydepay';
    const TYPE_PAYNOW          = 'paynow';
    const TYPE_COINPAYMENTS    = 'coinpayments';
    const TYPE_INSTAMOJO       = 'instamojo';
    const TYPE_PAYUMONEY       = 'payumoney';
    const TYPE_RAZORPAY        = 'razorpay';
    const TYPE_SSLCOMMERZ      = 'sslcommerz';
    const TYPE_AAMARPAY        = 'aamarpay';
    const TYPE_FLUTTERWAVE     = 'flutterwave';
    const TYPE_DIRECTPAYONLINE = 'directpayonline';
    const TYPE_SMANAGER        = 'smanager';
    const TYPE_PAYGATEGLOBAL   = 'paygateglobal';
    const TYPE_ORANGEMONEY     = 'orangemoney';
    const TYPE_CINETPAY        = 'cinetpay';
    const TYPE_AZAMPAY         = 'azampay';
    const TYPE_VODACOMMPESA    = 'vodacommpesa';
    const TYPE_PAYHERELK       = 'payherelk';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'options', 'status', 'type',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
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
        });
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return json_decode($this->options, true);
    }


    /**
     * Get option.
     *
     * @param $name
     *
     * @return string|null
     */
    public function getOption($name): ?string
    {
        $options = $this->getOptions();

        return $options[$name] ?? null;
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
