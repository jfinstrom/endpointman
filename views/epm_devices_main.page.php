<?php
/**
 * Endpoint Manager Devices Manager File
 *
 * BLEEEECKKKKKK, There I just puked all over this file. That's basically what it looks like in terms of code.
 * It's a hacked-together POS written by me (Andrew) and I really need to fix it ASAP!! ah!
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 *
 */

	if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

	//Set opened variables
	$message = NULL;
	$error_message = NULL;
	$final = NULL;
	$button = NULL;
	$searched = NULL;
	$edit = NULL;
	$mode = NULL;
	
	$family_list = FreePBX::Endpointman()->eda->all_products();
	$full_device_list = FreePBX::Endpointman()->eda->all_devices();
	$ava_exts = FreePBX::Endpointman()->display_registration_list();
	
	if((empty($family_list)) && (empty($full_device_list))) 
	{
		echo '<div class="alert alert-warning" role="alert">';
		echo '<strong>'._("Warning!").'</strong>'.(" Welcome to Endpoint Manager. You have no products (Modules) installed, click").' <a href="config.php?display=epm_config"><b>'._("here").'</b></a> '._(" to install some");
		echo '</div>';
//$endpoint->global_cfg['new'] = 1;
	} 
	elseif(FreePBX::Endpointman()->configmod->get("srvip") == "") 
	{
		echo '<div class="alert alert-warning" role="alert">';
		echo '<strong>'._("Warning!").'</strong>'.(" Your Global Variables are not set! Please head on over to ").'<a href="config.php?display=epm_advanced"><b>'._("Advanced Settings").'</b></a>'._(" to setup your configuration");
		echo '</div>';
	} 
	elseif(empty($ava_exts)) {
		//$message = "You have no more devices or extensions avalible to configure!";
		//$no_add = TRUE;
	}

	
	
	
	
	
	
	
	
	
	

	//Refresh the list after processing
	$devices_list = $full_device_list;
	
	$i = 0;
	$list = [];
	$device_statuses = shell_exec(FreePBX::Endpointman()->configmod->get("asterisk_location")." -rx 'sip show peers'");
	
	$device_statuses = explode("\n", $device_statuses);
	$devices_status = [];
	foreach($device_statuses as $data) {
		preg_match('/(\d*)\/[\d]*/i', $data, $extout);
		preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}\b/i', $data, $ipaddress);
		if(!empty($extout[1])) {
			if(preg_match('/OK \(.*\)/i', $data)) {
				$devices_status[$extout[1]]['status'] = TRUE;
				$devices_status[$extout[1]]['ip'] = $ipaddress[0];
			} else {
				$devices_status[$extout[1]]['status'] = FALSE;
			}
		}
	}
	
	foreach($devices_list as $devices_row) {
		$line_list = FreePBX::Endpointman()->eda->get_lines_from_device($devices_row['id']);
		$list[$i] = $devices_row;
		$z = 0;
		if (($devices_row['template_id'] == 0) && (isset($devices_row['global_custom_cfg_data'])) ) {
			$list[$i]['template_name'] = "Custom-".$devices_row['mac'];
		} elseif((!isset($devices_row['custom_cfg_data'])) && ($devices_row['template_id'] == 0)) {
			$list[$i]['template_name'] = "N/A";
		} else {
			$sql = "SELECT name FROM endpointman_template_list WHERE id =".$devices_row['template_id'];
			$template_name = sql($sql,'getOne');
			$list[$i]['template_name'] = $template_name;
		}
		if (!$devices_row['enabled']) {
			$list[$i]['model'] = $devices_row['model']."<i>(Disabled)</i>";
		}
		$list[$i]['master_id'] = $i;
		foreach($line_list as $line_row) {
			$list[$i]['line'][$z]['ext'] = $line_row['ext'];
			$list[$i]['line'][$z]['line'] = $line_row['line'];
			$list[$i]['line'][$z]['description'] = $line_row['description'];
			$list[$i]['line'][$z]['luid'] = $line_row['luid'];
			$list[$i]['line'][$z]['ipei'] = $line_row['ipei'];
			$list[$i]['line'][$z]['master_id'] = $i;
			$z++;
		}
		$ext = $list[$i]['line'][0]['ext'];
	
		$list[$i]['status']['status'] = $devices_status[$ext]['status'] ?? FALSE;
		$list[$i]['status']['ip'] = $devices_status[$ext]['ip'] ?? FALSE;
		$list[$i]['status']['port'] = '';
		$i++;
	}
	
	$unknown_list = FreePBX::Endpointman()->eda->all_unknown_devices();
	
	foreach($unknown_list as $row) {	#Displays unknown phones in the database with edit and delete buttons
		$list[$i] = $row;
	
		$brand_info = FreePBX::Endpointman()->get_brand_from_mac($row['mac']);
	
		$list[$i]['name'] = $brand_info['name'];
		$list[$i]['template_name'] = "N/A";
		$list[$i]['model'] = _("Unknown");
		$i++;
	}
	
