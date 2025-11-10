<?php
/**
 * WeCenter 安全函数库 - 反序列化防护
 * 
 * 用于防止反序列化漏洞
 * 
 * @author WeCenter Security Team
 * @version 1.0
 */

if (!defined('IN_ANWSION'))
{
	die;
}




// Security 1

/**
 * 安全的反序列化（白名单模式）
 * 
 * 只允许反序列化指定类的对象，防止对象注入攻击
 * 
 * @param string $data 序列化的数据
 * @param array $allowed_classes 允许的类白名单，空数组表示不允许任何类
 * @return mixed|false 反序列化的数据，失败返回false
 * 
 * @example
 * // 只允许stdClass
 * $data = safe_unserialize($serialized, array('stdClass'));
 * 
 * // 不允许任何对象，只允许基本类型
 * $data = safe_unserialize($serialized, array());
 */
function safe_unserialize($data, $allowed_classes = array())
{
	if (empty($data)) {
		return false;
	}
	
	// PHP 7.0+ 使用内置的 allowed_classes 选项
	if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
		try {
			return @unserialize($data, array('allowed_classes' => $allowed_classes));
		} catch (Exception $e) {
			if (defined('DEBUG') && DEBUG) {
				error_log('Unserialize error: ' . $e->getMessage());
			}
			return false;
		}
	}
	
	// PHP 5.x 手动验证
	// 检查是否包含对象序列化标记 O:数字:"类名"
	if (preg_match('/O:\d+:"/', $data)) {
		// 提取所有类名
		preg_match_all('/O:\d+:"([^"]+)"/', $data, $matches);
		
		if (!empty($matches[1])) {
			foreach ($matches[1] as $class_name) {
				// 检查类是否在白名单中
				if (!in_array($class_name, $allowed_classes)) {
					if (defined('DEBUG') && DEBUG) {
						error_log('Attempted to unserialize unauthorized class: ' . $class_name);
					}
					return false;
				}
			}
		}
	}
	
	try {
		return @unserialize($data);
	} catch (Exception $e) {
		if (defined('DEBUG') && DEBUG) {
			error_log('Unserialize exception: ' . $e->getMessage());
		}
		return false;
	}
}

/**
 * 带HMAC签名的安全序列化
 * 
 * 序列化数据并添加HMAC签名，防止数据被篡改
 * 
 * @param mixed $data 要序列化的数据
 * @param string $key HMAC密钥，默认使用G_SECUKEY
 * @return string JSON格式的带签名数据
 * 
 * @example
 * $signed = secure_serialize($data);
 * // 存储到数据库或文件
 */
function secure_serialize($data, $key = null)
{
	$key = $key ?: (defined('G_SECUKEY') ? G_SECUKEY : 'default_key');
	$serialized = serialize($data);
	$hmac = hash_hmac('sha256', $serialized, $key);
	
	return json_encode(array(
		'data' => base64_encode($serialized),
		'hmac' => $hmac,
		'time' => time(),
		'version' => '1.0'
	), JSON_UNESCAPED_UNICODE);
}

/**
 * 验证HMAC签名并反序列化
 * 
 * 验证数据完整性后再反序列化，防止数据被篡改
 * 
 * @param string $signed_data JSON格式的带签名数据
 * @param string $key HMAC密钥，默认使用G_SECUKEY
 * @param int $max_age 最大有效期（秒），0表示不检查时效
 * @param array $allowed_classes 允许的类白名单
 * @return mixed|false 反序列化的数据，失败返回false
 * 
 * @example
 * $data = secure_unserialize($signed_data);
 * 
 * // 带时效检查（24小时）
 * $data = secure_unserialize($signed_data, null, 86400);
 */
