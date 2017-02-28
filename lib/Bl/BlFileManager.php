<?php
SyL_Loader::userLib('Bl.Abstract');
SyL_Loader::userLib('Bl.FileStorageType');
SyL_Loader::userLib('Bl.FileStorageAbstract');
SyL_Loader::userLib('Data.FileArea');
SyL_Loader::userLib('BlueDriveException');

class BlFileManager extends BlAbstract
{
    /**
     * 範囲を取得する
     */
    public function createFileStorage($file_area_id)
    {
        $area = $this->getFileArea($file_area_id);
        return BlFileStorageAbstract::createInstance($area);
    }
    
    /**
     * 範囲を取得する
     */
    public function getFileArea($file_area_id)
    {
        $record = $this->getDaoAccess('bd_file_area')->select($file_area_id);
        if ($record == null) {
            throw new BlueDriveAuthorizationException("ファイル領域IDが正しくないか、または削除されています ({$file_area_id})");
        }

        $data = new DataFileArea();
        $data->file_area_id = $record->FILE_AREA_ID;
        $data->file_area_name = $record->FILE_AREA_NAME;
        $data->storage_type = $record->STORAGE_TYPE;
        $data->storage_name = BlFileStorageType::getName($record->STORAGE_TYPE);
        $data->root_directory = $record->ROOT_DIRECTORY;
        $data->root_url = $record->ROOT_URL;
        $data->connection_string = $record->CONNECTION_STRING;
        $data->valid_flag = $record->VALID_FLAG;
        $data->i_date = $record->I_DATE;
        $data->u_date = $record->U_DATE;
        return $data;
    }
    
    /**
     * ファイル保存領域の一覧を取得する
     */
    public function getFileAreas($storage_type, $page)
    {
        $select_rows = 20;

        list($dbrows, $pager) = $this->getDaoAccess('bd_file_area')->selectFileAreas($storage_type, $page, $select_rows);

        $rows = array();
        foreach ($dbrows as &$record) {
            $row = array();
            foreach ($record as $name => $value) {
                if ($name == 'STORAGE_TYPE') {
                    $row['STORAGE_NAME'] = BlFileStorageType::getName($value);
                } else {
                    $row[$name] = $value;
                }
            }
            $rows[] = $row;
        }

        $page_info['select_rows'] = $select_rows;
        $page_info['row_count'] = $pager->getPageCount();
        $page_info['row_max'] = $pager->getSum();
        $page_info['page_current'] = $pager->getCurrentPage();
        $page_info['page_max'] = $pager->getTotalPage();
        $page_info['range'] = $pager->getRange(4);

        return array($rows, $page_info);
    }

    /**
     * ファイル保存領域一覧を取得する
     */
    public function getFileAreaAll()
    {
        $access = $this->getDaoAccess('bd_file_area');
        $condition = $access->createCondition();
        $condition->addEqual('VALID_FLAG', '1');
        $dbrows = $access->selects($condition, array('FILE_AREA_NAME' => true, 'I_DATE' => false));

        $rows = array();
        foreach ($dbrows as &$record) {
            $row = array();
            foreach ($record as $name => $value) {
                switch ($name) {
                case 'STORAGE_TYPE':
                    $row[$name] = $value;
                    $row['STORAGE_NAME'] = BlFileStorageType::getName($value);
                    break;
                case 'FILE_AREA_ID':
                case 'FILE_AREA_NAME':
                case 'ROOT_DIRECTORY':
                case 'ROOT_URL':
                    $row[$name] = $value;
                    break;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * ファイル保存領域を登録する
     */
    public function registerFileArea($file_area_name, $storage_type, $root_directory, $root_url, $connection_string, $valid_flag, $i_id)
    {
        $current_date = date('Y-m-d H:i:s');

        $access = $this->getDaoAccess('bd_file_area');
        $record = $access->createRecord(true);
        $record->FILE_AREA_NAME = $file_area_name;
        $record->STORAGE_TYPE =  $storage_type;
        $record->ROOT_DIRECTORY = $root_directory;
        $record->ROOT_URL = $root_url;
        $record->CONNECTION_STRING = $connection_string;
        $record->VALID_FLAG = $valid_flag ? '1' : '0';
        $record->I_DATE = $current_date;
        $record->I_ID = $i_id;
        $record->U_DATE = $current_date;
        $record->U_ID = $i_id;
        $access->insert($record);
    }

    /**
     * ファイル保存領域を編集する
     */
    public function editFileArea($file_area_id, $file_area_name, $storage_type, $root_directory, $root_url, $connection_string, $valid_flag, $u_id)
    {
        $access = $this->getDaoAccess('bd_file_area');
        $record = $access->createRecord(true);
        $record->FILE_AREA_NAME = $file_area_name;
        $record->STORAGE_TYPE =  $storage_type;
        $record->ROOT_DIRECTORY = $root_directory;
        $record->ROOT_URL = $root_url;
        $record->CONNECTION_STRING = $connection_string;
        $record->VALID_FLAG = $valid_flag ? '1' : '0';
        $record->U_DATE = date('Y-m-d H:i:s');
        $record->U_ID = $u_id;

        if ($access->update($record, $file_area_id) == 0) {
            throw new SyL_DbRowNotFoundException("ファイル保存領域更新時に更新件数がありませんでした ({$file_area_id})");
        }
    }

    /**
     * ファイル保存領域を削除する
     */
    public function removeFileArea($file_area_id)
    {
        if ($this->getDaoAccess('bd_file_area')->delete($file_area_id) == 0) {
            throw new SyL_DbRowNotFoundException("ファイル保存領域削除時に更新件数がありませんでした ({$file_area_id})");
        }
    }
}
