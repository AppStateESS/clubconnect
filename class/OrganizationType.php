<?php

/**
 * OrganizationType - Model/data class for representing the various types/categories of organizations
 * @author Jeremy Booker
 */

class OrganizationType {
    
    /**
     * Static helper methods
     */
    
    /**
     * Returns an associative array (id => type) of organization types,
     * suitable for use in an drop down box
     * @return Array associate array of organization types
     */
    public static function getOrganizationTypes($select = 'assoc'){
        $db = new PHPWS_DB('sdr_organization_type');
        
        $db->addColumn('id');
        $db->addColumn('name');
        
        $db->addOrder('name ASC');
        
        $result = $db->select($select);
        
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        
        return $result;
    }
}

?>
