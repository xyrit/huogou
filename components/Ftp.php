<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/10
 * Time: 下午2:04
 */
namespace app\components;

use yii\base\Component;
use yii\base\Exception;

class Ftp extends Component
{

    public $username;
    public $password;
    public $host;
    public $ssl = false;
    public $port = 21;
    public $timeout = 90;

    private $_ftp_conn;

    public function init()
    {
        $this->connect($this->host, $this->ssl, $this->port, $this->timeout);
        try {
            ftp_login($this->_ftp_conn, $this->username, $this->password);
        } catch (\Exception $e) {
            throw new Exception('Wrong username or password');
        }
    }

    /** 把文件上传到ftp服务器
     * @param $remoteFile
     * @param $localFile
     * @param int $mode
     * @return bool
     */
    public function put($remoteFile, $localFile, $mode = FTP_BINARY)
    {
        return ftp_put($this->_ftp_conn, $remoteFile, $localFile, $mode);
    }

    /** 把文件下载到某个文件
     * @param $remoteFile
     * @param $localFile
     * @param int $mode
     * @return bool
     */
    public function get($remoteFile, $localFile, $mode = FTP_BINARY)
    {
        return ftp_get($this->_ftp_conn, $localFile, $remoteFile, $mode);
    }

    /** 打开一个ftp服务器的远程文件
     * @param $remoteFile
     * @param $mode
     * @return resource|bool
     */
    public function open($remoteFile, $mode)
    {
        $remoteFile = 'ftp://' . $this->username . ':' . $this->password . '@' . $this->host . '/' . trim($remoteFile, '/');
        return fopen($remoteFile, $mode);
    }

    /**
     * Downloads a file from the FTP server and saves to an open file
     *
     * @param resource $handle
     * @param string $remoteFile
     * @param int $mode
     * @param int $resumepos
     *
     * @return
     */
    public function fget($handle, $remoteFile, $mode = FTP_BINARY)
    {
        return ftp_fget($this->_ftp_conn, $handle, $remoteFile, $mode);
    }

    /**
     * Uploads from an open file to the FTP server
     *
     * @param string $remoteFile
     * @param resource $handle
     * @param int $mode
     * @param int $startPosision
     *
     * @return
     */
    public function fput($remoteFile, $handle, $mode = FTP_BINARY)
    {
        return ftp_fput($this->_ftp_conn, $remoteFile, $handle, $mode);
    }

