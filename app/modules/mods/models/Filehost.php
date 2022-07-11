<?php
namespace modules\mods\models;
class Filehost extends \Model {

    public function get_file_info($file_id) {
        return $this->db->row('SELECT * FROM mod_attached_files WHERE uid=?', [$file_id]);
    }

    public function upload_file($game_catalog_id, $data, $file) {
        //$data requires game_id, name, version

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        $allowed_exts = ['zip'];
        if(!in_array($ext, $allowed_exts)) {
            die('Could not upload mod. We only accept these file extensions: ' . implode(', ', $allowed_exts) . '<br>If you need an exception to be made, please contact the developer.');
        }

        $max_mb = 100;
        if($file['size'] > $max_mb * 1048576) {
            die("Could not upload mod. We only accept files under {$max_mb}MB. If you need an exception to be made, please contact the developer.");
        }

        $game_name = $this->db->cell('SELECT name FROM games WHERE uid=?', [$data['game_id']]);

        $upload_path = "uploads/{$game_name}/{$data['name']}/";
        $storage_name = "{$data['name']}-{$data['version']}.{$ext}";

        if(!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $full_path = "{$upload_path}{$storage_name}";
        $moved = move_uploaded_file($file['tmp_name'], $full_path);

        if($moved) {
            $this->db->insert('mod_attached_files', [
                'game_id' => $data['game_id'],
                'mod_catalog_id' => $game_catalog_id,
                'path' => $upload_path,
                'filename' => $storage_name,
                'version' => $data['version']
            ]);
        }

    }

}
