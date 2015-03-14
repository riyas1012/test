@extends('layout')

@section('content')

<div class="row third-portion">
    <div class="tab-content">
        <div class="tab-pane fade" id="option3">
            <div class="row tab-content-caption">
                <div class="container">
                    <div class="col-md-10 big-text">
                        <p><?= $title ?></p>
                        <!--
                        <form class="form-inline" role="form" method="get" action="<?php echo web_url(); ?>/provider/trips">
                        <label> Start Date</label>
                        <input type="text" class="form-control" id="start-date" name="start_date" value="{{ Input::get('start_date') }}" placeholder="Start Date">
                        <label> End Date</label>
                        <input type="text" class="form-control" id="end-date" name="end_date" placeholder="End Date"  value="{{ Input::get('end_date') }}">
                        <input type="submit" value="Download Report" class="btn btn-primary">
                        </form>
                        -->
                    </div>
                    
                </div>
            </div>
            
            <div class="row editable-content-div col-md-12">
                <div class="col-md-12">
                    <div class="col-sm-4">
                        <form method="get" action="<?php echo web_url(); ?>/admin/sortreq">
                            <div align="right"><strong>Sort by: </strong>
                                <select id="searchdrop" name="type">
                                    <option value="reqid" <?php if(isset($_GET['type']) && $_GET['type']=='reqid') {echo 'selected="selected"';}?>  id="reqid">Request ID</option>
                                    <option value="owner" <?php if(isset($_GET['type']) && $_GET['type']=='owner') {echo 'selected="selected"';}?>  id="owner">Owner Name</option>
                                    <option value="walker" <?php if(isset($_GET['type']) && $_GET['type']=='walker') {echo 'selected="selected"';}?>  id="walker">Provider</option>
                                </select>
                                <select id="searchdroporder" name="valu">
                                    <option value="asc" <?php if(isset($_GET['type']) && $_GET['valu']=='asc') {echo 'selected="selected"';}?>  id="asc">Ascending</option>
                                    <option value="desc" <?php if(isset($_GET['type']) && $_GET['valu']=='desc') {echo 'selected="selected"';}?>  id="desc">Descending</option>
                                </select>
                                <input type="submit" id="btnsort" value="Sort" />
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-5">           
                <form method="get" action="<?php echo web_url(); ?>/admin/searchreq">
                    <div align="right"><strong> by: </strong>
                        <select id="searchdrop" name="type">
                            <option value="reqid" id="reqid">Request ID</option>
                            <option value="owner" id="owner">Owner Name</option>
                            <option value="walker" id="walker">Provider</option>
                        </select>
                        <input type="text" name="valu" id="insearch" placeholder="keyword"/>
                        <input type="submit" id="btnsearch" value="Search" />
                    </div>
                </form>
                </div>
                </div>
       
                <table class="table table-stripped">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Owner Name</th>
                            <th>Provider</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php foreach ($walks as $walk) { ?>
                        <tr>
                            <td><?= $walk->id ?></td>
                            <td><?php echo $walk->owner_first_name." ".$walk->owner_last_name; ?> </td>
                            <td>
                            <?php 
                            if($walk->confirmed_walker)
                            {
                             echo $walk->walker_first_name." ".$walk->walker_last_name; 
                            }
                            else{
                            echo "Un Assigned";
                            }
                            ?>
                            </td>
                            <td><?php echo date("d M Y",strtotime($walk->date)); ?></td>
                            <td><?php echo date("g:iA",strtotime($walk->date)); ?></td>

                            <td>
                                <?php 
                                if($walk->is_cancelled == 1) {
                                    echo "<span style='color:red'>Cancelled</span>";
                                }
                                elseif ($walk->is_completed == 1) {
                                    echo "<span style='color:green'>Completed</span>";
                                }
                                elseif ($walk->is_started == 1) {
                                    echo "<span style='color:orange'>Started</span>";
                                }
                                elseif ($walk->is_walker_arrived == 1) {
                                    echo "<span style='color:orange'>Walker Arrived</span>";
                                }
                                elseif ($walk->is_walker_started == 1) {
                                    echo "<span style='color:orange'>Walker Started</span>";
                                }

                                else{
                                    echo "<span style='color:blue'>Yet To Start</span>";
                                }
                                ?>
                            </td>
                            <td>
                            <?php echo $walk->total; ?>
                            </td>
                            <td>
                                <?php 
                                if ($walk->is_paid == 1) {
                                    echo "<span style='color:green'>Completed</span>";
                                }
                                elseif ($walk->is_paid == 0 && $walk->is_completed == 1) {
                                    echo "<span style='color:red'>Pending</span>";
                                }
                                else {
                                    echo "<span style='color:orange'>Request Not Completed</span>";
                                }
                                
                                ?>
                            </td>
                            <td>
                              <div class="dropdown">
                                  <button class="btn btn-green dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                                    Actions
                                    <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                    

                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/request/map/<?= $walk->id; ?>">View Map</a></li>
                                   

                                    
                                    <!--
                                    <li role="presentation" class="divider"></li>
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/walk/delete/<?= $walk->id; ?>">Delete Walk</a></li>
                                    -->
                                  </ul>
                                </div>  

                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div align="right" id="paglink"><?php echo $walks->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>

            
        </div>
    </div>
</div>



<!--
  <script>
  $(function() {
    $( "#start-date" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#end-date" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#end-date" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#start-date" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
  });
  </script>
-->
@stop