$amp_send['AMPDBUSER'] = $amp_conf['AMPDBUSER'];
$amp_send['AMPDBPASS'] = $amp_conf['AMPDBPASS'];
$amp_send['AMPDBNAME'] = $amp_conf['AMPDBNAME'];
	
	$sql = "SELECT DISTINCT endpointman_product_list.* FROM endpointman_product_list, endpointman_model_list WHERE endpointman_product_list.id = endpointman_model_list.product_id AND endpointman_model_list.hidden = 0 AND endpointman_model_list.enabled = 1 AND endpointman_product_list.hidden != 1 AND endpointman_product_list.cfg_dir !=  ''";
	$template_list = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);
	$i = 1;
	$product_list = [];
	$product_list[0]['value'] = 0;
	$product_list[0]['text'] = "";
	foreach($template_list as $row) {
		$product_list[$i]['value'] = $row['id'];
		$product_list[$i]['text'] = $row['short_name'];
		$i++;
	}
	
	$sql = "SELECT DISTINCT endpointman_model_list.* FROM endpointman_product_list, endpointman_model_list WHERE endpointman_product_list.id = endpointman_model_list.product_id AND endpointman_model_list.hidden = 0 AND endpointman_model_list.enabled = 1 AND endpointman_product_list.hidden != 1 AND endpointman_product_list.cfg_dir !=  ''";
	$template_list = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);
	$i = 1;
	$model_list = [];
	$model_list[0]['value'] = 0;
	$model_list[0]['text'] = "";
	foreach($template_list as $row) {
		$model_list[$i]['value'] = $row['id'];
		$model_list[$i]['text'] = $row['model'];
		$i++;
	}
	
	
	
	
	
/*
	
$endpoint->tpl->assign("list", $list);

$serv_address = !empty($endpoint->global_cfg['nmap_search']) ? $endpoint->global_cfg['nmap_search'] : $_SERVER["SERVER_ADDR"].'/24';

$endpoint->tpl->assign("netmask", $serv_address);
$endpoint->tpl->assign("web_var", "?type=$type");

$ma = $endpoint->models_available();

if($ma != FALSE) {
	$endpoint->tpl->assign("models_ava", $ma);
}

$endpoint->tpl->assign("product_list", $product_list);
$endpoint->tpl->assign("model_list", $model_list);
$endpoint->tpl->assign("display_ext", $endpoint->display_registration_list());
$endpoint->tpl->assign("brand_ava", $endpoint->brands_available());
$endpoint->tpl->assign("unmanaged", $final);
$endpoint->tpl->assign("button", $button);
$endpoint->tpl->assign("searched", $searched);
$endpoint->tpl->assign("edit", $edit);
$endpoint->tpl->assign("amp_conf_serial", base64_encode(serialize($amp_send)));
$endpoint->tpl->assign("mode", $mode);
	
$edit_row['id'] = isset($edit_row['id']) ? $edit_row['id'] : '0';
$endpoint->tpl->assign("edit_id", $edit_row['id']);

*/


/*
if(isset($final)) {
	$_SESSION['dev_cache'] = base64_encode(serialize($final));
}
*/




