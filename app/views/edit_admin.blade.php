
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
            <form method="post" action="<?php echo web_url(); ?>/admin/admins/update"  enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $admin->id ?>">
            <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">
                <tbody>
                        <tr>
                          <td id="col1">User Name</td>
                          <td id="col2"><input type="text" name="username" value="{{$admin->username}}"></td>
                        </tr>
                        <tr>
                          <td id="col1">Password</td>
                          <td id="col2"><input type="password" name="password" placeholder="New Password"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><br><input type="submit" value="Update Changes" class="btn btn-green"></td>
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
    alert('Service Updated Successfully');
</script>
<?php } ?>
<?php
if($success == 2) { ?>
<script type="text/javascript">
    alert('Sorry Something went Wrong');
</script>
<?php } ?>
@stop