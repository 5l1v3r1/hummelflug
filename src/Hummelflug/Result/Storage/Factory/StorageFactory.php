<?php

namespace Hummelflug\Result\Storage\Factory;

use Hummelflug\Result\Storage\StorageInterface;

class StorageFactory
{
    /**
     * @param array $options
     *
     * @return StorageInterface
     */
    public static function create(array $options)
    {
        $storage = new $options['type'];

        foreach ($options as $key => $value) {
            switch ($key) {
                case 'type';
                    break;
                default:
                    $method = 'set' . ucfirst($key);

                    if (method_exists($storage, $method)) {
                        $storage->$method($value);
                    }
            }
        }

        return $storage;
    }
}