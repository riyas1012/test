
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
            
            <div class="row editable-content-div">
             <div class="container">
            <form method="post" action="<?php echo web_url(); ?>/admin/provider-type/update"  enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id ?>">
            <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">
                <tbody>
                    
                        <tr>
                          <td id="col1">Type Name</td>
                          <td id="col2"><input type="text" name="name" value="<?= $name ?>"></td>
                        </tr>

                       <tr >
                      <td id="col1">Icon File </td><td id="col2">
                      <?php if($icon != "") {?>
                      <img src="<?= $icon; ?>" height="50" width="50" style="position:relative;float:left;padding-right:15px;">
                      <?php } ?>
                      <input type="file" name="icon" ></td>
                      </tr>
                        <?php if(!$is_default == 1) { ?>
                        <tr>
                          <td id="col1">Set as Default</td>
                          <td id="col2"><input type="checkbox" name="is_default" value="1"></td>
                        </tr>
                        <?php }else{ ?>
                          <input type="hidden" name="is_default" value="1">
                        <?php } ?>
                        <tr>
                            <td></td>
                            <td><br><input type="submit" value="Save" class="btn btn-green"></td>
                        </tr>
                    
                </tbody>
            </table>
            
            </form>

            </div>       
               
            </div>
            <!--</form>-->
        </div>
    </div>
</div>

<?php
if($success == 1) { ?>
<script type="text/javascript">
    alert('Provider Type Updated Successfully');
    document.location.href="<?php echo web_url(); ?>/admin/provider-types";
</script>
<?php } ?>
<?php
if($success == 2) { ?>
<script type="text/javascript">
    alert('Sorry Something went Wrong');
</script>
<?php } ?>



@stop