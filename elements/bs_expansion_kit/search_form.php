<?php   defined('C5_EXECUTE') or die("Access Denied."); 
$form = Loader::helper('fixed_form', 'dashboard_page_managers');
$ps = Loader::helper('form/page_selector');
$url = Loader::helper('concrete/urls');
$bscHelper = Loader::helper('best_suite_core', 'dashboard_page_managers');
$ctID = $ct->getCollectionTypeID();

$customCollectionOptions = $bscHelper->getCollectionTypeDetails($ctID);

if (!$redirect) {
	if ($customCollectionOptions && $customCollectionOptions->hasCustomSearchInterface){
		$searchFolderName = $customCollectionOptions->customSearchInterfaceFolderName;
		$pkgHandle = Package::getByID($customCollectionOptions->pkgID)->getPackageHandle();
		$urlSearchAction = $url->getToolsURL($searchFolderName . '/search_results', $pkgHandle);
	} else {
		$urlSearchAction = $url->getToolsURL('dashboard_page_managers/search_results', 'dashboard_page_managers');
	}
} else {
	$urlSearchAction = View::url(Page::getByID($cID)->getCollectionPath());
}
$searchFields = array(
	''			 => '** ' . t('Fields'),
	'keywords'	 => t('Full Page Index'),
	'date_added'	 => t('Date Added'),
	'last_modified' => t('Last Modified'),
	'date_public'	 => t('Public Date'),
	'owner'		 => t('Page Owner')
);

if (!$searchDialog) {
	$searchFields['parent'] = t('Parent Page');
}

Loader::model('attribute/categories/collection');
$contentitems = $ct->getComposerContentItems();
$searchFieldAttributes = CollectionAttributeKey::getSearchableList();
foreach ($searchFieldAttributes as $ak) {
	$defaultSearchFieldAttributes[] = $ak->getAttributeKeyID();
}
$composerSearchFieldAttributes = array();
foreach ($contentitems as $ci) {
	if ($ci instanceof CollectionAttributeKey) {
		$ak = $ci;
		if (!in_array($ak->getAttributeKeyID(), $defaultSearchFieldAttributes)) {
			continue;
		}
		$composerSearchFieldAttributes[] = $ak;
		$searchFields[$ak->getAttributeKeyID()] = $ak->getAttributeKeyDisplayHandle();
	}
}
?>
<style type="text/css">
	.ccm-pane-options-permanent-search .search-element {
		display: inline-block;
		margin-right: 10px;
	}
	.ccm-ui .form-horizontal .ccm-pane-options-permanent-search .search-element div.controls{ 
		margin-left: 0;
	}
	div.page-selector-wrap .ccm-summary-selected-item {
		margin: 0 !important;
	}
	div.ccm-selected-field-content .ccm-search-option-type-select {
		/*outline: 1px solid blue;*/
		height: 300px;
		overflow: auto;
		border: 1px solid rgb(221, 221, 221);
		background: #fff;
		padding: 5px;
	}
	div.ccm-selected-field-content .ccm-search-option-type-select div{
		margin: 0 5px 10px 5px;
		/*outline: 1px solid red;*/
		position: relative;
		padding-left: 15px;
	}
	div.ccm-selected-field-content .ccm-search-option-type-select div input[type='checkbox']{
		position: absolute;
		left: 0;
		top: 0;
		
	}
	div.ccm-pane-options form.form-horizontal {
		position: relative;
	}
	.ccm-pane-options form.form-horizontal a.ccm-icon-option-closed, 
	.ccm-pane-options form.form-horizontal a.ccm-icon-option-open {
		top: 0;
		right: 0;
	}
	.ccm-pane-options form.form-horizontal a#ccm-list-view-customize,
	.ccm-pane-options form.form-horizontal a#ccm-list-view-customize-top{
		color: #666;
	}
</style>

<div id="ccm-<?php   echo $searchInstance?>-search-field-base-elements" style="display: none">
<span class="ccm-search-option"  search-field="keywords">
	<?php   echo $form->text('keywords', $searchRequest['keywords'], array('style' => 'width: 120px')) ?>
</span>

<span class="ccm-search-option ccm-search-option-type-date_time"  search-field="date_public">
	<?php   echo $form->text('date_public_from', array('style' => 'width: 86px')) ?>
	<?php   echo t('to') ?>
	<?php   echo $form->text('date_public_to', array('style' => 'width: 86px')) ?>
</span>

<span class="ccm-search-option ccm-search-option-type-date_time"  search-field="date_added">
	<?php   echo $form->text('date_added_from', array('style' => 'width: 86px')) ?>
	<?php   echo t('to') ?>
	<?php   echo $form->text('date_added_to', array('style' => 'width: 86px')) ?>
</span>

<span class="ccm-search-option ccm-search-option-type-date_time"  search-field="last_modified">
	<?php   echo $form->text('last_modified_from', array('style' => 'width: 86px')) ?>
	<?php   echo t('to') ?>
	<?php   echo $form->text('last_modified_to', array('style' => 'width: 86px')) ?>
</span>

