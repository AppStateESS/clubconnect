<?
/*
 * Save Handler
 *
 *   Abstractly deals with saving information from an array into a table and
 * confirming that the information matches the column requirements.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package sdr
 */

abstract class SDR_SaveHandler 
{
    var $table;
    var $class;
    var $request;
    var $errors;

    public function save()
    {
        if(!isset($this->table) || !isset($this->request) || !is_array($this->request) || !class_exists($this->class)){
            $this->errors['SYSTEM'] = 'Internal Error, please contact support if the problem persists';
            return false;
        }

        $db = new PHPWS_DB($this->table);
        foreach(get_class_vars($this->class) as $var => $value){
            if(in_array($var, array_keys($this->request))){
                $info = $db->getColumnInfo($var);
                $this->validate($var, $info);
            }
        }

        if(is_null($this->errors) || sizeof($this->errors) == 0){
            $this->saveObject();
        }

        return false;
    }
    
    protected abstract function saveObject();

    private function validate($var, $info){
        //TODO: add a check for not null
        switch($info['type']){
            case 'int':
                if(!is_numeric($this->request[$var])){
                    $this->errors[$var] = 'Must be a number!';
                    return false;
                }
                break;
            case 'string':
                if(strlen($this->request[$var]) > $info['len']){
                    $this->errors[$var] = 'Must not be longer than '.$info['len'].' characters!';
                    return false;
                }
                break;
            default:
                return true;
        }
    }
}
?>
