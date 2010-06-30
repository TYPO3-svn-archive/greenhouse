<?php

class ux_tx_impexp extends tx_impexp{
	/**
	 * This adds all files in relations.
	 * Call this method AFTER adding all records including relations.
	 *
	 * @return	void
	 * @see export_addDBRelations()
	 */
	function export_addFilesFromRelations()	{
		$this->addFilesFromPaths();
			// Traverse all "rels" registered for "records"
		if (is_array($this->dat['records']))	{
			reset($this->dat['records']);
			while(list($k)=each($this->dat['records']))	{
				if (is_array($this->dat['records'][$k]['rels']))	{
					reset($this->dat['records'][$k]['rels']);
					while(list($fieldname,$vR)=each($this->dat['records'][$k]['rels']))	{

							// For all file type relations:
						if ($vR['type']=='file')	{
							foreach($vR['newValueFiles'] as $key => $fI)	{
								$this->export_addFile($fI, $k, $fieldname);
									// Remove the absolute reference to the file so it doesn't expose absolute paths from source server:
								unset($this->dat['records'][$k]['rels'][$fieldname]['newValueFiles'][$key]['ID_absFile']);
							}
						}

							// For all flex type relations:
						if ($vR['type']=='flex')	{
							if (is_array($vR['flexFormRels']['file']))	{
								foreach($vR['flexFormRels']['file'] as $key => $subList)	{
									foreach($subList as $subKey => $fI)	{
										$this->export_addFile($fI, $k, $fieldname);
											// Remove the absolute reference to the file so it doesn't expose absolute paths from source server:
										unset($this->dat['records'][$k]['rels'][$fieldname]['flexFormRels']['file'][$key][$subKey]['ID_absFile']);
									}
								}
							}

								// DB oriented soft references in flex form fields:
							if (is_array($vR['flexFormRels']['softrefs']))	{
								foreach($vR['flexFormRels']['softrefs'] as $key => $subList)	{
									foreach($subList['keys'] as $spKey => $elements)	{
										foreach($elements as $subKey => $el)	{
											if ($el['subst']['type'] === 'file' && $this->includeSoftref($el['subst']['tokenID']))	{

													// Create abs path and ID for file:
												$ID_absFile = t3lib_div::getFileAbsFileName(PATH_site.$el['subst']['relFileName']);
												$ID = md5($ID_absFile);

												if ($ID_absFile)	{
													if (!$this->dat['files'][$ID])	{
														$fI = array(
															'filename' => basename($ID_absFile),
															'ID_absFile' => $ID_absFile,
															'ID' => $ID,
															'relFileName' => $el['subst']['relFileName']
														);
														$this->export_addFile($fI, '_SOFTREF_');
													}
													$this->dat['records'][$k]['rels'][$fieldname]['flexFormRels']['softrefs'][$key]['keys'][$spKey][$subKey]['file_ID'] = $ID;
												}
											}
										}
									}
								}
							}
						}

							// In any case, if there are soft refs:
						if (is_array($vR['softrefs']['keys']))	{
							foreach($vR['softrefs']['keys'] as $spKey => $elements)	{
								foreach($elements as $subKey => $el)	{
									if ($el['subst']['type'] === 'file' && $this->includeSoftref($el['subst']['tokenID']))	{

											// Create abs path and ID for file:
										$ID_absFile = t3lib_div::getFileAbsFileName(PATH_site.$el['subst']['relFileName']);
										$ID = md5($ID_absFile);

										if ($ID_absFile)	{
											if (!$this->dat['files'][$ID])	{
												$fI = array(
													'filename' => basename($ID_absFile),
													'ID_absFile' => $ID_absFile,
													'ID' => $ID,
													'relFileName' => $el['subst']['relFileName']
												);
												$this->export_addFile($fI, '_SOFTREF_');
											}
											$this->dat['records'][$k]['rels'][$fieldname]['softrefs']['keys'][$spKey][$subKey]['file_ID'] = $ID;
										}
									}
								}
							}
						}
					}
				}
			}
		} else $this->error('There were no records available.');
	}
	
	function addFilesFromPaths(){
        $this->x = 1;
        foreach($this->dat['records'] as $key => $value){
            if(substr($key,0,13) != "sys_template:") continue;
            $settings = t3lib_div::_GP('tx_impexp');
            $patterns = trim($settings["files"]);
			$parts = explode(":",$key);
			$table = $parts[0];
			$uid = $parts[1];

			if(empty($patterns)) break;
			$this->files = array();
            foreach(explode("\n",$patterns) as $pattern){
                $paths = glob(PATH_site.$pattern);
				if(!is_array($paths) || count($paths) < 1)
					$paths = array(PATH_site.$pattern);
                foreach($paths as $path){
                    $path = rtrim($path,"\\/\n\r");
					if(is_dir($path)){
						$dir_iterator = new RecursiveDirectoryIterator($path);
						$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
						foreach ($iterator as $file) {
							if(preg_match_all("/(.svn|.DS_Store|\._)/",$file,$matches) > 0) continue;
							if(!is_dir(strval($file))){
                                $file = str_replace("\\","/",strval($file));
								$this->addFileFrom($file,$table,$uid);
                            }
                        }
                    }else{
                        $file = $path;
						$this->addFileFrom($file,$table,$uid);
                    }
                }
            }
			$this->dat["records"][$key]["data"]["description"] = implode("\n",array_keys($this->files));
			$this->dat["records"][$key]["rels"]["description"]["softrefs"]["tokenizedContent"] = implode("\n",$this->files);
			if(!empty($this->dat["records"][$key]["rels"]["resources"]["newValueFiles"])){
				$this->dat["records"][$key]["rels"]["resources"]["type"] = "file";
			}
            break;
        }
	}
	
	function addFileFrom($file,$table,$uid){
		$ID = md5($file);
		$file = str_replace(PATH_site,"",$file);
		$absFile = PATH_site.$file;
		$key = $table.":".$uid;
		$content = file_get_contents($absFile);
		
		$this->dat["header"]["files"][$ID] = array(
			"filename" => basename($file),
			"filesize" => strlen($content),
			"filemtime" => filemtime($absFile),
			"relFileRef" => $file,
			"relFileName" => $file,
			"record_ref" => "_SOFTREF_/"
		);
		
		$this->dat["files"][$ID] = $this->dat["header"]["files"][$ID];
		$this->dat["files"][$ID]["content"] = $content;
		$this->dat["files"][$ID]["content_md5"] = md5($content);

		$this->dat["header"]["records"][$table][$uid]["softrefs"]["description:substitute:".$this->x] = array(
			"field" => "description",
			"spKey" => "substitute",
			"matchString" => $file,
			"file_ID" => $ID,
			"subst" => array(
				"type" => "file",
				"relFileName" => $file,
				"tokenID" => $ID,
				"tokenValue" => $file
			)
	    );
		
		$this->dat["records"][$key]["rels"]["resources"]["newValueFiles"][] = array(
			"filename" => basename($file),
			"ID" => $ID,
			"ID_absFile" => $absFile
		);
		
		$this->dat["records"][$key]["rels"]["description"]["softrefs"]["keys"]["substitute"][$this->x] = array(
			"matchString" => $file,
			"file_ID" => $ID,
			"subst" => array(
				"type" => $file,
				"relFileName" => $file,
				"tokenID" => $ID,
				"tokenValue" => $file
			)
		);
		
		unset($content);
		$this->x++;
		$this->files[$file] = "{softref:".md5($file)."}";
	}
}