function secure_unserialize($signed_data, $key = null, $max_age = 0, $allowed_classes = array())
{
	$key = $key ?: (defined('G_SECUKEY') ? G_SECUKEY : 'default_key');
	$decoded = json_decode($signed_data, true);
	
	if (!is_array($decoded) || !isset($decoded['data']) || !isset($decoded['hmac'])) {
		if (defined('DEBUG') && DEBUG) {
			error_log('Invalid signed data format');
		}
		return false;
	}
	
	// 检查时效性
	if ($max_age > 0 && isset($decoded['time'])) {
		if (time() - $decoded['time'] > $max_age) {
			if (defined('DEBUG') && DEBUG) {
				error_log('Signed data expired');
			}
			return false;
		}
	}
	
	$serialized = base64_decode($decoded['data']);
	if ($serialized === false) {
		return false;
	}
	
	$expected_hmac = hash_hmac('sha256', $serialized, $key);
	
	// 防止时序攻击的比较
	if (function_exists('hash_equals')) {
		$valid = hash_equals($expected_hmac, $decoded['hmac']);
	} else {
		// PHP 5.5 以下版本的时间安全比较
		$valid = secure_strcmp($expected_hmac, $decoded['hmac']);
	}
	
	if (!$valid) {
		if (defined('DEBUG') && DEBUG) {
			error_log('HMAC verification failed - possible data tampering');
		}
		return false;
	}
	
	// 使用安全的反序列化
	if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
		return @unserialize($serialized, array('allowed_classes' => $allowed_classes));
	} else {
		return safe_unserialize($serialized, $allowed_classes);
	}
}

/**
 * 时间安全的字符串比较（防止时序攻击）
 * 
 * PHP 5.5 以下版本的 hash_equals 替代实现
 * 
 * @param string $known 已知的正确值
 * @param string $user 用户提供的值
 * @return bool
 */
function secure_strcmp($known, $user)
{
	if (function_exists('hash_equals')) {
		return hash_equals($known, $user);
	}
	
	$known_len = strlen($known);
	$user_len = strlen($user);
	
	// 长度不同但不立即返回
	$result = $known_len ^ $user_len;
	
	// 比较每个字符
	$compare_len = min($known_len, $user_len);
	for ($i = 0; $i < $compare_len; $i++) {
		$result |= ord($known[$i]) ^ ord($user[$i]);
	}
	
	return $result === 0;
}

/**
 * 检查序列化数据是否包含对象
 * 
 * @param string $data 序列化数据
 * @return bool
 * 
 * @example
 * if (has_serialized_objects($data)) {
 *     // 数据包含对象，需要额外验证
 * }
 */
function has_serialized_objects($data)
{
	return (bool)preg_match('/O:\d+:"/', $data);
}

/**
 * 迁移旧的序列化数据到JSON格式
 * 
 * 用于批量迁移数据库中的序列化数据
 * 
 * @param string $data 原始数据（可能是serialize或JSON）
 * @param array $allowed_classes 如果是序列化数据，允许的类白名单
 * @return string JSON格式数据
 * 
 * @example
 * // 在数据库迁移脚本中使用
 * $old_data = $row['data'];
 * $new_data = migrate_serialize_to_json($old_data);
 * $model->update('table', array('data' => $new_data), 'id = ' . $id);
 */
function migrate_serialize_to_json($data, $allowed_classes = array())
{
	if (empty($data)) {
		return json_encode(null);
	}
	
	// 尝试JSON解析
	$json_data = json_decode($data, true);
	if (json_last_error() === JSON_ERROR_NONE) {
		// 已经是JSON格式
		return $data;
	}
	
	// 尝试反序列化
	$unserialized = safe_unserialize($data, $allowed_classes);
	if ($unserialized !== false) {
		// 转换为JSON
		return json_encode($unserialized, JSON_UNESCAPED_UNICODE);
	}
	
	// 都失败了，返回空JSON
	if (defined('DEBUG') && DEBUG) {
		error_log('Failed to migrate data: ' . substr($data, 0, 100));
	}
	return json_encode(null);
}

