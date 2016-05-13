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

	/**
	 * Generate Youtube embed code by video id
	 * @param  int $video_url Video ID
	 * @return string            Generated embed code
	 */
	public function getVideoThumbnail($video_url)
	{
		preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $video_url, $matches);
		$video_id = $matches[0];
		return 'http://img.youtube.com/vi/' . $video_id . '/sddefault.jpg';
	}
}
