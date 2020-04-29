<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Driver;

use Exception;
use DateTime;
use Detection\MobileDetect;

/**
 * @see https://www.ca-moncommerce.com/ma-documentation/
 * @see TEST: https://www.ca-moncommerce.com/wp-content/uploads/2018/08/e-transactions_parametre_de_tests_v6.31.pdf
 */
class Etransactions
{
    /**
     * Instance de la configuration.
     * @var EtransactionsConfig|null
     */
    private $_config;

    /**
     * Cartographie du nombre de décimales par devise.
     * @var int[]
     */
    private $_currencyDecimals = [
        '008' => 2,
        '012' => 2,
        '032' => 2,
        '036' => 2,
        '044' => 2,
        '048' => 3,
        '050' => 2,
        '051' => 2,
        '052' => 2,
        '060' => 2,
        '064' => 2,
        '068' => 2,
        '072' => 2,
        '084' => 2,
        '090' => 2,
        '096' => 2,
        '104' => 2,
        '108' => 0,
        '116' => 2,
        '124' => 2,
        '132' => 2,
        '136' => 2,
        '144' => 2,
        '152' => 0,
        '156' => 2,
        '170' => 2,
        '174' => 0,
        '188' => 2,
        '191' => 2,
        '192' => 2,
        '203' => 2,
        '208' => 2,
        '214' => 2,
        '222' => 2,
        '230' => 2,
        '232' => 2,
        '238' => 2,
        '242' => 2,
        '262' => 0,
        '270' => 2,
        '292' => 2,
        '320' => 2,
        '324' => 0,
        '328' => 2,
        '332' => 2,
        '340' => 2,
        '344' => 2,
        '348' => 2,
        '352' => 0,
        '356' => 2,
        '360' => 2,
        '364' => 2,
        '368' => 3,
        '376' => 2,
        '388' => 2,
        '392' => 0,
        '398' => 2,
        '400' => 3,
        '404' => 2,
        '408' => 2,
        '410' => 0,
        '414' => 3,
        '417' => 2,
        '418' => 2,
        '422' => 2,
        '426' => 2,
        '428' => 2,
        '430' => 2,
        '434' => 3,
        '440' => 2,
        '446' => 2,
        '454' => 2,
        '458' => 2,
        '462' => 2,
        '478' => 2,
        '480' => 2,
        '484' => 2,
        '496' => 2,
        '498' => 2,
        '504' => 2,
        '512' => 3,
        '516' => 2,
        '524' => 2,
        '532' => 2,
        '533' => 2,
        '548' => 0,
        '554' => 2,
        '558' => 2,
        '566' => 2,
        '578' => 2,
        '586' => 2,
        '590' => 2,
        '598' => 2,
        '600' => 0,
        '604' => 2,
        '608' => 2,
        '634' => 2,
        '643' => 2,
        '646' => 0,
        '654' => 2,
        '678' => 2,
        '682' => 2,
        '690' => 2,
        '694' => 2,
        '702' => 2,
        '704' => 0,
        '706' => 2,
        '710' => 2,
        '728' => 2,
        '748' => 2,
        '752' => 2,
        '756' => 2,
        '760' => 2,
        '764' => 2,
        '776' => 2,
        '780' => 2,
        '784' => 2,
        '788' => 3,
        '800' => 2,
        '807' => 2,
        '818' => 2,
        '826' => 2,
        '834' => 2,
        '840' => 2,
        '858' => 2,
        '860' => 2,
        '882' => 2,
        '886' => 2,
        '901' => 2,
        '931' => 2,
        '932' => 2,
        '934' => 2,
        '936' => 2,
        '937' => 2,
        '938' => 2,
        '940' => 0,
        '941' => 2,
        '943' => 2,
        '944' => 2,
        '946' => 2,
        '947' => 2,
        '948' => 2,
        '949' => 2,
        '950' => 0,
        '951' => 2,
        '952' => 0,
        '953' => 0,
        '967' => 2,
        '968' => 2,
        '969' => 2,
        '970' => 2,
        '971' => 2,
        '972' => 2,
        '973' => 2,
        '974' => 0,
        '975' => 2,
        '976' => 2,
        '977' => 2,
        '978' => 2,
        '979' => 2,
        '980' => 2,
        '981' => 2,
        '984' => 2,
        '985' => 2,
        '986' => 2,
        '990' => 0,
        '997' => 2,
        '998' => 2,
    ];

