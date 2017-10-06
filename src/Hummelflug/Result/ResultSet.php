<?php

namespace Hummelflug\Result;

class ResultSet implements ResultInterface, ResultSetInterface
{
    /**
     * @var string
     */
    private $attackId;

    /**
     * @var Result[]
     */
    private $results = [];

    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var string
     */
    private $mark;

    /**
     * @var array
     */
    private $additionalData = [];

    /**
     * ResultSet constructor.
     * @param Result[] $results
     * @param string $attackId
     */
    public function __construct(array $results, $attackId)
    {
        $this->results = $results;
        $this->attackId = $attackId;
    }

    /**
     * @return integer
     */
    public function getTransactions()
    {
        $transactions = 0;

        foreach ($this->results as $result) {
            $transactions += $result->getTransactions();
        }

        return $transactions;
    }

    /**
     * @return float
     */
    public function getElapsedTime()
    {
        $elapsedTime = 0;

        foreach ($this->results as $result) {
            $elapsedTime += $result->getElapsedTime();
        }

        if (count($this->results) > 0) {
            return $elapsedTime / count($this->results);
        }

        return  0;
    }

    /**
     * @return float
     */
    public function getDataTransferred()
    {
        $dataTransferred = 0;

        foreach ($this->results as $result) {
            $dataTransferred += $result->getDataTransferred();
        }

        return $dataTransferred;
    }

    /**
     * @return float
     */
    public function getResponseTimeAverage()
    {
        $responseTimeAverage = 0;

        foreach ($this->results as $result) {
            $responseTimeAverage += $result->getResponseTimeAverage();
        }

        if (count($this->results) > 0) {
            return $responseTimeAverage / count($this->results);
        }

        return 0;
    }

    /**
     * @return float
     */
    public function getConcurrency()
    {
        $transactions = $this->getTransactionsSuccessful() + $this->getTransactionsFailed();

        if ($this->getElapsedTime() > 0) {
            return $transactions / $this->getElapsedTime();
        }

        return 0;
    }

    /**
     * @return integer
     */
    public function getTransactionsSuccessful()
    {
        $transactionsSuccessful = 0;

        foreach ($this->results as $result) {
            $transactionsSuccessful += $result->getTransactionsSuccessful();
        }

        return $transactionsSuccessful;
    }

    /**
     * @return integer
     */
    public function getTransactionsFailed()
    {
        $transactionsFailed = 0;

        foreach ($this->results as $result) {
            $transactionsFailed += $result->getTransactionsFailed();
        }

        return $transactionsFailed;
    }

    /**
     * @return float
     */
    public function getLongestTransaction()
    {
        $longestTransaction = 0;

        foreach ($this->results as $result) {
            if ($result->getLongestTransaction() > $longestTransaction) {
                $longestTransaction = $result->getLongestTransaction();
            }
        }

        return $longestTransaction;
    }

    /**
     * @return float
     */
    public function getShortestTransaction()
    {
        $shortestTransaction = null;

        foreach ($this->results as $result) {
            if ($shortestTransaction === null || $result->getShortestTransaction() < $shortestTransaction) {
                $shortestTransaction = $result->getShortestTransaction();
            }
        }

        return $shortestTransaction;
    }

    /**
     * @return float
     */
    public function getAvailability()
    {
        $transactions = $this->getTransactionsSuccessful() + $this->getTransactionsFailed();

        if ($transactions > 0) {
            return $this->getTransactionsSuccessful() / $transactions;
        }

        return 0;
    }

    /**
     * @return Result[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return string
     */
    public function getAttackId()
    {
        return $this->attackId;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return float
     */
    public function getThroughput()
    {
        if ($this->getElapsedTime() > 0) {
            return $this->getDataTransferred() / $this->getElapsedTime();
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * @param string $mark
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAdditionalData($name)
    {
        return $this->additionalData[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAdditionalData($name, $value)
    {
        $this->additionalData[$name] = $value;
    }

    /**
     * @return bool
     */
    public function hasAdditionalData()
    {
        return !empty($this->additionalData);
    }

    /**
     * @return array
     */
    public function getAdditionalDataKeys()
    {
        return array_keys($this->additionalData);
    }

    /**
     * @return array
     */
    public function getAdditionalDataValues()
    {
        return array_values($this->additionalData);
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $property = str_replace('get', '', $name);

            if (array_key_exists($property, $this->additionalData)) {
                return $this->getAdditionalData($property);
            }
        }

        throw new \Exception('Method ' . $name . ' does not exist.');
    }
}