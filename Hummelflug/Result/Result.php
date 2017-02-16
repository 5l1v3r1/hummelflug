<?php

namespace Hummelflug\Result;

class Result implements ResultInterface
{
    /**
     * @var integer
     */
    private $transactions;

    /**
     * @var float
     */
    private $elapsedTime;

    /**
     * @var float
     */
    private $dataTransferred;

    /**
     * @var float
     */
    private $responseTimeAverage;

//  string(41) "Transaction rate:         8.43 trans/sec

//  string(33) "Throughput:                       0.00 MB/sec

    /**
     * @var float
     */
    private $concurrency;

    /**
     * @var integer
     */
    private $transactionsSuccessful;

    /**
     * @var integer
     */
    private $transactionsFailed;

//  string(34) "Failed transactions:                 0

    /**
     * @var float
     */
    private $longestTransaction;

    /**
     * @var float
     */
    private $shortestTransaction;

    /**
     * @var string
     */
    private $attackId;

    /**
     * @var string
     */
    private $instanceId;

    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var string
     */
    private $mark;

    /**
     * @param mixed $transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * @param mixed $elapsedTime
     */
    public function setElapsedTime($elapsedTime)
    {
        $this->elapsedTime = $elapsedTime;
    }

    /**
     * @param mixed $dataTransferred
     */
    public function setDataTransferred($dataTransferred)
    {
        $this->dataTransferred = $dataTransferred;
    }

    /**
     * @param mixed $responseTimeAverage
     */
    public function setResponseTimeAverage($responseTimeAverage)
    {
        $this->responseTimeAverage = $responseTimeAverage;
    }

    /**
     * @param mixed $concurrency
     */
    public function setConcurrency($concurrency)
    {
        $this->concurrency = $concurrency;
    }

    /**
     * @param mixed $transactionsSuccessful
     */
    public function setTransactionsSuccessful($transactionsSuccessful)
    {
        $this->transactionsSuccessful = $transactionsSuccessful;
    }

    /**
     * @param mixed $longestTransaction
     */
    public function setLongestTransaction($longestTransaction)
    {
        $this->longestTransaction = $longestTransaction;
    }

    /**
     * @param mixed $shortestTransaction
     */
    public function setShortestTransaction($shortestTransaction)
    {
        $this->shortestTransaction = $shortestTransaction;
    }

    /**
     * @return integer
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @return float
     */
    public function getElapsedTime()
    {
        return $this->elapsedTime;
    }

    /**
     * @return float
     */
    public function getDataTransferred()
    {
        return $this->dataTransferred;
    }

    /**
     * @return float
     */
    public function getResponseTimeAverage()
    {
        return $this->responseTimeAverage;
    }

    /**
     * @return float
     */
    public function getConcurrency()
    {
        return $this->concurrency;
    }

    /**
     * @return integer
     */
    public function getTransactionsSuccessful()
    {
        return $this->transactionsSuccessful;
    }

    /**
     * @return integer
     */
    public function getTransactionsFailed()
    {
        return $this->transactionsFailed;
    }

    /**
     * @return float
     */
    public function getLongestTransaction()
    {
        return $this->longestTransaction;
    }

    /**
     * @return float
     */
    public function getShortestTransaction()
    {
        return $this->shortestTransaction;
    }

    /**
     * @return float
     */
    public function getAvailability()
    {
        return $this->transactionsSuccessful / ($this->transactionsSuccessful + $this->transactionsFailed);
    }

    /**
     * @param int $transactionsFailed
     */
    public function setTransactionsFailed($transactionsFailed)
    {
        $this->transactionsFailed = $transactionsFailed;
    }

    /**
     * @return string
     */
    public function getAttackId()
    {
        return $this->attackId;
    }

    /**
     * @param string $attackId
     */
    public function setAttackId($attackId)
    {
        $this->attackId = $attackId;
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @param string $instanceId
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;
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
        return $this->getDataTransferred() / $this->getElapsedTime();
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
}