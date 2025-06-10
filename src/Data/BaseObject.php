<?php

namespace Vartruexuan\HyperfExcel\Data;

use Hyperf\Contract\Arrayable;

class BaseObject implements Arrayable
{
    public function __construct(array $config = [])
    {
        // 初始化
        $this->initConfig($config);
    }

    protected function initConfig(array $config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }
    }

    public function toArray(): array
    {
        $reflectionClass = new \ReflectionClass($this);
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
        $publicProperties = [];
        foreach ($properties as $property) {
            $value = $property->getValue($this);
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }
            $publicProperties[$property->getName()] = $value;
        }
        return $publicProperties;
    }

}