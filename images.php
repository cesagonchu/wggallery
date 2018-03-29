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
 * @version        $Id: 1.0 images.php 1 Mon 2018-03-19 10:04:51Z XOOPS Project (www.xoops.org) $
 */
include __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = 'wggallery_images' . $wggallery->getConfig('style_index_album') . '.tpl';
include_once XOOPS_ROOT_PATH . '/header.php';

$op    = XoopsRequest::getString('op', 'list');
$imgId = XoopsRequest::getInt('img_id');
$start = XoopsRequest::getInt('start', 0);
$limit = XoopsRequest::getInt('limit', $wggallery->getConfig('userpager'));

if (_CANCEL === XoopsRequest::getString('cancel', 'none')) {
    $op = 'list';
}

// Define Stylesheet
$GLOBALS['xoTheme']->addStylesheet($style, null);
$GLOBALS['xoTheme']->addStylesheet(WGGALLERY_CSS_URL . '/style' . $wggallery->getConfig('style_index_image') . '.css', null);
//
$GLOBALS['xoopsTpl']->assign('xoops_icons32_url', XOOPS_ICONS32_URL);
$GLOBALS['xoopsTpl']->assign('wggallery_url', WGGALLERY_URL);
$GLOBALS['xoopsTpl']->assign('wggallery_icon_url_16', WGGALLERY_ICONS_URL . '/16');
//