/*
if (isset($mode) && ($mode == "EDIT")) {
	$ma = $endpoint->models_available($edit_row['model_id'],$edit_row['brand_id']);
	if($ma != FALSE) {
		$endpoint->tpl->assign("mac", $edit_row['mac']);
		$endpoint->tpl->assign("name", $edit_row['name']);
		$b=0;
		foreach($edit_row['line'] as $data) {
			$edit_row['line'][$data['line']]['reg_list'] = $endpoint->display_registration_list($data['luid']);
			$edit_row['line'][$data['line']]['line_list'] = $endpoint->linesAvailable($data['luid']);
			$b++;
		}
		if($b == 1) {
			$endpoint->tpl->assign("disabled_delete_line", 1);
		}
		$endpoint->tpl->assign("line_list_edit", $edit_row['line']);
	
		$endpoint->tpl->assign("brand_id", $edit_row['brand_id']);
		$endpoint->tpl->assign("models_ava", $ma);

		$endpoint->tpl->assign("display_templates", $endpoint->display_templates($edit_row['product_id'],$edit_row['template_id']));
	
	} else {
		$message = _("You have disabled/removed all models that correspond to this brand. Please enable them in 'Brand Configurations/Setup' before trying to edit this phone");
		$endpoint->tpl->assign("mode", NULL);
	}
}
*/
	
		
	
	
	
	
	//echo load_view(__DIR__.'/epm_templates/main.views.grid.php', array('request' => $_REQUEST));
	//echo load_view(__DIR__.'/epm_templates/main.views.new.modal.php', array('request' => $_REQUEST));
?>


<h3><?php echo _('Device')?></h3>
<table align='center' width='97%'>
	<thead>
		<tr>
			<th width="7%""></th>
			<th width="13%" align='center'><?php echo _('MAC Address')?></th>
			<th width="13%" align='center'><?php echo _('Brand')?></th>
			<th width="10%" align='center'><?php echo _('Model of Phone')?></th>
			<th width="10%" align='center'><?php echo _('Line')?></th>
			<th width="19%" align='center'><?php echo _('Extension Number')?></th>
			<th width="15%" align='center'><?php echo _('Template')?></th>
			<th width="6%"></th>
			<th width="7%"></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td align='center' width='2%'>&nbsp;</td>
    		<td align='center'>
				{$mac}<input name='mac' type='text' tabindex='1' size="17" maxlength="17">
			</td>
			<td align='center'>
				<label>
				{$name}
				<select name="brand_list" id="brand_edit">		
				<?php
				$brand_ava = FreePBX::Endpointman()->brands_available();
				foreach ($brand_ava as $row) 
				{
					echo '<option value="'.$row['value'].'" '.(isset($row['selected']) ? "selected" : "").'>'.$row['text'].'</option>';
				} 	
				?>
				</select>
				</label>
			</td>
			<td align='center'>
				<label>
					<input name="display" type="hidden" value="epm_devices">
					{loop name="models_ava"}
					<select name="model_list" id="model_new">
						<option value="{$value.value}" {if condition="!empty($value.selected)"}selected{/if}>{$value.text}</option>
					</select>
					{else}
					<select name="model_list" id="model_new"><option></option></select>
				</label>
			</td>
			<td align='center'>
				<label>
					<select name="line_list" id="line_list" >
						<option></option>
					</select>
				</label>
			</td>
			<td align='center'>
				<label>
				{loop name="display_ext"}
				<select name="ext_list" id="select">
            		<option value="{$value.value}">{$value.text}</option>
				</select>
				</label>
    		</td>
    
    		<td align='center'>
    			<label>  
    				<div id="demo">
    					{loop name="display_templates"}
    					<select name="template_list" id="template_list">
                			<option value="{$value.value}" {if condition="isset($value.selected)"}selected{/if}>{$value.text}</option>
            			</select>
            			<a href="#" onclick="return popitup('config.php?display=epm_config&amp;quietmode=1&amp;handler=file&amp;file=popup.html.php&amp;module=endpointman&amp;pop_type=edit_template&amp;edit_id={$edit_id}', 'Template Editor', '{$edit_id}')"><i class='icon-pencil'></i></a>
            		</div>
        		</label>
        	</td>
    		<td align='center'>
        		<button type='submit' name='button_save' onclick="edit_device('edit',{$edit_id},'button_save');"><i class='icon-save blue'></i> <?php echo _('Save')?></button>
				{else}
        		<button type='button' name='button_add' onclick="add_device();"><i class='icon-plus success'></i>&nbsp;<?php echo _('Add')?></button>
    		</td>

			<td align='center'>
				{if condition="$mode != 'EDIT'"}
				<button type='reset'><i class='icon-rotate-left red'></i> <?php echo _('Reset')?>{/if}
			</td>
		</tr>
			
			
	
		<!-- 
		{loop name="line_list_edit"}
		<tr>
			<td align='center' width='2%'>&nbsp;</td>
			<td align='center'></td>
    		<td align='center'></td>
    		<td align='center'></td>
    		<td align='center'>
        		<label>
        			{loop name="value.line_list"}
            		<select name="line_list_{$value.luid}" id="line_list" >
                		<option value="{$value.value}" {if condition="isset($value.selected)"}selected{/if}>{$value.text}</option>
            		</select>
        		</label>
        	</td>
			<td align='center'>
				<label>
					{loop name="value.reg_list"}
					<select name="ext_list_{$value.luid}" id="select">
            			<option value="{$value.value}" {if condition="isset($value.selected)"}selected{/if}>{$value.text}</option>
        			</select>
        		</label>
    		</td>
    		<td align='center'></td>
    		<td align='center'>
    			{if condition="!isset($disabled_delete_line)"}
    			<div id="demo"><a href="#" onclick="edit_device('edit',{$value.luid},'delete');"><i class="red icon-remove" title="Delete Line from Device to the Left"></i></a></div>
    			{/if}
    		</td>
    		<td align='center'></td>
		</tr>
		{/loop}
		 -->
	</tbody>
