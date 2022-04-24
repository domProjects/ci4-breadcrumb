<?php

/**
 * (c) domProjects (https://domprojects.com)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Libraries;

/**
 * Breadcrumb Generating Class
 *
 */
class Breadcrumb
{
	/**
	 * The current version of Breadcrumb Class
	 */
	public const BREADCRUMB_VERSION = '1.0.0';

	/**
	 * Data for breadcrumb items
	 *
	 * @var array
	 */
	public $items = [];

	/**
	 * Breadcrumb layout template
	 *
	 * @var array
	 */
	public $template;

	/**
	 * Newline setting
	 *
	 * @var string
	 */
	public $newline = "\n";

	/**
	 * Set the template from the table config file if it exists
	 *
	 * @param array $config (default: array())
	 */
	public function __construct($config = [])
	{
		// Require URL helper
		helper('url');

		// initialize config
		foreach ($config as $key => $val)
		{
			$this->template[$key] = $val;
		}
	}

	/**
	 * Set the template
	 *
	 * @param array $template
	 *
	 * @return bool
	 */
	public function setTemplate($template)
	{
		if (! is_array($template))
		{
			return false;
		}
		else
		{
			$this->template = $template;
			return true;
		}
	}

	/**
	 * Add a breadcrumb item
	 *
	 * Can be passed as an array or discreet params
	 *
	 * @return Breadcrumb
	 */
	public function addItem()
	{
		$this->items[] = $this->_prepArgs(func_get_args());

		return $this;
	}

	/**
	 * Prep Args
	 *
	 * Ensures a standard associative array format for all item page
	 *
	 * @return array
	 */
	protected function _prepArgs(array $args)
	{
		// If there is no $args[0], skip this and treat as an associative array
		// This can happen if there is only a single key, for example this is passed to breadcrumb->generate
		// array(array('foo'=>'bar'))
		if (isset($args[0]) && count($args) === 1 && is_array($args[0]) && ! isset($args[0]['page']))
		{
			$args = $args[0];
		}

		foreach ($args as $key => $val)
		{
			if (! is_array($val))
			{
				$args[$key] = [
					'page' => $key,
					'href' => $val
				];
			}
		}

		return $args;
	}

	/**
	 * Generate the breadcrumb
	 *
	 * @param	mixed	$breadcrumbData
	 * @return	string
	 */
	public function generate()
	{
		if ($this->items)
		{
			// Compile and validate the template date
			$this->_compileTemplate();

			// Build the breadcrumb
			$out = $this->template['tag_open'] . $this->newline;

			foreach ($this->items as $item)
			{
				foreach ($item as $key => $value)
				{
					$keys = array_keys($item);

					if (end($keys) == $key)
					{
						$out .= $this->template['crumb_active'] . $value['page'] . $this->template['crumb_close'] . $this->newline;
					}
					else
					{
						$out .= $this->template['crumb_open'];
						$out .= anchor($value['href'], $value['page']);
						$out .= $this->template['crumb_close'];
						$out .= $this->newline;
					}
				}
			}

			$out .= $this->template['tag_close'] . $this->newline;

			// Clear Breadcrumb class properties before generating the breadcrumb
			$this->clear();

			return $out;
		}
	}

	/**
	 * Clears the breadcrumb arrays. Useful if multiple breadcrumbs are being generated
	 *
	 * @return	Breadcrumb
	 */
	public function clear()
	{
		$this->items = [];

		return $this;
	}

	/**
	 * Compile Template
	 */
	protected function _compileTemplate()
	{
		if ($this->template === null)
		{
			$this->template = $this->_defaultTemplate();
			return;
		}

		foreach ($this->_defaultTemplate() as $field => $template)
		{
			if (! isset($this->template[$field]))
			{
				$this->template[$field] = $template;
			}
		}
	}

	/**
	 * Default Template
	 *
	 * @return array
	 */
	protected function _defaultTemplate()
	{
		return [
			'tag_open' => '<ol>',
			'tag_close' => '</ol>',
			'crumb_open' => '<li>',
			'crumb_close' => '</li>',
			'crumb_active' => '<li class="active">'
		];
	}
}
