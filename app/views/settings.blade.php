@extends('layout')

@section('content')
<div class="row third-portion">
    <div class="tab-content">
        <div class="tab-pane fade" id="option3">
            <div class="row tab-content-caption">
                <div class="container">
                    <div class="col-md-10 big-text">
                        <p><?= $title ?></p>
                    </div>
                    
                </div>
            </div>
            
            <div class="row editable-content-div col-md-12">
             <div class="container">
             <h2>Theme Settings</h2> 
            <hr>
              <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">
                <?php 
                 $theme_color = '0000CC';
                 $primary_color = '0000FF';
                 $secondary_color = '3366FF';
                 $hover_color = '8AB800';
                 $active_color = '000066'; 
                foreach($theme as $themes) {
                 $theme_color = str_replace("#", "",$themes->theme_color);
                 $primary_color = str_replace("#", "",$themes->primary_color);
                 $secondary_color = str_replace("#", "",$themes->secondary_color);
                 $hover_color = str_replace("#", "",$themes->hover_color);
                 $active_color = str_replace("#", "",$themes->active_color);
                  }?>
                    <form enctype="multipart/form-data" onsubmit="Checkfiles(this)" method="post" action="<?php echo web_url(); ?>/admin/theme">
                      <tr>
                          <td id="col1">Theme color</td>
                          <td id="col2"><input name="color1" value="<?php echo $theme_color;?>" type="text" class="picker" id="picker"></input></td>
                      </tr>
                      <tr>
                          <td id="col1">Menu Tab Primary Color</td>
                          <td id="col2"><input name="color2" type="text" value="<?php echo $primary_color;?>" class="picker" id="picker1"></input></td>
                      </tr>
                      <tr>
                          <td id="col1">Menu Tab Secondary Color</td>
                          <td id="col2"><input name="color3" type="text" value="<?php echo $secondary_color;?>" class="picker" id="picker2"></input></td>
                      </tr>
                      <tr>
                          <td id="col1">Menu Tab Hover Color</td>
                          <td id="col2"><input name="color4" type="text" class="picker" value="<?php echo $hover_color;?>" id="picker3"></input></td>
                      </tr>
                      <tr>
                          <td id="col1">Menu Tab Active Color</td>
                          <td id="col2"><input name="color5" type="text" class="picker" value="<?php echo $active_color;?>" id="picker4"></input></td>
                      </tr>
                      <tr>
                          <td id="col1">Logo</td>
                          <td id="col2"><input type="file" name="logo" id="logo"></input></td>
                      </tr>
                      <tr>
                          <td id="col1">Title Icon</td>
                          <td id="col2"><input type="file" name="icon" id="icon"></input></td>
                      </tr>
                      <tr>
                          <td></td>
                          <td><input type="submit" value="Update Changes" class="btn btn-green"></td>
                      </tr>
                    </form>
                </table>
                        <form method="post" action="<?php echo web_url(); ?>/admin/settings"  enctype="multipart/form-data">
            <h2>Basic App Settings</h2> 
            <hr>
            <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">
                <tbody>
                    <?php foreach ($settings as $setting) { 
                       if($setting->page == 1){ 
                        if( $setting->key != 'default_distance_unit' && $setting->key != 'sms_notification' && $setting->key != 'email_notification' && $setting->key != 'push_notification' && $setting->key != 'default_charging_method_for_users') { ?>
                    <tr>
                      <td id="col1"><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo web_url(); ?>/image/icon-tooltip.jpg"></a></td>
                      <td id="col2">
                      <?php if((strstr($setting->key,'email_') || strstr($setting->key,'sms_'))& $setting->key != 'admin_email_address' ) {?>
                      <textarea rows="5" cols="50" name="<?php echo $setting->id; ?>" ><?php echo $setting->value; ?></textarea>
                      <?php } else{?>
                      <input type="text" name="<?php echo $setting->id; ?>" value="<?php echo $setting->value; ?>">
                      <?php } ?>
                      </td>
                    </tr>
                    <?php } else{ ?>
                        <tr>
                      <td id="col1"><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo web_url(); ?>/image/icon-tooltip.jpg"></a></td>
                      <td id="col2">
                      <select name="<?php echo $setting->id; ?>">
                          <option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
                          <?php if($setting->key == 'default_charging_method_for_users') {?>
                          Time and Distance Based
                          <?php } elseif($setting->key == 'default_distance_unit') { ?>
                          Miles
                          <?php }else{ ?>
                          Yes
                          <?php } ?>
                          </option>
                          <option value="0" <?php if($setting->value == 0) { echo "selected"; }?> >
                          <?php if($setting->key == 'default_charging_method_for_users') {?>
                          Fixed Price
                          <?php } elseif($setting->key == 'default_distance_unit') { ?>
                          KM
                          <?php }else{ ?>
                          No
                          <?php } ?>
                          </option>
                      </select>
                      
                      </td>
                    </tr>
                    <?php } ?>
                    <?php } ?>


                    <?php } ?>
                    
                    <tr>
                        <td></td>
                        <td><br><input type="submit" value="Update Changes" class="btn btn-green"></td>
                    </tr>
                    
                </tbody>
            </table>
            
            <h2>SMS Templates</h2>
            <hr>
                       <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">
                <tbody>
                    <?php foreach ($settings as $setting) { 
                       if($setting->page == 2){ 
                        if( $setting->key != 'sms_notification' && $setting->key != 'email_notification' && $setting->key != 'push_notification' && $setting->key != 'default_charging_method_for_users') { ?>
                    <tr>
                      <td id="col1"><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo web_url(); ?>/image/icon-tooltip.jpg"></a></td>
                      <td id="col2">
                      <?php if((strstr($setting->key,'email_') || strstr($setting->key,'sms_'))& $setting->key != 'admin_email_address' ) {?>
                      <textarea rows="5" cols="50" name="<?php echo $setting->id; ?>" ><?php echo $setting->value; ?></textarea>
                      <?php } else{?>
                      <input type="text" name="<?php echo $setting->id; ?>" value="<?php echo $setting->value; ?>">
                      <?php } ?>
                      </td>
                    </tr>
                    <?php } else{ ?>
                        <tr>
                      <td id="col1"><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo web_url(); ?>/image/icon-tooltip.jpg"></a></td>
                      <td id="col2">
                      <select name="<?php echo $setting->id; ?>">
                          <option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
                          <?php if($setting->key == 'default_charging_method_for_users') {?>
                          Time and Distance Based
                          <?php } else { ?>
                          Yes
                          <?php } ?>
                          </option>
                          <option value="0" <?php if($setting->value == 0) { echo "selected"; }?> >
                          <?php if($setting->key == 'default_charging_method_for_users') {?>
                          Fixed Price
                          <?php } else { ?>
                          No
                          <?php } ?>
                          </option>
                      </select>
                      
                      </td>
                    </tr>
                    <?php } ?>
                    <?php } ?>


                    <?php } ?>
                    
                    <tr>
                        <td></td>
                        <td><br><input type="submit" value="Update Changes" class="btn btn-green"></td>
                    </tr>
                    
                </tbody>
            </table> 
          
            <h2>Email Templates</h2> 
            <hr>
                      <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">

                <tbody>
                    <?php foreach ($settings as $setting) { 
                       if($setting->page == 3){ 
                        if( $setting->key != 'sms_notification' && $setting->key != 'email_notification' && $setting->key != 'push_notification' && $setting->key != 'default_charging_method_for_users') { ?>
                    <tr>
                      <td id="col1"><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo web_url(); ?>/image/icon-tooltip.jpg"></a></td>
                      <td id="col2">
                      <?php if((strstr($setting->key,'email_') || strstr($setting->key,'sms_'))& $setting->key != 'admin_email_address' ) {?>
                      <textarea rows="5" cols="50" name="<?php echo $setting->id; ?>" ><?php echo $setting->value; ?></textarea>
                      <?php } else{?>
                      <input type="text" name="<?php echo $setting->id; ?>" value="<?php echo $setting->value; ?>">
                      <?php } ?>
                      </td>
                    </tr>
                    <?php } else{ ?>
                        <tr>
                      <td id="col1"><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo web_url(); ?>/image/icon-tooltip.jpg"></a></td>
                      <td id="col2">
                      <select name="<?php echo $setting->id; ?>">
                          <option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
                          <?php if($setting->key == 'default_charging_method_for_users') {?>
                          Time and Distance Based
                          <?php } else { ?>
                          Yes
                          <?php } ?>
                          </option>
                          <option value="0" <?php if($setting->value == 0) { echo "selected"; }?> >
                          <?php if($setting->key == 'default_charging_method_for_users') {?>
                          Fixed Price
                          <?php } else { ?>
                          No
                          <?php } ?>
                          </option>
                      </select>
                      
                      </td>
                    </tr>
                    <?php }}} ?>
                    
                    <tr>
                        <td></td>
                        <td><br><input type="submit" value="Update Changes" class="btn btn-green"></td>
                    </tr>
                    
                </tbody>
            </table>

            <h2>Advanced Settings</h2> 
            <hr>
            <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">
                <tbody>
                  <?php foreach ($settings as $setting) {
                    if($setting->page == 4){
                      if($setting->key == 'provider_selection'){ ?>
                    <tr>
                      <td id="col1"><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo web_url(); ?>/image/icon-tooltip.jpg"></a></td>
                      <td id="col2">
                      <select name="<?php echo $setting->id; ?>">
                          <option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
                            Automatic
                          </option>
                          <option value="2" <?php if($setting->value == 2) { echo "selected"; }?> >
                            Manually
                          </option>
                      </select>
                      </td>
                    </tr>
                    <?php }else{ ?>
                        <tr>
                          <td id="col1"><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo web_url(); ?>/image/icon-tooltip.jpg"></a></td>
                          <td id="col2">
                          <textarea rows="5" cols="50" name="<?php echo $setting->id; ?>" ><?php echo $setting->value; ?></textarea>
                          </td>
                        </tr>
                  <?php }}} ?>
                    <tr>
                        <td></td>
                        <td><br><input type="submit" value="Update Changes" class="btn btn-green"></td>
                    </tr>
                </tbody>
            </table>
            </form>
            </div>
          </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $('#picker').colpick({
  layout:'hex',
  submit:0,
  colorScheme:'dark',
  onChange:function(hsb,hex,rgb,el,bySetColor) {
    $(el).css('border-color','#'+hex);
    // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
    if(!bySetColor) $(el).val(hex);
  }
}).keyup(function(){
  $(this).colpickSetColor(this.value);
});

