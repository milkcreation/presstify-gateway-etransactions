<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions;

use tiFy\Container\ServiceProvider as BaseServiceProvider;

class GatewayEtransactionsServiceProvider extends BaseServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * @internal requis. Tous les noms de qualification de services à traiter doivent être renseignés.
     * @var string[]
     */
    protected $provides = [
        'app.gateway.e-transactions',
    ];

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        add_action('after_setup_theme', function () {
            $this->getContainer()->get('app.gateway.e-transactions')->boot();
        });
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share('app.gateway.e-transactions', function () {
            return new GatewayEtransactions(config('gateway-etransactions', []));
        });
    }
}