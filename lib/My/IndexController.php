<?php
/**
 * 自訂程式
 *
 */
class My_IndexController extends GoEz_Controller
{
    /**
     * 預設動作
     *
     */
    public function indexAction()
    {
        $this->_view->renderTemplate('index.tpl.htm');
    }

    /**
     * Cron
     *
     */
    public function cronAction()
    {
        var_dump($this->_request->getParams());
    }
}