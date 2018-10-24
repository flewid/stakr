<?php

class MainWPRecentComments
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public static function test()
    {

    }

    public static function getName()
    {
        return __("Recent Comments",'mainwp');
    }

    public static function render()
    {
        ?>
    <div id="recentcomments_list"><?php MainWPRecentComments::renderSites(false, false); ?></div>
    <?php
    }

    public static function renderSites($renew, $pExit = true)
    {
        global $mainwpCommentsExtensionActivator;
        $websites = apply_filters('mainwp_getdashboardsites', $mainwpCommentsExtensionActivator->getChildFile(), $mainwpCommentsExtensionActivator->getChildKey());

        $allComments = array();		
        if ($websites)
        {	
            while ($websites && ($website = @MainWPCommentDB::fetch_object($websites)))
            {
				
                if ($website->recent_comments == '') continue;
                $comments = json_decode($website->recent_comments, 1);				
                if (count($comments) == 0) continue;
                foreach ($comments as $comment)
                {
                    $comment['website'] = (object) array('id' => $website->id, 'url' => $website->url);
                    $allComments[] = $comment;
                }
            }
            @MainWPCommentDB::free_result($websites);
        }
		
            $recent_comments_approved = MainWPCommentUtility::getSubArrayHaving($allComments, 'status', 'approved');
            $recent_comments_approved = MainWPCommentUtility::sortmulti($recent_comments_approved, 'dts', 'desc');
            $recent_comments_pending = MainWPCommentUtility::getSubArrayHaving($allComments, 'status', 'unapproved');
            $recent_comments_pending = MainWPCommentUtility::sortmulti($recent_comments_pending, 'dts', 'desc');

            //todo: RS: add lines + actions (action:Edit)
            ?>
        <div class="clear">
            <a class="mainwp_action left mainwp_action_down" href="#" id="recent_comments_approved_lnk"><?php _e('Approved','mainwp'); ?> (<?php echo count($recent_comments_approved); ?>)</a><a class="mainwp_action right" href="#" id="recent_comments_pending_lnk"><?php _e('Pending','mainwp'); ?> (<?php echo count($recent_comments_pending); ?>)</a>
            <br/>
            <br/>
            <div id="recent_comments_approved">
                <?php
                for ($i = 0; $i < count($recent_comments_approved) && $i < 5; $i++)
                {
                    if (isset($recent_comments_approved[$i]['dts']))
                    {
                        if (!stristr($recent_comments_approved[$i]['dts'], '-'))
                        {
                            $recent_comments_approved[$i]['dts'] = MainWPCommentUtility::formatTimestamp(MainWPCommentUtility::getTimestamp($recent_comments_approved[$i]['dts']));
                        }
                    }
                ?>
                <div class="mainwp-row">
                    <input class="commentId" type="hidden" name="id" value="<?php echo $recent_comments_approved[$i]['id']; ?>"/>
                    <input class="websiteId" type="hidden" name="id" value="<?php echo base64_encode($recent_comments_approved[$i]['website']->id); ?>"/>
                    <span class="mainwp-leftmid-col"><?php _e('From','mainwp'); ?> <em><?php echo $recent_comments_approved[$i]['author']; ?></em> on <a href="<?php echo $recent_comments_approved[$i]['website']->url; ?>?p=<?php echo $recent_comments_approved[$i]['postId']; ?>" target="_blank"><?php echo $recent_comments_approved[$i]['postName']; ?></a><br /><?php echo self::limitString($recent_comments_approved[$i]['content'], 110); ?></span>
                    <span class="mainwp-right-col"><?php echo MainWPCommentUtility::getNiceURL($recent_comments_approved[$i]['website']->url); ?><br/><?php echo $recent_comments_approved[$i]['dts']; ?></span>

                    <div style="clear: left;"></div>
                    <div class="mainwp-row-actions"><a href="#" class="mainwp-comment-unapprove"><?php _e('Unapprove','mainwp'); ?></a> | <a href="admin.php?page=SiteOpen&websiteid=<?php echo $recent_comments_approved[$i]['website']->id; ?>&location=<?php echo base64_encode('comment.php?action=editcomment&c=' . $recent_comments_approved[$i]['id']); ?>" title="Edit this comment"><?php _e('Edit','mainwp') ?></a> | <a href="#" class="mainwp-comment-spam"><?php _e('Spam','mainwp'); ?></a> | <a href="#" class="mainwp-comment-trash"><?php _e('Trash','mainwp'); ?></a> | <a href="admin.php?page=CommentBulkManage" class="mainwp-comment-viewall"><?php _e('View All','mainwp'); ?></a></div>
                    <div class="mainwp-row-actions-working"><?php do_action('mainwp_renderImage', 'images/loader.gif', 'Loading', ''); ?> <?php _e('Please wait','mainwp'); ?></div>
                    <div>&nbsp;</div>
                </div>
                <?php } ?>
            </div>

            <div id="recent_comments_pending" style="display: none">
                <?php
                for ($i = 0; $i < count($recent_comments_pending) && $i < 5; $i++)
                {
                    if (isset($recent_comments_pending[$i]['dts']))
                    {
                        if (!stristr($recent_comments_pending[$i]['dts'], '-'))
                        {
                            $recent_comments_pending[$i]['dts'] = MainWPCommentUtility::formatTimestamp(MainWPCommentUtility::getTimestamp($recent_comments_pending[$i]['dts']));
                        }
                    }
                ?>
                <div class="mainwp-row">
                    <input class="commentId" type="hidden" name="id" value="<?php echo $recent_comments_pending[$i]['id']; ?>"/>
                    <input class="websiteId" type="hidden" name="id" value="<?php echo base64_encode($recent_comments_pending[$i]['website']->id); ?>"/>
                    <span class="mainwp-leftmid-col">From <em><?php echo $recent_comments_pending[$i]['author']; ?></em> on <a href="<?php echo $recent_comments_pending[$i]['website']->url; ?>?p=<?php echo $recent_comments_pending[$i]['postId']; ?>" target="_blank"><?php echo $recent_comments_pending[$i]['postName']; ?></a><br /><?php echo $recent_comments_pending[$i]['content']; ?></span>
                    <span class="mainwp-right-col"><?php echo MainWPCommentUtility::getNiceURL($recent_comments_pending[$i]['website']->url); ?> <br/><?php echo $recent_comments_pending[$i]['dts']; ?></span>

                    <div style="clear: left;"></div>
                    <div class="mainwp-row-actions"><a href="#" class="mainwp-comment-approve"><?php _e('Approve','mainwp'); ?></a> | <a href="admin.php?page=SiteOpen&websiteid=<?php echo $recent_comments_pending[$i]['website']->id; ?>&location=<?php echo base64_encode('comment.php?action=editcomment&c=' . $recent_comments_pending[$i]['id']); ?>" title="Edit this comment"><?php _e('Edit','mainwp'); ?></a> | <a href="#" class="mainwp-comment-spam"><?php _e('Spam','mainwp'); ?></a> | <a href="#" class="mainwp-comment-trash"><?php _e('Trash','mainwp'); ?></a> | <a href="admin.php?page=CommentBulkManage" class="mainwp-comment-viewall"><?php _e('View All','mainwp'); ?></a></div>
                    <div class="mainwp-row-actions-working"><?php do_action('mainwp_renderImage', 'images/loader.gif', 'Loading', ''); ?> <?php _e('Please wait','mainwp'); ?></div>
                    <div>&nbsp;</div>
                </div>
                <?php } ?>
            </div>
        </div>
    <div class="clear"></div>
    <?php
        if ($pExit == true) exit();
    }

	public static function limitString( $pInput, $pMax = 500 ) {
		$output = strip_tags( $pInput );
		if ( strlen( $output ) > $pMax ) {
			// truncate string
			$outputCut = substr( $output, 0, $pMax );
			// make sure it ends in a word so assassinate doesn't become ass...
			$output = substr( $outputCut, 0, strrpos( $outputCut, ' ' ) ) . '...';
		}
		echo $output;
	}
	
    public static function approve()
    {
        MainWPRecentComments::action('approve');
        die(json_encode(array('result' => __('Comment has been approved','mainwp'))));
    }

    public static function unapprove()
    {
        MainWPRecentComments::action('unapprove');
        die(json_encode(array('result' => __('Comment has been unapproved','mainwp'))));
    }

    public static function spam()
    {
        MainWPRecentComments::action('spam');
        die(json_encode(array('result' => __('Comment has been marked as spam','mainwp'))));
    }

    public static function unspam()
    {
        MainWPRecentComments::action('unspam');
        die(json_encode(array('result' => __('Comment is no longer marked as spam','mainwp'))));
    }

    public static function trash()
    {
        MainWPRecentComments::action('trash');
        die(json_encode(array('result' => __('Comment has been moved to trash','mainwp'))));
    }

    public static function restore()
    {
        MainWPRecentComments::action('restore');
        die(json_encode(array('result' => __('Comment has been restored','mainwp'))));
    }

    public static function delete()
    {
        MainWPRecentComments::action('delete');
        die(json_encode(array('result' => __('Comment has been permanently deleted','mainwp'))));
    }

    protected static function action($pAction)
    {
        if (isset($_POST['comment_ids']) && $_POST['comment_ids']) {
            MainWPRecentComments::bulk_action($pAction);
            return;
        }    

        $commentId = $_POST['commentId'];
        $websiteIdEnc = $_POST['websiteId'];
        
        if (!MainWPCommentUtility::ctype_digit($commentId)) die(json_encode(array('error' => 'Invalid Request.')));
        $websiteId = base64_decode($websiteIdEnc);
        if (!MainWPCommentUtility::ctype_digit($websiteId)) die(json_encode(array('error' => 'Invalid Request.')));

        global $mainwpCommentsExtensionActivator;
        $information = apply_filters('mainwp_fetchurlauthed', $mainwpCommentsExtensionActivator->getChildFile(), $mainwpCommentsExtensionActivator->getChildKey(), $websiteId, 'comment_action', array(
                        'action' => $pAction,
                        'id' => $commentId));

        if (is_array($information) && isset($information['error']))
        {
            die(json_encode($information));
        }

        if (!isset($information['status']) || ($information['status'] != 'SUCCESS')) die(json_encode(array('error' => 'Unexpected error.')));
    }
    
    public static function action_message($act) {
        $mess = '';

        switch ($act) {
            case 'approve':
                $mess = __('Comment(s) has been approved','mainwp');
            break;
            case 'unapprove':
                $mess = __('Comment(s) has been unapproved','mainwp');
            break;
            case 'spam':
                $mess = __('Comment(s) has been marked as spam','mainwp');
            break;
            case 'unspam':
                $mess = __('Comment(s) is no longer marked as spam','mainwp');
            break;
            case 'trash':
                $mess = __('Comment(s) has been moved to trash','mainwp');
            break;
            case 'restore':
                $mess = __('Comment(s) has been restored','mainwp');
            break;
            case 'delete':
                $mess = __('Comment(s) has been permanently deleted','mainwp');
            break;            
        }

        return $mess;
    }
    
    protected static function bulk_action($pAction)
    {
        $commentIds = explode(',', $_POST['comment_ids']);
        $websiteIds = explode(',', $_POST['comment_wpids']);
        $websites = array();
        for ($i = 0; $i < count($websiteIds); $i++) {            
            $websites[$websiteIds[$i]]['_commentids'] .= $commentIds[$i].",";
        }
        $success = 0;
        if (count($websites) > 0)
        {
            foreach ($websites as $wpid => $web)
            {
                $comm_ids = rtrim($web['_commentids'], ',');

                if (!MainWPCommentUtility::ctype_digit($wpid)) continue;

                global $mainwpCommentsExtensionActivator;
                $information = apply_filters('mainwp_fetchurlauthed', $mainwpCommentsExtensionActivator->getChildFile(), $mainwpCommentsExtensionActivator->getChildKey(), $wpid, 'comment_bulk_action', array(
                    'action' => $pAction,
                    'ids' => $comm_ids));

                $success += intval(isset($information['success']) ? $information['success'] : 0);
            }
        }
        $ret['message'] = $success .' ' . MainWPRecentComments::action_message($pAction);
        // die right here to return json
        die(json_encode($ret));        
    }

}
