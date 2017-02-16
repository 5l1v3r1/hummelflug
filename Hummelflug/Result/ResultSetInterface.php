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
}