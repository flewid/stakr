/**
  * MainWPComment.page
  */
var commentCountSent = 0;
var commentCountReceived = 0;
jQuery(document).ready(function () {
    jQuery('.mainwp_datepicker').datepicker({dateFormat:"yy-mm-dd"});
    jQuery('#mainwp_show_comments').live('click', function () {
        mainwp_fetch_comments();
    });
    jQuery('.comment_submitdelete').live('click', function () {
        mainwpcomment_postAction(jQuery(this), 'trash');
        return false;
    });
    jQuery('.comment_submitdelete_perm').live('click', function () {
        mainwpcomment_postAction(jQuery(this), 'delete');
        return false;
    });
    jQuery('.comment_submitrestore').live('click', function () {
        mainwpcomment_postAction(jQuery(this), 'restore');
        return false;
    });
    jQuery('.comment_submitspam').live('click', function () {
        mainwpcomment_postAction(jQuery(this), 'spam');
        return false;
    });
    jQuery('.comment_submitunspam').live('click', function () {
        mainwpcomment_postAction(jQuery(this), 'unspam');
        return false;
    });
    jQuery('.comment_submitapprove').live('click', function () {
        mainwpcomment_postAction(jQuery(this), 'approve');
        return false;
    });
    jQuery('.comment_submitunapprove').live('click', function () {
        mainwpcomment_postAction(jQuery(this), 'unapprove');
        return false;
    });
    jQuery('#mainwp_comments_bulk_action_apply').live('click', function () {
        var action = jQuery('#mainwp_bulk_action').val();
        if (action == 'none') return false;

        var tmp = jQuery("input[name='comment[]']:checked");
        commentCountSent = tmp.length;

        if (jQuery('#mainwp_bulk_action_apply_to').val() == 'only_selected') {
            tmp.each(
                    function (index, elem) {
                        mainwpcomment_postAction(elem, action);
                    }
            );
        }
        else if (jQuery('#mainwp_bulk_action_apply_to').val() == 'all_results')
        {
            mainwp_bulk_actions_comments(action);
        }

        return false;
    });
});
mainwp_show_comments = function(siteId, postId)
{
    var siteElement = jQuery('input[name="selected_sites[]"][siteid="'+siteId+'"]');
    siteElement.prop('checked', true);
    siteElement.trigger("change");

    mainwp_fetch_comments(postId);
};

mainwpcomment_postAction = function (elem, what) {
    var rowElement = jQuery(elem).parents('tr');
    var commentId = rowElement.find('.commentId').val();
    var websiteId = rowElement.find('.websiteId').val();

    var data = {
        action:'mainwp_comment_' + what,
        commentId:commentId,
        websiteId:websiteId,
        security: mainwp_comments_security_nonces['mainwp_comment_' + what]
    };

    jQuery('#mainwp_comments_bulk_action_apply').attr('disabled', 'true');
    rowElement.find('.row-actions').hide();
    rowElement.find('.row-actions-working').show();
    jQuery.post(ajaxurl, data, function (response) {
        if (response.result) {
            rowElement.html('<td colspan="7">' + response.result + '</td>');
        }
        else {
            rowElement.find('.row-actions-working').hide();
        }
        commentCountReceived++;

        if (commentCountReceived == commentCountSent) {
            commentCountReceived = 0;
            commentCountSent = 0;
            jQuery('#mainwp_comments_bulk_action_apply').removeAttr('disabled');
        }
    }, 'json');

    return false;
};

mainwp_comments_table_reinit = function () {
    // store data to safe place
    if (jQuery('#bulk_comment_ids_tmp') != undefined && jQuery('#bulk_comment_wpids_tmp') != undefined) {
        jQuery('#bulk_comment_ids').val(jQuery('#bulk_comment_ids_tmp').val());
        jQuery('#bulk_comment_wpids').val(jQuery('#bulk_comment_wpids_tmp').val());
    }

    if (jQuery('#mainwp_comments_table').hasClass('tablesorter-default'))
    {
        jQuery('#mainwp_comments_table').trigger("updateAll").trigger('destroy.pager').tablesorterPager({container:jQuery("#pager")});
    }
    else
    {
        jQuery('#mainwp_comments_table').tablesorter({
            cssAsc:"desc",
            cssDesc:"asc",
            textExtraction:function (node) {
                if (jQuery(node).find('abbr').length == 0) {
                    return node.innerHTML
                } else {
                    return jQuery(node).find('abbr')[0].title;
                }
            },
            selectorHeaders: "> thead th:not(:first), > thead td:not(:first), > tfoot th:not(:first), > tfoot td:not(:first)"
        }).tablesorterPager({container:jQuery("#pager")});
    }
};

