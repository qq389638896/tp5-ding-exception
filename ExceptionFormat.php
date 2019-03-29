<?php

class ExceptionFormat
{

    /**
     * 格式化输出异常
     * @param \Exception $e
     * @param bool       $more
     * @return array
     * @User:Gover_chan
     * @Date: 2019/3/26
     */
    static public function get(\Exception $e, $more = true)
    {

        if (!$more) {

            $data = [
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'message' => $e->getMessage(),
            ];

        } else {

            $data = [
                'ip'      => getIP(),
                'env'     => env('app_env'),
                'date'    => date('Y/m/d H:i:s'),
                'name'    => get_class($e),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'message' => self::getMessage($e),
                'trace'   => self::getTrace($e),
                'code'    => self::getCode($e),
                'source'  => self::getSourceCode($e),
                'datas'   => self::getExtendData($e),
                'tables'  => [
                    'GET Data'              => $_GET,
                    'POST Data'             => $_POST,
                    'Files'                 => $_FILES,
                    'Cookies'               => $_COOKIE,
                    'Session'               => isset($_SESSION) ? $_SESSION : [],
                    'Server/Request Data'   => $_SERVER,
                    'Environment Variables' => $_ENV,
                    'ThinkPHP Constants'    => self::getConst(),
                ],
            ];

        }


        return $data;
    }


    /**
     * MarkDown格式字符串
     * @param \Exception $e
     * @return string
     * @User:Gover_chan
     * @Date: 2019/3/26
     */
    static public function getMarkDown(\Exception $e)
    {
        $sourceData = self::get($e);

        $error_file    = '[' . $sourceData['code'] . ']' . sprintf('%s in %s', self::my_parse_class($sourceData['name']), self::my_parse_file($sourceData['file'], $sourceData['line'])) . "\r\n";
        $error_message = nl2br(htmlentities($sourceData['message']));

        $markDownStr = <<<STR
# 【时间】  
> {$sourceData['date']}  

# 【环境】  
> {$sourceData['env']}  

# 【IP】  
> {$sourceData['ip']}    

# 【路径】  
> {$error_file}    

# 【信息】  
> {$error_message}    
STR;

        return $markDownStr;
    }

    /**
     * 简化对象转义成字符串
     * @param \Exception $e
     * @return array
     * @User:Gover_chan
     * @Date: 2019/3/27
     */
    static public function getTrace(\Exception $e)
    {

        $trace = $e->getTrace();

        foreach ($trace as &$value) {
            if (isset($value['args'])) {
                $value['args'] = self::parse_args($value['args']);
            }
        }

        return $trace;
    }

    /**
     * 钉钉ActionCard信息
     * @param \Exception $e
     * @return array
     * @User:Gover_chan
     * @Date: 2019/3/26
     */
    static public function getDingActionCard(\Exception $e)
    {
        $e_data = self::get($e);

        //存储钉钉ActionCard异常详细信息
        $dir = self::save_arr($e_data, 'dingding');


        //TODO 动态获取协议
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/common/dingError/' . base64_encode(rsaEncrypt(['dir' => $dir]));

        $rs = [
            'url'  => $url,
            'text' => self::getMarkDown($e),
        ];

        return $rs;
    }


    /**
     * 获取错误信息
     * ErrorException则使用错误级别作为错误编码
     * @access protected
     * @param  \Exception $e
     * @return string                错误信息
     */
    static public function getMessage(\Exception $e)
    {
        $message = $e->getMessage();

        if (PHP_SAPI == 'cli') {
            return $message;
        }

        $lang = \think\Container::get('lang');

        if (strpos($message, ':')) {
            $name    = strstr($message, ':', true);
            $message = $lang->has($name) ? $lang->get($name) . strstr($message, ':') : $message;
        } else if (strpos($message, ',')) {
            $name    = strstr($message, ',', true);
            $message = $lang->has($name) ? $lang->get($name) . ':' . substr(strstr($message, ','), 1) : $message;
        } else if ($lang->has($message)) {
            $message = $lang->get($message);
        }

        return $message;
    }

    /**
     * 获取错误编码
     * ErrorException则使用错误级别作为错误编码
     * @access protected
     * @param  \Exception $e
     * @return integer                错误编码
     */
    static public function getCode(\Exception $e)
    {
        $code = $e->getCode();

        if (!$code && $e instanceof \ErrorException) {
            $code = $e->getSeverity();
        }

        return $code;
    }