/**
 * 安全的JSON解码（带错误处理）
 * 
 * @param string $data JSON字符串
 * @param bool $assoc 返回关联数组而不是对象
 * @param int $depth 递归深度
 * @return mixed|false 解码后的数据，失败返回false
 */
function safe_json_decode($data, $assoc = true, $depth = 512)
{
	if (empty($data)) {
		return false;
	}
	
	$result = json_decode($data, $assoc, $depth);
	
	if (json_last_error() !== JSON_ERROR_NONE) {
		if (defined('DEBUG') && DEBUG) {
			error_log('JSON decode error: ' . json_last_error_msg());
		}
		return false;
	}
	
	return $result;
}

/**
 * 安全的数据存储（自动选择最佳格式）
 * 
 * 优先使用JSON，如果必须序列化则添加签名
 * 
 * @param mixed $data 要存储的数据
 * @param bool $force_serialize 强制使用序列化（不推荐）
 * @return string
 */
function safe_data_encode($data, $force_serialize = false)
{
	if ($force_serialize) {
		// 使用带签名的序列化
		return secure_serialize($data);
	}
	
	// 优先使用JSON
	$json = json_encode($data, JSON_UNESCAPED_UNICODE);
	if (json_last_error() === JSON_ERROR_NONE) {
		return $json;
	}
	
	// JSON失败，使用序列化
	return secure_serialize($data);
}

/**
 * 安全的数据读取（自动识别格式）
 * 
 * 自动识别JSON或序列化格式并解码
 * 
 * @param string $data 存储的数据
 * @param array $allowed_classes 如果是序列化数据，允许的类
 * @return mixed|false
 */
function safe_data_decode($data, $allowed_classes = array())
{
	if (empty($data)) {
		return false;
	}
	
	// 尝试JSON解码
	$result = safe_json_decode($data);
	if ($result !== false) {
		return $result;
	}
	
	// 尝试带签名的反序列化
	$result = secure_unserialize($data, null, 0, $allowed_classes);
	if ($result !== false) {
		return $result;
	}
	
	// 尝试普通反序列化（向后兼容，但会记录警告）
	if (defined('DEBUG') && DEBUG) {
		error_log('Warning: Using unsafe unserialize for backward compatibility');
	}
	return safe_unserialize($data, $allowed_classes);
}

/**
 * 验证序列化数据的安全性
 * 
 * @param string $data 序列化数据
 * @return array 返回验证结果
 */
function validate_serialized_data($data)
{
	$result = array(
		'safe' => true,
		'issues' => array(),
		'has_objects' => false,
		'classes' => array()
	);
	
	if (empty($data)) {
		return $result;
	}
	
	// 检查是否包含对象
	if (preg_match('/O:\d+:"/', $data)) {
		$result['has_objects'] = true;
		
		// 提取类名
		preg_match_all('/O:\d+:"([^"]+)"/', $data, $matches);
		if (!empty($matches[1])) {
			$result['classes'] = array_unique($matches[1]);
			
			// 检查危险类
			$dangerous_classes = array(
				'PDO', 'PDOStatement',
				'mysqli', 'mysqli_stmt',
				'SimpleXMLElement',
				'DOMDocument',
				'SplFileObject',
				'DirectoryIterator',
				'RecursiveDirectoryIterator'
			);
			
			foreach ($result['classes'] as $class) {
				if (in_array($class, $dangerous_classes)) {
					$result['safe'] = false;
					$result['issues'][] = 'Dangerous class detected: ' . $class;
				}
			}
		}
	}
	
	// 检查是否包含资源类型（不能被序列化）
	if (strpos($data, 'i:0;') !== false) {
		$result['issues'][] = 'May contain resources';
	}
	
	return $result;
}


// Security 2


// ==========================================
// 1. 禁用危险的 eval() 函数使用
// ==========================================

