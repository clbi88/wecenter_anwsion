<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class setting_class extends AWS_MODEL
{
	public function get_settings()
	{
		if ($system_setting = $this->fetch_all('system_setting'))
		{
			foreach ($system_setting as $key => $val)
			{
				if ($val['value'])
				{
					// 修复反序列化漏洞: 使用安全的反序列化，不允许对象 (2025-11-09)
					// 原代码: $val['value'] = unserialize($val['value']);

					// 优先尝试JSON解码（推荐格式）
					$decoded = safe_json_decode($val['value']);
					if ($decoded !== false) {
						$val['value'] = $decoded;
					} else {
						// 向后兼容：使用安全的反序列化，不允许任何对象
						$val['value'] = safe_unserialize($val['value'], array());
						if ($val['value'] === false) {
							// 如果解码失败，使用原值
							$val['value'] = $system_setting[$key]['value'];
						}
					}
				}

				$settings[$val['varname']] = $val['value'];
			}
		}

		return $settings;
	}

	public function set_vars($vars)
	{
		if (!is_array($vars))
		{
			return false;
		}

		foreach ($vars as $key => $val)
		{
			$this->update('system_setting', array(
				'value' => serialize($val)
			), "`varname` = '" . $this->quote($key) . "'");
		}

		return true;
	}

	public function get_ui_styles()
	{
		if ($handle = opendir(ROOT_PATH . 'views'))
		{
			while (false !== ($file = readdir($handle)))
			{
				if (substr($file, 0, 1) != '.' AND is_dir(ROOT_PATH . 'views/' . $file))
				{
					$dirs[] = $file;
				}
			}

			closedir($handle);
		}

		$ui_style = array();

		foreach ($dirs as $key => $val)
		{
			$ui_style[] = array(
				'id' => $val,
				'title' => $val
			);
		}

		return $ui_style;
	}
}
