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
        <iframe src="/index.php?action=rows&baseName=avalon&tableName=ScriptList" frameborder="0"
                style="width: 100%; height: 100%;"></iframe>
    </div>
</div>

<div class="compare-database-block">
    <h1>Compalex</h1>

    <h3>Database schema compare tool</h3>

    <h2 style="color: #820000;">ERROR:<br/>

        <div
            style="border-top: 1px solid red;border-bottom: 1px solid red; padding: 20px;"><?php echo $e->getMessage(); ?></div>
    </h2>
    <h4>Stack trace:</h4>
    <pre><?php echo $e->getTraceAsString(); ?></pre>

</div>
</body>