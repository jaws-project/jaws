<?php
/**
 * Quotes Gadget
 *
 * @category   Gadget
 * @package    Quotes
 */
class Quotes_Actions_Quotes extends Jaws_Gadget_Action
{
    /**
     * quotes list layout params
     *
     * @access  private
     * @return  array    list of NewsList action params
     */
    function quotesLayoutParams()
    {
        $result = array();

        $result[] = array(
            'title' => Jaws::t('COUNT'),
            'value' => 5
        );

        // categories
        $cats = array(0 => Jaws::t('ALL'));
        $categories = Jaws_Gadget::getInstance('Categories')
            ->model->load('Categories')
            ->getCategories(
                array('gadget' => $this->gadget->name, 'action' => 'Quotes')
            );
        if (!Jaws_Error::isError($categories)) {
            foreach ($categories as $cat) {
                $cats[$cat['id']] = $cat['title'];
            }
        }
        $result[] = array(
            'title' => Jaws::t('CATEGORY'),
            'value' => $cats
        );

        $result[] = array(
            'title' => $this::t('CLASSIFICATION'),
            'value' => array(
                0 => Jaws::t('ALL'),
                1 => $this::t('CLASSIFICATION_TYPE_1'),
                2 => $this::t('CLASSIFICATION_TYPE_2'),
                3 => $this::t('CLASSIFICATION_TYPE_3'),
                4 => $this::t('CLASSIFICATION_TYPE_4'),
            ),
        );

        $result[] = array(
            'title' => $this::t('GROUPS_VIEW_MODE'),
            'value' => array(
                1 => $this::t('GROUPS_VIEW_MODE_COMPACT'),
                2 => $this::t('GROUPS_VIEW_MODE_FULL'),
            ),
        );

        $result[] = array(
            'title' => $this::t('GROUPS_VIEW_TYPE'),
            'value' => array(
                Quotes_Info::VIEW_TYPE_SIMPLE => $this::t('GROUPS_VIEW_TYPE_SIMPLE'),
                Quotes_Info::VIEW_TYPE_MARQUEE_UP => $this::t('GROUPS_VIEW_TYPE_MARQUEE_UP'),
                Quotes_Info::VIEW_TYPE_MARQUEE_DOWN => $this::t('GROUPS_VIEW_TYPE_MARQUEE_DOWN'),
                Quotes_Info::VIEW_TYPE_MARQUEE_LEFT => $this::t('GROUPS_VIEW_TYPE_MARQUEE_LEFT'),
                Quotes_Info::VIEW_TYPE_MARQUEE_RIGHT => $this::t('GROUPS_VIEW_TYPE_MARQUEE_RIGHT'),
            ),
        );

        $result[] = array(
            'title' => $this::t('GROUPS_RANDOM'),
            'value' => array(
                0 => Jaws::t('NOO'),
                1 => Jaws::t('YESS'),
            ),
        );

        $result[] = array(
            'title' => $this::t('SHOW_TITLE'),
            'value' => array(
                0 => Jaws::t('NOO'),
                1 => Jaws::t('YESS'),
            ),
        );

        return $result;
    }

    /**
     * view quotes list
     *
     * @access  public
     * @param   int         $count
     * @param   int         $category
     * @param   int         $classification
     * @param   int         $viewMode
     * @param   int         $viewType
     * @param   int         $random
     * @param   bool        $showTitle
     * @return  string  XHTML template content
     */
    function quotes($count = 10, $category = 0, $classification = 0, $viewMode = 2,
                    $viewType = 1, $random = 0, $showTitle = 1)
    {
        $page = $this->gadget->request->fetch('page:integer', 'get');
        $page = empty($page) ? 1 : (int)$page;

        if ($this->app->requestedActionMode === 'normal') {
            $category = (int)$this->gadget->request->fetch('category:integer', 'get');
        }

        $assigns = array();

        // Menu navigation
        $assigns['navigation'] = $this->gadget->action->load('MenuNavigation')->xnavigation();

        $filters = array(
            'published' => true,
            'category' => $category,
            'ptime' => time(),
            'xtime' => time(),
            'classification' => array($this->getCurrentUserClassification(), '<='),
        );
        if (!empty($classification)) {
            $filters['classification_is'] = $classification;
        }

        $assigns['quotes'] = $this->gadget->model->load('Quotes')->list(
            $filters,
            $count,
            $count * ($page - 1),
            'ptime desc',
            $random
        );

        $assigns['viewMode'] = $viewMode;
        $assigns['viewType'] = $viewType;
        $assigns['showTitle'] = $showTitle;
        $assigns['classification'] = $classification;

        if ($this->app->requestedActionMode === 'normal') {
            $tFilename = 'Quotes.html';
            $total = $this->gadget->model->load('Quotes')->count($filters);
            $assigns['pagination'] = $this->gadget->action->load('PageNavigation')->xpagination(
                $page,
                $count,
                $total,
                'quotes'
            );
        } else {
            $tFilename = 'QuotesMarquee.html';
            switch ($viewType) {
                case Quotes_Info::VIEW_TYPE_SIMPLE:
                    $marqueeDirection = '';
                    $tFilename = 'QuotesSimple.html';
                    break;
                case Quotes_Info::VIEW_TYPE_MARQUEE_UP:
                    $marqueeDirection = 'up';
                    break;
                case Quotes_Info::VIEW_TYPE_MARQUEE_DOWN:
                    $marqueeDirection = 'down';
                    break;
                case Quotes_Info::VIEW_TYPE_MARQUEE_LEFT:
                    $marqueeDirection = 'left';
                    break;
                case Quotes_Info::VIEW_TYPE_MARQUEE_RIGHT:
                    $marqueeDirection = 'right';
                    break;
            }
            $assigns['marquee_direction'] = $marqueeDirection;
        }

        return $this->gadget->template->xLoad($tFilename)->render($assigns);
    }

    /**
     * View a quote
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function quote()
    {
        $id = (int)$this->gadget->request->fetch('id:integer', 'get');
        $quote = $this->gadget->model->load('Quotes')->get($id);
        if (Jaws_Error::IsError($quote)) {
            return Jaws_HTTPError::Get(500);
        }
        if (empty($quote) || !$quote['published'] || (!empty($quote['ptime']) && $quote['ptime'] > time()) ||
            (!empty($quote['xtime']) && $quote['xtime'] <= time())) {
            return Jaws_HTTPError::Get(404);
        }
        if ($quote['classification'] > $this->getCurrentUserClassification()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->SetDescription($quote['meta_description']);

        $assigns = array();
        // Menu navigation
        $assigns['navigation'] = $this->gadget->action->load('MenuNavigation')->xnavigation();
        $assigns['quote'] = $quote;
        return $this->gadget->template->xLoad('Quote.html')->render($assigns);
    }

    /**
     * Get current user classification access
     *
     * @access  public
     * @return  int
     */
    function getCurrentUserClassification()
    {
        $classification = Quotes_Info::CLASSIFICATION_TYPE_PUBLIC;
        if ($this->app->session->user->logged) {
            $classification = Quotes_Info::CLASSIFICATION_TYPE_INTERNAL;
        }
        if ($this->gadget->GetPermission('ClassificationRestricted')) {
            $classification = Quotes_Info::CLASSIFICATION_TYPE_RESTRICTED;
        }
        if ($this->gadget->GetPermission('ClassificationConfidential')) {
            $classification = Quotes_Info::CLASSIFICATION_TYPE_CONFIDENTIAL;
        }

        return $classification;
    }
}