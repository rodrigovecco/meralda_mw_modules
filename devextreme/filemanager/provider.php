<?php
//en contruccion usar con cuidado!
/**
 * DevExtreme backend provider for Meralda filemanager.
 * - NO output here. UI layer does json_output_data().
 * - Methods return arrays only.
 *
 * @property-read mwmod_mw_ap_paths_subpath $pathMan
 */
class mwmod_mw_devextreme_filemanager_provider extends mw_baseobj {
    private $pathMan;

    public $allowCreate = false;
    public $allowCopy = false;
    public $allowMove = false;
    public $allowDelete = false;
    public $allowRename = false;
    public $allowUpload = false;
    public $allowDownload = false;

    // Recursive delete must be explicit
    public $allowRecursiveDelete = false;

    function __construct($pathman) {
        $this->setPathMan($pathman);
    }

    // ============================================================
    // ROUTER
    // ============================================================
    function handle_request($action) {

        switch ($action) {
            case "list":
                return $this->cmd_list();

            case "mkdir":
                return $this->allowCreate ? $this->cmd_mkdir() : $this->err("Create not allowed");

            case "delete":
                return $this->allowDelete ? $this->cmd_delete() : $this->err("Delete not allowed");

            case "rename":
                return $this->allowRename ? $this->cmd_rename() : $this->err("Rename not allowed");

            case "move":
                return $this->allowMove ? $this->cmd_move() : $this->err("Move not allowed");

            case "copy":
                return $this->allowCopy ? $this->cmd_copy() : $this->err("Copy not allowed");

            case "upload":
                return $this->allowUpload ? $this->cmd_upload() : $this->err("Upload not allowed");

            // DevExtreme chunked upload
            case "uploadchunk":
                return $this->allowUpload ? $this->cmd_upload_chunk() : $this->err("Upload not allowed");

            case "cancelUpload":
                return $this->cmd_cancel_upload();

            case "download":
                return $this->allowDownload ? $this->cmd_download() : $this->err("Download not allowed");
        }

        return $this->err("Unknown action '$action'");
    }

    // ============================================================
    // LIST
    // ============================================================
    function cmd_list() {
        $relRaw = $_REQUEST["path"] ?? "";
        $rel = $this->norm_rel($relRaw); // false if root

        $dirAbs = $this->pathMan->get_sub_path($rel);

        // Root requested but base doesn't exist => ok empty
        if ($rel === false && (!$dirAbs || !is_dir($dirAbs))) {
            return ["success" => true, "items" => []];
        }

        if (!$dirAbs || !is_dir($dirAbs)) {
            return $this->err("Invalid directory");
        }

        $items = [];
        foreach (scandir($dirAbs) as $f) {
            if ($f === "." || $f === "..") continue;

            $full = $dirAbs . "/" . $f;
            $isDir = is_dir($full);

            $items[] = [
                "name" => $f,
                "isDirectory" => $isDir,
                "size" => $isDir ? 0 : filesize($full),
                "dateModified" => date("c", filemtime($full)),
                "path" => trim(($relRaw ? trim($relRaw, "/") . "/" : "") . $f, "/")
            ];
        }

        return ["success" => true, "items" => $items];
    }

    // ============================================================
    // MKDIR
    // ============================================================
    function cmd_mkdir() {
        $relRaw = $_REQUEST["path"] ?? "";
        $relClean = trim($relRaw, "/"); // keep string here for concatenation

        $name = $_REQUEST["name"] ?? "";
        $safe = $this->sanitize_name($name);
        if (!$safe) {
            return $this->err("Invalid folder name");
        }

        $fullRel = trim($relClean . "/" . $safe, "/");

        // Your API handles creation and security
        if (!$this->pathMan->check_and_create_path($fullRel)) {
            return $this->err("Cannot create folder");
        }

        return $this->ok();
    }

    // ============================================================
    // DELETE
    // ============================================================
    function cmd_delete() {
        $relRaw = $_REQUEST["path"] ?? "";
        $rel = $this->norm_rel($relRaw);
        if ($rel === false) {
            return $this->err("Missing path");
        }

        $sub = $this->pathMan->get_sub_path_man($rel);
        if (!$sub) {
            return $this->err("Invalid path");
        }

        $abs = $sub->get_path();
        if (!$abs || !file_exists($abs)) {
            return $this->err("Not found");
        }

        // File
        if (is_file($abs)) {
            if (!unlink($abs)) {
                return $this->err("Failed to delete file");
            }
            return $this->ok();
        }

        // Directory
        if (!$this->allowRecursiveDelete) {
            foreach (scandir($abs) as $f) {
                if ($f !== "." && $f !== "..") {
                    return $this->err("Directory not empty");
                }
            }
        }

        if (!$sub->delete()) {
            return $this->err("Delete failed");
        }

        return $this->ok();
    }

