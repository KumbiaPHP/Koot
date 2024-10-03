<?php

trait PagesTrait
{
    //protected string $pagesPath;

    public function __call($name, $params)
    {
        //array_unshift($params, $name);
        //exit(var_dump($name, $params));
        View::select('pages/' . implode('/', [$name, ...$params]));
    }
}
