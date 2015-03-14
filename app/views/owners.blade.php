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
             <div class="col-md-12">
              <div class="col-sm-4">
                        <form method="get" action="<?php echo web_url(); ?>/admin/sortur">
                            <div align="right"><strong>Sort by: </strong>
                                <select id="searchdrop" name="type">
                                    <option value="userid" <?php if(isset($_GET['type']) && $_GET['type']=='userid') {echo 'selected="selected"';}?> id="provid">User ID</option>
                                    <option value="username" <?php if(isset($_GET['type']) && $_GET['type']=='username') {echo 'selected="selected"';}?> id="pvname">User Name</option>
                                    <option value="useremail" <?php if(isset($_GET['type']) && $_GET['type']=='useremail') {echo 'selected="selected"';}?> id="pvemail">User Email</option>
                                </select>
                                <select id="searchdroporder" name="valu">
                                    <option value="asc" <?php if(isset($_GET['valu']) && $_GET['valu']=='asc') {echo 'selected="selected"';}?> id="asc">Ascending</option>
                                    <option value="desc" <?php if(isset($_GET['valu']) && $_GET['valu']=='desc') {echo 'selected="selected"';}?> id="desc">Descending</option>
                                </select>
                                <input type="submit" id="btnsort" value="Sort" />
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-5">
                <form method="get" action="<?php echo web_url(); ?>/admin/searchur">
                    <div align="right"><strong>by: </strong>
                        <select id="searchdrop" name="type">
                            <option value="userid" id="userid">Owner ID</option>
                            <option value="username" id="username">Owner Name</option>
                            <option value="useremail" id="useremail">Owner Email</option>
                            <option value="useraddress" id="useraddress">Owner Address</option>
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
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>State</th>
                            <th>Zipcode</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($owners as $owner) { ?>
                        <tr>
                            <td><?= $owner->id ?></td>
                            <td><?php echo $owner->first_name." ".$owner->last_name; ?> </td>
                            <td><?= $owner->email ?></td>
                            <td><?= $owner->phone ?></td>
                            <td><?= $owner->address ?></td>
                            <td><?= $owner->state ?></td>
                            <td><?= $owner->zipcode ?></td>
                            <td>
                            <div class="dropdown">
                              <button class="btn btn-green dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                                Actions
                                <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/user/edit/<?= $owner->id; ?>">Edit Owner</a></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/user/history/<?= $owner->id; ?>">View History</a></li>
                              </ul>
                            </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div align="right" id="paglink"><?php echo $owners->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>
        </div>
    </div>
</div>



@stop