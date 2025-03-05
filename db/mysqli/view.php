<?php
//20250207
class mwmod_mw_db_mysqli_view extends mwmod_mw_db_tbl {
    function __construct($dbman, $view) {
        parent::init($dbman, $view);
    }

    // Override method to load metadata from INFORMATION_SCHEMA (specific for views)
    function load_tbl_fields() {
        $sql = "SELECT COLUMN_NAME AS Field, DATA_TYPE AS Type, 
                       IS_NULLABLE AS `Null`, COLUMN_DEFAULT AS `Default` 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = '{$this->tbl}'";

        if (!$query = $this->dbman->query($sql)) {
            return false;
        }
        
        $r = [];
        while ($data = $this->fetch_assoc($query)) {
            if ($id = $data["Field"]) {
                $r[$id] = $data;
            }
        }
        return $r;
    }

    // Override: Views do not appear in SHOW TABLE STATUS, return dummy or false
    function get_current_status($cod = false) {
        return false; // Views do not have auto-increment or storage metadata
    }

    // Override: Identify if the current instance is a view (useful for debugging)
    function is_view() {
        return true;
    }
}
?>