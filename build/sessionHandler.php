<?php
/**
 * This handler sets up a session handler similar to the default one
 * provided by PHP. It's implementation resulted from occasions where
 * session_start 'hung' the editor. 
 * PHP Version 7.1
 * 
 * @package MySessionHandler
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
class MySessionHandler implements SessionHandlerInterface
{
    private $_savePath;

    public function open($_savePath, $sessionName)
    {
        $this->_savePath = $_savePath;
        if (!is_dir($this->_savePath)) {
            mkdir($this->_savePath, 0777);
        }

        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        return (string)@file_get_contents("$this->_savePath/sess_$id");
    }

    public function write($id, $data)
    {
        return file_put_contents("$this->_savePath/sess_$id", $data) === false ? false : true;
    }

    public function destroy($id)
    {
        $file = "$this->_savePath/sess_$id";
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    public function gc($maxlifetime)
    {
        foreach (glob("$this->_savePath/sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }
}
