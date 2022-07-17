<?php
/**
 * Quotes Model
 *
 * @category   GadgetModel
 * @package    Quotes
 */
class Quotes_Model_Admin_Quotes extends Jaws_Gadget_Model
{
    /**
     * add new quote
     *
     * @access  public
     * @param   array    $data
     * @return  bool
     */
    function add(array $data)
    {
        // begin transaction
        $objORM = Jaws_ORM::getInstance()->beginTransaction();

        $data['inserted'] = $data['updated'] = time();
        $qId = $objORM->table('quotes')
            ->insert($data)
            ->exec();
        if (Jaws_Error::IsError($qId)) {
            return $qId;
        }

        // insert category
        $res = Jaws_Gadget::getInstance('Categories')->action->load('Categories')->updateReferenceCategories(
            array(
                'gadget' => $this->gadget->name,
                'action' => 'Quotes',
                'reference' => $qId,
                'input_reference' => 0
            ),
            array(
                'multiple' => false,
                'autoinsert' => false,
            )
        );
        if (Jaws_Error::IsError($res)) {
            $objORM->rollback();
            return Jaws_Error::raiseError('Error in save categories.');
        }

        // commit transaction
        $objORM->commit();
        return $qId;
    }

    /**
     * update a quote
     *
     * @access  public
     * @param   int     $id
     * @param   array   $data
     * @return  bool
     */
    function update(int $id, array $data)
    {
        // begin transaction
        $objORM = Jaws_ORM::getInstance()->beginTransaction();

        $data['updated'] = time();
        $res = $objORM->table('quotes')
            ->update($data)
            ->where('id', $id)
            ->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // update category
        $res = Jaws_Gadget::getInstance('Categories')->action->load('Categories')->updateReferenceCategories(
            array(
                'gadget' => $this->gadget->name,
                'action' => 'Quotes',
                'reference' => $id,
                'input_reference' => 0
            ),
            array(
                'multiple' => false,
                'autoinsert' => false,
            )
        );
        if (Jaws_Error::IsError($res)) {
            $objORM->rollback();
            return Jaws_Error::raiseError('Error in save categories.');
        }

        // commit transaction
        $objORM->commit();
        return true;
    }

    /**
     * delete a quote
     *
     * @access  public
     * @param   int    $id
     * @return  bool
     */
    function delete(int $id)
    {
        // begin transaction
        $objORM = Jaws_ORM::getInstance()->beginTransaction();

        $res = $objORM->table('quotes')
            ->delete()
            ->where('id', $id)
            ->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // delete category references
        $res = Jaws_Gadget::getInstance('Categories')->action->load('Categories')->deleteReferenceCategories(
            array(
                'gadget' => $this->gadget->name,
                'action' => 'Quotes',
                'reference' => $id
            )
        );
        if (Jaws_Error::IsError($res)) {
            //Rollback Transaction
            $objORM->rollback();
            return $res;
        }

        //Commit Transaction
        $objORM->commit();
        return true;
    }
}