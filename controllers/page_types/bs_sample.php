<?php

defined('C5_EXECUTE') or die("Access Denied.");

class BsSamplePageTypeController extends Controller {

	public function on_start() {

		/*
		 * If we have kept our page type hidden, the only way to actually edit
		 * the master template is by linking from the search / list page. That 
		 * will show a "Back To Page Types" link when editing, instead of a 
		 * link back to the manager. If you would like it to link to the manager,
		 * uncomment the stuff below. You do have to hard code the link back to
		 * the listing interface, so be careful about updating that.
		 */
		$c = Page::getCurrentPage();
		$myCT = CollectionType::getByHandle($c->getCollectionTypeHandle());
		if ($myCT->isCollectionTypeInternal()) {
			if ($c->isMasterCollection()) {
				ob_start();
				?>
				<script type="text/javascript">
					$(document).ready(function() {
						$("#ccm-main-nav a.ccm-icon-back")
							   .attr("href", "<?php echo View::url('/dashboard/best_suite/sample') ?>")
							   .text("<?php echo t("Back to Sample"); ?>");
					});
				</script>
				<?php
				$changeHeaderScript = ob_get_clean();
				$this->addFooterItem($changeHeaderScript);
			} else {
				$c = Page::getCurrentPage();
				$cp = new Permissions($c);
				if ($cp->canViewToolbar()){
					$ct = CollectionType::getByHandle("bs_sample");
					$ctID = $ct->getCollectionTypeID();
					$bscHelper = Loader::helper('best_suite_core', 'dashboard_page_managers');
					$customCollectionOptions = $bscHelper->getCollectionTypeDetails($ctID);
					$managerCID = Page::getByPath("/dashboard/best_suite/sample")->getCollectionID();
					if ($customCollectionOptions && $customCollectionOptions->hasCustomEditPage) {
						$writePagePath = Page::getByID($customCollectionOptions->customEditPageCID)->getCollectionPath();
						$editAction = View::url($writePagePath, 'edit', $this->c->getCollectionID(), 0, $managerCID);
					} else {
						$editAction = View::url('/dashboard/composer/write-pm', 'edit', $this->c->getCollectionID(), 0, $managerCID);
					}
					ob_start();
					?>
					<script type="text/javascript">
						$(document).ready(function() {
							$("#ccm-main-nav li:not(#ccm-logo-wrapper) a.ccm-icon-edit.ccm-menu-icon")
								   .attr("id", "link-to-composer")
								   .attr("href", "<?php echo $editAction;?>")
								   .text("<?php echo t("Edit In Page Manager");?>");
						});
					</script>
					<?php
					$changeHeaderScript = ob_get_clean();
					$this->addFooterItem($changeHeaderScript);
				}
			}
		}
	}

	/*
	 * This function will be run by the editing page before publishing a page.
	 * You can do pretty much anything you want here. The values in the $_POST 
	 * array will be sent in as the $data. 
	 * 
	 * Because the validation/error object is a singleton, the things added here
	 * will also exist in the calling page controller. A return value isn't really
	 * needed because of that, but it's included so that this function can be 
	 * called from other places if there's a need.
	 * 
	 * @var $data Array */

	public function validateComposer($data = false) {
		$e = Loader::helper("validation/error");

		if (!$data) {
			return true;
		} else {

			/*
			 * A built in page feature
			 */
			if (!strlen($data['cDescription']) > 0) {
				$e->add(t("Please enter a short description."));
			}

			/*
			 * And now checking the value of an attribute key in the form
			 * If you have to update multiples, make an array of keys and 
			 * loop over it.
			 */
			$metaKeywords = CollectionAttributeKey::getByHandle("meta_keywords");
			$e1 = $metaKeywords->validateAttributeForm();
			if ($e1 == false) {
				$e->add(t('The field "%s" is required', tc('AttributeKeyName', $metaKeywords->getAttributeKeyName())));
			} else if ($e1 instanceof ValidationErrorHelper) {
				$e->add($e1);
			}

			/*
			 * Validating blocks is a bit different. It might be possible to 
			 * just call the validate function on the block if it exists. 
			 */
			$cobj = $this->getCollectionObject();
			$cID = $cobj->getCollectionID();
			$bscHelper = Loader::helper("best_suite_core", "dashboard_page_managers");
			$bscHelper->loadCollectionComposerItems($cobj);
			$block = $bscHelper->getNamedBlock("Sample Text");
			/* We only want to validate if the block was actually found. */
			if ($block && is_a($block, "Block")){
				$bID = $block->getBlockID();
				
				/* Here we are just checking the data array (probably $_POST 
				 * explicitly. If you know what exactly you need, that's OK,
				 * but it is probably better to call the validate function 
				 * on the block controller if it exists. 
				 */
				
				/*
				$content = $data['_bf']['BLOCK_' . $contentBlockBID . "_" . $cID]['content'];
				if (!strlen($content) > 0) {
					$e->add(t("Please include some Content."));
				}*/

				/* If there's a validate function on the block's controller,
				 * then you can do this. You will have to pay attention to 
				 * what block you are validating. 
				 * 
				 * The issue with this method is that if you have multiple
				 * blocks of the same type, it will be hard to actually know
				 * which block the error message is from. There's not really a 
				 * solution for this right now. 
				 * 
				 * Plus whenever the error helper has a new entry added, it's
				 * added everywhere. So you can't even just get an error object 
				 * from the block and then scope it for the final output...
				 * 
				 * If anyone has a solution, please email me at 
				 * jeremy.werst@gmail.com 
				 */

				$blockController = Loader::controller($block);
				if (method_exists($blockController, "validate")){
					$blockData = $content = $data['_bf']['BLOCK_' . $bID . "_" . $cID];
					$blockController->validate($blockData);
				}
			}
			
			if ($e->has()) {
				return $e;
			} else {
				return true;
			}
		}
	}

}