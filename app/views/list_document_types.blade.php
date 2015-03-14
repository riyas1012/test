@extends('layout')

@section('content')

<div class="row third-portion">
    <div class="tab-content">
        <div class="tab-pane fade" id="option3">
            <div class="row tab-content-caption">
                <div class="container">
                    <div class="col-md-10 big-text">
                        <p><?= $title ?></p> 
                        <a href="<?php echo web_url(); ?>/admin/document-type/edit/0"><input type="button" class="btn btn-green" value="Add New Document Type"></a>
                    </div>
                </div><br>
            </div>
            
            <div class="row editable-content-div col-md-12">
                <form method="get" action="<?php echo web_url(); ?>/admin/searchdoc">
                    <div align="right"><strong>by: </strong>
                        <select id="searchdrop" name="type">
                            <option value="docid" id="docid">ID</option>
                            <option value="docname" id="docname">Name</option>
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
                                
                                <td><a href="<?php echo web_url(); ?>/admin/document-type/edit/<?= $type->id ?>"><input type="button" class="btn btn-green" value="Edit"></a>
                                <?php if(!$type->is_default){ ?><a href="<?php echo web_url(); ?>/admin/document-type/delete/<?= $type->id ?>"><input type="button" class="btn btn-green" value="Delete"></a><?php } ?></td>
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