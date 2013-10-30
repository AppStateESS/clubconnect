<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class RoleController extends PDOController
{
    protected $all;
    protected $cert;

    public function getAll()
    {
        if(is_null($this->all)) {
            $stmt = $this->pdo->prepare('
                SELECT
                    id,
                    title,
                    rank 
                FROM sdr_role 
                WHERE hidden = 0 
                ORDER BY title
            ');

            if(!$this->safeExecute($stmt, array())) return FALSE;

            $this->all = $stmt->fetchAll();
        }

        return $this->all;
    }

    public function getRequiredForCertification()
    {
        if(is_null($this->cert)) {
            $all = $this->getAll();

            $ret = array();
            foreach($all as $role) {
                if($role['rank'] > 2) {
                    $ret[] = $role['id'];
                }
            }
            
            $this->cert = $ret;
        }

        return $this->cert;
    }
}

?>