    // ============================================================
    // RENAME (extension validated by FileMan)
    // ============================================================
    function cmd_rename() {
        $relRaw = $_REQUEST["path"] ?? "";
        $rel = $this->norm_rel($relRaw);

        $newName = $_REQUEST["newName"] ?? "";
        $safe = $this->sanitize_name($newName);

        if ($rel === false || !$safe) {
            return $this->err("Invalid name or path");
        }

        $oldAbs = $this->pathMan->get_sub_path($rel);
        if (!$oldAbs || !file_exists($oldAbs)) {
            return $this->err("Path not found");
        }

        $parentRelRaw = dirname(trim($relRaw, "/"));
        if ($parentRelRaw === "." || $parentRelRaw === "./") {
            $parentRelRaw = "";
        }

        $parentRel = $this->norm_rel($parentRelRaw); // false if root
        $parentAbs = $this->pathMan->get_sub_path($parentRel);

        if (!$parentAbs || !is_dir($parentAbs)) {
            return $this->err("Invalid parent directory");
        }

        $newAbs = $parentAbs . "/" . $safe;

        if (file_exists($newAbs)) {
            return $this->err("A file or folder with that name already exists");
        }

        // If file rename => validate ext using your fileman
        if (is_file($oldAbs)) {
            $fm = $this->get_fileman_for_rel($parentRel);
            if (!$fm || !$fm->check_ext_from_filename($safe)) {
                return $this->err("Invalid extension");
            }
        }

        if (!rename($oldAbs, $newAbs)) {
            return $this->err("Rename failed");
        }

        return $this->ok();
    }

    // ============================================================
    // MOVE
    // ============================================================
    function cmd_move() {
        $fromRaw = $_REQUEST["from"] ?? "";
        $toRaw   = $_REQUEST["to"] ?? "";

        $fromRel = $this->norm_rel($fromRaw);
        $toRel   = $this->norm_rel($toRaw);

        if ($fromRel === false || $toRel === false) {
            return $this->err("Missing parameters");
        }

        $srcAbs = $this->pathMan->get_sub_path($fromRel);
        if (!$srcAbs || !file_exists($srcAbs)) {
            return $this->err("Source not found");
        }

        $dstAbs = $this->pathMan->get_sub_path($toRel);
        if (!$dstAbs || !is_dir($dstAbs)) {
            return $this->err("Destination must be a directory");
        }

        $newAbs = $dstAbs . "/" . basename($srcAbs);
        if (file_exists($newAbs)) {
            return $this->err("Target already exists");
        }

        if (!rename($srcAbs, $newAbs)) {
            return $this->err("Move failed");
        }

        return $this->ok();
    }

    // ============================================================
    // COPY (validates extensions using destination FileMan)
    // ============================================================
    function cmd_copy() {
        $fromRaw = $_REQUEST["from"] ?? "";
        $toRaw   = $_REQUEST["to"] ?? "";

        $fromRel = $this->norm_rel($fromRaw);
        $toRel   = $this->norm_rel($toRaw);

        if ($fromRel === false || $toRel === false) {
            return $this->err("Missing parameters");
        }

        $srcAbs = $this->pathMan->get_sub_path($fromRel);
        $dstAbs = $this->pathMan->get_sub_path($toRel);

        if (!$srcAbs || !file_exists($srcAbs)) {
            return $this->err("Source not found");
        }
        if (!$dstAbs || !is_dir($dstAbs)) {
            return $this->err("Invalid destination");
        }

        $targetAbs = $dstAbs . "/" . basename($srcAbs);
        if (file_exists($targetAbs)) {
            return $this->err("Target already exists");
        }

        $fm = $this->get_fileman_for_rel($toRel);
        if (!$fm) {
            return $this->err("Fileman not available");
        }

        if (is_file($srcAbs)) {
            $fname = basename($srcAbs);
            if (!$fm->check_ext_from_filename($fname)) {
                return $this->err("Copy rejected (invalid extension)");
            }
            if (!copy($srcAbs, $targetAbs)) {
                return $this->err("Copy failed");
            }
            return $this->ok();
        }

        if (!$this->copy_recursive_dir_checked($srcAbs, $targetAbs, $fm)) {
            return $this->err("Recursive copy failed (or invalid extension)");
        }

        return $this->ok();
    }

    private function copy_recursive_dir_checked($src, $dst, $fm) {
        if (!mkdir($dst, 0777, true)) {
            return false;
        }

        foreach (scandir($src) as $f) {
            if ($f === "." || $f === "..") continue;

            $a = $src . "/" . $f;
            $b = $dst . "/" . $f;

            if (is_dir($a)) {
                if (!$this->copy_recursive_dir_checked($a, $b, $fm)) return false;
            } else {
                if (!$fm->check_ext_from_filename($f)) return false;
                if (!copy($a, $b)) return false;
            }
        }
        return true;
    }

