<?php

class SDR_Deans_Chancellors {
    static function getTranscriptItems($member_id) {
        $results = array();
        $db = new PHPWS_DB('sdr_deans_chancellors_lists');
//        if($db->isTable('sdr_deans_chancellors_lists')) {
            /*
            $member = SDR_Common::getMemberData($member_id);
            $student_id = $member[0]['id'];
            */
            $db->addWhere('member_id', $member_id);
            $db->addWhere('deleted', 0);
            if(($result = $db->select()) == NULL) {
                return;
            }
            
            foreach($result as $item) {
                $membership = new Membership($item);
                $membership->_role = $item['d_c_list'];
                
                $role = new Role();
                $role->setTitle($item['d_c_list']);
                $membership->_roles = array($role);
                
                $membership->_organization_name = $item['college'];
                $results[] = $membership;
            }
            
        return $results;
    }

    function setVisible($id, $vis) {
        $db = new PHPWS_DB('sdr_deans_chancellors_lists');
        $db->addWhere('id', $id);

        if($vis) {
            $db->addValue('hidden', 0);
        } else {
            $db->addValue('hidden', 1);
        }
        
        $result = $db->update();
    }

    function addItem(&$honors, $record, $state) {
        $item['semester'] = $record['semester'];
        $item['year']     = $record['year'];
        $index = SDR_Common::smstr_timestamp($item['semester'], $item['year']);
        $tags['CONTENT'] = "Honorary Award, " . $record['college'] . ", " . $record['d_c_list'];
        if($state == 'user_hide_show' || $state == 'user_view') {
            $vars['action'] = 'user';
            $vars['transcriptTabs'] = 'true';
            $vars['command'] = 'transcript';
            $vars['tab'] = 'modify_transcript';
            $vars['item_type'] = 'deans_chancellors_list';
            $vars['item_id'] = $record['id'];

            if(isset($record['hidden']) && $record['hidden'] == 1 && $state == 'user_hide_show') {
                $vars['sub_command'] = 'show_item';
                $tags['HIDE_SHOW'] = PHPWS_Text::moduleLink(_('show'), 'sdr', $vars);
            } else if($state == 'user_hide_show' && $record['hidden'] == 0) {
                $vars['sub_command'] = 'hide_item';
                $tags['HIDE_SHOW'] = PHPWS_Text::moduleLink(_('hide'), 'sdr', $vars);
            }
        
            $item['content'] = PHPWS_Template::processTemplate($tags, 'sdr', 'transcript/item.tpl');
        } else if ($state == 'admin_view') {
            if(isset($record['hidden']) && $record['hidden'] == 1) {
                $tags['HIDE_SHOW'] = 'Hidden';
            }
            $vars['action']         = 'admin';
            $vars['search_sdr']     = '1';
            $vars['tab']            = 'Search';
            $vars['catTab']         = 'Members';
            $vars['back_info']      = 'search_results';
            $vars['command']        = 'delete_item';
            $vars['item_id']        = $record['id'];
            $vars['id']             = $_REQUEST['id'];
            $vars['item_type']      = 'deans_chancellors';
            $tags['DELETE']         = PHPWS_Text::moduleLink(_('delete'), 'sdr', $vars);
            $item['content'] = PHPWS_Template::processTemplate($tags, 'sdr', 'transcript/item_admin_view.tpl');
        }

        $honors[$index][] = $item;
    }

    /**
     * Returns a warning for when an admin wishes to delete a deans or
     * chancellors list honor from a student's transcript.
     */

    function getDeleteWarning($item_id) {
        $warning = _('Please confirm the deletion of the Deans or Chancellors List item') . ":<br /><br />";
        $db = SDR_Common::createDb('sdr_deans_chancellors_lists');
        $db->addWhere('id', $item_id);
        $results = $db->select();
        foreach($results as $result) {
            $warning .= _('Type') . ": " . $result['d_c_list'] . "<br />";
            $warning .= _('College') . ": " . $result['college'] . "<br />";
            $warning .= _('Semester') . ": " . $result['semester'] . "<br />";
            $warning .= _('Year') . ": " . $result['year'] . "<br />";
        }
        return $warning;
    }
    
    /**
     * Delete a deans or chancellors list item
     */

    function deleteItem($item_id) {
        if(!Current_User::authorized('sdr')) {
            Current_User::disallow();
            return false;
        }
        $db = SDR_Common::createDb('sdr_deans_chancellors_lists');
        $db->addValue('deleted', 1);
        $db->addWhere('id', $item_id);
        return $db->update();
    }
}

