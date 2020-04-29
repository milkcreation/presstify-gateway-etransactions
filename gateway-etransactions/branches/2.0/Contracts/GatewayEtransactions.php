<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Contracts;

use tiFy\Contracts\{Log\Logger, Routing\Route, View\Engine};
use tiFy\Contracts\Support\ParamsBag;
use tiFy\Plugins\GatewayEtransactions\Driver\Etransactions;

interface GatewayEtransactions
{
    /**
     * Initialisation.
     *
     * @return static
     */
    public function boot(): GatewayEtransactions;

    /**
     * Récupération de paramètre|Définition de paramètres|Instance du gestionnaire de paramètre.
     *
     * @param string|array|null $key Clé d'indice du paramètre à récupérer|Liste des paramètre à définir.
     * @param mixed $default Valeur de retour par défaut lorsque la clé d'indice est une chaine de caractère.
     *
     * @return mixed|ParamsBag
     */
    public function config($key = null, $default = null);

    /**
     * Récupération de l'instance du pilote e-transactions.
     *
     * @return Etransactions|null
     */
    public function driver(): ?Etransactions;

    /**
     * Récupération de l'instance du gestionnaire de journalisation.
     *
     * @return Logger
     */
    public function log(): Logger;

    /**
     * Récupération d'une route de traitement de requête de paiement.
     *
     * @param string $name checkout|cancelled|failed|successed|ipn
     *
     * @return Route|null
     */
    public function route(string $name): ?Route;

    /**
     * Récupération de l'instance du gestionnaire de gabarit d'affichage.
     *
     * @return Engine
     */
    public function view(): Engine;

    /**
     * Définition des paramètres de configuration.
     *
     * @param array $attrs Liste des attributs de configuration.
     *
     * @return static
     */
    public function setConfig(array $attrs): GatewayEtransactions;
}