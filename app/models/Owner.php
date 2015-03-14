<?php

class Owner extends Eloquent {

    protected $table = 'owner';

    public function dog()
    {
        return $this->hasOne('Dog', 'owner_id');
    }


}