</table>





<!-- 
{if condition="$mode == 'EDIT'"}
<td align='center'><div id="demo"><a href="#" onclick="edit_device('edit',{$edit_id},'add_line_x');"><i class="green icon-plus" title="Add a Line to the device currently being edited"></i></a></div></td>
 -->





<?php 



return;
?>




{if condition="$searched == 1"}
<table width='90%' align='center'>
    <tr>
        <td align='center'>&nbsp;</td>
        <td align='center'>&nbsp;</td>
        <td align='center'>&nbsp;</td>
        <td colspan="3" align='center'><h3><?php echo _('Unmanaged Extensions')?></h3></td>
        <td align='center'>&nbsp;</td>
        <td align='center'>&nbsp;</td>
        <td align='center'>&nbsp;</td>
    </tr>
	{if condition="is_array($unmanaged)"}
    <form id="unmanaged" action='' method='POST'>
		{loop name="unmanaged"}
        <input name="mac_{$value.id}" type="hidden" value="{$value.mac_strip}">
        <input name="brand_{$value.id}" type="hidden" value="{$value.brand_id}">
        <tr id="{$value.mac_strip}">
            <td align='center' width='20'><input type="checkbox" name="add[]" value="{$value.id}"></td>
            <td align='center' width='148'>{$value.mac_strip}<br />({$value.ip})</td>
            <td width="188" align='center'>{$value.brand}</td>
            <td width="216" align='center'>

                <select name="model_list_{$value.id}">

	    {loop name="value.list"}

                    <option value="{$value.id}">{$value.model}</option>

	      {/loop}

                </select></td>
            <td width="141" align='center'>

            </td>

            <td width="276" align='center'>
                <select name="ext_list_{$value.id}" id="ext">

	    {loop name="display_ext"}

                    <option value="{$value.value}">{$value.text}</option>

	      {/loop}

                </select></td>
            <td align='center' width='220'>&nbsp;</td>
            <td align='center' width='154'></td>
            <td align='center' width='73'>&nbsp;</td>
        </tr>
		{/loop}
        <tr>
        <table width="90%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td><center><input type="submit" name="button_add_selected_phones" onclick="add_searched_devices();" value="<?php echo _('Add Selected Phones')?>"><br /><input type="checkbox" name="reboot_sel">Reboot Phones</center></td>
            </tr>
        </table>
        </tr>
    </form>
	{/if}
</table>
{/if}










<form id="managed" action='config.php?type=tool&amp;display=epm_devices' method='POST'>
<h3><?php echo _('Current Managed Extensions')?></h3>

