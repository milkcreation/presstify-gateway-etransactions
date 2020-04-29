<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions;

use tiFy\Contracts\{Http\Response, View\Engine};
use tiFy\Plugins\GatewayEtransactions\Contracts\{
    GatewayEtransactions as GatewayEtransactionsContract,
    GatewayEtransactionsController as GatewayEtransactionsControllerContract
};
use tiFy\Plugins\GatewayEtransactions\Driver\EtransactionsOrder;
use tiFy\Routing\BaseController;

class GatewayEtransactionsController extends BaseController implements GatewayEtransactionsControllerContract
{
    /**
     * Instance du gestionnaire de plateforme de paiement e-transactions.
     * @var GatewayEtransactionsContract|null
     */
    protected $manager;

    /**
     * @inheritDoc
     */
    public function checkout(): Response
    {
        $this->set([
            'action' => $this->manager->driver()->getSystemUrl(),
            'params' => $this->manager->driver()->buildSystemParams(new ETransactionsOrder([
                'id'                => 15,
                'billing_email'     => 'jordy@tigreblanc.fr',
                'billing_firstname' => 'Jordy',
                'billing_lastname'  => 'Manner',
                'total'             => 30,
            ]), 'standard', [
                'PBX_ANNULE'     => $this->manager->route('cancelled')->getUrl([], true),
                'PBX_EFFECTUE'   => $this->manager->route('successed')->getUrl([], true),
                'PBX_REFUSE'     => $this->manager->route('failed')->getUrl([], true),
                'PBX_REPONDRE_A' => $this->manager->route('ipn')->getUrl([], true),
                'PBX_SOURCE'     => 'RWD'
            ]),
        ]);

        return $this->view('app::checkout/index', $this->all());
    }

    /**
     * @inheritDoc
     */
    public function cancelled(): Response
    {
        var_dump($this->manager->driver()->fetchReturn());

        return $this->view('app::checkout/cancel', $this->all());
    }

    /**
     * @inheritDoc
     */
    public function failed(): Response
    {
        return $this->view('app::checkout/failed', $this->all());
    }

    /**
     * @inheritDoc
     */
    public function ipn(): Response
    {
        $this->manager->log()->info(json_encode($this->manager->driver()->fetchReturn()));
        exit;
    }

    /**
     * @inheritDoc
     */
    public function successed(): Response
    {
        var_dump($this->manager->driver()->fetchReturn());

        return $this->view('app::checkout/success', $this->all());
    }

    /**
     * @inheritDoc
     */
    public function setManager(GatewayEtransactionsContract $manager): GatewayEtransactionsControllerContract
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function viewEngine(): Engine
    {
        return $this->manager->view();
    }
}