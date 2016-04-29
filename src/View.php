<?php
namespace cncNL;

class View {

	private $data = array();

	function __construct()
	{
	}

	public function assign($variable, $value)
	{
		$this->data[$variable] = $value;
	}

	public function render($template)
	{
		extract($this->data);
		$file = CNCNL_TEMPLATE_DIR . CNCNL_DS . $template . '.php';
		if (!file_exists($file)) {
			throw new \Exception("File doesn't exist");
		}
		ob_start();
		include($file);
		return ob_get_clean();
	}

	public function renderList($data, $class, $name)
	{
		$listdata = [
			'data' => $data,
			'class' => $class,
			'name' => $name
		];
		$this->assign('listdata', $listdata);
		return $this->render('admin_list');
	}
}
