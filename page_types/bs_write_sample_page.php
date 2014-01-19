<?php
defined('C5_EXECUTE') or die("Access Denied.");

if (isset($entry)) {
	$url = Loader::helper('concrete/urls');
	$pk = PermissionKey::getByHandle('edit_page_properties');
	$pk->setPermissionObject($entry);
	$asl = $pk->getMyAssignment();
	$allowedAKIDs = $asl->getAttributesAllowedArray();
	
	/* Quick and easy tabs that match the core */
	$interfaceH = Loader::helper('concrete/interface');
	$tabs = array();
	$tabs[] = array('basic-info', t('Basic Info'), true);
	$tabs[] = array('content', t('Content'));

	/* The helper allows us to find blocks since they can't just be called by handle */
	$bscHelper = Loader::helper("best_suite_core", "dashboard_page_managers");
	$bscHelper->loadCollectionComposerItems($entry);

	/* And now to see if we're publishing the page or submitting it to workflow */
	$pk = PermissionKey::getByHandle('approve_page_versions');
	$pk->setPermissionObject($entry);
	$pa = $pk->getPermissionAccessObject();
	if ($pa) {
		$workflow = (count($pa->getWorkflows()) > 0);
	} else {
		$workflow = false;
	}
	?>
	<style type="text/css">
		.pane {
			padding-bottom: 15px;
		}
	</style>

	<?php echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(ucfirst($action) . ' ' . $ct->getCollectionTypeName(), false, false, false) ?>
	<?php if ($rcID) { ?>
		<form method="post" class="form-horizontal" enctype="multipart/form-data" action="<?php echo $this->action('save', $rcID) ?>" id="ccm-dashboard-composer-form">
		<?php } else { ?>
			<form method="post" class="form-horizontal" enctype="multipart/form-data" action="<?php echo $this->action('save') ?>" id="ccm-dashboard-composer-form">	
			<?php } ?>
			<input type="hidden" name="ccm-publish-draft" value="0" />

			<div class="ccm-pane-body">
				<div id="composer-save-status"></div>

				<div class="tabs">
					<?php echo($interfaceH->tabs($tabs, true)); ?>
				</div>
				<div id="ccm-tab-content-basic-info" class="pane form-horizontal" style="display: none;">
					<?php
					/* Begin top-level page items */
					if ($asl->allowEditName()) {
						?>
						<div class="control-group">
							<?php echo $form->label('cName', t('Name')) ?>
							<div class="controls">
								<?php
								echo
								$form->text(
									'cName', Loader::helper("text")->entities($name), array(
									'class' => 'input-xlarge',
									'onKeyUp' => "ccm_updateAddPageHandle()"))
								?>
							</div>		
						</div>
					<?php } ?>

					<?php if ($asl->allowEditPaths()) { ?>
						<div class="control-group">
							<?php echo $form->label('cHandle', t('URL Slug')) ?>
							<div class="controls"><?php echo $form->text('cHandle', $handle, array('class' => 'span3')) ?>
								<img src="<?php echo ASSETS_URL_IMAGES ?>/loader_intelligent_search.gif" width="43" height="11" id="ccm-url-slug-loader" style="display: none" />
							</div>		
						</div>
					<?php } ?>

					<?php if ($asl->allowEditDescription()) { ?>
						<div class="control-group">
							<?php echo $form->label('cDescription', t('Short Description')) ?>
							<div class="controls"><?php echo $form->textarea('cDescription', Loader::helper("text")->entities($description), array('class' => 'input-xlarge', 'rows' => 5)) ?></div>		
						</div>
					<?php } ?>

					<?php if ($asl->allowEditDateTime()) { ?>
						<div class="control-group" style="display: none;">
							<?php echo $form->label('cDatePublic', t('Date Posted')) ?>
							<div class="controls"><?php
								if ($this->controller->isPost()) {
									$cDatePublic = Loader::helper('form/date_time')->translate('cDatePublic');
								}
								?><?php echo Loader::helper('form/date_time')->datetime('cDatePublic', $cDatePublic) ?></div>		
						</div>
					<?php } ?>
					<?php if ($entry->isComposerDraft()) { ?>
						<div class="control-group">
							<?php echo $form->label('cPublishLocation', t('Publish Location')) ?>
							<div class="controls">
								<span id="ccm-composer-publish-location"><?php
									print $this->controller->getComposerDraftPublishText($entry);
									?>
								</span>
								<?php if ($ct->getCollectionTypeComposerPublishMethod() == 'PAGE_TYPE' || $ct->getCollectionTypeComposerPublishMethod() == 'CHOOSE') { ?>
								<a href="javascript:void(0)" onclick="ccm_openComposerPublishTargetWindow(false)"><?php echo t('Choose publish location.') ?></a>
								<?php } ?>
							</div>
						</div>
					<?php } 
					/* End top-level page items */
					
					/* Now to output our extra attribute in a different place
					 * Instead of just looping, use this syntax
					 */
					$bscHelper->outputAttributeKeyComposerEditForm("meta_keywords", $entry);
					?>
				</div>
				<div id="ccm-tab-content-content" class="pane form-horizontal" style="display: none;">
					<?php 
					/* Blocks are also pretty simple to output */
					$bscHelper->outputNamedBlockComposerEditForm("Sample Text", $entry);
					?>
				</div>
			</div>
			<div class="ccm-pane-footer">
				<?php
				$v = $entry->getVersionObject();
				$pp = new Permissions($entry);
				if ($entry->isComposerDraft()) { 
					if ($workflow) { 
						echo Loader::helper('concrete/interface')->submit(t('Submit to Workflow'), 'publish', 'right', 'primary');
					} else { 
						echo Loader::helper('concrete/interface')->submit(t('Publish Page'), 'publish', 'right', 'primary');
					}
				} else { 
					if ($workflow) { 
						echo Loader::helper('concrete/interface')->submit(t('Submit to Workflow'), 'publish', 'right', 'primary');
					} else { 
						echo Loader::helper('concrete/interface')->submit(t('Publish Changes'), 'publish', 'right', 'primary'); 
					}	
				} 
				echo Loader::helper('concrete/interface')->button_js(t('Preview'), 'javascript:ccm_composerLaunchPreview()', 'right', 'ccm-composer-hide-on-approved btn-success', array('id' => 'ccm-button-preview'));
				if ($rcID) {
					echo "<input type='hidden' name='do_redirect_on_save' value='1'>";
					$nh = Loader::helper('/navigation');
					$returnLink = $nh->getLinkToCollection(Page::getByID($rcID));
					?>
					<a href="<?php echo $returnLink; ?>"
					   class="btn ccm-button-v2 btn-info ccm-button-v2-right"
					   id="ccm-submit-save">
					<?php echo t('Save Draft and Return'); ?>
					</a>
					<?php
				} else {
					echo Loader::helper('concrete/interface')->submit(t('Save Draft'), 'save', 'right', 'btn-info');
				}
				if ($entry->isComposerDraft()) {
					echo Loader::helper('concrete/interface')->submit(t('Discard Permanently'), 'discard', 'left', 'error ccm-composer-hide-on-approved');
				} else {
					echo Loader::helper('concrete/interface')->submit(t('Cancel Changes'), 'discard', 'left', 'error ccm-composer-hide-on-approved');
				}
				echo $form->hidden('entryID', $entry->getCollectionID());
				if ($entry->isComposerDraft()) { 
					echo $form->hidden('cPublishParentID', $entry->getComposerDraftPublishParentID());
				} 
				echo $form->hidden('autosave', 0);
				echo Loader::helper('validation/token')->output('composer'); ?>
			</div>
		</form>
	<?php echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false) ?>
		<script type="text/javascript">
			var ccm_composerAutoSaveInterval = false;
			var ccm_composerDoAutoSaveAllowed = true;
			var ccm_composerAddPageTimer = false;

			ccm_updateAddPageHandle = function() {
				clearTimeout(ccm_composerAddPageTimer);
				ccm_composerAddPageTimer = setTimeout(function() {
					var val = $('#ccm-dashboard-composer-form input[name=cName]').val();
					$('#ccm-url-slug-loader').show();
					$.post('<?php echo REL_DIR_FILES_TOOLS_REQUIRED ?>/pages/url_slug', {
						'token': '<?php echo Loader::helper('validation/token')->generate('get_url_slug') ?>',
						'name': val
					}, function(r) {
						$('#ccm-url-slug-loader').hide();
						$('#ccm-dashboard-composer-form input[name=cHandle]').val(r);
					});
				}, 150);
			}

			ccm_composerDoAutoSave = function(callback) {
				if (!ccm_composerDoAutoSaveAllowed) {
					return false;
				}
				$('#ccm-submit-save').attr('disabled', true);
				$('#ccm-submit-publish').attr('disabled', true);
				$('#ccm-submit-discard').attr('disabled', true);
				$('#ccm-button-preview').attr('disabled', true);
				$('input[name=autosave]').val('1');
				try {
					tinyMCE.triggerSave(true, true);
				} catch (e) {
				}

				$('#ccm-dashboard-composer-form').ajaxSubmit({
					'dataType': 'json',
					'success': function(r) {
						$('input[name=autosave]').val('0');
						ccm_composerLastSaveTime = new Date();
						$("#composer-save-status").html('<div class="alert alert-info"><?php echo t("Page saved at ") ?>' + r.time + '</div>');
						$(".ccm-composer-hide-on-approved").show();
						$('#ccm-submit-save').attr('disabled', false);
						$('#ccm-submit-publish').attr('disabled', false);
						$('#ccm-submit-discard').attr('disabled', false);
						$('#ccm-button-preview').attr('disabled', false);
						if (callback) {
							callback();
						}
					}
				});

			}

			ccm_composerLaunchPreview = function() {
				jQuery.fn.dialog.showLoader();
				<?php $t = PageTheme::getSiteTheme(); ?>
				ccm_composerDoAutoSave(function() {
					ccm_previewInternalTheme(<?php echo $entry->getCollectionID() ?>, <?php echo $t->getThemeID() ?>, '<?php echo addslashes(str_replace(array("\r", "\n", "\n"), '', $t->getThemeName())) ?>');
				});
			}

			ccm_composerSelectParentPage = function(cID) {
				$("input[name=cPublishParentID]").val(cID);
				$(".ccm-composer-hide-on-no-target").show();
				$("#ccm-composer-publish-location").load('<?php echo $this->action("select_publish_target") ?>', {'entryID': <?php echo $entry->getCollectionID() ?>, 'cPublishParentID': cID});
				jQuery.fn.dialog.closeTop();

			}

			ccm_composerSelectParentPageAndSubmit = function(cID) {
				$("input[name=cPublishParentID]").val(cID);
				$(".ccm-composer-hide-on-no-target").show();
				$("#ccm-composer-publish-location").load('<?php echo $this->action("select_publish_target") ?>', {'entryID': <?php echo $entry->getCollectionID() ?>, 'cPublishParentID': cID}, function() {
					$("input[name=ccm-publish-draft]").val(1);
					$('#ccm-dashboard-composer-form').submit();
				});
			}

			ccm_composerLaunchPermissions = function(cID) {
				var shref = CCM_TOOLS_PATH + '/edit_collection_popup?ctask=edit_permissions&cID=<?php echo $entry->getCollectionID() ?>';
				jQuery.fn.dialog.open({
					title: '<?php echo t("Permissions") ?>',
					href: shref,
					width: '640',
					modal: false,
					height: '310'
				});
			}

			ccm_composerEditBlock = function(cID, bID, arHandle, w, h) {
				if (!w)
					w = 550;
				if (!h)
					h = 380;
				var editBlockURL = '<?php echo REL_DIR_FILES_TOOLS_REQUIRED ?>/edit_block_popup';
				$.fn.dialog.open({
					title: ccmi18n.editBlock,
					href: editBlockURL + '?cID=' + cID + '&bID=' + bID + '&arHandle=' + encodeURIComponent(arHandle) + '&btask=edit',
					width: w,
					modal: false,
					height: h
				});
			}
			
			ccm_openComposerPublishTargetWindow = function(submitOnChoose) {
				var shref = '<?php echo $url->getToolsURL('dashboard_page_managers/composer_target', 'dashboard_page_managers');?>?cID=<?php   echo $entry->getCollectionID()?>';
				if (submitOnChoose) {
					shref += '&submitOnChoose=1';
				}
				jQuery.fn.dialog.open({
					title: '<?php   echo t("Publish Page")?>',
					href: shref,
					width: '550',
					modal: false,
					height: '400'
				});
			}
			
			function forwardAfterDraft() {
				window.location = $("#ccm-submit-save").attr('href');
			}

			$(function() {
				<?php if (is_object($v) && $v->isApproved()) { ?>
					$(".ccm-composer-hide-on-approved").hide();
				<?php } ?>

				if ($("input[name=cPublishParentID]").val() < 1) {
					$(".ccm-composer-hide-on-no-target").hide();
				}

				var ccm_composerAutoSaveIntervalTimeout = 120000;
				var ccm_composerIsPublishClicked = false;
				<?php if ($entry->isComposerDraft()) { ?>
					$("#ccm-submit-discard").click(function() {
						return (confirm('<?php echo t("Discard this draft permanently?") ?>'));
					});
				<?php } else { ?>
					$("#ccm-submit-discard").click(function() {
						return (confirm('<?php echo t("Discard this draft?") ?>'));
					});
				<?php } ?>
				$("#ccm-submit-save").click(function() {
					ccm_composerDoAutoSaveAllowed = true;
					ccm_composerDoAutoSave(forwardAfterDraft);
					return false;
				});

				$("#ccm-dashboard-composer-form").submit(function() {
					ccm_composerDoAutoSaveAllowed = false;
				});

				<?php if ($entry->isComposerDraft()) { ?>
				$("#ccm-dashboard-composer-form").submit(function() {
					if ($("#cPublishParentID").val() > 0) {
						var publishTargetData = $.post('<?php echo $this->action("select_publish_target") ?>', {'entryID': <?php echo $entry->getCollectionID() ?>, 'cPublishParentID': $("#cPublishParentID").val()});
						console.log(publishTargetData);
						return true;
					}
					if (ccm_composerIsPublishClicked) {
						ccm_composerIsPublishClicked = false;
						$('input[name=ccm-publish-draft]').val(0);
					<?php if ($ct->getCollectionTypeComposerPublishMethod() == 'PAGE_TYPE' || $ct->getCollectionTypeComposerPublishMethod() == 'CHOOSE') { ?>
						ccm_openComposerPublishTargetWindow(true);
						return false;
					<?php } else if ($ct->getCollectionTypeComposerPublishMethod() == 'PARENT') { ?>
								return true;
					<?php } else { ?>
								return false;
					<?php } ?>
						}
					});
				<?php } ?>
				ccm_composerAutoSaveInterval = setInterval(
					   function() {
							ccm_composerDoAutoSave();
						},
						ccm_composerAutoSaveIntervalTimeout);

				});
		</script>
		<?php
	} else {
		$composer = Page::getByPath('/dashboard/composer');
		$nh = Loader::helper('/navigation');
		$link = $nh->getLinkToCollection($composer);
		echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Write Sample Pages'), false, 'span10 offset1')
		?>
		<p><?php echo t('You cannot access this page directly, you must link from a News Manager.') ?></p>
		<p><a href="<?php echo $link; ?>"<?php echo t('Use traditional composer.') ?></a></p>
		<?php echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper() ?>

<?php } ?>

