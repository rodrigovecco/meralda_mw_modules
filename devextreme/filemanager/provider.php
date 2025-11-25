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

	public $autoCreateThumbnails = false;//not implemented yet

    // Recursive delete must be explicit
    public $allowRecursiveDelete = false;
	public $downloadURL;

	public $imageOnlyMode=false;

    function __construct($pathman) {
        $this->setPathMan($pathman);
    }
	function setImagesOnlyMode(){
		$this->imageOnlyMode=true;
	}

    // ============================================================
    // ROUTER
    // ============================================================
    function handle_request($action) {

        switch ($action) {
            case "list":
                return $this->cmd_list();

            case "mkdir":
                return $this->allowCreate ? $this->cmd_mkdir() : $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.create_not_allowed','No está permitido crear'));

            case "delete":
                return $this->allowDelete ? $this->cmd_delete() : $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.delete_not_allowed','No está permitido eliminar'));

            case "rename":
                return $this->allowRename ? $this->cmd_rename() : $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.rename_not_allowed','No está permitido renombrar'));

            case "move":
                return $this->allowMove ? $this->cmd_move() : $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.move_not_allowed','No está permitido mover'));

            case "copy":
                return $this->allowCopy ? $this->cmd_copy() : $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.copy_not_allowed','No está permitido copiar'));

            case "upload":
                return $this->allowUpload ? $this->cmd_upload() : $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.upload_not_allowed','No está permitido subir archivos'));

            // DevExtreme chunked upload
            case "uploadchunk":
                return $this->allowUpload ? $this->cmd_upload_chunk() : $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.upload_not_allowed','No está permitido subir archivos'));

            case "cancelUpload":
                return $this->cmd_cancel_upload();

            case "download":
                return $this->allowDownload ? $this->cmd_download() : $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.download_not_allowed','No está permitido descargar'));
        }

        return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.unknown_action',"Acción desconocida '$action'"));
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_directory','Directorio inválido'));
        }

        $items = [];
        foreach (scandir($dirAbs) as $f) {
            if ($f === "." || $f === "..") continue;

            $full = $dirAbs . "/" . $f;
            $isDir = is_dir($full);
			$ok=false;
			if($isDir){
				if($this->is_dirname_allowed($f)){
					$ok=true;
				}
			}else{
				if($this->is_filename_allowed($f)){
					$ok=true;
				}
			}
			if($ok){
				$item = [
					"name" => $f,
					"isDirectory" => $isDir,
					"size" => $isDir ? 0 : filesize($full),
					"dateModified" => date("c", filemtime($full)),
					"path" => trim(($relRaw ? trim($relRaw, "/") . "/" : "") . $f, "/")
				];
				if($info=$this->pathMan->getPathInfoByRelative($item["path"])){
					//$item["_info"]=$info;
					if($info["is_image"]){
						if($this->downloadURL){
							$item["thumbnail"]=$this->downloadURL.$item["path"];
						}
					}

				}
				
				$items[] = $item;
			}

           
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_folder_name','Nombre de carpeta inválido'));
        }
		if(!$this->is_dirname_allowed($safe)){
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.folder_name_not_allowed','Nombre de carpeta no permitido'));
		}

        $fullRel = trim($relClean . "/" . $safe, "/");

        // Your API handles creation and security
        if (!$this->pathMan->check_and_create_path($fullRel)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.cannot_create_folder','No se pudo crear la carpeta'));
        }

        return $this->ok();
    }

    // ============================================================
    // DELETE
    // ============================================================
    function cmd_delete() {
		$relRaw = $_REQUEST["path"] ?? "";
		$rel = $this->norm_rel($relRaw);

		// Prevent deleting root
		if ($rel === false) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.cannot_delete_root','No se puede eliminar el directorio raíz'));
		}

		// Clean and validate directory path
		if(!$relChecked = $this->pathMan->check_sub_path($rel)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_path','Ruta inválida'));
		}

		// Absolute path resolved to Meralda FS root
		$abs = $this->pathMan->get_sub_path($relChecked);
		if (!$abs || !file_exists($abs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.not_found',"No encontrado: $abs - check_sub_path devolvió $relChecked"));
		}

		// --------------------------------------------------
		// DELETE FILE
		// --------------------------------------------------
		if (is_file($abs)) {
			$fileName = basename($abs);

			if(!$this->is_filename_allowed($fileName)) {
                return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.file_name_not_allowed','Nombre de archivo no permitido'));
			}

			if(!unlink($abs)) {
                return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.failed_delete_file','No se pudo eliminar el archivo'));
			}

			return $this->ok();
		}

		// --------------------------------------------------
		// DELETE DIRECTORY
		// --------------------------------------------------
		if (is_dir($abs)) {
			$dirName = basename($abs);

			if(!$this->is_dirname_allowed($dirName)) {
                return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.directory_name_not_allowed','Nombre de directorio no permitido'));
			}

			// Empty directory → delete
			$isEmpty = true;
			$dh = opendir($abs);
			if ($dh) {
				while (($f = readdir($dh)) !== false) {
					if ($f !== "." && $f !== "..") {
						$isEmpty = false;
						break;
					}
				}
				closedir($dh);
			}

			if ($isEmpty) {
				if(!rmdir($abs)) {
                    return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.failed_delete_directory','No se pudo eliminar el directorio'));
				}
				return $this->ok();
			}

			// Recursive delete allowed
			if ($this->allowRecursiveDelete) {
				$spm = $this->pathMan->get_sub_path_man($relChecked);
				if ($spm && $spm->delete()) {
					return $this->ok();
				}
                return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.recursive_delete_failed','Fallo en eliminación recursiva'));
			}

            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.directory_not_empty','Directorio no vacío'));
		}

        return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.delete_failed','Fallo al eliminar'));
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_name_or_path','Nombre o ruta inválida'));
        }

		if(!$this->pathMan->check_sub_path($rel)){
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_path','Ruta inválida'));
		}

        $oldAbs = $this->pathMan->get_sub_path($rel);
        if (!$oldAbs || !file_exists($oldAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.path_not_found','Ruta no encontrada'));
        }

        $parentRelRaw = dirname(trim($relRaw, "/"));
        if ($parentRelRaw === "." || $parentRelRaw === "./") {
            $parentRelRaw = "";
        }

        $parentRel = $this->norm_rel($parentRelRaw); // false if root
        $parentAbs = $this->pathMan->get_sub_path($parentRel);

        if (!$parentAbs || !is_dir($parentAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_parent_directory','Directorio padre inválido'));
        }

        $newAbs = $parentAbs . "/" . $safe;

        if (file_exists($newAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.already_exists','Ya existe un archivo o carpeta con ese nombre'));
        }

        // If file rename => validate ext using your fileman
        if (is_file($oldAbs)) {
            $fm = $this->get_fileman_for_rel($parentRel);
            if (!$fm || !$fm->check_ext_from_filename($safe)) {
                return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_extension','Extensión inválida'));
            }
        }

        if (!rename($oldAbs, $newAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.rename_failed','Error al renombrar'));
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.missing_parameters','Faltan parámetros'));
        }

        $srcAbs = $this->pathMan->get_sub_path($fromRel);
        if (!$srcAbs || !file_exists($srcAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.source_not_found','Origen no encontrado'));
        }

        $dstAbs = $this->pathMan->get_sub_path($toRel);
        if (!$dstAbs || !is_dir($dstAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.destination_must_be_directory','El destino debe ser un directorio'));
        }

        $newAbs = $dstAbs . "/" . basename($srcAbs);
        if (file_exists($newAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.target_already_exists','El destino ya existe'));
        }

        if (!rename($srcAbs, $newAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.move_failed','Error al mover'));
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.missing_parameters','Faltan parámetros'));
        }

        $srcAbs = $this->pathMan->get_sub_path($fromRel);
        $dstAbs = $this->pathMan->get_sub_path($toRel);

        if (!$srcAbs || !file_exists($srcAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.source_not_found','Origen no encontrado'));
        }
        if (!$dstAbs || !is_dir($dstAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_destination','Destino inválido'));
        }

        $targetAbs = $dstAbs . "/" . basename($srcAbs);
        if (file_exists($targetAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.target_already_exists','El destino ya existe'));
        }

        $fm = $this->get_fileman_for_rel($toRel);
        if (!$fm) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.fileman_not_available','FileMan no disponible'));
        }

        if (is_file($srcAbs)) {
            $fname = basename($srcAbs);
            if (!$fm->check_ext_from_filename($fname)) {
                return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.copy_rejected_invalid_ext','Copia rechazada (extensión inválida)'));
            }
            if (!copy($srcAbs, $targetAbs)) {
                return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.copy_failed','Error al copiar'));
            }
            return $this->ok();
        }

        if (!$this->copy_recursive_dir_checked($srcAbs, $targetAbs, $fm)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.recursive_copy_failed','Fallo en copia recursiva (o extensión inválida)'));
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_upload_path','Ruta de subida inválida'));
        }

        if (!isset($_FILES["file"])) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.no_file_received','No se recibió el archivo'));
        }

        $sub = $this->pathMan->get_sub_path_man($rel);
        if (!$sub) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_base_directory','Directorio base inválido'));
        }

        $fm = $sub->get_file_man();
        if (!$fm) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.fileman_not_available','FileMan no disponible'));
        }

        $originalName = $_FILES["file"]["name"] ?? "";
        $safeOrig = $this->sanitize_name($originalName);
        if (!$safeOrig) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_file_name','Nombre de archivo inválido'));
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.upload_rejected','Subida rechazada'));
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.missing_parameters','Faltan parámetros'));
        }

        $safe = $this->sanitize_name($fileName);
        if (!$safe) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_file_name','Nombre de archivo inválido'));
        }

        $destRel = $this->norm_rel($destRelRaw);
        $destAbs = $this->pathMan->get_sub_path($destRel);
        if (!$destAbs || !is_dir($destAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_upload_destination','Destino de subida inválido'));
        }

        $tmpDir = $destAbs . "/.__chunk_tmp_" . $uploadId;
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        if (!isset($_FILES["chunk"])) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.missing_chunk_file','Falta el fragmento (chunk)'));
        }

        $chunkPath = $tmpDir . "/chunk_" . $chunkIndex;
        if (!move_uploaded_file($_FILES["chunk"]["tmp_name"], $chunkPath)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.failed_store_chunk','Fallo al guardar el fragmento'));
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
                return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.missing_chunk',"Falta el fragmento $i"));
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_destination','Destino inválido'));
        }

        $fm = $sub->get_file_man();
        if (!$fm) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.fileman_unavailable','FileMan no disponible'));
        }

        // Validate extension
        if (!$fm->check_ext_from_filename($safe)) {
            $this->cleanup_chunk_reldir($destRel . "/.__chunk_tmp_" . $uploadId);
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.upload_rejected_invalid_ext','Subida rechazada (extensión inválida)'));
        }

        $finalNameNoExt = $fm->get_url_secure_filename_noext($safe);
        $ext = $fm->get_ext($safe);
        $finalNameFull = $finalNameNoExt . "." . $ext;

        $finalAbs = $destAbs . "/" . $finalNameFull;

        if (!rename($finalTmpPath, $finalAbs)) {
            
			$this->cleanup_chunk_reldir($destRel . "/.__chunk_tmp_" . $uploadId);
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.failed_finalize_upload','Fallo al finalizar la subida'));
        }

        $this->cleanup_chunk_reldir($destRel . "/.__chunk_tmp_" . $uploadId);
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
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.missing_uploadid','Falta uploadId'));
        }

        $destRel = $this->norm_rel($destRelRaw);
        $destAbs = $this->pathMan->get_sub_path($destRel);
        if (!$destAbs || !is_dir($destAbs)) {
            return $this->err($this->pathMan->lng_get_msg_txt('devext.filemanager.invalid_directory','Directorio inválido'));
        }

        $this->cleanup_chunk_reldir($destRel . "/.__chunk_tmp_" . $uploadId);

        return $this->ok();
    }

    
	private function cleanup_chunk_reldir($tmpRel) {

		// tmpRel = validated relative path such as "folder/.__chunk_tmp_xxxx"
		$spm = $this->pathMan->get_sub_path_man($tmpRel);
		if ($spm) {
			// Meralda-managed recursive delete
			$spm->delete();
			return;
		}

		// No fallback, no unsafe operations.
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
			if($this->imageOnlyMode){
				$exts=$fm->get_allowed_img_exts();
            }
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
	/**
	 * Verifica si un NOMBRE DE ARCHIVO (NO directorio) es permitido.
	 * Reglas:
	 * - Debe tener extensión
	 * - No puede empezar con "."
	 * - No puede contener ".."
	 * - No puede contener caracteres ilegales
	 * - Extensión debe ser permitida por FileMan
	 */
	private function is_filename_allowed($name) {

		if (!is_string($name) || trim($name) === "") {
			return false;
		}

		$name = trim($name);

		// 1) No puede empezar con punto
		if ($name[0] === ".") {
			return false;
		}

		// 2) Path traversal
		if (strpos($name, "..") !== false) {
			return false;
		}

		// 3) Caracteres ilegales (bloqueo, NO saneo)
		if (strpbrk($name, "/\\:*?\"<>|")) {
			return false;
		}

		// 4) Debe tener extensión
		if (strpos($name, ".") === false) {
			return false; // directorio o archivo sin extensión
		}

		// 5) Extensión permitida
		$fm = $this->pathMan->get_file_man();
		if (!$fm) {
			return false; // seguridad adicional
		}

		if (!$fm->check_ext_from_filename($name)) {
			return false;
		}
		if($this->imageOnlyMode){
			if(!$fm->is_image_ext_from_filename($name)){
				return false;
			}
		}

		// 6) Nombre razonable
		if (strlen($name) > 255) {
			return false;
		}

		return true;
	}
	/**
	 * Valida si un nombre de DIRECTORIO es permitido.
	 * NO sanea ni corrige, solo bloquea.
	 *
	 * Reglas:
	 * - No puede empezar con "."
	 * - No puede contener ".."
	 * - No puede contener caracteres ilegales
	 * - No puede tener extensión (directorios no llevan punto final)
	 */
	private function is_dirname_allowed($name) {

		if (!is_string($name) || trim($name) === "") {
			return false;
		}

		$name = trim($name);

		// 1) No puede comenzar con punto
		if ($name[0] === ".") {
			return false;
		}

		// 2) Path traversal
		if (strpos($name, "..") !== false) {
			return false;
		}

		// 3) Caracteres ilegales (bloqueo, no saneo)
		if (strpbrk($name, "/\\:*?\"<>|")) {
			return false;
		}

		// 4) Directorios no deben tener extensión
		//    Si el nombre tiene un punto en medio, se permite ("carpeta.v1")
		//    pero si termina en punto → no permitido
		if (substr($name, -1) === ".") {
			return false;
		}

		// 5) nombre máximo razonable
		if (strlen($name) > 255) {
			return false;
		}

		return true;
	}


}

?>
