<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Partial;

use Exception;
use tiFy\Partial\PartialDriver;
use tiFy\Plugins\GatewayEtransactions\Contracts\{
    GatewayEtransactions as GatewayEtransactionsContract,
    PaymentFormPartial as PaymentFormContract
};
use tiFy\Plugins\GatewayEtransactions\Driver\EtransactionsOrder;
use tiFy\Support\Proxy\Partial;

class PaymentForm extends PartialDriver implements PaymentFormContract
{
    /**
     * Instance du gestionnaire de transaction.
     * @var GatewayEtransactionsContract|null
     */
    protected $gateway;

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        parent::boot();

        $this->set(
            'viewer.directory', $this->gateway->resources('/views/payment-form')
        );
    }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'button' => [
                'content' => __('Régler la commande', 'tify'),
            ],
            'debug'  => false,
            'order'  => null,
            'params' => [],
            'type'   => 'standard',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $order = $this->get('order');

        if (!$order instanceof ETransactionsOrder) {
            return Partial::get('notice', [
                'content' => __('Impossible de récupérer la commande.', 'tify'),
                'type'    => 'error',
            ])->render();
        } else {
            try {
                $this->set([
                    'action'            => $this->gateway->driver()->getPlatformUrl(),
                    'button.attrs.type' => 'submit',
                    'params'            => $this->gateway->driver()->fetchRequest(
                        $order, $this->get('type', 'standard'), $this->get('params', [])),
                ]);
            } catch (Exception $e) {
                return Partial::get('notice', [
                    'content' => sprintf(
                        __('Impossible d\'initialiser le formulaire de paiement : [%s]', 'tify'),
                        $e->getMessage()
                    ),
                    'type'    => 'error',
                ])->render();
            }
        }

        return parent::render();
    }

    /**
     * @inheritDoc
     */
    public function setGateway(GatewayEtransactionsContract $gateway): PaymentFormContract
    {
        $this->gateway = $gateway;

        return $this;
    }
}