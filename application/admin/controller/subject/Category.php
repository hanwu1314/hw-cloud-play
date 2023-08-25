<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;


class Category extends Backend
{
    /**
     * Subject模型对象
     * @var \app\common\model\subject\Category
     */
    protected $model = null;


    public function __construct()
    {
        parent::__construct();
        // 加载模型
        $this->model = model('subject.Category');
    }
}
