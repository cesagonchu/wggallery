<{include file='db:wggallery_header.tpl'}>

<{if $albums}>
	<div class='panel panel-<{$panel_type}>'>
		<div class='panel-heading wgg-cats-header'><{$index_alb_title}></div>
		<div class='panel-body'>
			<{foreach name=album item=album from=$albums}>
                <{include file='db:wggallery_albumitem_hovereffectideas.tpl' album=$album}>
			<{/foreach}>
			<{if $pagenav_albums}>
				<div class='xo-pagenav floatright'><{$pagenav_albums}></div>
				<div class='clear spacer'></div>
			<{/if}>
		</div>
	</div>
<{/if}>
<{if $categories}>
	<div class='panel panel-<{$panel_type}>'>
		<div class='panel-heading wgg-cats-header'><{$index_cats_title}></div>
		<div class='panel-body'>
			<{foreach name=category item=category from=$categories}>
                <{if $category.newrow}><div class="grid"><{/if}>
                <{include file='db:wggallery_categoryitem_hovereffectideas.tpl' category=$category}>
                <{if $category.linebreak}></div><div class='clear'>&nbsp;</div><{/if}>
			<{/foreach}>
			<{if $pagenav_cats}>
				<div class='xo-pagenav floatright'><{$pagenav_cats}></div>
				<div class='clear spacer'></div>
			<{/if}>
		</div>
	</div>
<{/if}>

<{if $alb_for_id}>
	<div class='clear'>&nbsp;</div>
	<div class='wgg-goback'>
		<a class='btn btn-default wgg-btn' href='index.php?op=list&amp;subm_id=<{$subm_id}>' title='<{$smarty.const._CO_WGGALLERY_BACK}>'>
			<img class='wgg-btn-icon' src='<{$wggallery_icon_url_16}>/back.png' alt='<{$smarty.const._CO_WGGALLERY_BACK}>' /><{$smarty.const._CO_WGGALLERY_BACK}>
		</a>
	</div>
<{/if}>	

<div class='clear'>&nbsp;</div>

<{include file='db:wggallery_footer.tpl'}>
