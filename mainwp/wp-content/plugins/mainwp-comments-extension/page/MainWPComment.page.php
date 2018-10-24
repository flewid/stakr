<?php
class MainWPComment
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public static $subPages;
    public $security_nonces;

    public function init()
    {
         if (!function_exists("mainwp_current_user_can") || mainwp_current_user_can("extension", "mainwp-comments-extension")) {
            add_action('mainwp-pageheader-comment', array(MainWPComment::getClassName(), 'renderHeader'));
            add_action('mainwp-pagefooter-comment', array(MainWPComment::getClassName(), 'renderFooter'));
            add_action('mainwp_admin_menu_sub', array(MainWPComment::getClassName(), 'initMenuSubPages'));
            add_action('mainwp_admin_menu', array(MainWPComment::getClassName(), 'initMenu'));
            //$this->init_ajax();
        }
    }

    public static function initMenu()
    {
        add_submenu_page('mainwp_tab', __('Comments','mainwp'), __('Comments','mainwp'), 'read', 'CommentBulkManage', array('MainWPComment', 'render'));
        add_submenu_page('mainwp_tab', __('Comments Help','mainwp'), '<div class="mainwp-hidden">'.__('Comments Help','mainwp').'</div>', 'read', 'CommentsHelp', array(MainWPComment::getClassName(), 'QSGManageComments'));

        self::$subPages = apply_filters('mainwp-getsubpages-comment', array());
        if (isset(self::$subPages) && is_array(self::$subPages))
        {
            foreach (self::$subPages as $subPage)
            {
                add_submenu_page('mainwp_tab', $subPage['title'], '<div class="mainwp-hidden">' . $subPage['title'] . '</div>', 'read', MainWPComment::getClassName() . $subPage['slug'], $subPage['callback']);
            }
        }
    }

    public static function initMenuSubPages()
    {
        if (empty(self::$subPages)) return;

        ?>
       <div id="menu-mainwp-Pages" class="mainwp-submenu-wrapper">
            <div class="wp-submenu sub-open" style="">
                <div class="mainwp_boxout">
                    <div class="mainwp_boxoutin"></div>
                        <?php
                    if (isset(self::$subPages) && is_array(self::$subPages))
                    {
                        foreach (self::$subPages as $subPage)
                        {
                        ?>
                            <a href="<?php echo admin_url('admin.php?page='.MainWPComment::getClassName().$subPage['slug']); ?>" class="mainwp-submenu"><?php echo $subPage['title']; ?></a>
                        <?php
                        }
                    }
                        ?>
                </div>
            </div>
        </div>
        <?php
    }

    public static function renderHeader($shownPage)
    {
        do_action('mainwp_renderHeader', __('Comments', 'mainwp'), plugins_url('images/mainwp-comment.png', dirname(__FILE__)));
        ?>
        <div class="mainwp-tabs" id="mainwp-tabs">
                <a class="nav-tab pos-nav-tab <?php if ($shownPage === 'CommentBulkManage') { echo "nav-tab-active"; } ?>" href="admin.php?page=CommentBulkManage"><?php _e('Manage','mainwp'); ?></a>
                <a style="float: right" class="mainwp-help-tab nav-tab pos-nav-tab <?php if ($shownPage === 'CommentsHelp') { echo "nav-tab-active"; } ?>" href="admin.php?page=CommentsHelp"><?php _e('Help','mainwp'); ?></a>
        </div>
        <div id="mainwp_wrap-inside">
            <?php

    }

    public static function renderFooter($shownPage) {
        ?>
         </div>
        <?php
        do_action('mainwp_renderFooter', __('Comments', 'mainwp'), plugins_url('images/mainwp-comment.png', dirname(__FILE__)));
    }

    public static function render()
    {
        $cachedSearch = apply_filters('mainwp_cache_getcontext', 'Comment');
        if ($cachedSearch === false) $cachedSearch = null;

        self::renderHeader('CommentBulkManage');
        self::QSGComments();
        ?>
        <br/>
        <div class="mainwp-search-form" id="mainwp-search-form-comments">
            <div class="postbox mainwp-postbox">
            <h3 class="mainwp_box_title"><?php _e('Search Comments','mainwp'); ?></h3>
            <div class="inside">
            <ul class="mainwp_checkboxes">
                <li>
                    <input type="checkbox" id="mainwp_comment_search_type_approve" <?php echo ($cachedSearch == null || ($cachedSearch != null && in_array('approve', $cachedSearch['status']))) ? 'checked="checked"' : ''; ?> class="mainwp-checkbox2"/>
                    <label for="mainwp_comment_search_type_approve" class="mainwp-label2">Approved</label>
                </li>
                <li>
                    <input type="checkbox" id="mainwp_comment_search_type_hold" <?php echo ($cachedSearch != null && in_array('hold', $cachedSearch['status'])) ? 'checked="checked"' : ''; ?> class="mainwp-checkbox2"/>
                    <label for="mainwp_comment_search_type_hold" class="mainwp-label2">Pending</label>
                </li>
                <li>
                    <input type="checkbox" id="mainwp_comment_search_type_spam" <?php echo ($cachedSearch != null && in_array('spam', $cachedSearch['status'])) ? 'checked="checked"' : ''; ?> class="mainwp-checkbox2"/>
                    <label for="mainwp_comment_search_type_spam" class="mainwp-label2">Spam</label>
                </li>
                <li>
                    <input type="checkbox" id="mainwp_comment_search_type_trash" <?php echo ($cachedSearch != null && in_array('trash', $cachedSearch['status'])) ? 'checked="checked"' : ''; ?> class="mainwp-checkbox2"/>
                    <label for="mainwp_comment_search_type_trash" class="mainwp-label2">Trash</label>
                </li>
            </ul>
        
            <p>
                <?php _e('Containing Keyword:','mainwp'); ?><br />
                <input type="text" id="mainwp_comment_search_by_keyword" size="50" value="<?php if ($cachedSearch != null) { echo $cachedSearch['keyword']; } ?>"/>
            </p>
            <p>
                <?php _e('Date Range:','mainwp'); ?><br />
                <input type="text" id="mainwp_comment_search_by_dtsstart" class="mainwp_datepicker" size="12" value="<?php if ($cachedSearch != null) { echo $cachedSearch['dtsstart']; } ?>"/> <?php _e('to','mainwp'); ?> <input type="text" id="mainwp_comment_search_by_dtsstop" class="mainwp_datepicker" size="12" value="<?php if ($cachedSearch != null) { echo $cachedSearch['dtsstop']; } ?>"/>
            </p>
            <p>&nbsp;</p>
                </div>
        </div>          
        <?php do_action('mainwp_select_sites_box', __("Select Sites", 'mainwp'), 'checkbox', true, true, 'mainwp_select_sites_box_right'); ?>
        <div style="clear: both;"></div>
          
            
            <input type="button" name="mainwp_show_comments" id="mainwp_show_comments" class="button-primary" value="<?php _e('Show Comments','mainwp'); ?>"/>
            <?php
            if (isset($_REQUEST['siteid']) && isset($_REQUEST['postid']))
            {
                echo '<script>jQuery(document).ready(function() { mainwp_show_comments('.$_REQUEST['siteid'].', '.$_REQUEST['postid'].')});</script>';
            }
            ?>
            <span id="mainwp_comments_loading">&nbsp;<em><?php _e('Grabbing information from Child Sites','mainwp') ?></em>&nbsp;&nbsp;<?php do_action('mainwp_renderImage', 'images/loader.gif', 'Loading', ''); ?></span>
        </div>
        <div class="clear"></div>

        <div id="mainwp_comments_error"></div>
        <div id="mainwp_comments_main" <?php if ($cachedSearch != null) { echo 'style="display: block;"'; } ?>>
            <div class="alignleft">
                <select name="bulk_action" id="mainwp_bulk_action">
                    <option value="none"><?php _e('Bulk Action','mainwp'); ?></option>
                    <option value="unapprove"><?php _e('Unapprove','mainwp'); ?></option>
                    <option value="approve"><?php _e('Approve','mainwp'); ?></option>
                    <option value="spam"><?php _e('Mark as Spam','mainwp'); ?></option>
                    <option value="unspam"><?php _e('Not Spam','mainwp'); ?></option>
                    <option value="trash"><?php _e('Move to Trash','mainwp'); ?></option>
                    <option value="restore"><?php _e('Restore','mainwp'); ?></option>
                    <option value="delete"><?php _e('Delete Permanently','mainwp'); ?></option>
                </select> <select name="bulk_action_apply_to" id="mainwp_bulk_action_apply_to">
                    <option value="only_selected"><?php _e('Only Selected Items','mainwp'); ?></option>
                    <option value="all_results"><?php _e('All Search Results','mainwp'); ?></option>
                </select> <input type="button" name="" id="mainwp_comments_bulk_action_apply" class="button" value="<?php _e('Apply','mainwp'); ?>"/>
                <span id="mainwp_bulk_action_applying"><?php do_action('mainwp_renderImage', 'images/loader.gif', 'Loading', ''); ?>
                </span>
            </div>                    
            <input type="hidden" id="bulk_comment_ids" name="bulk_comment_ids" value=""/>
            <input type="hidden" id="bulk_comment_wpids" name="bulk_comment_wpids" value=""/>   
            <div class="alignright" id="mainwp_comments_total_results">
                <?php _e('Total Results:','mainwp'); ?> <span id="mainwp_comments_total"><?php echo $cachedSearch != null ? $cachedSearch['count'] : '0'; ?></span>
            </div>
            <div class="clear"></div>
            <div id="mainwp_comments_content">
                <table class="wp-list-table widefat fixed posts tablesorter" id="mainwp_comments_table"
                       cellspacing="0">
                    <thead>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input
                                type="checkbox"></th>
                        <th scope="col" id="title" class="manage-column column-author sortable desc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('Author','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="author" class="manage-column column-title sortable desc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('Comment','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="categories" class="manage-column column-categories sortable desc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('In Response To','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="date" class="manage-column column-date sortable asc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('Date','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="status" class="manage-column column-status sortable asc" style="width: 120px;">
                            <a href="#" onclick="return false;"><span><?php _e('Status','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="tags" class="manage-column column-tags sortable desc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('Website','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                    </tr>
                    </thead>

                    <tfoot>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input
                                type="checkbox"></th>
                        <th scope="col" id="title" class="manage-column column-author sortable desc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('Author','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="author" class="manage-column column-title sortable desc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('Comment','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="categories" class="manage-column column-categories sortable desc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('In Response To','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="date" class="manage-column column-date sortable asc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('Date','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="status" class="manage-column column-status sortable asc" style="width: 120px;">
                            <a href="#" onclick="return false;"><span><?php _e('Status','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" id="tags" class="manage-column column-tags sortable desc" style="">
                            <a href="#" onclick="return false;"><span><?php _e('Website','mainwp'); ?></span><span class="sorting-indicator"></span></a>
                        </th>
                    </tr>
                    </tfoot>

                    <tbody id="the-comment-list" class="list:posts">
                        <?php do_action('mainwp_cache_echo_body', 'Comment'); ?>
                    </tbody>
                </table>
                <div class="pager" id="pager">
                    <form>
                        <?php do_action('mainwp_renderImage', 'images/first.png', 'First', 'first'); ?>
                        <?php do_action('mainwp_renderImage', 'images/prev.png', 'Previous', 'prev'); ?>
                        <input type="text" class="pagedisplay" />
                        <?php do_action('mainwp_renderImage', 'images/next.png', 'Next', 'next'); ?>
                        <?php do_action('mainwp_renderImage', 'images/last.png', 'Last', 'last'); ?>
                        <span>&nbsp;&nbsp;<?php _e('Show:','mainwp'); ?> </span><select class="pagesize">
                            <option selected="selected" value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="1000000000">All</option>
                        </select><span> <?php _e('Comments per page','mainwp'); ?></span>
                    </form>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    <?php
        if ($cachedSearch != null) { echo '<script>mainwp_comments_table_reinit();</script>'; }
        self::renderFooter('CommentBulkManage');
    }

    public static function renderTable($keyword, $dtsstart, $dtsstop, $status, $groups, $sites, $postId)
    {
        do_action('mainwp_cache_init', 'Comment');

        //Fetch all!
        //Build websites array
        global $mainwpCommentsExtensionActivator;
        $dbwebsites = apply_filters('mainwp-getdbsites', $mainwpCommentsExtensionActivator->getChildFile(), $mainwpCommentsExtensionActivator->getChildKey(), $sites, $groups);

        $output = new stdClass();
        $output->errors = array();
        $output->comments = array();
        $output->commentscount = 0;

        if (count($dbwebsites) > 0) {
            $post_data = array(
                'keyword' => $keyword,
                'dtsstart' => $dtsstart,
                'dtsstop' => $dtsstop,
                'status' => $status,
                'maxRecords' => ((get_option('mainwp_maximumComments') === false) ? 50 : get_option('mainwp_maximumComments'))
            );
            if (isset($postId) && ($postId != ''))
            {
                $post_data['postId'] = $postId;
            }

            do_action('mainwp_fetchurlsauthed', $mainwpCommentsExtensionActivator->getChildFile(), $mainwpCommentsExtensionActivator->getChildKey(), $dbwebsites, 'get_all_comments', $post_data, array(MainWPComment::getClassName(), 'CommentsSearch_handler'), $output);
        }

        do_action('mainwp_cache_add_context', 'Comment', array('count' => $output->commentscount, 'keyword' => $keyword, 'dtsstart' => $dtsstart, 'dtsstop' => $dtsstop, 'status' => $status));

        //Sort if required

        ob_start();
        if ($output->commentscount == 0) {
            ?>
        <tr>
            <td colspan="7">No comments found</td>
        </tr>
        <?php
        }
        else
        {
            $cmids = rtrim($output->cmids, ',');
            $wpids = rtrim($output->wpids, ',');
            ?>
                <input type="hidden" id="bulk_comment_ids_tmp" name="bulk_comment_ids_tmp" value="<?php echo $cmids ?>"/>
                <input type="hidden" id="bulk_comment_wpids_tmp" name="bulk_comment_wpids_tmp" value="<?php echo $wpids ?>"/>
            <?php
        }

        $newOutput = ob_get_clean();
        echo $newOutput;

        do_action('mainwp_cache_add_body', 'Comment', $newOutput);
    }
   
   
    private static function getStatus($status)
    {
        if ($status == 'unapproved') $status = 'pending';

        return ucfirst($status);
    }

    public static function CommentsSearch_handler($data, $website, &$output)
    {
        if (preg_match('/<mainwp>(.*)<\/mainwp>/', $data, $results) > 0) {
            $comments = unserialize(base64_decode($results[1]));
            unset($results);

            foreach ($comments as $comment)
            {
                if (isset($comment['dts']))
                {
                    if (!stristr($comment['dts'], '-'))
                    {
                        $comment['dts'] = MainWPCommentUtility::formatTimestamp(MainWPCommentUtility::getTimestamp($comment['dts']));
                    }
                }

                $output->cmids .= $comment['id'].",";
                $output->wpids .= $website->id.",";
                $output->commentscount++;
                ob_start();
                ?>
            <tr id="post-1"
                class="post-1 post type-post status-publish format-standard hentry category-uncategorized alternate iedit author-self"
                valign="top">
                <th scope="row" class="check-column"><input type="checkbox" name="comment[]" value="1"></th>
                <td class="author column-author">
                    <?php echo $comment['author']; ?>
                    <?php echo $comment['author_email'] . ($comment['author_email'] != '' ? '<br />' : ''); ?>
                    <?php echo $comment['author_url'] . ($comment['author_url'] != '' ? '<br />' : ''); ?>
                    <?php echo $comment['author_ip'] . ($comment['author_ip'] != '' ? '<br />' : ''); ?>
                </td>
                <td class="comment column-comment">
                    <input class="commentId" type="hidden" name="id" value="<?php echo $comment['id']; ?>"/>
                    <input class="websiteId" type="hidden" name="id"
                           value="<?php echo base64_encode($website->id); ?>"/>

                    <?php echo $comment['content']; ?>

                    <div class="row-actions">
                        <?php if ($comment['status'] == 'approved') { ?>
                        <span class="unapprove">
                             <a class="comment_submitunapprove" title="Unapprove this item" href="#"><?php _e('Unapprove','mainwp'); ?></a> |
                        </span>
                        <?php } ?>
                        <?php if ($comment['status'] == 'unapproved') { ?>
                        <span class="approve" style="display: inline;">
                             <a class="comment_submitapprove" title="Approve this item" href="#"><?php _e('Approve','mainwp'); ?></a> |
                        </span>
                        <?php } ?>
                        <?php if (($comment['status'] != 'trash') && ($comment['status'] != 'spam')) { ?>
                        <span class="edit">
                            <a href="admin.php?page=SiteOpen&websiteid=<?php echo $website->id; ?>&location=<?php echo base64_encode('comment.php?action=editcomment&c=' . $comment['id']); ?>"
                               title="Edit this item"><?php _e('Edit','mainwp'); ?></a>
                        </span>
                        <span class="spam">
                             | <a class="comment_submitspam" title="Mark this item as Spam" href="#"><?php _e('Spam','mainwp'); ?></a>
                        </span>
                        <span class="trash">
                             | <a class="comment_submitdelete" title="Move this item to the Trash" href="#"><?php _e('Trash','mainwp'); ?></a>
                        </span>
                        <?php } ?>
                        <?php if ($comment['status'] == 'trash') { ?>
                        <span class="untrash approve" style="display: inline;">
                           <a class="comment_submitrestore" title="Restore this item" href="#"><?php _e('Restore','mainwp'); ?></a>
                        </span>
                        <?php } ?>
                        <?php if ($comment['status'] == 'spam') { ?>
                        <span class="unspam approve" style="display: inline;">
                           <a class="comment_submitunspam" title="" href="#"><?php _e('Not Spam','mainwp'); ?></a>
                        </span>
                        <?php } ?>
                        <?php if (($comment['status'] == 'trash') || ($comment['status'] == 'spam')) { ?>
                        <span class="trash">
                            | <a class="comment_submitdelete_perm" title="Delete this item permanently" href="#"><?php _e('Delete
                            Permanently','mainwp'); ?></a>
                        </span>
                        <?php } ?>
                    </div>
                    <div class="row-actions-working">
                        <?php do_action('mainwp_renderImage', 'images/loader.gif', 'Loading', ''); ?><?php _e('Please wait','mainwp'); ?></div>
                </td>
                <td class="comments column-comments">
                    <?php echo $comment['postName']; ?>
                    <div class="post-com-count-wrapper">
                        <a href="<?php echo admin_url('admin.php?page=CommentBulkManage&siteid='.$website->id.'&postid='.$comment['postId']); ?>" title="0 pending" class="post-com-count"><span
                                class="comment-count"><?php echo $comment['comment_count']; ?></span></a><a style="padding-left: .5em;" href="<?php echo admin_url('admin.php?page=PostBulkManage&siteid='.$website->id.'&postid='.$comment['postId']); ?>"><?php _e('View
                        Post','mainwp'); ?></a>
                    </div>
                </td>
                <td class="date column-date"><abbr
                        title="<?php echo $comment['dts']; ?>"><?php echo $comment['dts']; ?></abbr>
                </td>
                <td class="date column-status"><?php echo MainWPComment::getStatus($comment['status']); ?></td>
                <td class="categories column-categories">
                    <a href="<?php echo $website->url; ?>" target="_blank"><?php echo $website->url; ?></a>
                    <div class="row-actions">
                        <span class="edit"><a href="admin.php?page=managesites&dashboard=<?php echo base64_encode($website->id); ?>"><?php _e('Dashboard','mainwp'); ?></a> | <a href="admin.php?page=SiteOpen&websiteid=<?php echo base64_encode($website->id); ?>"><?php _e('WP Admin','mainwp'); ?></a></span>
                    </div>
                </td>
            </tr>
            <?php
                $newOutput = ob_get_clean();
                echo $newOutput;

                do_action('mainwp_cache_add_body', 'Comment', $newOutput);
            }
            unset($comments);
        } else {
            $output->errors[$website->id] = apply_filters('mainwp_getErrorMessage', 'NOMAINWP', $website->url);
        }
    }

    public function init_ajax()
    {
        //Page: Recent Comments
        $this->addAction('mainwp_comment_unapprove', array(&$this, 'mainwp_comment_unapprove'));
        $this->addAction('mainwp_comment_approve', array(&$this, 'mainwp_comment_approve'));
        $this->addAction('mainwp_comment_spam', array(&$this, 'mainwp_comment_spam'));
        $this->addAction('mainwp_comment_unspam', array(&$this, 'mainwp_comment_unspam'));
        $this->addAction('mainwp_comment_trash', array(&$this, 'mainwp_comment_trash'));
        $this->addAction('mainwp_comment_restore', array(&$this, 'mainwp_comment_restore'));
        $this->addAction('mainwp_comment_delete', array(&$this, 'mainwp_comment_delete'));
        //Page: Comments
        add_action('wp_ajax_mainwp_comments_search', array(&$this, 'mainwp_comments_search')); //ok
    }

    protected function addAction($action, $callback)
    {
        add_action('wp_ajax_' . $action, $callback);
        $this->addSecurityNonce($action);
    }

    protected function addSecurityNonce($action)
    {
        if (!is_array($this->security_nonces)) $this->security_nonces = array();

        if (!function_exists('wp_create_nonce')) include_once(ABSPATH . WPINC . '/pluggable.php');
        $this->security_nonces[$action] = wp_create_nonce($action);
    }


    /**
     * Page: Recent Comments
     */
    /**
     * Page: Comments
     */
    function mainwp_comments_search()
    {
        MainWPComment::renderTable($_POST['keyword'], $_POST['dtsstart'], $_POST['dtsstop'], $_POST['status'], (isset($_POST['groups']) ? $_POST['groups'] : ''), (isset($_POST['sites']) ? $_POST['sites'] : ''), (isset($_POST['postId']) ? $_POST['postId'] : ''));
        die();
    }

    function mainwp_comment_unapprove()
    {
        $this->secure_request('mainwp_comment_unapprove');

        MainWPRecentComments::unapprove();
    }
    function mainwp_comment_approve()
    {
        $this->secure_request('mainwp_comment_approve');

        MainWPRecentComments::approve();
    }
    function mainwp_comment_trash()
    {
        $this->secure_request('mainwp_comment_trash');

        MainWPRecentComments::trash();
    }
    function mainwp_comment_restore()
    {
        $this->secure_request('mainwp_comment_restore');

        MainWPRecentComments::restore();
    }
    function mainwp_comment_spam()
    {
        $this->secure_request('mainwp_comment_spam');

        MainWPRecentComments::spam();
    }
    function mainwp_comment_unspam()
    {
        $this->secure_request('mainwp_comment_unspam');

        MainWPRecentComments::unspam();
    }
    function mainwp_comment_delete()
    {
        $this->secure_request('mainwp_comment_delete');

        MainWPRecentComments::delete();
    }

    function secure_request($action, $query_arg = 'security')
    {
        if (!$this->check_security($action, $query_arg)) die(json_encode(array('error' => 'Invalid request')));
    }

    function check_security($action = -1, $query_arg = 'security')
    {
        if ($action == -1) return false;

        $adminurl = strtolower(admin_url());
        $referer = strtolower(wp_get_referer());
        $result = isset($_REQUEST[$query_arg]) ? wp_verify_nonce($_REQUEST[$query_arg], $action) : false;
        if (!$result && !(-1 == $action && strpos($referer, $adminurl) === 0))
        {
            return false;
        }

        return true;
    }

    public static function QSGManageComments()
    {
        $plugin_data =  get_plugin_data( MAINWP_COMMENTS_PLUGIN_FILE, false );         
        $description = $plugin_data['Description'];
        $extraHeaders = array('DocumentationURI' => 'Documentation URI');
        $file_data = get_file_data(MAINWP_COMMENTS_PLUGIN_FILE, $extraHeaders);
        $documentation_url  = $file_data['DocumentationURI'];

        self::renderHeader('CommentsHelp');
    ?>
    <div class="mainwp_ext_info_box">
        <div class="mainwp-ext-description"><?php echo $description; ?></div><br/>
       <b><?php echo __("Need Help?"); ?></b> <?php echo __("Review the Extension"); ?> <a href="<?php echo $documentation_url; ?>" target="_blank"><i class="fa fa-book"></i> <?php echo __('Documentation'); ?></a>.
        <a href="#" id="mainwp-quick-start-guide"><i class="fa fa-info-circle"></i> <?php _e('Show Quick Start Guide','mainwp'); ?></a></div>
                         <div  class="mainwp_ext_info_box" id="mainwp-qsg-tips">
                          <span><a href="#" class="mainwp-show-qsg" number="1"><i class="fa fa-book"></i> <?php _e('Manage Comments','mainwp') ?></a></span><span><a href="#" id="mainwp-qsg-dismiss" style="float: right;"><i class="fa fa-times-circle"></i> <?php _e('Dismiss','mainwp'); ?></a></span>
                      <div class="clear"></div>
                      <div id="mainwp-qsgs">
                        <div class="mainwp-qsg" number="1">
                            <h3>Manage Comments</h3>
                            <p>
                                <ol>
                                    <li>
                                        In Search Comments section, select do you want to search Approved, Pending, Spam or Trashed comments<br/><br/>
                                        <img src="http://docs.mainwp.com/wp-content/uploads/2013/02/new-comments-status.jpg" style="wight: 100% !important;" alt="screenshot"/>
                                    </li>
                                    <li>
                                        Optionaly, enter a Keyword for search and pick a Date Range <br/><br/>
                                        <img src="http://docs.mainwp.com/wp-content/uploads/2013/02/new-comments-keyword.jpg" style="wight: 100% !important;" alt="screenshot"/>
                                    </li>
                                    <li>
                                        Select Sites you want to search on <br/><br/>
                                        <img src="http://docs.mainwp.com/wp-content/uploads/2013/02/new-comments-sites.jpg" style="wight: 100% !important;" alt="screenshot"/>
                                    </li>
                                    <li>
                                        Hit the Show Comments button.
                                    </li>
                                    <li>
                                        Comment list appears in a Table. Author, Comment, In Response To, Website, Status and Date will be displayed it the table
                                    </li>
                                    <li>
                                        Use Bulk Action Menu or Quick Links to manage your Comments<br/><br/>
                                        <img src="http://docs.mainwp.com/wp-content/uploads/2013/02/new-comments-table-1024x269.jpg" style="wight: 100% !important;" alt="screenshot"/>
                                    </li>
                                </ol>
                            </p>
                        </div>
                      </div>
                    </div>
    <?php
    self::renderFooter('CommentsHelp');
  }

  public static function QSGComments()
    {
        $plugin_data =  get_plugin_data( MAINWP_COMMENTS_PLUGIN_FILE, false );         
        $description = $plugin_data['Description'];
        $extraHeaders = array('DocumentationURI' => 'Documentation URI');
        $file_data = get_file_data(MAINWP_COMMENTS_PLUGIN_FILE, $extraHeaders);
        $documentation_url  = $file_data['DocumentationURI'];
    ?>
    <div class="mainwp_ext_info_box">
        <div class="mainwp-ext-description"><?php echo $description; ?></div><br/>
       <b><?php echo __("Need Help?"); ?></b> <?php echo __("Review the Extension"); ?> <a href="<?php echo $documentation_url; ?>" target="_blank"><i class="fa fa-book"></i> <?php echo __('Documentation'); ?></a>.
        <a href="#" id="mainwp-quick-start-guide"><i class="fa fa-info-circle"></i> <?php _e('Show Quick Start Guide','mainwp'); ?></a></div>
                         <div  class="mainwp_ext_info_box" id="mainwp-qsg-tips">
                          <span><a href="#" class="mainwp-show-qsg" number="1"><i class="fa fa-book"></i> <?php _e('Manage Comments','mainwp') ?></a></span><span><a href="#" id="mainwp-qsg-dismiss" style="float: right;"><i class="fa fa-times-circle"></i> <?php _e('Dismiss','mainwp'); ?></a></span>
                      <div class="clear"></div>
                      <div id="mainwp-qsgs">
                        <div class="mainwp-qsg" number="1">
                            <h3>Manage Comments</h3>
                            <p>
                                <ol>
                                    <li>
                                        In Search Comments section, select do you want to search Approved, Pending, Spam or Trashed comments<br/><br/>
                                        <img src="http://docs.mainwp.com/wp-content/uploads/2013/02/new-comments-status.jpg" style="wight: 100% !important;" alt="screenshot"/>
                                    </li>
                                    <li>
                                        Optionaly, enter a Keyword for search and pick a Date Range <br/><br/>
                                        <img src="http://docs.mainwp.com/wp-content/uploads/2013/02/new-comments-keyword.jpg" style="wight: 100% !important;" alt="screenshot"/>
                                    </li>
                                    <li>
                                        Select Sites you want to search on <br/><br/>
                                        <img src="http://docs.mainwp.com/wp-content/uploads/2013/02/new-comments-sites.jpg" style="wight: 100% !important;" alt="screenshot"/>
                                    </li>
                                    <li>
                                        Hit the Show Comments button.
                                    </li>
                                    <li>
                                        Comment list appears in a Table. Author, Comment, In Response To, Website, Status and Date will be displayed it the table
                                    </li>
                                    <li>
                                        Use Bulk Action Menu or Quick Links to manage your Comments<br/><br/>
                                        <img src="http://docs.mainwp.com/wp-content/uploads/2013/02/new-comments-table-1024x269.jpg" style="wight: 100% !important;" alt="screenshot"/>
                                    </li>
                                </ol>
                            </p>
                        </div>
                      </div>
                    </div>
                    <?php
}

}

?>