    /**
     * Cartographie des messages d'erreur.
     * @var string[]
     */
    private $_errorCode = [
        '00000' => 'Successful operation',
        '00001' => 'Payment system not available',
        '00003' => 'Paybor error',
        '00004' => 'Card number or invalid cryptogram',
        '00006' => 'Access denied or invalid identification',
        '00008' => 'Invalid validity date',
        '00009' => 'Subscription creation failed',
        '00010' => 'Unknown currency',
        '00011' => 'Invalid amount',
        '00015' => 'Payment already done',
        '00016' => 'Existing subscriber',
        '00021' => 'Unauthorized card',
        '00029' => 'Invalid card',
        '00030' => 'Timeout',
        '00033' => 'Unauthorized IP country',
        '00040' => 'No 3D Secure',
    ];

    /**
     * Cartographie des langues.
     * @var string[]
     */
    private $_languages = [
        'fr'      => 'FRA',
        'es'      => 'ESP',
        'it'      => 'ITA',
        'de'      => 'DEU',
        'nl'      => 'NLD',
        'sv'      => 'SWE',
        'pt'      => 'PRT',
        'default' => 'GBR',
    ];

    /**
     * Instance de la requête HTTP.
     * @var EtransactionsRequest|null
     */
    private $_request;

    /**
     * Cartographie de conversion des paramètres de requête.
     * @var string[]
     */
    private $_returnMapping = [
        'M' => 'amount',
        'R' => 'reference',
        'T' => 'call',
        'A' => 'authorization',
        'B' => 'subscription',
        'C' => 'cardType',
        'D' => 'validity',
        'E' => 'error',
        'F' => '3ds',
        'G' => '3dsWarranty',
        'H' => 'imprint',
        'I' => 'ip',
        'J' => 'lastNumbers',
        'K' => 'sign',
        'N' => 'firstNumbers',
        'O' => '3dsInlistment',
        'o' => 'celetemType',
        'P' => 'paymentType',
        'Q' => 'time',
        'S' => 'transaction',
        'U' => 'subscriptionData',
        'W' => 'date',
        'Y' => 'country',
        'Z' => 'paymentIndex',
    ];

    /**
     * CONSTRUCTEUR.
     *
     * @param EtransactionsConfig $config
     * @param EtransactionsRequest|null $request
     *
     * @return void
     */
    public function __construct(EtransactionsConfig $config, ?EtransactionsRequest $request = null)
    {
        $this->_config = $config;
        $this->_request = $request ?? EtransactionsRequest::createFromGlobals();
    }

    public function addCartErrorMessage($message)
    {
        wc_add_notice($message, 'error');
    }

