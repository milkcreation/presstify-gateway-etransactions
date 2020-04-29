<?php declare(strict_types=1);

namespace tiFy\Plugins\GatewayEtransactions\Driver;

class EtransactionsConfig
{
    private $_values;

    private $_defaults = [
        '3ds_enabled' => 'always',
        '3ds_amount'  => '',
        'amount'      => '',
        'debug'       => 'no',
        'delay'       => 0,
        'currency'    => 'EUR',
        'environment' => 'TEST',
        'hmackey'     => '4642EDBBDFF9790734E673A9974FC9DD4EF40AA2929925C40B3A95170FF5A578E7D2579D6074E28A78BD07D633C0E72A378AD83D4428B0F3741102B69AD1DBB0',
        'identifier'  => 3262411,
        'ips'         => '194.2.122.158,195.25.7.166,195.101.99.76',
        'rank'        => 95,
        'site'        => 9999999,
    ];

    public function __construct(array $values)
    {
        $this->_values = $values;
    }

    protected function _getOption($name)
    {
        if (isset($this->_values[$name])) {
            return $this->_values[$name];
        }
        if (isset($this->_defaults[$name])) {
            return $this->_defaults[$name];
        }
        return null;
    }

    public function get3DSEnabled()
    {
        return $this->_getOption('3ds_enabled');
    }

    public function get3DSAmount()
    {
        $value = $this->_getOption('3ds_amount');
        return empty($value) ? null : floatval($value);
    }

    public function getAmount()
    {
        $value = $this->_getOption('amount');
        return empty($value) ? null : floatval($value);
    }

    public function getAllowedIps()
    {
        return explode(',', $this->_getOption('ips'));
    }

    public function getCurrency()
    {
        return $this->_getOption('currency');
    }

    public function getDefaults()
    {
        return $this->_defaults;
    }

    public function getDelay()
    {
        return (int)$this->_getOption('delay');
    }

    public function getDescription()
    {
        return $this->_getOption('description');
    }

    public function getHmacAlgo()
    {
        return 'SHA512';
    }

    public function getHmacKey()
    {
        $crypto = new EtransactionsEncrypt();

        return $crypto->decrypt($this->_values['hmackey']);
    }

    public function getIdentifier()
    {
        return $this->_getOption('identifier');
    }

    public function getRank()
    {
        return $this->_getOption('rank');
    }

    public function getSite()
    {
        return $this->_getOption('site');
    }

    public function getSystemProductionUrls()
    {
        return [
            'https://tpeweb.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi',
            'https://tpeweb1.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi',
        ];
    }

    public function getSystemTestUrls()
    {
        return [
            'https://preprod-tpeweb.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi',
        ];
    }

    public function getSystemUrls()
    {
        if ($this->isProduction()) {
            return $this->getSystemProductionUrls();
        }

        return $this->getSystemTestUrls();
    }

    public function getTitle()
    {
        return $this->_getOption('title');
    }

    public function getIcon()
    {
        return $this->_getOption('icon');
    }

    public function isDebug()
    {
        return $this->_getOption('debug') === 'yes';
    }

    public function isProduction()
    {
        return $this->_getOption('environment') === 'PRODUCTION';
    }
}
