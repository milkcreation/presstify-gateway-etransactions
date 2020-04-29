<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions;

use tiFy\Contracts\{
    Log\Logger,
    Routing\Route,
    View\Engine
};
use tiFy\Plugins\GatewayEtransactions\Contracts\{
    GatewayEtransactions as GatewayEtransactionsContract,
    GatewayEtransactionsController as GatewayEtransactionsControllerContract
};
use tiFy\Plugins\GatewayEtransactions\Driver\{Etransactions, EtransactionsConfig};
use tiFy\Support\{ParamsBag, Proxy\Log, Proxy\Router, Proxy\View};

/**
 * @desc Extension PresstiFy de plateforme de paiement e-transactions.
 * @author Jordy Manner <jordy@milkcreation.fr>
 * @package tiFy\Plugins\GatewayEtransactions
 * @version 2.0.0
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
     * Instance du pilote e-transactions.
     * @var Etransactions|null
     */
    private $driver;

    /**
     * Instance du gestionnaire de configuration.
     * @var ParamsBag
     */
    protected $config;

    /**
     * Instance du controleur.
     * @var GatewayEtransactionsControllerContract
     */
    protected $controller;

    /**
     * Instance du gestionnaire de journalisation des événements.
     * @var Logger|null
     */
    protected $log;

    /**
     * Liste des routes de traitement des requêtes de paiement.
     * @var Route[]|array
     */
    protected $route = [];

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
            // Initialisation du pilote e-transactions.
            $this->driver = new Etransactions(new EtransactionsConfig($this->config()->only([
                '3ds_enabled',
                '3ds_amount',
                'amount',
                'debug',
                'delay',
                'environment',
                'hmackey',
                'identifier',
                'ips',
                'rank',
                'site',
            ])));

            // Définition du routage de traitement des requêtes de paiement.
            $controller = $this->config('controller', null);
            if (is_string($controller) && is_callable($controller)) {
                $controller = new $controller();
            }

            $this->controller = $controller instanceof GatewayEtransactionsControllerContract
                ? $controller : new GatewayEtransactionsController();

            $this->controller->setManager($this);

            $pfx = 'gateways-etransations';
            $endpoints = array_merge([
                'checkout'  => "{$pfx}/checkout",
                'failed'    => "{$pfx}/failed",
                'cancelled' => "{$pfx}/cancelled",
                'ipn'       => "{$pfx}/ipn",
                'successed' => "{$pfx}/successed",
            ]);

            foreach ($endpoints as $name => $enpoint) {
                if ($name === 'ipn') {
                    $this->route[$name] = Router::post(
                        'checkout', [$this->controller, 'index']
                    );
                } else {
                    $this->route[$name] = Router::get(
                        'checkout', [$this->controller, 'index']
                    );
                }
            }

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
        return $this->driver;
    }

    /**
     * @inheritDoc
     */
    public function log(): Logger
    {
        if (is_null($this->log)) {
            $this->log = Log::registerChannel('checkout');
        }

        return $this->log;
    }

    /**
     * @inheritDoc
     */
    public function route(string $name): ?Route
    {
        return $this->route[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function view(): Engine
    {
        if (is_null($this->view)) {
            if (($view = $this->config('view', [])) && ($view instanceof Engine)) {
                $this->view = $view;
            } else {
                $this->view = View::getPlatesEngine(array_merge([
                    'directory' => dirname(__FILE__) . '/Resources/views'
                ], is_array($view) ? $view : []));
            }
        }

        return $this->view;
    }

    /**
     * @inheritDoc
     */
    public function setConfig(array $attrs): GatewayEtransactionsContract
    {
        $this->config($attrs);

        return $this;
    }
}