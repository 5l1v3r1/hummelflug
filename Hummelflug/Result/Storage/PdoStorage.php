<?php

namespace Hummelflug\Result\Storage;

use Hummelflug\Result\ResultSetInterface;

class PdoStorage implements StorageInterface
{
    /**
     * @var string
     */
    private $dsn;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array
     */
    private $summaryTableMapping;

    /**
     * @var string
     */
    private $summaryTable;

    /**
     * @var array
     */
    private $detailsTableMapping;

    /**
     * @var string
     */
    private $detailsTable;

    /**
     * @var \PDO
     */
    private $dbh;

    /**
     * @param ResultSetInterface $resultSet
     * @return mixed
     */
    public function store(ResultSetInterface $resultSet)
    {
        $this->dbh = new \PDO($this->dsn, $this->username, $this->password);

        $this->storeSummary($resultSet);
        $this->storeDetails($resultSet);
    }

    private function storeSummary(ResultSetInterface $resultSet)
    {
        if ($this->summaryTable === null) {
            return;
        }

        if ($this->summaryTableMapping === null) {
            $this->summaryTableMapping = $this->getDefaultSummaryTableMapping($resultSet);
        }

        $sql = 'INSERT INTO ' . $this->summaryTable . ' '
             . '(' . implode(', ', array_values($this->summaryTableMapping)) . ') '
             . 'VALUES '
             . '(' . implode(', ', array_fill(0, count($this->summaryTableMapping), '?')) . ')';

        $statement = $this->dbh->prepare($sql);

        $values = [];

        foreach ($this->summaryTableMapping as $key => $value) {
            $method = 'get' . $key;

            if (!method_exists($resultSet, $method)) {
                throw new \Exception('Invalid mapping key: ' . $key);
            }

            $value = $resultSet->$method();

            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d h:i:s');
            }

            $values[] =  $value;
        }

        $res = $statement->execute($values);

        if ($res === false) {
            throw new \Exception('Could not store summary: ' . $statement->errorInfo()[2]);
        }

        return $this;
    }

    private function storeDetails(ResultSetInterface $resultSet)
    {
        if ($this->detailsTable === null) {
            return;
        }

        if ($this->detailsTableMapping === null) {
            $this->detailsTableMapping = $this->getDefaultDetailsTableMapping($resultSet);
        }

        $sql = 'INSERT INTO ' . $this->summaryTable . ' '
             . '(' . implode(', ', array_values($this->summaryTableMapping)) . ') '
             . 'VALUES '
             . '(' . implode(', ', array_fill(0, count($this->summaryTableMapping), '?')) . ')';

        $statement = $this->dbh->prepare($sql);

        $values = [];

        foreach ($this->summaryTableMapping as $key => $value) {
            $method = 'get' . $key;

            if (!method_exists($resultSet, $method)) {
                throw new \Exception('Invalid mapping key: ' . $key);
            }

            $value = $resultSet->$method();

            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d h:i:s');
            }

            $values[] =  $value;
        }

        $res = $statement->execute($values);

        if ($res === false) {
            throw new \Exception('Could not store summary: ' . $statement->errorInfo()[2]);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * @param string $dsn
     */
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getSummaryTableMapping()
    {
        return $this->summaryTableMapping;
    }

    /**
     * @param mixed $mapping
     *
     * @throws \Exception
     */
    public function setSummaryTableMapping($mapping)
    {
        $this->summaryTableMapping = json_decode($mapping, true);

        if ($this->summaryTableMapping === false) {
            throw new \Exception('Invalid summary table mapping. Check your configuration, please!');
        }
    }

    /**
     * @return string
     */
    public function getSummaryTable()
    {
        return $this->summaryTable;
    }

    /**
     * @param string $summaryTable
     */
    public function setSummaryTable($summaryTable)
    {
        $this->summaryTable = $summaryTable;
    }

    private function getDefaultSummaryTableMapping(ResultSetInterface $resultSet)
    {
        $mapping = [];

        foreach (get_class_methods(get_class($resultSet)) as $method) {
            if (strpos($method, 'get') !== 0) {
                continue;
            }

            $key = str_replace('get', '', $method);

            $mapping[$key] = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
        }

        return $mapping;
    }

    private function getDefaultDetailsTableMapping(ResultSetInterface $resultSet)
    {
        $mapping = [];

        foreach (get_class_methods(get_class($resultSet->getResults()[0])) as $method) {
            if (strpos($method, 'get') !== 0) {
                continue;
            }

            $key = str_replace('get', '', $method);

            $mapping[$key] = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
        }

        return $mapping;
    }

    /**
     * @return array
     */
    public function getDetailsTableMapping()
    {
        return $this->detailsTableMapping;
    }

    /**
     * @param array $detailsTableMapping
     */
    public function setDetailsTableMapping($detailsTableMapping)
    {
        $this->detailsTableMapping = $detailsTableMapping;
    }

    /**
     * @return string
     */
    public function getDetailsTable()
    {
        return $this->detailsTable;
    }

    /**
     * @param string $detailsTable
     */
    public function setDetailsTable($detailsTable)
    {
        $this->detailsTable = $detailsTable;
    }

}