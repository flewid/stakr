<?php
if ( ! is_array( $this->params['data']['UptimeMonitor'] ) ) {  // is it object retrieved for monitor editing?
	$monitor = $this->params['data']['UptimeMonitor'];
	$this->params['data']['UptimeMonitor'] = array();
	$this->params['data']['UptimeMonitor']['monitor_name'] = $monitor->monitor_name;
	$this->params['data']['UptimeMonitor']['monitor_api_key'] = $monitor->monitor_api_key;
	$this->params['data']['UptimeMonitor']['monitor_not_email'] = $monitor->monitor_not_email;
	$this->params['data']['UptimeMonitor']['monitor_type4'] = $monitor->monitor_type % 10;
	$monitor->monitor_type = $monitor->monitor_type / 10;
	$this->params['data']['UptimeMonitor']['monitor_type3'] = $monitor->monitor_type % 10;
	$monitor->monitor_type = $monitor->monitor_type / 10;
	$this->params['data']['UptimeMonitor']['monitor_type2'] = $monitor->monitor_type % 10;
	$monitor->monitor_type = $monitor->monitor_type / 10;
	$this->params['data']['UptimeMonitor']['monitor_type1'] = $monitor->monitor_type % 10;
	$this->params['data']['UptimeMonitor']['monitor_id'] = $monitor->monitor_id;
}
?>
<fieldset class="monitor_form form_fieldset uptime_monitor_popup">
    <h3>Create a Monitor</h3>
    <?php $this->display_flash(); ?>
    <?php echo $this->form->create( $model->name ); ?>
    <?php
	if ( isset( $this->params['monitor_id'] ) ) {
		echo $this->form->hidden_input( 'monitor_id', array( 'value' => $this->params['monitor_id'] ) );
	}
	?>

    <?php echo $this->form->input( 'monitor_name', array( 'label' => 'Monitor Name:', 'value' => (isset( $this->params['data']['UptimeMonitor'] ) ? $this->params['data']['UptimeMonitor']['monitor_name'] : '') ) ); ?>
    <?php echo $this->form->input( 'monitor_api_key', array( 'label' => 'Monitor API key:', 'value' => (isset( $this->params['data']['UptimeMonitor'] ) ? $this->params['data']['UptimeMonitor']['monitor_api_key'] : '') ) ); ?>
    <!--    
<?php echo $this->form->input( 'monitor_not_email', array( 'label' => 'Monitor Notification Email:', 'value' => (isset( $this->params['data']['UptimeMonitor'] ) ? $this->params['data']['UptimeMonitor']['monitor_not_email'] : '') ) ); ?>
    --> 
    <!--            
        <div>   
            <label>Monitor Type:</label>
    <?php
	$monitor_types = array( '1' => 'HTTP(s)', '2' => 'Keyword Checking', '3' => 'Ping', '4' => 'TCP Ports' );
	$args = array( 'options' => $monitor_types );
	if ( isset( $this->params['data']['UptimeMonitor'] ) ) {
		$args['value'] = $this->params['data']['UptimeMonitor']['monitor_type'];
	}
	echo $this->form->select( 'data[UptimeMonitor][monitor_type]', array( 'options' => $monitor_types ) );
	?>
            <div class="monitor_type_checkboxes">
    <?php
	$checkbox1_opts = array( 'label' => 'HTTP(s)', 'value' => '1' );
	if ( isset( $this->params['data']['UptimeMonitor'] ) && (int) $this->params['data']['UptimeMonitor']['monitor_type1'] == 1 ) {
		$checkbox1_opts['checked'] = 'checked';
	}
	echo $this->form->checkbox_input( 'monitor_type1', $checkbox1_opts )
	?>
    <?php
	$checkbox2_opts = array( 'label' => 'Keyword Checking', 'value' => '1' );
	if ( isset( $this->params['data']['UptimeMonitor'] ) && (int) $this->params['data']['UptimeMonitor']['monitor_type2'] == 1 ) {
		$checkbox2_opts['checked'] = 'checked';
	}
	echo $this->form->checkbox_input( 'monitor_type2', $checkbox2_opts )
	?>
    <?php
	$checkbox3_opts = array( 'label' => 'Ping', 'value' => '1' );
	if ( isset( $this->params['data']['UptimeMonitor'] ) && (int) $this->params['data']['UptimeMonitor']['monitor_type3'] == 1 ) {
		$checkbox3_opts['checked'] = 'checked';
	}
	echo $this->form->checkbox_input( 'monitor_type3', $checkbox3_opts )
	?>
    <?php
	$checkbox4_opts = array( 'label' => 'TCP Ports', 'value' => '1' );
	if ( isset( $this->params['data']['UptimeMonitor'] ) && (int) $this->params['data']['UptimeMonitor']['monitor_type4'] == 1 ) {
		$checkbox4_opts['checked'] = 'checked';
	}
	echo $this->form->checkbox_input( 'monitor_type4', $checkbox4_opts )
	?>     
            </div>
            <div class="clear"></div>
        </div>
    -->         
<?php
$submit_text = isset( $this->params['data']['UptimeMonitor']['monitor_id'] ) ? 'Save' : 'Create';
echo $this->form->end( $submit_text );
?>
</fieldset>
<a href="#" class="close_link"><img src="<?php echo plugins_url( 'images/close.png', __FILE__ ) ?>" /></a>
<script>
    jQuery(document).ready(function () {
        var admin_url = '<?php echo get_admin_url() ?>';
        jQuery('.monitor_form form input[type="submit"]').addClass('aum_button');
        jQuery('.monitor_form form').attr('action', admin_url + 'admin-ajax.php?action=admin_uptime_monitors_monitor_form&wp_nonce=<?php wp_create_nonce( AdminUptimeMonitorsController::$nonce_token . 'monitor_form' ); ?>');
        jQuery('a.close_link').click(function () {
            var el = window.frameElement;
            el.parentNode.removeChild(el);
        })

<?php
if ( $monitor_saved ) {

	$monitor = $this->params['data']['UptimeMonitor'];
	?>

            jQuery('.monitor_form').find('input').attr('disabled', 'disabled');
            html = '<tr class="monitor_row new" monitor_id="<?php echo $monitor_id ?>">';
            html += '<td class="monitor_name" colspan=2>';
            html += '<img src="<?php echo plugins_url( 'images/monitor_active.gif', __FILE__ ) ?>" class="monitor_status" />';
            html += '<?php echo $monitor['monitor_name'] ?>';
            html += '</td>';
            html += '<td class="actions" style="padding-right:10px;" align=center>';
            html += '<a href="javascript:void(0)" class="aum_action_link"><img src="<?php echo plugins_url( 'images/stats_monitor.gif', __FILE__ ) ?>" /></a>';
            html += '<a href="javascript:void(0)" class="aum_action_link"><img src="<?php echo plugins_url( 'images/edit_monitor.gif', __FILE__ ) ?>" /></a>';
            html += '<a href="javascript:void(0)" class="aum_action_link"><img src="<?php echo plugins_url( 'images/delete_monitor.gif', __FILE__ ) ?>" /></a>';
            html += '</td>';
            html += '</tr>';

            var el = window.frameElement;

    // jQuery(el.parentNode).find('.aum_monitors_list').append(html);

            /*
             jQuery(el.parentNode).find('.aum_monitors_list tr').eq(jQuery(el.parentNode).find('.aum_monitors_list tr').length-1).find('td.monitor_name').bind('click',function(){
             get_monitor_urls(jQuery(this).parent());
             })
             */

            setTimeout(function () {
                parent.location.reload(true);
            }, 2000);
    <?php
}
?>
    })
</script>
