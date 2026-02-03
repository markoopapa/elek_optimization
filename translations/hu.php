<?php
global $_MODULE;
$_MODULE = array();

$prefix = '<{elek_optimization}prestashop>elek_optimization_';

$_MODULE[$prefix . md5('Customer Type')] = 'Ügyfél típusa';
$_MODULE[$prefix . md5('Natural Person')] = 'Magánszemély';
$_MODULE[$prefix . md5('Legal Person')] = 'Cég';
$_MODULE[$prefix . md5('EMPTY CART')] = 'KOSÁR ÜRÍTÉSE';
$_MODULE[$prefix . md5('Do you want to empty your cart?')] = 'Ki akarod törölni a kosarat?';
$_MODULE[$prefix . md5('Quantity selection')] = 'Mennyiség választása';
$_MODULE[$prefix . md5('%s items in stock total')] = '%s db van készleten';
$_MODULE[$prefix . md5('(of which %s are already in your cart)')] = '(ebből %s db kosárban van)';
$_MODULE[$prefix . md5('Not enough stock')] = 'Nincs elég készlet';
$_MODULE[$prefix . md5('Only %s left!')] = 'Csak %s maradt!';
$_MODULE[$prefix . md5('Yes')] = 'Igen';
$_MODULE[$prefix . md5('Cancel')] = 'Mégse';
$_MODULE[$prefix . md5('I understand')] = 'Értem';

return $_MODULE;
