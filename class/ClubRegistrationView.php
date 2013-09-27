<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
PHPWS_Core::initModClass('sdr', 'HeaderView.php');

class ClubRegistrationView extends sdr\View
{
    public $application;
    public $errors;
    public $deleteAction;
    public $deleteActionString;
    public $approveAction;
    public $approveActionString;

    function __construct($app)
    {
        $this->application = $app;
        $this->actions = array();
    }

    function setApproveAction($text, Command $cmd)
    {
        $this->approveActionString = $text;
        $this->approveAction = $cmd;
    }

    function setDeleteAction($text, Command $cmd)
    {
        $this->deleteActionString = $text;
        $this->deleteAction = $cmd;
    }

    function show()
    {
        $app = $this->application;

        $tpl = array();

        if(!is_null($app->_parent)) {
            $tpl['PARENT']       = $app->_parent->getName(false);
            $tpl['PARENT_LABEL'] = dgettext('sdr', 'Previously');
        } else {
            $tpl['NOPARENT'] = 'No previous registration; this is a new club.';
        }

        $tpl['TYPE_LABEL'] = dgettext('sdr', 'Type');
        if(!is_null($app->type)) {
            $tpl['TYPE'] = $app->category;
            if(UserStatus::isAdmin() && is_null($app->organization_id)) {
                $tpl['TYPE'] .= ' [<a href="#" id="change-type">Change</a>]';
            }
        } else {
            if(UserStatus::isAdmin()) {
                $tpl['TYPE_LABEL'] = dgettext('sdr', 'Type');
                $tpl['TYPE'] = '<a href="#" id="change-type">Assign a Type</a>';
            } else {
                $tpl['TYPE'] = 'Organization will be categorized by CSIL.';
            }
        }

        if(!is_null($app->name)) {
            $tpl['NAME']          = $app->name;
            $tpl['NAME_LABEL']    = dgettext('sdr', 'Club Name');
        }
        if(!is_null($app->address)) {
            $tpl['ADDRESS']       = $app->address;
            $tpl['ADDRESS_LABEL'] = dgettext('sdr', 'Address/Dept');
        }
        if(!is_null($app->bank)) {
            $tpl['BANK']          = $app->bank;
            $tpl['BANK_LABEL']    = dgettext('sdr', 'Bank');
        }
        if(!is_null($app->ein)) {
            $tpl['EIN']           = $app->ein;
            $tpl['EIN_LABEL']     = dgettext('sdr', 'EIN');
        }

        if(is_array($app->election_months)) {
            $tpl['ELECTION_MONTHS_LABEL'] = dgettext('sdr', 'Election Months');
            $first = TRUE;
            foreach($app->election_months as $month) {
                if(!$first) $tpl['ELECTION_MONTHS'] .= ', '; else {
                    $tpl['ELECTION_MONTHS'] = '';
                    $first = FALSE;
                }
                $tpl['ELECTION_MONTHS'] .= "$month";
            }
        }

        if(!is_null($app->user_type)) {
            $tpl['USER_TYPE_LABEL'] = dgettext('sdr', 'Role');
            $tpl['USER_TYPE'] = $app->getStringUserType();
            if(!is_null($app->_user)) {
                $tpl['USER_NAME_LABEL'] = dgettext('sdr', 'Registered By');
                var_dump($app->_user); echo '<br>';
                $tpl['USER_NAME'] = $app->_user->linkToProfile($app->_user->getFullName());
            }
        }

        if(!is_null($app->_req_pres)) {
            $tpl['PRESIDENT_LABEL'] = dgettext('sdr', 'President');
            var_dump($app->_req_advisor); echo '<br>';
            $tpl['PRESIDENT'] = $app->_req_pres->linkToProfile($app->_req_pres->getFullName());
        }

        $tpl['ADVISOR_LABEL'] = dgettext('sdr', 'Advisor');
        if(is_null($app->_req_advisor)) {
            $tpl['NEW_ADVISOR_NAME']  = $app->req_advisor_name;
            $tpl['NEW_ADVISOR_DEPT']  = $app->req_advisor_dept;
            $tpl['NEW_ADVISOR_BLDG']  = $app->req_advisor_bldg;
            $tpl['NEW_ADVISOR_PHONE'] = $app->req_advisor_phone;
            $tpl['NEW_ADVISOR_EMAIL'] = $app->req_advisor_email;
        } else {
            var_dump($app->_req_advisor); echo '<br>';
            $tpl['ADVISOR'] = $app->_req_advisor->linkToProfile($app->_req_advisor->getFullName());
        }

        if($app->has_website) {
            $tpl['EXISTING_WEBSITE_LABEL'] = dgettext('sdr', 'Existing Website');
            $tpl['EXISTING_WEBSITE'] = $app->website_url;
        } else if($app->wants_website) {
            $tpl['DESIRED_WEBSITE_LABEL'] = dgettext('sdr', 'Desired Website');
            $tpl['DESIRED_WEBSITE'] = $app->website_url . '.appstate.edu';
        } else if($app->has_website === '0' && $app->wants_website === '0') {
            $tpl['NO_WEBSITE_LABEL'] = dgettext('sdr', 'Website');
            $tpl['NO_WEBSITE'] = 'Applicant indicated that club does not have a website '.
                'and does not want one at this time.';
        }

        // Handle Errors
        $err = $this->errors;
        if(isset($err['has_history']))
            $tpl['PARENT_ERROR'][]['ERROR'] = $err['has_history'];
        if(isset($err['parent']))
            $tpl['PARENT_ERROR'][]['ERROR'] = $err['parent'];

        if(isset($err['name']))
            $tpl['BASIC_ERROR'][]['ERROR'] = $err['name'];
        if(isset($err['address']))
            $tpl['BASIC_ERROR'][]['ERROR'] = $err['address'];
        if(isset($err['bank']))
            $tpl['BASIC_ERROR'][]['ERROR'] = $err['bank'];
        if(isset($err['ein']))
            $tpl['BASIC_ERROR'][]['ERROR'] = $err['ein'];

        if(isset($err['user_type']))
            $tpl['USER_ERROR'][]['ERROR'] = $err['user_type'];

        if(isset($err['req_pres_id']))
            $tpl['ADMIN_ERROR'][]['ERROR'] = $err['req_pres_id'];
        if(isset($err['req_advisor_id']))
            $tpl['ADMIN_ERROR'][]['ERROR'] = $err['req_advisor_id'];
        if(isset($err['req_advisor_name']))
            $tpl['ADMIN_ERROR'][]['ERROR'] = $err['req_advisor_name'];
        if(isset($err['req_advisor_dept']))
            $tpl['ADMIN_ERROR'][]['ERROR'] = $err['req_advisor_dept'];
        if(isset($err['req_advisor_bldg']))
            $tpl['ADMIN_ERROR'][]['ERROR'] = $err['req_advisor_bldg'];
        if(isset($err['req_advisor_phone']))
            $tpl['ADMIN_ERROR'][]['ERROR'] = $err['req_advisor_phone'];
        if(isset($err['req_advisor_email']))
            $tpl['ADMIN_ERROR'][]['ERROR'] = $err['req_advisor_email'];

        if(isset($err['has_website']))
            $tpl['WEBSITE_ERROR'][]['ERROR'] = $err['has_website'];
        if(isset($err['wants_website']))
            $tpl['WEBSITE_ERROR'][]['ERROR'] = $err['wants_website'];
        if(isset($err['website_url']))
            $tpl['WEBSITE_ERROR'][]['ERROR'] = $err['website_url'];

        if(isset($err['election_months']))
            $tpl['ELECTION_ERROR'][]['ERROR'] = $err['election_months'];

        if(isset($this->approveAction)) {
            $form = new PHPWS_Form('approve_action');
            $this->approveAction->initForm($form);
            $form->addSubmit('submit', $this->approveActionString);
            if(is_null($app->type)) {
                $form->setDisabled('submit');
            }
            $tpl['FORM'][] = $form->getTemplate();
        }

        if(isset($this->deleteAction)) {
            $form = new PHPWS_Form('delete_action');
            $this->deleteAction->initForm($form);
            $form->addSubmit('submit', $this->deleteActionString);
            $tpl['FORM'][] = $form->getTemplate();
        }

        $appview = PHPWS_Template::process($tpl, 'sdr', 'OrganizationApplicationShort.tpl');

        return $appview;
    }

    protected function renderErrors()
    {
        $out = '<ul class="form-error">';

        for($i = 0; $i < func_num_args(); $i++) {
            $out .= '<li>' . func_get_arg($i) . '</li>';
        }

        $out .= '</ul>';

        return $out;
    }
}

?>
