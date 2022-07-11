<?php
namespace modules\discover\models;
class Catalog extends \Model {

    public function get_game($game_id) {
        $output = [];
        $output['game'] = $this->db->row('SELECT * FROM games WHERE uid=?', [$game_id]);
    }

    public function get_games() {
        return $this->db->all('SELECT * FROM games');
    }

}
