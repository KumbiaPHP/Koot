<?php

trait PagesTrait
{
    //protected string $pagesPath;

    public function __call($name, $params)
    {
        View::select('pages/' . implode('/', [$name, ...$params]));
    }
}
