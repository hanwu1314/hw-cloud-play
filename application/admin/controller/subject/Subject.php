<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;

/**
 * 课程管理
 *
 * @icon fa fa-circle-o
 */
class Subject extends Backend
{
    // 开启关联查询
    protected $relationSearch = true;
    /**
     * Subject模型对象
     * @var \app\common\model\subject\Subject
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        // $this->model = new \app\admin\model\subject\Subject;
        $this->model = model('subject.Subject');
    }


    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->with(['category'])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
}
