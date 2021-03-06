<?php

defined("C5_EXECUTE") or die(_("Access Denied."));

class BestSuiteExpansionKitPackage extends Package {

	protected $pkgHandle = 'best_suite_expansion_kit';
	protected $appVersionRequired = '5.6.1.2';
	protected $pkgVersion = '0.0.6';

	public function getPackageDescription() {
		return t("Learn to create applications that utilize Best Suite : Core");
	}

	public function getPackageName() {
		return t("Best Suite - Expansion Kit");
	}

	public function install() {

		$haveDPM = 0;
		$dpmUpToDate = 0;
		$packages = Package::getInstalledList();

		/* Make sure that we have the correct version of the Core */
		foreach ($packages as $_pkg) {
			$handle = $_pkg->getPackageHandle();
			if ($handle === "dashboard_page_managers") {
				$haveDPM = 1;
				$pkgVersion = $_pkg->getPackageVersion();
				if (version_compare($pkgVersion, "1.2.3") >= 0) {
					$dpmUpToDate = 1;
				}
			}
		}
		if ($haveDPM && $dpmUpToDate) {
			// We're good to go
			$pkg = parent::install();
			$this->installPageTypes($pkg);
			$this->installPages($pkg);
			$this->setupComposer($pkg);
			$this->registerWithBestSuiteCore($pkg);
		} else {
			// Abort installation
			$message = t("Requirements not met");
			if (!$haveDPM) {
				$message .= " - " . t("You must have Best Suite - Core installed");
			} else {
				if (!$dpmUpToDate) {
					$message .= " - " . t("Best Suite - Core must be version 1.2.3 or higher");
				}
			}
			throw new Exception($message);
		}
	}

	public function uninstall(){
		$bscHelper = Loader::helper("best_suite_core", "dashboard_page_managers");
		$bscHelper->removePackage($this->getPackageID());
		$sampleManager = Page::getByPath("/dashboard/best_suite/sample");
		if ($sampleManager && is_a($sampleManager, "Page")){
			$sampleManager->delete();
		}
		$sampleManager = Page::getByPath("/dashboard/composer/write-sample");
		if ($sampleManager && is_a($sampleManager, "Page")){
			$sampleManager->delete();
		}
		
		parent::uninstall();
	}
	
	/**
	 * @var $pkg Package
	 * @var $keepInternal bool */
	private function installPageTypes($pkg) {

		/*
		 *  We always want to keep the page for writing internal no matter
		 * what. Keep in mind, if you are not doing a custom view or controller
		 * then you don't actually need to install this page type, you can 
		 * just use the built in one from the core.
		 */
		$writeSamplePage = CollectionType::getByHandle('bs_write_sample_page');
		if (!is_object($writeSamplePage)) {
			$data = array(
				'ctHandle' => 'bs_write_sample_page',
				'ctName' => t('Write Sample Pages'),
				'ctIsInternal' => 1);
			$writeSamplePage = CollectionType::add($data, $pkg);
		}

		/*
		 * Now to install the actual page type that we will be editing.
		 */
		$sample = CollectionType::getByHandle('bs_sample');
		if (!is_object($sample)) {
			$data = array(
				'ctHandle' => 'bs_sample',
				'ctName' => t('Sample'));
			$sample = CollectionType::add($data, $pkg);
		}
	}
	
	private function installPages($pkg) {
		
		$pkgID = $pkg->getPackageID();

		/* We only need to do this if we are installing a custom editor.
		 * Otherwise, the built in is all you need. 
		 */
		$composer = Page::getByPath('/dashboard/composer');
		$writeSP = CollectionType::getByHandle('bs_write_sample_page');

		$data = array(
		    'cHandle' => "write-sample",
		    'cName' => t("Write a Sample Page"));
		$writeSP = $composer->add($writeSP, $data);

		$exNav = CollectionAttributeKey::getByHandle('exclude_nav');
		if ($exNav && is_a($exNav, "CollectionAttributeKey")) {
			$writeSP->setAttribute('exclude_nav', 1);
		}

		$exSI = CollectionAttributeKey::getByHandle('exclude_search_index');
		if ($exSI && is_a($exSI, "CollectionAttributeKey")) {
			$writeSP->setAttribute('exclude_search_index', 1);
		}

		$exPL = CollectionAttributeKey::getByHandle('exclude_page_list');
		if ($exPL && is_a($exPL, "CollectionAttributeKey")) {
			$writeSP->setAttribute('exclude_page_list', 1);
		}
		
		$icon = CollectionAttributeKey::getByHandle('icon_dashboard');
		if ($icon && is_a($icon, "CollectionAttributeKey")) {
			$writeSP->setAttribute('icon_dashboard', 'icon-pencil');
		}

		/* This is the page that will do the listing / searching */
		$bestSuiteParent = Page::getByPath("/dashboard/best_suite");
		$pageManagerCT = CollectionType::getByHandle("dashboard_page_manager");

		$data = array(
		    'cHandle' => "sample",
		    'cName' => t("Manage Sample Pages"));
		$samplePageManager = $bestSuiteParent->add($pageManagerCT, $data);
		
		$samplePageManager->setAttribute("dpm_page_type_handle", "bs_sample");
		
	}
	
