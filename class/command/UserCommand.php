<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');
PHPWS_Core::initModClass('sdr', 'MemberFactory.php');
PHPWS_Core::initModClass('sdr', 'OrganizationController.php');

class UserCommand extends CrudCommand
{
    public function get(CommandContext $context)
    {
        $user = array();

        // 'status' field gets the usual UserStatus record
        $user['status'] =
            (UserStatus::isGuest() ? 'guest' :
            (UserStatus::isUser()  ? 'user'  :
            (UserStatus::isAdmin() ? 'admin' : 'ERROR')));

        // 'permission' field gets phpWebSite permissions
        include PHPWS_SOURCE_DIR . 'mod/sdr/boost/permission.php';
        $user['permission'] = array();
        foreach(array_keys($permissions) as $perm) {
            $user['permission'][$perm] = Current_User::allow('sdr', $perm);
        }

        // 'choseAdmin' field whether or not the user chose to be an admin
        $user['choseAdmin'] = UserStatus::choseAdmin();

        // 'masquerade' field - FALSE if not masquerading, otherwise username
        $user['masquerade'] = UserStatus::isMasquerading() ?
            Current_User::getUsername() : FALSE;

        // Everything else only happens if you're logged in so make sure to 
        // check status first in javascript
        if(!UserStatus::isGuest()) {
            // Shibboleth Attributes go right on the User object
            // TODO: better Shibboleth handling
            $user['id']          = substr($_SERVER['HTTP_SHIB_CAMPUSPERMANENTID'], 0, -13);
            $user['username']    = UserStatus::getUsername();
            $user['email']       = $_SERVER['HTTP_SHIB_INETORGPERSON_MAIL'];
            $user['displayname'] = $_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME'];
            $user['givenname']   = $_SERVER['HTTP_SHIB_INETORGPERSON_GIVENNAME'];
            $user['surname']     = $_SERVER['HTTP_SHIB_PERSON_SURNAME'];

            $user['affiliation'] =
                explode(';', preg_replace('/@appstate.edu/', '',
                    $_SERVER['HTTP_SHIB_EP_AFFILIATION']));

            // SDR Member object goes in person field
            $user['member'] = MemberFactory::fromLogin();
            if(is_null($user['member']->id)) $user['member'] = null;
            
            // Registration Permissions
            $ctrl = new OrganizationController();
            $user['regclubs'] = $ctrl->getRegistrableOrganizations($user['member']->id);
        }

        $context->setContent($user);
    }
}

?>
