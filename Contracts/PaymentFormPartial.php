<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Contracts;

use tiFy\Contracts\Partial\PartialDriver;
use tiFy\Plugins\GatewayEtransactions\Contracts\{
    GatewayEtransactions as GatewayEtransactionsContract,
};

interface PaymentFormPartial extends PartialDriver
{
    /**
     * Définition du gestionnaire de plateforme de paiement etransactions.
     *
     * @param GatewayEtransactionsContract $gateway
     *
     * @return static
     */
    public function setGateway(GatewayEtransactionsContract $gateway): PaymentFormPartial;
}