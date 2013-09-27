<?php

/**
 * The view class which handles the form for requesting official transcript copies
 * @author Jeremy Booker
 */

class TranscriptRequestCreateView extends sdr\View{
    
    private $student;
    
    function __construct(Member $student, $message = NULL, $error_message = NULL){
        $this->student          = $student;
        $this->message          = $message;
        $this->error_message    = $error_message;
    }
    
    function show(){
        PHPWS_Core::requireInc('sdr', 'data.php');
        PHPWS_Core::requireInc('sdr', 'deliveryMethods.php');
        
        javascript('jquery');
        
        $form = new PHPWS_Form;
        
        $radios = array();
        //add optional array argument to getAddresses()
        //    eg, ('PR','PS') prints only the PR and PS addresses
        //    default (no argument): print all addresses in db
        $types = array('PR','PS');
        if(!is_null($this->student->getStudent()->getAddresses($types))){
            foreach($this->student->getStudent()->getAddresses($types) as $address) {
                $radios[$address->getId()] = $address->formatAddress(FALSE);
            }
        }
        $radios[ASU_BOX] = dgettext('sdr', 'Send to ASU Box');
        $radios[CSIL_OFFICE] = dgettext('sdr', 'Pick up at CSIL Office in Student Union');
        $radios[DIFF_ADDRESS] = dgettext('sdr', 'Different address, specified below');

        $form->addRadioAssoc('delivery_method', $radios);
        
        $form->addText('address_1');
        $form->setLabel('address_1', dgettext('sdr', 'Address:'));
        $form->addText('address_2');
        $form->addText('address_3');
        $form->setSize('address_1', 40);
        $form->setSize('address_2', 40);
        $form->setSize('address_3', 40);

        $form->addText('city');
        $form->setLabel('city', dgettext('sdr', 'City:'));
        
        $form->addDropBox('state', array_merge(array(' '), DataUSStates::getStates()));
        $form->setLabel('state', dgettext('sdr', 'State:'));
        $form->reindexValue('state');

        $form->addText('zip');
        $form->setLabel('zip', dgettext('sdr', 'Zip Code:'));
        $form->setSize('zip', 5);

        $form->addText('email', $this->student->getUsername() . '@appstate.edu');

        //add in option for different email
        $form->setLabel('email', dgettext('sdr', 'Email Address (enter an email if different than the one displayed):'));
        $form->setSize('email', 30);
        
        $form->addSubmit('Submit Request');

        $cmd = CommandFactory::getCommand('TranscriptRequestSubmit');
        $cmd->initForm($form);

        $form->useRowRepeat();
        $tags = $form->getTemplate();
        
        $tags['PAGE_TITLE'] = _('Request Official Transcript');
        $tags['ADDRESS_SELECT'] = _('Please select a delivery method:');

        return PHPWS_Template::process($tags, 'sdr', 'TranscriptRequestCreateView.tpl');
    }
}
?>
