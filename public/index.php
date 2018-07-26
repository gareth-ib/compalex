<?php
error_reporting(-1);

include '../vendor/autoload.php';

require_once 'config.php';

// try {
    if (!defined('FIRST_DSN')) throw new Exception('Check your config.php file and uncomment settings section for your database');
    if (!strpos(FIRST_DSN, '://')) throw new Exception('Wrong dsn format');

    $pdsn = explode('://', FIRST_DSN);

    define('DRIVER', $pdsn[0]);

    $class = '\\compalex\\Drivers\\'.$pdsn[0];

    $driverModel = new $class();

    $dsnFirstExpl   = explode('/', FIRST_DSN);
    $dsnSecondExpl  = explode('/', SECOND_DSN);

    define('FIRST_BASE_NAME', end($dsnFirstExpl));
    define('SECOND_BASE_NAME', end($dsnSecondExpl));

    $action = $_REQUEST['action'] ?? 'tables';

    $additionalTableInfo = array();
    switch ($action) {
        case "tables":
            $tables = $driverModel->getCompareTables();
            $additionalTableInfo = $driverModel->getAdditionalTableInfo();
            break;
        case "views":
            $tables = $driverModel->getCompareViews();
            break;
        case "procedures":
            $tables = $driverModel->getCompareProcedures();
            break;
        case "functions":
            $tables = $driverModel->getCompareFunctions();
            break;
        case "indexes":
            $tables = $driverModel->getCompareKeys();
            break;
        case "triggers":
            $tables = $driverModel->getCompareTriggers();
            break;
        case "rows":
            $rows = $driverModel->getTableRows($_REQUEST['baseName'], $_REQUEST['tableName']);
            break;
    }


    $basesName = array(
        'fArray' => FIRST_BASE_NAME,
        'sArray' => SECOND_BASE_NAME
    );

    if ($action == 'rows') {
        require_once TEMPLATE_DIR . 'rows.php';
    } else {
        require_once TEMPLATE_DIR . 'compare.php';
    }

// } catch (Exception $e) {
//     include_once TEMPLATE_DIR . 'error.php';
// }

