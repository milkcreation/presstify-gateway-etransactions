<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Adapter;

use tiFy\Plugins\GatewayEtransactions\GatewayEtransactions as GatewayEtransactionsContract;

class WcPaymentGateway /*  * / extends WC_Payment_Gateway /**/
{
    /**
     * Instance du gestionnaire de plateforme de paiement etransactions.
     * @var GatewayEtransactionsContract|null
     */
    protected $gateway;

    /**
     * Définition du gestionnaire de plateforme de paiement etransactions.
     *
     * @param GatewayEtransactionsContract $gateway
     *
     * @return $this
     */
    public function setGateway(GatewayEtransactionsContract $gateway): self
    {
        $this->gateway = $gateway;

        return $this;
    }
}