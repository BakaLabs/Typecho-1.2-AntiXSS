<?php

namespace TypechoPlugin\AntiXSS;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Utils\Helper;

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Typecho 1.2 稳定版反 XSS 插件<br />
 * Typecho 1.2 Stable Version AntiXSS Plugin
 *
 * @package AntiXSS
 * @author ohmyga
 * @version 1.2.0-fix.1
 * @link https://github.com/BakaLabs/Typecho-1.2-AntiXSS
 */

class Plugin implements PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        if (Helper::options()->version != '1.2.0') {
            throw new \Typecho\Plugin\Exception(_t('插件 <b>AntiXSS</b> 仅适用于 Typecho 1.2.0 稳定版'));
        }
        
        // 注册评论回调函数
        \Typecho\Plugin::factory('Widget_Feedback')->comment = [__CLASS__, 'filterComment'];
        
        // 注册评论过滤器
        \Typecho\Plugin::factory('Widget_Abstract_Comments')->filter = [__CLASS__, 'filterComments'];
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Form $form)
    {
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {
    }

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render()
    {
    }

    /**
     * 评论过滤器
     * Ref: https://github.com/typecho/typecho/blob/daef17d7eb250419ff84f499e87d25ee71daac87/var/Typecho/Common.php#L533
     * 
     * @static
     * @access private
     * @param array $comment 评论数据
     * @return array
     */
    private static function __filterComment(array $comment, $isSelf = false) {
        $comment_url = $comment["url"];
        $url_params = parse_url(str_replace(["\r", "\n", "\t", " "], "", $comment_url));

        if (!empty($url_params)) {
            if (isset($url_params["scheme"]) && !in_array($url_params["scheme"], ["http", "https"])) {
                if (!$isSelf) {
                    throw new \Typecho\Widget\Exception(_t("个人主页地址格式错误"));
                }
            }
        }

        $url_params = array_map(function ($string) {
            $string = str_replace(['%0d', '%0a'], '', strip_tags($string));
            return preg_replace([
                "/\(\s*(\"|')/i",
                "/(\"|')\s*\)/i",
            ], '', $string);
        }, $url_params);

        if (isset($url_params["path"])) {
            $url_params["path"] = htmlspecialchars($url_params["path"]);
        }

        $comment["url"] = \Typecho\Common::buildUrl($url_params);

        return $comment;
    }

    /**
     * 提交评论过滤器
     * 
     * @static
     * @access public
     * @param array $comment 评论数据
     * @param mixed $post    文章数据
     * @return array
     */
    public static function filterComment(array $comment, $post)
    {
        return self::__filterComment($comment, false);
    }

    /**
     * 评论链接过滤器
     * 
     * @static
     * @access public
     * @param array $comment 评论数据
     * @return array
     */
    public static function filterComments(array $comment) {
        return self::__filterComment($comment, true);
    }
}
