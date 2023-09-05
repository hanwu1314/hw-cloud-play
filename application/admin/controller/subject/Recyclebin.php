<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;

class Recyclebin extends Backend
{
    public function index()
    {
        return $this->fetch();
    }
}
