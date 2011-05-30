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
 * Response 類別
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class Response
{
    protected $_headers = array();

    public function setHeader($name, $value, $replace = false)
    {
        $this->canSendHeaders(true);
        $name = (string) $name;
        $value = (string) $value;

        if ($replace) {
            foreach ($this->_headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$key]);
                }
            }
        }

        $this->_headers[] = array(
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace
        );

        return $this;
    }

    public function sendHeaders()
    {
        if (count($this->_headers) || (200 != $this->_httpResponseCode)) {
            $this->canSendHeaders(true);
        } elseif (200 == $this->_httpResponseCode) {
            return $this;
        }

        $httpCodeSent = false;

        foreach ($this->_headers as $header) {
            if (!$httpCodeSent && $this->_httpResponseCode) {
                header($header['name'] . ': ' . $header['value'], $header['replace'], $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            }
        }

        if (!$httpCodeSent) {
            header('HTTP/1.1 ' . $this->_httpResponseCode);
            $httpCodeSent = true;
        }

        return $this;
    }

    protected $_isRedirect = false;

    public function isRedirect()
    {
        return $this->_isRedirect;
    }

    protected $_httpResponseCode = '200';

    public function setHttpResponseCode($code)
    {
        if (!is_int($code) || (100 > $code) || (599 < $code)) {
            throw new Exception('錯誤的 HTTP 回應代碼');
        }

        if ((300 <= $code) && (307 >= $code)) {
            $this->_isRedirect = true;
        } else {
            $this->_isRedirect = false;
        }

        $this->_httpResponseCode = $code;
        return $this;
    }

    public function getHttpResponseCode()
    {
        return $this->_httpResponseCode;
    }

    public function canSendHeaders($throw = false)
    {
        $ok = headers_sent($file, $line);
        if ($ok && $throw) {
            throw new Exception('無法傳送標頭；標頭已經 '
                    . $file . ' 的第 ' . $line . ' 行傳送了');
        }
        return !$ok;
    }

    protected $_body = array();

    public function setBody($content, $name = null)
    {
        if ((null === $name) || !is_string($name)) {
            $this->_body = array('default' => (string) $content);
        } else {
            $this->_body[$name] = (string) $content;
        }

        return $this;
    }

    public function appendBody($content, $name = null)
    {
        if ((null === $name) || !is_string($name)) {
            if (isset($this->_body['default'])) {
                $this->_body['default'] .= (string) $content;
            } else {
                $this->_body['default'] = $content;
            }
        } elseif (isset($this->_body[$name])) {
            $this->_body[$name] .= (string) $content;
        } else {
            $name = (string) $name;
            $this->_body[$name] = $content;
        }

        return $this;
    }

    public function outputBody()
    {
        $body = implode('', $this->_body);
        echo $body;
    }

    private $_exceptions = array();

    private $_renderExceptions = false;

    public function setException(Exception $e)
    {
        $this->_exceptions[] = $e;
    }

    public function getExceptions()
    {
        return $this->_exceptions;
    }

    public function isException()
    {
        return !empty($this->_exceptions);
    }

    public function renderExceptions($flag = null)
    {
        if (null !== $flag) {
            $this->_renderExceptions = $flag ? true : false;
        }

        return $this->_renderExceptions;
    }

    public function sendResponse()
    {
        $this->sendHeaders();
        if ($this->isException() && $this->renderExceptions()) {
            $this->displayException();
        } else {
            $this->outputBody();
        }
    }

    /**
     * 顯示異常
     *
     * @param bool $debug
     */
    public function displayException($debug = false)
    {
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        $body  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ';
        $body .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $body .= '<html xmlns="http://www.w3.org/1999/xhtml">';
        $body .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        $body .= '<title>程式發生錯誤</title></head><body>';
        $body .= '<h1 style="color:#f33;">程式發生錯誤</h1>';
        if ($debug) {
            foreach ($this->getExceptions() as $e) {
                $body .= '<p><strong>狀況： ' . $e->getMessage() . '</strong></p>';
                $body .= '<p><strong>追蹤資訊：</strong></p>';
                $body .= self::displayTrace($e->getTrace());
            }
        } else {
            $body .= '<p>您提供的網址或是您的操作造成了系統無法正確回應。</p>';
        }
        $body .= '</body></html>';
        $this->appendBody($body);
    }

    /**
     * 顯示錯誤流程
     *
     * @param array $traceList
     * @return string
     */
    protected static function displayTrace($traceList)
    {
        $result = '';
        foreach ($traceList as $trace) {
            $result .= '<pre style="border:1px solid #eee; background: #ffc; margin-left: 10px; padding: 5px; font-size: 12px;">';
            foreach ($trace as $col => $value) {
                $result .= '<strong>' . $col . '</strong>: ' . htmlspecialchars(print_r($value, true)) . "\n";
            }
            $result .= '</pre>';
        }
        return $result;
    }
}