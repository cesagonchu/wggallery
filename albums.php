<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * wgGallery module for xoops
 *
 * @copyright      module for xoops
 * @license        GPL 2.0 or later
 * @package        wggallery
 * @since          1.0
 * @min_xoops      2.5.7
 * @author         Wedega - Email:<webmaster@wedega.com> - Website:<https://wedega.com>
 * @version        $Id: 1.0 albums.php 1 Mon 2018-03-19 10:04:50Z XOOPS Project (www.xoops.org) $
 */
include __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = 'wggallery_albums_default.tpl';
include_once XOOPS_ROOT_PATH .'/header.php';

$op       = XoopsRequest::getString('op', 'list');
$albId    = XoopsRequest::getInt('alb_id', 0);
$albPid   = XoopsRequest::getInt('alb_pid');
$albSubm  = XoopsRequest::getInt('alb_submitter');
$albPid   = XoopsRequest::getInt('alb_pid');

if (_CANCEL === XoopsRequest::getString('cancel', 'none')) {
	$op = 'list';
}
if (_CO_WGGALLERY_FORM_SUBMIT_SUBMITUPLOAD === XoopsRequest::getString('submit_upload', 'none')) {
	$redir = 'upload';
}

// Define Stylesheet
$GLOBALS['xoTheme']->addStylesheet( $style, null );
$GLOBALS['xoTheme']->addStylesheet( WGGALLERY_URL . '/assets/css/style_default.css' );

// add scripts 
$GLOBALS['xoTheme']->addScript( XOOPS_URL . '/modules/wggallery/assets/js/admin.js' );

// assign vars
$GLOBALS['xoopsTpl']->assign('wggallery_icon_url_16', WGGALLERY_ICONS_URL . '/16');
$GLOBALS['xoopsTpl']->assign('wggallery_icon_url_32', WGGALLERY_ICONS_URL . '/32');
$GLOBALS['xoopsTpl']->assign('gallery_target', $wggallery->getConfig('gallery_target', true));

$keywords = array();