	private function registerWithBestSuiteCore($pkg) {
		$bscH = Loader::helper("best_suite_core", "dashboard_page_managers");
		
		$sampleEditPage = Page::getByPath("/dashboard/composer/write-sample")->getCollectionID();
		$ctID = CollectionType::getByHandle("bs_sample")->getCollectionTypeID();
		
		/**
		 * This is where we let the core system know what we need it to do with
		 * our package. The options are
		 * 
		 * @pkgID = This package's ID
		 * @ctID	= The page type that we are going to manage. Only one can be
		 * added at a time, but if you have multiples, you can call this helper 
		 * as many times as you need.
		 * @hasCustomEditPage = If you need to have a custom view and controller, 
		 * then you would set this to true
		 * @hasCustomEditPageCID = The page ID for the custom edit page
		 * @hasCustomSearchInterface = This will let you customize the search 
		 * form and the results list.
		 * @customSearchInterfaceFolderName = The container directory that holds
		 * the search elements. This is relative to package_root/elements/
		 */
		
		$data = array(
		    "pkgID" => $pkg->getPackageID(),
		    "ctID" => $ctID,
		    "hasCustomEditPage" => 1,
		    "customEditPageCID" => $sampleEditPage,
		    "hasCustomSearchInterface" => 1,
		    "customSearchInterfaceFolderName" => "bs_expansion_kit"
		);
		$bscH->registerCollectionTypeDetails($data);
	}

	private function setupComposer($pkg) {
		
		$niSample = CollectionType::getByHandle("bs_sample");
		$niMC = $niSample->getMasterTemplate();
		
		/*
		 * There are three options for publishing location. 
		 * 
		 * First, this will let the page be published anywhere
		 */
		$niSample->saveComposerPublishTargetAll();
		/*
		 * Now published under pages of a certain type. 
		 * Pass in the CollectionType, not just the ID
		 */
//		$niSample->saveComposerPublishTargetPageType($ct);
		/*
		 * Use this if you only want to publish under one particular page
		 * Pass in the full Page object, not just the ID
		 */
//		$niSample->saveComposerPublishTargetPage($c);
		
		/*
		 * These are the attributes that will be editable in your application.
		 * If you have custom attributes then you can set them up here as well.
		 * Just make sure that they are installed before trying to add them or
		 * you will get errors.
		 * 
		 * For this sample, we're just using one that's installed with the core
		 */
		$sampleAtts = array();
		$metaKeywords = CollectionAttributeKey::getByHandle("meta_keywords");
		if ($metaKeywords && is_a($metaKeywords, "CollectionAttributeKey")){
			$sampleAtts[] = $metaKeywordsID = $metaKeywords->getAttributeKeyID();
		}
		
		// Now they're added to composer
		$niSample->saveComposerAttributeKeys($sampleAtts);

		// Page Content
		// This adds the block to the master template
		$bt = BlockType::getByHandle('content');
		$data = array('content' => "");
		$sampleText = $niMC->addBlock($bt, 'Main', $data);
		
		/*
		 * And now the composer data. These will be passed in to the block's
		 * updateBlockInformation function, as well.
		 * 
		 * @bName = Block name, used for getByName(). Kind of deprecated, only 
		 * really works with blocks in scrapbooks, global areas, or stacks. 
		 * This can cause issues with custom templates if you set this on 
		 * blocks that aren't in this section
		 * 
		 * @bFilename = Custom template that will be applied to the block
		 * 
		 * @bIncludeInComposer = Lets composer know this page is included
		 * 
		 * @cbFilename = The name that will appear in composer
		 */
		$composerData = array(
		    "bName" => "",
		    "bFileName" => "Sample Text",
		    "bIncludeInComposer" => 1,
		    "cbFilename" => t("Sample Text")
		);
		$sampleText->updateBlockComposerSettings($composerData);

		/*
		 * Now we need to make sure that all of our items are in the right order
		 * This is not really an issue if you are doing a completely custom form, 
		 * but if you are doing composer in the default view where it's all just
		 * looped over in the order set here.
		 * 
		 * The only arguments that each of the objects in the items array can 
		 * have is an Attribute Key ID, or a Block ID. Don't try to do both, 
		 * it will cause errors when saving. I'm redefining the obj each time
		 * to avoid accidentally doing that.
		 */
		$composerItems = array();

		$obj = new stdClass();
		$obj->akID = $metaKeywordsID;
		$composerItems[] = $obj;

		$obj = new stdClass();
		$obj->bID = $sampleText->getBlockID();
		$composerItems[] = $obj;

		$niSample->saveComposerContentItemOrder($composerItems);
		
	}

}
