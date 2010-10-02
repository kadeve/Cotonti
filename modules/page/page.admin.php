<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=admin
[END_COT_EXT]
==================== */

/**
 * Pages manager & Queue of pages
 *
 * @package Cotonti
 * @version 0.7.0
 * @author Neocrome, Cotonti Team
 * @copyright Copyright (c) Cotonti Team 2008-2010
 * @license BSD
 */

(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('page', 'any');
cot_block($usr['isadmin']);

$t = new XTemplate(cot_skinfile('page.admin', 'module'));

cot_require('page');

$adminpath[] = array(cot_url('admin', 'm=page'), $L['Pages']);
$adminhelp = $L['adm_help_page'];

$id = cot_import('id', 'G', 'INT');

$d = cot_import('d', 'G', 'INT');
$d = empty($d) ? 0 : (int) $d;

$sorttype = cot_import('sorttype', 'R', 'ALP');
$sorttype = empty($sorttype) ? 'id' : $sorttype;
$sort_type = array(
	'id' => $L['Id'],
	'type' => $L['Type'],
	'key' => $L['Key'],
	'title' => $L['Title'],
	'desc' => $L['Description'],
	'text' => $L['Body'],
	'author' => $L['Author'],
	'ownerid' => $L['Owner'],
	'date' => $L['Date'],
	'begin' => $L['Begin'],
	'expire' => $L['Expire'],
	'rating' => $L['Rating'],
	'count' => $L['Hits'],
	'comcount' => $L['Comments'],//TODO: if comments plug not instaled this row generated error
	'file' => $L['adm_fileyesno'],
	'url' => $L['adm_fileurl'],
	'size' => $L['adm_filesize'],
	'filecount' => $L['adm_filecount']
);
$sqlsorttype = 'page_'.$sorttype;

$sortway = cot_import('sortway', 'R', 'ALP');
$sortway = empty($sortway) ? 'desc' : $sortway;
$sort_way = array(
	'asc' => $L['Ascending'],
	'desc' => $L['Descending']
);
$sqlsortway = $sortway;

$filter = cot_import('filter', 'R', 'ALP');
$filter = empty($filter) ? 'valqueue' : $filter;
$filter_type = array(
	'all' => $L['All'],
	'valqueue' => $L['adm_valqueue'],
	'validated' => $L['adm_validated']
);
if ($filter == 'all')
{
	$sqlwhere = "1 ";
}
elseif ($filter == 'valqueue')
{
	$sqlwhere = "page_state=1 ";
}
elseif ($filter == 'validated')
{
	$sqlwhere = "page_state<>1 ";
}

/* === Hook  === */
foreach (cot_getextplugins('admin.page.first') as $pl)
{
	include $pl;
}
/* ===== */

if ($a == 'validate')
{
	cot_check_xg();

	/* === Hook  === */
	foreach (cot_getextplugins('admin.page.validate') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$sql = cot_db_query("SELECT page_cat FROM $db_pages WHERE page_id='$id'");
	if ($row = cot_db_fetcharray($sql))
	{
		$usr['isadmin_local'] = cot_auth('page', $row['page_cat'], 'A');
		cot_block($usr['isadmin_local']);

		$sql = cot_db_query("UPDATE $db_pages SET page_state=0 WHERE page_id='$id'");
		$sql = cot_db_query("UPDATE $db_structure SET structure_pagecount=structure_pagecount+1 WHERE structure_code='".$row['page_cat']."' ");

		cot_log($L['Page'].' #'.$id.' - '.$L['adm_queue_validated'], 'adm');

		if ($cot_cache)
		{
			if ($cfg['cache_page'])
			{
				$cot_cache->page->clear('page/' . str_replace('.', '/', $cot_cat[$row['page_cat']]['path']));
			}
			if ($cfg['cache_index'])
			{
				$cot_cache->page->clear('index');
			}
		}

		cot_message('#'.$id.' - '.$L['adm_queue_validated']);
	}
	else
	{
		cot_die();
	}
}
elseif ($a == 'unvalidate')
{
	cot_check_xg();

	/* === Hook  === */
	foreach (cot_getextplugins('admin.page.unvalidate') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$sql = cot_db_query("SELECT page_cat FROM $db_pages WHERE page_id='$id'");
	if ($row = cot_db_fetcharray($sql))
	{
		$usr['isadmin_local'] = cot_auth('page', $row['page_cat'], 'A');
		cot_block($usr['isadmin_local']);

		$sql = cot_db_query("UPDATE $db_pages SET page_state=1 WHERE page_id='$id'");
		$sql = cot_db_query("UPDATE $db_structure SET structure_pagecount=structure_pagecount-1 WHERE structure_code='".$row['page_cat']."' ");

		cot_log($L['Page'].' #'.$id.' - '.$L['adm_queue_unvalidated'], 'adm');

		if ($cot_cache)
		{
			if ($cfg['cache_page'])
			{
				$cot_cache->page->clear('page/' . str_replace('.', '/', $cot_cat[$row['page_cat']]['path']));
			}
			if ($cfg['cache_index'])
			{
				$cot_cache->page->clear('index');
			}
		}

		cot_message('#'.$id.' - '.$L['adm_queue_unvalidated']);
	}
	else
	{
		cot_die();
	}
}
elseif ($a == 'delete')
{
	cot_check_xg();

	/* === Hook  === */
	foreach (cot_getextplugins('admin.page.delete') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$sql = cot_db_query("SELECT * FROM $db_pages WHERE page_id='$id' LIMIT 1");
	if ($row = cot_db_fetchassoc($sql))
	{
		if ($cfg['trash_page'])
		{
			cot_trash_put('page', $L['Page']." #".$id." ".$row['page_title'], $id, $row);
		}
		if ($row['page_state'] != 1)
		{
			$sql = cot_db_query("UPDATE $db_structure SET structure_pagecount=structure_pagecount-1 WHERE structure_code='".$row['page_cat']."' ");
		}

		$id2 = 'p'.$id;
		$sql = cot_db_query("DELETE FROM $db_pages WHERE page_id='$id'");
		$sql = cot_db_query("DELETE FROM $db_ratings WHERE rating_code='$id2'");
		$sql = cot_db_query("DELETE FROM $db_rated WHERE rated_code='$id2'");
		$sql = cot_db_query("DELETE FROM $db_com WHERE com_code='$id2'");//TODO: if comments plug not instaled this row generated error

		cot_log($L['Page'].' #'.$id.' - '.$L['Deleted'], 'adm');

		/* === Hook === */
		foreach (cot_getextplugins('admin.page.delete.done') as $pl)
		{
			include $pl;
		}
		/* ===== */

		if ($cot_cache)
		{
			if ($cfg['cache_page'])
			{
				$cot_cache->page->clear('page/' . str_replace('.', '/', $cot_cat[$row['page_cat']]['path']));
			}
			if ($cfg['cache_index'])
			{
				$cot_cache->page->clear('index');
			}
		}

		cot_message('#'.$id.' - '.$L['adm_queue_deleted']);
	}
	else
	{
		cot_die();
	}
}
elseif ($a == 'update_cheked')
{
	$paction = cot_import('paction', 'P', 'TXT');

	if ($paction == $L['Validate'] && is_array($_POST['s']))
	{
		cot_check_xp();
		$s = cot_import('s', 'P', 'ARR');

		$perelik = '';
		$notfoundet = '';
		foreach ($s as $i => $k)
		{
			if ($s[$i] == '1' || $s[$i] == 'on')
			{
				/* === Hook  === */
				foreach (cot_getextplugins('admin.page.cheked_validate') as $pl)
				{
					include $pl;
				}
				/* ===== */

				$sql = cot_db_query("SELECT * FROM $db_pages WHERE page_id='".$i."'");
				if ($row = cot_db_fetcharray($sql))
				{
					$id = $row['page_id'];
					$usr['isadmin_local'] = cot_auth('page', $row['page_cat'], 'A');
					cot_block($usr['isadmin_local']);

					$sql = cot_db_query("UPDATE $db_pages SET page_state=0 WHERE page_id='".$id."'");
					$sql = cot_db_query("UPDATE $db_structure SET structure_pagecount=structure_pagecount+1 WHERE structure_code='".$row['page_cat']."' ");

					cot_log($L['Page'].' #'.$id.' - '.$L['adm_queue_validated'], 'adm');

					if ($cot_cache && $cfg['cache_page'])
					{
						$cot_cache->page->clear('page/' . str_replace('.', '/', $cot_cat[$row['page_cat']]['path']));
					}

					$perelik .= '#'.$id.', ';
				}
				else
				{
					$notfoundet .= '#'.$id.' - '.$L['Error'].'<br  />';
				}
			}
		}

		if ($cot_cache && $cfg['cache_index'])
		{
			$cot_cache->page->clear('index');
		}

		if (!empty($perelik))
		{
			cot_message($notfoundet.$perelik.' - '.$L['adm_queue_validated']);
		}
	}
	elseif ($paction == $L['Delete'] && is_array($_POST['s']))
	{
		cot_check_xp();
		$s = cot_import('s', 'P', 'ARR');

		$perelik = '';
		$notfoundet = '';
		foreach ($s as $i => $k)
		{
			if ($s[$i] == '1' || $s[$i] == 'on')
			{
				/* === Hook  === */
				foreach (cot_getextplugins('admin.page.cheked_delete') as $pl)
				{
					include $pl;
				}
				/* ===== */

				$sql = cot_db_query("SELECT * FROM $db_pages WHERE page_id='".$i."' LIMIT 1");
				if ($row = cot_db_fetchassoc($sql))
				{
					$id = $row['page_id'];
					if ($cfg['trash_page'])
					{
						cot_trash_put('page', $L['Page'].' #'.$id.' '.$row['page_title'], $id, $row);
					}
					if ($row['page_state'] != 1)
					{
						$sql = cot_db_query("UPDATE $db_structure SET structure_pagecount=structure_pagecount-1 WHERE structure_code='".$row['page_cat']."' ");
					}

					$id2 = 'p'.$id;
					$sql = cot_db_query("DELETE FROM $db_pages WHERE page_id='$id'");
					$sql = cot_db_query("DELETE FROM $db_ratings WHERE rating_code='$id2'");
					$sql = cot_db_query("DELETE FROM $db_rated WHERE rated_code='$id2'");
					$sql = cot_db_query("DELETE FROM $db_com WHERE com_code='$id2'");//TODO: if comments plug not instaled this row generated error

					cot_log($L['Page'].' #'.$id.' - '.$L['Deleted'],'adm');

					if ($cot_cache && $cfg['cache_page'])
					{
						$cot_cache->page->clear('page/' . str_replace('.', '/', $cot_cat[$row['page_cat']]['path']));
					}

					/* === Hook === */
					foreach (cot_getextplugins('admin.page.delete.done') as $pl)
					{
						include $pl;
					}
					/* ===== */
					$perelik .= '#'.$id.', ';
				}
				else
				{
					$notfoundet .= '#'.$id.' - '.$L['Error'].'<br  />';
				}
			}
		}

		if ($cot_cache && $cfg['cache_index'])
		{
			$cot_cache->page->clear('index');
		}

		if (!empty($perelik))
		{
			cot_message($notfoundet.$perelik.' - '.$L['adm_queue_deleted']);
		}
	}
}

$totalitems = cot_db_result(cot_db_query("SELECT COUNT(*) FROM $db_pages WHERE ".$sqlwhere), 0, 0);
$pagenav = cot_pagenav('admin', 'm=page&sorttype='.$sorttype.'&sortway='.$sortway.'&filter='.$filter, $d, $totalitems, $cfg['maxrowsperpage'], 'd', '', $cfg['jquery'] && $cfg['turnajax']);

$sql = cot_db_query("SELECT p.*, u.user_name, u.user_avatar
	FROM $db_pages as p
	LEFT JOIN $db_users AS u ON u.user_id=p.page_ownerid
	WHERE $sqlwhere
		ORDER BY $sqlsorttype $sqlsortway
		LIMIT $d, ".$cfg['maxrowsperpage']);

$ii = 0;
/* === Hook - Part1 : Set === */
$extp = cot_getextplugins('admin.page.loop');
/* ===== */
while ($row = cot_db_fetcharray($sql))
{
	if ($row['page_type'] == 0)
	{
		$page_type = 'BBcode';
	}
	elseif ($row['page_type'] == 1)
	{
		$page_type = 'HTML';
	}
	elseif ($row['page_type'] == 2)
	{
		$page_type = 'PHP';
	}
	$page_urlp = empty($row['page_alias']) ? 'id='.$row['page_id'] : 'al='.$row['page_alias'];
	$row['page_begin_noformat'] = $row['page_begin'];
	$row['page_pageurl'] = cot_url('page', $page_urlp);
	$catpath = cot_build_catpath($row['page_cat']);
	$row['page_fulltitle'] = $catpath.' '.$cfg['separator'].' <a href="'.$row['page_pageurl'].'">'.htmlspecialchars($row['page_title']).'</a>';
	$sql4 = cot_db_query("SELECT SUM(structure_pagecount) FROM $db_structure WHERE structure_path LIKE '".$cot_cat[$row["page_cat"]]['rpath']."%' ");
	$sub_count = cot_db_result($sql4, 0, "SUM(structure_pagecount)");
	$row['page_file'] = intval($row['page_file']);
	if (!empty($row['page_url']) && $row['page_file'] > 0)
	{
		$dotpos = mb_strrpos($row['page_url'], '.') + 1;
		$fileex = mb_strtolower(mb_substr($row['page_url'], $dotpos, 5));
		$row['page_fileicon'] = 'images/pfs/'.$fileex.'.gif';
		if (!file_exists($row['page_fileicon']))
		{
			$row['page_fileicon'] = 'images/admin/page.gif';
		}
		$row['page_fileicon'] = '<img src="'.$row['page_fileicon'].'" alt="'.$fileex.'" />';
	}
	else
	{
		$row['page_fileicon'] = '';
	}

	$t->assign(array(
		'ADMIN_PAGE_ID' => $row['page_id'],
		'ADMIN_PAGE_ID_URL' => cot_url('page', 'id='.$row['page_id']),
		'ADMIN_PAGE_URL' => $row['page_pageurl'],
		'ADMIN_PAGE_TITLE' => $row['page_fulltitle'],
		'ADMIN_PAGE_SHORTTITLE' => htmlspecialchars($row['page_title']),
		'ADMIN_PAGE_TYPE' => $page_type,
		'ADMIN_PAGE_DESC' => htmlspecialchars($row['page_desc']),
		'ADMIN_PAGE_AUTHOR' => htmlspecialchars($row['page_author']),
		'ADMIN_PAGE_OWNER' => cot_build_user($row['page_ownerid'], htmlspecialchars($row['user_name'])),
		'ADMIN_PAGE_OWNER_AVATAR' => cot_build_userimage($row['user_avatar'], 'avatar'),
		'ADMIN_PAGE_DATE' => date($cfg['dateformat'], $row['page_date'] + $usr['timezone'] * 3600),
		'ADMIN_PAGE_BEGIN' => date($cfg['dateformat'], $row['page_begin'] + $usr['timezone'] * 3600),
		'ADMIN_PAGE_EXPIRE' => date($cfg['dateformat'], $row['page_expire'] + $usr['timezone'] * 3600),
		'ADMIN_PAGE_ADMIN_COUNT' => $row['page_count'],
		'ADMIN_PAGE_KEY' => htmlspecialchars($row['page_key']),
		'ADMIN_PAGE_ALIAS' => htmlspecialchars($row['page_alias']),
		'ADMIN_PAGE_FILE' => $cot_yesno[$row['page_file']],
		'ADMIN_PAGE_FILE_BOOL' => $row['page_file'],
		'ADMIN_PAGE_FILE_URL' => $row['page_url'],
		'ADMIN_PAGE_FILE_URL_FOR_DOWNLOAD' => cot_url('page', 'id='.$row['page_id'].'&a=dl'),
		'ADMIN_PAGE_FILE_NAME' => basename($row['page_url']),
		'ADMIN_PAGE_FILE_SIZE' => $row['page_size'],
		'ADMIN_PAGE_FILE_COUNT' => $row['page_filecount'],
		'ADMIN_PAGE_FILE_ICON' => $row['page_fileicon'],
		'ADMIN_PAGE_URL_FOR_VALIDATED' => cot_url('admin', 'm=page&a=validate&id='.$row['page_id'].'&d='.$d.'&'.cot_xg()),
		'ADMIN_PAGE_URL_FOR_DELETED' => cot_url('admin', 'm=page&a=delete&id='.$row['page_id'].'&d='.$d.'&'.cot_xg()),
		'ADMIN_PAGE_URL_FOR_EDIT' => cot_url('page', 'm=edit&id='.$row['page_id'].'&r=adm'),
		'ADMIN_PAGE_ODDEVEN' => cot_build_oddeven($ii),
		'ADMIN_PAGE_CAT_URL' => cot_url('page', 'c='.$row['page_cat']),
		'ADMIN_PAGE_CAT' => $row['page_cat'],
		'ADMIN_PAGE_CAT_TITLE' => $cot_cat[$row['page_cat']]['title'],
		'ADMIN_PAGE_CATPATH' => $catpath,
		'ADMIN_PAGE_CATDESC' => $cot_cat[$row['page_cat']]['desc'],
		'ADMIN_PAGE_CATICON' => $cot_cat[$row['page_cat']]['icon'],
		'ADMIN_PAGE_CAT_COUNT' => $sub_count
	));

	// Extra fields for structure
	foreach ($cot_extrafields['structure'] as $row_c)
	{
		$uname = strtoupper($row_c['field_name']);
		$t->assign('ADMIN_PAGE_CAT_'.$uname.'_TITLE', isset($L['structure_'.$row_c['field_name'].'_title']) ?  $L['structure_'.$row_c['field_name'].'_title'] : $row_c['field_description']);
		$t->assign('ADMIN_PAGE_CAT_'.$uname, cot_build_extrafields_data('structure', $row_c['field_type'], $row_c['field_name'], $cot_cat[$row['page_cat']][$row_c['field_name']]));
	}

	// Extra fields for pages
	foreach ($cot_extrafields['pages'] as $row_p)
	{
		$uname = strtoupper($row_p['field_name']);
		$t->assign('ADMIN_PAGE_'.$uname.'_TITLE', isset($L['page_'.$row_p['field_name'].'_title']) ?  $L['page_'.$row_p['field_name'].'_title'] : $row_p['field_description']);
		$t->assign('ADMIN_PAGE_'.$uname, cot_build_extrafields_data('page', $row_p['field_type'], $row_p['field_name'], $row['page_'.$row_p['field_name']]));
	}

	switch($row['page_type'])
	{
		case 2:
			if ($cfg['allowphp_pages'] && $cfg['allowphp_override'])
			{
				ob_start();
				eval($row['page_text']);
				$t->assign('ADMIN_PAGE_TEXT', ob_get_clean());
			}
			else
			{
				$t->assign('ADMIN_PAGE_TEXT', 'The PHP mode is disabled for pages.<br />Please see the administration panel, then "Configuration", then "Parsers".');
			}
		break;

		case 1:
			$row_more = ((int)$textlength > 0) ? cot_string_truncate($row['page_text'], $textlength) : cot_cut_more($row['page_text']);
			$t->assign('ADMIN_PAGE_TEXT', $row['page_text']);
		break;

		default:
			if($cfg['parser_cache'])
			{
				if(empty($row['page_html']))
				{
					$row['page_html'] = cot_parse(htmlspecialchars($row['page_text']), $cfg['parsebbcodepages'], $cfg['parsesmiliespages'], 1);
					cot_db_query("UPDATE $db_pages SET page_html = '".cot_db_prep($row['page_html'])."' WHERE page_id = " . $row['page_id']);
				}
				$row['page_html'] = ($cfg['parsebbcodepages']) ?  $row['page_html'] : htmlspecialchars($row['page_text']);
				$row_more = ((int)$textlength>0) ? cot_string_truncate($row['page_html'], $textlength) : cot_cut_more($row['page_html']);
				$row['page_html'] = cot_post_parse($row['page_html'], 'pages');
				$t->assign('ADMIN_PAGE_TEXT', $row['page_html']);
			}
			else
			{
				$row['page_html'] = cot_parse(htmlspecialchars($row['page_text']), $cfg['parsebbcodepages'], $cfg['parsesmiliespages'], 1);
				$row_more = ((int)$textlength>0) ? cot_string_truncate($row['page_html'], $textlength) : cot_cut_more($row['page_html']);
				$row['page_html'] = cot_post_parse($row['page_html'], 'pages');
				$t->assign('ADMIN_PAGE_TEXT', $row['page_html']);
			}
		break;
	}

	/* === Hook - Part2 : Include === */
	foreach ($extp as $pl)
	{
		include $pl;
	}
	/* ===== */

	$t->parse('MAIN.PAGE_ROW');
	$ii++;
}

$is_row_empty = (cot_db_numrows($sql) == 0) ? true : false ;

$totaldbpages = cot_db_rowcount($db_pages);
$sql = cot_db_query("SELECT COUNT(*) FROM $db_pages WHERE page_state=1");
$sys['pagesqueued'] = cot_db_result($sql, 0, 'COUNT(*)');

$t->assign(array(
	'ADMIN_PAGE_URL_CONFIG' => cot_url('admin', 'm=config&n=edit&o=core&p=page'),
	'ADMIN_PAGE_URL_ADD' => cot_url('page', 'm=add'),
	'ADMIN_PAGE_URL_EXTRAFIELDS' => cot_url('admin', 'm=extrafields&n=page'),
	'ADMIN_PAGE_FORM_URL' => cot_url('admin', 'm=page&a=update_cheked&sorttype='.$sorttype.'&sortway='.$sortway.'&filter='.$filter.'&d='.$d),
	'ADMIN_PAGE_ORDER' => cot_selectbox($sorttype, 'sorttype', array_keys($sort_type), array_values($sort_type), false),
	'ADMIN_PAGE_WAY' => cot_selectbox($sortway, 'sortway', array_keys($sort_way), array_values($sort_way), false),
	'ADMIN_PAGE_FILTER' => cot_selectbox($filter, 'filter', array_keys($filter_type), array_values($filter_type), false),
	'ADMIN_PAGE_TOTALDBPAGES' => $totaldbpages,
	'ADMIN_PAGE_PAGINATION_PREV' => $pagenav['prev'],
	'ADMIN_PAGE_PAGNAV' => $pagenav['main'],
	'ADMIN_PAGE_PAGINATION_NEXT' => $pagenav['next'],
	'ADMIN_PAGE_TOTALITEMS' => $totalitems,
	'ADMIN_PAGE_ON_PAGE' => $ii
));

cot_display_messages($t);

/* === Hook  === */
foreach (cot_getextplugins('admin.page.tags') as $pl)
{
	include $pl;
}
/* ===== */

$t->parse('MAIN');
if (COT_AJAX)
{
	$t->out('MAIN');
}
else
{
	$adminmain = $t->text('MAIN');
}

?>