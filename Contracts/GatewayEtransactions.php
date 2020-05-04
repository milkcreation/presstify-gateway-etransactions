<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Contracts;

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
     * Récupération de l'instance du pilote etransactions.
     *
     * @return Etransactions|null
     */
    public function driver(): ?Etransactions;

    /**
     * Récupération du chemin absolu vers le répertoire des ressources.
     *
     * @param string|null $path Chemin relatif d'une resource (répertoire|fichier).
     *
     * @return string
     */
    public function resources(string $path = null): string;

    /**
     * Définition des paramètres de configuration.
     *
     * @param array $attrs Liste des attributs de configuration.
     *
     * @return static
     */
    public function setConfig(array $attrs): GatewayEtransactions;

    /**
     * Définition du pilote ETransactions
     *
     * @param Etransactions $driver
     *
     * @return static
     */
    public function setDriver(Etransactions $driver): GatewayEtransactions;
}