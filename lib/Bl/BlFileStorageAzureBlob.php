<?php
require_once 'BlFileStorageAbstract.php';
require_once 'WindowsAzure/WindowsAzure.php';

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Blob\Models\CreateBlobOptions;
use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\ListBlobsOptions;
use WindowsAzure\Blob\Models\PublicAccessType;

/**
 * Azure Blob ストレージ
 */
class BlFileStorageAzureBlob extends BlFileStorageAbstract
{
    /**
     * Azure Storage アクセスプロキシ
     */
    private $proxy = null;
    /**
     * Azure Storage アクセスプロキシ
     */
    const CREATE_DIRECTORY_DUMMY_FILE = '__BlueDriveCreateDirectoryDummyFile';

    /**
     * コンストラクタ
     */
    protected function __construct(DataFileArea $area)
    {
        parent::__construct($area);
        $this->proxy = ServicesBuilder::getInstance()->createBlobService($area->connection_string);
    }

    /**
     * ファイルを取得する
     */
    public function getFile($file)
    {
        $container_name = $this->getTargetContainer($file);
        $target = $this->getTargetPath($file);
        $blob = $this->proxy->getBlob($container_name, $target);

        $name = basename($target);
        $mtime = date('Y-m-d H:i', $blob->getProperties()->getLastModified()->format('U'));
        $size = $blob->getProperties()->getContentLength();
        $owner = $this->proxy->getAccountName();
        $perm = '';
        return self::createFileData($name, $mtime, $size, $perm, $owner);
    }

    /**
     * ファイルリソースを取得する
     */
    public function getFileStream($file)
    {
        $container_name = $this->getTargetContainer($file);
        $target = $this->getTargetPath($file);
        $blob = $this->proxy->getBlob($container_name, $target);
        return $blob->getContentStream();
    }

    /**
     * ディレクトリ一覧を取得する
     */
    public function getDirectoryList($dir)
    {
        $container_name = $this->getTargetContainer($dir);

        $dirs = array();
        if ($container_name) {
            $target = $this->getTargetPath($dir);
            $options = new ListBlobsOptions();
            $options->setDelimiter('/');
            if ($target) {
                $options->setPrefix($target);
            } else {
                $options->setPrefix('');
            }
            $prefixes = $this->proxy->listBlobs($container_name, $options)->getBlobPrefixes();
            foreach($prefixes as $prefix) {
                $name = $prefix->getName();
                if ($name == '/') {
                    $dirs[] = self::createDirectoryData('/', true);
                } else {
                    $tmps = explode('/', substr($name, 0, -1));
                    $dirs[] = self::createDirectoryData(array_pop($tmps), true);
                }
            }

        } else {
            $containers = $this->proxy->listContainers()->getContainers();
            foreach($containers as $container) {
                $dirs[] = self::createDirectoryData($container->getName(), true);
            }
        }

        return $dirs;
    }

    /**
     * ファイル一覧を取得する
     */
    public function getFileList($dir)
    {
        $container_name = $this->getTargetContainer($dir);

        $files = array();
        if ($container_name) {
            $target = $this->getTargetPath($dir);
            $options = new ListBlobsOptions();
            $options->setIncludeMetadata(true);
            $options->setDelimiter('/');
            if ($target) {
                $options->setPrefix($target);
            } else {
                $options->setPrefix('');
            }
            $blobs = $this->proxy->listBlobs($container_name, $options)->getBlobs();
            foreach($blobs as $blob) {
                $name = basename($blob->getUrl());
                if ($name == self::CREATE_DIRECTORY_DUMMY_FILE) {
                    continue;
                }
                $mtime = date('Y-m-d H:i', $blob->getProperties()->getLastModified()->format('U'));
                $size = $blob->getProperties()->getContentLength();
                $owner = $this->proxy->getAccountName();
                $perm = '';
                $files[] = self::createFileData($name, $mtime, $size, $perm, $owner);
            }
        }

        return $files;
    }

    /**
     * ディレクトリを作成する
     */
    public function createDirectory($dir, $dirname)
    {
        $container_name = $this->getTargetContainer($dir);
        if ($container_name === null) {
            // コンテナ作成
            $options = new CreateContainerOptions();
            $options->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
            $this->proxy->createContainer($dirname, $options);
        } else {
            // Azureストレージはディレクトリ概念がないので、指定名のディレクトリにダミーファイルを作成する。
            $dummy_file = $dir . '/' . $dirname . '/' . self::CREATE_DIRECTORY_DUMMY_FILE;
            $target = $this->getTargetPath($dummy_file);
            $this->proxy->createBlockBlob($container_name, $target, '');
        }
    }

    /**
     * ディレクトリを削除する
     */
    public function removeDirectory($dir)
    {
        $container_name = $this->getTargetContainer($dir);
        if ($container_name === null) {
            throw new Exception("invalid container. root directory not found ({$dir})");
        }

        $dummy_file = $dir . '/' . self::CREATE_DIRECTORY_DUMMY_FILE;
        $target = $this->getTargetPath($dummy_file);
        try {
            if ($target == self::CREATE_DIRECTORY_DUMMY_FILE) {
                // コンテナ削除
                $this->proxy->deleteContainer($container_name);
            } else {
                $this->proxy->deleteBlob($container_name, $target);
            }
        } catch (Exception $e) {
            SyL_Logger::warn($e);
        }
    }

    /**
     * ファイルを削除する
     */
    public function removeFile($dir, array $files)
    {
        $container_name = $this->getTargetContainer($dir);
        if ($container_name === null) {
            throw new Exception("container not found ({$dir})");
        }

        foreach ($files as $file) {
            $target_file = $dir . '/' . $file;
            $target = $this->getTargetPath($target_file);
            $this->proxy->deleteBlob($container_name, $target);
        }
    }

    /**
     * ファイルをアップロードする
     */
    public function uploadFile($dir, array $uploadfiles)
    {
        $container_name = $this->getTargetContainer($dir);
        if (!$container_name) {
            throw new Exception('コンテナ名が指定されていません');
        }

        foreach ($uploadfiles as $uploadfile) {
            $name = $uploadfile->getName();
            $blob_name = $this->getTargetPath($dir . '/' . $name);
            $tmp_name = $uploadfile->getTmpName();
            $fp = fopen($tmp_name, 'rb');
            try {
                $this->proxy->createBlockBlob($container_name, $blob_name, $fp);
                fclose($fp);
            } catch (Exception $e) {
                fclose($fp);
                throw $e;
            }
        }
    }

    /**
     * 書き込み権限を確認する
     */
    public function isWritable($file)
    {
        return true;
    }

    private function getTarget($file)
    {
        $root_dir = $this->getArea()->root_directory;
        $target = '/' . $root_dir . '/' . $file;
        return preg_replace('/\/\/+/', '/', $target);
    }

    private function getTargetContainer($file)
    {
        $target = $this->getTarget($file);
        if ($target == '/') {
            return null;
        } else {
            list($container) = explode('/', substr($target, 1), 2);
            return $container;
        }
    }

    private function getTargetPath($file)
    {
        $target = $this->getTarget($file);
        $tmp = explode('/', $target);
        switch (count($tmp)) {
        case 1: return '';
        case 2: return '';
        default:
            array_shift($tmp);
            array_shift($tmp);
            return implode('/', $tmp);
        }
    }

}
