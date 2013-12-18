<?php
/**
 * Forums Gadget
 *
 * @category   GadgetModel
 * @package    Forums
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Model_Attachments extends Jaws_Gadget_Model
{
    /**
     * Returns array of Attachments information of one post
     *
     * @access  public
     * @param   int     $pid        Post ID
     * @return  array   List of attachments info or Jaws_Error on error
     */
    function GetAttachments($pid)
    {
        $attachTable = Jaws_ORM::getInstance()->table('forums_attachments');
        return $attachTable->select('*')->where('post', $pid)->fetchAll();
    }

    /**
     * Returns array of information of one attachment
     *
     * @access  public
     * @param   int     $aid        Attachment ID
     * @return  array   Info of selected attachment or Jaws_Error on error
     */
    function GetAttachmentInfo($aid)
    {
        $attachTable = Jaws_ORM::getInstance()->table('forums_attachments');
        return $attachTable->select('*')->where('id', $aid)->fetchRow();
    }

    /**
     * Returns array of Attachments information of one topic
     *
     * @access  public
     * @param   int     $tid        Topic ID
     * @return  array   List of attachments info or Jaws_Error on error
     */
    function GetTopicAttachments($tid)
    {
        $attachTable = Jaws_ORM::getInstance()->table('forums_attachments');
        $attachTable->join('forums_posts', 'forums_attachments.post', 'forums_posts.id');
        $attachTable->select('forums_attachments.id', 'post', 'title', 'filename', 'filesize', 'filetype', 'hitcount');
        return $attachTable->where('tid', $tid)->fetchAll();
    }

    /**
     * Insert attachments file info in DB
     *
     * @access  public
     * @param   int     $pid    Post ID
     * @param   array   $files  List of upload files with jaws_utils format
     * @return  boolean If insert complete true else Jaws_Error on error
     */
    function InsertAttachments($pid, $files)
    {
        if (is_array($files)) {
            $attachTable = Jaws_ORM::getInstance()->table('forums_attachments');
            $data['post'] = $pid;
            foreach ($files as $fileInfo) {
                $data['title'] = $fileInfo['user_filename'];
                $data['filename'] = $fileInfo['host_filename'];
                $data['filesize'] = $fileInfo['host_filesize'];
                $data['filetype'] = $fileInfo['host_filetype'];
                $data['hitcount'] = isset($fileInfo['hitcount'])? (int)$fileInfo['hitcount'] : 0;
                $result = $attachTable->insert($data)->exec();                
            }

            return true;
        }

        return false;
    }

    /**
     * Returns count of Attachments of one post
     *
     * @access  public
     * @param   int   $pid      Post ID
     * @return  int   Count of records of one post
     */
    function GetAttachmentsCount($pid)
    {
        $attachTable = Jaws_ORM::getInstance()->table('forums_attachments');
        return $attachTable->select('count(id) as attach_count:integer')->where('post', $pid)->fetchOne();
    }

    /**
     * Delete one attachment
     *
     * @access  public
     * @param   int         $aid      Attachment ID
     * @return  boolean     If complete true else Jaws_Error on error
     */
    function DeleteAttachment($aid)
    {
        $attachmentInfo = $this->GetAttachmentInfo($aid);
        $attachTable = Jaws_ORM::getInstance()->table('forums_attachments');
        $result = $attachTable->delete()->where('id', $aid)->exec();
        if ($result && !empty($attachmentInfo['filename'])) {
            Jaws_Utils::Delete(JAWS_DATA . 'forums/' . $attachmentInfo['filename']);
        }
        return $result;
    }

    /**
     * Delete one attachment record and file
     *
     * @access  public
     * @param   int         $aid        Attachment ID
     * @param   string      $filename   Real file name to delete
     * @return  boolean     If complete true else Jaws_Error on error
     */
    function DeleteAttachmentWithFName($aid, $filename)
    {
        $attachTable = Jaws_ORM::getInstance()->table('forums_attachments');
        $result = $attachTable->delete()->where('id', $aid)->exec();
        if ($result) {
            Jaws_Utils::Delete(JAWS_DATA . 'forums/' . $filename);
        }
        return $result;
    }

    /**
     * Delete all attachments of one post
     *
     * @access  public
     * @param   aid         $pid      post ID
     * @return  boolean     If complete true else Jaws_Error on error
     */
    function DeletePostAttachments($pid)
    {
        $attachmentsInfo = $this->GetAttachments($pid);
        $attachTable = Jaws_ORM::getInstance()->table('forums_attachments');
        $result = $attachTable->delete()->where('post', $pid)->exec();
        if ($result) {
            foreach ($attachmentsInfo as $attachment) {
                if (!empty($attachment['filename'])) {
                    Jaws_Utils::Delete(JAWS_DATA . 'forums/' . $attachment['filename']);
                }
            }
        }
        return $result;
    }

    /**
     * Increment attachment download hits
     *
     * @access  public
     * @param   int     $aid    Attachment ID
     * @return  mixed   True if hits increased successfully or Jaws_Error on error
     */
    function HitAttachmentDownload($aid)
    {
        $table = Jaws_ORM::getInstance()->table('forums_attachments');
        $res = $table->update(
            array(
                'hitcount' => $table->expr('hitcount + ?', 1)
            )
        )->where('id', $aid)->exec();

        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return true;
    }
}