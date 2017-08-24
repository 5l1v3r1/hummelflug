<?php

namespace Hummelflug\Result;

interface ResultInterface
{
    /**
     * @return integer
     */
    public function getTransactions();

    /**
     * @return float
     */
    public function getElapsedTime();

    /**
     * @return float
     */
    public function getDataTransferred();

    /**
     * @return float
     */
    public function getResponseTimeAverage();

    /**
     * @return float
     */
    public function getConcurrency();

    /**
     * @return integer
     */
    public function getTransactionsSuccessful();

    /**
     * @return integer
     */
    public function getTransactionsFailed();

    /**
     * @return float
     */
    public function getLongestTransaction();

    /**
     * @return float
     */
    public function getShortestTransaction();

    /**
     * @return float
     */
    public function getAvailability();

    /**
     * @return \DateTime
     */
    public function getStart();

    /**
     * @return float
     */
    public function getThroughput();

    /**
     * @return string
     */
    public function getMark();
}