<button type="button" id="selecter"   style="zoom: 0.8" onclick="togglePhones(true)"  ><i class="info icon-check"       id="toggle_all_phones_on"  title="Click to Select All Phones"  ></i> Select All</button>
<button type="button" id="deselecter" style="zoom: 0.8" onclick="togglePhones(false)" ><i class="info icon-check-empty" id="toggle_all_phones_off" title="Click to Deselect All Phones"></i> Deselect All</button>
<button type="button" id="expander"   style="zoom: 0.8" onclick="toggleDisplayAll('expand')"  ><i class="info icon-chevron-down" id="toggle_all_img" title="Click to Expand All Line Information"  ></i> Expand All</button>
<button type="button" id="collapser"  style="zoom: 0.8" onclick="toggleDisplayAll('collapse')"><i class="info icon-chevron-up"   id="toggle_all_img" title="Click to Collapse All Line Information"></i> Collapse All</button>
<table width='97%' align='center' id='devList'>
    <thead>
    <tr class="headerRow">
        <th width="7%""></th>
        <th width="13%" align='center'><?php echo _('MAC Address')?></th>
        <th width="13%" align='center'><?php echo _('Brand')?></th>
        <th width="10%" align='center'><?php echo _('Model of Phone')?></th>
        <th width="10%" align='center'><?php echo _('Line')?></th>
        <th width="19%" align='center'><?php echo _('Extension Number')?></th>
        <th width="15%" align='center'><?php echo _('Template')?></th>
        <th width="6%"><?php echo _('Edit')?></th>
        <th width="7%"><?php echo _('Delete')?></th>
    </tr>
    </thead>
    <tbody>
    
    
    $list
    
	{loop name="list"}
        <tr class="headerRow">
            <td align='center' width="7%"><i class="icon-off icon-large {if condition="$value.status.status === TRUE"}green{else}red{/if}" alt="{$value.status.ip}:{$value.status.port}"></i><input type="checkbox" class="device" name="selected[]" value="{$value.id}"></td>
            <td align='center' width='13%'>{$value.mac}</td>
            <td width="13%" align='center'>{$value.name}</td>
            <td width="10%" align='center'>{$value.model}</td>
            <td width="10%" align='center'><div id="demo"><a><i class="info icon-chevron-down" id="img2rowGroup{$value.master_id}" onclick="toggleDisplay(document.getElementById('devList'),'rowGroup{$value.master_id}')" title="Click to Expand Line Information"></i></a></div></td>
            <td width="19%" align='center'><div id="demo"><a><i class="info icon-chevron-down" id="img3rowGroup{$value.master_id}" onclick="toggleDisplay(document.getElementById('devList'),'rowGroup{$value.master_id}')" title="Click to Expand Line Information"></i></a></div></td>
            <td align='center' width='15%'><a href="#" onclick="submit_stype('edit',{$value.id});">{$value.template_name}</a></td>
            <td align='center' width='6%'><div id="demo"><a href="#" onclick="submit_wtype('edit',{$value.id});"><i class='blue icon-pencil' alt='<?php echo _('Edit')?>' title="Edit phone"></i></a></div></td>
            <td align='center' width='7%'><div id="demo"><a href="#" onclick="delete_device({$value.id});"><i class='red icon-trash' alt='<?php echo _('Delete')?>' title="Delete phone"></i></a></div></td>
        </tr>
        
        {loop name="value.line"}
        <tr class="rowGroup{$value.master_id} toggle_all" id="{$value.master_id}" style="display:none;">
            <td align='center' width='7%' ></td>
            <td align='center' width='13%'></td>
            <td width="13%" align='center'></td>
            <td width="10%" align='center'></td>
            <td width="10%" align='center'>{$value.line}</td>
            <td width="19%" align='center'>{$value.ext} - {$value.description}</td>
            <td align='center' width='15%'></td>
            <td align='center' width='6%'></td>
            <td align='center' width='7%'><div id="demo"><a href="#" onclick="submit_wtype('delete_line',{$value.luid});"><i class="red icon-remove" alt='<?php echo _('Delete')?>' title='Delete Line'></i></a></div></td>
        </tr>
        {/loop}
        
	{/loop}
	
    </tbody>
</table>

<h4><?php echo _('Selected Phone(s) Options')?></h4>
<p><button style="width: 100px" type="submit" style="vertical-align:middle" name="button_delete_selected_phones" onclick="managed_options('delete_selected_phones');"><i class="icon-trash red"></i> <?php echo _('Delete')?></button> <?php echo ('Delete Selected Phones')?></p>
<p><button style="width: 100px" type="submit" name="button_rebuild_selected" onclick="managed_options('rebuild_selected_phones');"><i class="icon-refresh green"></i> <?php echo _('Rebuild')?></button> <?php echo _('Rebuild Configs for Selected Phones')?> (<label><input type="checkbox" name="reboot"><font size="-1">Reboot Phones</font></label>)</p>
<p><button style="width: 100px" style="vertical-align:middle" type="submit" name="button_update_phones" onclick="managed_options('change_brand');"><i class='blue icon-random'></i> <?php echo _('Update')?></button>
<?php echo _('Change Selected Phones to')?>&nbsp;
<select name="brand_list_selected" id="brand_list_selected"><option><?php echo _('Brand')?></option>{loop name="brand_ava"}<option value="{$value.value}" {if condition="isset($value.selected)"}selected{/if}>{$value.text}</option>{/loop}</select> <select name="model_list_selected" id="model_list_selected"><option><?php echo _('Model')?></option></select> (<label><input type="checkbox" name="reboot_change"><font size="-1">Reboot Phones</font></label>)</p>
</form>











