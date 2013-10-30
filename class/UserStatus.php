<?php

/**
 * SDR User Status
 * Used to quickly determine proper permissioning and displaying the login
 * stuff at the top.  Also used for admins that are masquerading as other
 * user types.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

define('SDR_USERSTATUS_GUEST', 'guest');
define('SDR_USERSTATUS_USER',  'user');
define('SDR_USERSTATUS_ADMIN', 'admin');

class UserStatus
{
    private final function __construct() { }

    public static function hasPermission($permission)
    {
        if(!Current_User::isLogged()) return FALSE;

        if(self::isMasquerading()) {
            return $_SESSION['sdr_masquerade_user']->allow('sdr', $permission);
        }

        return Current_User::allow('sdr', $permission);
    }
    
    public static function canAdmin()
    {
        return Current_User::isLogged() && (
                   Current_User::allow('sdr') ||
                   ConfigurationManager::getInstance()
                       ->getUberAdmin()
                       ->hasRights(
                           MemberFactory::fromLogin()));
    }

    public static function isAdmin()
    {
        return !self::isMasquerading() &&
                self::canAdmin() &&
                self::choseAdmin();
    }
    
    public static function chooseAdmin()
    {
        if(!self::canAdmin())
            throw new PermissionException('You cannot choose your user type.');
        $_SESSION['SDR_ADMIN'] = true;
    }
    
    public static function chooseUser()
    {
        if(!self::canAdmin())
            throw new PermissionException('You cannot choose your user type.');
        $_SESSION['SDR_ADMIN'] = false;
    }
    
    public static function choseAdmin()
    {
        if(!isset($_SESSION['SDR_ADMIN'])) {
           return self::canAdmin();
        }
        
        return $_SESSION['SDR_ADMIN'];
    }

    public static function isUser()
    {
        return self::isMasquerading() ||
            (!self::isAdmin() && !self::isGuest());
    }

    public static function isGuest()
    {
        return !Current_User::isLogged();
    }

    public static function isMasquerading()
    {
        return isset($_SESSION['sdr_masquerade_user']);
    }

    public static function getUsername()
    {
        if(self::isMasquerading()) {
            return $_SESSION['sdr_masquerade_user']->username;
        }

        return Current_User::getUsername();
    }

    public static function wearMask($username)
    {
        // Invoke Users Module
        $user = new PHPWS_User;
        $db = new PHPWS_DB('users');
        $db->addWhere('username', strtolower($username));
        $result = $db->loadObject($user);

        if(PHPWS_Error::logIfError($result) || $result === FALSE || !$user->approved || !$user->active) {
            PHPWS_Core::initModClass('sdr', 'exception/NoMemberFoundException.php');
            throw new NoMemberFoundException("Could not masquerade as $username");
        }
        $user->loadPermissions();

        $_SESSION['sdr_masquerade_user'] = $user;
    }

    public static function removeMask()
    {
        unset($_SESSION['sdr_masquerade_user']);
    }
    
    public static function orgAdmin($orgid = NULL, $username = NULL, $term = NULL)
    {
        if(is_null($username)) {
            $username = self::getUsername();
            
            if(self::isAdmin())
                return true;
        }
            
        if(is_null($term))
            $term = Term::getCurrentTerm();
            
        $db = new PHPWS_DB('sdr_member');
        $db->addJoin('left', 'sdr_member', 'sdr_membership', 'id', 'member_id');
        if(!is_null($orgid))
            $db->addWhere('sdr_membership.organization_id', $orgid);
        $db->addWhere('sdr_membership.administrator', 1);
        if($term != 'ALL')
            $db->addWhere('sdr_membership.term', $term);
        $db->addWhere('sdr_member.username', $username);
        $result = $db->count();
        
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Permission Counting Failed');
        }
        
        return $result > 0;
    }  

    public static function getDisplay()
    {
        $vars = array();
        $user = Current_User::getDisplayName();

        $auth = SDRSettings::getAuthURI();

        if(UserStatus::isGuest()) {
            $vars['LOGGED_IN_AS'] = dgettext('sdr', 'Viewing as Guest');
            $vars['LOG_LINK']   = '<a href="'.$auth.'"><i class="icon-signin"></i>Log In to ClubConnect</a>';
        } else if(UserStatus::isMasquerading()) {
            $vars['LOGGED_IN_AS'] = sprintf(dgettext('sdr', 'Masquerading as %s'), self::getUsername());
            $vars['OTHERCLASS'] = 'masquerading';
            $cmd = CommandFactory::getCommand('RemoveMask');
            $vars['LOG_LINK'] = $cmd->getLink('Return to Admin');
        } else {
            $vars['LOGGED_IN_AS'] = sprintf(dgettext('sdr', 'Welcome, %s!'), $user);
            $vars['LOG_LINK']  = UserStatus::getLogoutLink();
        }
        
        if(UserStatus::canAdmin() && !UserStatus::isMasquerading()) {
            if(UserStatus::choseAdmin()) {
                $cmd = CommandFactory::getCommand('ChooseUser');
            } else {
                $cmd = CommandFactory::getCommand('ChooseAdmin');
            }
            
            $vars['ADMIN_TYPE'] = $cmd->getLink();
        }
    
        return PHPWS_Template::process($vars, 'sdr', 'UserStatus.tpl');
    }

    public static function sendToLogin()
    {
        $auth = SDRSettings::getAuthURI();
        NQ::close();
        header('HTTP/1.1 307 Temporary Redirect');
        header('Location: '. $auth);
        \sdr\Environment::getInstance()->cleanExit();
    }
    
    public static function getBigLogin($message = NULL)
    {
        if(!UserStatus::isGuest()) {
            return;
        }

        $auth = SDRSettings::getAuthURI();
        
        $vars = array();
        
        if(!is_null($message))
           $vars['MESSAGE'] = $message; 
           
        $vars['LINK'] = '<a class="btn btn-primary btn-block btn-lg" href="'.$auth.'"><i class="icon-signin"></i> Log In to ClubConnect</a>';
        
        return PHPWS_Template::process($vars, 'sdr', 'UserBigLogin.tpl');
    }

    public static function getLogoutLink()
    {
        $auth = Current_User::getAuthorization();
        return '<a href="'.$auth->logout_link.'">Logout</a>';
    }
    
    // This is awful and should be replaced by a user system what makes sense
    public static function makeSureItAllMakesSense()
    {
        if(self::isGuest()) return;
        
        
    }
}

?>