    // ============================================================
    // UPLOAD NORMAL (FileMan returns new filename)
    // ============================================================
    function cmd_upload() {
		die("not implemented");
        $relRaw = $_REQUEST["path"] ?? "";
        $rel = $this->norm_rel($relRaw);

        $dstAbs = $this->pathMan->get_sub_path($rel);
        if (!$dstAbs || !is_dir($dstAbs)) {
            return $this->err("Invalid upload path");
        }

        if (!isset($_FILES["file"])) {
            return $this->err("No file received");
        }

        $sub = $this->pathMan->get_sub_path_man($rel);
        if (!$sub) {
            return $this->err("Invalid base directory");
        }

        $fm = $sub->get_file_man();
        if (!$fm) {
            return $this->err("Fileman not available");
        }

        $originalName = $_FILES["file"]["name"] ?? "";
        $safeOrig = $this->sanitize_name($originalName);
        if (!$safeOrig) {
            return $this->err("Invalid file name");
        }

        // IMPORTANT: upload_file returns STRING filename or false
        $newNameFull = $fm->upload_file(
            "file",
            $dstAbs,
            false,
            false,
            $safeOrig,
            true,
            true,
            false
        );

        if (!$newNameFull || !is_string($newNameFull)) {
            return $this->err("Upload rejected");
        }

        $finalRelPath = trim((trim($relRaw, "/") ? trim($relRaw, "/") . "/" : "") . $newNameFull, "/");

        return [
            "success" => true,
            "name"    => $newNameFull,
            "path"    => $finalRelPath
        ];
    }

    // ============================================================
    // UPLOAD CHUNKED (assemble then validate with FileMan)
    // ============================================================
    private function cmd_upload_chunk() {
		
        $uploadId  = $_REQUEST["uploadId"] ?? false;
        $destRelRaw = $_REQUEST["destinationDirectory"] ?? "";
        $fileName  = $_REQUEST["fileName"] ?? false;

        $chunkIndex = intval($_REQUEST["chunkIndex"] ?? -1);
        $chunkCount = intval($_REQUEST["chunkCount"] ?? -1);

        if (!$uploadId || !$fileName) {
            return $this->err("Missing parameters");
        }

        $safe = $this->sanitize_name($fileName);
        if (!$safe) {
            return $this->err("Invalid file name");
        }

        $destRel = $this->norm_rel($destRelRaw);
        $destAbs = $this->pathMan->get_sub_path($destRel);
        if (!$destAbs || !is_dir($destAbs)) {
            return $this->err("Invalid upload destination");
        }

        $tmpDir = $destAbs . "/.__chunk_tmp_" . $uploadId;
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        if (!isset($_FILES["chunk"])) {
            return $this->err("Missing chunk file");
        }

        $chunkPath = $tmpDir . "/chunk_" . $chunkIndex;
        if (!move_uploaded_file($_FILES["chunk"]["tmp_name"], $chunkPath)) {
            return $this->err("Failed to store chunk");
        }

        // Not last chunk
        if ($chunkIndex + 1 < $chunkCount) {
            return $this->ok();
        }

        // Assemble final tmp
        $finalTmpPath = $tmpDir . "/final_tmp_upload";
        $out = fopen($finalTmpPath, "wb");

        for ($i = 0; $i < $chunkCount; $i++) {
            $part = $tmpDir . "/chunk_" . $i;
            if (!file_exists($part)) {
                fclose($out);
                return $this->err("Missing chunk $i");
            }
            fwrite($out, file_get_contents($part));
        }
        fclose($out);

        // FileMan validation + secure name
		$destRel1=$destRel;
		if(!$destRel1){
			$destRel1=false;
		}
		if($destRel1){
			 $sub = $this->pathMan->get_sub_path_man($destRel1);
		}else{
			 $sub = $this->pathMan;
		}
       
        if (!$sub) {
            return $this->err("Invalid destination");
        }

        $fm = $sub->get_file_man();
        if (!$fm) {
            return $this->err("Fileman unavailable");
        }

        // Validate extension
        if (!$fm->check_ext_from_filename($safe)) {
            $this->cleanup_chunk_dir($tmpDir);
            return $this->err("Upload rejected (invalid extension)");
        }

        $finalNameNoExt = $fm->get_url_secure_filename_noext($safe);
        $ext = $fm->get_ext($safe);
        $finalNameFull = $finalNameNoExt . "." . $ext;

        $finalAbs = $destAbs . "/" . $finalNameFull;

        if (!rename($finalTmpPath, $finalAbs)) {
            $this->cleanup_chunk_dir($tmpDir);
            return $this->err("Failed to finalize upload");
        }

        $this->cleanup_chunk_dir($tmpDir);

        $finalRelPath = trim((trim($destRelRaw, "/") ? trim($destRelRaw, "/") . "/" : "") . $finalNameFull, "/");

        return [
            "success" => true,
            "name"    => $finalNameFull,
            "path"    => $finalRelPath
        ];
    }

