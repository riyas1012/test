@extends('layout')

@section('content')

<div class="row third-portion">
    <div class="tab-content">
        <div class="tab-pane fade" id="option3">
            <div class="row tab-content-caption">
                <div class="container">
                    <div class="col-md-10 big-text">
                        <p><?= $title ?></p> 
                        <a href="<?php echo web_url(); ?>/admin/provider-type/edit/0"><input type="button" class="btn btn-green" value="Add New Provider Type"></a>
                        
                    </div>
                    
                </div><br>
            </div>
            

            <div class="row editable-content-div col-md-12">
            <div class="col-md-12">
              <div class="col-sm-4">
                        <form method="get" action="<?php echo web_url(); ?>/admin/sortpvtype">
                            <div align="right"><strong>Sort by: </strong>
                                <select id="searchdrop" name="type">
                                    <option value="provid" <?php if(isset($_GET['type']) && $_GET['type']=='provid') {echo 'selected="selected"';}?> id="provid">Provider Type ID</option>
                                    <option value="pvname" <?php if(isset($_GET['type']) && $_GET['type']=='pvname') {echo 'selected="selected"';}?> id="pvname">Provider Name</option>
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
                <form method="get" action="<?php echo web_url(); ?>/admin/searchpvtype">
                    <div align="right"><strong>by: </strong>
                        <select id="searchdrop" name="type">
                            <option value="provid" id="provid">Provider Type ID</option>
                            <option value="provname" id="provname">Provider Name</option>
                        </select>
                        <input type="text" name="valu" id="insearch" placeholder="keyword"/>
                        <input type="submit" id="btnsearch" value="Search" />
                    </div>
                </form>
                </div>
                </div>
                <div class="container">
                    <table class="table table-stripped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($types as $type) { ?>
                            <tr>
                                <td><?= $type->id ?></td>
                                <td><?= $type->name ?>
                                    <?php if($type->is_default){ ?>
                                         <font style="color:green">(Default)</font>
                                    <?php } ?>
                                </td>
                                <td><a href="<?php echo web_url(); ?>/admin/provider-type/edit/<?= $type->id ?>"><input type="button" class="btn btn-green" value="Edit"></a>
                                <?php if(!$type->is_default){ ?><a href="<?php echo web_url(); ?>/admin/provider-type/delete/<?= $type->id ?>"><input type="button" class="btn btn-green" value="Delete"></a><?php } ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div align="right" id="paglink"><?php echo $types->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>

           
        </div>
    </div>
</div>



@stop