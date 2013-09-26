<?
defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getByID(Loader::helper('security')->sanitizeInt($_REQUEST['cID']));
$btl = new BlockTypeList();
$blockTypes = $btl->getBlockTypeList();
$dsh = Loader::helper('concrete/dashboard');
$dashboardBlockTypes = array();
if ($dsh->inDashboard()) {
	$dashboardBlockTypes = BlockTypeList::getDashboardBlockTypes();
}
$blockTypes = array_merge($blockTypes, $dashboardBlockTypes);
if ($c->isMasterCollection()) {
	$bt = BlockType::getByHandle(BLOCK_HANDLE_PAGE_TYPE_OUTPUT_PROXY);
	$blockTypes[] = $bt;
}
if ($_REQUEST['tab']) {
	$_SESSION['panels_page_add_block_tab'] = $_REQUEST['tab'];
	$tab = $_REQUEST['tab'];
} else {
	$tab = $_SESSION['panels_page_add_block_tab'];
}

$ih = Loader::helper('concrete/interface');
$ci = Loader::helper('concrete/urls');

if (is_object($c) && !$c->isError()) {
	$cp = new Permissions($c);
	if ($cp->canEditPageContents()) { ?>

	<section>

	<div data-panel-menu="accordion" class="ccm-panel-header-accordion">
	<nav>
	<span></span>
	<ul class="ccm-panel-header-accordion-dropdown">
		<li><a data-panel-accordion-tab="blocks" <? if (!in_array($tab, array('clipboard', 'stacks', 'tiles'))) { ?>data-panel-accordion-tab-selected="true" <? } ?>><?=t('Blocks')?></a></li>
		<li><a data-panel-accordion-tab="clipboard" <? if ($tab == 'clipboard') { ?>data-panel-accordion-tab-selected="true" <? } ?>><?=t('Clipboard')?></a></li>
		<li><a data-panel-accordion-tab="stacks" <? if ($tab == 'stacks') { ?>data-panel-accordion-tab-selected="true" <? } ?>><?=t('Stacks')?></a></li>
		<li><a data-panel-accordion-tab="tiles" <? if ($tab == 'tiles') { ?>data-panel-accordion-tab-selected="true" <? } ?>><?=t('Gathering Tiles')?></a></li>
	</ul>
	</nav>
	</div>


		<?
		switch($tab) {

			case 'tiles': ?>

				Gathering tiles

			<?
				break;


			case 'stacks': ?>

				Stacks

			<?
				break;

			case 'clipboard': ?>

		<div id="ccm-panel-clipboard-list">
		<?

		$sp = Pile::getDefault();
		$contents = $sp->getPileContentObjects('date_desc');
		if (count($contents) == 0) { 
			print t('You have no items in your Clipboard.');
		}
		foreach($contents as $obj) { 
			$item = $obj->getObject();
			if (is_object($item)) {
				$bt = $item->getBlockTypeObject();
				?>
				<div class="ccm-panel-clipboard-item">
					<a href="javascript:void(0)"><i class="glyphicon glyphicon-trash"></i></a>
					<div class="ccm-panel-clipboard-item-inner">
					<?	
					try {
						$bv = new BlockView($item);
						$bv->render('scrapbook');
					} catch(Exception $e) {
						print BLOCK_NOT_AVAILABLE_TEXT;
					}	
					?>
					</div>
					<?=$bt->getBlockTypeName()?>
				</div>
			<? } ?>
		<? } ?>

		</div>			

			<?
		break;

		default: ?>

		<div class="ccm-panel-content-inner">

		<?
		$sets = BlockTypeSet::getList();
		$types = array();
		foreach($blockTypes as $bt) { 
		if (!$cp->canAddBlockType($bt)) {
			continue;
		}

		$btsets = $bt->getBlockTypeSets();
		foreach($btsets as $set) {
			$types[$set->getBlockTypeSetName()][] = $bt;
		}
		if (count($btsets) == 0) {
			$types['Other'][] = $bt;
		}
		}

		for ($i = 0; $i < count($sets); $i++) { 
		$set = $sets[$i];

		?>
		<div class="ccm-panel-add-block-set">
			<header><?=$set->getBlockTypeSetName()?></header>
			<ul>

			<? $blocktypes = $types[$set->getBlockTypeSetName()]; 
			foreach($blocktypes as $bt) { 

				$btIcon = $ci->getBlockTypeIconURL($bt);

				?>

			<li>
				<a class="ccm-add-block-draggable-block-type"  data-cID="<?=$c->getCollectionID()?>" data-block-type-handle="<?=$bt->getBlockTypeHandle()?>" data-dialog-title="<?=t('Add %s', $bt->getBlockTypeName())?>" data-dialog-width="<?=$bt->getBlockTypeInterfaceWidth()?>" data-dialog-height="<?=$bt->getBlockTypeInterfaceHeight()?>" data-has-add-template="<?=$bt->hasAddTemplate()?>" data-supports-inline-add="<?=$bt->supportsInlineAdd()?>" data-btID="<?=$bt->getBlockTypeID()?>" href="javascript:void(0)">
					<p><img src="<?=$btIcon?>" /><span><?=$bt->getBlockTypeName()?></span></p>
				</a>
			</li>

			<? } ?>
			</ul>	
		</div>

		<? } ?>


		<div class="ccm-panel-add-block-set">
			<header><?=t('Other')?></header>
			<ul>
			<? $blocktypes = $types['Other']; 
			foreach($blocktypes as $bt) { ?>

			<li data-block-type-sets="<?=$sets?>">
				<a data-add-block-drag-item="block" class="ccm-add-block-draggable-block-type"  data-cID="<?=$c->getCollectionID()?>" data-block-type-handle="<?=$bt->getBlockTypeHandle()?>" data-dialog-title="<?=t('Add %s', $bt->getBlockTypeName())?>" data-dialog-width="<?=$bt->getBlockTypeInterfaceWidth()?>" data-dialog-height="<?=$bt->getBlockTypeInterfaceHeight()?>" data-has-add-template="<?=$bt->hasAddTemplate()?>" data-supports-inline-add="<?=$bt->supportsInlineAdd()?>" data-btID="<?=$bt->getBlockTypeID()?>" href="javascript:void(0)"><p><img src="<?=$btIcon?>" /><span><?=$bt->getBlockTypeName()?></span></p></a>
			</li>

			<? } ?>
			</ul>	

		</div>

		</div>

		</section>

		<script type="text/javascript">

		$(function() {
			CCMEditMode.activateAddBlocksPanel();
		});
		</script>

		<? } ?>

	<? }
}
?>