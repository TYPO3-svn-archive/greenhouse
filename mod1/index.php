<?php
			/***************************************************************
			*  Copyright notice
			*
			*  (c) 2010  <>
			*  All rights reserved
			*
			*  This script is part of the TYPO3 project. The TYPO3 project is
			*  free software; you can redistribute it and/or modify
			*  it under the terms of the GNU General Public License as published by
			*  the Free Software Foundation; either version 2 of the License, or
			*  (at your option) any later version.
			*
			*  The GNU General Public License can be found at
			*  http://www.gnu.org/copyleft/gpl.html.
			*
			*  This script is distributed in the hope that it will be useful,
			*  but WITHOUT ANY WARRANTY; without even the implied warranty of
			*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
			*  GNU General Public License for more details.
			*
			*  This copyright notice MUST APPEAR in all copies of the script!
			***************************************************************/
			/**
 			 * [CLASS/FUNCTION INDEX of SCRIPT]
 			 *
 			 * Hint: use extdeveval to insert/update function index above.
 			 */


			$LANG->includeLLFile('EXT:greenhouse/mod1/locallang.xml');
			require_once(PATH_t3lib . 'class.t3lib_scbase.php');
			$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
				// DEFAULT initialization of a module [END]


				error_reporting(E_ERROR | E_WARNING | E_PARSE);

/**
			 * Module 'Greenhouse' for the 'greenhouse' extension.
			 *
			 * @author	 <>
			 * @package	TYPO3
			 * @subpackage	tx_greenhouse
			 */
class  tx_greenhouse_module1 extends t3lib_SCbase {
				var $pageinfo;

				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();

					/*
					if (t3lib_div::_GP('clear_all_cache'))	{
						$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
					}
					*/
				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function1'),
							'2' => $LANG->getLL('function2'),
							'3' => $LANG->getLL('function3'),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
					
					$url = t3lib_div::getIndpEnv("TYPO3_REQUEST_URL");
					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;
				
					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->doc->getPageRenderer()->loadPrototype();
						$this->doc->getPageRenderer()->loadScriptaculous();
						$this->doc->getPageRenderer()->loadScriptaculous ('effects,dragdrop');
						$this->doc->getPageRenderer()->loadExtJS();
#						$this->doc->form='<form action="" method="post" enctype="multipart/form-data" class="mainform">';
						$this->content.="<style>
							body div.typo3-mediumDoc,.mainform,table{width:99%}
							.x-panel-body
							.x-panel-body-noheader{
							  border:none;
							}

							.x-html-editor-tb{
							  display:none;
							}

							button.x-btn-text{
								color:#333333;
							}

							.x-layout-split{
								background:#DFE0E5;
							}
							
							.container {width:480px;margin:10px;font-size:2em}
							
							#ext-gen19{margin-left:10px;margin-top:10px;}
							
