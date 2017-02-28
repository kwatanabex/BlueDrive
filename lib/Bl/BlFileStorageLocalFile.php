<?php
require_once 'BlFileStorageAbstract.php';

/**
 * ローカルファイルストレージ
 */
class BlFileStorageLocalFile extends BlFileStorageAbstract
{
    /**
     * ファイルを取得する
     */
    public function getFile($file)
    {
        $target_file = $this->getTargetPath($file);
        $name = basename($target_file);
        $mtime = filemtime($target_file);
        $size = filesize($target_file);
        $owner = posix_getpwuid(fileowner($target_file));
        $perm = substr(sprintf('%o', fileperms($target_file)), -4);
        return self::createFileData($name, $mtime, $size, $perm, $owner['name']);
    }

    /**
     * ファイルリソースを取得する
     */
    public function getFileStream($file)
    {
        $target_file = $this->getTargetPath($file);
        return fopen($target_file, 'rb');
    }

    /**
     * ディレクトリ一覧を取得する
     */
    public function getDirectoryList($dir)
    {
        $target_dir = $this->getTargetPath($dir);

        $dirs = array();
        $it = new DirectoryIterator($target_dir);
        foreach ($it as $info) {
            if (!$info->isDot() && $info->isDir()) {
                $dirs[] = self::createDirectoryData($info->getBasename(), $info->isWritable());
            }
        }

        return $dirs;
    }

    /**
     * ファイル一覧を取得する
     */
    public function getFileList($dir)
    {
        $target_dir = $this->getTargetPath($dir);

        $files = array();
        $it = new DirectoryIterator($target_dir);
        foreach ($it as $info) {
            if ($info->isFile()) {
                $owner = posix_getpwuid($info->getOwner());
                $perm = substr(sprintf('%o', $info->getPerms()), -4);
                $mtime = date('Y-m-d H:i', $info->getMTime());
                $files[] = self::createFileData($info->getBasename(), $mtime, $info->getSize(), $perm, $owner['name']);
            }
        }

        return $files;
    }

    /**
     * ディレクトリを作成する
     */
    public function createDirectory($dir, $dirname)
    {
        $target_dir = $this->getTargetPath($dir);

        if (($dirname === '') || ($dirname === null)) {
            throw new Exception("invalid dir name ({$dirname})");
        }
        if (preg_match('/[\\\\\/:\*\?"<>\|\\[\] ]/', $dirname)) {
            throw new Exception("invalid dir name ({$dirname})");
        }
        if (!is_writable($target_dir)) {
            throw new Exception("directory permission denied ({$dirname})");
        }

        $newdir = $target_dir . '/' . $dirname;
        if (is_dir($newdir)) {
            throw new Exception("directory already exist ({$newdir})");
        }

        mkdir($newdir);
    }

    /**
     * ディレクトリを削除する
     */
    public function removeDirectory($dir)
    {
        $target_dir = $this->getTargetPath($dir);
        if (!is_writable(dirname($target_dir))) {
            throw new Exception("directory permission denied ({$target_dir})");
        }
        rmdir($target_dir);
    }

    /**
     * ファイルを削除する
     */
    public function removeFile($dir, array $files)
    {
        $target_dir = $this->getTargetPath($dir);
        if (!is_writable($target_dir)) {
            throw new Exception("directory permission denied ({$target_dir})");
        }

        foreach ($files as $file) {
            $file = $dir . '/' . $file;
            $target_file = $this->getTargetPath($file);
            if (is_file($target_file)) {
                unlink($target_file);
            }
        }
    }

    /**
     * ファイルをアップロードする
     */
    public function uploadFile($dir, array $uploadfiles)
    {
        $target_dir = $this->getTargetPath($dir);
        if (!is_writable($target_dir)) {
            throw new Exception("directory permission denied ({$target_dir})");
        }

        foreach ($uploadfiles as $uploadfile) {
            $uploadfile->upload($target_dir);
        }
    }

    /**
     * 書き込み権限を確認する
     */
    public function isWritable($file)
    {
        $target = $this->getTargetPath($file);
        return is_writable($target);
    }

    private function getTargetPath($file)
    {
        $root_dir = $this->getArea()->root_directory;
        $target = realpath($root_dir . $file);
        if (!$target) {
            throw new Exception("path not found ({$target})");
        }
        if (!preg_match('/^' . preg_quote($root_dir, '/') . '/', $target)) {
            throw new Exception("invalid path ({$target})");
        }

        if (!is_readable($target)) {
            throw new Exception("unable read file. permission denied ({$target})");
        }

        return $target;
    }
}
