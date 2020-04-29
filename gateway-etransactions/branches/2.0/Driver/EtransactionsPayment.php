<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Driver;

use Illuminate\Database\Capsule\Manager as DbDriver;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;

class EtransactionsPayment
{
    /**
     * Instance du pilote de base de données.
     * @var DbDriver|null
     */
    private $_driver;

    /**
     * Nom de qualification de la table d'enregistrement des paiements.
     * @var string
     */
    protected $table = 'etransactions_payment';

    /**
     * CONSTRUCTEUR.
     *
     * @param array $params
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $this->_driver = new DbDriver();

        $this->_driver->addConnection([
            'driver'    => $params['DB_CONNECTION'] ?? 'mysql',
            'host'      => ($params['DB_HOST'] ?? '127.0.0.1') .
                (!empty($params['DB_PORT']) ? ":{$params['DB_PORT']}" : ''),
            'database'  => $params['DB_DATABASE'] ?? null,
            'username'  => $params['DB_USERNAME'] ?? 'root',
            'password'  => $params['DB_PASSWORD'] ?? null,
            'charset'   => $params['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => $params['DB_COLLATE'] ?? 'utf8mb4_unicode_ci',
            'prefix'    => $params['DB_PREFIX'] ?? '',
            'strict'    => false
        ]);

        $this->_driver->bootEloquent();

        $schema = $this->_driver->getConnection()->getSchemaBuilder();

        if (!$schema->hasTable($this->table)) {
            $schema->create($this->table, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('order_id');
                $table->enum('type', ['capture', 'first_payment', 'second_payment', 'third_payment']);
                $table->longText('data');
                $table->timestamps();
                $table->index('order_id', 'order_id');
            });
        }
    }

    /**
     * Ajout d'informations de réglement d'une commande.
     *
     * @param int $order_id
     * @param string $type
     * @param array $data
     *
     * @return int
     */
    public function addOrderType(int $order_id, string $type, array $data)
    {
        return $this->query()->insertGetId([
            'order_id' => $order_id,
            'type'     => $type,
            'data'     => serialize($data)
        ]);
    }

    /**
     * Récupération d'un paiement associé à la commande.
     *
     * @param int $order_id
     * @param string $type
     *
     * @return array|object|void|null
     */
    public function getOrderType(int $order_id, string $type)
    {
        return $this->query()->where(compact('order_id', 'type'));
    }

    /**
     * Instance d'une requête sur la table de gestion des paiements.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->_driver->getConnection()->table($this->table);
    }
}