$('#picker1').colpick({
  layout:'hex',
  submit:0,
  colorScheme:'dark',
  onChange:function(hsb,hex,rgb,el,bySetColor) {
    $(el).css('border-color','#'+hex);
    // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
    if(!bySetColor) $(el).val(hex);
  }
}).keyup(function(){
  $(this).colpickSetColor(this.value);
});


$('#picker2').colpick({
  layout:'hex',
  submit:0,
  colorScheme:'light',
  onChange:function(hsb,hex,rgb,el,bySetColor) {
    $(el).css('border-color','#'+hex);
    // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
    if(!bySetColor) $(el).val(hex);
  }
}).keyup(function(){
  $(this).colpickSetColor(this.value);
});

$('#picker3').colpick({
  layout:'hex',
  submit:0,
  colorScheme:'dark',
  onChange:function(hsb,hex,rgb,el,bySetColor) {
    $(el).css('border-color','#'+hex);
    // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
    if(!bySetColor) $(el).val(hex);
  }
}).keyup(function(){
  $(this).colpickSetColor(this.value);
});
$('#picker4').colpick({
  layout:'hex',
  submit:0,
  colorScheme:'dark',
  onChange:function(hsb,hex,rgb,el,bySetColor) {
    $(el).css('border-color','#'+hex);
    // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
    if(!bySetColor) $(el).val(hex);
  }
}).keyup(function(){
  $(this).colpickSetColor(this.value);
});

</script>
<script type="text/javascript">
    function Checkfiles()
    {
        var fup = document.getElementById('logo');
        var fileName = fup.value;
        if(fileName !='')
  {
        var ext = fileName.substring(fileName.lastIndexOf('.') + 1);

    if(ext =="PNG" || ext=="png")
    {
        return true;
    }
    else
    {
        alert("Upload PNG Images only for Logo");
        return false;
    }
  }
    var fup = document.getElementById('icon');
        var fileName1 = fup.value;
        if(fileName1 !='')
  {
        var ext = fileName1.substring(fileName1.lastIndexOf('.') + 1);

    if(ext =="ICO" || ext=="ico")
    {
        return true;
    }
    else
    {
        alert("Upload Icon Images only for Favicon");
        return false;
    }
  }
    }
</script>
<?php
if($success == 1) { ?>
<script type="text/javascript">
    alert('Settings Updated Successfully');
</script>
<?php } ?>
<?php
if($success == 2) { ?>
<script type="text/javascript">
    alert('Sorry Something went Wrong');
</script>
<?php } ?>

<script>
   $(function () { $("[data-toggle='tooltip']").tooltip(); });
</script>


@stop