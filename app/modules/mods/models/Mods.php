<?php
namespace modules\mods\models;
class Mods extends \Model {

    public function does_mod_exist($game_id, $name) {
        return !empty($this->db->all("SELECT uid FROM mod_catalog WHERE game_id=? AND name=?", [$game_id, $name]));
    }

    public function get_user($user_id) {

        $output = [];

        $output['user'] = $this->db->row("SELECT display_name FROM users WHERE uid=?", [$user_id]);
        $output['mods'] = $this->get_user_mods($user_id);

        return $output;

    }

    public function update_mod($mod_catalog_id, $update) {

        $update_str = $this->db->parse_data_array($update, 'update');
        $update['uid'] = $mod_catalog_id;

        $this->db->run("UPDATE mod_catalog SET {$update_str} WHERE uid=:uid", $update);

    }

    public function get_user_mods($user_id) {
        return $this->db->all('
            SELECT
                mc.*,
                u.display_name as author,
                g.name as game_name
            FROM
                mod_catalog mc
            LEFT JOIN users u ON u.uid=owner
            LEFT JOIN games g ON g.uid=game_id
            WHERE
                mc.owner=?
                AND mc.disabled=0
        ', [$user_id]);
    }

    public function get_mods($game_id) {
        return $this->db->all('
            SELECT
                mc.*,
                u.display_name as author,
                g.name as game_name
            FROM
                mod_catalog mc
            LEFT JOIN users u ON u.uid=owner
            LEFT JOIN games g ON g.uid=game_id
            WHERE
                mc.game_id=?
                AND mc.disabled=0
        ', [$game_id]);
    }

    public function get_mod($mod_catalog_id) {

        $output = [];

        $output['info'] = $this->db->row('
            SELECT
                mc.*,
                u.display_name as author
            FROM
                mod_catalog mc
            LEFT JOIN users u ON u.uid=mc.owner
            WHERE
                mc.uid=?
        ', [$mod_catalog_id]);

        $current_file = null;
        $files = $this->db->all("SELECT * FROM mod_attached_files WHERE status=4 AND mod_catalog_id=?", [$mod_catalog_id]);

        foreach($files as $key => $file) {
            if($file['version'] == $output['info']['current_version']) {
                $current_file = $file;
                unset($files[$key]);
            }
        }

        $output['files'] = [
            'current' => $current_file,
            'other' => $files
        ];

        return $output;

    }

    public function add_mod($data) {

        $catalog_id = $this->db->insert('mod_catalog', [
            'game_id' => $data['game_id'],
            'owner' => $_SESSION['user']['uid'],
            'name' => $data['name'],
            'description' => $data['description'],
            'current_version' => $data['version']
        ]);

        foreach($data['link_file'] ?: [] as $key => $link) {
            $description = $data['link_file_description'][$key];
            $this->db->insert('mod_attached_links', [
                'mod_catalog_id' => $catalog_id,
                'href' => $link,
                'description' => $description
            ]);
        }

        return $catalog_id;

    }

}
