<?php

namespace Stillman\Kohana;

use Kohana_Controller;
use Kohana_Exception;
use Stillman\Filters\Filter;
use View;

class TemplateController extends Kohana_Controller
{
	public $layout = null;

	protected $actions = [];

	// Layout view object
	protected $_layout;

	public function filters()
	{
		// Nothing by default
		return [];
	}

	public function before()
	{
		foreach ($this->filters() as $filter)
		{
			$class = $filter['class'];
			$obj = new $class($this, $filter);

			if ( ! $obj instanceof Filter)
			{
				throw new Kohana_Exception('Invalid filter class');
			}

			$obj->run();
		}

		$this->_layout = new \View;
		return parent::before();
	}

	public function render($view = NULL, array $data = [])
	{
		if ( ! $view)
		{
			// No view name specified, take it from action name
			$view = $this->request->action();
		}

		if ($view[0] === '/')
		{
			// This is an absolute path, do not add controller/directory prefix
			$view = substr($view, 1);
		}
		else
		{
			$directory = $this->request->directory();
			$controller = $this->request->controller();
			$path = $directory ? $directory.'/'.$controller : $controller;

			// The view is relative to the controller and directory
			$view = strtolower(str_replace('_', '/', $path)).'/'.$view;
		}

		$data['_controller'] = $this;
		$data['_layout'] = $this->_layout;

		$content = View::factory($view, $data)->render();

		if ($this->layout)
		{
			$this->_layout->set_filename($this->layout);
			$this->_layout->content = $content;
			$this->_layout->_controller = $this;
			$content = $this->_layout->render();
		}

		$this->response->body($content);
	}

	public function execute()
	{
		$actn = strtolower($this->request->action());

		if (isset($this->actions[$actn]))
		{
			$actn = $this->actions[$actn];
			$action = new $actn($this);
			$this->before();
			$action->execute();
			$this->after();

			return $this->response;
		}
		else
		{
			return parent::execute();
		}
	}
}