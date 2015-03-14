
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
            <form method="post" action="<?php echo web_url(); ?>/admin/information/update"  enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id ?>">
              <div class="input-group input-group-lg" style="width:400px;">
              <label><br>Title </label><input type="text" name="title" class="form-control" placeholder="Title" value="<?= $info_title ?>">
              </div>
              <div class="input-group input-group-lg" style="width:400px;">
              <label><br>Icon File </label>
              <?php if($icon != "") {?>
              <img src="<?= $icon; ?>" height="50" width="50" style="position:relative;float:left;padding-right:15px;">
              <?php } ?>
              <input type="file" name="icon" class="form-control" >
              </div>
              <div class="input-group input-group-lg" style="width:400px;">
              <br>
              <label>Description </label><br>
              
              </div>
              <textarea name="description" class="ckeditor" style="height: 200px">
                <?= $description ?>  
              </textarea>
              <br>
              <input type="submit" value="Save Page" class="btn btn-green btn-large"></td>
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
    alert('Page Updated Successfully');
</script>
<?php } ?>
<?php
if($success == 2) { ?>
<script type="text/javascript">
    alert('Sorry Something went Wrong');
</script>
<?php } ?>



@stop