mainwp_fetch_comments = function (postId) {
    var errors = [];
    var selected_sites = [];
    var selected_groups = [];

    if (jQuery('#select_by').val() == 'site') {
        jQuery("input[name='selected_sites[]']:checked").each(function (i) {
            selected_sites.push(jQuery(this).val());
        });
        if (selected_sites.length == 0) {
            errors.push('Please select websites or groups.');
            jQuery('#selected_sites').addClass('form-invalid');
        }
        else {
            jQuery('#selected_sites').removeClass('form-invalid');
        }
    }
    else {
        jQuery("input[name='selected_groups[]']:checked").each(function (i) {
            selected_groups.push(jQuery(this).val());
        });
        if (selected_groups.length == 0) {
            errors.push('Please select websites or groups.');
            jQuery('#selected_groups').addClass('form-invalid');
        }
        else {
            jQuery('#selected_groups').removeClass('form-invalid');
        }
    }

    var status = "";
    var statuses = ['approve', 'hold', 'spam', 'trash'];
    for (var i = 0; i < statuses.length; i++) {
        if (jQuery('#mainwp_comment_search_type_' + statuses[i]).attr('checked')) {
            if (status != "") status += ",";
            status += statuses[i];
        }
    }
    if (status == "") {
        errors.push('Please select a comment status.');
    }

    if (errors.length > 0) {
        jQuery('#mainwp_comments_error').html(errors.join('<br />'));
        jQuery('#mainwp_comments_error').show();
        return;
    }
    else {
        jQuery('#mainwp_comments_error').html("");
        jQuery('#mainwp_comments_error').hide();
    }

    var data = {
        action:'mainwp_comments_search',
        keyword:jQuery('#mainwp_comment_search_by_keyword').val(),
        dtsstart:jQuery('#mainwp_comment_search_by_dtsstart').val(),
        dtsstop:jQuery('#mainwp_comment_search_by_dtsstop').val(),
        status:status,
        'groups[]':selected_groups,
        'sites[]':selected_sites,
        postId: postId
    };

    jQuery('#mainwp_comments_loading').show();
    jQuery.post(ajaxurl, data, function (response) {
        response = jQuery.trim(response);
        jQuery('#mainwp_comments_loading').hide();
        jQuery('#mainwp_comments_main').show();
        var matches = (response == null ? null : response.match(/comment\[\]/g));
        jQuery('#mainwp_comments_total').html(matches == null ? 0 : matches.length);
        jQuery('#the-comment-list').html(response);
        jQuery('#mainwp_comments_bulk_action_apply').removeAttr('disabled');
        mainwp_comments_table_reinit();
    });
};

mainwp_bulk_actions_comments = function(what) {
    if (jQuery('#bulk_comment_ids') == undefined || jQuery('#bulk_comment_ids').val() == '')
        return false;
    if (jQuery('#bulk_comment_wpids') == undefined || jQuery('#bulk_comment_wpids').val() == '')
        return false;
    if (confirm("Are you sure to apply the action to all search results!") != true) return false;

    var data = {
        action: 'mainwp_comment_' + what,
        comment_ids:jQuery('#bulk_comment_ids').val(),
        comment_wpids:jQuery('#bulk_comment_wpids').val()
    };
    jQuery('#mainwp_comments_bulk_action_apply').attr('disabled', 'true');
    jQuery('#mainwp_bulk_action_applying').show();
    jQuery.post(ajaxurl, data, function (response) {
        jQuery('#mainwp_bulk_action_applying').hide();
        jQuery('#mainwp_comments_main').show();
        jQuery('#the-comment-list').html('<tr><td colspan="7">' + response.message + '</td></tr>');
        jQuery('#mainwp_comments_bulk_action_apply').removeAttr('disabled');
        jQuery('#bulk_comment_ids').val('');
        jQuery('#bulk_comment_wpids').val('');
    }, 'json');

    return false;

};

jQuery(document).ready(function () {
    jQuery('.mainwp-comment-unapprove').live('click', function () {
        commentAction(jQuery(this), 'unapprove');
        return false;
    });
    jQuery('.mainwp-comment-approve').live('click', function () {
        commentAction(jQuery(this), 'approve');
        return false;
    });
    jQuery('.mainwp-comment-spam').live('click', function () {
        commentAction(jQuery(this), 'spam');
        return false;
    });
    jQuery('.mainwp-comment-trash').live('click', function () {
        commentAction(jQuery(this), 'trash');
        return false;
    });
    jQuery('#recent_comments_approved_lnk').live('click', function () {
        showRecentCommentList(true, false);
        return false;
    });
    jQuery('#recent_comments_pending_lnk').live('click', function () {
        showRecentCommentList(false, true);
        return false;
    });
});
commentAction = function (elem, what) {
    var rowElement = jQuery(elem).parent().parent();
    var commentId = rowElement.children('.commentId').val();
    var websiteId = rowElement.children('.websiteId').val();

    var data ={
        action:'mainwp_comment_' + what,
        commentId:commentId,
        websiteId:websiteId,
        security: mainwp_comments_security_nonces['mainwp_comment_' + what]
    };
    rowElement.children('.mainwp-row-actions').hide();
    rowElement.children('.mainwp-row-actions-working').show();
    jQuery.post(ajaxurl, data, function (response) {
        if (response.result) {
            rowElement.html(response.result);
        }
        else {
            rowElement.children('.mainwp-row-actions-working').hide();
        }
    }, 'json');

    return false;
};
showRecentCommentList = function (approved, pending) {
    if (approved) jQuery("#recent_comments_approved_lnk").addClass('mainwp_action_down');
    else jQuery("#recent_comments_approved_lnk").removeClass('mainwp_action_down');

    if (pending) jQuery("#recent_comments_pending_lnk").addClass('mainwp_action_down');
    else jQuery("#recent_comments_pending_lnk").removeClass('mainwp_action_down');

    if (approved) jQuery("#recent_comments_approved").show();
    if (pending) jQuery("#recent_comments_pending").show();

    if (!approved) jQuery("#recent_comments_approved").hide();
    if (!pending) jQuery("#recent_comments_pending").hide();
};
