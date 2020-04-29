<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Contracts;

use App\Gateway\ETransactions\{ETransactions, ETransactionsOrder};
use tiFy\Contracts\{Http\Response, Log\Logger};
use tiFy\Routing\BaseController;
use tiFy\Support\Proxy\{Log, Request, Router};

/**
 * @mixin BaseController
 */
interface GatewayEtransactionsController
{
    /**
     * Paiement.
     *
     * @return Response
     */
    public function checkout(): Response;

    /**
     * Annulation de paiement.
     *
     * @return Response
     */
    public function cancelled(): Response;

    /**
     * Echec de paiement.
     *
     * @return Response
     */
    public function failed(): Response;

    /**
     * Validation de paiement.
     *
     * @return Response
     */
    public function ipn(): Response;

    /**
     * Succès de paiement.
     *
     * @return Response
     */
    public function successed(): Response;

    /**
     * Définition du gestionnaire de plateforme de paiement e-transactions.
     *
     * @param GatewayEtransactions $manager
     *
     * @return static
     */
    public function setManager(GatewayEtransactions $manager): GatewayEtransactionsController;
}