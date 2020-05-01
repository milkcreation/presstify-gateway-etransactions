<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Driver;

use Detection\MobileDetect;

class EtransactionsConfig
{
    /**
     * Liste des paramètres de configuration personnalisés.
     * @see https://www.ca-moncommerce.com/wp-content/uploads/2018/08/tableau_correspondance_sips_atos-paybox_v4.pdf
     * @var array
     */
    private $_values;

    /**
     * Liste des paramètres de configuration par défaut.
     * @see https://www.ca-moncommerce.com/wp-content/uploads/2018/08/e-transactions_parametre_de_tests_v6.31.pdf
     * @var array
     */
    private static $_defaults = [
        '3ds_enabled' => 'always',
        '3ds_amount'  => 0,
        'currency'    => 'EUR',
        'debug'       => true,
        'delay'       => 0,
        'environment' => 'TEST',
        'hmacalgo'    => 'SHA512',
        'hmackey'     => '4642EDBBDFF9790734E673A9974FC9DD4EF40AA2929925C40B3A95170FF5A578E7D2579D6074E28A78BD07D633C0E72A378AD83D4428B0F3741102B69AD1DBB0',
        'identifier'  => 3262411,
        'ipn_method'  => 'POST',
        'lang'        => 'default',
        'rank'        => 95,
        'site'        => 9999999,
        'source'      => 'RWD'
    ];

    /**
     * CONSTRUCTEUR.
     *
     * @param array $values
     *
     * @return void
     */
    public function __construct(array $values = [])
    {
        $this->_values = [];

        foreach ($values as $k => $v) {
            if (static::isAllowed($k)) {
                $this->_values[$k] = $v;
            }
        }
    }

    /**
     * Récupération d'un paramètre de configuration.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    private function _getOption(string $name)
    {
        if (isset($this->_values[$name])) {
            return $this->_values[$name];
        }

        if (isset(static::$_defaults[$name])) {
            return static::$_defaults[$name];
        }

        return null;
    }

    /**
     * Récupération des clés d'indice de paramètres de configuration autorisées.
     *
     * @return array
     */
    public static function getAllowed(): array
    {
        return array_keys(static::$_defaults);
    }

    /**
     * Récupération de la liste des paramètres de configuration par défaut.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return static::$_defaults;
    }

    /**
     * Vérifie si une clé d'indice de paramètre de configuration est autorisée.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function isAllowed(string $key): bool
    {
        return in_array($key, static::getAllowed());
    }

    /**
     * Activation de l'authentification 3D Secure.
     *
     * @return string always|never|conditional
     */
    public function get3DSEnabled(): string
    {
        return $this->_getOption('3ds_enabled');
    }

    /**
     * Montant palier de l'activation de l'authentification 3D Secure.
     * {@internal Si [3ds_enabled] === conditional. Exprimé en centimes (sans virgule ni point)}
     *
     * @return float
     */
    public function get3DSAmount(): float
    {
        $value = $this->_getOption('3ds_amount');

        return empty($value) ? 0 : floatval($value);
    }

    /**
     * Devise de paiement de la commande (requise).
     * {@internal Conversion automatique au format ISO 4217.}
     *
     * @return string
     * @see EtransactionsCurrency::$_mapping
     */
    public function getCurrency(): string
    {
        return EtransactionsCurrency::getIsoCode($this->_getOption('currency'));
    }

    /**
     * Nombre de jours de différé entre la transaction et la capture des fonds.
     *
     * @return int
     */
    public function getDelay(): int
    {
        return (int)$this->_getOption('delay');
    }

    /**
     * Algorithme de hashage de la clé HMAC fournie par l'établissement bancaire.
     *
     * @return string SHA512|SHA256|RIPEMD160|SHA384|SHA224|MDC2
     */
    public function getHmacAlgo(): string
    {
        $algo = strtoupper($this->_getOption('hmackey'));

        return in_array($algo, ['SHA512', 'SHA256', 'RIPEMD160', 'SHA384', 'SHA224', 'MDC2']) ? $algo : 'SHA512';
    }

    /**
     * Clé d'authentification HMAC fournie par l'établissement bancaire (requise).
     *
     * @return string
     */
    public function getHmacKey(): string
    {
        return (string)$this->_getOption('hmackey');
    }

    /**
     * Identifiant ATOS/Paybox de 1 à 9 chiffres, fourni par l'établissement bancaire (requis).
     *
     * @return int
     */
    public function getIdentifier(): int
    {
        return (int)$this->_getOption('identifier');
    }

    /**
     * Méthode de la requête HTTP de l'appel de la notification de paiement instantané.
     *
     * @return string GET|POST
     */
    public function getIpnMethod(): string
    {
        $method = strtoupper($this->_getOption('ipn_method'));

        return (string) in_array($method, ['GET', 'POST']) ? $method : 'GET';
    }

    /**
     * Méthode de la requête HTTP de l'appel de la notification de paiement instantané.
     *
     * @return string
     */
    public function getLang(): string
    {
        return (string)$this->_getOption('lang');
    }

    /**
     * Numéro de rang à 2 chiffres, fourni par l'établissement bancaire  (requis).
     *
     * @return string
     */
    public function getRank(): string
    {
        return sprintf('%02s', $this->_getOption('rank'));
    }

    /**
     * Numéro de site à 7 chiffres, fourni par l'établissement bancaire (requis).
     *
     * @retun int
     */
    public function getSite()
    {
        return $this->_getOption('site');
    }

    /**
     * Récupération de la liste des urls vers la plateforme de paiement.
     *
     * @return string[]
     */
    public function getPlatformUrls(): array
    {
        if ($this->isProduction()) {
            return [
                'https://tpeweb.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi',
                'https://tpeweb1.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi',
            ];
        }

        return [
            'https://preprod-tpeweb.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi',
        ];
    }

    /**
     * Format d'affichage de la page du choix de moyen de paiement.
     *
     * @return string HTML|WAP|IMODE|XHTML|RWD
     */
    public function getSource(): string
    {
        $source = strtoupper($this->_getOption('source'));

        return in_array($source, ['HTML', 'WAP', 'IMODE', 'XHTML', 'RWD'])
            ? $source : ($this->isMobile() ? 'XHTML' : 'HTML');
    }

    /**
     * Vérifie l'activation du mode de débogguage.
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return filter_var($this->_getOption('debug'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Vérifie si le terminal de navigation est un mobile.
     *
     * @return bool
     */
    public function isMobile(): bool
    {
        return (new MobileDetect())->isMobile();
    }

    /**
     * Vérifie si l'environnement de production est actif pour le traitement de la transaction.
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->_getOption('environment') === 'PRODUCTION';
    }
}
