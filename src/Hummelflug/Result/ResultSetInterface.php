<?php

namespace Hummelflug\Result;

/**
 * Interface ResultSetInterface
 * @package Hummelflug\Result
 */
interface ResultSetInterface extends ResultInterface
{
    /**
     * ResultSetInterface constructor.
     * @param Result[] $results
     * @param string $attackId
     */
    public function __construct(array $results, $attackId);

    /**
     * @return Result[]
     */
    public function getResults();

    /**
     * @return string
     */
    public function getAttackId();

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAdditionalData($name);

    /**
     * @param string $additionalData
     */
    public function setAdditionalData($additionalData);

    /**
     * @return bool
     */
    public function hasAdditionalData();

    /**
     * @return array
     */
    public function getAdditionalDataKeys();

    /**
     * @return array
     */
    public function getAdditionalDataValues();
}