/**
 * 安全的数组编码转换（替代原有的eval版本）
 */
if (!function_exists('convert_encoding_array_safe')) {
    function convert_encoding_array_safe($data, $from_encoding = 'GBK', $target_encoding = 'UTF-8')
    {
        if (!is_array($data)) {
            if (is_string($data) && function_exists('convert_encoding')) {
                return convert_encoding($data, $from_encoding, $target_encoding);
            }
            return $data;
        }

        $result = array();
        foreach ($data as $key => $value) {
            // 递归处理键名
            if (is_string($key) && function_exists('convert_encoding')) {
                $new_key = convert_encoding($key, $from_encoding, $target_encoding);
            } else {
                $new_key = $key;
            }

            // 递归处理值
            if (is_array($value)) {
                $result[$new_key] = convert_encoding_array_safe($value, $from_encoding, $target_encoding);
            } elseif (is_string($value) && function_exists('convert_encoding')) {
                $result[$new_key] = convert_encoding($value, $from_encoding, $target_encoding);
            } else {
                $result[$new_key] = $value;
            }
        }

        return $result;
    }
}

// ==========================================
// 2. 输入过滤助手函数
// ==========================================

/**
 * 安全获取GET参数
 */
if (!function_exists('get_safe')) {
    function get_safe($key, $type = 'string', $default = null)
    {
        if (!isset($_GET[$key])) {
            return $default;
        }

        $value = $_GET[$key];

        switch ($type) {
            case 'int':
            case 'integer':
                return intval($value);

            case 'float':
            case 'double':
                return floatval($value);

            case 'bool':
            case 'boolean':
                return (bool)$value;

            case 'email':
                $value = filter_var($value, FILTER_SANITIZE_EMAIL);
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : $default;

            case 'url':
                $value = filter_var($value, FILTER_SANITIZE_URL);
                return filter_var($value, FILTER_VALIDATE_URL) ? $value : $default;

            case 'alpha':
                return preg_replace('/[^a-zA-Z]/', '', $value);

            case 'alphanum':
                return preg_replace('/[^a-zA-Z0-9]/', '', $value);

            case 'string':
            default:
                // 移除SQL危险字符
                $value = strip_dangerous_sql($value);
                // 移除XSS
                $value = strip_xss_basic($value);
                return trim($value);
        }
    }
}

/**
 * 安全获取POST参数
 */
if (!function_exists('post_safe')) {
    function post_safe($key, $type = 'string', $default = null)
    {
        if (!isset($_POST[$key])) {
            return $default;
        }

        $value = $_POST[$key];

        // 使用与get_safe相同的过滤逻辑
        return get_safe($key, $type, $default);
    }
}

/**
 * 基础SQL危险字符过滤
 */
if (!function_exists('strip_dangerous_sql')) {
    function strip_dangerous_sql($str)
    {
        if (!is_string($str)) {
            return $str;
        }

        // 移除SQL注释
        $str = preg_replace('/\/\*.*?\*\//s', '', $str);
        $str = preg_replace('/(--|#).*?(\r|\n|$)/s', '', $str);

        // 移除多余空格
        $str = preg_replace('/\s+/', ' ', $str);

        return $str;
    }
}

/**
 * 基础XSS过滤
 */
if (!function_exists('strip_xss_basic')) {
    function strip_xss_basic($str)
    {
        if (!is_string($str)) {
            return $str;
        }

        // 移除危险标签
        $str = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $str);
        $str = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $str);
        $str = preg_replace('/<object[^>]*>.*?<\/object>/is', '', $str);
        $str = preg_replace('/<embed[^>]*>/is', '', $str);

        // 移除危险协议
        $str = preg_replace('/(javascript|vbscript|data):/i', '', $str);

        // 移除事件处理器
        $str = preg_replace('/on\w+\s*=/i', '', $str);

        return $str;
    }
}

// ==========================================
// 3. 输出转义助手函数
// ==========================================

