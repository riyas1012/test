@extends('layout')

@section('content')

<div class="row third-portion">
    <div class="tab-content">
        <div class="tab-pane fade" id="option3">
            <div class="row tab-content-caption">
                <div class="container">
                    <div class="col-md-10 big-text" style="padding-bottom:20px;">
                        <p><?= $title ?></p>
                        <a href="<?php echo web_url(); ?>/admin/provider/add"><button class="btn btn-green dropdown-toggle" type="button">Add Provider</button></a>
                        <div style="float:right">
                               <?php if (Session::get('che')) {?>
                        <a href="<?php echo web_url(); ?>/admin/providers"><button class="btn btn-green dropdown-toggle" type="button">Provider List</button></a>
                        <?php }
                        else 
                            {?><a href="<?php echo web_url(); ?>/admin/provider/current"><button class="btn btn-green dropdown-toggle" type="button">Currently Providing</button></a>
                        <?php }?>
                    </div>


                    </div>
                    
                </div>
            </div>
            

            <div class="row editable-content-div col-md-12">
            <div class="col-md-12">
              <div class="col-sm-4">
                        <form method="get" action="<?php echo web_url(); ?>/admin/sortpv">
                            <div align="right"><strong>Sort by: </strong>
                                <select id="searchdrop" name="type">
                                    <option value="provid" <?php if(isset($_GET['type']) && $_GET['type'] =='provid') {echo 'selected="selected"';}?> id="provid">Provider ID</option>
                                    <option value="pvname" <?php if(isset($_GET['type']) && $_GET['type'] =='pvname') {echo 'selected="selected"';}?> id="pvname">Provider Name</option>
                                    <option value="pvemail" <?php if(isset($_GET['type']) && $_GET['type']=='pvemail') {echo 'selected="selected"';}?> id="pvemail">Provider Email</option>
                                    <option value="pvaddress" <?php if(isset($_GET['type']) && $_GET['type']=='pvaddress') {echo 'selected="selected"';}?>  id="pvaddress"> ProviderAddress</option>
                                </select>
                                <select id="searchdroporder" name="valu">
                                    <option value="asc" <?php if(isset($_GET['valu']) && $_GET['valu']=='asc') {echo 'selected="selected"';}?> selected id="asc">Ascending</option>
                                    <option value="desc" <?php if(isset($_GET['valu']) && $_GET['valu']=='desc') {echo 'selected="selected"';}?> id="desc">Descending</option>
                                </select>
                                <input type="submit" id="btnsort" value=" Sort " />
                            </div>
                        </form>
                    </div>
                    
                    <div class="col-sm-5">
               
                <form method="get" action="<?php echo web_url(); ?>/admin/searchpv">
                    <div align="right"><strong>by: </strong>
                        <select id="searchdrop" name="type">
                            <option value="provid" id="provid">Provider ID</option>
                            <option value="pvname" id="pvname">Provider Name</option>
                            <option value="pvemail" id="pvemail">Provider Email</option>
                            <option value="bio" id="bio">Provider Bio</option>
                        </select>
                        <input type="text" name="valu" id="insearch" placeholder="keyword"/>
                        <input type="submit" id="btnsearch" value="Search" />
                    </div>
                </form>
                </div>
                </div>
                <table class="table table-stripped col-md-12">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Photo</th>
                            <th>Bio</th>
                            <th>Total Requests</th>
                            <th>Acceptance Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php foreach ($walkers as $walker) { ?>
                        <tr>
                            <td><?= $walker->id ?></td>
                            <td><?php echo $walker->first_name." ".$walker->last_name; ?> </td>
                            <td><?= $walker->email ?></td>
                            <td><?= $walker->phone ?></td>
                            <td><a href="<?php echo $walker->picture; ?> target="_blank" onclick="window.open('<?php echo $walker->picture; ?>', 'popup', 'height=500px, width=400px'); return false;"">View Photo</a></td>
                            <td><?= $walker->bio ?></td>
                            <td><?= $walker->total_requests ?></td>
                            <td><?php
                            if($walker->total_requests != 0)
                            {
                             echo round(($walker->accepted_requests/$walker->total_requests)*100,2);
                            }
                            else{
                                echo 0;
                            } 
                             ?> %</td>
                            <td><?php if($walker->is_approved == 1){
                                    echo "<span style='color:green'>Approved</span>";
                                }
                                else{
                                    echo "<span style='color:red'>Pending</span>";
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
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/provider/edit/<?= $walker->id; ?>">Edit Details</a></li>
                                    <?php if($walker->merchant_id == NULL){ ?>
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/provider/banking/<?= $walker->id; ?>">Add Banking Details</a></li>
                                    <?php } ?>
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/provider/history/<?= $walker->id; ?>">View History</a></li>
                                    
                                    <?php if($walker->is_approved == 0){ ?>
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/provider/approve/<?= $walker->id; ?>">Approve</a></li>
                                    <?php }else{ ?>
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/provider/decline/<?= $walker->id; ?>">Decline</a></li>
                                    
                                    <?php } ?>
                                    <!--
                                    <li role="presentation" class="divider"></li>
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/walker/delete/<?= $walker->id; ?>">Delete Walker</a></li>
                                    -->
                                  </ul>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div align="right" id="paglink"><?php echo $walkers->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>
        </div>
    </div>
</div>




@stop