<?php

class Driver extends BaseDriver
{
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }


    public function getCompareTables()
    {
        return $this->_getTableAndViewResult('BASE TABLE');
    }

    public function getCompareViews()
    {
        return $this->_getTableAndViewResult('VIEW');
    }

    public function getCompareProcedures()
    {
        return $this->_getRoutineResult('PROCEDURE');
    }

    public function getCompareFunctions()
    {
        return $this->_getRoutineResult('FUNCTION');
    }

    public function getCompareKeys()
    {
        $query = "
SELECT
    CONCAT( t.relname , ' [', i.relname, '] ' ) AS \"ARRAY_KEY_1\",
    a.attname                                   AS \"ARRAY_KEY_2\",
    ''                                          AS dtype
FROM
    pg_class     t,
    pg_class     i,
    pg_index     ix,
    pg_attribute a
WHERE
        t.oid       = ix.indrelid
    AND i.oid       = ix.indexrelid
    AND a.attrelid  = t.oid
    AND a.attnum    = ANY( ix.indkey )
    AND t.relkind   = 'r'
ORDER BY
    t.relname,
    i.relname
";

        return $this->_getCompareArray($query);

    }


    private function _getTableAndViewResult($type)
    {
        $query = "
SELECT
    cl.table_schema || '.' || cl.table_name AS \"ARRAY_KEY_1\",
    cl.column_name                          AS \"ARRAY_KEY_2\",
    cl.udt_name                             AS dtype
FROM
    information_schema.columns  cl,
    information_schema.tables   ss
WHERE
        cl.table_schema NOT IN ( 'pg_catalog', 'information_schema' )
    AND cl.table_name = ss.table_name
    AND ss.table_type = '{$type}'
ORDER BY
    1 --cl.table_name ";

        return $this->_getCompareArray($query);

    }

    private function _getRoutineResult($type)
    {
        $query = "
SELECT
    routine_schema || '.' || routine_name   AS \"ARRAY_KEY_1\",
    routine_definition                      AS \"ARRAY_KEY_2\",
    ''                                      AS dtype
FROM
    information_schema.routines
WHERE
        routine_schema  NOT IN ( 'pg_catalog', 'information_schema' )
    AND routine_type    =   '{$type}'
ORDER BY
    1";

        return $this->_getCompareArray($query, true);

    }

}