    /**
     * 获取出错文件内容
     * 获取错误的前9行和后9行
     * @access protected
     * @param  \Exception $e
     * @return array                 错误文件内容
     */
    static public function getSourceCode(\Exception $e)
    {
        // 读取前9行和后9行
        $line  = $e->getLine();
        $first = ($line - 9 > 0) ? $line - 9 : 1;

        try {
            $contents = file($e->getFile());
            $source   = [
                'first'  => $first,
                'source' => array_slice($contents, $first - 1, 19),
            ];
        } catch (\Exception $e) {
            $source = [];
        }

        return $source;
    }


    /**
     * 获取异常扩展信息
     * 用于非调试模式html返回类型显示
     * @access protected
     * @param  \Exception $e
     * @return array                 异常类定义的扩展数据
     */
    static public function getExtendData(\Exception $e)
    {
        $data = [];

        if ($e instanceof \think\Exception) {
            $data = $e->getData();
        }

        return $data;
    }


    /**
     * 返回异常模板
     * @param $e \Exception | array
     * @return \think\Response
     * @User:Gover_chan
     * @Date: 2019/3/26
     */
    static public function showTpl($e)
    {

        if ($e instanceof \Exception) {
            $data = self::get($e);
        } else {
            $data = $e;
        }

        //保留一层
        while (ob_get_level() > 1) {
            ob_end_clean();
        }

        $data['echo'] = ob_get_clean();

        ob_start();
        extract($data);
        include \think\facade\Env::get('app_path') . 'common/tpl/think_exception.tpl';

        // 获取并清空缓存
        $content  = ob_get_clean();
        $response = \think\Response::create($content, 'html');

        $response->code(200);

        return $response;
    }

    ###############
    ##保护工具函数##
    ###############

    protected static function my_parse_class($name)
    {
        $names = explode('\\', $name);

        return end($names);
    }

    protected static function my_parse_file($file, $line)
    {
        return basename($file) . " line {$line}";
    }

    protected static function getConst()
    {
        $const = get_defined_constants(true);

        return isset($const['user']) ? $const['user'] : [];
    }


    protected static function parse_class($name)
    {
        $names = explode('\\', $name);

        return '<abbr title="' . $name . '">' . end($names) . '</abbr>';
    }

    protected static function parse_file($file, $line)
    {
        return '<a class="toggle" title="' . "{$file} line {$line}" . '">' . basename($file) . " line {$line}" . '</a>';
    }

    protected static function parse_args($args)
    {
        $result = [];

        foreach ($args as $key => $item) {
            switch (true) {
                case is_object($item):
                    $value = sprintf('<em>object</em>(%s)', self::parse_class(get_class($item)));
                    break;
                case is_array($item):
                    if (count($item) > 3) {
                        $value = sprintf('[%s, ...]', self::parse_args(array_slice($item, 0, 3)));
                    } else {
                        $value = sprintf('[%s]', self::parse_args($item));
                    }
                    break;
                case is_string($item):
                    if (strlen($item) > 20) {
                        $value = sprintf(
                            '\'<a class="toggle" title="%s">%s...</a>\'',
                            htmlentities($item),
                            htmlentities(substr($item, 0, 20))
                        );
                    } else {
                        $value = sprintf("'%s'", htmlentities($item));
                    }
                    break;
                case is_int($item):
                case is_float($item):
                    $value = $item;
                    break;
                case is_null($item):
                    $value = '<em>null</em>';
                    break;
                case is_bool($item):
                    $value = '<em>' . ($item ? 'true' : 'false') . '</em>';
                    break;
                case is_resource($item):
                    $value = '<em>resource</em>';
                    break;
                default:
                    $value = htmlentities(str_replace("\n", '', var_export(strval($item), true)));
                    break;
            }

            $result[] = is_int($key) ? $value : "'{$key}' => {$value}";
        }

        return implode(', ', $result);
    }

    /**
     * 缓存数据
     * @param $data
     * @return string
     * @User:Gover_chan
     * @Date: 2019/3/27
     */
    protected static function save_arr($data, $type)
    {
        $rela_path = $type . '/' . md5(time()) . '.log';
        $path      = config('log.path') . '/' . $rela_path;
        $dirName   = dirname($path);
        if (!file_exists($dirName)) mkdir($dirName, 0755, true);

        $message = '<?php return $arr=' . var_export($data, true) . ";";

        error_log($message, 3, $path);

        return $rela_path;
    }

}
