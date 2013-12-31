<?php
defined('C5_EXECUTE') or die("Access Denied.");

/*
 * If this system needs anything custom as far as saving / editing in the 
 * controller, then override the methods from the core system. 
 * 
 * That's a bit out of scope for this package, especially since it will
 * be different for each system that someone builds. 
 * 
 * What is shown here is the base needed in order to get the system to work
 * with a custom editing form. 
 * 
 */

$pkg = Loader::package("dashboard_page_managers");
$pkgPath = $pkg->getPackagePath() . "/controllers/page_types/write_pm_page.php";
require_once $pkgPath;

class BsWriteSamplePagePageTypeController extends WritePmPagePageTypeController {

}
