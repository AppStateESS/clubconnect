<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationSearch {

    private $searchField;
    private $searchType = 'registered';
    private $result;
    private $term;

    public function __construct()
    {
        $this->term = Term::getCurrentTerm();
    }

    public function setSearchField($value)
    {
        $this->searchField = $value;
    }

    public function setSearchType($type)
    {
        $type = strtolower($type);
        if(in_array($type, array('all', 'registered', 'unregistered')))
            $this->searchType = $type;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function doSearch()
    {
        $db = new PHPWS_DB('sdr_organization_recent');
        $db->addColumn('category');
        $db->addColumn('name');
        $db->addColumn('id');
        $db->addColumn('term');

        if($this->searchType == 'registered') {
            $db->addWhere('term', '201210', '>=');
        } else if($this->searchType == 'unregistered') {
            $db->addWhere('term', $this->term, '!=');
        }

        if(strlen($this->searchField) > 0) {
            $db->addWhere('name', "%$this->searchField%", 'ilike', 'OR', 'name');
            $db->addWhere('category', "%$this->searchField%", 'ilike', 'OR', 'name');
        }

        $db->addOrder('category');
        $db->addOrder('name');

        $result = $db->select();

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Database Error loading Organization List');
        }

        $curTerm = Term::getCurrentTerm();
        $cmd = CommandFactory::getCommand('ShowOrganizationProfile');
        foreach($result as &$r) {
            if($r['term'] != $curTerm) {
                if(UserStatus::isAdmin()) {
                    $r['name'] .= ' <em>' . Term::toString($r['term']) . '</em>';
                } else {
                    $r['name'] .= '<span style="color: #F00">*</span>';
                }
            }
            $cmd->setOrganizationId($r['id']);
            $r['uri'] = $cmd->getUri();
        }

        $this->result = $result;
    }
}

?>
