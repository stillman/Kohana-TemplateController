<?php

namespace Stillman\Kohana;

class TemplateController extends \Kohana_Controller
{
	public $layout = null;

	protected $actions = array();

	// Layout view object
	protected $_layout;

	public function before()
	{
		$this->_layout = new \View;
		parent::before();
	}

	public function render($view, array $data = array())
	{
		if ($view[0] === '/')
		{
			// This is an absolute path, do not add controller/directory prefix
			$view = substr($view, 1);
		}
		else
		{
			$view = strtolower(str_replace('_', '/', $this->request->controller())).'/'.$view;
		}

		$data['_controller'] = $this;
		$data['_layout'] = $this->_layout;

		$content = \View::factory($view, $data)->render();

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