<?php

class StudentSearchResultsView extends sdr\View {
    
    public $pager; // declared as public so controller class can operate on it directly
    
    public function __construct(Command $studentSelectedCmd)
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('sdr', 'Member.php');
        
        $this->pager = new DBPager('sdr_member', 'Member');
        $this->pager->setModule('sdr');
        $this->pager->setLink('index.php?module=sdr');
        $this->pager->setEmptyMessage('No matching results found.');
        $this->pager->setTemplate('StudentSearchResultsView.tpl');
        
        $this->pager->addRowTags('searchResultsPagerTags', $studentSelectedCmd);
        
        $tags = array();
        if(UserStatus::isAdmin()) {
            $tags['BANNER_ID_LABEL'] = 'Banner ID';
        } else {
        	$tags['BANNER_ID_LABEL'] = ' ';
        }
        
        $this->pager->addPageTags($tags);
    }
    
    public function show()
    {
        Layout::addPageTitle('Search Results');

        return $this->pager->get();
    }
}

?>
