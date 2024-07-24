<?php
/**
 * Files Gadget Admin
 *
 * @category    GadgetModel
 * @package     Files
 */
class Files_Model_Files extends Jaws_Gadget_Model
{
    /**
     * Insert attachments file info in DB
     *
     * @access  public
     * @param   array   $interface  Gadget connection interface
     * @param   array   $files  List of upload files in FileManagement format
     * @return  bool    True if insert successfully otherwise False
     */
    function insertFiles($interface, $files)
    {
        if (!is_array($files) || empty($files)) {
            return false;
        }

        $data = array(
            'gadget'      => '',
            'action'      => '',
            'reference'   => 0,
            'type'        => 0,
            'folderized'  => false,
            'description' => '',
            'public'      => true,
        );
        // remove invalid interface keys
        $interface = array_intersect_key($interface, $data);
        // set undefined keys by default values
        $data = array_merge($data, $interface);
        $data['user'] = $this->app->session->user->id;

        $resultFiles = array();
        $attachTable = Jaws_ORM::getInstance()->table('files');
        foreach ($files as $file) {
            $data['folderized'] = $interface['folderized'];
            $data['title']    = $file['user_filename'];
            $data['postname'] = $file['user_filename'];
            $data['filename'] = $file['host_filename'];
            $data['filesize'] = $file['host_filesize'];
            $data['mimetype'] = $file['host_mimetype'];
            $data['filetype'] = $file['host_filetype'];
            $data['filetime'] = time();
            $data['filehits'] = 0;
            $data['filekey']  = md5(uniqid('', true));

            $result = $attachTable->insert($data)->exec();  
            if (!Jaws_Error::IsError($result)) {
                $filepath = 'files/'. $interface['gadget']. '/'. $interface['action']. '/'. $data['filename'];
                if ($interface['folderized']) {
                    $filepath.= $interface['reference'];
                }
                $resultFiles[] = array_merge(
                    array(
                        'id' => $result,
                        'filepath' => strtolower($filepath)
                    ),
                    $data
                );
            }
        }

        return $resultFiles;
    }

    /**
     * Returns array of files details of a reference
     *
     * @access  public
     * @param   array   $interface  Gadget connection interface
     * @param   array   $ids        File(s) ID(s)
     * @return  array   List of files info or Jaws_Error on error
     */
    function getFiles($interface, $ids = array())
    {
        $data = array(
            'gadget'      => '',
            'action'      => '',
            'reference'   => 0,
            'type'        => 0,
            'public'      => true,
        );
        $interface = array_merge($data, $interface);
        $interface['user'] = $this->app->session->user->id;

        return Jaws_ORM::getInstance()
            ->table('files')
            ->select(
                'id:integer', 'type:integer', 'title', 'description', 'public:boolean', 'folderized:boolean',
                'postname', 'filename', 'mimetype', 'filetype:integer', 'filesize:integer', 'filetime:integer',
                'filehits:integer', 'filekey'
            )->where('id', (array)$ids, 'in')
            ->and()
            ->where('gadget', $interface['gadget'])
            ->and()
            ->where('action', $interface['action'])
            ->and()
            ->where('reference', $interface['reference'])
            ->and()
            ->where('type', $interface['type'])
            ->and()
            ->where('public', $interface['public'])
            ->fetchAll();
    }

    /**
     * Returns array of files details of a reference
     *
     * @access  public
     * @param   array   $interface  Gadget connection interface
     * @param   array   $ids        File(s) ID(s)
     * @return  array   List of files info or Jaws_Error on error
     */
    function deleteFiles($interface, $ids = array())
    {
        $data = array(
            'gadget'      => '',
            'action'      => '',
            'reference'   => 0,
            'type'        => 0,
            'public'      => true,
        );
        $interface = array_merge($data, $interface);
        $interface['user'] = $this->app->session->user->id;

        // get reference files list from database
        $files = $this->getFiles($interface, $ids);
        if (Jaws_Error::IsError($files)) {
            return $files;
        }

        $result = Jaws_ORM::getInstance()
            ->table('files')
            ->delete()
            ->where('id', (array)$ids, 'in')
            ->and()
            ->where('gadget', $interface['gadget'])
            ->and()
            ->where('action', $interface['action'])
            ->and()
            ->where('reference', $interface['reference'])
            ->and()
            ->where('type', $interface['type'])
            ->and()
            ->where('public', $interface['public'])
            ->exec();
        if (!Jaws_Error::IsError($result)) {
            $filesPath = strtolower('files/'. $interface['gadget']. '/'. $interface['action']. '/');
            foreach ($files as $file) {
                if (!empty($file['filename'])) {
                    $this->gadget->fileManagement::delete(
                        ROOT_DATA_PATH. $filesPath. ($file['folderized']? ($interface['reference'].'/') : ''). $file['filename']
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Returns array of files details of a reference
     *
     * @access  public
     * @param   int     $id     File ID
     * @return  array   List of files info or Jaws_Error on error
     */
    function getFile($id)
    {
        return Jaws_ORM::getInstance()
            ->table('files')
            ->table('files')->select(
                'id:integer', 'gadget', 'action', 'reference', 'type:integer', 'title', 'description',
                'public:boolean', 'folderized:boolean', 'postname', 'filename', 'mimetype', 'filetype:integer',
                'filesize:integer', 'filetime:integer', 'filehits:integer', 'filekey'
            )->where('id', $id)
            ->fetchRow();
    }

    /**
     * Increment download hits
     *
     * @access  public
     * @param   int     $id     File ID
     * @return  bool    True if hits was successfully increment and false on error
     */
    function hitDownload($id)
    {
        $table = Jaws_ORM::getInstance()->table('files');
        $result = $table->update(
            array(
                'filehits' => $table->expr('filehits + ?', 1)
            )
        )->where('id', $id)->exec();

        return !Jaws_Error::IsError($result);
    }

}