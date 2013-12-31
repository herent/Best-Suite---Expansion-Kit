<?php  
defined('C5_EXECUTE') or die("Access Denied.");
$ih = Loader::helper('concrete/interface');
$form = Loader::helper('form');
$page_title = $_GET['page_title'];
$composerToken = Loader::helper('validation/token')->generate('composer');
$ct = CollectionType::getByHandle($ctHandle);
$ctID = $ct->getCollectionTypeID();
$ctName = $ct->getCollectionTypeName();
$bscHelper = Loader::helper('best_suite_core', 'dashboard_page_managers');
$customCollectionOptions = $bscHelper->getCollectionTypeDetails($ctID);

if (!isset($sitemap_select_mode)) {
	if (isset($_GET['sitemap_select_mode'])) {
		$sitemap_select_mode = Loader::helper('text')->entities($_GET['sitemap_select_mode']);
	}
}

if (!isset($sitemap_select_callback)) {
	if (isset($_GET['sitemap_select_callback'])) {
		$sitemap_select_callback = Loader::helper('text')->entities($_GET['sitemap_select_callback']);
	}
}

if (isset($_GET['searchInstance'])) {
	$searchInstance = Loader::helper('text')->entities($_GET['searchInstance']);
}

if ($customCollectionOptions && $customCollectionOptions->hasCustomEditPage){
	$pmEdit = Page::getByID($customCollectionOptions->customEditPageCID);
} else {
	$pmEdit = Page::getByPath('/dashboard/composer/write-pm');
}
$advancedEnabled = PERMISSIONS_MODEL === "advanced" ? 1 : 0;
?> 
<input name="ctID" type="hidden" value="<?php  echo  $ctID;?>">
<style type="text/css">
	#ccm-<?php   echo $searchInstance?>-search-results table.ccm-results-list td.page-actions {
		white-space: nowrap;
	}
	#ccm-<?php   echo $searchInstance?>-search-results table.ccm-results-list td.page-actions .btn-mini {
		margin-bottom: 5px;
	}
	#ccm-<?php   echo $searchInstance?>-search-results table.ccm-results-list th.checkboxes-column {
		width: 25px !important;
		max-width: 25px !important;
	}
	#ccm-<?php   echo $searchInstance?>-search-results table.ccm-results-list tr.dpm-list-record td {
		padding: 8px !important;
	}
	.ccm-ui .tooltip.in {
		opacity:1;
		filter:alpha(opacity=100);
	}
	
</style>
<div id="ccm-<?php   echo $searchInstance?>-search-results">
	<?php    if (!$searchDialog) { ?>
	<div class="ccm-pane-body">
	<?php   } ?>
		<div id="ccm-list-wrapper">
			<a name="ccm-<?php   echo $searchInstance?>-list-wrapper-anchor"></a>
			<div style="margin-bottom: 20px">
				<?php    $form = Loader::helper('form'); ?>

				<select id="ccm-<?php   echo $searchInstance?>-list-multiple-operations" class="span3" disabled>
					<option value="">** <?php   echo t('With Selected')?></option>
					<option value="properties"><?php   echo t('Edit Properties')?></option>
					<option value="permissions"><?php   echo t('Change Permissions')?></option>
					<option value="delete"><?php   echo t('Delete')?></option>
				</select>	
				<?php  
				if ($customCollectionOptions && $customCollectionOptions->hasCustomEditPage) {
					$writePagePath = Page::getByID($customCollectionOptions->customEditPageCID)->getCollectionPath();
					print $ih->button(
						t('Add %s', $ctName) . "&nbsp;<i class='icon-plus icon-white'></i>", 
						View::url($writePagePath, $ctID, $cID), 
						'right', 'btn-success'); 
					/* If we're doing a hidden page type, then check if we should link to edit the master */
					if ($ct->isCollectionTypeInternal() && TaskPermission::getByHandle('access_page_defaults')->can()){
						print $ih->button(
							t('Edit Defaults') . "&nbsp;<i class='icon-edit icon-white'></i>", 
							View::url('/dashboard/pages/types?cID=' . $ct->getMasterTemplate()->getCollectionID() . '&task=load_master'), 
							'right', 'btn-primary', array("style" => "margin-right:10px;"));
					}
				} else {
					print $ih->button(
						t('Add %s', $ctName) . "&nbsp;<i class='icon-plus icon-white'></i>", 
						View::url('/dashboard/composer/write-pm', $ctID, $cID), 
						'right', 'btn-success'); 
				}
