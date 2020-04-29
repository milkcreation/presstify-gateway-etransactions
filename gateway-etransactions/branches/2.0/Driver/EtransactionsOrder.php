<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Driver;

use Illuminate\Support\{Arr, Str};
use tiFy\Wordpress\Query\QueryPost;
use WC_Order;

class EtransactionsOrder
{
    /**
     * Liste des données associées à la commande.
     * @return array
     */
    protected $datas = [];

    /**
     * CONSTRUCTEUR
     *
     * @param array $datas
     *
     * @return void
     */
    public function __construct(array $datas = [])
    {
        $this->datas = $datas;
    }

    /**
     * Récupération d'une commande.
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function createFromPostId(int $id): ?self
    {
        return ($order = QueryPost::createFromId($id)) ? new static($order->all()) : null;
    }

    /**
     * Récupération d'une commande Woocommerce.
     *
     * @param int|WC_Order|null $id
     *
     * @return static|null
     *
     * @todo
     */
    public static function createFromWcOrder($id): ?self
    {
        if (function_exists('wc_get_order')) {
            $order = call_user_func('wc_get_order', $id);
        } else {
            return null;
        }

        return $order instanceof WC_Order ? new static(get_object_vars($order)) : null;
    }

    /**
     * Ajout d'une note de commande.
     *
     * @param string $message
     *
     * @return int
     */
    public function addOrderNote(string $message): int
    {
        return 0;
    }

    /**
     * Ajout d'informations de réglement d'une commande.
     *
     * @param string $type
     * @param array $data
     *
     * @return int
     */
    public function addOrderPayment(string $type, array $data)
    {
        return 0;
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
     * Récupération d'un paiement associé à la commande.
     *
     * @param string $type
     *
     * @return array|object|void|null
     */
    public function getPayment(string $type)
    {
        return null;
    }

    /**
     * Récupération de l'email de facturation.
     *
     * @return string
     */
    public function getBillingEmail(): string
    {
        return $this->get('billing_email', '');
    }

    /**
     * Récupération du prénom de facturation du client.
     *
     * @return string
     */
    public function getBillingFirstname(): string
    {
        return $this->get('billing_firstname', '');
    }

    /**
     * Récupération du nom de famille de facturation du client.
     *
     * @return string
     */
    public function getBillingLastname(): string
    {
        return $this->get('billing_lastname', '');
    }

    /**
     * Récupération du nom de qualification de facturation du client.
     *
     * @return string
     */
    public function getBillingName(): string
    {
        $name = trim(preg_replace(
            '/[^-. a-zA-Z0-9]/', '', Str::ascii($this->getBillingFirstname() . ' ' . $this->getBillingLastname())
        ));

        return $name;
    }

    /**
     * Récupération de l'identifiant de qualification de la commande.
     *
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->get('id', 0);
    }

    /**
     * Récupération du montant total de la commande.
     *
     * @return float
     */
    public function getTotal(): float
    {
        return (float)$this->get('total', 0);
    }
}