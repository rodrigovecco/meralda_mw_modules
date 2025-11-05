<?php
/**
 * Extended Bootstrap Grid Group for Meralda JS Input System.
 * 
 * This class builds dynamic grid structures compatible with
 * mwmod_mw_jsobj_obj and mwmod_mw_jsobj_array.
 * 
 * It automatically creates missing rows/columns and safely
 * repositions existing children.
 */
class mwmod_mw_jsobj_inputs_btgridgr extends mwmod_mw_jsobj_inputs_gr {
    private $_rowCodes = [];
    private $_colCodes = [];

	public $defaultSpan=4;

    function __construct($cod, $def_js_class_pref = false) {
        parent::__construct($cod, "group_btGrid", $def_js_class_pref);
        $this->set_js_type("group_btGrid");
    }
	function setTitleMode($lbl=false,$type="group_btGridWithTitle"){
		if(!$type){
			$type="group_btGridWithTitle";	
		}
		$this->set_js_type($type);
		if($lbl){
			$this->set_prop("lbl",$lbl);	
		}
		
	}

    //────────────────────────────────────────────
    // Core grid helpers
    //────────────────────────────────────────────

    /**
     * Get or create the main rows array.
     * @return mwmod_mw_jsobj_array
     */
    protected function getRowsArray() {
        return $this->get_array_prop("btGrid.rows");
    }

    /**
     * Retrieve or create a row (mwmod_mw_jsobj_obj) by index or code.
     */
    function getOrCreateRow($rowCodOrIndex, $colsIfCreate = 1) {
        $rows = $this->getRowsArray();
        $rowIndex = $this->resolveRowIndex($rowCodOrIndex);

        // Numeric index
        if (!$rowIndex) {
            if (is_numeric($rowCodOrIndex)) {
                $rowIndex = intval($rowCodOrIndex);
            } else {
                // New code
                $rowIndex = count($rows->get_data()) + 1;
                $this->_rowCodes[$rowCodOrIndex] = $rowIndex;
            }
        }

        // Create missing rows
        $rowsCount = count($rows->get_data());
        while ($rowsCount < $rowIndex) {
            $newRow = $rows->add_data_obj();
            $newRow->get_array_prop("cols");
            $rowsCount++;
        }

        $row = $rows->get_data()[$rowIndex - 1];
        if (!$row) {
            $row = $rows->add_data_obj();
            $row->get_array_prop("cols");
        }

        return $row;
    }

    /**
     * Retrieve or create a column inside a given row.
     */
    function getOrCreateCol($rowObj, $colCodOrIndex, $defaultSpan = null) {
		if($defaultSpan===null){
			$defaultSpan=$this->defaultSpan;
		}
        $cols = $rowObj->get_array_prop("cols");

        // If col is referenced by code
        if (!is_numeric($colCodOrIndex) && isset($this->_colCodes[$colCodOrIndex])) {
            list($rowIdx, $colIdx) = $this->_colCodes[$colCodOrIndex];
            return $cols->get_data()[$colIdx - 1] ?? $cols->add_data_obj();
        }

        $colIndex = intval($colCodOrIndex);
        if ($colIndex < 1) $colIndex = 1;

        // Create missing columns
        $colsCount = count($cols->get_data());
        while ($colsCount < $colIndex) {
            $newCol = $cols->add_data_obj();
            $newCol->set_prop("colSpan", $defaultSpan);
            $colsCount++;
        }

        return $cols->get_data()[$colIndex - 1];
    }

    /**
     * Assign a code to a column.
     */
    function addColCode($rowCodOrIndex, $colIndex, $colCod) {
        $rowIndex = $this->resolveRowIndex($rowCodOrIndex);
        if ($rowIndex) {
            $this->_colCodes[$colCod] = [$rowIndex, $colIndex];
        }
    }

    /**
     * Resolve a row code or index to a numeric index.
     */
    private function resolveRowIndex($rowCodOrIndex) {
        if (is_numeric($rowCodOrIndex)) return (int)$rowCodOrIndex;
        return $this->_rowCodes[$rowCodOrIndex] ?? null;
    }

    //────────────────────────────────────────────
    // Main API
    //────────────────────────────────────────────

    /**
     * Add or move an input to a specific cell.
     * Automatically creates rows/columns as needed.
     */
    function addInputInCell($codOrInput, $rowCodOrIndex, $colCodOrIndex, $lbl = false, $type = false) {
        // Ensure row/col exist
        $rowObj = $this->getOrCreateRow($rowCodOrIndex);
        $colObj = $this->getOrCreateCol($rowObj, $colCodOrIndex);

        // Resolve input object
        if (is_object($codOrInput)) {
            $input = $codOrInput;
            if (!$this->get_child($input->cod)) {
                $this->add_child($input);
            }
        } else {
            if ($existing = $this->get_child($codOrInput)) {
                $input = $existing;
            } else {
                $input = $this->addNewChild($codOrInput, $type);
                if ($lbl) $input->set_prop("lbl", $lbl);
            }
        }

        // Row & column numeric indexes for saving props
        $rowIndex = $this->resolveRowIndex($rowCodOrIndex) ?: 1;
        $colIndex = is_numeric($colCodOrIndex) ? intval($colCodOrIndex) : 1;

        $input->set_prop("parentGrid.row", $rowIndex);
        $input->set_prop("parentGrid.col", $colIndex);
        if ($lbl) $input->set_prop("lbl", $lbl);

        return $input;
    }

    /**
     * Add a row with fixed number of columns.
     */
    function addRowWithCols($colsCount = 1, $colSpan = false, $rowCod = false) {
        $rows = $this->getRowsArray();
        if (!$colSpan) $colSpan = intval(12 / $colsCount);
        $row = $rows->add_data_obj();
        $cols = $row->get_array_prop("cols");

        for ($i = 1; $i <= $colsCount; $i++) {
            $col = $cols->add_data_obj();
            $col->set_prop("colSpan", $colSpan);
        }

        if ($rowCod) {
            $index = count($rows->get_data());
            $this->_rowCodes[$rowCod] = $index;
        }
        return $row;
    }

    function setColSpan($rowCodOrIndex, $colCodOrIndex, $span) {
        $row = $this->getOrCreateRow($rowCodOrIndex);
        $col = $this->getOrCreateCol($row, $colCodOrIndex);
        if ($col) $col->set_prop("colSpan", $span);
    }

    function getRowsCount() {
        return count($this->getRowsArray()->get_data());
    }
}
?>
