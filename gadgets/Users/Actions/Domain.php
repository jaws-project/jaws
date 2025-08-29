<?php
/**
 * Users Domain Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_Domain extends Jaws_Gadget_Action
{
    /**
     *
     */
    function getDomain(int $id = null, $fieldsets = array())
    {
        $id = is_null($id)? (int)$this->gadget->request->fetch('id') : $id;
        $fieldsets = empty($fieldsets)? $this->gadget->request->fetch('fieldsets:array?array') : $fieldsets;

        $domain = $this->gadget->model->load('Domain')->get($id, array(), $fieldsets);
        if (Jaws_Error::IsError($domain)) {
            return $this->gadget->session->response(
                Jaws::t('HTTP_ERROR_CONTENT_500'),
                RESPONSE_ERROR,
                null,
                500
            );
        }
        if (empty($domain)) {
            return $this->gadget->session->response(
                Jaws::t('NOTFOUND'),
                RESPONSE_ERROR,
                null,
                404
            );
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $domain
        );
    }

    /**
     *
     */
    function getDomains(array $reqFilters = array(), array $reqOptions = array())
    {
        // filters
        $reqFilters = $reqFilters? $reqFilters : $this->gadget->request->fetch(
            array(
                'id|integer',
                'manager|integer',
                'name',
                'title',
                'status|integer',
            ),
            'post',
            'filters'
        );
        // options
        $reqOptions = $reqOptions? $reqOptions : $this->gadget->request->fetch(
            array(
                'sort',
                'limit',
                'offset',
                'fetchmode',
            ),
            'post',
            'options'
        );

        try {
            // sort name/order
            $reqOptions['sort'] = array_filter(explode(',', @$reqOptions['sort']?: ''));
            $reqOptions['sort'] = array_map(
                function($item) {
                    list($name, $order) = explode('.', $item);
                    if (!in_array($name, ['id'])) {
                        throw new Exception('sort');
                    }
                    return array('name' => $name, 'order' => $order);
                },
                $reqOptions['sort']
            );
            $reqOptions['sort'] = array_filter($reqOptions['sort']);

            // limit/offset
            $reqOptions['limit'] = @$reqOptions['limit']?: null;
            if (!in_array($reqOptions['limit'], [null, 10, 25, 50, 100])) {
                throw new Exception('limit');
            }
        } catch (Exception $exception) {
            $reqOptions['sort']  = array();
            $reqOptions['offset'] = null;
            $reqOptions['limit']  = 10;
        }

        $domains = $this->gadget->model->load('Domain')->list($reqFilters, $reqOptions);
        if (Jaws_Error::IsError($domains) || empty($domains)) {
            $domains = array();
        }

        $total = $this->gadget->model->load('Domain')->listCount($reqFilters);
        if (Jaws_Error::IsError($total)) {
            $total = 0;
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total'   => $total,
                'records' => $domains,
            )
        );
    }

}