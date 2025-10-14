<?php
/**
 * Extended Bootstrap Grid Group for Meralda JS Input System.
 *
 * This class provides an easy way to build and manage btGrid-style layouts
 * with rows, columns, and inputs. It supports optional coded row/col references
 * and safely repositions existing children without duplicating them.
 *
 * Example:
 *   $grid = new mwmod_mw_jsobj_inputs_btgridgr("data");
 *   $grid->addRowWithCols(3, 4, "maininfo");
 *   $grid->addColCode("maininfo", 1, "projectcol");
 *   $grid->addColCode("maininfo", 2, "clientcol");
 *   $grid->addColCode("maininfo", 3, "typecol");
 *
 *   $grid->addInputInCell("project_name", "maininfo", "projectcol", "Project");
 *   $grid->addInputInCell("client_name",  "maininfo", "clientcol",  "Client");
 *   $grid->addInputInCell("type_name",    "maininfo", "typecol",    "Type");
 */
class mwmod_mw_jsobj_inputs_btgridgr extends mwmod_mw_jsobj_inputs_gr {

    private $_rows = [];
    private $_rowCodes = [];
    private $_colCodes = [];

    function __construct($cod, $def_js_class_pref = false) {
        parent::__construct($cod, "group_btGrid", $def_js_class_pref);
        $this->set_js_type("group_btGrid");
    }

    /**
     * Adds a new row with the given number of columns.
     * Optionally assign a code to the row (so you can reference it by name).
     *
     * @param int $colsCount Number of columns.
     * @param int|false $colSpan Optional colSpan for each column.
     * @param string|false $rowCod Optional row code.
     * @return object The created row object.
     */
    function addRowWithCols($colsCount = 1, $colSpan = false, $rowCod = false) {
        if (!$colSpan) {
            $colSpan = intval(12 / $colsCount);
        }
        $rows = $this->get_array_prop("btGrid.rows");
        $rowIndex = count($this->_rows) + 1;

        $row = $rows->add_data_obj();
        $cols = $row->get_array_prop("cols");

        for ($i = 1; $i <= $colsCount; $i++) {
            $col = $cols->add_data_obj();
            $col->set_prop("colSpan", $colSpan);
        }

        $this->_rows[$rowIndex] = [
            "obj" => $row,
            "cols" => $cols,
            "count" => $colsCount,
            "cod" => $rowCod ?: "row{$rowIndex}"
        ];

        if ($rowCod) {
            $this->_rowCodes[$rowCod] = $rowIndex;
        }

        return $row;
    }

    /**
     * Optionally assign a code to a specific column of a given row.
     */
    function addColCode($rowCodOrIndex, $colIndex, $colCod) {
        $rowIndex = $this->resolveRowIndex($rowCodOrIndex);
        if ($rowIndex && isset($this->_rows[$rowIndex])) {
            $this->_colCodes[$colCod] = [$rowIndex, $colIndex];
        }
    }

    /**
     * Resolve a row code or index into its numeric index.
     */
    private function resolveRowIndex($rowCodOrIndex) {
        if (is_numeric($rowCodOrIndex)) return (int)$rowCodOrIndex;
        return $this->_rowCodes[$rowCodOrIndex] ?? null;
    }

    /**
     * Resolve a column code or index into its numeric coordinates.
     */
    private function resolveColCoords($rowCodOrIndex, $colCodOrIndex) {
        if (is_numeric($colCodOrIndex)) {
            return [$this->resolveRowIndex($rowCodOrIndex), (int)$colCodOrIndex];
        }
        if (isset($this->_colCodes[$colCodOrIndex])) {
            return $this->_colCodes[$colCodOrIndex];
        }
        return [null, null];
    }

    /**
     * Retrieve a row object by index or code.
     */
    function getRow($rowCodOrIndex) {
        $i = $this->resolveRowIndex($rowCodOrIndex);
        return $i ? ($this->_rows[$i]["obj"] ?? null) : null;
    }

    /**
     * Retrieve a column object by row/col index or code.
     */
    function getCol($rowCodOrIndex, $colCodOrIndex) {
        list($r, $c) = $this->resolveColCoords($rowCodOrIndex, $colCodOrIndex);
        if ($r && $c && isset($this->_rows[$r])) {
            return $this->_rows[$r]["cols"]->get_item_by_index($c - 1);
        }
        return null;
    }

    /**
     * Change the span (width) of a specific column.
     */
    function setColSpan($rowCodOrIndex, $colCodOrIndex, $span) {
        if ($col = $this->getCol($rowCodOrIndex, $colCodOrIndex)) {
            $col->set_prop("colSpan", $span);
        }
    }

    /**
     * Add or move an input to a specific cell.
     * - If the input already exists in this group, it is simply moved.
     * - If not, it is added.
     * - If you pass a string code, it reuses or creates a new input.
     *
     * @param object|string $codOrInput Input object or code.
     * @param string|int $rowCodOrIndex Row code or index.
     * @param string|int $colCodOrIndex Column code or index.
     * @param string|false $lbl Optional label.
     * @param string|false $type Optional input type.
     * @return object|false The input object.
     */
    function addInputInCell($codOrInput, $rowCodOrIndex, $colCodOrIndex, $lbl = false, $type = false) {
		list($rowIndex, $colIndex) = $this->resolveColCoords($rowCodOrIndex, $colCodOrIndex);
		if (!$rowIndex || !$colIndex) {
			return false;
		}

		// Case 1: argument is an existing object
		if (is_object($codOrInput)) {
			$input = $codOrInput;
			// If not already registered in this group, add it
			if (!$this->get_child($input->cod)) {
				$this->add_child($input);
			}
		}
		// Case 2: argument is a code (string)
		else {
			// Try to reuse existing input
			if ($existing = $this->get_child($codOrInput)) {
				$input = $existing;
			} else {
				// Create it if not found
				$input = $this->addNewChild($codOrInput, $type);
				if ($lbl) {
					$input->set_prop("lbl", $lbl);
				}
			}
		}

		// Update grid coordinates
		$input->set_prop("parentGrid.row", $rowIndex);
		$input->set_prop("parentGrid.col", $colIndex);

		// Optional: update label if provided (even for existing ones)
		if ($lbl) {
			$input->set_prop("lbl", $lbl);
		}

		return $input;
	}


    /**
     * Return the number of rows currently created.
     */
    function getRowsCount() {
        return count($this->_rows);
    }
}
?>
