<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationRegistrationPrintSettings extends PDOController
{
    protected $username;
    protected $cache;

    public function __construct($username)
    {
        $this->username = $username;
        $this->cache = array();
        parent::__construct();
    }

    public function get($username = null)
    {
        if(is_null($username)) $username = $this->username;

        if(!array_key_exists($username, $this->cache)) {
            $stmt = $this->pdo->prepare('
                SELECT a.* FROM sdr_printsettings_registration AS a
                JOIN (SELECT max(setseq) AS setseq FROM sdr_printsettings_registration WHERE username=:username)
                    AS b ON a.setseq = b.setseq
                WHERE username=:username
            ');

            $this->safeExecute($stmt, array('username' => $username));

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if($result === FALSE) {
                $this->safeExecute($stmt, array('username' => 'default'));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if($result === FALSE) {
                    throw new \Exception('No default registration print settings found.');
                }
            }

            $this->cache[$username] = $result;
        }

        return $this->cache[$username];
    }

    public function save($data)
    {
        if(is_null($data['username'])) {
            $data['username'] = $this->username;
        }

        if(array_key_exists($this->cache[$data['username']])) {
            unset($this->cache[$data['username']]);
        }

        $stmt = $this->pdo->prepare('
            SELECT max(setseq) FROM sdr_printsettings_registration WHERE username=:username
        ');

        $this->safeExecute($stmt, array('username' => $data['username']));

        $result = $stmt->fetch(PDO::FETCH_NUM);
        if($result === FALSE) {
            $setseq = 1;
        } else {
            $setseq = $result[0] + 1;
        }

        $stmt = $this->pdo->prepare('
            INSERT INTO sdr_printsettings_registration (
                username,
                setseq,
                header_font,
                header_weight,
                header_font_size,
                header_x,
                header_y,
                title_width,
                cell_height,
                footer_x,
                footer_y
            ) VALUES (
                :username,
                :setseq,
                :header_font,
                :header_weight,
                :header_font_size,
                :header_x,
                :header_y,
                :title_width,
                :cell_height,
                :footer_x,
                :footer_y
            );
        ');

        $commit = array(
            'username'         => $data['username'],
            'setseq'           => $setseq,
            'header_font'      => $data['header_font'],
            'header_weight'    => $data['header_weight'],
            'header_font_size' => $data['header_font_size'],
            'header_x'         => $data['header_x'],
            'header_y'         => $data['header_y'],
            'title_width'      => $data['title_width'],
            'cell_height'      => $data['cell_height'],
            'footer_x'         => $data['footer_x'],
            'footer_y'         => $data['footer_y']
        );

        $this->safeExecute($stmt, $commit);
    }
}

?>
