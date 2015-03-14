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
                <form method="get" action="<?php echo web_url(); ?>/admin/searchrev">
                    <div align="right"><strong>by: </strong>
                        <select id="searchdrop" name="type">
                            <option value="owner" id="owner">Owner Name</option>
                            <option value="walker" id="walker">Provider</option>
                        </select>
                        <input type="text" name="valu" id="insearch" placeholder="keyword"/>
                        <input type="submit" id="btnsearch" value="Search" />
                    </div>
                </form>
                <table class="table table-stripped">
                    <thead>
                        <tr>
                            <th>Owner Name</th>
                            <th>Provider</th>
                            <th>Rating</th>
                            <th>Date and Time</th>
                            <th>Comment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review) { ?>
                        <tr>
                            <td><?php echo $review->owner_first_name." ".$review->owner_last_name; ?> </td>
                            <td><?php echo $review->walker_first_name." ".$review->walker_last_name; ?> </td>
                            <td><?= $review->rating ?></td>
                            <td><?php echo date("d M Y",strtotime($review->created_at)); ?></td>
                            <td><?= $review->comment ?></td>
                            <td><a href="<?php echo web_url(); ?>/admin/reviews/delete/<?= $review->review_id ?>"><input type="button" class="btn btn-green" value="Delete"></a></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div align="right" id="paglink"><?php echo $reviews->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>
                    
                </div>
            </div>

       </div>

@stop