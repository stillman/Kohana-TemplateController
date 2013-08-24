<?php

namespace Stillman\Kohana\Controller;

abstract class Action
{
	protected $controller;

	protected $request;

	protected $response;

	public function __construct(\Kohana_Controller $controller)
	{
		$this->controller = $controller;
		$this->request = $controller->request;
		$this->response = $controller->response;
	}

	public function render($view, array $data = array())
	{
		return $this->controller->render($view, $data);
	}

	abstract public function execute();
}