/**
 * 安全HTML输出（防XSS）
 */
if (!function_exists('h')) {
    function h($str)
    {
        if (is_array($str)) {
            return array_map('h', $str);
        }
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

/**
 * 安全输出到HTML属性中
 */
if (!function_exists('attr')) {
    function attr($str)
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

/**
 * 安全输出到JavaScript中
 */
if (!function_exists('js')) {
    function js($str)
    {
        return json_encode($str, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}

// ==========================================
// 4. URL重定向验证
// ==========================================

/**
 * 验证URL是否为站内安全地址
 */
if (!function_exists('is_safe_redirect_url')) {
    function is_safe_redirect_url($url)
    {
        // 空URL不安全
        if (empty($url)) {
            return false;
        }

        // 允许相对路径
        if (substr($url, 0, 1) === '/') {
            return true;
        }

        // 检查是否为本站域名
        if (function_exists('base_url')) {
            $site_url = parse_url(base_url());
            $redirect_url = parse_url($url);

            if (isset($redirect_url['host'])) {
                return ($redirect_url['host'] === $site_url['host']);
            }
        }

        return false;
    }
}

/**
 * 安全重定向
 */
if (!function_exists('safe_redirect')) {
    function safe_redirect($url, $default = '/')
    {
        if (!is_safe_redirect_url($url)) {
            $url = $default;
        }

        if (function_exists('HTTP::redirect')) {
            HTTP::redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
}

// ==========================================
// 5. CSRF Token 简易实现
// ==========================================

/**
 * 生成CSRF Token
 */
if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (!isset($_SESSION)) {
            @session_start();
        }

        if (!isset($_SESSION['_csrf_token'])) {
            if (function_exists('random_bytes')) {
                $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $_SESSION['_csrf_token'] = md5(uniqid(mt_rand(), true));
            }
            $_SESSION['_csrf_token_time'] = time();
        }

        return $_SESSION['_csrf_token'];
    }
}

/**
 * 验证CSRF Token
 */
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token = null)
    {
        if (!isset($_SESSION)) {
            @session_start();
        }

        if ($token === null) {
            $token = isset($_POST['_csrf_token']) ? $_POST['_csrf_token'] :
                     (isset($_GET['_csrf_token']) ? $_GET['_csrf_token'] :
                     (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : null));
        }

        if (!isset($_SESSION['_csrf_token']) || $token === null) {
            return false;
        }

        // 检查过期（2小时）
        if (isset($_SESSION['_csrf_token_time']) && (time() - $_SESSION['_csrf_token_time'] > 7200)) {
            unset($_SESSION['_csrf_token']);
            unset($_SESSION['_csrf_token_time']);
            return false;
        }

        // 防时序攻击的比较
        if (function_exists('hash_equals')) {
            return hash_equals($_SESSION['_csrf_token'], $token);
        } else {
            return ($_SESSION['_csrf_token'] === $token);
        }
    }
}

/**
 * CSRF Token 表单字段
 */
if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return '<input type="hidden" name="_csrf_token" value="' . attr(csrf_token()) . '" />';
    }
}

// ==========================================
// 6. 安全日志记录
// ==========================================

/**
 * 记录安全事件
 */
if (!function_exists('log_security_event')) {
    function log_security_event($type, $message, $severity = 'warning')
    {
        $log_file = defined('ROOT_PATH') ? ROOT_PATH . 'data/security.log' : 'data/security.log';
        $log_dir = dirname($log_file);

        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }

        $log_entry = array(
            'time' => date('Y-m-d H:i:s'),
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown',
            'uri' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 200) : ''
        );

        @file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);

        // 如果文件过大（>10MB），轮转
        if (file_exists($log_file) && filesize($log_file) > 10 * 1024 * 1024) {
            @rename($log_file, $log_file . '.' . date('Ymd_His') . '.bak');
        }
    }
}