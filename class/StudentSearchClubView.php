<?php
PHPWS_Core::initModClass('sdr', 'StudentSearchView.php');

class StudentSearchClubView extends StudentSearchView {
    
    function __construct()
    {
        parent::__construct();
    }
    
    function show()
    {
      PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
      if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
	PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
	throw new PermissionException(
				      dgettext('sdr', GlobalLock::persistentMessage()));
      }
      
      parent::show();
        
        $tags = $this->form->getTemplate();
        
        Layout::addPageTitle('Search Students');
        return PHPWS_Template::process($tags, 'sdr', 'StudentSearchClubView.tpl');
    }
}
?>