?>
			</div>
			<?php  
			$txt = Loader::helper('text');
			$url = Loader::helper('concrete/urls');
			$soargs = array();
			$soargs['searchInstance'] = $searchInstance;
			$soargs['sitemap_select_mode'] = $sitemap_select_mode;
			$soargs['sitemap_select_callback'] = $sitemap_select_callback;
			$soargs['searchDialog'] = $searchDialog;
			$soargs['cID'] = $cID;
			if ($customCollectionOptions && $customCollectionOptions->hasCustomSearchInterface) {
				$searchFolderName = $customCollectionOptions->customSearchInterfaceFolderName;
				$pkgHandle = Package::getByID($customCollectionOptions->pkgID)->getPackageHandle();
				$bu = $url->getToolsURL(
					$searchFolderName . '/search_results', 
					$pkgHandle) . "?cID=" . $cID;
			} else {
				$bu = $url->getToolsURL(
					'dashboard_page_managers/search_results', 
					'dashboard_page_managers') . "?cID=" . $cID;
			}
			if (count($pages) > 0) {
				$nh = Loader::helper('navigation');
				$deleteURL = Page::getByID($cID)->getCollectionPath();
				$dpUrl = $url->getToolsURL('dashboard_page_managers/edit_collection_popup', 'dashboard_page_managers');
				$pvUrl = $url->getToolsURL('dashboard_page_managers/versions', 'dashboard_page_managers');
				?>	
				
				<table border="0" 
					  cellspacing="0" 
					  cellpadding="0"  
					  id="ccm-<?php  echo  $searchInstance;?>-list"
					  class="ccm-results-list">
					<tr class="ccm-results-list-header">
						<?php    if (!$searchDialog) { ?><th class="checkboxes-column"><input id="ccm-<?php   echo $searchInstance?>-list-cb-all" type="checkbox" /></th><?php    } ?>
						<?php    if ($pl->isIndexedSearch()) { ?>
							<th class="<?php   echo $pl->getSearchResultsClass('cIndexScore')?>">
								<a href="<?php   echo $pl->getSortByURL('cIndexScore', 'desc', $bu, $soargs)?>">
									<?php   echo t('Score')?>
								</a>
							</th>
						<?php    } ?>
						<?php    foreach($columns->getColumns() as $col) { ?>
							<?php    if ($col->isColumnSortable()) { ?>
								<th class="<?php   echo $pl->getSearchResultsClass($col->getColumnKey())?>">
									<a href="<?php   echo $pl->getSortByURL($col->getColumnKey(), 
										$col->getColumnDefaultSortDirection(), $bu, $soargs)?>">
											<?php   echo $col->getColumnName()?>
									</a>
								</th>
							<?php    } else { ?>
								<th><?php   echo $col->getColumnName()?></th>
							<?php    } ?>
						<?php    } ?>
						<th>
							<?php   echo t("Status");?>
						</th>
						<th>
						</th>
					</tr>
					<?php  
					foreach ($pages as $page) {
						$cp = new Permissions($page);
						
							$_cID = $page->getCollectionID();
							$activeVersionID = $page->getVersionID();
							$recentVersionID = Page::getByID($page->getCollectionID(), "RECENT")->getVersionID();
							if ($customCollectionOptions && $customCollectionOptions->hasCustomEditPage) {
								$writePagePath = Page::getByID($customCollectionOptions->customEditPageCID)->getCollectionPath();
								$editAction = View::url($writePagePath, 'edit', $_cID, 0, $cID);
							} else {
								$editAction = View::url('/dashboard/composer/write-pm', 'edit', $_cID, 0, $cID);
							}
							if ($page->isCheckedOut()) {
								if (!$page->isCheckedOutByMe()) {
									$cantCheckOut = true;
								}
							}

							if (!isset($striped) || $striped == 'dpm-list-record-alt') {
								$striped = '';
							} else if ($striped == '') {
								$striped = 'dpm-list-record-alt';
							}
							?>

							<tr 
								class="dpm-list-record <?php   echo $striped ?>">
								<td class="ccm-<?php   echo $searchInstance?>-list-cb" 
								    style="vertical-align: middle !important">
									<input type="checkbox" value="<?php   echo $page->getCollectionID()?>" />
								</td>
								<?php   if ($pl->isIndexedSearch()){?>
								<td>
									<?php  echo  $page->getPageIndexScore();?>
								</td>
								<?php   } ?>
								<?php    foreach($columns->getColumns() as $col) { ?>
									<?php    if ($col->getColumnKey() == 'cvName') { ?>
										<td class="ccm-page-list-name">
											<a href="<?php echo  $nh->getLinkToCollection($page, true);?>" title="<?php  echo t("Visit Page");?>">
											<?php   echo $txt->highlightSearch($page->getCollectionName(), $_REQUEST['cvName'])?>
											</a>
										</td>		
									<?php    } else { ?>
										<td><?php   echo $col->getColumnValue($page)?></td>
									<?php    } ?>
								<?php    } ?>
								<td>
									<?php   
									if ($page->isActive()){
										if ($page->getVersionObject()->isApproved()){
											echo t("Published");
										} else {
											echo t("Revision Pending");
										}
									} else {
										echo t("Draft");
									}
									?>

								</td>
								<td class="page-actions">
									<a href="<?php echo  $nh->getLinkToCollection($page, true);?>"
									   class="btn btn-success btn-mini ccm-button-v2-right"
									   style="margin-left: 10px;"
									   title="<?php  echo t("Visit Page");?>">
										<i class="icon-arrow-right icon-white"></i>
									</a>
									<?php    
									if (!$cantCheckOut) { ?>
									<?php  if ($cp->canEditPageContents()){
										print $ih->button(
										'<i class="icon-edit icon-white"></i>' . t(''), 
										$editAction, 
										'right', 
										'primary btn-mini', 
										array('style' => "margin-left: 10px", "title" => t("Edit"))); 
									}?>
									<?php   
									if($cp->canEditPagePermissions()){?>
									<a href="javascript:void(0)"
									   class="btn btn-info btn-mini ccm-button-v2-right"
									   onclick="ccm_composerLaunchPermissions(<?php  echo  $_cID;?>)"
									   style="margin-left: 10px;"
									   title="<?php  echo t("Permissions");?>">
										<i class="icon-lock icon-white"></i>
									</a>
									<?php  } ?>
									
									<?php    if ($cp->canViewPageVersions()) { ?>
									<a class="ccm-menu-icon btn btn-success btn-mini dialog-me" 
										<?php    if (!$page->isCheckedOut()) { ?> 
										dialog-on-close="ccm_sitemapExitEditMode(<?php  echo  $_cID;?>)" <?php    } ?> 
										id="ccm-toolbar-nav-versions" 
										dialog-width="640" 
										dialog-height="340" 
										dialog-modal="false" 
										dialog-title="<?php   echo t('Page Versions')?>" 
										id="menuVersions<?php   echo $page->getCollectionID()?>" 
										href="<?php   echo $pvUrl;?>?cID=<?php  echo  $_cID;?>&forcereload=1"
										title="<?php   echo t('Versions')?>"
										style="float: right; margin-left: 10px">
										<i class="icon-th-list icon-white"></i>	
									</a>
									<?php   } else { ?>
									<a href="javascript"
									   class="btn btn-success btn-mini ccm-button-v2-right"
									   onclick="ccm_composerLaunchPreview(<?php  echo  $_cID;?>, $(this).attr('data-page-preview-text'))"
									   style="margin-left: 10px;"
									   data-page-preview-text="<?php  echo  t("Previewing Page: %s", $page->getCollectionName());?>"
									   title="<?php  echo t("Preview");?>">
										<i class="icon-eye-open icon-white"></i>
									</a>
									<?php   } ?>
									<?php    if ($cp->canPreviewPageAsUser() && $advancedEnabled) { ?>
									<a class="btn btn-success btn-mini ccm-icon-preview-as-user dialog-me" 
										<?php    if (!$page->isCheckedOut()) { ?> dialog-on-close="ccm_sitemapExitEditMode(<?php  echo  $_cID;?>)" <?php    } ?> 
										id="ccm-toolbar-nav-preview-as-user" 
										dialog-width="90%" 
										dialog-height="70%" 
										dialog-append-buttons="true" 
										dialog-modal="false" 
										dialog-title="<?php   echo t('View Page as Someone Else')?>" 
										href="<?php   echo REL_DIR_FILES_TOOLS_REQUIRED?>/edit_collection_popup.php?cID=<?php  echo  $_cID;?>&ctask=preview_page_as_user"
										style="float: right; margin-left: 10px"
										title="<?php  echo t("View Page as Someone Else");?>">
										<i class="icon-user icon-white"></i>
									</a>
									<?php   } ?>
									<?php    if ($cp->canDeletePage()) {?>
									<a class="dialog-me btn btn-error error btn-mini ccm-button-v2-right"  
										dialog-append-buttons="true" 
										id="ccm-toolbar-nav-delete" 
										dialog-width="360" 
										dialog-height="150" 
										dialog-modal="false" 
										dialog-title="<?php   echo t('Delete Page')?>" 
										href="<?php   echo $dpUrl?>?cID=<?php   echo $_cID?>&ctask=delete&display_mode=search&instance_id=<?php  echo  $searchInstance;?>&select_mode=&rel=SITEMAP"
										title="<?php   echo t('Delete')?>"
										style="margin-left: 10px;">
										<i class="icon-trash icon-white"></i>
									</a>
									<?php   } ?>
									<?php   } else { 
										echo t("%s is currently editing this page.", $page->getCollectionCheckedOutUserName());
										}?>
								</td>
							</tr>
							<?php  
						}
					?>
				</table>
				
			<?php   } else { ?>
			<div id="ccm-list-none" class="ccm-results-list-none">
				<?php   echo t('No %s Pages Found.', CollectionType::getByID($ctID)->getCollectionTypeName()); ?>
			</div>
			<?php   } ?>
		</div>
		<?php   $pl->displayPagingV2($bu, false); ?>
		<?php   $pl->displaySummary(); ?>
		<div class="clearfix" style="padding: 0 !important;"></div>
	</div>

