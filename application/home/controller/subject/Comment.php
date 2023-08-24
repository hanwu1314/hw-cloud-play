<?php

namespace app\home\controller\subject;

use app\common\controller\Home;


class Comment extends Home
{
    // 不需要登录的方法
    protected $noNeedLogin = ['index'];

    // 评论模型
    protected $CommentModel = null;

    public function __construct()
    {
        parent::__construct();

        // 加载模型
        $this->CommentModel = model('subject.Comment');
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            $subid = $this->request->param('subid', 0, 'trim');
            $page = $this->request->param('page', 1, 'trim');
            $limit = $this->request->param('limit', 20, 'trim');

            // 获取数据总条数
            $count = $this->CommentModel->where(['subid' => $subid])->count();

            // 查询数据
            $list = $this->CommentModel
                ->with(['business'])
                ->where(['subid' => $subid])
                ->page($page, $limit)
                ->select();

            $data = [
                'count' => $count,
                'list' => $list
            ];

            if ($list) {
                $this->success('返回数据', null, $data);
            } else {
                $this->error('暂无数据');
            }
        }
        return $this->fetch();
    }

    public function add()
    {
        $orderid = $this->request->param('orderid', 0, 'trim');
        // 根据订单id 查询订单
        $order = model('subject.Order')->with(['subject'])
            ->where(['busid' => $this->LoginBusiness['id'], 'order.id' => $orderid])->find();

        if (!$order) {
            $this->error('订单不存在');
        }

        $where = [
            'subid' => $order['subid'],
            'busid' => $this->LoginBusiness['id']
        ];
        // 根据课程id和用户id查询评论表
        $comment = $this->CommentModel->where($where)->find();

        if ($comment) {
            $this->error('您已评价过该订单了');
        }

        if ($this->request->isPost()) {
            $content = $this->request->param('content', '', 'trim');

            $data = [
                'content' => $content,
                'subid' => $order['subid'],
                'busid' => $this->LoginBusiness['id']
            ];

            $result = $this->CommentModel->validate('common/subject/Comment')->save($data);

            if ($result === false) {
                $this->error($this->CommentModel->getError());
            } else {
                $this->success('评价成功', url('/home/business/order'));
            }
        }

        $this->assign([
            'order' => $order
        ]);

        return $this->fetch();
    }
}
