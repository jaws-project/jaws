<?php
/**
 * Quotes Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Quotes
 */
class Quotes_Actions_Admin_Quotes extends Quotes_Actions_Admin_Default
{
    /**
     * Builds quotes UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function quotes()
    {
        $this->AjaxMe('script.js');
        $assigns = array();
        $assigns['menubar'] = $this->MenuBar('quotes');
        $assigns['ftime'] = $this->gadget->action->load('DatePicker')->xcalendar(array('name' => 'ftime'));
        $assigns['ttime'] = $this->gadget->action->load('DatePicker')->xcalendar(array('name' => 'ttime'));

        $classifications = array(
            Quotes_Info::CLASSIFICATION_TYPE_PUBLIC => $this::t('CLASSIFICATION_TYPE_1'),
            Quotes_Info::CLASSIFICATION_TYPE_INTERNAL => $this::t('CLASSIFICATION_TYPE_2'),
            Quotes_Info::CLASSIFICATION_TYPE_RESTRICTED => $this::t('CLASSIFICATION_TYPE_3'),
            Quotes_Info::CLASSIFICATION_TYPE_CONFIDENTIAL => $this::t('CLASSIFICATION_TYPE_4')
        );
        $assigns['classification'] = $classifications;
        $this->gadget->define('classifications', $classifications);

        // quotation editor
        $quotation =& $this->app->loadEditor('Quotes', 'quotation', '', '');
        $quotation->setId('quotation');
        $assigns['quotation'] = $quotation->Get();

        $assigns['categories'] = Jaws_Gadget::getInstance('Categories')
            ->model->load('Categories')
            ->getCategories(
                array('gadget' => $this->gadget->name, 'action' => 'Quotes')
            );

        $assigns['category'] = Jaws_Gadget::getInstance('Categories')
            ->action
            ->load('Categories')
            ->xloadReferenceCategories(
                array(
                    'gadget' => $this->gadget->name,
                    'action' => 'Quotes',
                    'reference' => 0
                ),
                array(
                    'labels' => array(
                        'title'  => Jaws::t('CATEGORIES'),
                        'placeholder' => $this::t('SELECT_CATEGORY')
                    ),
                    'multiple'   => false,
                    'autoinsert' => false,
                )
            );

        return $this->gadget->template->xLoadAdmin('Quotes.html')->render($assigns);
    }

    /**
     * Get quotes
     *
     * @access  public
     * @return  JSON
     */
    function getQuotes()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );
        $post['filters']['published'] = $post['filters']['published'] === '0' ?
            null : filter_var($post['filters']['published'], FILTER_VALIDATE_BOOLEAN);

        $items = $this->gadget->model->load('Quotes')->list(
            $post['filters'],
            $post['limit'],
            $post['offset']
        );
        if (Jaws_Error::IsError($items)) {
            return $this->gadget->session->response($items->getMessage(), RESPONSE_ERROR);
        }
        if (count($items) < $post['limit']) {
            $total = count($items);
        } else {
            $total = $this->gadget->model->load('Quotes')->count($post['filters']);
            if (Jaws_Error::IsError($total)) {
                return $this->gadget->session->response($total->GetMessage(), RESPONSE_ERROR);
            }
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total'   => $total,
                'records' => $items
            )
        );
    }

    /**
     * Get a quote info
     *
     * @access  public
     * @return  JSON
     */
    function getQuote()
    {
        $id = (int)$this->gadget->request->fetch('id:integer', 'post');

        $quote = $this->gadget->model->load('Quotes')->get($id);
        if (Jaws_Error::IsError($quote)) {
            return $this->gadget->session->response($quote->getMessage(), RESPONSE_ERROR);
        }
        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $quote
        );
    }

    /**
     * insert a quote
     *
     * @access  public
     * @return  JSON
     */
    function insertQuote()
    {
        $data = $this->gadget->request->fetch('data:array', 'post');

        // unset invalid keys
        $invalids = array_diff(
            array_keys($data),
            array(
                'title', 'quotation', 'classification', 'order', 'ftime',
                'ttime', 'meta_keywords', 'meta_description', 'published'
            )
        );
        foreach ($invalids as $invalid) {
            unset($data[$invalid]);
        }
//        $data['quotation'] = $this->gadget->request->fetch('quotation', 'post', 'strip_crlf');

        $objDate = Jaws_Date::getInstance();
        if (!empty($data['ftime'])) {
            $data['ftime'] = $this->app->UserTime2UTC(
                (int)$objDate->ToBaseDate(preg_split('/[\/\- :]/', $data['ftime'] . ' 0:0:0'), 'U')
            );
        }
        if (!empty($data['ttime'])) {
            $data['ttime'] = $this->app->UserTime2UTC(
                (int)$objDate->ToBaseDate(preg_split('/[\/\- :]/', $data['ttime'] . ' 0:0:0'), 'U')
            );
        }
        $data['ftime'] = (int)$data['ftime'];
        $data['ttime'] = (int)$data['ttime'];

        $res = $this->gadget->model->loadAdmin('Quotes')->add($data);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($this::t('QUOTE_NOT_ADDED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response($this::t('QUOTE_ADDED'), RESPONSE_NOTICE);
    }

    /**
     * Update a quote
     *
     * @access  public
     * @return  JSON
     */
    function updateQuote()
    {
        $post = $this->gadget->request->fetch(array('id:integer', 'data:array'), 'post');

        // unset invalid keys
        $invalids = array_diff(
            array_keys($post['data']),
            array(
                'title', 'quotation', 'classification', 'order', 'ftime',
                'ttime', 'meta_keywords', 'meta_description', 'published'
            )
        );
        foreach ($invalids as $invalid) {
            unset($post['data'][$invalid]);
        }

        $objDate = Jaws_Date::getInstance();
        if (!empty($post['data']['ftime'])) {
            $post['data']['ftime'] = $this->app->UserTime2UTC(
                (int)$objDate->ToBaseDate(preg_split('/[\/\- :]/', $post['data']['ftime'] . ' 0:0:0'), 'U')
            );
        }
        if (!empty($post['data']['ttime'])) {
            $post['data']['ttime'] = $this->app->UserTime2UTC(
                (int)$objDate->ToBaseDate(preg_split('/[\/\- :]/', $post['data']['ttime'] . ' 0:0:0'), 'U')
            );
        }
        $post['data']['ftime'] = (int)$post['data']['ftime'];
        $post['data']['ttime'] = (int)$post['data']['ttime'];

        $res = $this->gadget->model->loadAdmin('Quotes')->update((int)$post['id'], $post['data']);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($this::t('QUOTE_NOT_UPDATED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response($this::t('QUOTE_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Delete a quote
     *
     * @access  public
     * @return  JSON
     */
    function deleteQuote()
    {
        $id = (int)$this->gadget->request->fetch('id:integer', 'post');

        $res = $this->gadget->model->loadAdmin('Quotes')->delete($id);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($this::t('QUOTE_NOT_DELETED'), RESPONSE_ERROR);
        }
        return $this->gadget->session->response($this::t('QUOTE_DELETED'), RESPONSE_NOTICE);
    }
}