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
            <input type="hidden" name="id" value="<?= $walker->id ?>">
            <table class="display" cellspacing="0" width="100%" style="position:relative;left:20px;">
                <tbody>
                    
                        <tr>
                          <td id="col1">First Name</td>
                          <td id="col2"><input type="text" name="first_name" value="<?= $walker->first_name ?>"></td>
                        </tr>
                        <tr>
                          <td id="col1">Last Name</td>
                          <td id="col2"><input type="text" name="last_name" value="<?= $walker->last_name ?>"></td>
                        </tr>
                        <tr>
                          <td id="col1">Email</td>
                          <td id="col2"><input type="text" name="email" value="<?= $walker->email ?>"></td>
                        </tr>
                        <tr>
                          <td id="col1">Phone</td>
                          <td id="col2"><input type="text" name="phone" value="<?= $walker->phone ?>"></td>
                        </tr>
                        <tr>
                          <td id="col1">Bio</td>
                          <td id="col2"><input type="text" name="bio" value="<?= $walker->bio ?>"></td>
                        </tr>
                        <tr>
                          <td id="col1">Address</td>
                          <td id="col2"><input type="text" name="address" value="<?= $walker->address ?>"></td>
                        </tr>
                       
                        <tr>
                          <td id="col1">State</td>
                          <td id="col2"><input type="text" name="state" value="<?= $walker->state ?>"></td>
                        </tr>
                        <tr>
                          <td id="col1">Country</td>
                          <td id="col2"><input type="text" name="country" value="<?= $walker->country ?>"></td>
                        </tr>
                        <tr>
                          <td id="col1">Zipcode</td>
                          <td id="col2"><input type="text" name="zipcode" value="<?= $walker->zipcode ?>"></td>
                        </tr>
                        <tr>
                          <td id="col1">Picture</td>
                          <td id="col2"><img src="<?= $walker->picture; ?>" height="50" width="50" style="position:relative;float:left;padding-right:15px;"><input type="file" name="pic" ></td>
                        </tr>
                          @foreach($type as $types)

                        <tr>
                          <td id="col1">Service Type</td>
                          <td id="col2">
                            <?php
                              foreach ($ps as $pss) {
                                  $ser = ProviderType::where('id',$pss->type)->first();
                                  $ar[] = $ser->name;
                               }
                               $servname = $types->name;
                            ?>
                              <input name="service[]" type="checkbox" value="{{$types->id}}" <?php if(!empty($ar)){if (in_array($servname, $ar)) echo "checked='checked'";}  ?>>{{$types->name}}<br>
                          </td>
                          <td>
                              <input name="service_base_price[]" type="text" value="<?php $proviserv = ProviderServices::where('provider_id',$walker->id)->where('type',$types->id)->first(); if(empty($proviserv)){echo "";}else{echo $proviserv->base_price;} ?>" placeholder="Base Price" ><br>
                          </td>
                          <td>
                              <input name="service_price_distance[]" type="text" value="<?php $proviserv = ProviderServices::where('provider_id',$walker->id)->where('type',$types->id)->first(); if(empty($proviserv)){echo "";}else{echo $proviserv->price_per_unit_distance;} ?>" placeholder="Price per unit distance" ><br>
                          </td>
                          <td>
                              <input name="service_price_time[]" type="text" value="<?php $proviserv = ProviderServices::where('provider_id',$walker->id)->where('type',$types->id)->first(); if(empty($proviserv)){echo "";}else{echo $proviserv->price_per_unit_time;} ?>" placeholder="Price per unit time" ><br>
                          </td>

                        </tr>
                          @endforeach
                        <tr>
                          <td id="col1">Is Currently Providing</td>
                          <td id="col2"><?php $walk = DB::table('walk')
                                                  -> select('id')
                                                  ->where('walk.is_started', 1)
                                                  ->where('walk.is_completed', 0)
                                                  ->where('walker_id', $walker->id);
                                                  $count=$walk->count();
                                                  if($count>0)
                                                  {
                                                    echo "Yes";
                                                  }
                                                  else
                                                  {
                                                    echo "No";
                                                  }
              ?></td></tr><tr>
              <td id="col1">Is Provider Available </td>
                          <td id="col2"><?php $walk = DB::table('walker')
                                                  -> select('id')
                                                  ->where('walker.is_available', 1)
                                                  ->where('walker.id', $walker->id);
                                                  $count=$walk->count();
                                                  if($count>0)
                                                  {
                                                    echo "Yes";
                                                  }
                                                  else
                                                  {
                                                    echo "No";
                                                  }
              ?></td>
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
    alert('Walker Profile Updated Successfully');
</script>
<?php } ?>
<?php
if($success == 2) { ?>
<script type="text/javascript">
    alert('Sorry Something went Wrong');
</script>
<?php } ?>


@stop