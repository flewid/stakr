<div class="wrap monitors">    <h2><?php echo MvcInflector::titleize( $model->name ); ?></h2>
    <div class="aum_legend">
        <div class="item">
            <div class="color green"></div>
            Up
        </div>
        <div class="item">
            <div class="color red"></div>
            Down
        </div>
        <div class="item">
            <div class="color yellow"></div>
            Seems Off
        </div>
        <div class="item">
            <div class="color grey"></div>
            Paused
        </div>
        <div class="item">
            <div class="color light-grey"></div>
            Not Checked
        </div>
        <div class="clear"></div>
    </div>

    <input type="button" class="aum_button aum_add_new_button urm_add_new_monitor_button" value="+ ADD NEW" />
    <div class="clear"></div>   
    <?php
	if ( ! empty( $objects ) ) {
		echo '<table class="aum_monitors_list">';
		foreach ( $objects as $object ) {
			?>  
            <tr class="monitor_row" monitor_id="<?php echo esc_attr( $object->monitor_id ); ?>">
                <td class="monitor_name" colspan=2>
                    <img src="<?php echo plugins_url( 'images/monitor_active.gif', __FILE__ ) ?>" class="monitor_status" />
                    <span><?php echo esc_html( $object->monitor_name ); ?></span>
                </td>
                <td class="actions" style="padding-right: 10px;">
                    <!--            
                                                    <a href="javascript:void(0)" class="aum_action_link monitor_stats_link"><img src="<?php echo plugins_url( 'images/stats_monitor.gif', __FILE__ ) ?>" /></a>
                    -->             
                    <a href="javascript:void(0)" class="aum_action_link monitor_edit_link"><img src="<?php echo plugins_url( 'images/edit_monitor.gif', __FILE__ ) ?>" /></a>
                    <a href="javascript:void(0)" class="aum_action_link monitor_delete_link"><img src="<?php echo plugins_url( 'images/delete_monitor.gif', __FILE__ ) ?>" /></a>
                </td>
            </tr>
            <?php
		}
		echo '</table>';
	} else {
		?>
        <div class="warning" id="message">
            <p>
                The account has no monitors
            </p>
        </div>
        <?php
	}
	?>  

</div>
