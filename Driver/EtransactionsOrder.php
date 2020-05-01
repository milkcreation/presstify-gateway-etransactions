<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Driver;

use Illuminate\Support\{Arr, Str};
use tiFy\Support\ParamsBag;
use tiFy\Wordpress\Query\QueryPost;

class EtransactionsOrder
{
    /**
     * Liste des données associées à la commande.
     * @return array
     */
    protected $datas = [];

    /**
     * Cartographie des données.
     * @var array
     */
    protected static $datasMap = [];

    /**
     * CONSTRUCTEUR
     *
     * @param array $datas
     *
     * @return void
     */
    public function __construct(array $datas = [])
    {
       $_datas = (new ParamsBag())->set($datas);
       if ($_datas->count()) {
           foreach (self::$datasMap as $_keys => $k) {
               if ($_datas->has($_keys)) {
                   $this->datas[$k] = $_datas->pull($_keys);
               }
           }
           $this->datas = array_merge($this->datas, $_datas->all());
       }
    }

    /**
     * Récupération d'une commande.
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function createFromId(int $id): ?self
    {
        return ($order = QueryPost::createFromId($id)) ? new static($order->all()) : null;
    }

    /**
     * Définition de la cartographie des données.
     *
     * @param array $map
     *
     * @return void
     */
    public static function setDatasMap(array $map): void
    {
        self::$datasMap = $map;
    }

    /**
     * @param string $key
     * @param null $default
     *
     * @return array|mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->datas, $key, $default);
    }

    /**
     * Récupération de l'email de facturation.
     *
     * @return string
     */
    public function getBillingEmail(): string
    {
        return $this->get('billing_email', 'johndoe@domain.ltd');
    }

    /**
     * Récupération du prénom de facturation du client.
     *
     * @return string
     */
    public function getBillingFirstname(): string
    {
        return $this->get('billing_firstname', 'John');
    }

    /**
     * Récupération du nom de famille de facturation du client.
     *
     * @return string
     */
    public function getBillingLastname(): string
    {
        return $this->get('billing_lastname', 'Doe');
    }

    /**
     * Récupération du nom de qualification de facturation du client.
     *
     * @return string
     */
    public function getBillingName(): string
    {
        return trim(preg_replace(
            '/[^-. a-zA-Z0-9]/', '', Str::ascii($this->getBillingFirstname() . ' ' . $this->getBillingLastname())
        ));
    }

    /**
     * Récupération de l'identifiant de qualification de la commande.
     *
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->get('id', rand());
    }

    /**
     * Récupération du montant total de la commande.
     *
     * @return float
     */
    public function getTotal(): float
    {
        return (float)$this->get('total', 1);
    }
}