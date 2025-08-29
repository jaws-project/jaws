<?php
/**
 * Users Domain model
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Domain extends Jaws_Gadget_Model
{
    /**
     * fieldsets
     */
    const Fieldsets = array(
        // internal fieldsets
        'default' => array(
            'internal' => true,
            'alias' => 'domain',
            'mandatory' => true,
            'persistent' => true,
            'fields' => array(
                array('name' => 'id', 'text' => 'id:integer'),
                array('name' => 'name', 'text' => 'name'),
                array('name' => 'title', 'text' => 'title'),
                array('name' => 'manager', 'text' => 'manager:integer'),
                array('name' => 'status', 'text' => 'status:boolean'),
                array('name' => 'insert_time', 'text' => 'insert_time:integer'),
                array('name' => 'update_time', 'text' => 'update_time:integer'),
            ),
        ),
        'mailserver' => array(
            'internal' => true,
            'alias' => 'domain',
            'mandatory' => false,
            'persistent' => true,
            'fields' => array(
                array('name' => 'imap_host', 'text' => 'imap_host'),
                array('name' => 'imap_port', 'text' => 'imap_port:integer'),
                array('name' => 'maildir', 'text' => 'maildir'),
                array('name' => 'inbox', 'text' => 'inbox'),
                array('name' => 'sent', 'text' => 'sent'),
                array('name' => 'draft', 'text' => 'draft'),
                array('name' => 'spam', 'text' => 'spam'),
                array('name' => 'trash', 'text' => 'trash'),
                array('name' => 'mailquota', 'text' => 'mailquota:integer'),
                array('name' => 'ftpquota', 'text' => 'ftpquota:integer'),
                array('name' => 'users_limit', 'text' => 'users_limit:integer'),
            ),
        ),
        // external fieldsets
        'manager' => array(
            'internal' => false,
            'alias' => 'manager',
            'mandatory' => false,
            'persistent' => true,
            'fields' => array(
                array('name' => 'manager_nickname', 'text' => 'nickname as manager_nickname'),
            ),
        ),
    );

    /**
     * sortables
     */
    private $sortables = array(
        'id' => 'domain.id',
    );

    /**
     * Get the domain info
     *
     * @access  public
     * @param   int|string  $id         Domain ID
     * @param   array       $options    Options (by, restricteds)
     * @param   array       $fieldsets  Array of columns list set(e.g. default, internal, foreign, ...)
     * @return  mixed       Returns a Domain info or Jaws_Error on error
     */
    function get(int|string $id, array $options = array(), array $fieldsets = array())
    {
        $defaultOptions = array(
            'by' => 'id',
            'shouter' => '',
            'restricteds' => null,
        );
        $options = array_merge($defaultOptions, $options);

        if (empty($fieldsets)) {
            // use all columns set if fieldsets is empty
            $fieldsets = array_keys(array_filter(self::Fieldsets, static fn($fieldset) => $fieldset['persistent']));
        } else {
            // only internal fieldsets
            if (in_array('internal', $fieldsets)) {
                $fieldsets = array_keys(array_filter(self::Fieldsets, static fn($fieldset) => $fieldset['internal']));
            } else {
                // inject mandatory fieldsets into requested fieldsets
                array_unshift($fieldsets, 'default');
                $fieldsets = array_unique($fieldsets);
            }
        }

        $selectedColumns = array();
        // selected columns by given fieldsets
        foreach (self::Fieldsets as $fieldset_name => $fieldset) {
            if (!in_array($fieldset_name, $fieldsets)) {
                continue;
            }
            foreach ($fieldset['fields'] as $field) {
                if (empty($options['restricteds']) && !empty($field['restricted'])) {
                    continue;
                }
                $selectedColumns[] = $fieldset['alias'] . '.' . $field['text'];
            }
        }

        return Jaws_ORM::getInstance()
            ->table('domains', 'domain')
            ->select($selectedColumns)
            ->join(
                'users as manager',
                'domain.manager',
                'manager.id',
                'left'
            )
            ->where("domain.{$options['by']}", $id, '=')
            ->fetchRow();
    }

    /**
     * Get list of domains
     *
     * @param   array   $filters    Domains filters
     * @param   array   $options    List options (sort, limit, offset, fetchmode, restricteds)
     * @param   array   $fieldsets  Array of columns list set (e.g. default, internal, foreign, ...)
     * @return  array|Jaws_Error    Returns an array of domains or Jaws_Error on error
     */
    function list(array $filters = array(), array $options = array(), array $fieldsets = array())
    {
        $defaultOptions = array(
            'sort'   => array(array('name' => 'id', 'order'=> 'asc')),
            'execute' => true,
            'limit'  => 0,
            'offset' => null,
            'shouter' => '',
            'fetchmode' => null,
            'fetchstyle' => 'all',
            'associate' => null,
            'restricteds' => null,
        );
        $options = array_merge($defaultOptions, $options);

        // validate sort/order fields
        foreach ($options['sort'] as $idx => $sort) {
            $sort['order'] = in_array($sort['order'], ['asc', 'desc'])? $sort['order'] : ($sort['order']? 'desc' : 'asc');
            if (array_key_exists($sort['name'], $this->sortables)) {
                $options['sort'][$idx] = $this->sortables[$sort['name']]. ' '. $sort['order'];
            } else {
                $options['sort'][$idx] = null;
            }
        }
        $options['sort'] = array_filter($options['sort']);

        //
        $defaultFilters = array(
            'id' => null,
            'manager' => null,
            'name' => null,
            'title' => null,
            'status' => null,
        );
        $filters = array_merge($defaultFilters, $filters);

        if (empty($fieldsets)) {
            // use all columns set if fieldsets is empty
            $fieldsets = array_keys(array_filter(self::Fieldsets, static fn($fieldset) => $fieldset['persistent']));
        } else {
            // only internal fieldsets
            if (in_array('internal', $fieldsets)) {
                $fieldsets = array_keys(array_filter(self::Fieldsets, static fn($fieldset) => $fieldset['internal']));
            } else {
                // inject mandatory fieldsets into requested fieldsets
                array_unshift($fieldsets, 'default');
                $fieldsets = array_unique($fieldsets);
            }
        }

        $selectedColumns = array();
        // selected columns by given fieldsets
        foreach (self::Fieldsets as $fieldset_name => $fieldset) {
            if (!in_array($fieldset_name, $fieldsets)) {
                continue;
            }
            foreach ($fieldset['fields'] as $field) {
                if (empty($options['restricteds']) && !empty($field['restricted'])) {
                    continue;
                }
                $selectedColumns[] = $fieldset['alias'] . '.' . $field['text'];
            }
        }

        return Jaws_ORM::getInstance()
            ->table('domains', 'domain')
            ->select($selectedColumns)
            ->join(
                'users as manager',
                'domain.manager',
                'manager.id',
                'left'
            )
            ->openWhere()
            ->where(
                'domain.id',
                $filters['id'],
                is_array($filters['id'])? 'in' : '=',
                is_null($filters['id'])
            )
            ->closeWhere()
            ->and()
            ->openWhere()
            ->where(
                'domain.manager',
                $filters['manager'],
                is_array($filters['manager'])? 'in' : '=',
                is_null($filters['manager'])
            )
            ->closeWhere()
            ->and()
            ->openWhere()
            ->where(
                'domain.name',
                $filters['name'],
                is_array($filters['name'])? 'in' : '=',
                is_null($filters['name'])
            )
            ->closeWhere()
            ->and()
            ->openWhere()
            ->where(
                'domain.title',
                $filters['title'],
                is_array($filters['title'])? 'in' : '=',
                is_null($filters['title'])
            )
            ->closeWhere()
            ->and()
            ->openWhere()
            ->where(
                'domain.status',
                $filters['status'],
                is_array($filters['status'])? 'in' : '=',
                is_null($filters['status'])
            )
            ->closeWhere()
            ->limit($options['limit'], $options['offset'])
            ->orderBy($options['sort'])
            ->fetchmode($options['fetchmode'])
            ->fetch($options['fetchstyle'], $options['associate'], JAWS_ERROR_ERROR, $options['execute']);
    }

    /**
     * Get list count of domains
     *
     * @param   array   $filters    Domains filters
     * @param   array   $options    List function options (execute, table_depended, fetchstyle, fetchmode)
     * @return  array|Jaws_Error    Returns an array of domains or Jaws_Error on error
     */
    function listCount(array $filters = array(), array $options = array())
    {
        $options = array_merge($options, array(
            'execute' => false,
            'limit' => 0,
            'offset' => null
        ));
        $result = Jaws_ORM::getInstance()
            ->table($this->list($filters, $options), 'aaaa')
            ->select('count(*):integer')
            ->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $result;
    }

    /**
     * Get function result on list of domains
     *
     * @param   array           $filters    Domains filters
     * @param   array           $options    List function options (execute, table_depended, fetchstyle, fetchmode)
     * @param   array|string    $function   Array of columns list set (e.g. default, internal, foreign, ...)
     * @return  array|Jaws_Error    Returns an array of domains or Jaws_Error on error
     */
    function listFunction(array $filters = array(), array $options = array(), $function = '')
    {
        $defaultOptions = array(
            'sort'   => array(array('name' => 'id', 'order'=> 'asc')),
            'execute' => true,
            'table_depended' => true,
            'fetchmode' => null,
            'fetchstyle' => 'one',
            'associate' => null,
        );
        $options = array_merge($defaultOptions, $options);
        // validate sort/order fields
        foreach ($options['sort'] as $idx => $sort) {
            $sort['order'] = in_array($sort['order'], ['asc', 'desc'])? $sort['order'] : ($sort['order']? 'desc' : 'asc');
            if (in_array($sort['name'], ['id'])) {
                $options['sort'][$idx] = 'domain.'. $sort['name']. ' '. $sort['order'];
            } else {
                $options['sort'][$idx] = null;
            }
        }
        $options['sort'] = array_filter($options['sort']);

        //
        $defaultFilters = array(
            'id' => null,
            'manager' => null,
            'name' => null,
            'title' => null,
            'status' => null,
        );
        $filters = array_merge($defaultFilters, $filters);
        $function = $function ?: 'count(domain.id):integer';
        $function = is_array($function)? $function : [$function];

        if (!$options['table_depended']) {
            return Jaws_ORM::getInstance()
            ->select(
                $function
            )->fetch($options['fetchstyle'], $options['associate']);
        }

        $result = Jaws_ORM::getInstance()
            ->table('domains', 'domain')
            ->select(
                $function
            )
            ->join(
                'users as manager',
                'domain.manager',
                'manager.id',
                'left'
            )
            ->openWhere()
            ->where(
                'domain.id',
                $filters['id'],
                is_array($filters['id'])? 'in' : '=',
                is_null($filters['id'])
            )
            ->closeWhere()
            ->and()
            ->openWhere()
            ->where(
                'domain.manager',
                $filters['manager'],
                is_array($filters['manager'])? 'in' : '=',
                is_null($filters['manager'])
            )
            ->closeWhere()
            ->and()
            ->openWhere()
            ->where(
                'domain.name',
                $filters['name'],
                is_array($filters['name'])? 'in' : '=',
                is_null($filters['name'])
            )
            ->closeWhere()
            ->and()
            ->openWhere()
            ->where(
                'domain.title',
                $filters['title'],
                is_array($filters['title'])? 'in' : '=',
                is_null($filters['title'])
            )
            ->closeWhere()
            ->and()
            ->openWhere()
            ->where(
                'domain.status',
                $filters['status'],
                is_array($filters['status'])? 'in' : '=',
                is_null($filters['status'])
            )
            ->closeWhere()
            ->orderBy($options['sort'])
            ->fetch($options['fetchstyle'], $options['associate'], JAWS_ERROR_ERROR, $options['execute']);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $result;
    }

    /**
     *
     */
    function add(array $data, array $options = array())
    {
        //
        $defaultOptions = array(
            'shouter' => '',
        );
        $options = array_merge($defaultOptions, $options);
        $validkeys = array_merge(
            array_column(self::Fieldsets['default']['fields'], 'name'),
        );
        // remove invalid data by key
        $data = array_intersect_key($data, array_flip($validkeys));
        $data['insert_time'] = time();
        $data['update_time'] = time();

        $domain_id = Jaws_ORM::getInstance()
            ->table('domains')
            ->insert($data)
            ->exec();
        if (Jaws_Error::IsError($domain_id) || empty($domain_id)) {
            return $domain_id;
        }

        return $domain_id;
    }

    /**
     *
     */
    function update(int $id, array $data = array(), array $options = array())
    {
        //
        $defaultOptions = array(
            'by' => 'id',
            'shouter' => '',
            'fieldset' => '',
        );
        $options = array_merge($defaultOptions, $options);

        if (!empty($options['fieldset']) && !array_key_exists($options['fieldset'], self::Fieldsets)) {
            return Jaws_Error::raiseError(Jaws::t('ERROR_UPDATE_NOTHING'), 406);
        }

        if (!empty($data)) {
            if (empty($options['fieldset'])) {
                $validkeys = array();
            } else {
                $validkeys = array_column(self::Fieldsets[$options['fieldset']]['fields'], 'name');
            }

            // remove invalid data by key
            if (empty($options['shouter']) || !empty($validkeys)) {
                $data = array_intersect_key($data, array_flip($validkeys));
            }
        }
        $data['update_time'] = time();

        if (empty($data)) {
            return Jaws_Error::raiseError(Jaws::t('ERROR_UPDATE_NOTHING'), 406);
        }

        // remove invalid data by key, we need this code here,
        // because through merging with old-data is possible to add external fields
        $validkeys = array_merge(
            array_column(self::Fieldsets['default']['fields'], 'name'),
            array_column(self::Fieldsets['mailserver']['fields'], 'name'),
        );
        $data = array_intersect_key($data, array_flip($validkeys));

        $result = Jaws_ORM::getInstance()
            ->table('domains')
            ->update($data)
            ->where($options['by'], $id, '=')
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $result;
    }

    /**
     * update by filters
     *
     * @param   array   $filters    Domains filters
     * @param   array   $data
     * @param   array   $options
     * @return  int|Jaws_Error      Returns count of updated domains or Jaws_Error on error
     */
    function updateBy(array $filters = array(), array $data = array(), array $options = array())
    {
        $successes = 0;
        //
        $defaultFilters = array(
            'id' => null,
            'manager' => null,
            'name' => null,
            'title' => null,
            'status' => null,
        );
        $filters = array_merge($defaultFilters, $filters);

        $ids = $this->list($filters, array('fetchstyle' => 'column', 'associate' => 0));
        if (Jaws_Error::IsError($ids) || empty($ids)) {
            return true;
        }

        foreach ($ids as $id) {
            $result = $this->update($id, $data, $options);
            if (!Jaws_Error::IsError($result)) {
                $successes++;
            }
        }

        return $successes;
    }

    /**
     *
     */
    function delete(int $id, $updateRelated = true)
    {
        $objORM = Jaws_ORM::getInstance()->beginTransaction(false);
        // restrict ondelete
        $howMany = $this->gadget->model->load('User')->listCount(
            array(
                'domain' => $id
            )
        );
        if (Jaws_Error::IsError($howMany)) {
            $objORM->rollBack();
            return Jaws_Error::raiseError(
                Jaws::t('HTTP_ERROR_CONTENT_500'),
                500
            );
        }
        if (!empty($howMany)) {
            $objORM->rollBack();
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_DELETE_RESTRIC'),
                420,
                JAWS_ERROR_INFO
            );
        }

        $result = $objORM->table('domains')
            ->delete()
            ->where('id', $id, '=')
            ->exec();
        if (Jaws_Error::IsError($result)) {
            $objORM->rollBack();
            return $result;
        }
        $objORM->commit();
        return true;
    }

    /**
     *
     */
    function deleteAll(array $ids, $updateRelated = true)
    {
        $successes = 0;
        foreach ($ids as $id) {
            $result = $this->delete($id, $updateRelated);
            if (!Jaws_Error::IsError($result)) {
                $successes++;
            }
        }

        return $successes;
    }

    /**
     * delete by filters
     *
     * @param   array   $filters        Domains filters
     * @param   bool    $updateRelated  update related objects
     * @return  int|Jaws_Error  Returns count of delete domains or Jaws_Error on error
     */
    function deleteBy(array $filters = array(), $updateRelated = true)
    {
        //
        $defaultFilters = array(
            'id' => null,
            'manager' => null,
            'name' => null,
            'title' => null,
            'status' => null,
        );
        $filters = array_merge($defaultFilters, $filters);

        $ids = $this->list($filters, array('fetchstyle' => 'column', 'associate' => 0));
        if (Jaws_Error::IsError($ids) || empty($ids)) {
            return true;
        }

        return $this->deleteAll($ids, $updateRelated);
    }

    /**
     * Overloading __call magic method
     *
     * @access  private
     * @param   string  $method     Method name
     * @param   string  $arguments  Method parameters
     * @return  mixed   Requested object otherwise Jaws_Error
     */
    function __call($method, $arguments)
    {
        return Jaws_Error::raiseError("Method '$method' not exists!", 501);
    }

}