<h4><?php echo _('Global Phone Options')?></h4>
{if condition="$no_add == FALSE"}
<p>
<form id='go' action='config.php?type=tool&amp;display=epm_devices' method='POST'>
  <button style="width: 100px" type="Submit" name="button_go" id="button_go" onclick="find_devices();"><i class='icon-search blue'></i> <?php echo _('Search')?></button>
  <?php echo _('Search for new devices in netmask')?>
  <input name="netmask" type="text" value="{$netmask}">
  (<label><input name="nmap" type="checkbox" value="1" checked><font size="-1"><?php echo _('Use NMAP')?></font></label>)
</form>
</p>
{/if}

<p><form action='' name='globalmanaged' id='globalmanaged' method='POST'>
<button style="width: 100px" type='Submit' name='button_rebuild_configs_for_all_phones' onclick="submit_global('rebuild_configs_for_all_phones');"><i class='icon-refresh green'></i> <?php echo _('Rebuild')?></button> <?php echo _('Rebuild Configs for All Phones')?>&nbsp;(<label><input type="checkbox" name="reboot"><font size="-1">Reboot Phones</font></label>)
</form></p>

<p><form action='' name='globalmanaged2' id='globalmanaged2' method='POST'>
<button style="width: 100px" type='Submit' name='button_reboot_this_brand' onclick="submit_global2('reboot_brand');"><i class='icon-off red'></i> <?php echo _('Reboot')?></button> <?php echo _('Reboot This Brand')?> <select name="rb_brand">{loop name="brand_ava"}<option value="{$value.value}">{$value.text}</option>{/loop}</select>
</form></p>

<p><form action='' name='globalmanaged3' id='globalmanaged3' method='POST'>
<button style="width: 100px" type="submit" name="button_rebuild_reboot" onclick="submit_global3('rebuild_reboot');"><i class="icon-random blue"></i> <?php echo _('Configure')?></button>
<?php echo _('Reconfigure all')?> (products) <select name="product_select" id="product_select">{loop name="product_list"}<option value="{$value.value}">{$value.text}</option>{/loop}</select> <?php echo _('with')?>
<label><select name="template_selector" id="template_selector"><option></option></select></label>
(<label><input type="checkbox" name="reboot"><font size='-1'>Reboot Phones</font></label>)
</form></p>

<p><form action='' name='globalmanaged4' id='globalmanaged4' method='POST'>
<button style="width: 100px" type="submit" name="button_rebuild_reboot" onclick="submit_global3('mrebuild_reboot');"><i class="icon-random blue"></i> <?php echo _('Configure')?></button>
<?php echo _('Reconfigure all')?> (models) <select name="model_select" id="model_select">{loop name="model_list"}<option value="{$value.value}">{$value.text}</option>{/loop}</select> <?php echo _('with')?>
<label><select name="model_template_selector" id="model_template_selector"><option></option></select></label>
(<label><input type="checkbox" name="reboot"><font size='-1'>Reboot Phones</font></label>)
</form></p>

</form>

{if condition="!isset($disable_help)"}
<script>
    $("#demo img[title]").tooltip();
</script>
{/if}
<script>
    $("#collapser").hide();
</script>







<!--<hr>
{include="global_footer"}
<h6 align='center'>The Endpoint Configuration Manager is currently maintained by <a target="_blank" href=http://www.andrewsnagy.com/>Andrew Nagy</a>
<br/><?php echo _("The Endpoint Configuration Manager was originally written by")?> <a target="_blank" href=http://www.mymcs.us>Ed Macri</a>, <a target="_blank" href=http://www.cohutta.com> John Mullinix.</a> and <a target="_blank" href=http://www.colsolgrp.com>Tony Shiffer</a>
<br/>Endpoint Configuration Manager uses code from the MPL licensed project Provisioner.net at <a href="http://provisioner.net" target="_blank">http://www.provisioner.net</a> co-written by Darren Schreiber &amp Andrew Nagy
<br> <?php echo _("The project is maintained at")?> 
<a target="_blank" href="http://projects.colsolgrp.net/projects/show/endpointman"> CSG Software Projects.</a>
-->











<?PHP






return;

?>
