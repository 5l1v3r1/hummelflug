<?php

namespace Hummelflug\Result\Storage;

use Hummelflug\Result\ResultSetInterface;

/**
 * Class CsvStorage
 * @package Hummelflug\Result\Storage
 */
class CsvStorage implements StorageInterface
{
    /**
     * @var string
     */
    private $summaryPath;

    /**
     * @var string;
     */
    private $detailsPath;

    /**
     * @param ResultSetInterface $resultSet
     *
     * @throws \Exception
     */
    public function store(ResultSetInterface $resultSet)
    {
        $this
            ->storeSummary($resultSet)
            ->storeDetails($resultSet);
    }

    /**
     * @param ResultSetInterface $resultSet
     *
     * @return CsvStorage
     * @throws \Exception
     */
    private function storeSummary(ResultSetInterface $resultSet)
    {
        $summaryFileExists = file_exists($this->summaryPath);

        $fh = @fopen($this->summaryPath, 'a');

        if ($fh === false) {
            throw new \Exception('Could not open file: ' . $this->summaryPath);
        }

        if ($summaryFileExists === false) {
            $csvHeader = [
                'AttackID',
                'Transactions',
                'ElapsedTime',
                'DataTransferred',
                'ResponseTimeAverage',
                'Concurrency',
                'TransactionsSuccessful',
                'TransactionsFailed',
                'LongestTransaction',
                'ShortestTransaction',
                'Availability',
                'Mark',
            ];

            if ($resultSet->hasAdditionalData()) {
                $csvHeader = array_merge(
                    $csvHeader,
                    $resultSet->getAdditionalDataKeys()
                );
            }

            fputcsv($fh, $csvHeader);
        }

        $csvData = [
            $resultSet->getAttackId(),
            $resultSet->getTransactions(),
            number_format($resultSet->getElapsedTime(), 2),
            number_format($resultSet->getDataTransferred(), 2),
            number_format($resultSet->getResponseTimeAverage(), 2),
            number_format($resultSet->getConcurrency(), 2),
            $resultSet->getTransactionsSuccessful(),
            $resultSet->getTransactionsFailed(),
            number_format($resultSet->getLongestTransaction(), 2),
            number_format($resultSet->getShortestTransaction(), 2),
            number_format($resultSet->getAvailability(), 2),
            $resultSet->getMark(),
        ];

        if ($resultSet->hasAdditionalData()) {
            $csvData = array_merge(
                $csvData,
                $resultSet->getAdditionalDataValues()
            );
        }

        fputcsv($fh, $csvData);

        fclose($fh);

        return $this;
    }

    /**
     * @param ResultSetInterface $resultSet
     *
     * @return CsvStorage
     * @throws \Exception
     */
    private function storeDetails(ResultSetInterface $resultSet)
    {
        $detailsFileExists = file_exists($this->detailsPath);

        $fh = @fopen($this->detailsPath, 'a');

        if ($fh === false) {
            throw new \Exception('Could not open file: ' . $this->detailsPath);
        }

        if ($detailsFileExists === false) {
            $csvHeader = [
                'AttackID',
                'InstanceId',
                'Transactions',
                'ElapsedTime',
                'DataTransferred',
                'ResponseTimeAverage',
                'Concurrency',
                'TransactionsSuccessful',
                'TransactionsFailed',
                'LongestTransaction',
                'ShortestTransaction',
                'Availability',
                'Mark',
            ];

            if ($resultSet->hasAdditionalData()) {
                $csvHeader = array_merge(
                    $csvHeader,
                    $resultSet->getAdditionalDataKeys()
                );
            }

            fputcsv($fh, $csvHeader);
        }

        foreach ($resultSet->getResults() as $result) {
            $csvData = [
                $result->getAttackId(),
                $result->getInstanceId(),
                $result->getTransactions(),
                number_format($result->getElapsedTime(), 2),
                number_format($result->getDataTransferred(), 2),
                number_format($result->getResponseTimeAverage(), 2),
                number_format($result->getConcurrency(), 2),
                $result->getTransactionsSuccessful(),
                $result->getTransactionsFailed(),
                number_format($result->getLongestTransaction(), 2),
                number_format($result->getShortestTransaction(), 2),
                number_format($result->getAvailability(), 2),
                $result->getMark(),
            ];

            if ($resultSet->hasAdditionalData()) {
                $csvData = array_merge(
                    $csvData,
                    $resultSet->getAdditionalDataValues()
                );
            }

            fputcsv($fh, $csvData);
        }

        fclose($fh);

        return $this;
    }

    /**
     * @return string
     */
    public function getSummaryPath()
    {
        return $this->summaryPath;
    }

    /**
     * @param string $summaryPath
     */
    public function setSummaryPath($summaryPath)
    {
        $this->summaryPath = $summaryPath;
    }

    /**
     * @return string
     */
    public function getDetailsPath()
    {
        return $this->detailsPath;
    }

    /**
     * @param string $detailsPath
     */
    public function setDetailsPath($detailsPath)
    {
        $this->detailsPath = $detailsPath;
    }
}