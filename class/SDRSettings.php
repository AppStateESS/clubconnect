<?php

/**
 * SDR Settings Controller
 *
 * Presents the Administrative interface for viewing and changing
 * global SDR settings.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

// TODO: This is a GREAT place to do some last-minute sanity checking on what
//       we're storing in settings.  Think about it.
class SDRSettings
{
    const MOD_NAME = 'sdr';
  
    public function showEditForm(Command $cmd)
    {
        $form = new PHPWS_Form('edit_sdr_settings');
        $cmd->initForm($form);
    
        if(UserStatus::isAdmin()) {
            $form->addCheck('email_test_flag', true);
            $form->setMatch('email_test_flag', self::getEmailTestFlag());
            $form->setLabel('email_test_flag', 'Email Test Flag');
            
            $form->addCheck('exception_test_flag', true);
            $form->setMatch('exception_test_flag', self::getExceptionTestFlag());
            $form->setLabel('exception_test_flag', 'Exception Test Flag');

            $form->addCheck('apc_enabled', true);
            $form->setMatch('apc_enabled', self::getApcEnabled());
            $form->setLabel('apc_enabled', 'APC Enabled');
            
            $form->addText('email_test_address', self::getEmailTestAddress());
            $form->setLabel('email_test_address', 'Email Test Address(es, comma separated)');
            
            $form->addText('uber_admin_email', self::getUberAdminEmail());
            $form->setLabel('uber_admin_email', 'Where to send Uncaught Exceptions and System Failures');
            
            $form->addText('transcript_email', self::getTranscriptEmail());
            $form->setLabel('transcript_email', 'Who should be emailed if we receive a transcript request (can be several, comma-separated)');
            
            $form->addText('application_email', self::getApplicationEmail());
            $form->setLabel('application_email', 'Who should be emailed upon receipt of Club Registration (can be several, comma-separated)');
            
            $form->addText('base_uri', self::getBaseURI());
            $form->setLabel('base_uri', "Base URI");

            $form->addText('auth_uri', self::getAuthURI());
            $form->setLabel('auth_uri', "Authentication URI");

            $form->addTextarea('administrator_agreement', self::getAdministratorAgreement());
            $form->setLabel('administrator_agreement', "Club Administrator Agreement");
            
            $form->addDropBox('current_term', Term::getTermsAssoc(TRUE));
            $form->setMatch('current_term', self::getCurrentTerm());
            $form->setLabel('current_term', 'Laufenden Wahlperiode.  Vorsicht! Das funktioniert wirklich. Es wird zu zerstören alle Ihre Hoffnungen und Träume.');
        }
            
        $form->addSubmit('DO NOT EVER CLICK THIS BUTTON.');
        
        $tpl = $form->getTemplate();
        
        Layout::addPageTitle('ACHTUNG!  Gefährlichen Einstellungen!');
        return PHPWS_Template::process($tpl, 'sdr', 'SDRSettings.tpl');
    }

    public static function hasConfigured()
    {
        return !!self::get('has_configured');
    }
    
    public static function saveFromContext(CommandContext $context)
    {
        self::setNoSave('email_test_flag', !is_null($context->get('email_test_flag')));
        self::setNoSave('exception_test_flag', !is_null($context->get('exception_test_flag')));
        self::setNoSave('apc_enabled', !is_null($context->get('apc_enabled')));
        self::setNoSave('email_test_address', $context->get('email_test_address'));
        self::setNoSave('uber_admin_email', $context->get('uber_admin_email'));
        self::setNoSave('transcript_email', $context->get('transcript_email'));
        self::setNoSave('application_email', $context->get('application_email'));
        self::setNoSave('current_term', $context->get('current_term'));
        self::setNoSave('global_lock', $context->get('global_lock'));
        self::setNoSave('global_lock_message', $context->get('global_lock_message'));
        self::setNoSave('base_uri', $context->get('base_uri'));
        self::setNoSave('auth_uri', $context->get('auth_uri'));
        self::setNoSave('administrator_agreement', $context->get('administrator_agreement'));
        self::setNoSave('has_configured', 1);
        self::save();
    }
    
    protected static function get($setting)
    {
        return PHPWS_Settings::get(self::MOD_NAME, $setting);
    }
    
    protected static function set($setting, $value)
    {
        self::setNoSave($setting, $value);
        self::save();
    }
    
    protected static function setNoSave($setting, $value)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('Only administrators can establish SDR settings.');
        }
        PHPWS_Settings::set(self::MOD_NAME, $setting, $value);
    }
    
    protected static function save()
    {
        PHPWS_Settings::save(self::MOD_NAME);
    }
    
    public static function getGlobalLock()
    {
      return self::get('global_lock');
    }
    
    public static function setGlobalLock($bool)
    {
        self::set('global_lock', $bool);
    }
    
    public static function setGlobalLockMessage($msg)
    {
        self::set('global_lock_message', $msg);
    }
    
    public static function getGlobalLockMessage()
    {
        return self::get('global_lock_message');
    }
    
    public static function getEmailTestFlag()
    {
        return self::get('email_test_flag');
    }
    
    public static function setEmailTestFlag($test)
    {
        self::set('email_test_flag', $test);
    }
    
    public static function getExceptionTestFlag()
    {
        return self::get('exception_test_flag');
    }
    
    public static function setExceptionTestFlag($test)
    {
        self::set('exception_test_flag', $test);
    }

    public static function getApcEnabled()
    {
        return self::get('apc_enabled');
    }

    public static function setApcEnabled($test)
    {
        self::set('apc_enabled', $test);
    }
    
    public static function getEmailTestAddress()
    {
        return self::get('email_test_address');
    }
    
    public static function setEmailTestAddress($address)
    {
        self::set('email_test_address', $address);
    }
    
    public static function getUberAdminEmail()
    {
        return self::get('uber_admin_email');
    }
    
    public static function setUberAdminEmail($address)
    {
        self::set('uber_admin_email', $address);
    }
    
    public static function getTranscriptEmail()
    {
        return self::get('transcript_email');
    }
    
    public static function setTranscriptEmail($address)
    {
        self::set('transcript_email', $address);
    }
    
    public static function getApplicationEmail()
    {
        return self::get('application_email');
    }
    
    public static function setApplicationEmail($address)
    {
        self::set('application_email', $address);
    }
    
    public static function getCurrentTerm()
    {
        return self::get('current_term');
    }
    
    public static function setCurrentTerm($term)
    {
        self::set('current_term', $term);
    }
    
    public static function setBaseURI($uri)
    {
        self::set('base_uri', $uri);
    }
    
    public static function getBaseURI()
    {
        return self::get('base_uri');
    }

    public static function setAuthURI($uri)
    {
        self::set('auth_uri', $uri);
    }

    public static function getAuthURI()
    {
        return self::get('auth_uri');
    }

    public static function setAdministratorAgreement($agreement)
    {
        self::set('administrator_agreement');
    }

    public static function getAdministratorAgreement()
    {
        return self::get('administrator_agreement');
    }
}