    /**
     * @param EtransactionsOrder $order Order
     * @param string $type Type of payment (standard or threetime)
     * @param array $additionalParams Additional parameters
     *
     * @return array
     *
     * @throws Exception
     */
    public function buildSystemParams(EtransactionsOrder $order, string $type, array $additionalParams = []): array
    {
        $values = [];

        // Merchant information
        $values['PBX_SITE'] = $this->_config->getSite();
        $values['PBX_RANG'] = $this->_config->getRank();
        $values['PBX_IDENTIFIANT'] = $this->_config->getIdentifier();

        // Order information
        $values['PBX_PORTEUR'] = $order->getBillingEmail();
        $values['PBX_DEVISE'] = $this->getCurrency();
        $values['PBX_CMD'] = "{$order->getId()} - {$order->getBillingName()}";

        // Amount
        $orderAmount = $order->getTotal();
        $amountScale = $this->_currencyDecimals[$values['PBX_DEVISE']];
        $amountScale = pow(10, $amountScale);

        switch ($type) {
            case 'standard':
                $delay = $this->_config->getDelay();

                if ($delay > 0) {
                    if ($delay > 7) {
                        $delay = 7;
                    }
                    $values['PBX_DIFF'] = sprintf('%02d', $delay);
                }

                $values['PBX_TOTAL'] = sprintf('%03d', round($orderAmount * $amountScale));
                break;
            case 'threetime':
                // Compute each payment amount
                $step = round($orderAmount * $amountScale / 3);
                $firstStep = ($orderAmount * $amountScale) - 2 * $step;
                $values['PBX_TOTAL'] = sprintf('%03d', $firstStep);
                $values['PBX_2MONT1'] = sprintf('%03d', $step);
                $values['PBX_2MONT2'] = sprintf('%03d', $step);

                // Payment dates
                $now = new DateTime();
                $now->modify('1 month');
                $values['PBX_DATE1'] = $now->format('d/m/Y');
                $now->modify('1 month');
                $values['PBX_DATE2'] = $now->format('d/m/Y');

                // Force validity date of card
                $values['PBX_DATEVALMAX'] = $now->format('ym');
                break;
            default:
                throw new Exception(sprintf('Unexpected type [%s]', $type));
                break;
        }

        // 3D Secure
        switch ($this->_config->get3DSEnabled()) {
            case 'never':
                $enable3ds = false;
                break;
            case null:
            case 'always':
                $enable3ds = true;
                break;
            case 'conditional':
                $tdsAmount = $this->_config->get3DSAmount();
                $enable3ds = empty($tdsAmount) || ($orderAmount >= $tdsAmount);
                break;
            default:
                throw new Exception(sprintf('Unexpected 3-D Secure status [%s]', $this->_config->get3DSEnabled()));
                break;
        }

        // Enable is the default behaviour
        if (!$enable3ds) {
            $values['PBX_3DS'] = 'N';
        }

        // E-Transactions => Magento
        $values['PBX_RETOUR'] = 'M:M;R:R;T:T;A:A;B:B;C:C;D:D;E:E;F:F;G:G;I:I;J:J;N:N;O:O;P:P;Q:Q;S:S;W:W;Y:Y;K:K';
        $values['PBX_RUF1'] = 'POST';

        // Choose correct language
        if ($locale = get_locale()) {
            $locale = preg_replace('#_.*$#', '', $locale);
        }

        $values['PBX_LANGUE'] = $this->_languages[$locale] ?? $this->_languages['default'];

        // Choose page format depending on browser/devise
        if ($this->isMobile()) {
            $values['PBX_SOURCE'] = 'XHTML';
        }

        // Misc.
        $values['PBX_TIME'] = date('c');
        $values['PBX_HASH'] = strtoupper($this->_config->getHmacAlgo());

        // Adding additionnal informations
        $values = array_merge($values, $additionalParams);

        // Sort parameters for simpler debug
        ksort($values);

        // Sign values
        $sign = $this->signValues($values);

        // Hash HMAC
        $values['PBX_HMAC'] = $sign;

        return $values;
    }

