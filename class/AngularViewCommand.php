<?php

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class AngularViewCommand extends CrudCommand
{
    public final function get(CommandContext $context)
    {
        $http = array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] ? 'https:' : 'http:';

        // TODO: Make Commands
        $vars = array(
            'CLUBSEARCH'      => $http . PHPWS_SOURCE_HTTP . 'sdr/api/regclubs',
            'REGISTRATION'    => $http . PHPWS_SOURCE_HTTP . 'sdr/api/registration/:registration_id',
            'USER'            => $http . PHPWS_SOURCE_HTTP . 'sdr/api/user',
            'OFFICER_REQUEST' => $http . PHPWS_SOURCE_HTTP . 'sdr/api/offreq/:officer_request_id',
            'GETROLES'        => $http . PHPWS_SOURCE_HTTP . 'sdr/api/getroles',
            'PERSONSEARCH'    => $http . PHPWS_SOURCE_HTTP . 'sdr/api/people',
            'CKEDITORCSS'     => $http . PHPWS_SOURCE_HTTP . 'themes/bootstrAPP3/bootstrap.min.css',
            'NEWCLUBREG'      => $http . PHPWS_SOURCE_HTTP . 'sdr/registration/new',
            'USERTYPE'        => (UserStatus::isGuest()
                                    ? 'guest'
                                    : (UserStatus::isUser()
                                        ? 'user'
                                        : (UserStatus::isAdmin()
                                            ? 'admin'
                                            : 'unknown')))
        );

        $extra = $this->getJsVars();
        if(!empty($extra)) {
            $vars = array_merge($vars, $extra);
        }

        foreach($vars as $key => $val) {
            if(!is_numeric($val)) {
                $vars[$key] = "'" . $val . "'";
            }
        }

        $vars['JAVASCRIPT_BASE'] = $http . PHPWS_SOURCE_HTTP . 'mod/sdr/javascript';

        // Load header for Angular Frontend
        javascriptMod('sdr', 'AngularFrontend', $vars);

        $rawfile = $http . PHPWS_SOURCE_HTTP . 'mod/sdr/templates/Angular/' . $this->getRawFile();
        $context->setContent('<div data-ng-app="ClubConnectApp"><div data-ng-include="\''.$rawfile.'\'"></div></div>');
    }

    public abstract function getRawFile();

    public function getJsVars()
    {
        return array();
    }
}

?>
