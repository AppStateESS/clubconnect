<?php

class Student_Employment {

    /**
     * Retrieves employment data for student to appear on their transcript.
     *
     */
    static function getTranscriptEmployment($member_id) {
        $results = array();
        $db = new PHPWS_DB('sdr_employments');
        if($db->isTable('sdr_employments')) {
            
            $db->addColumn('sdr_employments.*');
            $db->addColumn('sdr_employers.employer_description', NULL, '_organization_name');
            
            $db->addJoin('LEFT', 'sdr_employments', 'sdr_employers', 'employer_id', 'id');
            
            $db->addWhere('member_id', $member_id);
            $db->addWhere('deleted', 1, '!=');
            if(($result = $db->select()) == NULL) {
                return;
            }
            
            foreach($result as $item) {
                $mem = new Membership($item);
                $role = new Role();
                $role->setTitle('Student Employee');
                $mem->_roles = array($role);
                $results[] = $mem;
            }
        }
        return $results;
    }
    
    /**
     * Hide/Show employment items on transcript.
     *
     */
    function setVisible($id, $vis) {
        $db = new PHPWS_DB('sdr_employments');
        $db->addWhere('id', $id);

        if($vis) {
            $db->addValue('hidden', 0);
        } else {
            $db->addValue('hidden', 1);
        }
        
        $result = $db->update();
    }

    /**
     * Adds a single employment item which may span multiple semesters.
     *
     */

    function addItem(&$employments, &$record, $state) {
        $db = SDR_Common::createDb('sdr_employers');
        $db->addWhere('id', $record['employer_id']);
        $employer_data = $db->select();
        $employer = $employer_data[0]['employer_description'];
        $item['semester']   = $record['term'];
        $item['year']       = $record['year'];
        $index = SDR_Common::smstr_timestamp($item['semester'], $item['year']);
        $tags['CONTENT'] = "Student Employee, " . $employer;
        if($state == 'user_hide_show' || $state == 'user_view' || $state == 'print_view') {
            $vars['action']         = 'user';
            $vars['transcriptTabs'] = 'true';
            $vars['command']        = 'transcript';
            $vars['tab']            = 'modify_transcript';
            $vars['item_type']      = 'employment';
            $vars['item_id']        = $record['id'];
            if(isset($_REQUEST['id'])) {
                $vars['id']             = $_REQUEST['id'];
            }

            if(isset($record['transcript_hidden']) && $record['transcript_hidden'] == 1 && $state == 'user_hide_show') {
                $vars['sub_command'] = 'show_item';
                $tags['HIDE_SHOW'] = PHPWS_Text::moduleLink(_('show'), 'sdr', $vars);
            } else if($state == 'user_hide_show' && $record['transcript_hidden'] == 0) {
                $vars['sub_command'] = 'hide_item';
                $tags['HIDE_SHOW'] = PHPWS_Text::moduleLink(_('hide'), 'sdr', $vars);
            }

            $item['content'] = PHPWS_Template::processTemplate($tags, 'sdr', 'transcript/item.tpl');
        } else if ($state == 'admin_view') {
            if(isset($record['transcript_hidden']) && $record['transcript_hidden'] == 1) {
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
            $vars['item_type']      = 'employment';
            $tags['DELETE']         = PHPWS_Text::moduleLink(_('delete'), 'sdr', $vars);
            $item['content'] = PHPWS_Template::processTemplate($tags, 'sdr', 'transcript/item_admin_view.tpl');
        }

        $employments[$index][] = $item;
    }

    /**
     * Returns a properly formatted warning statement for when an admin
     * wants to delete an employment item from a student's transcript
     */

    function getDeleteWarning($item_id) {
        $warning = _('Please confirm the deletion of the employment item') . ":<br /><br />";
        $db = SDR_Common::createDb('sdr_employments');
        $db->addWhere('id', $item_id);
        $results = $db->select();
        return $warning;
    }

    /**
     * Delete an employment item
     */
    
    function deleteItem($item_id) {
        if(!Current_User::authorized('sdr')) {
            Current_User::disallow();
            return false;
        }
        $db = SDR_Common::createDb('sdr_employments');
        $db->addValue('deleted', 1);
        $db->addWhere('id', $item_id);
        return $db->update();
    }
}

?>
