<?php

namespace Stillman\Filters;

abstract class Filter
{
    public function __construct(\Kohana_Controller $controller, array $config)
    {
        $this->controller = $controller;
        $this->config = $config;
    }

    abstract public function run();
}