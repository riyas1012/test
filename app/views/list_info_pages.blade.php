@extends('layout')

@section('content')

<div class="row third-portion">
    <div class="tab-content">
        <div class="tab-pane fade" id="option3">
            <div class="row tab-content-caption">
                <div class="container">
                    <div class="col-md-10 big-text">
                        <p><?= $title ?></p> 
                        <a href="<?php echo web_url(); ?>/admin/information/edit/0"><input type="button" class="btn btn-green" value="Add New Page"></a>
                        
                    </div>
                    
                </div><br>
            </div>
            
            <div class="row editable-content-div col-md-12">
                <form method="get" action="<?php echo web_url(); ?>/admin/searchinfo">
                    <div align="right"><strong>by: </strong>
                        <select id="searchdrop" name="type">
                            <option value="infoid" id="infoid">ID</option>
                            <option value="infotitle" id="infotitle">Title</option>
                        </select>
                        <input type="text" name="valu" id="insearch" placeholder="keyword"/>
                        <input type="submit" id="btnsearch" value="Search" />
                    </div>
                </form>
                <div class="container">
                        <table class="table table-stripped">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Actions</th>
                                    
                                </tr>
                            </thead>
                     
                            
                            <tbody>
                                
                                    <?php foreach ($informations as $information) { ?>
                                    <tr>
                                        <td><?= $information->id ?></td>
                                        <td><?= $information->title ?></td>
                                        <td><a href="<?php echo web_url(); ?>/admin/information/edit/<?= $information->id ?>"><input type="button" class="btn btn-green" value="Edit"></a>
                                        <a href="<?php echo web_url(); ?>/admin/information/delete/<?= $information->id ?>"><input type="button" class="btn btn-green" value="Delete"></a></td>
                                    </tr>
                                    <?php } ?>
                                
                            </tbody>
                        </table>
                
                </div>
            </div>

            <div align="right" id="paglink"><?php echo $informations->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>


        </div>
    </div>
</div>


@stop