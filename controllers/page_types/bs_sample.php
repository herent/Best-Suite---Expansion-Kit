<?php
defined('C5_EXECUTE') or die("Access Denied.");

class BsSamplePageTypeController extends Controller {

	public function on_start() {

		/*
		 * These will allow you to adjust the edit bar, or remove it. If you 
		 * would like to leave the default editing functions in place, then 
		 * remove or comment out this stuff
		 */
		
		/*
		 * This will overwrite the edit button so that the dropdown doesn't 
		 * show, and then change it so that when clicked on, it will send you 
		 * to the dashboard manager. 
		 */
		$c = Page::getCurrentPage();
		$myCT = CollectionType::getByHandle($c->getCollectionTypeHandle());
		if (!$c->isMasterCollection()) {
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
		
		/*
		 * And this one will completely remove the whole edit bar. If needed
		 * you might want to check if a user is or isn't in particular group 
		 * before doing this, but it's not 100% necessary.
		 *
		$g = Group::getByName("Administrators");
		$u = new User();
		if ($g && is_a($g, "Group") && !$u->inGroup($g)){
			$c = Page::getCurrentPage();
			if (!$c->isMasterCollection()) {
				$c = Page::getCurrentPage();
				$cp = new Permissions($c);
				if ($cp->canViewToolbar()){
					ob_start();
					?>
					<script type="text/javascript">
						$(document).ready(function() {
							$("#ccm-main-nav").remove();
						});
					</script>
					<?php
					$changeHeaderScript = ob_get_clean();
					$this->addFooterItem($changeHeaderScript);
				}
			}
		}
		 */
		
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
		$cobj = Page::getCurrentPage();
		
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
			 * Select attributes and file attributes don't appear to validate
			 * properly due to some core bugs. So I'm manually checking them
			 * here, it's a bit hacky, but it works
			 */
			
			$thumbAttAkID = CollectionAttributeKey::getByHandle("bs_sample_thumbnail")->getAttributeKeyID();
			if (!intval($data['akID'][$thumbAttAkID]['value'])>0){
				$e->add(t("Please choose a thumbnail."));
			}

			$categoryAkID = CollectionAttributeKey::getByHandle("bs_sample_category")->getAttributeKeyID();
			if (!count($data['akID'][$categoryAkID]['atSelectOptionID'])>0 && !count($data['akID'][$categoryAkID]['atSelectNewOption'])>0){
				$e->add(t("Please select a category."));
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
			if ($block && is_a($block, "Block")) {
				$bID = $block->getBlockID();

				/* Here we are just checking the data array (probably $_POST 
				 * explicitly. If you know what exactly you need, that's OK,
				 * but it is probably better to call the validate function 
				 * on the block controller if it exists. 
				 */

				$content = $data['_bf']['BLOCK_' . $bID . "_" . $cID]['content'];
				if (!strlen($content) > 0) {
					$e->add(t("Please include some content."));
				}

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


				  $blockController = Loader::controller($block);
				  if (method_exists($blockController, "validate")){
				  $blockData = $content = $data['_bf']['BLOCK_' . $bID . "_" . $cID];
				  $blockController->validate($blockData);
				  }
				 */
			}

			if ($e->has()) {
				return $e;
			} else {
				return true;
			}
		}
	}

}
