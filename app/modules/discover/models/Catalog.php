<?php
namespace modules\discover\models;
class Catalog extends \Model {

    public function get_games() {
        return $this->db->all("SELECT * FROM games");
    }

}