switch ($op) {
    case 'list':
    default:
        $imagesCount = $imagesHandler->getCountImages();
        $imagesAll   = $imagesHandler->getAllImages($start, $limit);
        $keywords    = array();
        if ($imagesCount > 0) {
            $images = array();
            // Get All Images
            foreach (array_keys($imagesAll) as $i) {
                $images[]   = $imagesAll[$i]->getValuesImages();
                $keywords[] = $imagesAll[$i]->getVar('img_name');
            }
            $GLOBALS['xoopsTpl']->assign('images', $images);
            unset($images);
            // Display Navigation
            if ($imagesCount > $limit) {
                include_once XOOPS_ROOT_PATH . '/class/pagenav.php';
                $pagenav = new XoopsPageNav($imagesCount, $limit, $start, 'start', 'op=list&limit=' . $limit);
                $GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav(4));
            }
            $GLOBALS['xoopsTpl']->assign('type', $wggallery->getConfig('table_type'));
            $GLOBALS['xoopsTpl']->assign('divideby', $wggallery->getConfig('divideby'));
            $GLOBALS['xoopsTpl']->assign('numb_col', $wggallery->getConfig('numb_col'));

            //check permissions
            $GLOBALS['xoopsTpl']->assign('user_edit', true);
        }

        break;
    case 'save':
        // Security Check
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header('images.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        if (isset($imgId)) {
            $imagesObj = $imagesHandler->get($imgId);
        } else {
            $imagesObj = $imagesHandler->create();
        }
        // Set Vars
        $imagesObj->setVar('img_title', $_POST['img_title']);
        $imagesObj->setVar('img_desc', $_POST['img_desc']);
        $imagesObj->setVar('img_name', $_POST['img_name']);
        $imagesObj->setVar('img_origname', $_POST['img_origname']);
        $imagesObj->setVar('img_mimetype', isset($_POST['img_mimetype']) ? $_POST['img_mimetype'] : 0);
        $imagesObj->setVar('img_size', isset($_POST['img_size']) ? $_POST['img_size'] : 0);
        $imagesObj->setVar('img_resx', isset($_POST['img_resx']) ? $_POST['img_resx'] : 0);
        $imagesObj->setVar('img_resy', isset($_POST['img_resy']) ? $_POST['img_resy'] : 0);
        $imagesObj->setVar('img_downloads', isset($_POST['img_downloads']) ? $_POST['img_downloads'] : 0);
        $imagesObj->setVar('img_ratinglikes', isset($_POST['img_ratinglikes']) ? $_POST['img_ratinglikes'] : 0);
        $imagesObj->setVar('img_votes', isset($_POST['img_votes']) ? $_POST['img_votes'] : 0);
        $imagesObj->setVar('img_weight', isset($_POST['img_weight']) ? $_POST['img_weight'] : 0);
        $imagesObj->setVar('img_albid', isset($_POST['img_albid']) ? $_POST['img_albid'] : 0);
        $imagesObj->setVar('img_state', isset($_POST['img_state']) ? $_POST['img_state'] : 0);
        $imageDate = date_create_from_format(_SHORTDATESTRING, $_POST['img_date']);
        $imagesObj->setVar('img_date', $imageDate->getTimestamp());
        $imagesObj->setVar('img_submitter', isset($_POST['img_submitter']) ? $_POST['img_submitter'] : 0);
        $imagesObj->setVar('img_ip', $_POST['img_ip']);
        // Insert Data
        if ($imagesHandler->insert($imagesObj)) {
            redirect_header('images.php?op=list', 2, _CO_WGGALLERY_FORM_OK);
        }
        // Get Form
        $GLOBALS['xoopsTpl']->assign('error', $imagesObj->getHtmlErrors());
        $form = $imagesObj->getFormImages();
        $GLOBALS['xoopsTpl']->assign('form', $form->render());

        break;
    case 'edit':
        // Get Form
        $imagesObj = $imagesHandler->get($imgId);
        $form      = $imagesObj->getFormImages();
        $GLOBALS['xoopsTpl']->assign('form', $form->render());

        break;
    case 'delete':
        $imagesObj = $imagesHandler->get($imgId);
        if (isset($_REQUEST['ok']) && 1 == $_REQUEST['ok']) {
            if (!$GLOBALS['xoopsSecurity']->check()) {
                redirect_header('images.php', 3, implode(', ', $GLOBALS['xoopsSecurity']->getErrors()));
            }
            $img_name = $imagesObj->getVar('img_name');
            if ($imagesHandler->delete($imagesObj)) {
                if ($imagesHandler->unlinkImages($img_name)) {
                    redirect_header('images.php', 3, _CO_WGGALLERY_FORM_DELETE_OK);
                } else {
                    $GLOBALS['xoopsTpl']->assign('error', _CO_WGGALLERY_IMAGE_ERRORUNLINK);
                }
                redirect_header('images.php', 3, _CO_WGGALLERY_FORM_DELETE_OK);
            } else {
                $GLOBALS['xoopsTpl']->assign('error', $imagesObj->getHtmlErrors());
            }
        } else {
            // xoops_confirm(array('ok' => 1, 'img_id' => $imgId, 'op' => 'delete'), $_SERVER['REQUEST_URI'], sprintf(_CO_WGGALLERY_FORM_SURE_DELETE, $imagesObj->getVar('img_name')));
            $form = $wggallery->getFormDelete(array('ok' => 1, 'img_id' => $imgId, 'op' => 'delete'), _CO_WGGALLERY_FORM_DELETE, $imagesObj->getVar('img_name'));
            $GLOBALS['xoopsTpl']->assign('form', $form->render());
        }

        break;
}

// Breadcrumbs
$xoBreadcrumbs[] = array('title' => _MA_WGGALLERY_IMAGES);
// Keywords
if (null !== ($wggallery->getConfig('keywords')) && isset($keywords)) {
    wggalleryMetaKeywords($wggallery->getConfig('keywords') . ', ' . implode(',', $keywords));
    unset($keywords);
}
// Description
wggalleryMetaDescription(_MA_WGGALLERY_IMAGES_DESC);
$GLOBALS['xoopsTpl']->assign('xoops_mpageurl', WGGALLERY_URL . '/images.php');
$GLOBALS['xoopsTpl']->assign('wggallery_upload_url', WGGALLERY_UPLOAD_URL);
include __DIR__ . '/footer.php';