    /**
     * Récupération de la liste des paramètres au retour de la plateforme de paiement.
     *
     * @return array
     *
     * @throws Exception
     */
    public function fetchReturn(): array
    {
        if (!$data = file_get_contents('php://input') ?: $_SERVER['QUERY_STRING']) {
            throw new Exception('An unexpected error in E-Transactions call has occured: no parameters.');
        }

        // Récupération de la signature.
        $matches = [];
        if (!preg_match('#^(.*)&K=(.*)$#', $data, $matches)) {
            throw new Exception('An unexpected error in E-Transactions call has occured: missing signature.');
        }

        // Vérification de la signature.
        $signature = base64_decode(urldecode($matches[2]));
        $pubkey = file_get_contents(dirname(__FILE__) . '/pubkey.pem');
        $res = (boolean)openssl_verify($matches[1], $signature, $pubkey);

        if (!$res) {
            if (preg_match('#^s=i&(.*)&K=(.*)$#', $data, $matches)) {
                $signature = base64_decode(urldecode($matches[2]));
                $res = (boolean)openssl_verify($matches[1], $signature, $pubkey);
            }

            if (!$res) {
                throw new Exception('An unexpected error in E-Transactions call has occured: invalid signature.');
            }
        }

        // Cartographie des paramètres de retour.
        $rawReturn = [];
        parse_str($data, $rawReturn);

        $return = [];
        foreach ($this->_returnMapping as $mapKey => $mapName) {
            if (isset($rawReturn[$mapKey])) {
                $return[$mapName] = utf8_encode($rawReturn[$mapKey]);
            }
        }

        if (empty($return)) {
            throw new Exception('An unexpected error in E-Transactions call has occured: no parameters.');
        }

        return $return;
    }

    /**
     * Récupération de l'IPV4 du client.
     *
     * @return string|null
     */
    public function getClientIp(): ?string
    {
        return $this->_request->getClientIp();
    }

    /**
     * Récupération de la devise de paiement.
     *
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return EtransactionsCurrency::getIsoCode($this->_config->getCurrency());
    }

    /**
     * Récupération de l'url de la plateforme de paiement.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSystemUrl(): string
    {
        $urls = $this->_config->getSystemUrls();

        if (empty($urls)) {
            throw new Exception('Missing URL for E-Transactions system in configuration');
        }

        $error = null;
        foreach ($urls as $url) {
            $testUrl = preg_replace('#^([a-zA-Z0-9]+://[^/]+)(/.*)?$#', '\1/load.html', $url);

            $connectParams = [
                'timeout'     => 5,
                'redirection' => 0,
                'user-agent'  => 'Woocommerce E-Transactions module',
                'httpversion' => '2',
            ];

            try {
                $response = wp_remote_get($testUrl, $connectParams);

                if (is_array($response) && ($response['response']['code'] == 200)) {
                    if (preg_match('#<div id="server_status" style="text-align:center;">OK</div>#',
                            $response['body']) == 1) {
                        return $url;
                    }
                }
            } catch (Exception $e) {
                unset($e);
            }
        }

        throw new Exception(__('E-Transactions not available. Please try again later.'));
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
     * @param array $values
     *
     * @return string
     *
     * @throws Exception
     */
    public function signValues(array $values): string
    {
        $params = [];
        foreach ($values as $name => $value) {
            $params[] = $name . '=' . $value;
        }
        $query = implode('&', $params);

        $key = pack('H*', $this->_config->getHmacKey());

        $sign = hash_hmac($this->_config->getHmacAlgo(), $query, $key);

        if ($sign === false) {
            throw new Exception('Unable to create hmac signature. Maybe a wrong configuration.');
        }

        return strtoupper($sign);
    }

    /**
     * Récupération du message d'erreur associé à un code.
     *
     * @param string $code
     *
     * @return string
     */
    public function toErrorMessage(string $code): string
    {
        return $this->_errorCode[$code] ?? "Unknown error {$code}";
    }

    /**
     * Récupération de la commande depuis le jeton de retour.
     *
     * @param string $token
     *
     * @return EtransactionsOrder
     *
     * @throws Exception
     */
    public function untokenizeOrder(string $token): EtransactionsOrder
    {
        $parts = explode(' - ', $token, 2);
        if (count($parts) < 2) {
            throw new Exception(sprintf('Invalid decrypted token [%s]', $token));
        }

        if (!$order = EtransactionsOrder::createFromPostId((int)$parts[0])) {
            throw new Exception(sprintf('Not existing order id from decrypted token [%s]', $token));
        }

        $name = $order->getBillingName();

        if (($name != utf8_decode($parts[1])) && ($name != $parts[1])) {
            throw new Exception(sprintf('Consistency error on descrypted token [%s]', $token));
        }

        return $order;
    }
}
