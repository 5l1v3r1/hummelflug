<?php

namespace Hummelflug\Parser;

use Hummelflug\Result\Result;

/**
 * Class SiegeOutputParser
 * @package Hummelflug\Parser
 */
class SiegeOutputParser
{
    /**
     * @param $filename
     *
     * @return Result
     */
    public static function parse($filename)
    {
        $result = new Result();
        $content = file($filename);

        foreach ($content as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }

            list($key, $value) = explode(':', $line);

            $key = trim($key);
            $value = trim($value);

            switch ($key) {
                case 'Transactions':
                    $result->setTransactions((int) $value);
                    break;
                case 'Elapsed time':
                    $result->setElapsedTime((float) $value);
                    break;
                case 'Data transferred':
                    $result->setDataTransferred((float) $value);
                    break;
                case 'Response time':
                    $result->setResponseTimeAverage((float) $value);
                    break;
                case 'Concurrency':
                    $result->setConcurrency((float) $value);
                    break;
                case 'Successful transactions':
                    $result->setTransactionsSuccessful((integer) $value);
                    break;
                case 'Failed transactions':
                    $result->setTransactionsFailed((integer) $value);
                    break;
                case 'Longest transaction':
                    $result->setLongestTransaction((float) $value);
                    break;
                case 'Shortest transaction':
                    $result->setShortestTransaction((float) $value);
                    break;
            }
        }

        return $result;
    }
}