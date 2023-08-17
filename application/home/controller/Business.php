<?php

namespace app\home\controller;

use app\common\controller\Home;

class Business extends Home
{
    public function index()
    {
        return $this->fetch();
    }
}
