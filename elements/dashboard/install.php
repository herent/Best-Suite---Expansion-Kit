<?php
defined("C5_EXECUTE") or die(_("Access Denied."));
$form = Loader::helper('form');
?>

<div>
     <h2><?php echo t("Best Suite Expansion Kit - Installation"); ?></h2>
     <p>
		<?php echo t("When creating a new package that utilizes the "
			. "Best Suite - Core system, you can choose to either keep "
			. "your new page types internal, or available for adding on "
			. "the front end."); ?>
     </p>
	<p>
		<?php echo t("Utilizing an install form like this allows you to "
			. "customize your package in many ways. The file is located at ") .
			"<br /><br /><strong>/package_root/elements/dashboard/install.php</strong><br /><br />" .
			t("Any form fields in that file will be passed in as an array to the "
				. "installation function."); ?>
     </p>
	<p>
		<?php echo t("If you would like your page type hidden, check the box below. "); ?>
     </p>
     <div class="control-group">
          <div class="controls">
			<label class="checkbox" style="font-weight: bold;swl">
				<?php echo $form->checkbox("keepInternal", 1, 0); ?>
				<?php echo t("Keep Page Types Internal?"); ?>
			</label>
          </div>
     </div>
</div>