<?php
//rvh 2023-02-26 v 2
/** @property-read mwmod_mw_ap_paths $pathman  */
class  mwmod_mw_ap_paths_subpath extends mw_apsubbaseobj{
	private $pathman;//mwmod_mw_ap_paths
	private $dir;//sub dir de $pathman
	private $_is_ok;
	/**
	* Constructor. Initializes the subpath manager with a directory and parent path manager.
	* @param string $dir Subdirectory relative to parent
	* @param mwmod_mw_ap_paths $pathman Parent path manager
	*/
	function __construct($dir,$pathman){
		$this->init($dir,$pathman);
		
	}
	/**
	 * Entrega un archivo o recurso al navegador según su tipo.
	 * Usa getPathInfoByRelative() para clasificar automáticamente.
	 * 
	 * - Imagen → inline
	 * - PDF → inline
	 * - Texto → inline (UTF-8)
	 * - Binario → descarga
	 * - Directorio → no hace nada (caller debe manejarlo)
	 * 
	 * @param string $relpath Ruta relativa (ej. "folder/sub/file.png")
	 * @param string|false $fakename Nombre de descarga o presentación
	 * @return bool True si hubo output, false si no existe o es dir
	 */
	function outputByRelPath($relpath, $fakename = false){
		$info = $this->getPathInfoByRelative($relpath);

		// No existe → 404
		if(!$info["exists"]){
			header("HTTP/1.1 404 Not Found");
			echo "File not found";
			return false;
		}

		// Directorio → caller decide, no hacemos output
		if($info["is_dir"]){
			return false;
		}

		$abs  = $info["abs"];
		$mime = $info["mime"];
		$ext  = $info["extension"];
		$fname = $fakename ?: $info["filename"];

		// Limpiar buffers antes de enviar headers
		if(ob_get_level()){
			@ob_end_clean();
		}

		//
		// 🔥 IMÁGENES → inline
		//
		if($info["is_image"]){
			header("Content-Type: {$mime}");
			header("Content-Disposition: inline; filename=\"$fname\"");
			header("Content-Length: " . filesize($abs));
			readfile($abs);
			return true;
		}

		//
		// 🔥 PDF → inline
		//
		if($info["is_pdf"]){
			header("Content-Type: application/pdf");
			header("Content-Disposition: inline; filename=\"$fname\"");
			header("Content-Length: " . filesize($abs));
			readfile($abs);
			return true;
		}

		//
		// 🔥 TEXTO → inline (utf-8)
		// Esto podría ajustarse según políticas
		//
		if($info["is_text"]){
			header("Content-Type: {$mime}; charset=utf-8");
			header("Content-Disposition: inline; filename=\"$fname\"");
			header("Content-Length: " . filesize($abs));
			readfile($abs);
			return true;
		}

		//
		// 🔥 BINARIOS → descarga normal
		//
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$fname\"");
		header("Content-Length: " . filesize($abs));
		readfile($abs);

