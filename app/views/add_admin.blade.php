
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
            <form method="post" action="<?php echo web_url(); ?>/admin/admins/add">
            <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">
                <tbody>
                        <tr>
                          <td id="col1">Email</td>
                          <td id="col2"><input type="text" name="username" placeholder="Add admin email"></td>
                        </tr>
                        <tr>
                          <td id="col1">Password</td>
                          <td id="col2"><input type="password" name="password" placeholder="Add admin password"></td>
                        </tr>
                            <td></td>
                            <td><br><input type="submit" value="Add Admin" class="btn btn-green"></td>
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

@stop