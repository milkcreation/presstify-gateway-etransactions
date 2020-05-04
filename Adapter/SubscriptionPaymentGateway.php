<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Adapter;

use Exception;
use tiFy\Plugins\GatewayEtransactions\GatewayEtransactions as GatewayEtransactionsContract;
use tiFy\Plugins\Subscription\Contracts\PaymentGateway;
use tiFy\Plugins\Subscription\Gateway\AbstractPaymentGateway;
use tiFy\Plugins\GatewayEtransactions\Driver\{Etransactions,
    EtransactionsOrder,
    EtransactionsConfig,
    EtransactionsCurrency};
use tiFy\Support\{DateTime, Proxy\Partial};

class SubscriptionPaymentGateway extends AbstractPaymentGateway
{
    /**
     * Instance du gestionnaire de plateforme de paiement etransactions.
     * @var GatewayEtransactionsContract|null
     */
    protected $gateway;

    /**
     * Initialisation.
     *
     * @return static
     */
    public function boot(): PaymentGateway
    {
        if (!$this->params('lang')) {
            $this->params(['lang' => preg_replace('#_.*$#', '', get_locale())]);
        }

        if (!$this->params('currency')) {
            $this->params(['currency' => $this->subscription()->settings()->getCurrency()]);
        }

        $this->gateway->setDriver(new Etransactions(
            new EtransactionsConfig(array_merge($this->gateway->config()->all(), $this->params->all()))
        ));

        return parent::boot();
    }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            /**
             * Intitulé de qualification.
             * @var string
             */
            'label' => __('Carte bancaire', 'tify'),
            /**
             * Attributs de configuration du formulaire de paiement.
             * @see \tiFy\Plugins\GatewayEtransactions\Partial\PaymentForm;
             * @var array
             */
            'payment-form' => [],
            /** ---------------------------------------------------------------------------------------------------- */
            /**
             * Activation de l'authentification 3D Secure.
             * @var string always|never|conditional
             */
            '3ds_enabled' => 'always',
            /**
             * Montant palier de l'activation de l'authentification 3D Secure.
             * {@internal Si [3ds_enabled] === conditional. Exprimé en centimes (sans virgule ni point)}
             * @var float
             */
            '3ds_amount'  => 0,
            /**
             * Devise de paiement de la commande (requise).
             * {@internal Conversion automatique au format ISO 4217. Si null >> Valeur de la boutique (recommandé).}
             * @see EtransactionsCurrency::$_mapping
             * @var string|null
             */
            'currency'    => null,
            /**
             * Nombre de jours de différé entre la transaction et la capture des fonds.
             * @var int
             */
            'delay'       => 0,
            /**
             * Environnement de traitement de la transaction.
             * @var string TEST|PRODUCTION
             */
            'environment' => 'TEST',
            /**
             * Algorithme de hashage de la clé HMAC fournie par l'établissement bancaire.
             * @var string SHA512|SHA256|RIPEMD160|SHA384|SHA224|MDC2
             */
            'hmacalgo'    => 'SHA512',
            /**
             * Clé d'authentification HMAC fournie par l'établissement bancaire (requise).
             * @var string
             */
            'hmackey'     => null,
            /**
             * Identifiant ATOS/Paybox de 1 à 9 chiffres, fourni par l'établissement bancaire (requis).
             * @var int
             */
            'identifier'  => null,
            /**
             * Méthode de la requête HTTP de l'appel de la notification de paiement instantané.
             * @var string GET|POST
             */
            'ipn_method'  => 'POST',
            /**
             * Langue de la page de paiement
             * {@internal Si null >> Valeur de l'environnement (recommandé).}
             * @var string|null fr|es|it|de|nl|sv|pt|default
             */
            'lang' => null,
            /**
             * Liste des paramètres de requête complémentaires personnalisés.
             * {@internal Tableau associatif dont les indices sont au format [PBX_*]}
             * @see https://www.ca-moncommerce.com/wp-content/uploads/2018/08/tableau_correspondance_sips_atos-paybox_v4.pdf
             * @var array
             */
            'params'      => [],
            /**
             * Numéro de rang à 2 chiffres, fourni par l'établissement bancaire  (requis).
             * @var int
             */
            'rank'        => null,
            /**
             * Numéro de site à 7 chiffres, fourni par l'établissement bancaire (requis).
             * @var int
             */
            'site'        => null,
            /**
             * Format d'affichage de la page du choix de moyen de paiement.
             * @var string HTML|WAP|IMODE|XHTML|RWD
             */
            'source'      => 'RWD'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentForm(): string
    {
        if ($order = $this->getOrder()) {
            EtransactionsOrder::setDatasMap([
                'ID'                => 'id',
                'billing.email'     => 'billing_email',
                'billing.firstname' => 'billing_firstname',
                'billing.lastname'  => 'billing_lastname',
            ]);

            return Partial::get('gateways-etransations.payment-form', array_merge($this->params('payment-form', []), [
                'debug'  => $this->isDebug(),
                'order'  => new EtransactionsOrder($this->getOrder()->all()),
                'params' => array_merge([
                    'PBX_ANNULE'     => $order->getHandleCancelledUrl(),
                    'PBX_ATTENTE'    => $order->getHandleOnHoldUrl(),
                    'PBX_EFFECTUE'   => $order->getHandleSuccessedUrl(),
                    'PBX_REFUSE'     => $order->getHandleFailedUrl(),
                    'PBX_REPONDRE_A' => $order->getHandleIpnUrl()
                ], $this->params('params', [])),
            ]))->render();
        } else {
            return '';
        }
    }