<span class="ccm-search-option"  search-field="owner">
	<?php   echo $form->text('owner', array('class' => 'span5')) ?>
</span>

<?php   if (!$searchDialog) { ?>
	<span class="ccm-search-option" search-field="parent">

		<?php  
		$ps = Loader::helper("form/page_selector");
		print $ps->selectPage('cParentIDSearchField');
		?>

		<br/><strong><?php   echo t('Search All Children?') ?></strong><br/>
		<label class="checkbox"><?php   echo $form->radio('cParentAll', 0, false) ?> <span><?php   echo t('No') ?></span></label>
		<label class="checkbox"><?php   echo $form->radio('cParentAll', 1, false) ?> <span><?php   echo t('Yes') ?></span></label>
	</span>
<?php   } ?>		

<?php  
if (count($composerSearchFieldAttributes)>0){
	foreach ($composerSearchFieldAttributes as $sfa) {
		$sfa->render('search');
	}
} ?>
</div>
<?php   if (!$redirect) { ?>

	<form 
		method="get" 
		id="ccm-<?php   echo $searchInstance ?>-advanced-search"
		action="<?php   echo $urlSearchAction; ?>?cID=<?php  echo  $cID; ?>"
		class="form-horizontal">
		<input id="doReload" type="hidden"value="0" />
<?php   } else { ?>
		<form 
			method="post" 
			id="ccm-<?php   echo $searchInstance ?>-advanced-search"
			action="<?php   echo $urlSearchAction; ?>"
			class="form-horizontal">
		<input id="doReload" type="hidden" value="1" />
		<input id="reloadPath" type="hidden" value="<?php   echo $urlSearchAction; ?>" />
<?php   } ?>
		<input type="hidden" name="submit_search" value="1" />
		<input type="hidden" name="searchInstance" value="<?php   echo $searchInstance ?>" />
		<input type="hidden" name="ctID" value="<?php   echo $ctID ?>" />
		<?php  
		print $form->hidden('ccm_order_dir', $searchRequest['ccm_order_dir']);
		print $form->hidden('ccm_order_by', $searchRequest['ccm_order_by']);
		if ($searchDialog) {
			print $form->hidden('searchDialog', true);
		}
		if ($sitemap_select_mode) {
			print $form->hidden('sitemap_select_mode', $sitemap_select_mode);
		}
		if ($sitemap_select_callback) {
			print $form->hidden('sitemap_select_callback', $sitemap_select_callback);
		}
		if ($sitemap_display_mode) {
			print $form->hidden('sitemap_display_mode', $sitemap_display_mode);
		}
		?>
		<div class="ccm-pane-options-permanent-search">
			<div class="search-element">
				<div class="controls">
					<?php   echo $form->text('cvName', $searchRequest['cvName'], array('class' => 'input-large', 'placeholder' => t('Page Name'))); ?>
				</div>
			</div>
			<div class="search-element">
				<div class="controls">
					<?php  
					echo $form->select('page_status', array(
						'all'		 => 'All Results',
						'published'	 => 'Published',
						'pending'		 => 'Revision Pending',
						'draft'		 => 'Drafts'
						), $searchRequest['page_status'], array('class' => 'input-medium'))
					?>
				</div>
			</div>
			<div class="search-element">
				<?php   echo $form->label('num_results', t('# Per Page'), array('style' => 'display: inline-block; float: left;')) ?>
				<div class="controls" style="margin-left: 10px;">
					<?php  
					echo $form->select('num_results', array(
						'10'	 => '10',
						'25'	 => '25',
						'50'	 => '50',
						'100' => '100',
						'500' => '500'
						), $searchRequest['num_results'], array('style' => 'width:65px'))
					?>
				</div>
			<img 
				src="<?php   echo ASSETS_URL_IMAGES ?>/loader_intelligent_search.gif" 
				width="43" 
				height="11" 
				class="ccm-search-loading" 
				id="ccm-dashboard-page-managers-search-loading" />
			<?php   echo 
				$form->submit(
					'ccm-search-pagess', 
					t('Search'), 
					array('style' => 'margin-left: 20px;'), 
					'ccm-button-v2-right primary') ?>
			</div>
		</div>
		<a href="javascript:void(0)" onclick="ccm_paneToggleOptions(this)" class="ccm-icon-option-<?php   if (is_array($searchRequest['selectedSearchField']) && count($searchRequest['selectedSearchField']) > 1) { ?>open<?php   } else { ?>closed<?php   } ?>"><?php   echo t('Advanced Search') ?></a>
		<div class="clearfix ccm-pane-options-content" <?php   if (is_array($searchRequest['selectedSearchField']) && count($searchRequest['selectedSearchField']) > 1) { ?>style="display: block" <?php   } ?>>
			<br/>
			<table class="table-striped table ccm-search-advanced-fields" id="ccm-<?php   echo $searchInstance ?>-search-advanced-fields">
				<tr>
					<th colspan="2" width="100%"><?php   echo t('Additional Filters') ?></th>
					<th style="text-align: right; white-space: nowrap"><a href="javascript:void(0)" id="ccm-<?php   echo $searchInstance ?>-search-add-option" class="ccm-advanced-search-add-field"><span class="ccm-menu-icon ccm-icon-view"></span><?php   echo t('Add') ?></a></th>
				</tr>
				<tr id="ccm-search-field-base">
					<td><?php   echo $form->select('searchField', $searchFields); ?></td>
					<td width="100%">
						<input type="hidden" value="" class="ccm-<?php   echo $searchInstance ?>-selected-field" name="selectedSearchField[]" />
						<div class="ccm-selected-field-content">
						<?php   echo t('Select Search Field.') ?>				
						</div></td>
					<td><a href="javascript:void(0)" class="ccm-search-remove-option"><img src="<?php   echo ASSETS_URL_IMAGES ?>/icons/remove_minus.png" width="16" height="16" /></a></td>
				</tr>
				<?php  
				$i = 1;
				if (is_array($searchRequest['selectedSearchField'])) {
					foreach ($searchRequest['selectedSearchField'] as $req) {
						if ($req == '') {
							continue;
						}
						?>

						<tr class="ccm-search-field ccm-search-request-field-set" ccm-search-type="<?php   echo $req ?>" id="ccm-<?php   echo $searchInstance ?>-search-field-set<?php   echo $i ?>">
							<td><?php   echo $form->select('searchField' . $i, $searchFields, $req); ?></td>
							<td width="100%">
								<input type="hidden" value="<?php   echo $req ?>" 
									  class="ccm-<?php   echo $searchInstance ?>-selected-field" 
									  name="selectedSearchField[]" />
								<div class="ccm-selected-field-content">
										<?php   if ($req == 'date_public') { ?>
										<span class="ccm-search-option ccm-search-option-type-date_time"  search-field="date_public">
											<?php   echo $form->text('date_public_from', $searchRequest['date_public_from'], array('style' => 'width: 86px')) ?>
											<?php   echo t('to') ?>
										<?php   echo $form->text('date_public_to', $searchRequest['date_public_to'], array('style' => 'width: 86px')) ?>
										</span>
									<?php   } ?>

										<?php   if ($req == 'keywords') { ?>
										<span class="ccm-search-option"  search-field="keywords">
										<?php   echo $form->text('keywords', $searchRequest['keywords'], array('style' => 'width: 120px')) ?>
										</span>
									<?php   } ?>

										<?php   if ($req == 'date_added') { ?>
										<span class="ccm-search-option ccm-search-option-type-date_time"  search-field="date_added">
											<?php   echo $form->text('date_added_from', $searchRequest['date_added_from'], array('style' => 'width: 86px')) ?>
											<?php   echo t('to') ?>
										<?php   echo $form->text('date_added_to', $searchRequest['date_added_to'], array('style' => 'width: 86px')) ?>
										</span>
									<?php   } ?>

										<?php   if ($req == 'owner') { ?>
										<span class="ccm-search-option"  search-field="owner">
										<?php   echo $form->text('owner', $searchRequest['owner'], array('class' => 'span5')) ?>
										</span>
									<?php   } ?>

									<?php   if ((!$searchDialog) && $req == 'parent') { ?>
										<span class="ccm-search-option" search-field="parent">

											<?php  
											$ps = Loader::helper("form/page_selector");
											print $ps->selectPage('cParentIDSearchField', $searchRequest['cParentIDSearchField']);
											?>

											<br/><strong><?php   echo t('Search All Children?') ?></strong><br/>

											<ul class="inputs-list">
												<li><label><?php   echo $form->radio('_cParentAll', 0, $searchRequest['cParentAll']) ?> <span><?php   echo t('No') ?></span></label></li>
												<li><label><?php   echo $form->radio('_cParentAll', 1, $searchRequest['cParentAll']) ?> <span><?php   echo t('Yes') ?></span></label></li>
											</ul>
										</span>
									<?php   } ?>

									<?php  
									foreach ($searchFieldAttributes as $sfa) {
										if ($sfa->getAttributeKeyID() == $req) {
											$at = $sfa->getAttributeType();
											$at->controller->setRequestArray($searchRequest);
											$at->render('search', $sfa);
										}
									}
									?>
								</div>
							</td>
							<td style="text-align: right;">
								<a href="javascript:void(0)" 
								   class="ccm-search-remove-option">
									<img src="<?php   echo ASSETS_URL_IMAGES ?>/icons/remove_minus.png" width="16" height="16" />
								</a>
							</td>
						</tr>
						<?php  
						$i++;
					}
				}
				?>
			</table>
		</div>
		<div id="ccm-search-fields-submit">
			<a href="<?php   echo $url->getToolsURL('dashboard_page_managers/customize_search_columns', 'dashboard_page_managers');?>?searchInstance=<?php   echo $searchInstance ?>&ctID=<?php  echo  $ctID;?>&cID=<?php  echo  $cID;?>" 
			   id="ccm-list-view-customize">
				<span class="ccm-menu-icon ccm-icon-properties"></span>
					<?php   echo t('Customize Results') ?>
			</a>
		</div>
	</form>
