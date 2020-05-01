<?php

// @see https://www.ca-moncommerce.com/wp-content/uploads/2018/08/e-transactions_parametre_de_tests_v6.31.pdf

/** Défaut */
return [
    '3ds_enabled' => 'always',
    '3ds_amount'  => 0,
    'currency'    => 'EUR',
    'debug'       => true,
    'delay'       => 0,
    'environment' => 'TEST',
    'hmacalgo'    => 'SHA512',
    'hmackey'     => '4642EDBBDFF9790734E673A9974FC9DD4EF40AA2929925C40B3A95170FF5A578E7D2579D6074E28A78BD07D633C0E72A378AD83D4428B0F3741102B69AD1DBB0',
    'identifier'  => 3262411,
    'ipn_method'  => 'POST',
    'lang'        => 'default',
    'rank'        => 95,
    'site'        => 9999999,
    'source'      => 'RWD',
];
/**/

/** 3DS Secure * /
return [
    '3ds_enabled' => 'always',
    'debug'       => true,
    'delay'       => 0,
    'currency'    => 'EUR',
    'environment' => 'TEST',
    'hmackey'     => '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF',
    'identifier'  => 3262411,
    'rank'        => 43,
    'site'        => 1999887,
];
 /**/

/** Non 3DS Secure * /
return [
    '3ds_enabled' => 'never',
    'debug'       => true,
    'delay'       => 0,
    'currency'    => 'EUR',
    'environment' => 'TEST',
    'hmackey'     => '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF',
    'identifier'  => 3262411,
    'rank'        => 32,
    'site'        => 1999887,
];
/**/

/** 3DS Secure && Encaissements Automatisés * /
return [
    '3ds_enabled' => 'always',
    'debug'       => true,
    'delay'       => 0,
    'currency'    => 'EUR',
    'environment' => 'TEST',
    'hmackey'     => '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF',
    'identifier'  => 3262411,
    'rank'        => 63,
    'site'        => 1999887,
];
/**/

/** Non 3DS Secure && Encaissements Automatisés * /
return [
    '3ds_enabled' => 'never',
    'debug'       => true,
    'delay'       => 0,
    'currency'    => 'EUR',
    'environment' => 'TEST',
    'hmackey'     => '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF',
    'identifier'  => 3262411,
    'rank'        => 85,
    'site'        => 1999887,
];
/**/