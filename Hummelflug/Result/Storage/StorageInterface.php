<?php

namespace Hummelflug\Result\Storage;

use Hummelflug\Result\ResultSetInterface;

interface StorageInterface
{
    /**
     * @param ResultSetInterface $resultSet
     * @return mixed
     */
    public function store(ResultSetInterface $resultSet);
}