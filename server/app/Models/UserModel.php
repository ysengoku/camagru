<?php
    class UserModel {
        private $db;

        public function __construct($db) {
            $this->db = $db;
        }
    }
?>