<?php

defined('C5_EXECUTE') or die("Access Denied.");

class BsSamplePagesListBlockController extends Concrete5_Controller_Block_PageList {

	protected $btTable = 'btBsSamplePageList';

	public function getBlockTypeDescription() {
		return t("List Sample Pages based on which Sample List page they are under.");
	}

	public function getBlockTypeName() {
		return t("Sample Page List");
	}

	public function add() {
		Loader::model("collection_types");
		$c = Page::getCurrentPage();
		$uh = Loader::helper('concrete/urls');
		//	echo $rssUrl;
		$this->set('c', $c);
		$this->set('uh', $uh);
		$this->set('bt', BlockType::getByHandle('bs_sample_pages_list'));
		$this->set('displayAliases', true);
		$pl = new PageList();
		$pl->sortByName();
		$pl->filterByCollectionTypeHandle("bs_sample_list");
		$publishLocations = $pl->get();
		$this->set("publishLocations", $publishLocations);
	}

	public function edit() {
		$b = $this->getBlockObject();
		$bCID = $b->getBlockCollectionID();
		$bID = $b->getBlockID();
		$this->set('bID', $bID);
		$uh = Loader::helper('concrete/urls');
		$this->set('uh', $uh);
		$this->set('bt', BlockType::getByHandle('bs_sample_pages_list'));
		$pl = new PageList();
		$pl->sortByName();
		$pl->filterByCollectionTypeHandle("bs_sample_list");
		$publishLocations = $pl->get();
		$this->set("publishLocations", $publishLocations);
		if (!$this->cParentIDs) {
			$this->set('cParentID', 0);
			$this->set('multipleLocations', false);
		} else {
			$selectedLocations = explode(",", $this->cParentIDs);
			if ($selectedLocations) {
				if (count($selectedLocations) == 1) {
					$c = Page::getCurrentPage();
					if ($c->getCollectionID() === $this->cParentIDs) {
						$this->set('multipleLocations', false);
						$this->set('cParentID', $this->cParentIDs);
						$this->set('selectedLocations', $selectedLocations);
					} else {
						$this->set('multipleLocations', true);
						$this->set('selectedLocations', $selectedLocations);
					}
				} else {
					$this->set('multipleLocations', true);
					$this->set('selectedLocations', $selectedLocations);
					$this->set('cParentID', $this->cParentIDs);
				}
			}
		}
	}

	function save($args) {
		// If we've gotten to the process() function for this class, we assume that we're in
		// the clear, as far as permissions are concerned (since we check permissions at several
		// points within the dispatcher)
		$db = Loader::db();

		$bID = $this->bID;
		$c = $this->getCollectionObject();
		if (is_object($c)) {
			$this->cID = $c->getCollectionID();
		}

		$args['num'] = ($args['num'] > 0) ? $args['num'] : 0;
		
		$args['cThis'] = ($args['cParentIDs'] == $this->cID || $args['cThis'] == 1) ? '1' : '0';
		
		$args['cParentIDs'] = ($args['cParentIDs'] == 'OTHER') ? implode(",", $args['cParentIDsMulti']) : $args['cParentIDs'];
		if (!$args['cParentIDs']) {
			$args['cParentIDs'] = 0;
		}
		
		$args['truncateSummaries'] = ($args['truncateSummaries']) ? '1' : '0';
		$args['displayFeaturedOnly'] = ($args['displayFeaturedOnly']) ? '1' : '0';
		$args['displayAliases'] = ($args['displayAliases']) ? '1' : '0';
		$args['truncateChars'] = intval($args['truncateChars']);
		$args['paginate'] = intval($args['paginate']);
		$args['rss'] = intval($args['rss']);
		$args['ctID'] = intval($args['ctID']);

		if ($this->btTable) {
			$db = Loader::db();
			$columns = $db->GetCol('show columns from `' . $this->btTable . '`'); // I have no idea why getAttributeNames isn't working anymore.
			$this->record = new BlockRecord($this->btTable);
			$this->record->bID = $this->bID;
			foreach ($columns as $key) {
				if (isset($args[$key])) {
					$this->record->{$key} = $args[$key];
				}
			}
			$this->record->Replace();
			if ($this->cacheBlockRecord() && ENABLE_BLOCK_CACHE) {
				$record = serialize($this->record);
				$db = Loader::db();
				$db->Execute('update Blocks set btCachedBlockRecord = ? where bID = ?', array($record, $this->bID));
			}
		}
	}

