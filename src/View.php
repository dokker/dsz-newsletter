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

	public function renderList($data, $class, $name, $selected_id = null)
	{
		$listdata = [
			'data' => $data,
			'class' => $class,
			'name' => $name
		];
		$this->assign('listdata', $listdata);
		if ($selected_id !== null) {
			$this->assign('selected_id', $selected_id);
		}
		return $this->render('admin_list');
	}

	/**
	 * Get selected shows markup
	 * @param  array $data Shows data
	 * @return string       HTML markup
	 */
	public function renderSelectedList($data)
	{
		$html = '';
		if (!empty($data)) {
			foreach ($data as $item) {
				$html .= sprintf('<li id="items_%d" data-id="%d">%s - %s - %s</li>',
					$item['id'], $item['id'], $item['title'], $item['date'], $item['location']);
			}
		}
		return $html;
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
