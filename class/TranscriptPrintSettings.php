<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class TranscriptPrintSettings extends PDOController
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
                SELECT a.* FROM sdr_transcript_settings AS a
                JOIN (SELECT max(setseq) AS setseq FROM sdr_transcript_settings WHERE username=:username)
                    AS b ON a.setseq = b.setseq
                WHERE username=:username
            ');

            $this->safeExecute($stmt, array('username' => $username));

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if($result === FALSE) {
                $this->safeExecute($stmt, array('username' => 'default'));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if($result === FALSE) {
                    throw new \Exception('No default transcript print settings found.');
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
            SELECT max(setseq) FROM sdr_transcript_settings WHERE username=:username
        ');

        $this->safeExecute($stmt, array('username' => $data['username']));

        $result = $stmt->fetch(PDO::FETCH_NUM);
        if($result === FALSE) {
            $setseq = 1;
        } else {
            $setseq = $result[0] + 1;
        }

        $stmt = $this->pdo->prepare('
            INSERT INTO sdr_transcript_settings (
                username,
                setseq,
                std_font_size,
                start_y,
                start_x,
                cell_height,
                name_width,
                sid_x_offset,
                sid_width,
                body_y_offset,
                foot_x,
                foot_y,
                date_width,
                pn_x_offset,
                pn_width,
                of_x_offset,
                of_width
            ) VALUES (
                :username,
                :setseq,
                :std_font_size,
                :start_y,
                :start_x,
                :cell_height,
                :name_width,
                :sid_x_offset,
                :sid_width,
                :body_y_offset,
                :foot_x,
                :foot_y,
                :date_width,
                :pn_x_offset,
                :pn_width,
                :of_x_offset,
                :of_width
            )
        ');

        $commit = array(
            'username'      => $data['username'],
            'std_font_size' => $data['std_font_size'],
            'start_y'       => $data['start_y'],
            'start_x'       => $data['start_x'],
            'cell_height'   => $data['cell_height'],
            'name_width'    => $data['name_width'],
            'sid_x_offset'  => $data['sid_x_offset'],
            'sid_width'     => $data['sid_width'],
            'body_y_offset' => $data['body_y_offset'],
            'foot_x'        => $data['foot_x'],
            'foot_y'        => $data['foot_y'],
            'date_width'    => $data['date_width'],
            'pn_x_offset'   => $data['pn_x_offset'],
            'pn_width'      => $data['pn_width'],
            'of_x_offset'   => $data['of_x_offset'],
            'of_width'      => $data['of_width'],
            'setseq'        => $setseq,
        );

        $this->safeExecute($stmt, $commit);
    }
}

?>