class SDR_Scholarships {
    static function getTranscriptItems($member_id) {
        $results = array();
        $db = new PHPWS_DB('sdr_scholarship');
        
        if($db->isTable('sdr_scholarship')) {
            $db->addColumn('sdr_scholarship.*');
            $db->addColumn('sdr_settings_scholarship_types.long_description', NULL, '_organization_name');
            
            $db->addWhere('member_id', $member_id);
            $db->addWhere('deleted', 0);
            
            // Join to get the name of the scholarship
            $db->addJoin('LEFT', 'sdr_scholarship', 'sdr_settings_scholarship_types', 'scholarship_id', 'id');
            
            if(($result = $db->select()) == NULL) {
                return;
            }
            
            if(is_null($result)){
                return $results; // return empty array
            }

            foreach($result as $item) {
                $mem = new Membership($item);
                
                $role = new Role();
                $role->setTitle('Recipient');
                $mem->_roles = array($role);
                
                $results[] = $mem;
            }
        }
        return $results;
    }
    
    function setVisible($id, $vis) {
        $db = new PHPWS_DB('sdr_scholarship');
        $db->addWhere('id', $id);

        if($vis) {
            $db->addValue('hidden', 0);
        } else {
            $db->addValue('hidden', 1);
        }
        
        $result = $db->update();
    }

    function addItem(&$honors, $record, $state) {
        $item['semester'] = $record['semester'];
        $item['year']     = $record['year'];
        $index = SDR_Common::smstr_timestamp($item['semester'], $item['year']);
        $db = SDR_Common::createDb('sdr_settings_scholarship_types');
        $db->addColumn('long_description');
        $db->addWhere('id', $record['scholarship_id']);
        $description = $db->select('one');
        $tags['CONTENT'] = "Scholarship, " . $description;
        if($state == 'user_hide_show' || $state == 'user_view' || $state == 'print_view') {
            $vars['action'] = 'user';
            $vars['transcriptTabs'] = 'true';
            $vars['command'] = 'transcript';
            $vars['tab'] = 'modify_transcript';
            $vars['item_type'] = 'scholarship';
            $vars['item_id'] = $record['id'];

            if(isset($record['hidden']) && $record['hidden'] == 1 && $state == 'user_hide_show') {
                $vars['sub_command'] = 'show_item';
                $tags['HIDE_SHOW'] = PHPWS_Text::moduleLink(_('show'), 'sdr', $vars);
            } else if($state == 'user_hide_show' && $record['hidden'] == 0) {
                $vars['sub_command'] = 'hide_item';
                $tags['HIDE_SHOW'] = PHPWS_Text::moduleLink(_('hide'), 'sdr', $vars);
            }
        
            $item['content'] = PHPWS_Template::processTemplate($tags, 'sdr', 'transcript/item.tpl');
        } else if ($state == 'admin_view') {
            if(isset($record['hidden']) && $record['hidden'] == 1) {
                $tags['HIDE_SHOW'] = 'Hidden';
            }
            $vars['action']         = 'admin';
            $vars['search_sdr']     = '1';
            $vars['tab']            = 'Search';
            $vars['catTab']         = 'Members';
            $vars['back_info']      = 'search_results';
            $vars['command']        = 'delete_item';
            $vars['item_id']        = $record['id'];
            $vars['id']             = $_REQUEST['id'];
            $vars['item_type']      = 'scholarship';
            $tags['DELETE']         = PHPWS_Text::moduleLink(_('delete'), 'sdr', $vars);
            $item['content'] = PHPWS_Template::processTemplate($tags, 'sdr', 'transcript/item_admin_view.tpl');
        }

        $honors[$index][] = $item;
    }

    /**
     * Returns a warning for when an admin wishes to delete a scholarship
     * item from a student's transcript.
     */

    function getDeleteWarning($item_id) {
        $warning = _('Please confirm the deletion of the Scholarship item') . ":<br /><br />";
        $db = SDR_Common::createDb('sdr_scholarship');
        $db->addWhere('id', $item_id);
        $results = $db->select();
        foreach($results as $result) {
            $warning .= _('Semester') . ": " . $result['semester'] . "<br />";
            $warning .= _('Year') . ": " . $result['year'] . "<br />";
        }
        return $warning;
    }
    
    /**
     * Delete a scholarship item
     */

    function deleteItem($item_id) {
        if(!Current_User::authorized('sdr')) {
            Current_User::disallow();
            return false;
        }
        $db = SDR_Common::createDb('sdr_scholarship');
        $db->addValue('deleted', 1);
        $db->addWhere('id', $item_id);
        return $db->update();
    }
}

?>