switch($op) {
	case 'list':
	default:
		// add scripts 
		$GLOBALS['xoTheme']->addScript( XOOPS_URL . '/modules/wggallery/assets/js/jquery-ui.min.js' );
		$GLOBALS['xoTheme']->addScript( XOOPS_URL . '/modules/wggallery/assets/js/sortable.js' );
		$GLOBALS['xoTheme']->addScript( XOOPS_URL . '/modules/wggallery/assets/js/jquery.mjs.nestedSortable.js' );
		$GLOBALS['xoTheme']->addStylesheet( WGGALLERY_URL . '/assets/css/nestedsortable.css' );

		$start = XoopsRequest::getInt('start', 0);
		$limit = XoopsRequest::getInt('limit', $wggallery->getConfig('adminpager'));
        $crAlbums = new CriteriaCompo();
        if (!$permissionsHandler->permGlobalSubmit()) {
            $crAlbums->add(new Criteria('alb_state', WGGALLERY_STATE_ONLINE_VAL));
        }
		$crAlbums->add(new Criteria('alb_pid', $albPid));
        $crAlbums->setStart( $start );
		$crAlbums->setLimit( $limit );
        $crAlbums->setSort('alb_weight ASC, alb_date');
		$crAlbums->setOrder('DESC');
		$albumsCount = $albumsHandler->getCount($crAlbums);
		$albumsAll = $albumsHandler->getAll($crAlbums);
		$GLOBALS['xoopsTpl']->assign('albums_count', $albumsCount);
		$GLOBALS['xoopsTpl']->assign('wggallery_url', WGGALLERY_URL);
		$GLOBALS['xoopsTpl']->assign('wggallery_upload_url', WGGALLERY_UPLOAD_URL);
		// Table view albums
		if($albumsCount > 0) {
			foreach(array_keys($albumsAll) as $i) {
				$album = $albumsAll[$i]->getValuesAlbums();
                //check permissions
                $album['edit'] = $permissionsHandler->permAlbumEdit($albumsAll[$i]->getVar('alb_id'), $albumsAll[$i]->getVar('alb_submitter'));
				$keywords[] = $albumsAll[$i]->getVar('alb_name');
				$GLOBALS['xoopsTpl']->append('albums_list', $album);
				unset($album);
			}
			// Display Navigation
			if($albumsCount > $limit) {
				include_once XOOPS_ROOT_PATH .'/class/pagenav.php';
				$pagenav = new XoopsPageNav($albumsCount, $limit, $start, 'start', 'op=list&limit=' . $limit . '&alb_id=' . $albId . '&alb_pid=' . $albPid . '&alb_submitter=' . $albSubm);
				$GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav(4));
			}
		} else {
			$GLOBALS['xoopsTpl']->assign('error', _CO_WGGALLERY_THEREARENT_ALBUMS);
		}
		// add list for sorting
		$albumlist_sort = $albumsHandler->getListChildsOfCategory(0);
		// var_dump($albumlist_sort);
		$GLOBALS['xoopsTpl']->assign('albumlist_sort', $albumlist_sort);

		$GLOBALS['xoopsTpl']->assign('global_submit', $permissionsHandler->permGlobalSubmit());
		$pr_gallery = $gallerytypesHandler->getPrimaryGallery();
		$GLOBALS['xoopsTpl']->assign('gallery', 'none' != $pr_gallery['template']);
		$GLOBALS['xoopsTpl']->assign('goback', 0 < $albPid);
	break;
	case 'new':
		// Get Form
		if ($permissionsHandler->permGlobalSubmit()) {
			$albumsObj = $albumsHandler->create();
			$form = $albumsObj->getFormAlbums();
			$GLOBALS['xoopsTpl']->assign('form', $form->render());
		} else {
			redirect_header('albums.php', 3, _NOPERM);
		}
	break;
	case 'save':
		// Security Check
		if(!$GLOBALS['xoopsSecurity']->check()) {
			// redirect_header('albums.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
		}
		if (!$permissionsHandler->permGlobalSubmit()) {
			redirect_header('albums.php', 3, _NOPERM);
		}
		if( 0 < $albId) {
			$albumsObj = $albumsHandler->get($albId);
		} else {
			$albumsObj = $albumsHandler->create();
		}
		// Set Vars
		$albumsObj->setVar('alb_pid', $albPid);
		$albIscat = XoopsRequest::getInt('alb_iscat');
		$albumsObj->setVar('alb_iscat', $albIscat);
		$alb_name =  XoopsRequest::getString('alb_name');
		$albumsObj->setVar('alb_name', $alb_name);
		$albumsObj->setVar('alb_desc', XoopsRequest::getString('alb_desc'));
		$albumsObj->setVar('alb_weight', XoopsRequest::getInt('alb_weight'));
		// Set Var alb_image
        $albumsObj->setVar('alb_imgcat', XoopsRequest::getInt('alb_imgcat'));
		include_once XOOPS_ROOT_PATH .'/class/uploader.php';
		$uploader = new XoopsMediaUploader(WGGALLERY_UPLOAD_IMAGE_PATH.'/albums/', 
													$wggallery->getConfig('mimetypes'), 
													$wggallery->getConfig('maxsize', true), null, null);
		if($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
			$extension = preg_replace('/^.+\.([^.]+)$/sU', '', $_FILES['attachedfile']['name']);
			$imgName = str_replace(' ', '', $alb_name).'.'.$extension;
			$uploader->setPrefix($imgName);
			$uploader->fetchMedia($_POST['xoops_upload_file'][0]);
			if(!$uploader->upload()) {
				$errors = $uploader->getErrors();
				redirect_header('javascript:history.go(-1).php', 3, $errors);
			} else {
				$albumsObj->setVar('alb_image', $uploader->getSavedFileName());
			}
		} else {
			$albumsObj->setVar('alb_image', XoopsRequest::getString('alb_image'));
		}
        $imgName = XoopsRequest::getString('alb_imgid', 'none');
        $albImgid = 0;
        if ('none' !== $imgName) {
            $crImages = new CriteriaCompo();
            $crImages->add(new Criteria('img_name', $imgName));
            $imagesAll = $imagesHandler->getAll($crImages);
			// Get All Images
			foreach(array_keys($imagesAll) as $i) {
				$albImgid = $imagesAll[$i]->getVar('img_id');
			}
        }
		$albumsObj->setVar('alb_imgid', $albImgid);
		$albumsObj->setVar('alb_state', XoopsRequest::getInt('alb_state'));
		$albumsObj->setVar('alb_allowdownload', XoopsRequest::getInt('alb_allowdownload'));
		$albumDate = date_create_from_format(_SHORTDATESTRING, $_POST['alb_date']);
		$albumsObj->setVar('alb_date', $albumDate->getTimestamp());
		$albumsObj->setVar('alb_submitter', XoopsRequest::getInt('alb_submitter'));
		// Insert Data
		if($albumsHandler->insert($albumsObj)) {
			$newAlbId = $albumsObj->getNewInsertedIdAlbums();
			$albId = 0 < $albId ? $albId : $newAlbId;
			$gpermHandler = xoops_gethandler('groupperm');
            $perm_modid = $GLOBALS['xoopsModule']->getVar('mid');
			// remove all existing rights
			$gpermHandler->deleteByModule($perm_modid, 'wggallery_view', $albId);
			$gpermHandler->deleteByModule($perm_modid, 'wggallery_dlfullalb', $albId);
			$gpermHandler->deleteByModule($perm_modid, 'wggallery_dlimage', $albId);
            
			// set selected rights new
			// Permission to view
			if(isset($_POST['groups_view'])) {
				foreach($_POST['groups_view'] as $onegroupId) {
					$gpermHandler->addRight('wggallery_view', $albId, $onegroupId,  $perm_modid);
				}
			}
			// Permission to download full album
			if(isset($_POST['groups_dlfullalb'])) {
				foreach($_POST['groups_dlfullalb'] as $onegroupId) {
					$gpermHandler->addRight('wggallery_dlfullalb', $albId, $onegroupId, $perm_modid);
				}
			}
			// Permission to download images
			if(isset($_POST['groups_dlimage'])) {
				foreach($_POST['groups_dlimage'] as $onegroupId) {
					$gpermHandler->addRight('wggallery_dlimage', $albId, $onegroupId, $perm_modid);
				}
			}

			$albumsHandler->setAlbumIsCat();
			if ( 'upload' === $redir ) {
				redirect_header('upload.php?alb_id=' . $albId, 2, _CO_WGGALLERY_FORM_OK);
			} else {
				redirect_header('albums.php?op=list' . '&amp;alb_pid=' .$albPid, 2, _CO_WGGALLERY_FORM_OK);
			}
		}
		// Get Form
		$GLOBALS['xoopsTpl']->assign('error', $albumsObj->getHtmlErrors());
		$form = $albumsObj->getFormAlbums();
		$GLOBALS['xoopsTpl']->assign('form', $form->render());

	break;
	case 'edit':
		// Get Form
		if ($permissionsHandler->permGlobalSubmit()) {
			$albumsObj = $albumsHandler->get($albId);
			$form = $albumsObj->getFormAlbums();
			$GLOBALS['xoopsTpl']->assign('form', $form->render());
		} else {
			redirect_header('albums.php', 3, _NOPERM);
		}

	break;
	case 'delete':
		if (!$permissionsHandler->permGlobalSubmit()) {
			redirect_header('albums.php', 3, _NOPERM);
		}
		$albumsObj = $albumsHandler->get($albId);
		if(1 == XoopsRequest::getInt('ok')) {
			if(!$GLOBALS['xoopsSecurity']->check()) {
				redirect_header('albums.php', 3, implode(', ', $GLOBALS['xoopsSecurity']->getErrors()));
			}
			$alb_image = $albumsObj->getVar('alb_image');
			if($albumsHandler->delete($albumsObj)) {
				// delete albimage
				if ('blank.gif' !== $alb_image ) {
					unlink( WGGALLERY_UPLOAD_IMAGE_PATH. '/albums/' . $alb_image );
				}
				// delete all images linked to this album
				$crit_img = new CriteriaCompo();
				$crit_img->add(new Criteria('img_albid', $albId));
				$imagesAll = $imagesHandler->getAll($crit_img);
				foreach(array_keys($imagesAll) as $i)
				{
					$imagesHandler->unlinkImages( $imagesAll[$i]->getVar('img_name'), $imagesAll[$i]->getVar('img_namelarge') );
					$imagesObj = $imagesHandler->get($imagesAll[$i]->getVar('img_id'));
					$imagesHandler->delete($imagesObj, true);
				}
				redirect_header('albums.php', 3, _CO_WGGALLERY_FORM_DELETE_OK);
			} else {
				$GLOBALS['xoopsTpl']->assign('error', $albumsObj->getHtmlErrors());
			}
		} else {
			// xoops_confirm(array('ok' => 1, 'alb_id' => $albId, 'op' => 'delete'), $_SERVER['REQUEST_URI'], sprintf(_CO_WGGALLERY_FORM_SURE_DELETE, $albumsObj->getVar('alb_name')));
			$form = $wggallery->getFormDelete(array('ok' => 1, 'alb_id' => $albId, 'op' => 'delete'), _CO_WGGALLERY_FORM_DELETE, $albumsObj->getVar('alb_name'), _CO_WGGALLERY_ALBUM_DELETE_DESC);
            $GLOBALS['xoopsTpl']->assign('form', $form->render());
		}

	break;
    
    case 'order':
        $aorder = $_POST['menuItem'];
		$i = 0;
		foreach(array_keys($aorder) as $key){
			$albumsObj = $albumsHandler->get($key);
			$aorderrr = $aorder[$key];
            $albumsObj->setVar('alb_pid', $aorder[$key]);
			$albumsObj->setVar('alb_weight', $i+1);
            $albumsHandler->insert($albumsObj);
			$i++;
		}
    break;
}

// Breadcrumbs
if ( 0 < $albPid) {
	$xoBreadcrumbs[] = array('title' => _CO_WGGALLERY_ALBUMS, 'link' => 'albums.php?op=list');
	$albumsObjPid = $albumsHandler->get($albPid);
	$xoBreadcrumbs[] = array('title' => $albumsObjPid->getVar('alb_name'));
	unset($albumsObjPid);
} else {
	$xoBreadcrumbs[] = array('title' => _CO_WGGALLERY_ALBUMS);
}

$GLOBALS['xoopsTpl']->assign('panel_type', $wggallery->getConfig('panel_type'));

// Keywords
wggalleryMetaKeywords($wggallery->getConfig('keywords').', '. implode(',', $keywords));
unset($keywords);
// Description
wggalleryMetaDescription(_CO_WGGALLERY_ALBUMS);
// $GLOBALS['xoopsTpl']->assign('xoops_mpageurl', WGGALLERY_URL.'/albums.php');
$GLOBALS['xoopsTpl']->assign('wggallery_upload_url', WGGALLERY_UPLOAD_URL);
include __DIR__ . '/footer.php';
