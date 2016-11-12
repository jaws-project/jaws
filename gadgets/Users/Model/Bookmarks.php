<?php
/**
 * Users Bookmarks model 
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Model_Bookmarks extends Jaws_Gadget_Model
{
    /**
     * Updates bookmark
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference ID
     * @param   bool    $bookmarked bookmarked?
     * @return  mixed   True if successful otherwise Jaws_Error
     */
    function UpdateBookmark($user, $gadget, $action, $reference, $bookmarked)
    {
        $bookmarked = (bool)$bookmarked;
        $objORM = Jaws_ORM::getInstance()->table('bookmarks');
        if ($bookmarked) {
            $result = $objORM->upsert(
                    array(
                        'user' => (int)$user, 'gadget'=> $gadget, 'action'=> $action, 'reference'=> $reference
                    )
                )->where('user', (int)$user)
                ->and()
                ->where('gadget', $gadget)
                ->and()
                ->where('action', $action)
                ->and()
                ->where('reference', $reference)
                ->exec();
        } else {
            $result = $objORM->delete()
                ->where('user', (int)$user)
                ->and()
                ->where('gadget', $gadget)
                ->and()
                ->where('action', $action)
                ->and()
                ->where('reference', $reference)
                ->exec();
        }

        return $result;
    }

    /**
     * Gets user's bookmarks
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  mixed   Bookmarks array if successful otherwise Jaws_Error
     */
    function GetBookmarks($user)
    {
        return Jaws_ORM::getInstance()->table('bookmarks')
            ->select('gadget', 'action', 'reference')
            ->where('user', (int)$user)
            ->fetchAll();
    }

}