<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/10
 * Time: 下午7:14
 */
namespace app\components;

use yii\base\Component;
use yii\base\Exception;

class SFtp extends Component
{
    public $username;
    public $password;
    public $host;
    public $port = 22;

    private $_sftp_conn;
    private $_sftp;

    public function init()
    {
        if ($this->_sftp) {
            return;
        }
        $this->connect($this->host, $this->port);
        $login = ssh2_auth_password($this->_sftp_conn, $this->username, $this->password);
        if (!$login) {
            throw new Exception("Wrong username or password");
        }
        $this->_sftp = ssh2_sftp($this->_sftp_conn);
    }

    public function getSFtpPath($path)
    {
        $sftp = $this->_sftp;
        $path = "ssh2.sftp://$sftp" . '/' . trim($path, '/');
        return $path;
    }

    public function createDir($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        if ($recursive && !is_dir($parentDir)) {
            $this->createDir($parentDir, $mode, true);
        }
        try {
            $result = mkdir($path, $mode);
            ssh2_sftp_chmod($this->_sftp, $path, $mode);
        } catch (\Exception $e) {
            throw new \yii\base\Exception("Failed to create directory '$path': " . $e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * Open a SFTP connection
     *
     * @param string $host
     * @param bool $ssl
     * @param int $port
     * @param int $timeout
     *
     * @throws Exception If unable to connect
     */
    private function connect($host, $port)
    {
        try {
            $this->_sftp_conn = ssh2_connect($host, $port);
        } catch (\Exception $e) {
            throw new \yii\base\Exception("Failed to connect sftp: " . $e->getMessage(), $e->getCode(), $e);
        }

    }
}