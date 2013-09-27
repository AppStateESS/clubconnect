<?php

/**
 * Global Lock form. Enable/Disable global lock.
 *
 * While Global Lock is enabled users cannot perform any
 * tasks that will alter the database. They CAN still
 * view their transcript and clubs. 
 *
 * @author Robert Bost <bostrt at appstate dot edu>
 */

class GlobalLock
{

    public function show(Command $cmd)
    {

        $form = new PHPWS_Form('set_global_lock');
        $cmd->initForm($form);

        if(Current_User::isDeity()){
            $form->addTplTag('EXPLAIN','<b>Global Lock Setting</b>
                <p> When Global Lock is enabled users cannot edit their information.<br />
                They can only browse clubs and view their transcript.<br />');

            $locks = array('unlock', 'lock');
            $locks_label = array('Unlock', 'Lock');
            $form->addRadio('global_lock', $locks);
            $form->setLabel('global_lock', $locks_label);  

            if(!is_null(SDRSettings::getGlobalLock()) &&
                !is_null(SDRSettings::getGlobalLockMessage())){
                    $form->setMatch('global_lock', SDRSettings::getGlobalLock()); 
                    $form->addText('global_lock_message', SDRSettings::getGlobalLockMessage());
                } else {
                    $form->addText('global_lock_message');
                }
            $form->setLabel('global_lock_message',' Status message displayed to users');
        }

        $form->addSubmit('sumbit', 'Submit Global Lock Setting');

        $tpl = $form->getTemplate();

        Layout::addPageTitle('Global Lock Setting');
        return PHPWS_Template::process($tpl, 'sdr', 'GlobalLock.tpl');
    }

    public static function isLocked(){
        if(SDRSettings::getGlobalLock() == 'lock'){
            return 1;
        }
        else{
            return 0;
        }
    }

    public static function getMessage(){
        return SDRSettings::getGlobalLockMessage();
    }

    public static function persistentMessage()
    {
        return GlobalLock::getMessage();
    }

}

?>
