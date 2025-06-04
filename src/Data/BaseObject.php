<?php

namespace Vartruexuan\HyperfExcel\Data;

class BaseObject
{
    public function __construct(array $config = [])
    {
        // 初始化
        $this->initConfig($config);
    }

    protected function initConfig(array $config = [])
    {
        foreach ($config as $name => $value) {
            if(property_exists($this, $name)){
                $this->{$name} = $value;
            }
        }
    }

}