	public function view() {
		$cArray = $this->getPages();
		$nh = Loader::helper('navigation');
		$this->set('nh', $nh);
		$this->set('cArray', $cArray); //Legacy (pre-5.4.2)
		$this->set('pages', $cArray); //More descriptive variable name (introduced in 5.4.2)
		//RSS...
		$showRss = false;
		$rssIconSrc = '';
		$rssInvisibleLink = '';
		if ($this->rss) {
			$showRss = true;
			$rssIconSrc = Loader::helper('concrete/urls')->getBlockTypeAssetsURL(BlockType::getByID($this->getBlockObject()->getBlockTypeID()), 'rss.png');
			//DEV NOTE: Ideally we'd set rssUrl here, but we can't because for some reason calling $this->getBlockObject() here doesn't load all info properly, and then the call to $this->getRssUrl() fails when it tries to get the area handle of the block.
		}
		$this->set('showRss', $showRss);
		$this->set('rssIconSrc', $rssIconSrc);

		//Pagination...
		$showPagination = false;
		$paginator = null;
		$pl = $this->get('pl'); //Terrible horrible hacky way to get the $pl object set in $this->getPages() -- we need to do it this way for backwards-compatibility reasons
		if ($this->paginate && $this->num > 0 && is_object($pl)) {
			$description = $pl->getSummary();
			if ($description->pages > 1) {
				$showPagination = true;
				$paginator = $pl->getPagination();
			}
		}
		$this->set('showPagination', $showPagination);
		$this->set('paginator', $paginator);
	}

	public function getPageList() {
		Loader::model('page_list');
		$db = Loader::db();
		$bID = $this->bID;
		if ($this->bID) {
			$q = "select num, cParentIDs, cThis, orderBy, ctID, displayAliases, rss from btBsSamplePageList where bID = '$bID'";
			$r = $db->query($q);
			if ($r) {
				$row = $r->fetchRow();
			}
		} else {
			$row['num'] = $this->num;
			$row['cParentIDs'] = $this->cParentIDs;
			$row['cThis'] = $this->cThis;
			$row['orderBy'] = $this->orderBy;
			$row['ctID'] = $this->ctID;
			$row['rss'] = $this->rss;
			$row['displayAliases'] = $this->displayAliases;
		}


		$pl = new PageList();
		$pl->setNameSpace('b' . $this->bID);

		$cArray = array();

		switch ($row['orderBy']) {
			case 'display_asc':
				$pl->sortByDisplayOrder();
				break;
			case 'display_desc':
				$pl->sortByDisplayOrderDescending();
				break;
			case 'chrono_asc':
				$pl->sortByPublicDate();
				break;
			case 'alpha_asc':
				$pl->sortByName();
				break;
			case 'alpha_desc':
				$pl->sortByNameDescending();
				break;
			default:
				$pl->sortByPublicDateDescending();
				break;
		}

		$num = (int) $row['num'];

		$pl->setItemsPerPage($num);

		$c = Page::getCurrentPage();
		if (is_object($c)) {
			$this->cID = $c->getCollectionID();
		}

		Loader::model('attribute/categories/collection');
		if ($this->displayFeaturedOnly == 1) {
			$cak = CollectionAttributeKey::getByHandle('is_featured');
			if (is_object($cak)) {
				$pl->filterByIsFeatured(1);
			}
		}
		if (!$row['displayAliases']) {
			$pl->filterByIsAlias(0);
		}
		$pl->filter('cvName', '', '!=');

		if ($row['ctID']) {
			$pl->filterByCollectionTypeID($row['ctID']);
		}

		$columns = $db->MetaColumns(CollectionAttributeKey::getIndexedSearchTable());
		if (isset($columns['AK_EXCLUDE_PAGE_LIST'])) {
			$pl->filter(false, '(ak_exclude_page_list = 0 or ak_exclude_page_list is null)');
		}

		if ($row['cParentIDs'] && strlen($row['cParentIDs'])>0) {
			$selectedLocations = explode(",", $row['cParentIDs']);
			if ($selectedLocations) {
				if (count($selectedLocations) == 1) {
					$cParentID = ($row['cThis']) ? $this->cID : $selectedLocations[0];
					$pl->filterByParentID($cParentID);
				} else {
					$pl->filterByParentID($selectedLocations);
				}
			}
		}
		return $pl;
	}

	public function getPages() {
		$pl = $this->getPageList();

		if ($pl->getItemsPerPage() > 0) {
			$pages = $pl->getPage();
		} else {
			$pages = $pl->get();
		}
		$this->set('pl', $pl);
		return $pages;
	}

}
