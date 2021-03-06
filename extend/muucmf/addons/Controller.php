<?php
namespace muucmf\addons;

use think\Request;
use think\Config;
use think\Loader;
use think\Db;

/**
 * 插件前台基类控制器
 * Class Controller
 * @package think\addons
 */
class Controller extends \app\common\controller\Common
{
    // 当前插件操作
    protected $addon = null;
    protected $controller = null;
    protected $action = null;
    // 当前template
    protected $template;
    // 模板配置信息
    protected $config = [
        'type' => 'Think',
        'view_path' => '',
        'view_suffix' => 'html',
        'strip_space' => true,
        'view_depr' => DS,
        'tpl_begin' => '{',
        'tpl_end' => '}',
        'taglib_begin' => '{',
        'taglib_end' => '}',
    ];

    /**
     * 架构函数
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(Request $request = null)
    {
        // 生成request对象
        $this->request = is_null($request) ? Request::instance() : $request;
        // 初始化配置信息
        $this->config = Config::get('template') ?: $this->config;
        // 处理路由参数
        $route = $this->request->param('route', '');
        $param = explode('-', $route);
        // 是否自动转换控制器和操作名
        $convert = \think\Config::get('url_convert');
        // 格式化路由的插件位置
        $this->action = $convert ? strtolower(array_pop($param)) : array_pop($param);
        $this->controller = $convert ? strtolower(array_pop($param)) : array_pop($param);
        $this->addon = $convert ? strtolower(array_pop($param)) : array_pop($param);

        // 生成view_path
        $view_path = $this->config['view_path'] ?: 'view';

        // 重置配置
        Config::set('template.view_path', ADDONS_PATH . $this->addon . DS . $view_path . DS);
        
        parent::__construct($request);
    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array $vars 模板输出变量
     * @param array $replace 模板替换
     * @param array $config 模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        $controller = Loader::parseName($this->controller);
        if ('think' == strtolower($this->config['type']) && $controller && 0 !== strpos($template, '/')) {
            $depr = $this->config['view_depr'];
            $template = str_replace(['/', ':'], $depr, $template);
            if ('' == $template) {
                // 如果模板文件名为空 按照默认规则定位
                $template = str_replace('.', DS, $controller) . $depr . $this->action;
            } elseif (false === strpos($template, $depr)) {
                $template = str_replace('.', DS, $controller) . $depr . $template;
            }
        }
        return parent::fetch($template, $vars, $replace, $config);
    }

    /**
     * 获取插件的配置数组
     */
    final public function getConfig($name=''){
        static $_config = [];
        if(empty($name)){
            $name = $this->getName();
        }
        if(isset($_config[$name])){
            return $_config[$name];
        }
        $config = [];
        $map['name']    =   $name;
        $map['status']  =   1;
        $config  =   Db::name('Addons')->where($map)->value('config');
        if($config){
            $config   =   json_decode($config, true);
        }else{
            if (is_file($this->config_file)) {
                $temp_arr = include $this->config_file;
                foreach ($temp_arr as $key => $value) {
                    if($value['type'] == 'group'){
                        foreach ($value['options'] as $gkey => $gvalue) {
                            foreach ($gvalue['options'] as $ikey => $ivalue) {
                                $config[$ikey] = $ivalue['value'];
                            }
                        }
                    }else{
                        $config[$key] = $temp_arr[$key]['value'];
                    }
                }
            }
        }
        $_config[$name]     =   $config;
        return $config;
    }
}
