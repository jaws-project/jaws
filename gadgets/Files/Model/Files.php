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
    function insertFiles($interface, $files, $move2folder = false)
    {
        if (!is_array($files) || empty($files)) {
            return false;
        }

        $data = array(
            'gadget'      => '',
            'action'      => '',
            'folder'      => '',
            'reference'   => 0,
            'type'        => 0,
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
                $actionpath = strtolower('files/'. $data['gadget']. '/'. $data['action']. '/');
                $filepath = $actionpath . strtolower(((string)$data['folder'] === '')? '' : ($data['folder'] . '/'));
                if ($move2folder) {
                    if ($this->gadget->fileManagement::mkdir(ROOT_DATA_PATH . $filepath)) {
                        $ret = $this->gadget->fileManagement::rename(
                            ROOT_DATA_PATH . $actionpath . $file['host_filename'],
                            ROOT_DATA_PATH . $filepath . $file['host_filename']
                        );
                        // FIXME: we need an action for moving failure
                    }
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
                'id:integer', 'gadget', 'action', 'reference', 'folder', 'type:integer', 'title', 'description',
                'public:boolean', 'postname', 'filename', 'mimetype', 'filetype:integer',
                'filesize:integer', 'filetime:integer', 'filehits:integer', 'filekey'
            )->where('id', (array)$ids, 'in')
            ->and()
            ->where('gadget', $interface['gadget'])
            ->and()
            ->where('action', $interface['action'])
            ->and()
            ->where('reference', $interface['reference'], is_array($interface['reference'])? 'in' : '=')
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
            'gadget'    => '',
            'action'    => '',
            'reference' => 0,
            'type'      => 0,
            'public'    => true,
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
                        ROOT_DATA_PATH. $filesPath.
                        (((string)$file['folder'] === '')? '' : ($file['folder']. '/')) . $file['filename']
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
                'id:integer', 'gadget', 'action', 'reference', 'folder', 'type:integer', 'title', 'description',
                'public:boolean', 'postname', 'filename', 'mimetype', 'filetype:integer',
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