    /**
     * @inheritDoc
     */
    public function handleCancelled(): void
    {
        try {
            $params = $this->gateway->driver()->fetchResponse();

            $this->getOrder()->updateStatus('cancelled');

            $message = __(
                'Le paiement a été annulé par l\'utilisateur sur la plateforme de paiement E-Transactions.',
                'tify'
            );
            $this->getOrder()->addNote($message);
            $this->subscription()->log()->addInfo($message, [
                'order'   => $this->getOrder()->all(),
                'gateway' => $params,
            ]);

            $this->subscription()->notify(__('Le paiement a été annulé.', 'tify'), 'warning');
        } catch (Exception $e) {
            $this->getOrder()->updateStatus('cancelled');

            $message = __(
                'Annulation de paiement sur la plateforme de paiement E-Transactions indeterminée.',
                'tify'
            );
            $this->getOrder()->addNote($message);
            $this->subscription()->log()->addInfo($message, $this->getOrder()->all());

            $this->subscription()->notify(__('Le paiement a été annulé.', 'tify'), 'warning');
        }
    }

    /**
     * @inheritDoc
     */
    public function handleFailed(): void
    {
        try {
            $params = $this->gateway->driver()->fetchResponse();

            $this->getOrder()->updateStatus('failed');

            $message = __(
                'Le paiement a été réfusé par la plateforme de paiement E-Transactions.',
                'tify'
            );
            $this->getOrder()->addNote($message);
            $this->subscription()->log()->addInfo($message, [
                'order'   => $this->getOrder()->all(),
                'gateway' => $params,
            ]);

            $this->subscription()->notify(__('Le paiement a été refusé.', 'tify'), 'warning');
        } catch (Exception $e) {
            $this->getOrder()->updateStatus('failed');

            $message = __(
                'Refus de paiement sur la plateforme de paiement E-Transactions indeterminée.',
                'tify'
            );
            $this->getOrder()->addNote($message);
            $this->subscription()->log()->addInfo($message, $this->getOrder()->all());

            $this->subscription()->notify(__('Le paiement a été refusé.', 'tify'), 'warning');
        }
    }

    /**
     * @inheritDoc
     */
    public function handleIpn(): void
    {
        if (!$this->getOrder()->isStatusPaymentComplete()) {
            try {
                $params = $this->gateway->driver()->fetchResponse();

                if ($params['error'] == '00000') {
                    $message = __(
                        'Notification de Paiement Instantané accepté reçu de la plateforme de paiement E-Transactions.',
                        'tify'
                    );
                    $this->getOrder()->addNote($message);
                    $this->subscription()->log()->addSuccess($message, [
                        'order'   => $this->getOrder()->all(),
                        'gateway' => $params,
                    ]);

                    $this->capturePayment($this->parseReturnParams($params));
                }
            } catch (Exception $e) {
                unset($e);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function handleSuccessed(): void
    {
        if (!$this->getOrder()->isStatusPaymentComplete()) {
            try {
                $params = $this->gateway->driver()->fetchResponse();

                $message = __(
                    'Le paiement a été accepté par la plateforme de paiement E-Transactions.',
                    'tify'
                );
                $this->getOrder()->addNote($message);
                $this->subscription()->log()->addSuccess($message, [
                    'order'   => $this->getOrder()->all(),
                    'gateway' => $params,
                ]);

                $this->capturePayment($this->parseReturnParams($params));
            } catch (Exception $e) {
                $this->getOrder()->updateStatus('completed');

                $message = __(
                    'Acceptation du paiement sur la plateforme de paiement E-Transactions.',
                    'tify'
                );
                $this->getOrder()->addNote($message);
                $this->subscription()->log()->addSuccess($message, $this->getOrder()->all());
            }
        }
    }

    /**
     * Traitement de la liste des paramètres de retours pour correspondre aux attentes de la boutique.
     *
     * @param array $params
     *
     * @return array
     */
    public function parseReturnParams(array $params)
    {
        return [
            'card_first'     => $params['firstNumbers'] ?? null,
            'card_last'      => $params['lastNumbers'] ?? null,
            'card_valid'     => $params['validity'] ?? null,
            'transaction_id' => $params['transaction'] ?? null,
            'date_paid'      => DateTime::createFromFormat(
                'dmY H:i:s', $params['date'] . ' ' . $params['time']
            )->utc('U'),
        ];
    }

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