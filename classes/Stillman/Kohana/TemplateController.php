<?php

namespace Stillman\Kohana;

use Kohana_Controller;
use Kohana_Exception;
use Stillman\Filters\Filter;
use View;

class TemplateController extends Kohana_Controller
{
	/**
	 * @var string|null Layout view filename
	 */
	public $layout = null;

	protected $actions = [];

	/**
	 * @var View|null Layout view object
	 */
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

	/**
	 * @param string $view
	 * @param array $data
	 * @param bool|string $render_layout
	 */
	public function render($view = null, array $data = [], $render_layout = true)
	{
		$this->response->body($this->renderToString($view, $data, $render_layout));
	}

	/**
	 * @param string $view
	 * @param array $data
	 * @param bool|string $render_layout
	 * @return string
	 */
	public function renderToString($view = null, array $data = [], $render_layout = false)
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

		if ($render_layout)
		{
			$_layout = null;

			if (is_string($render_layout))
			{
				$_layout = $render_layout;
			}
			elseif ($this->layout)
			{
				$_layout = $this->layout;
			}

			if ($_layout)
			{
				$this->_layout->set_filename($_layout);
				$this->_layout->content = $content;
				$this->_layout->_controller = $this;
				$content = $this->_layout->render();
			}
		}

		return $content;
	}

	/**
	 * Render a view without layout
	 */
	public function renderPartial($view, array $data = [])
	{
		return $this->render($view, $data, false);
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