							.container table {border-left:1px solid #A2AAB8;border-top:1px solid #A2AAB8;cell-spacing:0;border-spacing:0;}
							.container td, .container th{border-bottom:1px solid #A2AAB8;border-right:1px solid #A2AAB8;cell-spacing:0;border-spacing:0;padding:5px;}
						</style>";
							// JavaScript
						$this->doc->JScode = '
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';
						
						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);
						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						
						$this->content.= $this->doc->header("Greenhouse");
						switch ($_REQUEST["step"]) {
							case "wizard":
								$this->content.="<form action='".$url."&step=replace' method='post'>";
								$this->content.=file_get_contents(t3lib_extMgm::extPath("greenhouse")."templates/wizard.html");
								$this->content.="</form>";
								break;
							case "replace":
								$data = file_get_contents($_REQUEST["file"]);
								$data = str_replace("TYPO3 Website",$_REQUEST["project-title"],$data);
								$data = str_replace("__project-alias__",$_REQUEST["project-alias"],$data);
								$data = str_replace("localhost/typo3-tpl",rtrim($_REQUEST["project-domain"],"/"),$data);
								$info = "Project Title: ".$_REQUEST["project-title"]."\nProject Alias: ".$_REQUEST["project-alias"]."\nProject Domain: ".$_REQUEST["project-domain"];
								$data = str_replace("</notes>","\n".$info."</notes>",$data);
								$data = preg_replace("#<created>[^<]+</created>#","<created>". strftime('%A %e. %B %Y', time())."</created>",$data);
								$filename = str_replace(".xml"," (".$_REQUEST["project-alias"].").xml",basename($_REQUEST["file"]));
								$link = "<a href='".$BACK_PATH."../fileadmin/".$filename."'>".$filename."</a>";
								$filename = PATH_site."fileadmin/".$filename;
								if(file_put_contents($filename,$data)){
								}else{
									$flashMessage = t3lib_div::makeInstance(
									        't3lib_FlashMessage',
									        '',
									        'Couldn\'t create the new File',
									        t3lib_FlashMessage::ERROR
									);
									$this->content .= $flashMessage->render();
								}
							case "delete":
								if($_REQUEST["step"] == "delete")
									unlink($_REQUEST["file"]);
							default:
								$files = glob(PATH_site."fileadmin/*.xml");
								$metas = array();
								foreach ($files as $file) {
									$choose = "<a href='".$url."&step=wizard&file=".$file."' style='margin-left:5px'><img src='".$BACK_PATH."sysext/t3skin/icons/gfx/edit2.gif'></a>";
									$delete = "<a href='".$url."&step=delete&file=".$file."' style='margin-left:15px' onclick='return confirm(\"Are you sure?\");'><img src='".$BACK_PATH."sysext/t3skin/icons/gfx/garbage.gif'></a>";
									$xml = file_get_contents($file,false,NULL,-1,9000);
#									dump($xml);
									$xml = preg_replace("#</extensionDependencies\>.*#s","</extensionDependencies>",$xml);
									$xml.= "\n\t</header>\n</T3RecordDocument>";
									$data = t3lib_div::xml2array($xml);
#									dump($data);
									$meta = $data["header"]["meta"];
									$link = "<a href='".$BACK_PATH."../fileadmin/".basename($file)."'>".basename($file)."</a>";
									$meta["title"] = "<b>".$meta["title"]."<b/>";
									$meta["filename"] = $link;
									$meta["Actions"] = $choose.$delete;
									$meta["notes"] = "<pre>".$meta["notes"]."</pre>";
									unset($data);
									unset($meta["TYPO3_version"]);
									unset($meta["packager_name"]);
									unset($meta["packager_email"]);
									unset($meta["packager_username"]);
									$metas[$file] = $meta;
								}
#								dump($metas);
									$this->content.= '<div class="container" style="width:100%">
										<div id="ext-comp-1002" class=" x-panel x-form-label-top" style="width: auto;">
											<div class="x-panel-header x-unselectable" id="ext-gen8" style="-moz-user-select: none;">
												<span class="x-panel-header-text" id="ext-gen10">Overview
												</span>
											</div>
											<div class="x-panel-bwrap" id="ext-gen9">
												<form id="ext-gen7" method="POST" class="x-panel-body x-form" style="padding: 5px 5px 0pt; width: auto;">
													<div id="ext-comp-1003" class=" x-panel">
														<div class="x-panel-bwrap" id="ext-gen11">
															<div class="x-panel-body x-panel-body-noheader x-column-layout-ct" id="ext-gen12">
																<div class="x-column-inner" id="ext-gen16">
																	<div id="ext-comp-1004" class=" x-panel x-form-label-top x-column">
																		<div class="x-panel-bwrap" id="ext-gen18">
																			<div class="x-panel-body x-panel-body-noheader" id="ext-gen19">
																				<div class="x-form-item ">
																					'.$this->renderTable($metas).'
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>';
								break;
						}
					}
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{
					$this->content.=$this->doc->endPage();
					echo $this->content;
				}
				
				function renderTable($rows){
					$header = array_keys(current($rows));
					$content= "<table>";
					$content.= "<tr>";
					foreach ($header as $key => $value) {
						$content.= "<th>".ucfirst(str_replace("_"," ",$value))."</th>";
					}
					$content.= "</tr>";
					foreach ($rows as $row) {
						$content.= "<tr>";
						foreach ($row as $cell) {
							$content.= "<td>".$cell."</td>";
						}
						$content.= "</tr>";
					}
					return $content;
				}
		}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/greenhouse/mod1/index.php'])	{
				include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/greenhouse/mod1/index.php']);
			}
			



			// Make instance:
			$SOBE = t3lib_div::makeInstance('tx_greenhouse_module1');
			$SOBE->init();
			
			// Include files?
			foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);
			
			$SOBE->main();
			$SOBE->printContent();
			
			?>