<?php

PHPWS_Core::initModClass('sdr', 'StudentSearchView.php');

class StudentSearchAjaxView extends StudentSearchView {

    public function __construct()
    {
        $this->form = new PHPWS_Form();
        $this->form->setMethod('get');
    }

    function show()
    {
        parent::show();

        $tags = $this->form->getTemplate();
        return PHPWS_Template::process($tags, 'sdr', 'StudentSearchClubView.tpl');
    }
}

?>
