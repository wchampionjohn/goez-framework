<?php
/**
 * Goez
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 * @version    $Id$
 */

namespace Goez;

/**
 * 抽象 Controller 類別
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
abstract class Controller
{
    /**
     * @var array
     */
    protected $_config = array();

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @var \Goez\Request
     */
    protected $_request = null;

    /**
     * @param \Goez\Request $request
     */
    public function setRequest(Request $request)
    {
        $this->_request = $request;
    }

    /**
     * @return \Goez\Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @var \Goez\Db
     */
    protected $_db = null;

    /**
     * @param \Goez\Db $db
     */
    public function setDb(Db $db = null)
    {
        $this->_db = $db;
    }

    /**
     * @return \Goez\Db
     */
    public function getDb()
    {
        return $this->_db;
    }

    /**
     * View
     *
     * @var Goez_View
     */
    protected $_view = null;

    /**
     * @param \Goez\View $view
     */
    public function setView(View $view)
    {
        $this->_view = $view;
    }

    /**
     * @return \Goez\View
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Controller 初始化
     *
     */
    public function init() {}

    /**
     * Dispatch 之前
     *
     */
    public function beforeDispatch() {}

    /**
     * Dispatch 之後
     *
     */
    public function afterDispatch() {}

    /**
     * 預設動作
     *
     */
    public function indexAction() {}

    /**
     * 頁面重導向
     *
     * 如果代入的 url 不是一般的 http:// 網址，
     * 那麼就會在前面加上 BaseUrl 後再做導向的動作
     *
     * @param string $url
     */
    public function redirect($url)
    {
        if (!$this->_request->isAjax()) {
            if (!preg_match('/^[a-z]+?:\/\//i', $url)) {
                $url = $this->_request->getBaseUrl() . '/' . ltrim($url, '/');
            }
            header('Location: ' . $url);
            exit;
        }
    }

    /**
     * 設定讓瀏覽器下載檔案的標頭
     *
     * @param string $fileName
     * @param int $fileSize
     */
    public function setDownloadHeader($fileName = 'unnamed', $fileSize = null)
    {
        header('Pragma: public');
        header('Expires: 0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i ') . ' GMT');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/octet-stream');
        if ($fileSize) { header('Content-Length: ' . $fileSize); }
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Content-Transfer-Encoding: binary');
    }
}