    // ============================================================
    // CANCEL UPLOAD (remove temp chunks)
    // ============================================================
    private function cmd_cancel_upload() {
        $uploadId = $_REQUEST["uploadId"] ?? false;
        $destRelRaw = $_REQUEST["destinationDirectory"] ?? "";

        if (!$uploadId) {
            return $this->err("Missing uploadId");
        }

        $destRel = $this->norm_rel($destRelRaw);
        $destAbs = $this->pathMan->get_sub_path($destRel);
        if (!$destAbs || !is_dir($destAbs)) {
            return $this->err("Invalid directory");
        }

        $tmpDir = $destAbs . "/.__chunk_tmp_" . $uploadId;
        $this->cleanup_chunk_dir($tmpDir);

        return $this->ok();
    }

    private function cleanup_chunk_dir($tmpDir) {
        if (!is_dir($tmpDir)) return;
        foreach (glob($tmpDir . "/chunk_*") as $c) {
            @unlink($c);
        }
        @unlink($tmpDir . "/final_tmp_upload");
        @rmdir($tmpDir);
    }

    // ============================================================
    // DOWNLOAD (UI handles real download)
    // ============================================================
    function cmd_download() {
        return ["success" => true];
    }

    // ============================================================
    // HELPERS
    // ============================================================
    private function norm_rel($relRaw) {
        $rel = trim((string)$relRaw, "/");
        return ($rel === "") ? false : $rel;
    }

    private function get_fileman_for_rel($relFolder = false) {
        $sub = $this->pathMan->get_sub_path_man($relFolder);
        if (!$sub) return false;
        return $sub->get_file_man();
    }

    private function sanitize_name($name) {
		$name = trim((string)$name);
		if ($name === "") return false;

		// 1. Evitar path traversal
		$name = str_replace(["..", "/", "\\"], "", $name);

		// 2. Normalizar unicode → ascii seguro
		$name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);

		// 3. Reemplazar cualquier carácter no permitido por "_"
		$name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);

		// 4. Evitar múltiples ____ seguidos
		$name = preg_replace('/_+/', '_', $name);

		// 5. Evitar que empiece con un punto
		$name = ltrim($name, '.');

		return $name ?: false;
	}

    private function ok() {
        return ["success" => true];
    }

    private function err($msg) {
        return ["success" => false, "msg" => $msg];
    }

    // ============================================================
    // PERMISSIONS (unchanged)
    // ============================================================
    /**
     * @param mwmod_mw_jsobj_obj $filemanagerParams
     * @return void
     */
    function setDxFileManagerProps($filemanagerParams) {
        $filemanagerParams->set_prop("permissions.create",$this->allowCreate);
        $filemanagerParams->set_prop("permissions.copy",$this->allowCopy);
        $filemanagerParams->set_prop("permissions.move",$this->allowMove);
        $filemanagerParams->set_prop("permissions.delete",$this->allowDelete);
        $filemanagerParams->set_prop("permissions.rename",$this->allowRename);
        $filemanagerParams->set_prop("permissions.upload",$this->allowUpload);
        $filemanagerParams->set_prop("permissions.download",$this->allowDownload);
		$filemanagerParams->set_prop("upload.chunkSize",1048576); // 1 MB

        if ($fm = $this->pathMan->get_file_man()) {
            $exts = $fm->get_allowed_exts();
            $arr = $filemanagerParams->get_array_prop("allowedFileExtensions");
            foreach ($exts as $ext) {
                $arr->add_data("." . $ext);
            }
        }
    }

    function setAllowDefaults() {
        $this->allowCreate = true;
        $this->allowDelete = true;
        $this->allowUpload = true;
        $this->allowDownload = true;
    }

    function setAllowAll() {
        $this->allowCreate = true;
        $this->allowCopy = true;
        $this->allowMove = true;
        $this->allowDelete = true;
        $this->allowRename = true;
        $this->allowUpload = true;
        $this->allowDownload = true;
        // allowRecursiveDelete stays false unless explicitly enabled
    }

    final function setPathMan($pathman) { $this->pathMan = $pathman; }
    final function __get_priv_pathMan() { return $this->pathMan; }

    function setAllowRecursiveDelete($val = true) {
        $this->allowRecursiveDelete = $val ? true : false;
    }
}

?>