</div>
<script type="text/javascript">
		$(document).ready(function(){
			$('td.page-actions .btn.dialog-me').dialog();
			$('td.page-actions .btn').tooltip();
		});
		ccm_composerLaunchPermissions = function(cID) {
			var shref = CCM_TOOLS_PATH + '/edit_collection_popup?ctask=edit_permissions&cID=' + cID;
			jQuery.fn.dialog.open({
				title: '<?php   echo t("Permissions")?>',
				href: shref,
				width: '640',
				modal: false,
				height: '310'
			});
		}
		dpm_sitemapSetupSearch = function(instance_id) {
			if ($("#doReload").val() == 0){
				ccm_setupAdvancedSearch(instance_id); 
			} else {
				ccm_setupAdvancedSearchFields(instance_id);
				ccm_setupInPagePaginationAndSorting(instance_id);
				ccm_setupSortableColumnSelection(instance_id);
			}
			ccm_searchActivatePostFunction[instance_id] = function() {
				dpm_sitemapSearchSetupCheckboxes(instance_id);	
			}
			dpm_sitemapSearchSetupCheckboxes(instance_id);	
		}
		dpm_sitemapSearchSetupCheckboxes = function(instance_id) {
			$("#ccm-" + instance_id + "-list-cb-all").click(function(e) {
				e.stopPropagation();
				if ($(this).prop('checked') == true) {
					$('.dpm-list-record td.ccm-' + instance_id + '-list-cb input[type=checkbox]').attr('checked', true);
					$("#ccm-" + instance_id + "-list-multiple-operations").attr('disabled', false);
				} else {
					$('.dpm-list-record td.ccm-' + instance_id + '-list-cb input[type=checkbox]').attr('checked', false);
					$("#ccm-" + instance_id + "-list-multiple-operations").attr('disabled', true);
				}
			});
			$("td.ccm-" + instance_id + "-list-cb input[type=checkbox]").click(function(e) {
				e.stopPropagation();
				if ($("td.ccm-" + instance_id + "-list-cb input[type=checkbox]:checked").length > 0) {
					$("#ccm-" + instance_id + "-list-multiple-operations").attr('disabled', false);
				} else {
					$("#ccm-" + instance_id + "-list-multiple-operations").attr('disabled', true);
				}
			});

			$("#ccm-" + instance_id + "-list-multiple-operations").change(function() {
				var action = $(this).val();
				cIDstring = '';
				$("td.ccm-" + instance_id + "-list-cb input[type=checkbox]:checked").each(function() {
					cIDstring=cIDstring+'&cID[]='+$(this).val();
				});
				switch(action) {
					case "delete":
						jQuery.fn.dialog.open({
							width: 500,
							height: 400,
							modal: false,
							appendButtons: true,
							href: CCM_TOOLS_PATH + '/pages/delete?' + cIDstring + '&searchInstance=' + instance_id,
							title: ccmi18n_sitemap.deletePages				
						});
						break;
					case "design":
						jQuery.fn.dialog.open({
							width: 610,
							height: 405,
							modal: false,
							appendButtons: true,
							href: CCM_TOOLS_PATH + '/pages/design?' + cIDstring + '&searchInstance=' + instance_id,
							title: ccmi18n_sitemap.pageDesign				
						});
						break;
					case 'move_copy':
						jQuery.fn.dialog.open({
							width: 640,
							height: 340,
							modal: false,
							href: CCM_TOOLS_PATH + '/sitemap_overlay?instance_id=' + instance_id + '&select_mode=move_copy_delete&' + cIDstring,
							title: ccmi18n_sitemap.moveCopyPage				
						});
						break;
					case 'speed_settings':
						jQuery.fn.dialog.open({
							width: 610,
							height: 340,
							modal: false,
							appendButtons: true,
							href: CCM_TOOLS_PATH + '/pages/speed_settings?' + cIDstring,
							title: ccmi18n_sitemap.speedSettingsTitle				
						});
						break;
					case 'permissions':
						jQuery.fn.dialog.open({
							width: 430,
							height: 630,
							modal: false,
							appendButtons: true,
							href: CCM_TOOLS_PATH + '/pages/permissions?' + cIDstring,
							title: ccmi18n_sitemap.pagePermissionsTitle				
						});
						break;
					case "properties": 
						jQuery.fn.dialog.open({
							width: 630,
							height: 450,
							modal: false,
							href: CCM_TOOLS_PATH + '/pages/bulk_metadata_update?' + cIDstring,
							title: ccmi18n_sitemap.pagePropertiesTitle				
						});
						break;				
				}

				$(this).get(0).selectedIndex = 0;
			});
		}
		ccm_composerLaunchPreview = function(cID, previewTitle) {
			jQuery.fn.dialog.showLoader();
			<?php    $t = PageTheme::getSiteTheme(); ?>
			ccm_previewInternalTheme(
				cID, 
				<?php   echo $t->getThemeID()?>, 
				previewTitle,
				<?php  echo  $ctID;?>);
		}

		dpm_sitemapDeletePages = function(searchInstance) {
			$("#ccm-" + searchInstance + "-delete-form").ajaxSubmit(function(resp) {
				ccm_parseJSON(resp, function() {	
					jQuery.fn.dialog.closeTop();
					ccm_deactivateSearchResults(searchInstance);
					$("#ccm-" + searchInstance + "-advanced-search").ajaxSubmit(function(resp) {
						ccm_parseAdvancedSearchResponse(resp, searchInstance);
					});
				});
			});
		}

	</script>