		return true;
	}
	/**
	 * Retorna información completa del path relativo solicitado.
	 * Incluye:
	 * - existencia
	 * - si es archivo o directorio
	 * - ruta abs / rel
	 * - filename / subpath
	 * - extensión
	 * - mime real (si es archivo)
	 * - tipo general (image/pdf/text/video/audio/bin/dir/unknown)
	 * - banderas booleanas
	 */
	function getPathInfoByRelative($relpath){
		$relpath = trim(str_replace("\\", "/", $relpath), "/");

		// Inicializar respuesta base
		$info = [
			"exists"     => false,
			"is_file"    => false,
			"is_dir"     => false,

			"abs"        => false,
			"rel"        => $relpath,

			"filename"   => false,
			"subpath"    => false,

			"extension"  => "",
			"mime"       => null,
			"type"       => "unknown",

			"is_image"   => false,
			"is_pdf"     => false,
			"is_text"    => false,
			"is_binary"  => false,
		];

		if(!$relpath){
			return $info;
		}

		// Obtener filename y subpath usando tu método actual
		$filename = false;
		$subpath = false;

		$abs = $this->getFileOrDirAbsByRelPath($relpath, $filename, $subpath);
		if(!$abs){
			return $info;
		}

		$info["abs"]      = $abs;
		$info["filename"] = $filename;
		$info["subpath"]  = $subpath;

		//
		// 1. Detectar existencia y tipo
		//
		if(is_dir($abs)){
			$info["exists"]  = true;
			$info["is_dir"]  = true;
			$info["type"]    = "dir";
			return $info;
		}

		if(!is_file($abs)){
			return $info;
		}

		$info["exists"]  = true;
		$info["is_file"] = true;

		//
		// 2. Obtener extensión (normalizada)
		//
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$info["extension"] = $ext ?: "";

		//
		// 3. Detectar MIME real con finfo
		//
		$mime = null;
		if(function_exists("finfo_open")){
			$f = finfo_open(FILEINFO_MIME_TYPE);
			if($f){
				$mime = finfo_file($f, $abs);
				finfo_close($f);
			}
		}
		// fallback débil si no hay finfo
		if(!$mime && $ext){
			$mime = $this->guessMimeFromExtension($ext);
		}
		if(!$mime){
			$mime = "application/octet-stream";
		}

		$info["mime"] = $mime;

		//
		// 4. Clasificación por tipo
		//
		if(strpos($mime, "image/") === 0){
			$info["type"]     = "image";
			$info["is_image"] = true;
			return $info;
		}

		if($mime === "application/pdf"){
			$info["type"]   = "pdf";
			$info["is_pdf"] = true;
			return $info;
		}

		if(strpos($mime, "text/") === 0){
			$info["type"]   = "text";
			$info["is_text"] = true;
			return $info;
		}

		if(strpos($mime, "video/") === 0){
			$info["type"] = "video";
			return $info;
		}

		if(strpos($mime, "audio/") === 0){
			$info["type"] = "audio";
			return $info;
		}

		//
		// 5. Resto → binario
		//
		$info["type"]      = "bin";
		$info["is_binary"] = true;

		return $info;
	}

	/**
	 * Fallback para MIME cuando finfo no disponible.
	 */
	function guessMimeFromExtension($ext){
		static $map = [
			"jpg" => "image/jpeg",
			"jpeg"=> "image/jpeg",
			"png" => "image/png",
			"gif" => "image/gif",
			"webp"=> "image/webp",
			"svg" => "image/svg+xml",

			"txt" => "text/plain",
			"md"  => "text/markdown",
			"json"=> "application/json",
			"xml" => "text/xml",
			"html"=> "text/html",
			"css" => "text/css",
			"js"  => "application/javascript",

			"pdf" => "application/pdf",

			"mp3" => "audio/mpeg",
			"wav" => "audio/wav",

			"mp4" => "video/mp4",
			"mov" => "video/quicktime",

			"zip" => "application/zip",
			"gz"  => "application/gzip",
			"tar" => "application/x-tar",
			"rar" => "application/x-rar-compressed",
		];

		return $map[$ext] ?? "application/octet-stream";
	}
	
	function getFileOrDirAbsByRelPath($relpath,&$filename=false,&$subpath=false){
		if(!$relpath){
			return false;	
		}
		if(!is_string($relpath)){
			return false;	
		}
		$relpath=str_replace("\\","/",$relpath);
		$relpath=trim($relpath,"/");
		if(strpos($relpath,"/")===false){
			$filename=$relpath;
			$subpath=false;
			return $this->get_full_path_filename($relpath);	
		}
		$filename=basename($relpath);
		$subpath=dirname($relpath);
		if($subpath=="."||$subpath==".."){
			$subpath=false;	
		}
		return $this->get_full_path_filename($filename,$subpath);


		/*
		$parts=explode("/",$relpath);
		return $parts[count($parts)-1];
		*/
		
	}
	/**
	* Opens a new file for writing. Does not overwrite if file exists.
	* @param string $filename
	* @param string|false $subpath
	* @param string $mode File open mode (default 'a')
	* @return resource|false File handle or false on failure
	*/
	function openNewFile($filename,$subpath=false,$mode="a"){
		//does not delete, if exists return false
		//creates path
		if(!$filename=$this->checkFileName($filename)){
			return false;	
		}
		if($existing=$this->fileOrDir_exists($filename,$subpath)){
			return false;	
		}
		if(!$p=$this->check_and_create_path($subpath)){
			return false;	
		}
		$full=$p."/".$filename;
		$myFile= fopen($full,$mode); // Open the file for writing
		return $myFile;
		
		
			
	}
	function downloadFileByRelPath($fileRelPath,$fakename=false){
		$filename=false;
		$subpath=false;
		if(!$f=$this->getFileOrDirAbsByRelPath($fileRelPath,$filename,$subpath)){
			return false;	
		}
		return $this->download_file($filename,$subpath,$fakename);
	}
	/**
	 * Stream a file to the client for download.
	 * Sends appropriate headers and reads the file in chunks to avoid high memory usage.
	 * @param string $filename Filename (or path relative to subpath)
	 * @param string|false $subpath Optional subpath to resolve the file location
	 * @param string|false $fakename Optional download filename presented to client
	 * @return string|false Returns the full file path on success, or false on failure
	 */
	function download_file($filename,$subpath=false,$fakename=false){
		if(!$f=$this->file_exists($filename,$subpath)){
			return false;	
		}
		if(!$fakename){
			$fakename=basename($filename);	
		}
		ob_end_clean();
		$download_rate = 20.5;
		header('Cache-control: private');
		header('Content-Type: application/octet-stream');
		header('Content-Length: '.filesize($f));
		header('Content-Disposition: filename='.$fakename);
		flush();
		$file = fopen($f, "r");
		while(!feof($file)){
			print fread($file, round($download_rate * 1024));
			flush();
			//sleep(1);
		}
		fclose($file);
		return $f;
		
	
	}
	/**
	 * Return the real (absolute) file path if the file exists, otherwise false.
	 * @param string $filename
	 * @param string|false $subpath
	 * @return string|false Absolute file path or false
	 */
	function get_file_path_if_exists($filename,$subpath=false){
		if($full=$this->get_full_path_filename($filename,$subpath)){
			if(file_exists($full)){
				if(is_file($full)){
					return realpath($full);
				}
			}
		}
	}
	/**
	 * Build and return the full filesystem path for a filename inside this subpath.
	 * Validates the filename and resolves the subpath.
	 * @param string $filename
	 * @param string|false $subpath
	 * @return string|false Full path string or false on invalid filename/subpath
	 */
	function get_full_path_filename($filename,$subpath=false){
		if(!$filename=$this->checkFileName($filename)){
			return false;	
		}
		if(!$p=$this->get_sub_path($subpath)){
			return false;
		}
		return $p."/".$filename;
	}
	/**
	 * Validate a filename to ensure it is safe to use in this subpath.
	 * Disallows empty names and directory separators.
	 * @param string $filename
	 * @return string|false Sanitized filename or false if invalid
	 */
	function checkFileName($filename){
		if(!$filename=trim($filename)){
			return false;	
		}
		
		if(strpos($filename,"/")!==false){
			return false;	
		}
		if(strpos($filename,"\\")!==false){
			return false;	
		}
		return $filename;

	}
	/**
	 * Check whether a file or directory exists under the given subpath.
	 * @param string $filename File or directory name
	 * @param string|false $subpath Optional subpath to check in
	 * @return string|false Full path if exists, otherwise false
	 */
	function fileOrDir_exists($filename,$subpath=false){
		if(!$p=$this->get_sub_path($subpath)){
			return false;	
		}
		if(file_exists($p."/".$filename)){
			return $p."/".$filename;	
		}
	}
	/**
	 * Validate a subpath string using the parent path manager.
	 * Returns the normalized subpath or false if invalid/empty.
	 * @param string $subpath
	 * @return string|false Normalized subpath or false
	 */
	function check_sub_path($subpath){
		if(!$subpath){
			return false;	
		}
		if($p=$this->pathman->check_sub_path($subpath)){
			return $p;
		}


	}
	/**
	 * Check if a file exists (and is a regular file) inside the resolved subpath.
	 * @param string $filename
	 * @param string|false $subpath
	 * @return string|false Full path if file exists, otherwise false
	 */
	function file_exists($filename,$subpath=false){
		$filename=basename($filename);
		if(!$p=$this->get_sub_path($subpath)){
			return false;	
		}
		if(is_file($p."/".$filename)){
			if(file_exists($p."/".$filename)){
				
				return $p."/".$filename;	
			}
		}
	}
	/**
	 * Delete the path represented by this subpath using the file manager.
	 * @return bool True on success, false on failure
	 */
	function delete(){
		if(!$path=$this->get_path()){
			return false;	
		}
		if(!$fm=$this->get_file_man()){
			return false;	
		}
		return $fm->delete_path($path);
	}

	
	/**
	 * Ensure the resolved subpath exists on disk; create it if necessary.
	 * Uses the file manager to create directories when required.
	 * @param string|false $subpath
	 * @return string|false Path that was checked/created, or false on failure
	 */
	function check_and_create_path($subpath=false){
		if(!$p=$this->get_sub_path($subpath)){
			return false;	
		}
		if($fm=$this->get_file_man()){
			return $fm->check_and_create_path($p);	
		}
		return false;
	}
	/**
	 * Return the helper file manager from the parent path manager.
	 * @return mwmod_mw_helper_fileman
	 */
	function get_file_man(){
		return $this->pathman->get_file_man();	
	}
	
	/**
	 * Build and return the relative subpath for this subpath manager.
	 * If `$subpath` is provided it is validated and appended to this object's relative dir.
	 * @param string|false $subpath
	 * @return string|false Relative subpath string or false
	 */
	function get_rel_sub_path($subpath=false){
		if(!$dir=$this->get_rel_dir()){
			return false;	
		}
		if($subpath===false){
			return $dir;	
		}
		if($subpath=$this->pathman->check_sub_path($subpath)){
			return $dir."/".$subpath;
		}
		return false;	
	}
	/**
	 * Resolve and return an absolute filesystem path for a relative subpath.
	 * Delegates to the parent `pathman` after composing the relative subpath.
	 * @param string|false $subpath
	 * @return string|false Absolute path or false
	 */
	function get_sub_path($subpath=false){
		if($p=$this->get_rel_sub_path($subpath)){
			return $this->pathman->get_sub_path($p);	
		}
		return false;
		
	}

	/**
	 * Alias for `get_sub_path()`.
	 * @return string|false Absolute path or false
	 */
	function get_path(){
		return $this->get_sub_path();
	}
	/**
	 * Return debug information about this subpath manager.
	 * @return array Associative array with debug keys (rel dir, subpaths, site-root paths, mode)
	 */
	function get_debug_data(){
		$r=array(
			"get_rel_dir"=>$this->get_rel_dir(),
			"get_rel_sub_path"=>$this->get_rel_sub_path(),
			"get_rel_sub_path_test_hello"=>$this->get_rel_sub_path("test/hello"),
			"get_sub_path"=>$this->get_sub_path(),
			"get_sub_path_test_hello"=>$this->get_sub_path("test/hello"),
			"getSiteRootRelPath"=>$this->getSiteRootRelPath(),
			"getSiteRootRelPath_test_hello"=>$this->getSiteRootRelPath("test/hello"),
			
			
			"mode"=>$this->pathman->mode
			
		);
		return $r;
	}
	/**
	 * Return a site-root relative path for this subpath, optionally appending another subpath.
	 * @param string|false $subpath
	 * @return string|false Site-root relative path or false
	 */
	function getSiteRootRelPath($subpath=false){
		if(!$sub=$this->get_rel_sub_path($subpath)){
			return false;	
		}
		return $this->pathman->getSiteRootRelSubPath($sub);
	
	}

	/**
	 * Return a `mwmod_mw_ap_paths_subpath` manager for a nested subpath.
	 * @param string $subpath
	 * @return mwmod_mw_ap_paths_subpath|false Subpath manager or false if invalid
	 */
	function get_sub_path_man($subpath){
		if(!$subpath){
			return false;	
		}
		if(!$sub=$this->get_rel_sub_path($subpath)){
			return false;	
		}
		return $this->pathman->get_sub_path_man($sub);
	}
	
	/**
	 * Return this object's relative directory string if valid.
	 * @return string|false Relative dir or false if invalid
	 */
	final function get_rel_dir(){
		if(!$this->check()){
			return false;	
		}
		return $this->dir;
		
	}
	/**
	 * Initialize this subpath object with its directory and parent path manager.
	 * @param string $dir Relative directory to manage
	 * @param mwmod_mw_ap_paths $pathman Parent path manager instance
	 * @return void
	 */
	final function init($dir,$pathman){
		$ap=$pathman->mainap;
		$this->set_mainap($ap);
		$this->pathman=$pathman;	
		$this->dir=$dir;	
	}
	/**
	 * Validate and normalize this object's directory using the parent `pathman`.
	 * Caches the result in `$_is_ok` to avoid repeated checks.
	 * @return bool True if directory is valid, false otherwise
	 */
	final function check(){
		if(isset($this->_is_ok)){
			return $this->_is_ok;	
		}
		$this->_is_ok=false;
		if($p=$this->pathman->check_sub_path($this->dir)){
			$this->dir=$p;
			$this->_is_ok=true;	
		}
		return $this->_is_ok;

	}
	/**
	 * Internal accessor for the private `dir` property.
	 * @return string
	 */
	final function __get_priv_dir(){
		return $this->dir; 	
	}
	/**
	 * Internal accessor for the private `pathman` property.
	 * @return mwmod_mw_ap_paths
	 */
	final function __get_priv_pathman(){
		return $this->pathman; 	
	}
	
}

?>