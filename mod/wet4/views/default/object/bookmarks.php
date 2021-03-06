<?php
/**
 * Elgg bookmark view
 *
 * @package ElggBookmarks
 */

$full = elgg_extract('full_view', $vars, FALSE);
$bookmark = elgg_extract('entity', $vars, FALSE);
$lang = get_current_language();
if (!$bookmark) {
	return;
}

$owner = $bookmark->getOwnerEntity();
$owner_icon = elgg_view_entity_icon($owner, 'medium');
$categories = elgg_view('output/categories', $vars);

$link = elgg_view('output/url', array('href' => $bookmark->address));
if($bookmark->description3){
    $description = elgg_view('output/longtext', array('value' => gc_explode_translation($bookmark->description3, $lang), 'class' => 'pbl'));
}else{
$description = elgg_view('output/longtext', array('value' => $bookmark->description, 'class' => 'pbl'));
}

$owner_link = elgg_view('output/url', array(
	'href' => "bookmarks/owner/$owner->username",
	'text' => $owner->name,
	'is_trusted' => true,
));
$author_text = elgg_echo('byline', array($owner_link));

$date = elgg_view_friendly_time($bookmark->time_created);
//Nick - making the newer header 
$owner_link_final = '<div class="col-sm-12 object-header-avatar">'.$owner_icon. '<div class="object-header-name">'.$owner_link . $date .'</div></div>';

$comments_count = $bookmark->countComments();
//only display if there are commments
if ($comments_count != 0) {
	$text = elgg_echo("comments") . " ($comments_count)";
	$comments_link = elgg_view('output/url', array(
		'href' => $bookmark->getURL() . '#comments',
		'text' => $text,
		'is_trusted' => true,
	));
} else {
	$comments_link = '';
}

$metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'handler' => 'bookmarks',
	'sort_by' => 'priority',
	'class' => 'list-inline',
));

$subtitle = "$author_text $date $comments_link $categories";

// do not show the metadata and controls in widget view
if (elgg_in_context('widgets')) {
	$metadata = '';
}

if ($full && !elgg_in_context('gallery')) {

	$params = array(
		'entity' => $bookmark,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
	);
	$params = $params + $vars;
	$summary = elgg_view('object/elements/summary', $params);

	$bookmark_icon = '<i class="fa fa-link mrgn-rght-md"></i>';
	$body = <<<HTML
<div class="bookmark elgg-content mts">
	$bookmark_icon<span class="elgg-heading-basic mbs">$link</span>
	<div class="mrgn-tp-md">$description</div>
</div>
HTML;

	echo elgg_view('object/elements/full', array(
		'entity' => $bookmark,
		'icon' => $owner_link_final,
		'summary' => $summary,
		'body' => $body,
	));

} elseif (elgg_in_context('gallery')) {
	echo <<<HTML
<div class="bookmarks-gallery-item">
	<h3>$bookmark->title</h3>
	<p class='subtitle'>$owner_link $date</p>
</div>
HTML;
} else {
	// brief view
	$url = $bookmark->address;
	$display_text = $url;
	$excerpt = elgg_get_excerpt($bookmark->description);
	if ($excerpt) {
		$excerpt = " - $excerpt";
	}

	if (strlen($url) > 25) {
		$bits = parse_url($url);
		if (isset($bits['host'])) {
			$display_text = $bits['host'];
		} else {
			$display_text = elgg_get_excerpt($url, 100);
		}
	}

	$link = elgg_view('output/url', array(
		'href' => $bookmark->address,
		'text' => $display_text,
	));

	$content = '<i class="fa fa-link mrgn-rght-md"></i>' . "$link{$excerpt}";

    if(elgg_in_context('group_profile')){
        $metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'handler' => 'bookmarks',
	'sort_by' => 'priority',
	'class' => 'list-inline',
));
    }

	$params = array(
		'entity' => $bookmark,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'content' => $content,
	);
	$params = $params + $vars;
	$body = elgg_view('object/elements/summary', $params);
	
	echo elgg_view_image_block($owner_link_final, $body, array('class'=>'discussion-card'));
}
