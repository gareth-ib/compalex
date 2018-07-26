<?php

namespace compalex\Drivers;

use PDO;
use Exception;

abstract class BaseDriver
{

    protected $_dsn = array();

    protected static $_instance = null;

    protected function _getFirstConnect()
    {
        return $this->_getConnect(FIRST_DSN, FIRST_BASE_NAME);
    }

    protected function _getSecondConnect()
    {
        return $this->_getConnect(SECOND_DSN, SECOND_BASE_NAME);
    }

    protected function _getConnect($dsn)
    {
        if (! isset($this->_dsn[$dsn])) {
            $pdsn = parse_url($dsn);

            $dsn = DRIVER . ':host=' . $pdsn['host'] . ';port=' . $pdsn['port'] . ';dbname=' . substr($pdsn['path'], 1, 1000) . (DRIVER !== 'pgsql' ? ';charset=' . DATABASE_ENCODING : '');
            $this->_dsn[$dsn] = new PDO($dsn, $pdsn['user'], isset($pdsn['pass']) ? $pdsn['pass'] : '', array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ));
        }
        return $this->_dsn[$dsn];
    }

    protected function _select($query, $connect, $baseName)
    {
        $out = array();

        $query = str_replace('<<BASENAME>>', $baseName, $query);

        $stmt = $connect->prepare($query);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $out[] = $row;
        }
        return $out;
    }

    protected function _getCompareArray($query, $diffMode = false, $ifOneLevelDiff = false)
    {

        $out = array();

        $tablesFirst  = $this->_prepareOutArray($this->_select($query, $this->_getFirstConnect(), FIRST_BASE_NAME), $diffMode, $ifOneLevelDiff);
        $tablesSecond = $this->_prepareOutArray($this->_select($query, $this->_getSecondConnect(), SECOND_BASE_NAME), $diffMode, $ifOneLevelDiff);

        $allTableNames = array_unique(
            array_merge(
                array_keys($tablesFirst),
                array_keys($tablesSecond)
            )
        );

        sort($allTableNames);

        foreach ($allTableNames as $tableName) {

            $allFieldNames = array_unique(
                array_merge(
                    array_keys( $tablesFirst[$tableName]->fields  ?? [] ),
                    array_keys( $tablesSecond[$tableName]->fields ?? [] )
                )
            );

            foreach ($allFieldNames as $fieldName) {
                if (! isset($tablesFirst[$tableName]->fields[$fieldName])) {
                    if (isset($tablesSecond[$tableName]->fields[$fieldName])) {
                        $tablesSecond[$tableName]->fields[$fieldName]->isNew = true;
                        $tablesSecond[$tableName]->isNew = true;
                    }
                } else if (! isset($tablesSecond[$tableName]->fields[$fieldName])) {
                    if (isset($tablesFirst[$tableName]->fields[$fieldName])) {
                        $tablesFirst[$tableName]->fields[$fieldName]->isNew = true;
                        $tablesFirst[$tableName]->isNew = true;
                    }
                } else if (
                            isset($tablesFirst[$tableName]->fields[$fieldName]->dtype)
                        &&  isset($tablesSecond[$tableName]->fields[$fieldName]->dtype)
                        &&  $tablesFirst[$tableName]->fields[$fieldName]->dtype != $tablesSecond[$tableName]->fields[$fieldName]->dtype
                    ) {
                    $tablesFirst[$tableName]->fields[$fieldName]->changeType = true;
                    $tablesSecond[$tableName]->fields[$fieldName]->changeType = true;
                }
            }
            $out[$tableName] = array(
                'fArray' => $tablesFirst[$tableName]  ?? [],
                'sArray' => $tablesSecond[$tableName] ?? []
            );
        }
        return $out;
    }

    private function _prepareOutArray($result, $diffMode, $ifOneLevelDiff)
    {

        $mArray = array();
        foreach ($result as $row) {
            if ($diffMode) {
                foreach (explode("\n", $row['ARRAY_KEY_2']) as $pr) {
                    $mArray[$row['ARRAY_KEY_1']][$pr] = new \compalex\Objects\Field($row);
                }
            } else {
                if ($ifOneLevelDiff) {
                    $mArray[$row['ARRAY_KEY_1']] = new \compalex\Objects\Field($row);
                } else {
                    if( !isset( $mArray[$row['ARRAY_KEY_1']] ) ) {
                        $mArray[$row['ARRAY_KEY_1']] = new \compalex\Objects\Table();
                    }
                    $mArray[$row['ARRAY_KEY_1']]->fields[$row['ARRAY_KEY_2']] = new \compalex\Objects\Field($row);
                }
            }
        }
        return $mArray;
    }

    public function getCompareTables()
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getAdditionalTableInfo()
    {
        return array();
    }

    public function getCompareIndex()
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareProcedures()
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareFunctions()
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareViews()
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareKeys()
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareTriggers()
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getTableRows($baseName, $tableName, $rowCount = SAMPLE_DATA_LENGTH)
    {
        if (! $baseName)
            throw new Exception('$baseName is not set');
        if (! $tableName)
            throw new Exception('$tableName is not set');
        $rowCount = (int) $rowCount;
        $tableName = preg_replace("$[^A-z0-9.,-_]$", '', $tableName);
        switch (DRIVER) {
            case "mssql":
            case "dblib":
                $query = 'SELECT TOP ' . $rowCount . ' * FROM ' . $baseName . '..' . $tableName;
                break;
            case "pgsql":
            case "mysql":
                $query = 'SELECT * FROM ' . $tableName . ' LIMIT ' . $rowCount;
                break;
        }
        if ($baseName === FIRST_BASE_NAME) {
            $result = $this->_select($query, $this->_getFirstConnect(), FIRST_BASE_NAME);
        } else {
            $result = $this->_select($query, $this->_getSecondConnect(), SECOND_BASE_NAME);
        }

        if ($result) {
            $firstRow = array_shift($result);

            $out[] = array_keys($firstRow);
            $out[] = array_values($firstRow);

            foreach ($result as $row) {
                $out[] = array_values($row);
            }
        } else {
            $out = array();
        }

        if (DATABASE_ENCODING != 'utf-8' && $out) {
            // $out = array_map(function($item){ return array_map(function($itm){ return iconv(DATABASE_ENCODING, 'utf-8', $itm); }, $item); }, $out);
        }

        return $out;
    }
}