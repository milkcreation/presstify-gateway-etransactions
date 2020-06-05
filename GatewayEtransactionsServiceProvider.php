<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions;

use tiFy\Container\ServiceProvider as BaseServiceProvider;
use tiFy\Plugins\GatewayEtransactions\Adapter\{
    ShopPaymentGateway,
    SubscriptionPaymentGateway,
    WcPaymentGateway
};
class GatewayEtransactionsServiceProvider extends BaseServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * @internal requis. Tous les noms de qualification de services à traiter doivent être renseignés.
     * @var string[]
     */
    protected $provides = [
        'gateway.etransactions',
        'shop.gateway.etransactions',
        'subscription.gateway.etransactions',
        'woocommerce.gateway.etransactions',
    ];

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        add_action('after_setup_theme', function () {
            $this->getContainer()->get('gateway.etransactions')->boot();
        });
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share('gateway.etransactions', function () {
            return new GatewayEtransactions(config('gateway-etransactions', []));
        });

        $this->getContainer()->share('shop.gateway.etransactions', function () {
            return (new ShopPaymentGateway())->setGateway($this->getContainer()->get('gateway.etransactions'));
        });

        $this->getContainer()->share('subscription.gateway.etransactions', function () {
            return (new SubscriptionPaymentGateway())->setGateway($this->getContainer()->get('gateway.etransactions'));
        });

        $this->getContainer()->share('woocommerce.gateway.etransactions', function () {
            return (new WcPaymentGateway())->setGateway($this->getContainer()->get('gateway.etransactions'));
        });
    }
}