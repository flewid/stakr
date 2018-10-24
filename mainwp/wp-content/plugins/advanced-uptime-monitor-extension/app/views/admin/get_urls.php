<?php
if ( ! empty( $urls ) ) {
	?>
    <tr class="url_row" url_id="<?php echo esc_attr( $url->url_id ); ?>">
        <td colspan="3" class="url_cell aum_url_header">
            <div class = ""cell aum_url_checkbox" style="float:left; margin-right:10px;">
                 <input type="checkbox" name="checkall" class = "url_checkall" id="url_checkall" style="float:left; margin-right:10px;">
            </div>
            <div class="cell url_name">
                SITE
            </div>
            <div class="cell url_diagram">
                <?php
				$hour = (int) date( 'H' );
				if ( 24 == $hour ) {
					for ( $i = 1; $i <= $hour; $i++ ) {
						echo '<div class="aum_time">' . intval( $i ) . '</div>'; }
				} else {
					$begin_hour = $hour + 1;
					for ( $i = $begin_hour; $i <= 24; $i++ ) {
						echo '<div class="aum_time">' . intval( $i ) . '</div>';
					}
					for ( $i = 1; $i <= $hour; $i++ ) {
						echo '<div class="aum_time">' . intval( $i ) . '</div>';
					}
				}
				?>              
            </div>
            <div style="float:left;margin-left: 340px;">
                DISPLAY
            </div>
            <div class="cell url_status">
                STATUS
            </div>              
            <div class="cell url_actions right">
                ACTION
            </div>                      
        </td>
    </tr>
    <?php
	foreach ( $urls as $url ) {
		?>

        <tr class="url_row url_data" url_id="<?php echo intval( $url->url_id ); ?>">
            <td colspan="3" class="url_cell">
                <div class = ""cell aum_url_checkbox" style="float:left; margin-right:10px;">
                     <input type="checkbox" name="checkbox_url" class = "checkbox_url" id="checkbox_url" style="float:left; margin-right:10px;">
                </div>
                <div class="cell url_name">     
                    <?php echo esc_html( ! empty( $url->url_friendly_name ) ? $url->url_friendly_name : $url->url_address ); ?>
                </div>
                <div id= "url_adress" style="display: none;">
                    <?php echo esc_html( $url->url_address ); ?>
                </div>
                <div id="loading_status"  class="aum_mainwp_uptime_monitor_loading monitor_actions_loading">
                    <i class="fa fa-spinner fa-pulse"></i>   
                </div>
                <?php
				$stats_indexes = array_keys( $stats[ $url->url_uptime_monitor_id ] );
				$last_event = $stats[ $url->url_uptime_monitor_id ][ $stats_indexes[ count( $stats_indexes ) - 1 ] ];
				?>                      
                <div class="cell url_diagram" last_event="<?php esc_html( $last_event->type ); ?>">
                    <?php
					$i = 0;
					foreach ( $stats[ $url->url_uptime_monitor_id ] as $index => $event ) {
						//          echo '<div class="dot" style="left:'.($event->point_pos-6).'px"></div>';
						$event_fill_from = $event->point_pos;
						$event_fill_to = isset( $stats[ $url->url_uptime_monitor_id ][ $stats_indexes[ $i + 1 ] ] ) ? $stats[ $url->url_uptime_monitor_id ][ $stats_indexes[ $i + 1 ] ]->point_pos : $diagram_width;
						echo '<div class="event_fill ' . ($event->type ? $event->type : 'not_checked') . '" style="width:' . ($event_fill_to - $event_fill_from) . 'px;"></div>';
						//          if($url->url_uptime_monitor_id == '775789070')
						//              var_dump($event_fill_from,$event_fill_to);
						$i++;
					}
					echo '<div class="clear"></div>';
					echo '<div class="aum_diagram_overlay"></div>';
					?>              
                </div>
                <div class="cell url_status <?php echo ($url->monitor_type == '1' ? ($last_event->type ? $last_event->type : 'not_checked') : 'paused') ?>"><?php echo (int) $url->uptime_ratio == 0 || (int) $url->uptime_ratio / $url->uptime_ratio == 1 ? $url->uptime_ratio : number_format( $url->uptime_ratio, 2, '.', '' ) ?>%</div>
                <div class="cell url_display" style="display: block; margin-left: 220px;">
                    <img src="" class="monitor_status"  >               
                </div> 
                <div class="cell url_actions right">
                    <?php
					if ( $url->monitor_type != '1' ) {
						?>          
                        <div class="aum_action_link status_link start"><i class="fa fa-play fa-lg"></i></div>           
                        <?php
					} else {
						?>
                        <div class="aum_action_link status_link pause"><i class="fa fa-pause fa-lg"></i></div>
                        <?php
					}
					?>
                    <a href="javascript:void(0)" class="aum_action_link stats_link"><i class="fa fa-bar-chart fa-lg"></i></a>
                    <a href="javascript:void(0)" class="aum_action_link url_edit_link"><i class="fa fa-pencil-square-o fa-lg"></i></a>
                    <a href="javascript:void(0)" class="aum_action_link url_delete_link"><i class="fa fa-trash-o fa-lg"></i></a>
                </div>

            </td>
        </tr>
        <?php
	}
}
?>
<script>
    jQuery('.aum_add_new_url_button').click(function (event) {
        /*
         jQuery(this).parent().hide();
         
         new_url_html='<td colspan=3  class="url_cell">';
         new_url_html += '<input type="text" name="url_address" placeholder="URL" />&nbsp;';
         new_url_html += '<input type="button" value="Add" class="aum_button aum_button2 new_url_submit" />';
         new_url_html += '<input type="button" value="Cancel" class="aum_button2 aum_button3 cancel_url" />';
         new_url_html += '</td>';
         jQuery(this).parent().parent().append(new_url_html);
         
         jQuery('.new_url_submit').bind('click',function(){
         var data = {
         action: 'admin_uptime_monitors_urls_add_url',
         monitor_id: jQuery(this).parent().parent().attr('monitor_id'),
         url_address : jQuery('input[name="url_address"]').val()
         };
         // ajaxurl is defined by WordPress
         jQuery('.new_url_submit').parent().append('<img src="../wp-content/plugins/uptime-robot-monitors/app/views/admin/images/loading.gif" class="aumloading" />');
         jQuery.post(ajaxurl, data, function(response){      
         jQuery('.aumloading').remove();
         insert_urls_rows(jQuery('.active_monitor').parent(),response)
         })
         })
         
         jQuery('.cancel_url').bind('click',function(){
         jQuery(this).parent().prev().show();
         jQuery(this).parent().remove();
         })
         */
        monitor_name = jQuery(this).parent().parent().prevAll('.monitor_row').eq(0).find('td.monitor_name span').html();
        monitor_id = jQuery(this).parent().parent().prevAll('.monitor_row').eq(0).attr('monitor_id');
        inline_window('url_form&monitor_id=' + monitor_id, 'Add monitor', jQuery('.monitors'), 500, 370, event);
    })

    jQuery('.url_delete_link').bind('click', function () {
        if (!confirm('Are you sure to delele selected item?'))
            return;
        url_row_obj = jQuery(this).parent().parent().parent();
        jQuery.post(ajaxurl, {action: 'admin_uptime_monitors_delete_url', 'url_id': jQuery(this).parent().parent().parent().attr('url_id')}, function (response) {
            if (response == 'success')
                url_row_obj.remove();
        });
    })

    jQuery('.url_edit_link').click(function (event) {
        inline_window('update_url&url_id=' + jQuery(this).parent().parent().parent().attr('url_id'), 'Update URL', jQuery('.monitors'), 500, 370, event);
    })

    jQuery('.status_link').click(function (event) {
        var current_status = jQuery(this).hasClass('start') ? 'start' : 'pause';
        var status_link_obj = jQuery(this);

        var data = {
            action: 'admin_uptime_monitors_url_' + (jQuery(this).hasClass('start') ? 'start' : 'pause'),
            url_id: jQuery(this).parent().parent().parent().attr('url_id')
        };
        show_loading(event);
        jQuery.post(ajaxurl, data, function (response) {
            hide_loading();
            if (response == 'success')
                if (current_status == 'start') {
                    status_link_obj.removeClass('start').addClass('pause');
                    status_link_obj.parent().parent().find('.url_status').removeClass('paused').addClass(status_link_obj.parent().parent().find('.url_diagram').attr('last_event'));
                }
                else {
                    status_link_obj.removeClass('pause').addClass('start');
                    status_link_obj.parent().parent().find('.url_status').removeClass('down').removeClass('up').addClass('paused');
                }
        });
    })
    jQuery('.stats_link').click(function (event) {
        url_id = jQuery(this).parent().parent().parent().attr('url_id');
        inline_window2('statistics_table&url_id=' + url_id, 'URL Statistics And Reports', jQuery('.monitors'), 500, 600, event);
    })
    jQuery('input[name=checkall]').click(function () {

        if (jQuery(this).is(':checked'))
        {
            jQuery('input[name=checkbox_url]').each(function () {
                jQuery(this).attr('checked', 'checked');

            })
        }
        else
        {
            jQuery('input[name=checkbox_url]').each(function () {
                jQuery(this).removeAttr('checked');
            })
        }


    })
</script>
