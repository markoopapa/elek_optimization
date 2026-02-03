<?php
global $_MODULE;
$_MODULE = array();

$prefix = '<{elek_optimization}prestashop>elek_optimization_';

$_MODULE[$prefix . md5('Customer Type')] = 'Tip Client';
$_MODULE[$prefix . md5('Natural Person')] = 'Persoană Fizică';
$_MODULE[$prefix . md5('Legal Person')] = 'Persoană Juridică';
$_MODULE[$prefix . md5('EMPTY CART')] = 'GOLIȚI COȘUL';
$_MODULE[$prefix . md5('Do you want to empty your cart?')] = 'Doriți să goliți coșul?';
$_MODULE[$prefix . md5('Quantity selection')] = 'Selectare cantitate';
$_MODULE[$prefix . md5('%s items in stock total')] = 'Total %s buc. în stoc';
$_MODULE[$prefix . md5('(of which %s are already in your cart)')] = '(din care %s sunt deja în coș)';
$_MODULE[$prefix . md5('Not enough stock')] = 'Stoc insuficient';
$_MODULE[$prefix . md5('Only %s left!')] = 'Doar %s rămase!';
$_MODULE[$prefix . md5('Yes')] = 'Da';
$_MODULE[$prefix . md5('Cancel')] = 'Anulează';
$_MODULE[$prefix . md5('I understand')] = 'Am înțeles';

return $_MODULE;
