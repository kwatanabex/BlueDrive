<?php
require_once 'BlFileStorageType.php';

/**
 * ファイルストレージインターフェイス
 */
abstract class BlFileStorageAbstract
{
    private $area = null;

    /**
     * コンストラクタ
     */
    protected function __construct(DataFileArea $area)
    {
        $this->area = $area;
    }

    /**
     * ストレージクラスを生成する
     */
    public static function createInstance(DataFileArea $area)
    {
        switch ($area->storage_type) {
        case BlFileStorageType::LOCAL_STORAGE:
            include_once 'BlFileStorageLocalFile.php';
            return new BlFileStorageLocalFile($area);
        case BlFileStorageType::AZURE_BLOB_STORAGE:
            include_once 'BlFileStorageAzureBlob.php';
            return new BlFileStorageAzureBlob($area);
        default:
            throw new Exception('invalid parameter [type]');
        }
    }

    protected function getArea()
    {
        return $this->area;
    }

    /**
     * ファイルを取得する
     */
    public abstract function getFile($file);

    /**
     * ファイルリソースを取得する
     */
    public abstract function getFileStream($file);

    /**
     * ディレクトリ一覧を取得する
     */
    public abstract function getDirectoryList($dir);

    /**
     * ファイル一覧を取得する
     */
    public abstract function getFileList($dir);

    /**
     * ディレクトリを作成する
     */
    public abstract function createDirectory($dir, $name);

    /**
     * ディレクトリを削除する
     */
    public abstract function removeDirectory($dir);

    /**
     * ファイルを削除する
     */
    public abstract function removeFile($dir, array $files);

    /**
     * ファイルをアップロードする
     */
    public abstract function uploadFile($dir, array $uploadfiles);

    /**
     * 書き込み権限を確認する
     */
    public abstract function isWritable($dir);

    /**
     * ディレクトリ情報を生成する
     */
    protected static function createDirectoryData($name, $writable)
    {
        return array(
          'name'  => $name,
          'write' => $writable
        );
    }

    /**
     * ファイル情報を生成する
     */
    protected static function createFileData($name, $mtime, $size, $perm, $owner)
    {
        return array(
          'name'  => $name,
          'mtime' => $mtime,
          'size'  => $size,
          'perm'  => $perm,
          'owner' => $owner
        );
    }
}
