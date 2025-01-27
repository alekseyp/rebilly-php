<?php
/**
 * This source file is proprietary and part of Rebilly.
 *
 * (c) Rebilly SRL
 *     Rebilly Ltd.
 *     Rebilly Inc.
 *
 * @see https://www.rebilly.com
 */

namespace Rebilly\Tests\Api;

use JsonSerializable;
use Rebilly\Entities;
use Rebilly\Entities\Customer;
use Rebilly\Rest;
use Rebilly\Tests\TestCase;

/**
 * Class ApiTest.
 *
 */
class ApiTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideEntityClasses
     *
     * @param string $class
     * @param string $id
     * @param array $excludeAttributes
     */
    public function buildJson($class, $id = 'id', $excludeAttributes = [])
    {
        $getters = [];
        $setters = [];
        $values = [];
        $objects = [];

        if ($id !== null) {
            $values[$id] = $this->getFakeValue($id, $class);
            $values['id'] = $values[$id];
        }

        /** @var Rest\Resource $resource */
        $resource = new $class($values);
        $values = array_merge($values, $resource->jsonSerialize());

        $methods = get_class_methods($resource);

        foreach ($methods as $method) {
            $prefix = substr($method, 0, 3);
            $attribute = lcfirst(substr($method, 3));

            if (in_array($attribute, $excludeAttributes, true)) {
                continue;
            }

            if ($prefix === 'get') {
                $getters[$attribute] = $method;
            } elseif ($prefix === 'set') {
                $setters[$attribute] = $method;
            }
        }

        foreach ($setters as $attribute => $method) {
            $value = $this->getFakeValue($attribute, $class);
            $values[$attribute] = $value;

            // Test attributes factory
            if (is_array($value) && method_exists($resource, "create{$attribute}")) {
                $objects[$attribute] = $resource->{"create{$attribute}"}($value);
            }

            $resource->$method($value);
        }

        foreach ($getters as $attribute => $method) {
            $value = $resource->$method();

            if (isset($objects[$attribute])) {
                if ($value instanceof JsonSerializable) {
                    $value = $value->jsonSerialize();
                } elseif (is_array($value)) {
                    $value = array_map(
                        function (JsonSerializable $item) {
                            return $item->jsonSerialize();
                        },
                        $value
                    );
                }

                $this->assertSame($values[$attribute], $value, sprintf('Invalid %s:$%s', $class, $attribute));
            } elseif (isset($values[$attribute])) {
                $this->assertSame(json_encode($values[$attribute]), json_encode($value), sprintf('Invalid %s:$%s', $class, $attribute));
            } else {
                $this->assertNull($value);
            }
        }

        $json = $resource->jsonSerialize();

        $this->assertTrue(is_array($json));
        $this->assertNotEmpty($json);
    }

    /**
     * @test
     */
    public function useFactories()
    {
        $resource = new Customer();

        $data = $this->getFakeValue('primaryAddress', Customer::class);

        self::assertNotEmpty($data);

        $resource->setPrimaryAddress($data);

        self::assertInstanceOf(Entities\Address::class, $resource->getPrimaryAddress());

        $data = $this->getFakeValue('primaryAddress', Customer::class);

        $resource->setPrimaryAddress(Entities\Address::createFromData($data));

        self::assertInstanceOf(Entities\Address::class, $resource->getPrimaryAddress());
    }

    public function provideEntityClasses(): iterable
    {
        // Ignore deprecated fields in payment token tests, they conflicted with new attribute setters `setPaymentInstrument`.
        $paymentTokenDeprecatedAttributes = [
            'pan',
            'cvv',
            'last4',
            'expYear',
            'expMonth',
            'firstName',
            'lastName',
            'address',
            'address2',
            'city',
            'region',
            'country',
            'postalCode',
            'phoneNumber',
            'method',
        ];

        $cases = [
            [Entities\Address::class],
            [Entities\Attachment::class],
            [Entities\AuthenticationOptions::class, null],
            [Entities\AuthenticationToken::class, 'token'],
            [Entities\Blacklist::class, 'id', ['expireTime', 'expiredTime']],
            [Entities\Contact\Email::class],
            [Entities\Contact\PhoneNumber::class],
            [Entities\CustomerCredential::class],
            [Entities\File::class],
            [Entities\Invoice::class],
            [Entities\InvoiceItem::class],
            [Entities\PaymentMethods\PaymentCardMethod::class, null],
            [Entities\PaymentCard::class],
            [Entities\PaymentCardToken::class, null, $paymentTokenDeprecatedAttributes],
            [Entities\PaymentToken::class, null, $paymentTokenDeprecatedAttributes],
            [Entities\Plan::class],
            [Entities\ResetPasswordToken::class, 'token'],
            [Entities\SubscriptionReactivation::class, null],
            [Entities\Subscription::class],
            [Entities\SubscriptionCancel::class, null],
            [Entities\SubscriptionCancellation::class, null],
            [Entities\LineItem::class, null],
            [Entities\Transaction::class],
            [Entities\Website::class],
            [Entities\Organization::class],
            [Entities\GatewayAccount::class],
            [Entities\BankAccount::class],
            [Entities\PayPalAccount::class],
            [Entities\CustomField::class, 'name'],
            [Entities\Session::class],
            [Entities\User::class],
            [Entities\ThreeDSecure::class],
            [Entities\UpdatePassword::class],
            [Entities\ApiKey::class],
            [Entities\ApiTracking::class],
            [Entities\SubscriptionTracking::class],
            [Entities\Signup::class],
            [Entities\ResetPassword::class],
            [Entities\ForgotPassword::class],
            [Entities\Login::class],
            [Entities\Dispute::class],
            [Entities\Coupons\Coupon::class, null, ['redemptionCode']],
            [Entities\Coupons\Redemption::class, null, ['redemptionCode']],
            [Entities\ValuesList::class],
            [Entities\Product::class],
            [Entities\Webhook::class],
            [Entities\WebhookCredential::class, 'hash'],
            [Entities\RiskMetadata::class],
            [Entities\WebhookTracking::class],
            [Entities\Shipping\ShippingZone::class],
            [Entities\InvoiceTax::class],
            [Entities\SubscriptionChangePlan::class],
            [Entities\SubscriptionInterimInvoice::class],
            [Entities\PaymentInstruments\BankAccountPaymentInstrument::class, null],
            [Entities\PaymentInstruments\PaymentCardPaymentInstrument::class, null],
            [Entities\PaymentInstruments\KhelocardCardPaymentInstrument::class, null],
            [Entities\PaymentRetryAttempt::class],
            [Entities\GatewayAccountDowntime::class],
            [Entities\PlaidCredential::class, 'hash'],
            [Entities\ExperianCredential::class, 'hash'],
            [Entities\CommonPaymentInstrument::class],
        ];

        foreach ($cases as $case) {
            yield $case[0] => $case;
        }
    }
}
