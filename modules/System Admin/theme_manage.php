<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "./modules/System Admin/moduleFunctions.php" ;

@session_start() ;

if (isActionAccessible($guid, $connection2, "/modules/System Admin/theme_manage.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print _("You do not have access to this action.") ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . _("Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . _(getModuleName($_GET["q"])) . "</a> > </div><div class='trailEnd'>" . _('Manage Themes') . "</div>" ;
	print "</div>" ;

	if (isset($_GET["deleteReturn"])) { $deleteReturn=$_GET["deleteReturn"] ; } else { $deleteReturn="" ; }
	$deleteReturnMessage="" ;
	$class="error" ;
	if (!($deleteReturn=="")) {
		if ($deleteReturn=="success0") {
			$deleteReturnMessage=_("Uninstall was successful. You will still need to remove the theme's files yourself.") ;
			$class="success" ;
		}
		if ($deleteReturn=="success1") {
			$deleteReturnMessage=_("Uninstall was successful.") ;
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $deleteReturnMessage;
		print "</div>" ;
	}

	if (isset($_GET["addReturn"])) { $addReturn=$_GET["addReturn"] ; } else { $addReturn="" ; }
	$addReturnMessage="" ;
	$class="error" ;
	if (!($addReturn=="")) {
		if ($addReturn=="fail0") {
			$addReturnMessage=_("Your request failed because you do not have access to this action.") ;
		}
		else if ($addReturn=="fail2") {
			$addReturnMessage=_("Your request failed due to a database error.") ;
		}
		else if ($addReturn=="fail3") {
			$addReturnMessage=_("Your request failed because your manifest file was invalid.") ;
		}
		else if ($addReturn=="fail4") {
			$addReturnMessage=_("Your request failed because a theme with the same name is already installed.") ;
		}
		else if ($addReturn=="success0") {
			$addReturnMessage=_("Your request was successful. You can now add another record if you wish.") ;
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $addReturnMessage;
		print "</div>" ;
	}

	if (isset($_GET["updateReturn"])) { $updateReturn=$_GET["updateReturn"] ; } else { $updateReturn="" ; }
	$updateReturnMessage="" ;
	$class="error" ;
	if (!($updateReturn=="")) {
		if ($updateReturn=="fail0") {
			$updateReturnMessage=_("Your request failed because you do not have access to this action.") ;
		}
		else if ($updateReturn=="fail1") {
			$updateReturnMessage=_("Your request failed because your inputs were invalid.") ;
		}
		else if ($updateReturn=="fail2") {
			$updateReturnMessage=_("Your request failed due to a database error.") ;
		}
		else if ($updateReturn=="fail4") {
			$updateReturnMessage=_("Your request failed because your inputs were invalid.") ;
		}
		else if ($updateReturn=="success0") {
			$updateReturnMessage=_("Your request was completed successfully.") ;
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $updateReturnMessage;
		print "</div>" ;
	}


	//Get themes from database, and store in an array
	try {
		$data=array();
		$sql="SELECT * FROM gibbonTheme ORDER BY name" ;
		$result=$connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) {
		print "<div class='error'>" . $e->getMessage() . "</div>" ;
	}
	$themesSQL=array() ;
	while ($row=$result->fetch()) {
		$themesSQL[$row["name"]][0]=$row ;
		$themesSQL[$row["name"]][1]="orphaned" ;
	}

	//Get list of modules in /modules directory
	$themesFS=glob($_SESSION[$guid]["absolutePath"] . "/themes/*" , GLOB_ONLYDIR);

	print "<div class='warning'>" ;
		print sprintf(_('To install a theme, upload the theme folder to %1$s on your server and then refresh this page. After refresh, the theme should appear in the list below: use the install button in the Actions column to set it up.'), "<b><u>" . $_SESSION[$guid]["absolutePath"] . "/modules/</u></b>") ;
	print "</div>" ;

	if (count($themesFS)<1) {
		print "<div class='error'>" ;
		print _("There are no records to display.") ;
		print "</div>" ;
	}
	else {
		print "<form method='post' action='" . $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/theme_manageProcess.php'>" ;
			print "<table cellspacing='0' style='width: 100%' data-responsive='true'>" ;
				print "<thead><tr class='head'>" ;
					print "<th>" ;
						print _("Name") ;
					print "</th>" ;
					print "<th>" ;
						print _("Status") ;
					print "</th>" ;
					print "<th>" ;
						print _("Description") ;
					print "</th>" ;
					print "<th>" ;
						print _("Version") ;
					print "</th>" ;
					print "<th>" ;
						print _("Author") ;
					print "</th>" ;
					print "<th>" ;
						print _("Active") ;
					print "</th>" ;
					print "<th>" ;
						print _("Action") ;
					print "</th>" ;
				print "</tr></thead>" ;

				$count=0;
				$rowNum="odd" ;
				foreach ($themesFS AS $themesFS) {
					$themeName=substr($themesFS, strlen($_SESSION[$guid]["absolutePath"] ."/themes/")) ;
					$themesSQL[$themeName][1]="present" ;

					if ($count%2==0) {
						$rowNum="even" ;
					}
					else {
						$rowNum="odd" ;
					}

					$installed=TRUE ;
					if (isset($themesSQL[$themeName][0])==FALSE) {
						$installed=FALSE ;
						$rowNum="warning" ;
					}

					$count++ ;

					//COLOR ROW BY STATUS!
					print "<tr class=$rowNum>" ;
						print "<td>" ;
							print _($themeName) ;
						print "</td>" ;
						if ($installed) {
							print "<td>" ;
								print _("Installed") ;
							print "</td>" ;
						}
						else {
							//Check for valid manifest
							$manifestOK=FALSE ;
							if (include $_SESSION[$guid]["absolutePath"] . "/themes/$themeName/manifest.php") {
								if ($name!="" AND $description!="" AND $version!="") {
									if ($name==$themeName) {
										$manifestOK=TRUE ;
									}
								}
							}
							if ($manifestOK) {
								print "<td colspan=5>" ;
									print _("Not Installed") ;
								print "</td>" ;
							}
							else {
								print "<td colspan=6>" ;
									print _("Theme Error") ;
								print "</td>" ;
							}
						}
						if ($installed) {
							print "<td>" ;
								print $themesSQL[$themeName][0]["description"] ;
							print "</td>" ;
							print "<td>" ;
								if ($themesSQL[$themeName][0]["name"]=="Default") {
									print "v" . $version ;
								}
								else {
									$themeVerison=getThemeVersion($themeName, $guid) ;
									if ($themeVerison>$themesSQL[$themeName][0]["version"]) {
										//Update database
										try {
											$data=array("version"=>$themeVerison, "gibbonThemeID"=>$themesSQL[$themeName][0]["gibbonThemeID"] );
											$sql="UPDATE gibbonTheme SET version=:version WHERE gibbonThemeID=:gibbonThemeID" ;
											$result=$connection2->prepare($sql);
											$result->execute($data);
										}
										catch(PDOException $e) {
											print "<div class='error'>" . $e->getMessage() . "</div>" ;
										}

									}
									else {
										$themeVerison=$themesSQL[$themeName][0]["version"] ;
									}


									print "v" . $themeVerison ;
								}
							print "</td>" ;
							print "<td>" ;
								if ($themesSQL[$themeName][0]["url"]!="") {
									print "<a href='" . $themesSQL[$themeName][0]["url"] . "'>" . $themesSQL[$themeName][0]["author"] . "</a>" ;
								}
								else {
									print $themesSQL[$themeName][0]["author"] ;
								}
							print "</td>" ;
							print "<td>" ;
								if ($themesSQL[$themeName][0]["active"]=="Y") {
									print "<input checked type='radio' name='gibbonThemeID' value='" . $themesSQL[$themeName][0]["gibbonThemeID"] . "'>" ;
								}
								else {
									print "<input type='radio' name='gibbonThemeID' value='" . $themesSQL[$themeName][0]["gibbonThemeID"] . "'>" ;
								}
							print "</td>" ;
							print "<td>" ;
								if ($themesSQL[$themeName][0]["name"]!="Default") {
									print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/theme_manage_uninstall.php&gibbonThemeID=" . $themesSQL[$themeName][0]["gibbonThemeID"] . "'><img title='" . _('Remove Record') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a>" ;
								}else{
									print "<img class='disabled' title='" . _('Remove Record') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/>";
								}
							print "</td>" ;
						}
						else {
							if ($manifestOK) {
								print "<td>" ;
									print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/theme_manage_installProcess.php?name=" . urlencode($themeName) . "'><img title='" . _('Install') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new.png'/></a>" ;
								print "</td>" ;
							}
						}
					print "</tr>" ;
				}

			print "</table>" ;
			?>
			<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
			<input type="submit" value="<?php print _("Submit") ; ?>">
			<?php
		print "</form>" ;
	}

	//Find and display orphaned themes
	$orphans=FALSE ;
	foreach($themesSQL AS $themeSQL) {
		if ($themeSQL[1]=="orphaned") {
			$orphans=TRUE ;
		}
	}

	if ($orphans) {
		print "<h2 style='margin-top: 40px'>" ;
			print _("Orphaned Themes") ;
		print "</h2>" ;
		print "<p>" ;
			print _("These themes are installed in the database, but are missing from within the file system.") ;
		print "</p>" ;

		print "<table cellspacing='0' style='width: 100%'>" ;
			print "<tr class='head'>" ;
				print "<th>" ;
					print _("Name") ;
				print "</th>" ;
				print "<th style='width: 50px'>" ;
					print _("Action") ;
				print "</th>" ;
			print "</tr>" ;

			$count=0;
			$rowNum="odd" ;
			foreach($themesSQL AS $themeSQL) {
				if ($themeSQL[1]=="orphaned") {
					$themeName=$themeSQL[0]["name"] ;

					if ($count%2==0) {
						$rowNum="even" ;
					}
					else {
						$rowNum="odd" ;
					}

					$count++ ;

					//COLOR ROW BY STATUS!
					print "<tr class=$rowNum>" ;
						print "<td>" ;
							print _($themeName) ;
						print "</td>" ;
						print "<td>" ;
							print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/theme_manage_uninstall.php&gibbonThemeID=" . $themesSQL[$themeName][0]["gibbonThemeID"] . "&orphaned=true'><img title='" . _('Remove Record') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a>" ;
						print "</td>" ;
					print "</tr>" ;
				}
			}
		print "</table>" ;
		?>
		<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
		<input type="submit" value="<?php print _("Submit") ; ?>">
		<?php
	}
}
?>
