@extends('layout')

@section('content')

<div class="row third-portion">
    <div class="tab-content">
        <div class="tab-pane fade" id="option3">
            <div class="row tab-content-caption">
                <div class="container">
                    <div class="col-md-12 big-text">
                        <p><?= $title ?></p>
                        <form class="form-inline" role="form" method="get" action="<?php echo web_url(); ?>/admin/report" >
                        <label> Start Date</label>
                        <input type="text" class="form-control" style="width:10%; overflow:hidden;" id="start-date" name="start_date" value="{{ Input::get('start_date') }}" placeholder="Start Date">
                        <label> End Date</label>
                        <input type="text" class="form-control" style="width:10%; overflow:hidden;" id="end-date" name="end_date" placeholder="End Date"  value="{{ Input::get('end_date') }}">
                        <label> Provider</label>
                        <select name="walker_id" style="width:20%; overflow:hidden;" class="form-control">
                            <option value="0">All</option>
                            <?php foreach ($walkers as $walker) { ?>
                            <option value="<?= $walker->id ?>" <?php echo Input::get('walker_id') == $walker->id?"selected":"" ?>><?= $walker->first_name; ?> <?= $walker->last_name ?></option>
                            <?php } ?>
                        </select>
                        <label> User</label>
                        <select name="owner_id" style="width:20%; overflow:hidden;" class="form-control">
                            <option value="0">All</option>
                            <?php foreach ($owners as $owner) { ?>
                            <option value="<?= $owner->id ?>" <?php echo Input::get('owner_id') == $owner->id?"selected":"" ?>><?= $owner->first_name; ?> <?= $owner->last_name ?></option>
                            <?php } ?>
                        </select>
                        <label>Status</label>
                        <select name="status"  class="form-control">
                            <option value="0">All</option>
                            <option value="1" <?php echo Input::get('status') == 1?"selected":"" ?> >Completed</option>
                            <option value="2" <?php echo Input::get('status') == 2?"selected":"" ?>>Cancelled</option>
                        </select>
                        <br><br>    
                        <input type="submit" value="Filter Data" name="submit" class="btn btn-green">
                        <input type="submit" value="Download Report" name="submit" class="btn btn-green">
                        </form>
                        <br>
                        <h4>Summary</h4>
                        <label>Total Trips</label>&nbsp;&nbsp;-&nbsp;&nbsp;<?= $completed_rides + $cancelled_rides ?><br>
                        <label>Completed Trips</label>&nbsp;&nbsp;-&nbsp;&nbsp;<?= $completed_rides  ?><br>
                        <label>Cancelled Trips</label>&nbsp;&nbsp;-&nbsp;&nbsp;<?= $cancelled_rides ?><br>
                        <label>Total Payment</label>&nbsp;&nbsp;-&nbsp;&nbsp;<?= $credit_payment + $card_payment ?><br>
                        <label>Card Payment</label>&nbsp;&nbsp;-&nbsp;&nbsp;<?= $card_payment ?><br>
                        <label>Credit Payment</label>&nbsp;&nbsp;-&nbsp;&nbsp;<?= $credit_payment ?><br><br>

                    </div>
                    
                </div>
            </div>
            
            <div class="row editable-content-div col-md-12">
             

                        <table id="example" class="table table-striped" cellspacing="0" width="100%">
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
                                    <th>Ledger Payment</th>
                                    <th>Card Payment</th>
                                    
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
                                          <?= $walk->ledger_payment; ?>
                                        </td>
                                        <td>
                                            <?= $walk->card_payment; ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                
                            </tbody>
                        </table>
                <div align="right" id="paglink"><?php echo $walks->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>
            </div>
            <!--</form>-->
        </div>
    </div>
</div>

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

@stop