    /** 是否是文件夹
     * @param $directory
     * @return bool
     */
    public function isDirectory($directory)
    {
        // get current directory
        $original_directory = ftp_pwd($this->_ftp_conn);
        // test if you can change directory to $dir
        // suppress errors in case $dir is not a file or not a directory
        if (@ftp_chdir($this->_ftp_conn, $directory)) {
            // If it is a directory, then change the directory back to the original directory
            ftp_chdir($this->_ftp_conn, $original_directory);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Changes the current directory to the specified one
     *
     * @return
     */
    public function changeDirectory($directory)
    {
        $result = @ftp_chdir($this->_ftp_conn, $directory);

        if ($result === false) {
            throw new Exception('Unable to change directory');
        }
        return $this;
    }

    /**
     * Changes to the parent directory
     *
     * @return
     */
    public function parentDirectory()
    {
        $result = @ftp_cdup($this->_ftp_conn);

        if ($result === false) {
            throw new Exception('Unable to get parent folder');
        }
        return $this;
    }

    /**
     * Returns the current directory name
     *
     * @return string
     */
    public function getDirectory()
    {
        $result = @ftp_pwd($this->_ftp_conn);

        if ($result === false) {
            throw new Exception('Unable to get directory name');
        }
        return $result;
    }

    /**
     * Creates a directory
     *
     * @param string $directory
     *
     * @return
     */
    public function createDirectory($directory)
    {
        $result = @ftp_mkdir($this->_ftp_conn, $directory);

        if ($result === false) {
            throw new Exception('Unable to create directory');
        }
        return $this;
    }

    /**
     * @param $ftpBaseDir
     * @param $ftpPath
     */
    public function createSubDirectory($ftpBaseDir, $ftpPath)
    {
        @ftp_chdir($this->_ftp_conn, $ftpBaseDir); // /var/www/uploads
        $parts = explode('/', $ftpPath); // 2013/06/11/username
        foreach ($parts as $part) {
            if (!@ftp_chdir($this->_ftp_conn, $part)) {
                ftp_mkdir($this->_ftp_conn, $part);
                ftp_chdir($this->_ftp_conn, $part);
                //ftp_chmod($this->_ftp_conn, 0755, $part);
            }
        }
    }

    /**
     * Removes a directory
     *
     * @param string $directory
     *
     * @return
     */
    public function removeDirectory($directory)
    {
        $result = @ftp_rmdir($this->_ftp_conn, $directory);

        if ($result === false) {
            throw new Exception('Unable to remove directory');
        }
        return $this;
    }

    /**
     * Returns a list of files in the given directory
     *
     * @param string $directory
     *
     * @return array
     */
    public function listDirectory($directory)
    {
        $result = @ftp_nlist($this->_ftp_conn, $directory);

        if ($result === false) {
            throw new Exception('Unable to list directory');
        }

        asort($result);
        return $result;
    }

    /**
     * @param string $parameters
     * @param bool $recursive
     *
     * @return array
     *
     * @throws \Exception
     */
    public function rawlistDirectory($parameters, $recursive = false)
    {
        $result = @ftp_rawlist($this->_ftp_conn, $parameters, $recursive);
        if ($result === false) {
            throw new Exception('Unable to list directory');
        }
        return $result;
    }

    /**
     * Deletes a file on the FTP server
     *
     * @param string $path
     *
     * @return
     */
    public function delete($path)
    {
        $result = @ftp_delete($this->_ftp_conn, $path);

        if ($result === false) {
            throw new Exception('Unable to get parent folder');
        }
        return $this;
    }

    /**
     * Returns the size of the given file.
     * Return -1 on error
     *
     * @param string $remoteFile
     *
     * @return int
     *
     */
    public function size($remoteFile)
    {
        $size = @ftp_size($this->_ftp_conn, $remoteFile);
        if ($size === -1) {
            throw new Exception('Unable to get file size');
        }
        return $size;
    }

    /**
     * Returns the last modified time of the given file.
     * Return -1 on error
     *
     * @param string $remoteFile
     *
     * @return int
     *
     */
    public function modifiedTime($remoteFile, $format = null)
    {
        $time = ftp_mdtm($this->_ftp_conn, $remoteFile);
        if ($time !== -1 && $format !== null) {
            return date($format, $time);
        } else {
            return $time;
        }
    }

    /**
     * Renames a file or a directory on the FTP server
     *
     * @param string $currentName
     * @param string $newName
     *
     * @return bool
     */
    public function rename($currentName, $newName)
    {
        $result = @ftp_rename($this->_ftp_conn, $currentName, $newName);
        return $result;
    }

    /**
     * Open a FTP connection
     *
     * @param string $host
     * @param bool $ssl
     * @param int $port
     * @param int $timeout
     *
     * @throws Exception If unable to connect
     */
    private function connect($host, $ssl = false, $port = 21, $timeout = 90)
    {
        if (!$ssl) {
            $this->_ftp_conn = ftp_connect($host, $port, $timeout);
        } else {
            $this->_ftp_conn = ftp_ssl_connect($host, $port, $timeout);
        }
        if (!$this->_ftp_conn) {
            throw new Exception('Unable to connect');
        }
    }

    /**
     * Closes FTP connection
     *
     * @return void
     */
    public function close()
    {
        $result = @ftp_close($this->_ftp_conn);
        if ($result === false) {
            throw new Exception('Unable to close connection');
        }
    }

}