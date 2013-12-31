<?php defined("C5_EXECUTE") or die(_("Access Denied."));
$c = Page::getCurrentPage();
?>
<div class="clear"></div>

<div class="sidebar" style="width: 23%; float: right;">
	<?php
	$a = new Area('Sidebar');
	$a->display($c);
	?>
</div>

<div class="body" style="width: 67%; float: left;">
	<div class="pageSection">
		<h1><?php echo $c->getCollectionName();?></h1>
		<p class="meta">
		<?php 
			echo t('Posted by %s on %s',
				$c->getVersionObject()->getVersionAuthorUserName(),
				$c->getCollectionDatePublic(DATE_APP_GENERIC_MDY_FULL));?>
		</p>
	</div>
	<hr>
	<div class="pageSection">
		<?php
		$a = new Area('Main');
		$a->display($c);
		?>
	</div>
</div>

<div class="spacer">&nbsp;</div>	