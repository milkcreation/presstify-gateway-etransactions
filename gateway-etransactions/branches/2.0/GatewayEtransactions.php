<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions;

use tiFy\Plugins\GatewayEtransactions\Contracts\GatewayEtransactions as GatewayEtransactionsContract;
use tiFy\Plugins\GatewayEtransactions\Driver\{Etransactions, EtransactionsConfig};
use tiFy\Plugins\GatewayEtransactions\Partial\PaymentForm;
use tiFy\Support\ParamsBag;
use tiFy\Support\Proxy\{Partial, View};

/**
 * @desc Extension PresstiFy de plateforme de paiement etransactions.
 * @author Jordy Manner <jordy@milkcreation.fr>
 * @package tiFy\Plugins\GatewayEtransactions
 * @version 2.0.1
 *
 * USAGE :
 * Activation
 * ---------------------------------------------------------------------------------------------------------------------
 * Dans config/app.php ajouter \tiFy\Plugins\GatewayEtransactions\GatewayEtransactionsServiceProvider à la liste des
 *     fournisseurs de services. ex.
 * <?php
 * ...
 * use tiFy\Plugins\GatewayEtransactions\GatewayEtransactionsServiceProvider;
 * ...
 *
 * return [
 *      ...
 *      'providers' => [
 *          ...
 *          GatewayEtransactionsServiceProvider::class
 *          ...
 *      ]
 * ];
 *
 * Configuration
 * ---------------------------------------------------------------------------------------------------------------------
 * Dans le dossier de config, créer le fichier tb-set.php
 * @see /vendor/presstify-plugins/gateway-etransactions/Resources/config/gateway-etransactions.php
 */
class GatewayEtransactions implements GatewayEtransactionsContract
{
    /**
     * Indicateur d'initialisation.
     * @var bool
     */
    private $booted = false;

    /**
     * Instance du pilote etransactions.
     * @var Etransactions|null
     */
    private $driver;

    /**
     * Instance du gestionnaire de configuration.
     * @var ParamsBag
     */
    protected $config;

    /**
     * Instance du gestionnaire des gabarits d'affichage.
     * @var View|null
     */
    protected $view;

    /**
     * CONSTRUCTEUR.
     *
     * @param array|null $config
     *
     * @return void
     */
    public function __construct(?array $config = null)
    {
        if (!is_null($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * @inheritDoc
     */
    public function boot(): GatewayEtransactionsContract
    {
        if (!$this->booted) {
            // - Déclaration du formulaire de paiement.
            Partial::register('gateways-etransations.payment-form', (new PaymentForm())->setGateway($this));

            $this->booted = true;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function config($key = null, $default = null)
    {
        if (!$this->config instanceof ParamsBag) {
            $this->config = new ParamsBag();
        }

        if (is_string($key)) {
            return $this->config->get($key, $default);
        } elseif (is_array($key)) {
            return $this->config->set($key);
        } else {
            return $this->config;
        }
    }

    /**
     * @inheritDoc
     */
    public function driver(): ?Etransactions
    {
        if (is_null($this->driver)) {
            $this->driver = new Etransactions(new EtransactionsConfig($this->config()->all()));
        }

        return $this->driver;
    }

    /**
     * @inheritDoc
     */
    public function resources(string $path = null): string
    {
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists(__DIR__ . "/Resources{$path}")) ? __DIR__ . "/Resources{$path}" : '';
    }

    /**
     * @inheritDoc
     */
    public function setConfig(array $attrs): GatewayEtransactionsContract
    {
        $this->config($attrs);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDriver(Etransactions $driver): GatewayEtransactionsContract
    {
        $this->driver = $driver;

        return $this;
    }
}
