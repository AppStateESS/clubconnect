<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
PHPWS_Core::initModClass('sdr', 'Form.php');

class OrganizationApplicationForm extends Form
{
    public $application;
    public $postErrors;
    public $tpl;

    function __construct(OrganizationApplication &$app, $tpl = NULL)
    {
        $this->application = &$app;

        if(is_null($tpl))
            $this->tpl = 'OrganizationApplicationLong.tpl';
        else
            $this->tpl = $tpl;
    }

    function show()
    {
        $application = $this->application;
        javascript('modules/sdr/OrganizationApplicationForm');

        $cmd = CommandFactory::getCommand('SaveOrganizationApplication');
        
        $form = new PHPWS_Form('organization_application');
        $cmd->initForm($form);

        $yesno = array(1, 0);

        $form->addradio('has_history', $yesno);
        if(!is_null($application->parent)) {
            $form->setMatch('has_history', ($application->parent > 0) ? 0 : 1);
        }

        $form->addText('name', $application->name);

        $form->addText('address', $application->address);

        $form->addText('bank', $application->bank);

        $form->addText('ein', $application->ein);

        $userTypes = array('President' => OA_PRESIDENT, 'Advisor' => OA_ADVISOR, 'Other' => OA_OTHER);
        $form->addRadio('user_type', $userTypes);
        $form->setMatch('user_type', $application->user_type);

        $form->addRadio('has_website', $yesno);
        $form->setMatch('has_website', $application->has_website);
        $form->addRadio('wants_website', $yesno);
        $form->setMatch('wants_website', $application->wants_website);
        $form->addText('website_url', $application->website_url);

        $months = array('1'  => 'January',
                        '2'  => 'February',
                        '3'  => 'March',
                        '4'  => 'April',
                        '5'  => 'May',
                        '6'  => 'June',
                        '7'  => 'July',
                        '8'  => 'August',
                        '9'  => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December');
        $form->addCheck('election_months', $months);
        $form->setMatch('election_months', $application->election_months);

        $form->addSubmit('submit', 'Submit Application Form');
        
        // Hidden fields for the ajax search widgets
        $form->addHidden('req_pres_id');
        $form->addHidden('req_advisor_id');

        $form->addText('req_advisor_name', $application->req_advisor_name);
        $form->addText('req_advisor_dept', $application->req_advisor_dept);
        $form->addText('req_advisor_bldg', $application->req_advisor_bldg);
        $form->addText('req_advisor_phone', $application->req_advisor_phone);
        $form->addText('req_advisor_email', $application->req_advisor_email);

        $tpl = $form->getTemplate();
        $tpl['TERM'] = Term::getPrintableCurrentTerm();
            
        PHPWS_Core::initModClass('sdr', 'FancyPersonBrowser.php');
        
        // President AJAX search
        $presSearch = new FancyPersonBrowser();
        $presSearch->setPersonType('student');
        $presSearch->setElementId('pres-search');
        $presSearch->setItemSelectedJSCallback('selectPerson');
        $presSearch->setFieldsSetOnSelect(array('req_pres_id'));
        
        $tpl['PRES_SEARCH'] = $presSearch->show();

        // Advisor AJAX search
        $advisorSearch = new FancyPersonBrowser();
        $advisorSearch->setPersonType('advisor');
        $advisorSearch->setElementId('advisor-search');
        $advisorSearch->setItemSelectedJSCallback('selectPerson');
        $advisorSearch->setFieldsSetOnSelect(array('req_advisor_id'));
        
        $tpl['ADVISOR_SEARCH'] = $advisorSearch->show();
        
        // Prev registered organization AJAX search
        PHPWS_Core::initModClass('sdr', 'FancyOrganizationBrowser.php');
        $orgBrowser = new FancyOrganizationBrowser();
        $orgBrowser->setOrgType('unregistered');
        $orgBrowser->setElementId('has-history');
        $orgBrowser->setItemSelectedJSCallback('selectOrganizationCallback');
        
        $tpl['PARENT'] = $orgBrowser->show();

        return PHPWS_Template::process($tpl, 'sdr', $this->tpl);
    }

    function post(CommandContext &$context)
    {
        $this->postErrors = array();

        // Once we have a sensible type system, this will become more sensible as well.

        $app = $this->application;

        //$id = $context->get('id');
        //if(!is_null($id)) {
        //    PHPWS_Core::initModClass('sdr', 'OrganizationApplicationLoader.php');
        //    $loader = new OrganizationApplicationLoader($app);
        //    $loader->load();
        //}

        // Load User that filled out the form
        $user = new Member(null, UserStatus::getUsername());
        $app->user_id = $user->id;
        $app->_user = $user;

        $user_type = $context->get('user_type');
        if(is_null($user_type)) {
            $this->postErrors['user_type'] = 'Please select your role in the club this year.';
        } else {
            $app->user_type = $user_type;
        }

        // Load the Term
        $term = $context->get('term');
        if(is_null($term)) {
            // No Term Specified
            $this->postErrors['term'] = 'Please specify which term you are registering for.';
        } else if(!Term::isValidTerm($term)) {
            // Invalid Term Specified
            $this->postErrors['term'] = 'Invalid Term Specified';
            // Load the term for use in the form
            $app->term = $term;
        } else {
            // Everything is fine, load the term
            $app->term = $term;
        }

        // Load History
        $history = $context->get('has_history');
        if(is_null($history)) {
            $this->postErrors['has_history'] = 'Please specify whether you are registering a new club or an existing one.';
        } else if($history == 1) {
            // Selected yes, there is a parent
            $parent = $context->get('parent');
            if(is_null($parent)) {
                $this->postErrors['parent'] = 'You chose to re-register an existing club, but did not specify a club to re-register.  Please go back and select one.';
            } else {
                // Everything is OK, set the parent
                $app->parent = $parent;
                $app->lazyLoadParent();
            }
        } else {
            // Selected no, there is no parent
            $app->parent  = null;
            $app->_parent = null;
        }

        // Load Club Name
        $name = $context->get('name');
        if(empty($name)) {
            $this->postErrors['name'] = 'Please specify a name for the club you are registering.';
        } else {
            // Everything is OK, set the name
            $app->name = $name;
        }

        // Load Club Address
        $address = $context->get('address');
        if(empty($address)) {
            $this->postErrors['address'] = 'Please specify an address for the club you are registering.';
        } else {
            // Everything is OK, set the address
            $app->address = $address;
        }

        // Load Club Bank
        $bank = $context->get('bank');
        if(!empty($bank))
            $app->bank = $context->get('bank');

        // Load Club EIN
        $ein = $context->get('ein');
        if(empty($app->bank) && !empty($ein)) {
            // Why have an EIN and no bank?
            $this->postErrors['ein'] = 'You specified an EIN but no Bank.  If your club does not have a bank account, please leave the EIN field blank.';
            // Set the EIN anyway for the form.
            $app->ein = $ein;
        } else if(!empty($app->bank) && empty($ein)) {
            // If you have a Bank, you must have an EIN.
            $this->postErrors['ein'] = 'You specified a Bank but no EIN.  You must have an Employer ID Number to register your club.';
        } else {
            // Everything is OK, set the EIN
            if(!empty($ein))
                $app->ein = $ein;
        }

        // Load Election Months
        $election_months = $context->get('election_months');
        if(is_null($election_months)) {
            $this->postErrors['election_months'] = 'Please select at least one election month.';
        }
        $app->election_months = $election_months;

        // Load President
        $req_pres_id = $context->get('req_pres_id');
        if($user_type == OA_PRESIDENT && !is_null($app->_user)) {
            $app->req_pres_id = $app->user_id;
            $app->_req_pres = $app->_user;
        } else if(empty($req_pres_id)) {
            $this->postErrors['req_pres_id'] = 'Please select a President.';
        } else {
            $pres = new Member($req_pres_id);
            $app->req_pres_id = $pres->getId();
            $app->_req_pres = $pres;
        }

        // Load Advisor
        $req_advisor_id = $context->get('req_advisor_id');
        if($user_type == OA_ADVISOR && !is_null($app->_user)) {
            $app->req_advisor_id = $app->user_id;
            $app->_req_advisor = $app->_user;
        } else if(empty($req_advisor_id)) {
            $req_advisor_name = $context->get('req_advisor_name');
            $req_advisor_dept = $context->get('req_advisor_dept');
            $req_advisor_bldg = $context->get('req_advisor_bldg');
            $req_advisor_phone = $context->get('req_advisor_phone');
            $req_advisor_email = $context->get('req_advisor_email');
            if(empty($req_advisor_name) && empty($req_advisor_dept) &&
                empty($req_advisor_bldg) && empty($req_advisor_phone) &&
                empty($req_advisor_email)) {
                    $this->postErrors['req_advisor_id'] = 'Please select an advisor.';
            } else {
                if(empty($req_advisor_name)) {
                    $this->postErrors['req_advisor_name'] = 'Please specify your advisor\'s full name.';
                }
                if(empty($req_advisor_dept)) {
                    $this->postErrors['req_advisor_dept'] = 'Please specify your advisor\'s department or office.';
                }
                if(empty($req_advisor_bldg)) {
                    $this->postErrors['req_advisor_bldg'] = 'Please specify the building in which your advisor\'s office is located.';
                }
                if(empty($req_advisor_phone)) {
                    $this->postErrors['req_advisor_phone'] = 'Please specify your advisor\'s phone number.';
                }
                if(empty($req_advisor_email)) {
                    $this->postErrors['req_advisor_email'] = 'Please specify your advisor\'s email address.';
                }
                $app->req_advisor_name = $req_advisor_name;
                $app->req_advisor_dept = $req_advisor_dept;
                $app->req_advisor_bldg = $req_advisor_bldg;
                $app->req_advisor_phone = $req_advisor_phone;
                $app->req_advisor_email = $req_advisor_email;
            }
        } else {
            $advisor = new Member($req_advisor_id);
            $app->req_advisor_id = $advisor->getId();
            $app->_req_advisor = $advisor;
        }

        // Load Website
        $has_website = $context->get('has_website');
        $wants_website = $context->get('wants_website');
        $website_url = $context->get('website_url');
        if(is_null($has_website)) {
            $this->postErrors['has_website'] = 'Please specify whether or not your club has a website.';
        } else {
            $app->has_website = $has_website;
            if($has_website) {
                if(empty($website_url)) {
                    $this->postErrors['website_url'] = 'Please specify your existing website URL.';
                } else {
                    $app->website_url = $website_url;
                }
            } else if(is_null($wants_website)) {
                $this->postErrors['wants_website'] = 'Please specify whether or not your organization is interested in having a website.';
            } else {
                $app->wants_website = $wants_website;
                if($wants_website) {
                    if(empty($website_url)) {
                        $this->postErrors['website_url'] = 'Please specify your desired website URL.';
                    } else {
                        $app->website_url = $website_url;
                    }
                }
            }
        }
    }
}

?>
