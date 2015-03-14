@extends('layout')

@section('content')
<script type="text/javascript" src="<?php echo asset_url(); ?>/admin/javascript/searchin.js"></script>

<div class="row third-portion">
    <div class="tab-content">
        <div class="tab-pane fade" id="option3">
            <div class="row tab-content-caption">
                <div class="container">
                    <div class="col-md-10 big-text" style="padding-bottom:20px;">
                        <p><?= $title ?></p>
                        <a href="<?php echo web_url(); ?>/admin/add_admin"><button class="btn btn-green dropdown-toggle" type="button">Add Admin</button></a>
                    </div>
                </div>
            </div>
            
            <div class="row editable-content-div col-md-12">
                <div id="container" align="right">
                     <input type="text" name="searchitem" id="id_search" placeholder="Search Here..."/>
                </div>
                <table class="table table-stripped">
                    <thead>
                        <tr>
                            <th>AdminID</th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                            <?php foreach ($admin as $admins) { ?>
                            <tr>
                                <td>{{$admins->id}}</td>
                                <td>{{$admins->username}}</td>
                                <td>
                                    <div class="dropdown left">
                                      <button class="btn btn-green dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                                        Actions
                                        <span class="caret"></span>
                                      </button>
                                      <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/admins/edit/<?= $admins->id; ?>">Edit Admin</a></li>
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/admins/delete/<?= $admins->id; ?>">Delete Admin</a></li>

                                      </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                    </tbody>
                </table>
            </div>
            <div align="right" id="paglink"><?php echo $admin->links(); ?></div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $('input#id_search').quicksearch('table tbody tr', {
    });
});
</script>

@stop