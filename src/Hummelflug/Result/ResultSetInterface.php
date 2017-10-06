<?php

namespace Hummelflug\Result;

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
     * @param string $name
     * @param mixed $value
     */
    public function setAdditionalData($name, $value);

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