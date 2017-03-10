<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Admin_Categories extends Jaws_Gadget_Model
{
    /**
     * Creates a new category
     *
     * @access  public
     * @param   string  $name           Category name
     * @param   string  $description    Category description
     * @param   string  $fast_url       Category fast url
     * @param   string  $meta_keywords  Meta keywords of the category
     * @param   string  $meta_desc      Meta description of the category
     * @param   array   $image_info     Image info
     * @param   array   $delete_image   Delete old image
     * @return  mixed   True on success, Jaws_Error on failure
     */
    function NewCategory($name, $description, $fast_url, $meta_keywords, $meta_desc, $image_info, $delete_image)
    {
        $fast_url = empty($fast_url) ? $name : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'blog_category');

        $now = Jaws_DB::getInstance()->date();
        $params['name']             = $name;
        $params['description']      = $description;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keywords;
        $params['meta_description'] = $meta_desc;
        $params['createtime']       = $now;
        $params['updatetime']       = $now;

        $objORM = Jaws_ORM::getInstance()->beginTransaction();
        $catTable = $objORM->table('blog_category');
        $categoryId = $catTable->insert($params)->exec();
        if (Jaws_Error::IsError($categoryId)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_ADDED'));
        }

        // move uploaded image file
        if ($delete_image) {
            Jaws_Utils::delete($this->GetCategoryLogoPath($categoryId));
        } else if (!empty($image_info)) {
            $tmpLogo = Jaws_Utils::upload_tmp_dir() . DIRECTORY_SEPARATOR . $image_info['host_filename'];

            // Save original Logo
            $objImage = Jaws_Image::factory();
            if (Jaws_Error::IsError($objImage)) {
                return Jaws_Error::raiseError($objImage->getMessage());
            }
            $objImage->load($tmpLogo);
            $res = $objImage->save($this->GetCategoryLogoPath($categoryId) , 'jpg');
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                //Rollback Transaction
                $objORM->rollback();

                // Return an error if image can't be resized
                return new Jaws_Error(_t('BLOG_ERROR_CANT_RESIZE_IMAGE'));
            }

            // Save resize logo
            $imgSize = explode('x', $this->gadget->registry->fetch('category_image_size'));
            if (empty($imgSize)) {
                $imgSize = array(128, 128);
            }
            $objImage = Jaws_Image::factory();
            if (Jaws_Error::IsError($objImage)) {
                return Jaws_Error::raiseError($objImage->getMessage());
            }
            $objImage->load($tmpLogo);
            $objImage->resize($imgSize[0], $imgSize[1]);
            $res = $objImage->save($this->GetCategoryLogoPath($categoryId), 'jpg');
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                //Rollback Transaction
                $objORM->rollback();

                // Return an error if image can't be resized
                return new Jaws_Error(_t('BLOG_ERROR_CANT_RESIZE_IMAGE'));
            }
            Jaws_Utils::delete($tmpLogo);
        }

        $this->gadget->acl->insert('CategoryAccess', $categoryId, true);
        $this->gadget->acl->insert('CategoryManage', $categoryId, false);
        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_CATEGORY_ADDED'), RESPONSE_NOTICE);

        //commit transaction
        $objORM->commit();
        return true;
    }

    /**
     * Updates a category entry
     *
     * @access  public
     * @param   int     $cid            Category ID
     * @param   string  $name           Category name
     * @param   string  $description    Category description
     * @param   string  $fast_url       Category fast url
     * @param   string  $meta_keywords  Meta keywords of the category
     * @param   string  $meta_desc      Meta description of the category
     * @param   array   $image_info     Image info
     * @param   array   $delete_image   Delete old image
     * @return  mixed   True on success, Jaws_Error on failure
     */
    function UpdateCategory($cid, $name, $description, $fast_url, $meta_keywords, $meta_desc, $image_info, $delete_image)
    {
        if(!$this->gadget->GetPermission('CategoryManage', $cid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_ACCESS_DENIED'), RESPONSE_ERROR);
            return false;
        }

        $fast_url = empty($fast_url) ? $name : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'blog_category', false);

        $params['name']             = $name;
        $params['description']      = $description;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keywords;
        $params['meta_description'] = $meta_desc;
        $params['updatetime']       = Jaws_DB::getInstance()->date();

        $catTable = Jaws_ORM::getInstance()->table('blog_category');
        $result = $catTable->update($params)->where('id', $cid)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_UPDATED'));
        }

        if ($this->gadget->registry->fetch('generate_category_xml') == 'true') {
            $model = $this->gadget->model->load('Feeds');
            $catAtom = $model->GetCategoryAtomStruct($cid);
            $model->MakeCategoryAtom($cid, $catAtom, true);
            $model->MakeCategoryRSS($cid, $catAtom, true);
        }

        // move uploaded image file
        if ($delete_image) {
            Jaws_Utils::delete($this->GetCategoryLogoPath($cid));
        } else if (!empty($image_info)) {
            $tmpLogo = Jaws_Utils::upload_tmp_dir() . DIRECTORY_SEPARATOR . $image_info['host_filename'];

            // Save original Logo
            $objImage = Jaws_Image::factory();
            if (Jaws_Error::IsError($objImage)) {
                return Jaws_Error::raiseError($objImage->getMessage());
            }
            $objImage->load($tmpLogo);
            $res = $objImage->save($this->GetCategoryLogoPath($cid) , 'jpg');
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                // Return an error if image can't be resized
                return new Jaws_Error(_t('BLOG_ERROR_CANT_RESIZE_IMAGE'));
            }

            // Save resize logo
            $imgSize = explode('x', $this->gadget->registry->fetch('category_image_size'));
            if (empty($imgSize)) {
                $imgSize = array(128, 128);
            }
            $objImage = Jaws_Image::factory();
            if (Jaws_Error::IsError($objImage)) {
                return Jaws_Error::raiseError($objImage->getMessage());
            }
            $objImage->load($tmpLogo);
            $objImage->resize($imgSize[0], $imgSize[1]);
            $res = $objImage->save($this->GetCategoryLogoPath($cid), 'jpg');
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                // Return an error if image can't be resized
                return new Jaws_Error(_t('BLOG_ERROR_CANT_RESIZE_IMAGE'));
            }
            Jaws_Utils::delete($tmpLogo);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_CATEGORY_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes a category entry
     *
     * @access  public
     * @param   int     $id     ID of category
     * @return  mixed   Returns True if Category was successfully deleted, else Jaws_Error
     */
    function DeleteCategory($id)
    {
        /**
         * Uncomment if you want don't want a category associated with a post
        $sql = "SELECT COUNT([entry_id]) FROM [[blog_entrycat]] WHERE [category_id] = {id}";
        $count = Jaws_DB::getInstance()->queryOne($sql, $params);
        if (Jaws_Error::IsError($count)) {
        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'));
        }

        if ($count > 0) {
        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORIES_LINKED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('BLOG_ERROR_CATEGORIES_LINKED'));
        }
         **/

        $entrycatTable = Jaws_ORM::getInstance()->table('blog_entrycat');
        $result = $entrycatTable->delete()->where('category_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'));
        }

        $catTable = Jaws_ORM::getInstance()->table('blog_category');
        $result = $catTable->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOG_ERROR_CATEGORY_NOT_DELETED'));
        }

        $this->gadget->acl->delete('CategoryAccess', $id);
        $this->gadget->acl->delete('CategoryManage', $id);
        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_CATEGORY_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Get category logo path
     *
     * @access  public
     * @param   int     $id     Category id
     * @return  bool True or error
     */
    function GetCategoryLogoPath($id)
    {
        return JAWS_DATA . 'blog' . DIRECTORY_SEPARATOR . 'categories' . DIRECTORY_SEPARATOR
            . $id . '.jpg';
    }

}