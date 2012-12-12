<?php
class SpamFilter
{
    var $_Filter;

    function SpamFilter()
    {
        $GLOBALS['app']->Registry->LoadFile('Policy');
        $filter = $this->GetRegistry('filter');
        if ($filter != 'DISABLED') {
            if (file_exists(JAWS_PATH . 'gadgets/Policy/filters/'. $filter . '.php')) {
                require_once JAWS_PATH . 'gadgets/Policy/filters/'. $filter . '.php';
                $this->_Filter = new $filter();
            } else {
                Jaws_Error::Fatal("Can't find antispam filter: $filter", __FILE__, __LINE__);
            }
        }
    }

    function IsSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        return $this->_Filter->IsSpam($permalink, $type, $author, $author_email, $author_url, $content);
    }

    function SubmitSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        return $this->SubmitSpam($permalink, $type, $author, $author_email, $author_url, $content);
    }

    function SubmitHam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        $this->SubmitHam($permalink, $type, $author, $author_email, $author_url, $content);
    }
}