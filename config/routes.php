<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['offices/office/(:num)/(:any)'] = 'office/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['offices/list'] = 'myoffice/list';
$route['offices/show/(:num)/(:any)'] = 'myoffice/show/$1/$2';
$route['offices/pdf/(:num)'] = 'myoffice/pdf/$1';

