<?php
class Test
{
    protected $name;
    public function __construct(string $name)
    {
        $this->name=$name;
    }
}

//似乎会进行一个类型转换
$test = new Test(9.0);
$zero = var_export($test, true);
