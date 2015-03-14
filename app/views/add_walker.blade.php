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
            <form method="post" action="<?php echo web_url(); ?>/admin/provider/update"  enctype="multipart/form-data">
            <input type="hidden" name="id" value="0>">
            <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">
                <tbody>
                    
                        <tr>
                          <td id="col1">First Name</td>
                          <td id="col2"><input type="text" name="first_name" value=""></td>
                        </tr>
                        <tr>
                          <td id="col1">Last Name</td>
                          <td id="col2"><input type="text" name="last_name" value=""></td>
                        </tr>
                        <tr>
                          <td id="col1">Email</td>
                          <td id="col2"><input type="text" name="email" value=""></td>
                        </tr>
                        <tr>
                          <td id="col1">Phone</td>
                          <td id="col2"><input type="text" name="phone" value=""></td>
                        </tr>
                        <tr>
                          <td id="col1">Bio</td>
                          <td id="col2"><input type="text" name="bio" value=""></td>
                        </tr>
                        <tr>
                          <td id="col1">Address</td>
                          <td id="col2"><input type="text" name="address" value=""></td>
                        </tr>
                       
                        <tr>
                          <td id="col1">State</td>
                          <td id="col2"><input type="text" name="state" value=""></td>
                        </tr>
                        <tr>
                          <td id="col1">Country</td>
                          <td id="col2"><input type="text" name="country" value=""></td>
                        </tr>
                        <tr>
                          <td id="col1">Zipcode</td>
                          <td id="col2"><input type="text" name="zipcode" value=""></td>
                        </tr>
                        <tr>
                          <td id="col1">Picture</td>
                          <td id="col2"><input type="file" name="pic" ></td>
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


@stop