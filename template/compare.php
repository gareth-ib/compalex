<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>COMPALEX - database schema compare tool</title>
    <script src="/js/jquery.min.js"></script>
    <script src="/js/functional.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
</head>

<body>
<div class="modal-background" onclick="Data.hideTableData(); return false;">
    <div class="modal">
        <iframe src="" frameborder="0"></iframe>
    </div>
</div>

<div class="compare-database-block">
    <h1>Compalex</h1>

    <h3>Database schema compare tool</h3>

    <table class="table">
        <tr class="panel">
            <td>
<?php
switch (DRIVER) {
    case 'mysql':
        $buttons = array('tables', 'views', 'procedures', 'functions', 'indexes', 'triggers');
        break;
    case 'mssql':
    case 'dblib':
        $buttons = array('tables', 'views', 'procedures', 'functions', 'indexes');
        break;
    case 'pgsql':
        $buttons = array('tables', 'views', 'functions', 'indexes');
        break;
}

if (!isset($_REQUEST['action']))
    $_REQUEST['action'] = 'tables';
foreach ($buttons as $li) {
    echo '<a href="index.php?action=' . $li . '"  ' . ($li == $_REQUEST['action'] ? 'class="active"' : '') . '>' . $li . '</a>&nbsp;';
}
?>
            </td>
            <td class="sp">
                <a href="#" onclick="Data.showAll(this); return false;" class="active">all</a>
                <a href="#" onclick="Data.showDiff(this); return false;">changed</a>
            </td>
        </tr>
    </table>

    <table class="table">
        <tr class="header">
            <td width="50%">
                <h2><?php echo DATABASE_NAME ?></h2>
                <span><?php $spath = explode("@", FIRST_DSN);
                    echo end($spath); ?></span>
            </td>
            <td  width="50%">
                <h2><?php echo DATABASE_NAME_SECONDARY ?></h2>
                <span><?php $spath = explode("@", SECOND_DSN);
                    echo end($spath); ?></span>
            </td>
        </tr>
    <?php
    foreach ($tables as $tableName => $data) {

?>
        <tr class="data">
<?php
        foreach (array('fArray', 'sArray') as $blockType) {

            $tableData = $data[$blockType];

?>
        	<td class="type-<?php echo $_REQUEST['action']; ?>">
<?php

            if( empty( $tableData->isNew ) ) {
                echo '</td>';
                continue;
            }

            if( empty ( $tableData->fields ) ) {
                echo '</td>';
                continue;
            }

?>
                <h3><?php echo $tableName; ?> <sup style="color: red;"><?php echo count($tableData->fields); ?></sup></h3>
                <div class="table-additional-info">
<?php
            if(isset($additionalTableInfo[$tableName][$blockType]->fields)) {
                foreach ($additionalTableInfo[$tableName][$blockType]->fields as $paramKey => $paramValue) {
                    if(strpos($paramKey, 'ARRAY_KEY') === false) echo "<b>{$paramKey}</b>: {$paramValue}<br />";
                }
            }
?>
                </div>
<?php
            if ($tableData->fields) {
?>
                <ul>
<?php
                foreach ($tableData->fields as $fieldName => $tparam) {
                    if( empty( $tparam->isNew ) ) {
                        continue;
                    }
?>
                    <li <?php
                    if (isset($tparam->isNew) && $tparam->isNew) {
                        echo 'style="color: red;" class="new" ';
                    }
                    ?>><b><?php echo $fieldName; ?></b>
                        <span <?php if (isset($tparam->changeType) && $tparam->changeType): ?>style="color: red;" class="new" <?php endif;?>> <?php echo $tparam->dtype; ?> </span>
                    </li>
<?php
                }
?>
            	</ul>
<?php
            }

            if (count($tableData->fields) && in_array($_REQUEST['action'], array('tables', 'views'))) {
?>
<a
    target="_blank"
    onclick="Data.getTableData('index.php?action=rows&baseName=<?php echo $basesName[$blockType]; ?>&tableName=<?php echo $tableName; ?>'); return false;"
    href="#" class="sample-data">Sample data (<?php echo SAMPLE_DATA_LENGTH; ?> rows)</a><?php
            }
?>
            </td>
<?php
        }
?>
        </tr>
<?php
    }
?>
    </table>

</div>
</body>
